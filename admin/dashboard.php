<?php
session_start();

// Strong cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Get comprehensive stats
$products_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
$products = mysqli_fetch_assoc($products_count);

// Check if status column exists before querying
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'status'");
$has_status_column = mysqli_num_rows($columns_check) > 0;

if ($has_status_column) {
    $active_products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $active_products_data = mysqli_fetch_assoc($active_products);
} else {
    // If status column doesn't exist, use total products as active
    $active_products_data = ['count' => $products['count']];
}

$subscribers_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM subscribers");
$subscribers = mysqli_fetch_assoc($subscribers_count);

// Orders stats
$total_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$total_orders_data = mysqli_fetch_assoc($total_orders);

$paid_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'paid'");
$paid_orders_data = mysqli_fetch_assoc($paid_orders);

$pending_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
$pending_orders_data = mysqli_fetch_assoc($pending_orders);

$delivered_orders = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status = 'delivered'");
$delivered_orders_data = mysqli_fetch_assoc($delivered_orders);

// Revenue stats
$total_revenue = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$revenue_data = mysqli_fetch_assoc($total_revenue);
$revenue = $revenue_data['total'] ? $revenue_data['total'] : 0;

$monthly_revenue = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$monthly_revenue_data = mysqli_fetch_assoc($monthly_revenue);
$monthly_rev = $monthly_revenue_data['total'] ? $monthly_revenue_data['total'] : 0;

// Customers stats
$total_customers = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$customers_data = mysqli_fetch_assoc($total_customers);

// Returns/Refunds stats
$total_returns = mysqli_query($conn, "SELECT COUNT(*) as count FROM return_refund_requests");
$returns_data = mysqli_fetch_assoc($total_returns);

$pending_returns = mysqli_query($conn, "SELECT COUNT(*) as count FROM return_refund_requests WHERE status = 'pending'");
$pending_returns_data = mysqli_fetch_assoc($pending_returns);

// Gift Cards stats - with comprehensive error handling
$gift_cards_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'gift_cards'");
if ($gift_cards_table_check && mysqli_num_rows($gift_cards_table_check) > 0) {
    $total_gift_cards = mysqli_query($conn, "SELECT COUNT(*) as count FROM gift_cards");
    $gift_cards_data = $total_gift_cards ? mysqli_fetch_assoc($total_gift_cards) : ['count' => 0];
    
    $active_gift_cards = mysqli_query($conn, "SELECT COUNT(*) as count FROM gift_cards WHERE status = 'active'");
    $active_gift_cards_data = $active_gift_cards ? mysqli_fetch_assoc($active_gift_cards) : ['count' => 0];
    
    // Use 'amount' column instead of 'balance'
    $gift_cards_value = mysqli_query($conn, "SELECT SUM(amount) as total FROM gift_cards WHERE status = 'active'");
    $gift_cards_value_data = $gift_cards_value ? mysqli_fetch_assoc($gift_cards_value) : ['total' => 0];
    $total_gift_card_value = $gift_cards_value_data['total'] ? $gift_cards_value_data['total'] : 0;
} else {
    $gift_cards_data = ['count' => 0];
    $active_gift_cards_data = ['count' => 0];
    $total_gift_card_value = 0;
}

// Subcategories stats - with comprehensive error handling
$subcategories_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'subcategories'");
if ($subcategories_table_check && mysqli_num_rows($subcategories_table_check) > 0) {
    $total_subcategories = mysqli_query($conn, "SELECT COUNT(*) as count FROM subcategories");
    $subcategories_data = $total_subcategories ? mysqli_fetch_assoc($total_subcategories) : ['count' => 0];
} else {
    $subcategories_data = ['count' => 0];
}

// Reviews stats - with comprehensive error handling
$reviews_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'reviews'");
if ($reviews_table_check && mysqli_num_rows($reviews_table_check) > 0) {
    $total_reviews = mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews");
    $reviews_data = $total_reviews ? mysqli_fetch_assoc($total_reviews) : ['count' => 0];
    
    $pending_reviews = mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");
    $pending_reviews_data = $pending_reviews ? mysqli_fetch_assoc($pending_reviews) : ['count' => 0];
} else {
    $reviews_data = ['count' => 0];
    $pending_reviews_data = ['count' => 0];
}

