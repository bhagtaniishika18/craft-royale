<?php
include 'includes/db.php';
include 'includes/header.php';

$order = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_input = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
    $email_phone = isset($_POST['email_phone']) ? trim($_POST['email_phone']) : '';
    
    if (empty($order_input) || empty($email_phone)) {
        $error = 'Please enter both Order ID and Email/Phone Number';
    } else {
        // Clean the order input - remove #, spaces, and handle different formats
        $order_input = str_replace(['#', ' ', '-'], '', $order_input);
        
        // Check if it's numeric (just numbers) or contains letters (like CR202601218068)
        $email_phone_escaped = mysqli_real_escape_string($conn, $email_phone);
        
        // Try searching by order_number first (most common format like CR202601218068)
        $order_number_escaped = mysqli_real_escape_string($conn, $order_input);
        
        // Also try with CR prefix if not present
        $order_number_with_prefix = $order_number_escaped;
        if (!preg_match('/^CR/i', $order_number_escaped)) {
            $order_number_with_prefix = 'CR' . $order_number_escaped;
        }
        
        // Search by order_number (with or without CR prefix) and email/phone
        $query = "SELECT * FROM orders 
                 WHERE (order_number = '$order_number_escaped' 
                        OR order_number = '$order_number_with_prefix'
                        OR REPLACE(REPLACE(order_number, '-', ''), ' ', '') = '$order_number_escaped')
                 AND (email = '$email_phone_escaped' OR phone = '$email_phone_escaped')";
        
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $order = mysqli_fetch_assoc($result);
        } else {
            // If not found by order_number, try by numeric ID as fallback
            if (is_numeric($order_input)) {
                $order_id_escaped = (int)$order_input;
                $query = "SELECT * FROM orders 
                         WHERE id = $order_id_escaped 
                         AND (email = '$email_phone_escaped' OR phone = '$email_phone_escaped')";
                $result = mysqli_query($conn, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $order = mysqli_fetch_assoc($result);
                } else {
                    $error = 'Order not found. Please check your Order ID and Email/Phone Number.';
                }
            } else {
                $error = 'Order not found. Please check your Order ID and Email/Phone Number.';
            }
        }
    }
}

// If order found, format dates
if ($order) {
    $order_date = date('d M Y, h:i A', strtotime($order['created_at']));
    $updated_date = date('d M Y, h:i A', strtotime($order['updated_at']));
    
    // Define status details
    $status_details = [
        'pending' => [
            'title' => 'Order Pending',
            'description' => 'Your order has been received and is being prepared.',
            'icon' => '⏳',
            'active' => in_array($order['order_status'], ['pending', 'processing', 'shipped', 'delivered'])
        ],
        'processing' => [
            'title' => 'Order Processing',
            'description' => 'Your order is being processed and packed.',
            'icon' => '📦',
            'active' => in_array($order['order_status'], ['processing', 'shipped', 'delivered'])
        ],
        'shipped' => [
            'title' => 'Order Shipped',
            'description' => 'Your order has been shipped and is on the way.',
            'icon' => '🚚',
            'active' => in_array($order['order_status'], ['shipped', 'delivered'])
        ],
        'delivered' => [
            'title' => 'Order Delivered',
            'description' => 'Your order has been delivered successfully.',
            'icon' => '✅',
            'active' => $order['order_status'] === 'delivered'
        ]
    ];
    
    $status_order = ['pending', 'processing', 'shipped', 'delivered'];
    $current_status_index = array_search($order['order_status'], $status_order);
    if ($current_status_index === false) {
        $current_status_index = 0;
    }
}
?>

