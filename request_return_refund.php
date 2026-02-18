<?php
session_start();
include "includes/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: account.php');
    exit;
}

// Fetch order details
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: account.php');
    exit;
}

// Check if order is delivered
if ($order['order_status'] !== 'delivered') {
    header('Location: account.php?error=order_not_delivered');
    exit;
}

// Check if return/refund request already exists
$existing_request_query = "SELECT * FROM return_refund_requests WHERE order_id = $order_id";
$existing_request_result = mysqli_query($conn, $existing_request_query);
$existing_request = mysqli_fetch_assoc($existing_request_result);

if ($existing_request) {
    header('Location: account.php?error=return_exists');
    exit;
}

// Check if within 7 days of delivery (using updated_at as delivery date)
$delivery_date = strtotime($order['updated_at']);
$current_date = time();
$days_since_delivery = floor(($current_date - $delivery_date) / (60 * 60 * 24));

if ($days_since_delivery > 7) {
    header('Location: account.php?error=return_window_expired');
    exit;
}

// Initialize variables
$error_message = '';
$success_message = '';

// Handle form submission BEFORE including header (to avoid header errors)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_type = isset($_POST['request_type']) ? mysqli_real_escape_string($conn, $_POST['request_type']) : 'return';
    $return_reason = mysqli_real_escape_string($conn, $_POST['return_reason']);
    $other_reason = isset($_POST['other_reason']) ? mysqli_real_escape_string($conn, $_POST['other_reason']) : '';
    
    // Validate request type
    if (!in_array($request_type, ['return', 'refund'])) {
        $request_type = 'return'; // Default to return
    }
    
    // Validate reason
    $valid_reasons = ['colour_wrong', 'item_wrong', 'defective', 'no_good_quality', 'other'];
    if (!in_array($return_reason, $valid_reasons)) {
        $error_message = 'Invalid return reason selected.';
    } elseif ($return_reason === 'other' && empty($other_reason)) {
        $error_message = 'Please provide a reason for return.';
    } else {
        // Handle image upload
        $image_name = '';
        if (isset($_FILES['return_image']) && $_FILES['return_image']['error'] == 0) {
            $upload_dir = 'uploads/returns/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['return_image']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $image_name = time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $image_name;
                
                if (!move_uploaded_file($_FILES['return_image']['tmp_name'], $upload_path)) {
                    $error_message = 'Failed to upload image. Please try again.';
                }
            } else {
                $error_message = 'Invalid image format. Please upload JPG, PNG, GIF, or WebP.';
            }
        } else {
            $error_message = 'Image is required. Please upload an image of the product.';
        }
        
        if (empty($error_message)) {
            // Check if request_type column exists in the table
            $check_column = mysqli_query($conn, "SHOW COLUMNS FROM return_refund_requests LIKE 'request_type'");
            $has_request_type = ($check_column && mysqli_num_rows($check_column) > 0);
            
            // Insert return/refund request
            if ($has_request_type) {
                $insert_query = "INSERT INTO return_refund_requests 
                                (order_id, user_id, order_number, request_type, return_reason, other_reason, image, status) 
                                VALUES 
                                ($order_id, $user_id, '{$order['order_number']}', '$request_type', '$return_reason', 
                                " . ($other_reason ? "'$other_reason'" : "NULL") . ", '$image_name', 'pending')";
            } else {
                // If column doesn't exist, insert without it
                $insert_query = "INSERT INTO return_refund_requests 
                                (order_id, user_id, order_number, return_reason, other_reason, image, status) 
                                VALUES 
                                ($order_id, $user_id, '{$order['order_number']}', '$return_reason', 
                                " . ($other_reason ? "'$other_reason'" : "NULL") . ", '$image_name', 'pending')";
            }
            
            if (mysqli_query($conn, $insert_query)) {
                // Redirect before any HTML output
                header('Location: account.php?success=return_requested');
                exit;
            } else {
                $error_message = 'Failed to submit return request. Please try again.';
            }
        }
    }
}

// Now include header after form processing (only if not redirecting)
include "includes/header.php";

// Prepare display variables (ensure they're set)
if (!isset($error_message)) {
    $error_message = '';
}
if (!isset($success_message)) {
    $success_message = '';
}

$order_date = date('d M Y, h:i A', strtotime($order['created_at']));
$delivery_date_display = date('d M Y, h:i A', strtotime($order['updated_at']));
$days_remaining = 7 - $days_since_delivery;
?>

