<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$return_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($return_id <= 0) {
    header('Location: account.php');
    exit;
}

// Fetch return/refund request details
$return_query = "SELECT r.*, o.order_number, o.total_amount, o.created_at as order_date, o.updated_at as delivery_date 
                 FROM return_refund_requests r 
                 JOIN orders o ON r.order_id = o.id 
                 WHERE r.id = $return_id AND r.user_id = $user_id";
$return_result = mysqli_query($conn, $return_query);
$return_request = mysqli_fetch_assoc($return_result);

if (!$return_request) {
    header('Location: account.php');
    exit;
}

// Format dates
$request_date = date('d M Y, h:i A', strtotime($return_request['created_at']));
$order_date = date('d M Y, h:i A', strtotime($return_request['order_date']));
$delivery_date = date('d M Y, h:i A', strtotime($return_request['delivery_date']));
$product_received_date = $return_request['product_received_at'] ? date('d M Y, h:i A', strtotime($return_request['product_received_at'])) : null;
$refund_processed_date = $return_request['refund_processed_at'] ? date('d M Y, h:i A', strtotime($return_request['refund_processed_at'])) : null;

// Reason labels
$reason_labels = [
    'colour_wrong' => 'Colour is wrong',
    'item_wrong' => 'Item is wrong',
    'defective' => 'Defective',
    'no_good_quality' => 'No good quality',
    'other' => 'Other reason'
];

$status_labels = [
    'pending' => 'Pending Review',
    'approved' => 'Approved - Please send the product back',
    'product_received' => 'Product Received - Refund processing',
    'refund_processing' => 'Refund Processing',
    'refunded' => 'Refunded',
    'rejected' => 'Rejected'
];

$status_colors = [
    'pending' => '#ffc107',
    'approved' => '#17a2b8',
    'product_received' => '#007bff',
    'refund_processing' => '#6f42c1',
    'refunded' => '#28a745',
    'rejected' => '#dc3545'
];
?>