<style>
    .track-order-page {
        min-height: calc(100vh - 200px);
        position: relative;
        background-image: url('assets/images/tracking-bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        padding: 80px 20px;
        overflow: hidden;
    }
    
    .track-order-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.7) 0%, rgba(118, 75, 162, 0.7) 50%, rgba(240, 147, 251, 0.6) 100%);
        z-index: 1;
    }
    
    .track-order-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .track-order-container {
        max-width: 550px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .track-order-form-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        padding: 50px 40px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(20px);
        position: relative;
        overflow: hidden;
    }
    
    .track-order-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #2fc7b4, #2fa76b, #667eea, #764ba2);
        background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .track-order-form-card h2 {
        text-align: center;
        margin-bottom: 35px;
        color: #2b2b2b;
        font-size: 32px;
        font-weight: 800;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }
    
    .track-order-form-group {
        margin-bottom: 25px;
        position: relative;
    }
    
    .track-order-form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 700;
        color: #2b2b2b;
        font-size: 15px;
        letter-spacing: 0.3px;
    }
    
    .track-order-form-group input {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e8e8e8;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
        background: #fff;
        color: #2b2b2b;
    }
    
    .track-order-form-group input:focus {
        outline: none;
        border-color: #2fa76b;
        box-shadow: 0 0 0 4px rgba(47, 167, 107, 0.1), 0 4px 12px rgba(47, 167, 107, 0.15);
        transform: translateY(-2px);
    }
    
    .track-order-form-group input::placeholder {
        color: #bbb;
    }
    
    .track-order-btn {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #2b2b2b 0%, #1a1a1a 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 15px;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .track-order-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }
    
    .track-order-btn:hover::before {
        left: 100%;
    }
    
    .track-order-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        background: linear-gradient(135deg, #1a1a1a 0%, #2b2b2b 100%);
    }
    
    .track-order-btn:active {
        transform: translateY(-1px);
    }
    
    .track-order-error {
        background: linear-gradient(135deg, #fee 0%, #fdd 100%);
        color: #c33;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        border-left: 4px solid #c33;
        box-shadow: 0 4px 12px rgba(204, 51, 51, 0.1);
        animation: shake 0.5s ease;
        font-weight: 600;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    
    .track-order-results {
        margin-top: 35px;
        padding-top: 35px;
        border-top: 2px solid #e8e8e8;
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .track-order-powered {
        text-align: center;
        margin-top: 25px;
        font-size: 13px;
        color: #999;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    
    .order-status-timeline {
        margin-top: 35px;
    }
    
    .order-status-timeline h3 {
        margin-bottom: 25px;
        color: #2b2b2b;
        font-size: 22px;
        font-weight: 800;
        text-align: center;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .timeline-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        position: relative;
        padding: 15px;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .timeline-item:hover {
        background: rgba(47, 167, 107, 0.05);
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 35px;
        top: 65px;
        width: 3px;
        height: calc(100% + 15px);
        background: linear-gradient(180deg, #e0e0e0 0%, #f0f0f0 100%);
    }
    
    .timeline-item.completed:not(:last-child)::after {
        background: linear-gradient(180deg, #28a745 0%, #e0e0e0 100%);
    }
    
    .timeline-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        background: #f5f5f5;
        color: #999;
        border: 3px solid #e0e0e0;
        position: relative;
        z-index: 2;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-item.active .timeline-icon {
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        color: #fff;
        border-color: #2fa76b;
        box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
        transform: scale(1.1);
        animation: pulse 2s ease infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4); }
        50% { box-shadow: 0 6px 30px rgba(47, 167, 107, 0.6); }
    }
    
    .timeline-item.completed .timeline-icon {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff;
        border-color: #28a745;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
    
    .timeline-content {
        flex: 1;
        padding-top: 8px;
    }
    
    .timeline-title {
        font-size: 18px;
        font-weight: 800;
        color: #2b2b2b;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }
    
    .timeline-item.active .timeline-title {
        color: #2fa76b;
    }
    
    .timeline-item.completed .timeline-title {
        color: #28a745;
    }
    
    .timeline-description {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
    }
    
    .order-info-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 25px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #e8e8e8;
    }
    
    .order-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e8e8e8;
        transition: all 0.3s ease;
    }
    
    .order-info-row:hover {
        background: rgba(47, 167, 107, 0.03);
        margin: 0 -10px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
    }
    
    .order-info-row:last-child {
        border-bottom: none;
    }
    
    .order-info-label {
        font-weight: 700;
        color: #666;
        font-size: 14px;
        letter-spacing: 0.3px;
    }
    
    .order-info-value {
        color: #2b2b2b;
        font-weight: 700;
        font-size: 15px;
    }
    
    .track-order-action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .track-order-action-btn {
        flex: 1;
        min-width: 150px;
        padding: 14px 20px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        letter-spacing: 0.3px;
    }
    
    .track-order-btn-track {
        background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
        color: #fff;
    }
    
    .track-order-btn-track:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(47, 167, 107, 0.4);
    }
    
    .track-order-btn-receipt {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
        color: #fff;
    }
    
    .track-order-btn-receipt:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4);
    }
    
    .track-order-btn-download {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
        color: #fff;
    }
    
    .track-order-btn-download:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4);
    }
    
    .track-order-btn-disabled {
        background: #e0e0e0 !important;
        color: #999 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }
    
    .track-order-btn-disabled:hover {
        transform: none !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        background: #e0e0e0 !important;
    }
    
    button.track-order-btn-disabled:disabled {
        background: #e0e0e0 !important;
        color: #999 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }
    
    .track-order-results a {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #2fa76b;
        text-decoration: none;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 2px solid #2fa76b;
    }
    
    .track-order-results a:hover {
        background: #2fa76b;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(47, 167, 107, 0.3);
    }
    
    @media (max-width: 768px) {
        .track-order-page {
            padding: 40px 15px;
        }
        
        .track-order-form-card {
            padding: 35px 25px;
            border-radius: 20px;
        }
        
        .track-order-form-card h2 {
            font-size: 26px;
        }
        
        .order-info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
    }
</style>

<div class="track-order-page">
    <div class="track-order-container">
        <div class="track-order-form-card">
            <h2 style="text-align: center; margin-bottom: 30px; color: #2b2b2b; font-size: 28px; font-weight: 700;">Track Your Order</h2>
            
            <?php if ($error): ?>
                <div class="track-order-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!$order): ?>
                <form method="POST" action="">
                    <div class="track-order-form-group">
                        <label for="order_id">Order ID</label>
                        <input type="text" id="order_id" name="order_id" placeholder="Enter your order ID (e.g., CR202601218068)" value="<?= isset($_POST['order_id']) ? htmlspecialchars($_POST['order_id']) : '' ?>" required>
                    </div>
                    
                    <div class="track-order-form-group">
                        <label for="email_phone">Email or Phone Number</label>
                        <input type="text" id="email_phone" name="email_phone" placeholder="Enter your email or phone number" value="<?= isset($_POST['email_phone']) ? htmlspecialchars($_POST['email_phone']) : '' ?>" required>
                    </div>
                    
                    <button type="submit" class="track-order-btn">Track Your Order</button>
                    
                    <div class="track-order-powered">Powered by Track123</div>
                </form>
            <?php else: ?>
                <div class="track-order-results">
                    <div class="order-info-box">
                        <div class="order-info-row">
                            <span class="order-info-label">Order ID:</span>
                            <span class="order-info-value">#<?= htmlspecialchars($order['id']) ?></span>
                        </div>
                        <?php if (!empty($order['order_number'])): ?>
                        <div class="order-info-row">
                            <span class="order-info-label">Order Number:</span>
                            <span class="order-info-value"><?= htmlspecialchars($order['order_number']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="order-info-row">
                            <span class="order-info-label">Order Date:</span>
                            <span class="order-info-value"><?= $order_date ?></span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label">Status:</span>
                            <span class="order-info-value" style="color: #2fa76b; font-weight: 700;"><?= ucfirst($order['order_status']) ?></span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label">Total Amount:</span>
                            <span class="order-info-value" style="color: #e91e63; font-size: 18px;">₹<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="order-status-timeline">
                        <h3 style="margin-bottom: 20px; color: #2b2b2b;">Order Status</h3>
                        <?php 
                        foreach ($status_order as $index => $status): 
                            $detail = $status_details[$status];
                            $is_current = $order['order_status'] === $status;
                            $is_completed = $current_status_index > $index;
                            
                            $item_class = '';
                            if ($is_current) {
                                $item_class = 'active';
                            } elseif ($is_completed) {
                                $item_class = 'completed';
                            }
                        ?>
                            <div class="timeline-item <?= $item_class ?>">
                                <div class="timeline-icon"><?= $detail['icon'] ?></div>
                                <div class="timeline-content">
                                    <div class="timeline-title"><?= $detail['title'] ?></div>
                                    <div class="timeline-description"><?= $detail['description'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="track-order-action-buttons">
                        <?php 
                        // Get order status - simple direct check
                        $order_status_check = strtolower(trim($order['order_status']));
                        $is_delivered = ($order_status_check === 'delivered');
                        ?>
                        
                        <?php if ($is_delivered): ?>
                            <!-- Delivered: Disable Track Order, Show Download Receipt -->
                            <a href="javascript:void(0)" onclick="downloadReceipt(<?= $order['id'] ?>)" class="track-order-action-btn track-order-btn-download">
                                <span>⬇</span> Download Receipt
                            </a>
                            <button type="button" class="track-order-action-btn track-order-btn-disabled" disabled style="background: #e0e0e0 !important; color: #999 !important; cursor: not-allowed !important; opacity: 0.6 !important; pointer-events: none !important; border: none !important;">
                                <span>🚚</span> Track Order
                            </button>
                        <?php else: ?>
                            <!-- Not Delivered: Show Track Order and View Receipt -->
                            <a href="track_order.php?id=<?= $order['id'] ?>" class="track-order-action-btn track-order-btn-track">
                                <span>🚚</span> Track Order
                            </a>
                            <a href="view_receipt.php?id=<?= $order['id'] ?>" class="track-order-action-btn track-order-btn-receipt">
                                <span>📄</span> View Receipt
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <script>
                    // Force disable Track Order button if order is delivered
                    (function() {
                        var orderStatus = '<?= strtolower(trim($order['order_status'])) ?>';
                        if (orderStatus === 'delivered') {
                            var buttons = document.querySelectorAll('.track-order-action-buttons button, .track-order-action-buttons a');
                            buttons.forEach(function(btn) {
                                if (btn.textContent.includes('Track Order') && btn.tagName === 'BUTTON') {
                                    btn.disabled = true;
                                    btn.style.background = '#e0e0e0';
                                    btn.style.color = '#999';
                                    btn.style.cursor = 'not-allowed';
                                    btn.style.opacity = '0.6';
                                    btn.style.pointerEvents = 'none';
                                    btn.onclick = function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        return false;
                                    };
                                }
                            });
                        }
                    })();
                    </script>
                    
                    <a href="track-order.php" style="display: block; text-align: center; margin-top: 30px; color: #2fa76b; text-decoration: none; font-weight: 600; padding: 12px; border-radius: 8px; transition: all 0.3s ease;">Track Another Order</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