<style>
    .return-request-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .return-request-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e5e5;
    }
    
    .return-request-header h1 {
        color: #2b2b2b;
        font-size: 28px;
        margin-bottom: 10px;
    }
    
    .return-request-header p {
        color: #666;
        font-size: 14px;
    }
    
    .order-info-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .order-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .order-info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #2b2b2b;
    }
    
    .info-value {
        color: #666;
    }
    
    .warning-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .warning-box p {
        margin: 0;
        color: #856404;
        font-size: 14px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2b2b2b;
        font-size: 14px;
    }
    
    .form-group label.required::after {
        content: ' *';
        color: #dc3545;
    }
    
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2fc7b4;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .file-upload-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .file-upload-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        border: 2px dashed #e5e5e5;
        border-radius: 8px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .file-upload-label:hover {
        border-color: #2fc7b4;
        background: #f0f9f7;
    }
    
    .file-upload-label i {
        font-size: 48px;
        color: #2fc7b4;
        margin-right: 15px;
    }
    
    .file-upload-text {
        color: #666;
        font-size: 14px;
    }
    
    .file-preview {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        display: none;
    }
    
    .file-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
    }
    
    .submit-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(47, 199, 180, 0.3);
    }
    
    .back-btn {
        display: inline-block;
        margin-top: 20px;
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
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .request-type-options {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }
    
    .request-type-label {
        flex: 1;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        background: #fff;
        position: relative;
    }
    
    .request-type-label input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .request-type-label:hover {
        border-color: #2fc7b4;
        background: #f0f9f7;
    }
    
    .request-type-label input[type="radio"]:checked + i,
    .request-type-label:has(input[type="radio"]:checked) {
        border-color: #2fc7b4;
        background: #e8f8f0;
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.2);
    }
    
    .request-type-label:has(input[type="radio"]:checked) strong {
        color: #2fc7b4;
    }
</style>

<div class="return-request-container">
    <div class="return-request-header">
        <h1>Request Return/Refund</h1>
        <p>Please provide details about your return request</p>
    </div>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <div class="order-info-box">
        <div class="order-info-row">
            <span class="info-label">Order Number:</span>
            <span class="info-value"><?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="order-info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value"><?= $order_date ?></span>
        </div>
        <div class="order-info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value"><?= $delivery_date_display ?></span>
        </div>
        <div class="order-info-row">
            <span class="info-label">Total Amount:</span>
            <span class="info-value" style="color: #2fc7b4; font-weight: 600;">₹<?= number_format($order['total_amount'], 2) ?></span>
        </div>
    </div>
    
    <div class="warning-box">
        <p><strong>Note:</strong> You have <?= $days_remaining ?> day(s) remaining to request a return/refund. Return requests must be submitted within 7 days of delivery.</p>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="required">Request Type</label>
            <div class="request-type-options" style="display: flex; gap: 20px; margin-top: 10px;">
                <label class="request-type-label" style="flex: 1; padding: 15px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s ease; background: #fff;">
                    <input type="radio" name="request_type" value="return" checked style="margin-right: 8px;">
                    <i class="fas fa-undo-alt" style="font-size: 24px; color: #2fc7b4; display: block; margin-bottom: 8px;"></i>
                    <strong style="display: block; color: #2b2b2b;">Return</strong>
                    <small style="color: #666; font-size: 12px;">Return the product</small>
                </label>
                <label class="request-type-label" style="flex: 1; padding: 15px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s ease; background: #fff;">
                    <input type="radio" name="request_type" value="refund" style="margin-right: 8px;">
                    <i class="fas fa-money-bill-wave" style="font-size: 24px; color: #2fa76b; display: block; margin-bottom: 8px;"></i>
                    <strong style="display: block; color: #2b2b2b;">Refund</strong>
                    <small style="color: #666; font-size: 12px;">Get money back</small>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="required">Return Reason</label>
            <select name="return_reason" id="return_reason" required>
                <option value="">Select a reason</option>
                <option value="colour_wrong">Colour is wrong</option>
                <option value="item_wrong">Item is wrong</option>
                <option value="defective">Defective</option>
                <option value="no_good_quality">No good quality</option>
                <option value="other">Other reason</option>
            </select>
        </div>
        
        <div class="form-group" id="other_reason_group" style="display: none;">
            <label class="required">Please describe the problem</label>
            <textarea name="other_reason" id="other_reason" placeholder="Please provide details about the issue..."></textarea>
        </div>
        
        <div class="form-group">
            <label class="required">Upload Image</label>
            <div class="file-upload-wrapper">
                <input type="file" name="return_image" id="return_image" class="file-upload-input" accept="image/*" required>
                <label for="return_image" class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span class="file-upload-text">Click to upload or drag and drop<br><small>JPG, PNG, GIF, or WebP (Max 5MB)</small></span>
                </label>
            </div>
            <div class="file-preview" id="file_preview">
                <img id="preview_image" src="" alt="Preview">
            </div>
        </div>
        
        <button type="submit" class="submit-btn" id="submit_btn">
            <i class="fas fa-paper-plane"></i> <span id="submit_btn_text">Submit Return Request</span>
        </button>
    </form>
    
    <a href="account.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to My Orders
    </a>
</div>

<script>
    // Update button text based on request type selection
    document.querySelectorAll('input[name="request_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const submitBtnText = document.getElementById('submit_btn_text');
            if (this.value === 'return') {
                submitBtnText.textContent = 'Submit Return Request';
            } else if (this.value === 'refund') {
                submitBtnText.textContent = 'Submit Refund Request';
            }
        });
    });
    
    // Show/hide other reason textarea
    document.getElementById('return_reason').addEventListener('change', function() {
        const otherReasonGroup = document.getElementById('other_reason_group');
        const otherReasonInput = document.getElementById('other_reason');
        if (this.value === 'other') {
            otherReasonGroup.style.display = 'block';
            otherReasonInput.required = true;
        } else {
            otherReasonGroup.style.display = 'none';
            otherReasonInput.required = false;
            otherReasonInput.value = '';
        }
    });
    
    // Preview uploaded image
    document.getElementById('return_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_image').src = e.target.result;
                document.getElementById('file_preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