// Tutorials stats - with comprehensive error handling
$tutorials_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tutorials'");
if ($tutorials_table_check && mysqli_num_rows($tutorials_table_check) > 0) {
    $total_tutorials = mysqli_query($conn, "SELECT COUNT(*) as count FROM tutorials");
    $tutorials_data = $total_tutorials ? mysqli_fetch_assoc($total_tutorials) : ['count' => 0];
    
    $published_tutorials = mysqli_query($conn, "SELECT COUNT(*) as count FROM tutorials WHERE status = 'active'");
    $published_tutorials_data = $published_tutorials ? mysqli_fetch_assoc($published_tutorials) : ['count' => 0];
} else {
    $tutorials_data = ['count' => 0];
    $published_tutorials_data = ['count' => 0];
}

// Blog Posts stats - with comprehensive error handling
$blog_posts_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'blog_posts'");
if ($blog_posts_table_check && mysqli_num_rows($blog_posts_table_check) > 0) {
    $total_blog_posts = mysqli_query($conn, "SELECT COUNT(*) as count FROM blog_posts");
    $blog_posts_data = $total_blog_posts ? mysqli_fetch_assoc($total_blog_posts) : ['count' => 0];
    
    $published_blog_posts = mysqli_query($conn, "SELECT COUNT(*) as count FROM blog_posts WHERE status = 'active'");
    $published_blog_posts_data = $published_blog_posts ? mysqli_fetch_assoc($published_blog_posts) : ['count' => 0];
} else {
    $blog_posts_data = ['count' => 0];
    $published_blog_posts_data = ['count' => 0];
}

// Contact Messages stats - with comprehensive error handling
$contact_messages_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
if ($contact_messages_table_check && mysqli_num_rows($contact_messages_table_check) > 0) {
    $total_contact_messages = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages");
    $contact_messages_data = $total_contact_messages ? mysqli_fetch_assoc($total_contact_messages) : ['count' => 0];
    
    $unread_contact_messages = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
    $unread_contact_messages_data = $unread_contact_messages ? mysqli_fetch_assoc($unread_contact_messages) : ['count' => 0];
} else {
    $contact_messages_data = ['count' => 0];
    $unread_contact_messages_data = ['count' => 0];
}

// Recent orders - check if created_at column exists
$orders_created_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'created_at'");
$orders_has_created_at = mysqli_num_rows($orders_created_check) > 0;

if ($orders_has_created_at) {
    $recent_orders = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
} else {
    $recent_orders = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5");
}
$recent_orders_list = [];
if ($recent_orders) {
    while ($order = mysqli_fetch_assoc($recent_orders)) {
        $recent_orders_list[] = $order;
    }
}

// Recent products - check if created_at column exists
$created_at_check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'created_at'");
$has_created_at = mysqli_num_rows($created_at_check) > 0;

