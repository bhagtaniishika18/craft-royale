<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: account.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: account.php');
    exit;
}

// Prevent tracking for cancelled orders
if ($order['order_status'] === 'cancelled') {
    header('Location: account.php');
    exit;
}

// Format date
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

// Get current status index
$status_order = ['pending', 'processing', 'shipped', 'delivered'];
$current_status_index = array_search($order['order_status'], $status_order);
if ($current_status_index === false) {
    $current_status_index = 0;
}
?>
<style>
    .track-order-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #fff5f5 0%, #f0f7ff 100%);
        padding: 40px 20px;
    }
    
    .track-order-container {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        padding: 40px;
    }
    
    .track-order-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 3px solid #e91e63;
    }
    
    .track-order-title {
        font-size: 32px;
        font-weight: 800;
        color: #e91e63;
        margin: 0 0 10px 0;
    }
    
    .track-order-id {
        font-size: 20px;
        font-weight: 600;
        color: #2b2b2b;
        margin-top: 10px;
    }
    
    .order-info-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 12px;
    }
    
    .order-info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .order-info-row:last-child {
        border-bottom: none;
    }
    
    .order-info-label {
        font-weight: 600;
        color: #666;
    }
    
    .order-info-value {
        color: #2b2b2b;
        font-weight: 600;
    }
    
    .tracking-timeline {
        margin: 40px 0;
    }
    
    .timeline-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        position: relative;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 60px;
        width: 2px;
        height: calc(100% + 10px);
        background: #e0e0e0;
    }
    
    .timeline-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        background: #f0f0f0;
        color: #999;
        border: 3px solid #e0e0e0;
        position: relative;
        z-index: 1;
    }
    
    .timeline-item.active .timeline-icon {
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        color: #fff;
        border-color: #2fa76b;
        box-shadow: 0 4px 15px rgba(47, 167, 107, 0.3);
    }
    
    .timeline-item.completed .timeline-icon {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }
    
    .timeline-content {
        flex: 1;
        padding-top: 5px;
    }
    
    .timeline-title {
        font-size: 18px;
        font-weight: 700;
        color: #2b2b2b;
        margin-bottom: 5px;
    }
    
    .timeline-item.active .timeline-title {
        color: #2fa76b;
    }
    
    .timeline-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
    }
    
    .timeline-date {
        font-size: 12px;
        color: #999;
    }
    
    .track-order-actions {
        margin-top: 40px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .track-order-btn {
        padding: 14px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .track-order-btn-primary {
        background: linear-gradient(135deg, #e91e63, #c2185b);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
    }
    
    .track-order-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4);
    }
    
    .track-order-btn-secondary {
        background: #f5f5f5;
        color: #2b2b2b;
        border: 2px solid #e5e5e5;
    }
    
    .track-order-btn-secondary:hover {
        background: #e5e5e5;
    }
    
    @media (max-width: 768px) {
        .track-order-container {
            padding: 25px;
        }
        
        .track-order-title {
            font-size: 24px;
        }
        
        .order-info-row {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>

<div class="track-order-page">
    <div class="track-order-container">
        <div class="track-order-header">
            <h1 class="track-order-title">Track Your Order</h1>
            <div class="track-order-id">Order #<?= htmlspecialchars($order['order_number']) ?></div>
        </div>
        
        <div class="order-info-section">
            <div class="order-info-row">
                <span class="order-info-label">Order Date:</span>
                <span class="order-info-value"><?= $order_date ?></span>
            </div>
            <div class="order-info-row">
                <span class="order-info-label">Payment Status:</span>
                <span class="order-info-value">
                    <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            background: #d4edda; color: #155724;
                        <?php else: ?>
                            background: #fff3cd; color: #856404;
                        <?php endif; ?>">
                        <?= ucfirst($order['payment_status']) ?>
                    </span>
                </span>
            </div>
            <div class="order-info-row">
                <span class="order-info-label">Total Amount:</span>
                <span class="order-info-value" style="color: #e91e63; font-size: 18px;">₹<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>
        
        <div class="tracking-timeline">
            <h2 style="margin-bottom: 30px; color: #2b2b2b; font-size: 24px; text-align: center;">Order Status</h2>
            
            <?php 
            $statuses = ['pending', 'processing', 'shipped', 'delivered'];
            foreach ($statuses as $index => $status): 
                $detail = $status_details[$status];
                $is_active = $detail['active'];
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
                        <?php if ($is_current || $is_completed): ?>
                            <div class="timeline-date">Updated: <?= $updated_date ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="track-order-actions">
            <a href="view_receipt.php?id=<?= $order['id'] ?>" class="track-order-btn track-order-btn-primary">
                <span>📄</span> View Receipt
            </a>
            <a href="account.php" class="track-order-btn track-order-btn-secondary">
                <span>←</span> Back to Orders
            </a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
