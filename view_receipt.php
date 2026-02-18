<?php
ob_start();
session_start();

// Force MySQLi to throw exceptions for better debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    include "includes/db.php";

    // Get order ID
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($order_id <= 0) {
        throw new Exception("Invalid Order ID provided.");
    }

    // Allow both users and admins
    $is_admin = isset($_SESSION['admin']);
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$is_admin && $user_id <= 0) {
        throw new Exception("Authentication required. Please log in to view receipts.");
    }

    // Fetch order details
    $order_query = "SELECT o.* FROM orders o WHERE o.id = $order_id";
    if (!$is_admin) {
        $order_query .= " AND o.user_id = $user_id";
    }

    $order_result = mysqli_query($conn, $order_query);
    if (!$order_result || mysqli_num_rows($order_result) == 0) {
        throw new Exception("Order not found or you don't have permission to view it.");
    }
    $order = mysqli_fetch_assoc($order_result);

    // FETCH ORDER ITEMS - NEW SIMPLIFIED LOGIC
    $order_items = [];
    
    // First, try to get anything from order_items for this order
    $base_items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
    $base_items_res = mysqli_query($conn, $base_items_query);
    
    while ($item = mysqli_fetch_assoc($base_items_res)) {
        // Now find the product info if missing
        $pid = (int)$item['product_id'];
        $p_query = "SELECT name, image, price FROM products WHERE id = $pid";
        $p_res = mysqli_query($conn, $p_query);
        $p_data = mysqli_fetch_assoc($p_res);
        
        // Fill fallbacks
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

    // Format date
    $order_date = date('d M Y, h:i A', strtotime($order['created_at']));

    // Payment method display
    $payment_methods = [
        'razorpay' => 'Razorpay (UPI/Card/Wallet)',
        'upi' => 'UPI',
        'credit_card' => 'Credit Card',
        'bank_deposit' => 'Bank Deposit',
        'cod' => 'Cash On Delivery (COD)',
        'gift_card' => 'Gift Card'
    ];
    $payment_method_display = $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);

    // Check for gift card discount
    $gift_card_discount = 0;
    $gift_card_query = "SELECT amount_used FROM gift_card_transactions WHERE order_id = {$order['id']} AND transaction_type = 'usage' LIMIT 1";
    $gift_card_result = mysqli_query($conn, $gift_card_query);
    if ($gift_card_result && mysqli_num_rows($gift_card_result) > 0) {
        $gift_card_row = mysqli_fetch_assoc($gift_card_result);
        $gift_card_discount = floatval($gift_card_row['amount_used']);
    }

    // Ensure GST is shown (12% if not set)
    $display_tax = floatval($order['tax_amount']);
    if ($display_tax <= 0) {
        $display_tax = floatval($order['subtotal']) * 0.12;
    }

    // Calculate mathematically correct display total
    $display_total = floatval($order['subtotal']) + floatval($order['shipping_cost']) + $display_tax - $gift_card_discount;

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<div style='padding: 50px; font-family: sans-serif; max-width: 800px; margin: 50px auto; background: #fff1f1; border: 1px solid #ff0000; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color: #d11; margin-top: 0;'>⚠️ Receipt Generation Error</h2>";
    echo "<p style='font-size: 16px; color: #333;'>We encountered a database issue while preparing your receipt. Please try again or contact support.</p>";
    echo "<div style='background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #ddd; margin-top: 20px;'>";
    echo "<p><strong>Error Details:</strong></p>";
    echo "<code style='display: block; padding: 10px; background: #f8f8f8; border-radius: 4px; overflow-x: auto;'>" . htmlspecialchars($e->getMessage()) . "</code>";
    echo "<p style='margin-top: 10px; font-size: 13px; color: #888;'>Order ID: $order_id | Admin: " . ($is_admin ? 'Yes' : 'No') . "</p>";
    echo "</div>";
    echo "<div style='margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>";
    echo "<a href='account.php' style='display: inline-block; padding: 10px 20px; background: #333; color: #fff; text-decoration: none; border-radius: 6px;'>Return to Dashboard</a>";
    echo "</div>";
    echo "</div>";
    exit;
}