if ($has_created_at) {
    $recent_products = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
} else {
    $recent_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 5");
}
$recent_products_list = [];
if ($recent_products) {
    while ($product = mysqli_fetch_assoc($recent_products)) {
        $recent_products_list[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Dashboard - Craft Royale</title>
    <!-- Favicon -->
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- SIDEBAR -->
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p class="welcome-text">Welcome back, <?php echo $_SESSION['admin']; ?>! 👋</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- STATS CARDS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b);">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Products</h3>
                            <p class="stat-number"><?php echo $products['count']; ?></p>
                            <span class="stat-label"><?php echo $active_products_data['count']; ?> active products</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff6b9d, #ff8fab);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Orders</h3>
                            <p class="stat-number"><?php echo $total_orders_data['count']; ?></p>
                            <span class="stat-label"><?php echo $delivered_orders_data['count']; ?> delivered</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #9c27b0, #673ab7);">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Revenue</h3>
                            <p class="stat-number">₹<?php echo number_format($revenue, 0); ?></p>
                            <span class="stat-label">₹<?php echo number_format($monthly_rev, 0); ?> this month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Customers</h3>
                            <p class="stat-number"><?php echo $customers_data['count']; ?></p>
                            <span class="stat-label">Registered users</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #21CBF3);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Subscribers</h3>
                            <p class="stat-number"><?php echo $subscribers['count']; ?></p>
                            <span class="stat-label">Email subscribers</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e91e63, #c2185b);">
                            <i class="fas fa-undo"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Returns</h3>
                            <p class="stat-number"><?php echo $returns_data['count']; ?></p>
                            <span class="stat-label"><?php echo $pending_returns_data['count']; ?> pending</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f44336, #e91e63);">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Gift Cards</h3>
                            <p class="stat-number"><?php echo $gift_cards_data['count']; ?></p>
                            <span class="stat-label"><?php echo $active_gift_cards_data['count']; ?> active (₹<?php echo number_format($total_gift_card_value, 0); ?>)</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #00bcd4, #0097a7);">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Subcategories</h3>
                            <p class="stat-number"><?php echo $subcategories_data['count']; ?></p>
                            <span class="stat-label">Product subcategories</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4caf50, #388e3c);">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Reviews</h3>
                            <p class="stat-number"><?php echo $reviews_data['count']; ?></p>
                            <span class="stat-label"><?php echo $pending_reviews_data['count']; ?> pending approval</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff5722, #f4511e);">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tutorials</h3>
                            <p class="stat-number"><?php echo $tutorials_data['count']; ?></p>
                            <span class="stat-label"><?php echo $published_tutorials_data['count']; ?> active</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #607d8b, #455a64);">
                            <i class="fas fa-blog"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Blog Posts</h3>
                            <p class="stat-number"><?php echo $blog_posts_data['count']; ?></p>
                            <span class="stat-label"><?php echo $published_blog_posts_data['count']; ?> active</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3f51b5, #303f9f);">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Contact Messages</h3>
                            <p class="stat-number"><?php echo $contact_messages_data['count']; ?></p>
                            <span class="stat-label"><?php echo $unread_contact_messages_data['count']; ?> unread</span>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS & RECENT DATA -->
                <div class="content-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="add_product.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Add New Product</span>
                            </a>
                            <a href="manage_products.php" class="action-btn">
                                <i class="fas fa-edit"></i>
                                <span>Manage Products</span>
                            </a>
                            <a href="manage_orders.php" class="action-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span>View Orders</span>
                            </a>
                            <a href="manage_returns.php" class="action-btn">
                                <i class="fas fa-undo"></i>
                                <span>Manage Returns</span>
                            </a>
                            <a href="subscribers.php" class="action-btn">
                                <i class="fas fa-envelope"></i>
                                <span>View Subscribers</span>
                            </a>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_orders_list) > 0): ?>
                                <?php foreach ($recent_orders_list as $order): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 152, 0, 0.2)); color: #ff9800;">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong>Order #<?php echo htmlspecialchars($order['order_number']); ?></strong> - ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                            <span class="activity-time"><?php echo isset($order['created_at']) ? date('M d, Y', strtotime($order['created_at'])) : 'Recently'; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>No orders yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-box"></i> Recent Products</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_products_list) > 0): ?>
                                <?php foreach ($recent_products_list as $product): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon" style="background: linear-gradient(135deg, rgba(47, 199, 180, 0.2), rgba(47, 167, 107, 0.2)); color: #2fa76b;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><strong><?php echo htmlspecialchars(isset($product['product_name']) ? $product['product_name'] : $product['name']); ?></strong></p>
                                            <span class="activity-time">₹<?php echo number_format($product['price'], 2); ?> - <?php echo isset($product['created_at']) ? date('M d, Y', strtotime($product['created_at'])) : 'Recently'; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-box"></i>
                                    <p>No products yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
