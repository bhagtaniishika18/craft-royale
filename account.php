<?php
session_start();

// Prevent caching of authenticated pages
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

include 'includes/db.php';

// Check if user is logged in - redirect if not
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';

// Get user data
$user_logged_in = true;
$user_id = $_SESSION['user_id'];
$user_data = null;
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
}

// Get user orders
$orders = [];
$orders_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
if ($orders_result && mysqli_num_rows($orders_result) > 0) {
    while ($order = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $order;
    }
}

// Get return/refund requests for all orders
$return_requests = [];
foreach ($orders as $order) {
    $return_query = "SELECT * FROM return_refund_requests WHERE order_id = {$order['id']} ORDER BY created_at DESC LIMIT 1";
    $return_result = mysqli_query($conn, $return_query);
    if ($return_result && mysqli_num_rows($return_result) > 0) {
        $return_requests[$order['id']] = mysqli_fetch_assoc($return_result);
    }
}
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline preload="auto" webkit-playsinline>
        <source src="assets/images/my_orders.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="My Account Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">MY ORDERS</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">My Orders</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="account-section">
    <div class="account-container">
        <?php if (!$user_logged_in): ?>
            <!-- NOT LOGGED IN - SHOW LOGIN/REGISTER OPTIONS -->
            <div class="account-card">
                <div class="account-header">
                    <h2>My Account</h2>
                </div>
                <div class="account-body">
                    <div class="account-tabs">
                        <button class="tab-btn active" onclick="showTab('login')">Login</button>
                        <button class="tab-btn" onclick="showTab('register')">Register</button>
                    </div>

                    <!-- LOGIN TAB -->
                    <div id="loginTab" class="tab-content active">
                        <form id="accountLoginForm" onsubmit="handleAccountLogin(event)">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <label>Password *</label>
                                <input type="password" name="password" placeholder="Password" required>
                            </div>
                            <div class="form-options">
                                <a href="#" class="forgot-link">Forgot your password?</a>
                            </div>
                            <button type="submit" class="account-btn">SIGN IN</button>
                            <p class="account-switch">
                                New customer? <a href="#" onclick="showTab('register'); return false;">Create your account</a>
                            </p>
                            <div class="social-login">
                                <p>Or login with</p>
                                <div class="social-buttons">
                                    <button type="button" class="social-btn facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </button>
                                    <button type="button" class="social-btn google">
                                        <i class="fab fa-google"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- REGISTER TAB -->
                    <div id="registerTab" class="tab-content">
                        <form id="accountRegisterForm" onsubmit="handleAccountRegister(event)">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" placeholder="First Name" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" placeholder="Last Name" required>
                            </div>
                            <div class="form-group">
                                <label>Mobile No *</label>
                                <input type="tel" name="mobile_no" placeholder="Mobile No" maxlength="10" pattern="[0-9]{10}" title="Please enter exactly 10 digits" required>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <label>Password *</label>
                                <input type="password" name="password" placeholder="Password" required>
                            </div>
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="city" placeholder="City" required>
                            </div>
                            <div class="form-group">
                                <label>State *</label>
                                <input type="text" name="state" placeholder="State" required>
                            </div>
                            <div class="form-group">
                                <label>Zipcode *</label>
                                <input type="text" name="zipcode" placeholder="Zipcode" required>
                            </div>
                            <button type="submit" class="account-btn">CREATE ACCOUNT</button>
                            <p class="account-switch">
                                Already have an account? <a href="#" onclick="showTab('login'); return false;">Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- LOGGED IN - SHOW ACCOUNT DASHBOARD -->
            <div class="account-dashboard">
                <div class="account-welcome">
                    <h2>Welcome, <?php echo htmlspecialchars((isset($user_data['first_name']) ? $user_data['first_name'] : '') . ' ' . (isset($user_data['last_name']) ? $user_data['last_name'] : '')); ?>!</h2>
                    <p>Manage your orders here</p>
                </div>

                <div class="account-grid">
                    <!-- ORDERS SECTION -->
                    <div class="account-card orders-card-enhanced">
                        <div class="account-header orders-header-enhanced">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>My Orders</h3>
                            <p class="header-subtitle">Track and manage all your orders</p>
                        </div>
                        <div class="account-body">
                            <?php 
                            // Display success/error messages
                            if (isset($_GET['success']) && $_GET['success'] === 'return_requested'): 
                            ?>
                                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                                    <i class="fas fa-check-circle"></i> Your return/refund request has been submitted successfully. We will review it shortly.
                                </div>
                            <?php 
                            endif; 
                            if (isset($_GET['error'])): 
                                $error_messages = [
                                    'order_not_delivered' => 'Return/refund can only be requested for delivered orders.',
                                    'return_window_expired' => 'The return/refund window has expired. Returns must be requested within 7 days of delivery.',
                                    'return_exists' => 'A return/refund request already exists for this order.'
                                ];
                                $error_msg = $error_messages[$_GET['error']] ?? 'An error occurred.';
                            ?>
                                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (empty($orders)): ?>
                                <div class="empty-orders">
                                    <i class="fas fa-box-open"></i>
                                    <p>No orders yet</p>
                                    <a href="index.php" class="account-btn">Start Shopping</a>
                                </div>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($orders as $order): 
                                        $order_date = date('d M Y, h:i A', strtotime($order['created_at']));
                                        $payment_methods = [
                                            'razorpay' => 'Razorpay (UPI/Card/Wallet)',
                                            'upi' => 'UPI',
                                            'credit_card' => 'Credit Card',
                                            'bank_deposit' => 'Bank Deposit',
                                            'cod' => 'Cash On Delivery (COD)'
                                        ];
                                        $payment_method_display = $payment_methods[$order['payment_method']] ?? ucfirst($order['payment_method']);
                                        
                                        $order_status_colors = [
                                            'pending' => '#ffc107',
                                            'processing' => '#17a2b8',
                                            'shipped' => '#007bff',
                                            'delivered' => '#28a745',
                                            'cancelled' => '#dc3545'
                                        ];
                                        $order_status_color = $order_status_colors[$order['order_status']] ?? '#6c757d';
                                        
                                        $payment_status_colors = [
                                            'pending' => '#ffc107',
                                            'paid' => '#28a745',
                                            'failed' => '#dc3545',
                                            'refunded' => '#6c757d'
                                        ];
                                        $payment_status_color = $payment_status_colors[$order['payment_status']] ?? '#6c757d';
                                    ?>
                                        <div class="order-item">
                                            <div class="order-header">
                                                <div class="order-info">
                                                    <h4>Order #<?= htmlspecialchars($order['order_number']) ?></h4>
                                                    <p class="order-date"><?= $order_date ?></p>
                                                </div>
                                                <div class="order-status-badges">
                                                    <span class="status-badge" style="background: <?= $order_status_color ?>;">
                                                        <?= ucfirst($order['order_status']) ?>
                                                    </span>
                                                    <span class="status-badge" style="background: <?= $payment_status_color ?>;">
                                                        <?= ucfirst($order['payment_status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="order-details">
                                                <div class="order-detail-row">
                                                    <span class="detail-label">Payment Method:</span>
                                                    <span class="detail-value"><?= htmlspecialchars($payment_method_display) ?></span>
                                                </div>
                                                <div class="order-detail-row">
                                                    <span class="detail-label">Total Amount:</span>
                                                    <span class="detail-value amount">₹<?= number_format($order['total_amount'], 2) ?></span>
                                                </div>
                                                <?php 
                                                // Check if return/refund request exists
                                                $return_request = isset($return_requests[$order['id']]) ? $return_requests[$order['id']] : null;
                                                if ($return_request && isset($return_request['status']) && !empty($return_request['status'])): 
                                                    $return_status_colors = [
                                                        'pending' => '#ffc107',
                                                        'approved' => '#17a2b8',
                                                        'product_received' => '#007bff',
                                                        'refund_processing' => '#6f42c1',
                                                        'refunded' => '#28a745',
                                                        'rejected' => '#dc3545'
                                                    ];
                                                    $return_status_color = $return_status_colors[$return_request['status']] ?? '#6c757d';
                                                    $return_status_labels = [
                                                        'pending' => 'Pending Review',
                                                        'approved' => 'Approved',
                                                        'product_received' => 'Product Received',
                                                        'refund_processing' => 'Refund Processing',
                                                        'refunded' => 'Refunded',
                                                        'rejected' => 'Rejected'
                                                    ];
                                                ?>
                                                <div class="order-detail-row">
                                                    <span class="detail-label">Return Status:</span>
                                                    <span class="detail-value">
                                                        <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?= $return_status_color ?>; color: #fff;">
                                                            <?= $return_status_labels[$return_request['status']] ?? 'Unknown' ?>
                                                        </span>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="order-actions">
                                                <?php 
                                                $order_status_lower = strtolower(trim($order['order_status']));
                                                $is_delivered = ($order_status_lower === 'delivered');
                                                ?>
                                                
                                                <?php if ($order['order_status'] !== 'cancelled' && !$is_delivered): ?>
                                                <a href="track_order.php?id=<?= $order['id'] ?>" class="order-action-btn track-order">
                                                    <i class="fas fa-truck"></i> Track Order
                                                </a>
                                                <?php elseif ($is_delivered): ?>
                                                <button class="order-action-btn track-order" disabled style="background: #e0e0e0 !important; color: #999 !important; cursor: not-allowed !important; opacity: 0.6 !important; pointer-events: none !important; border: none !important;">
                                                    <i class="fas fa-truck"></i> Track Order
                                                </button>
                                                <?php endif; ?>
                                                <?php 
                                                // Show return/refund button for delivered orders within 7 days
                                                if ($order['order_status'] === 'delivered' && !$return_request): 
                                                    $delivery_date = strtotime($order['updated_at']);
                                                    $current_date = time();
                                                    $days_since_delivery = floor(($current_date - $delivery_date) / (60 * 60 * 24));
                                                    if ($days_since_delivery <= 7):
                                                ?>
                                                <a href="request_return_refund.php?id=<?= $order['id'] ?>" class="order-action-btn return-refund" style="background: linear-gradient(135deg, #e91e63, #c2185b);">
                                                    <i class="fas fa-undo"></i> Return/Refund
                                                </a>
                                                <?php 
                                                    endif;
                                                endif; 
                                                // Show return status link if request exists
                                                if ($return_request):
                                                ?>
                                                <a href="view_return_status.php?id=<?= $return_request['id'] ?>" class="order-action-btn return-status" style="background: linear-gradient(135deg, #e91e63, #c2185b);">
                                                    <i class="fas fa-info-circle"></i> Return Status
                                                </a>
                                                <?php endif; ?>
                                                <?php if ($is_delivered): ?>
                                                <a href="javascript:void(0)" onclick="downloadReceipt(<?= $order['id'] ?>)" class="order-action-btn view-receipt">
                                                    <i class="fas fa-download"></i> Download Receipt
                                                </a>
                                                <?php endif; ?>
                                                <a href="view_receipt.php?id=<?= $order['id'] ?>" class="order-action-btn view-receipt" target="_blank">
                                                    <i class="fas fa-receipt"></i> View Receipt
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
/* HERO BANNER - Reusing existing styles */
.embroidery-hero-banner {
    padding: 80px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.embroidery-hero-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
    min-width: 100%;
    min-height: 100%;
}

.embroidery-hero-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.embroidery-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
}

.embroidery-hero-title {
    font-size: 72px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 4px;
    margin: 0 0 20px 0;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
}

.breadcrumb {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 16px;
    color: #fff;
}

.breadcrumb a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #2fc7b4;
}