include "includes/header.php";
?>
<style>
    :root {
        --primary-color: #00c2cb;
        --secondary-color: #e91e63;
        --text-dark: #2b2b2b;
        --text-light: #666;
        --border-color: #eee;
    }
    .receipt-page {
        min-height: 100vh;
        background: #f0f2f5;
        padding: 60px 20px;
    }
    
    .receipt-container { 
        max-width: 850px; 
        margin: 0 auto; 
        background: #ffffff; 
        padding: 50px; 
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.08);
        font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        color: var(--text-dark);
        position: relative;
        overflow: hidden;
    }
    
    .receipt-header { 
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px; 
        padding-bottom: 30px; 
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
        margin-bottom: 40px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--primary-color);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
    }
    .info-item { margin-bottom: 12px; }
    .info-label { font-size: 10px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .info-value { font-size: 13px; font-weight: 600; color: var(--text-dark); }
    .payment-status-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 800;
        background: #e6f9f1;
        color: #15803d;
    }

    .items-section { margin-bottom: 30px; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { 
        text-align: left; 
        padding: 12px; 
        font-size: 11px; 
        font-weight: 800;
        color: #555;
        text-transform: uppercase;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }
    .items-table td { padding: 15px 12px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
    .p-image { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    .p-name { font-weight: 700; font-size: 13px; color: var(--text-dark); display: block; max-width: 400px; }
    .p-qty { font-size: 13px; color: var(--text-light); text-align: center; font-weight: 600; }
    .p-price { font-weight: 700; color: var(--text-dark); text-align: right; font-size: 13px; }

    .totals-area {
        float: right;
        width: 300px;
        margin-top: 20px;
        background: #fdfdfd;
        padding: 15px;
        border-radius: 10px;
    }
    .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; color: #666; }
    .total-row.grand-total { 
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #ddd;
        font-size: 22px;
        font-weight: 900;
        color: var(--secondary-color);
    }
    
    .receipt-bottom {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .terms-box { font-size: 10px; color: #999; line-height: 1.5; width: 300px; }
    .thank-you-text { font-size: 13px; color: var(--primary-color); font-weight: 700; margin-top: 15px; }

    .sign-section { text-align: right; }
    .combined-sign-img { width: 280px; height: auto; }
    .auth-line { 
        border-top: 1.5px solid #333; 
        padding-top: 5px; 
        font-size: 10px; 
        font-weight: 800; 
        text-transform: uppercase;
        width: 280px;
        text-align: center;
        margin-left: auto;
    }

    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 80px;
        font-weight: 900;
        color: rgba(0, 194, 203, 0.03);
        pointer-events: none;
        white-space: nowrap;
    }

    .receipt-actions {
        margin-top: 40px;
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .action-btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
    }
    .btn-pink { background: linear-gradient(135deg, var(--secondary-color), #c2185b); color: #fff; }
    .btn-gray { background: #f0f0f0; color: #555; }

    .clearfix::after { content: ""; clear: both; display: table; }

    @media (max-width: 768px) {
        .receipt-grid { grid-template-columns: 1fr; }
        .receipt-bottom { flex-direction: column; align-items: center; text-align: center; }
        .sign-section { margin-top: 30px; }
        .auth-line { margin: 5px auto; }
    }
</style>

<div class="receipt-page">
    <div class="receipt-container" id="receipt-content">
        <div class="watermark">CRAFT ROYALE</div>
        
        <div class="receipt-header">
            <div class="brand-section">
                <h1>Craft Royale</h1>
                <p>Premium Embroidery & Craft Supplies</p>
            </div>
            <div class="order-meta">
                <div class="order-number">ORDER #<?= htmlspecialchars($order['order_number']) ?></div>
                <div class="order-date-text"><?= $order_date ?></div>
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
                            // Robust image path resolution
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
                            <td colspan="4" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-box-open" style="display: block; font-size: 24px; margin-bottom: 10px; color: #eee;"></i>
                                No items found in records for Order #<?= $order_id ?>.
                            </td>
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
                
                <?php 
                // Regular Discount (if any)
                $regular_discount = floatval($order['discount_amount'] ?? 0);
                if ($regular_discount > 0): ?>
                    <div class="total-row" style="color: #15803d;"><span>Discount:</span><span>-₹<?= number_format($regular_discount, 2) ?></span></div>
                <?php endif; ?>

                <?php if ($gift_card_discount > 0): ?>
                    <div class="total-row" style="color: #0d47a1; font-weight: 600;"><span>Gift Card:</span><span>-₹<?= number_format($gift_card_discount, 2) ?></span></div>
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

        <div class="receipt-actions">
            <button onclick="downloadReceipt(<?= $order['id'] ?>)" class="action-btn btn-pink">
                <i class="fas fa-file-download"></i> Download Premium PDF
            </button>
            <a href="account.php" class="action-btn btn-gray">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