<style>
    .return-status-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .return-status-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e5e5;
    }
    
    .return-status-header h1 {
        color: #2b2b2b;
        font-size: 28px;
        margin-bottom: 10px;
    }
    
    .status-badge-large {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        color: #fff;
        margin-top: 10px;
    }
    
    .info-section {
        margin-bottom: 30px;
    }
    
    .info-section h2 {
        color: #2b2b2b;
        font-size: 20px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e5e5;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .info-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .info-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .info-value {
        font-size: 16px;
        color: #2b2b2b;
        font-weight: 600;
    }
    
    .image-preview {
        margin-top: 15px;
        text-align: center;
    }
    
    .image-preview img {
        max-width: 100%;
        max-height: 400px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .admin-notes-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .admin-notes-box h3 {
        color: #856404;
        font-size: 16px;
        margin-bottom: 10px;
    }
    
    .admin-notes-box p {
        color: #856404;
        margin: 0;
        line-height: 1.6;
    }
    
    .timeline {
        margin-top: 30px;
    }
    
    .timeline-item {
        display: flex;
        margin-bottom: 20px;
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 40px;
        bottom: -20px;
        width: 2px;
        background: #e5e5e5;
    }
    
    .timeline-item:last-child::before {
        display: none;
    }
    
    .timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e5e5;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
        font-size: 14px;
    }
    
    .timeline-item.active .timeline-icon {
        background: #2fc7b4;
        color: #fff;
    }
    
    .timeline-item.completed .timeline-icon {
        background: #28a745;
        color: #fff;
    }
    
    .timeline-content {
        flex: 1;
    }
    
    .timeline-title {
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 5px;
    }
    
    .timeline-date {
        font-size: 12px;
        color: #666;
    }
    
    .back-btn {
        display: inline-block;
        margin-top: 30px;
        padding: 12px 24px;
        background: #f5f5f5;
        color: #2b2b2b;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
    }
    
    .back-btn:hover {
        background: #e5e5e5;
    }
</style>

<div class="return-status-container">
    <div class="return-status-header">
        <h1>Return/Refund Status</h1>
        <div class="status-badge-large" style="background: <?= $status_colors[$return_request['status']] ?>;">
            <?= $status_labels[$return_request['status']] ?>
        </div>
    </div>
    
    <div class="info-section">
        <h2>Order Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Order Number</div>
                <div class="info-value"><?= htmlspecialchars($return_request['order_number']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Order Date</div>
                <div class="info-value"><?= $order_date ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Delivery Date</div>
                <div class="info-value"><?= $delivery_date ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Amount</div>
                <div class="info-value" style="color: #2fc7b4;">₹<?= number_format($return_request['total_amount'], 2) ?></div>
            </div>
        </div>
    </div>
    
    <div class="info-section">
        <h2>Return Request Details</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Request Date</div>
                <div class="info-value"><?= $request_date ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Return Reason</div>
                <div class="info-value"><?= htmlspecialchars($reason_labels[$return_request['return_reason']]) ?></div>
            </div>
            <?php if ($return_request['return_reason'] === 'other' && $return_request['other_reason']): ?>
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Problem Description</div>
                <div class="info-value"><?= htmlspecialchars($return_request['other_reason']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($return_request['image']): ?>
        <div class="image-preview">
            <img src="uploads/returns/<?= htmlspecialchars($return_request['image']) ?>" alt="Return Image">
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($return_request['admin_notes']): ?>
    <div class="admin-notes-box">
        <h3><i class="fas fa-info-circle"></i> Admin Notes</h3>
        <p><?= nl2br(htmlspecialchars($return_request['admin_notes'])) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="info-section">
        <h2>Status Timeline</h2>
        <div class="timeline">
            <div class="timeline-item <?= in_array($return_request['status'], ['pending', 'approved', 'product_received', 'refund_processing', 'refunded']) ? 'completed' : '' ?> <?= $return_request['status'] === 'pending' ? 'active' : '' ?>">
                <div class="timeline-icon">1</div>
                <div class="timeline-content">
                    <div class="timeline-title">Request Submitted</div>
                    <div class="timeline-date"><?= $request_date ?></div>
                </div>
            </div>
            
            <?php if (in_array($return_request['status'], ['approved', 'product_received', 'refund_processing', 'refunded'])): ?>
            <div class="timeline-item completed <?= $return_request['status'] === 'approved' ? 'active' : '' ?>">
                <div class="timeline-icon">2</div>
                <div class="timeline-content">
                    <div class="timeline-title">Request Approved</div>
                    <div class="timeline-date">Please send the product back</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($return_request['status'], ['product_received', 'refund_processing', 'refunded'])): ?>
            <div class="timeline-item completed <?= $return_request['status'] === 'product_received' ? 'active' : '' ?>">
                <div class="timeline-icon">3</div>
                <div class="timeline-content">
                    <div class="timeline-title">Product Received</div>
                    <div class="timeline-date"><?= $product_received_date ?: 'Processing...' ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($return_request['status'], ['refund_processing', 'refunded'])): ?>
            <div class="timeline-item completed <?= $return_request['status'] === 'refund_processing' ? 'active' : '' ?>">
                <div class="timeline-icon">4</div>
                <div class="timeline-content">
                    <div class="timeline-title">Refund Processing</div>
                    <div class="timeline-date">Refund will be processed within 48 hours</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($return_request['status'] === 'refunded'): ?>
            <div class="timeline-item completed active">
                <div class="timeline-icon">✓</div>
                <div class="timeline-content">
                    <div class="timeline-title">Refund Completed</div>
                    <div class="timeline-date"><?= $refund_processed_date ?: 'Completed' ?></div>
                    <?php if ($return_request['refund_amount']): ?>
                    <div style="margin-top: 5px; color: #28a745; font-weight: 600;">
                        Refund Amount: ₹<?= number_format($return_request['refund_amount'], 2) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($return_request['status'] === 'rejected'): ?>
            <div class="timeline-item active">
                <div class="timeline-icon" style="background: #dc3545;">✗</div>
                <div class="timeline-content">
                    <div class="timeline-title">Request Rejected</div>
                    <div class="timeline-date">Your return request has been rejected</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="account.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to My Orders
    </a>
</div>

<?php include 'includes/footer.php'; ?>
