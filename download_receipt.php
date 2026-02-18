<?php
/**
 * Craft Royale - Precise Receipt Download
 * Matches the layout and logic of view_receipt.php exactly.
 */

// Disable warnings to ensure clean PDF generation
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once "includes/db.php";

// 1. Basic Validation
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    die('Error: Invalid Order ID.');
}

$is_admin = isset($_SESSION['admin']);
$user_id = $_SESSION['user_id'] ?? 0;
if (!$is_admin && $user_id <= 0) {
    die('Error: Authentication required.');
}

// 2. Fetch Order Details
$order_query = "SELECT o.* FROM orders o WHERE o.id = $order_id";
if (!$is_admin) {
    $order_query .= " AND o.user_id = $user_id";
}
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die('Error: Order not found.');
}

// 3. Fetch Order Items (Matching refined view_receipt.php approach)
$order_items = [];
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id ORDER BY id ASC";
$items_res = mysqli_query($conn, $items_query);

while ($item = mysqli_fetch_assoc($items_res)) {
    $pid = (int)$item['product_id'];
    $p_query = "SELECT name, image, price FROM products WHERE id = $pid";
    $p_res = mysqli_query($conn, $p_query);
    $p_data = mysqli_fetch_assoc($p_res);
    
    if (empty($item['product_name']) && !empty($p_data['name'])) $item['product_name'] = $p_data['name'];
    if (empty($item['product_name'])) $item['product_name'] = 'Product #' . $pid;
    
    if (empty($item['product_image']) && !empty($p_data['image'])) $item['product_image'] = $p_data['image'];
    
    $item['unit_price'] = floatval($item['unit_price']);
    if ($item['unit_price'] <= 0 && !empty($p_data['price'])) {
        $item['unit_price'] = floatval($p_data['price']);
        $item['subtotal'] = $item['unit_price'] * $item['quantity'];
    }
    $item['subtotal'] = floatval($item['subtotal'] > 0 ? $item['subtotal'] : ($item['unit_price'] * $item['quantity']));
    
    $order_items[] = $item;
}

// 4. Financial Logic
$display_tax = floatval($order['tax_amount']);
if ($display_tax <= 0) {
    $display_tax = floatval($order['subtotal']) * 0.12;
}

$gift_card_discount = 0;
$gift_card_query = "SELECT amount_used FROM gift_card_transactions WHERE order_id = {$order['id']} AND transaction_type = 'usage' LIMIT 1";
$gift_card_result = mysqli_query($conn, $gift_card_query);
if ($gift_card_result && mysqli_num_rows($gift_card_result) > 0) {
    $gift_card_row = mysqli_fetch_assoc($gift_card_result);
    $gift_card_discount = floatval($gift_card_row['amount_used']);
}

$display_total = floatval($order['subtotal']) + floatval($order['shipping_cost']) + $display_tax - $gift_card_discount;

$payment_methods = [
    'razorpay' => 'Razorpay (UPI/Card/Wallet)',
    'upi' => 'UPI',
    'credit_card' => 'Credit Card',
    'bank_deposit' => 'Bank Deposit',
    'cod' => 'Cash On Delivery (COD)',
    'gift_card' => 'Gift Card'
];
$payment_method_display = $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);