.breadcrumb-separator {
    color: #fff;
    margin: 0 4px;
}

.breadcrumb-current {
    color: #2fc7b4;
    font-weight: 600;
}

/* ACCOUNT SECTION */
.account-section {
    padding: 60px 20px;
    background: #f5f7fa;
    min-height: 60vh;
}

.account-container {
    max-width: 900px;
    margin: 0 auto;
}

.account-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.account-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.12);
}

.account-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    color: #fff;
    text-align: center;
    position: relative;
}

.account-header::before,
.account-header::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 80px;
    height: 2px;
    background: #fff;
}

.account-header::before {
    left: 30px;
}

.account-header::after {
    right: 30px;
}

.account-header h2,
.account-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.account-body {
    padding: 40px;
}

/* TABS */
.account-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.tab-btn {
    flex: 1;
    padding: 15px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #2fa76b;
    border-bottom-color: #2fa76b;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* FORM STYLES */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2b2b2b;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #2fc7b4;
    box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
}

.form-options {
    text-align: right;
    margin-bottom: 20px;
}

.forgot-link {
    color: #2fc7b4;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-link:hover {
    color: #2fa76b;
    text-decoration: underline;
}

.account-btn {
    width: 100%;
    padding: 15px;
    background: #2b2b2b;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.account-btn:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.account-switch {
    text-align: center;
    color: #666;
    font-size: 14px;
    margin: 20px 0;
}

.account-switch a {
    color: #2fc7b4;
    text-decoration: none;
    font-weight: 600;
}

.account-switch a:hover {
    text-decoration: underline;
}

/* SOCIAL LOGIN */
.social-login {
    text-align: center;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #e0e0e0;
}

.social-login p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 14px;
}

.social-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.social-btn {
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 8px;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.social-btn.facebook {
    background: #1877f2;
    color: #fff;
}

.social-btn.google {
    background: #ea4335;
    color: #fff;
}

.social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* ACCOUNT DASHBOARD */
.account-welcome {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    border-radius: 12px;
}

.account-welcome h2 {
    font-size: 32px;
    color: #2fa76b;
    margin: 0 0 10px 0;
}

.account-welcome p {
    color: #666;
    margin: 0;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.empty-orders {
    text-align: center;
    padding: 60px 30px;
    color: #999;
    background: linear-gradient(135deg, #f8fffe 0%, #ffffff 100%);
    border-radius: 16px;
    border: 2px dashed #e0f2ef;
}

.empty-orders i {
    font-size: 80px;
    margin-bottom: 25px;
    color: #2fc7b4;
    opacity: 0.6;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.empty-orders p {
    margin: 0 0 30px 0;
    font-size: 18px;
    color: #666;
    font-weight: 500;
}

/* ORDERS LIST */
/* Enhanced Orders Card */
.orders-card-enhanced {
    background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.orders-header-enhanced {
    background: linear-gradient(135deg, #2fc7b4 0%, #2fa76b 100%);
    padding: 35px 40px;
    position: relative;
    overflow: hidden;
}

.orders-header-enhanced::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.orders-header-enhanced::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
}

.header-icon-wrapper {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    backdrop-filter: blur(10px);
}

.header-icon-wrapper i {
    font-size: 28px;
    color: #fff;
}

.orders-header-enhanced h3 {
    font-size: 28px;
    margin-bottom: 8px;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-subtitle {
    color: rgba(255, 255, 255, 0.95);
    font-size: 14px;
    margin: 0;
    font-weight: 400;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    padding: 30px;
}

.order-item {
    border: none;
    border-radius: 16px;
    padding: 0;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
}

.order-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #2fc7b4, #2fa76b);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.order-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(47, 199, 180, 0.2);
}

.order-item:hover::before {
    opacity: 1;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
    padding: 25px 25px 20px;
    background: linear-gradient(135deg, #f8fffe 0%, #ffffff 100%);
    border-bottom: 2px solid #f0f9f7;
}

.order-info h4 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.5px;
}

.order-date {
    margin: 0;
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.order-date::before {
    content: '📅';
    font-size: 14px;
}

.order-status-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: transform 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

.order-details {
    margin-bottom: 20px;
    padding: 20px 25px;
    background: linear-gradient(135deg, #f8fffe 0%, #f0f9f7 100%);
    border-radius: 12px;
    margin-left: 25px;
    margin-right: 25px;
    border: 1px solid #e8f5f3;
}

.order-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(47, 199, 180, 0.1);
}

.order-detail-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #555;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.detail-value {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 15px;
}

.detail-value.amount {
    font-size: 22px;
    color: #2fa76b;
    font-weight: 800;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.order-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    padding: 0 25px 25px;
}

.order-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 12px rgba(47, 199, 180, 0.25);
    position: relative;
    overflow: hidden;
}

.order-action-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.order-action-btn:hover::before {
    width: 300px;
    height: 300px;
}

.order-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(47, 199, 180, 0.4);
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
}

.order-action-btn i {
    position: relative;
    z-index: 1;
}

.order-action-btn span {
    position: relative;
    z-index: 1;
}

.order-action-btn.track-order {
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    box-shadow: 0 4px 12px rgba(47, 199, 180, 0.25);
}

.order-action-btn.track-order:hover {
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    box-shadow: 0 8px 20px rgba(47, 167, 107, 0.4);
}

.order-action-btn.view-receipt {
    background: linear-gradient(135deg, #e91e63, #c2185b);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.25);
}

.order-action-btn.view-receipt:hover {
    background: linear-gradient(135deg, #c2185b, #e91e63);
    box-shadow: 0 8px 20px rgba(233, 30, 99, 0.4);
}

.order-action-btn.return-refund,
.order-action-btn.return-status {
    background: linear-gradient(135deg, #e91e63, #c2185b);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.25);
}

.order-action-btn.return-refund:hover,
.order-action-btn.return-status:hover {
    background: linear-gradient(135deg, #c2185b, #e91e63);
    box-shadow: 0 8px 20px rgba(233, 30, 99, 0.4);
}

.account-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    background: #dc3545;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.account-action-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .embroidery-hero-title {
        font-size: 48px;
    }

    .account-body {
        padding: 25px 20px;
    }

    .account-grid {
        grid-template-columns: 1fr;
    }

    .account-header::before,
    .account-header::after {
        display: none;
    }
}


</style>

<script>
// Tab switching
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName + 'Tab').classList.add('active');
    event.target.classList.add('active');
}

// Handle account login
function handleAccountLogin(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    fetch('login-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        } else {
            alert(data.message || 'Login failed');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        alert('Something went wrong. Please try again.');
    });
}

// Handle account register
function handleAccountRegister(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    fetch('register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Registration successful! Please login.');
            showTab('login');
        } else {
            alert(data.message || 'Registration failed');
        }
    })
    .catch(error => {
        console.error('Register error:', error);
        alert('Something went wrong. Please try again.');
    });
}