// 5. Capture HTML Content
ob_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    :root {
        --primary-color: #00c2cb;
        --secondary-color: #e91e63;
        --text-dark: #2b2b2b;
        --text-light: #666;
        --border-color: #eee;
    }
    body { margin: 0; padding: 0; background: #fff; }
    .receipt-container { 
        width: 800px; 
        margin: 0; 
        background: #ffffff; 
        padding: 40px; 
        font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        color: var(--text-dark);
        position: relative;
    }
    
    .receipt-header { 
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px; 
        padding-bottom: 20px; 
    }
    .brand-section h1 { 
        font-size: 38px; 
        font-weight: 900; 
        color: var(--secondary-color); 
        margin: 0;
    }
    .brand-section p { 
        font-size: 13px; 
        color: var(--text-light); 
        margin: 5px 0 0 0;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .order-meta { text-align: right; }
    .order-number { 
        font-size: 18px; 
        font-weight: 800; 
        color: var(--text-dark);
        margin-bottom: 5px;
    }
    .order-date-text { font-size: 14px; color: var(--text-light); }

    .receipt-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .section-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 18px;
    }
    .info-item { margin-bottom: 10px; }
    .info-label { font-size: 9px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .info-value { font-size: 12px; font-weight: 600; color: var(--text-dark); }
    .payment-status-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 800;
        background: #e6f9f1;
        color: #15803d;
        text-transform: uppercase;
    }

    .items-section { margin-bottom: 30px; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { 
        text-align: left; 
        padding: 10px; 
        font-size: 10px; 
        font-weight: 800;
        color: #555;
        text-transform: uppercase;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }
    .items-table td { padding: 12px 10px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
    .p-image { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    .p-name { font-weight: 700; font-size: 12px; color: var(--text-dark); display: block; max-width: 450px; }
    .p-qty { font-size: 12px; color: var(--text-light); text-align: center; font-weight: 600; }
    .p-price { font-weight: 700; color: var(--text-dark); text-align: right; font-size: 12px; }

    .totals-area {
        float: right;
        width: 280px;
        margin-top: 20px;
        background: #fdfdfd;
        padding: 15px;
        border-radius: 10px;
    }
    .total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; color: #666; }
    .total-row.grand-total { 
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #ddd;
        font-size: 20px;
        font-weight: 900;
        color: var(--secondary-color);
    }
    
    .receipt-bottom {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .terms-box { font-size: 9px; color: #999; line-height: 1.5; width: 300px; }
    .thank-you-text { font-size: 12px; color: var(--primary-color); font-weight: 700; margin-top: 10px; }

    .sign-section { text-align: right; }
    .combined-sign-img { width: 220px; height: auto; }
    .auth-line { 
        border-top: 1.5px solid #333; 
        padding-top: 4px; 
        font-size: 9px; 
        font-weight: 800; 
        text-transform: uppercase;
        width: 220px;
        text-align: center;
        margin-left: auto;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70px;
        font-weight: 900;
        color: rgba(0, 194, 203, 0.03);
        pointer-events: none;
        white-space: nowrap;
        z-index: 0;
    }
    .clearfix::after { content: ""; clear: both; display: table; }
</style>

<div class="receipt-container" id="receipt-capture-target">
    <div class="watermark">CRAFT ROYALE</div>
    
    <div class="receipt-header">
        <div class="brand-section">
            <h1>Craft Royale</h1>
            <p>Premium Embroidery & Craft Supplies</p>
        </div>
        <div class="order-meta">
            <div class="order-number">ORDER #<?= htmlspecialchars($order['order_number']) ?></div>
            <div class="order-date-text"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
        </div>
    </div>

    <div class="receipt-grid">
        <div class="grid-item">
            <h2 class="section-title"><i class="fas fa-user-circle"></i> BILLING & SHIPPING</h2>
            <div class="info-card">
                <div class="info-item">
                    <div class="info-label">Customer Name</div>
                    <div class="info-value"><?= htmlspecialchars($order['name']) ?></div>
                </div>
                <div class="info-item" style="margin-bottom: 0;">
                    <div class="info-label">Delivery Address</div>
                    <div class="info-value">
                        <?= htmlspecialchars($order['address']) ?><br>
                        <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> - <?= htmlspecialchars($order['pin_code']) ?><br>
                        <?= htmlspecialchars($order['country'] ?? 'India') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid-item">
            <h2 class="section-title"><i class="fas fa-credit-card"></i> PAYMENT DETAILS</h2>
            <div class="info-card">
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= htmlspecialchars($payment_method_display) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Current Status</div>
                    <div class="info-value"><span class="payment-status-pill"><?= strtoupper($order['payment_status']) ?></span></div>
                </div>
                <div class="info-item" style="margin-bottom: 0;">
                    <div class="info-label">Transaction ID</div>
                    <div class="info-value"><?= !empty($order['payment_id']) ? htmlspecialchars($order['payment_id']) : 'N/A' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr>
                    <th width="70%">PRODUCT</th>
                    <th style="text-align: center;">QTY</th>
                    <th style="text-align: right;">UNITPRICE (₹)</th>
                    <th style="text-align: right;">SUBTOTAL (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): 
                        $item_img = !empty($item['product_image']) ? $item['product_image'] : '';
                        $product_image = 'assets/images/placeholder.jpg';
                        
                        if (!empty($item_img)) {
                            $clean_img = str_replace(array('?', '–', '—'), '-', $item_img);
                            $possible_paths = [
                                $item_img, $clean_img,
                                'uploads/products/' . $item_img, 'uploads/products/' . $clean_img,
                                'products/' . $item_img, 'products/' . $clean_img,
                                'assets/images/' . $item_img, 'assets/images/' . $clean_img
                            ];
                            
                            foreach ($possible_paths as $path) {
                                if (!empty($path) && file_exists($path)) {
                                    $product_image = $path;
                                    break;
                                }
                            }
                        }
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="<?= htmlspecialchars($product_image) ?>" class="p-image" onerror="this.src='assets/images/placeholder.jpg'">
                                    <span class="p-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                </div>
                            </td>
                            <td class="p-qty"><?= (int)$item['quantity'] ?></td>
                            <td class="p-price"><?= number_format($item['unit_price'], 2) ?></td>
                            <td class="p-price"><?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #999;">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="clearfix">
        <div class="totals-area">
            <?php 
                $gst_label = "GST (12%)";
                $current_subtotal = floatval($order['subtotal']);
                if ($current_subtotal > 0) {
                    $gst_rate = round(($display_tax / $current_subtotal) * 100);
                    if ($gst_rate > 0) $gst_label = "GST ($gst_rate%)";
                }
            ?>
            <div class="total-row"><span>Subtotal:</span><span>₹<?= number_format($order['subtotal'], 2) ?></span></div>
            <div class="total-row"><span>Shipping:</span><span>₹<?= number_format($order['shipping_cost'], 2) ?></span></div>
            <div class="total-row"><span><?= $gst_label ?>:</span><span>₹<?= number_format($display_tax, 2) ?></span></div>
            <?php if ($gift_card_discount > 0): ?>
            <div class="total-row" style="color: #15803d;"><span>Discount:</span><span>-₹<?= number_format($gift_card_discount, 2) ?></span></div>
            <?php endif; ?>
            <div class="total-row grand-total">
                <span>Grand Total:</span>
                <span>₹<?= number_format($display_total, 2) ?></span>
            </div>
        </div>
    </div>

    <div class="receipt-bottom">
        <div class="bottom-left">
            <div class="terms-box">
                Terms & Conditions:<br>
                1. Goods once sold will not be taken back.<br>
                2. Any claims should be reported within 24 hours of delivery.<br>
                3. This is a computer generated document.
            </div>
            <div class="thank-you-text">✨ Thank you for choosing Craft Royale! ✨</div>
        </div>
        <div class="sign-section">
            <img src="assets/images/ss.png" class="combined-sign-img" alt="Authorized Signature">
            <div class="auth-line">AUTHORIZED SIGNATURE</div>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();
if (isset($_GET['html_only'])) { echo $html; exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Downloading Invoice #<?= htmlspecialchars($order['order_number']) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { margin: 0; padding: 0; background: #fafafa; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .loader-wrap { text-align: center; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #e91e63; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h3 { color: #333; margin: 0; }
        p { color: #888; font-size: 13px; margin: 5px 0 0 0; }
    </style>
</head>
<body>
    <div class="loader-wrap">
        <div class="spinner"></div>
        <h3>Generating Your Invoice</h3>
        <p>This will only take a moment...</p>
    </div>
    <div style="position: absolute; left: -9999px; top: 0; width: 800px; background: #fff;">
        <?= $html ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const target = document.getElementById('receipt-capture-target');
                const images = target.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    return new Promise(resolve => {
                        if (img.complete) resolve();
                        else { img.onload = resolve; img.onerror = resolve; }
                    });
                });
                Promise.all(imagePromises).then(() => {
                    html2canvas(target, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(canvas => {
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'a4');
                        const imgData = canvas.toDataURL('image/jpeg', 0.95);
                        pdf.addImage(imgData, 'JPEG', 0, 0, 210, (canvas.height * 210) / canvas.width);
                        pdf.save('Craft_Royale_Invoice_<?= $order["order_number"] ?>.pdf');
                        setTimeout(() => { if (window.opener) window.close(); else window.location.href = 'account.php'; }, 1000);
                    }).catch(err => { alert("Error generating PDF."); });
                });
            }, 800);
        });
    </script>
</body>
</html>