// Update profile
function updateProfile(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';
    
    fetch('update-profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(data => {
        // Always re-enable button after response
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.status === 'success') {
            // Show beautiful success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🎉 Success!',
                    html: '<div style="font-size: 18px; color: #2fa76b; font-weight: 600;">Profile updated successfully! ✨</div><div style="margin-top: 10px; font-size: 14px; color: #777;">Your changes have been saved.</div>',
                    icon: 'success',
                    confirmButtonText: 'Awesome! 🚀',
                    confirmButtonColor: '#2fa76b',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-popup-custom',
                        title: 'swal2-title-custom',
                        htmlContainer: 'swal2-html-container-custom',
                        confirmButton: 'swal2-confirm-custom'
                    }
                });
            } else {
                alert('Profile updated successfully!');
            }
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Show beautiful error message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '❌ Error!',
                    html: '<div style="font-size: 16px; color: #dc3545;">' + (data.message || 'Failed to update profile') + '</div>',
                    icon: 'error',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#dc3545',
                    customClass: {
                        popup: 'swal2-popup-custom',
                        title: 'swal2-title-custom',
                        htmlContainer: 'swal2-html-container-custom',
                        confirmButton: 'swal2-confirm-custom'
                    }
                });
            } else {
                alert(data.message || 'Failed to update profile');
            }
        }
    })
    .catch(error => {
        console.error('Update profile error:', error);
        // Always re-enable button on error
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '❌ Error!',
                html: '<div style="font-size: 16px; color: #dc3545;">Something went wrong. Please try again.</div>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'swal2-popup-custom',
                    title: 'swal2-title-custom',
                    htmlContainer: 'swal2-html-container-custom',
                    confirmButton: 'swal2-confirm-custom'
                }
            });
        } else {
            alert('Something went wrong. Please try again.');
        }
    });
}

// Video autoplay
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('.embroidery-hero-video');
    if (video) {
        video.play().catch(function(error) {
            setTimeout(function() {
                video.play().catch(function(err) {
                    console.log('Video play failed:', err);
                });
            }, 100);
        });
    }
});
</script>

