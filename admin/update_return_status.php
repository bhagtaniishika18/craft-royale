<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

include '../includes/db.php';

$return_id = isset($_POST['return_id']) ? (int)$_POST['return_id'] : 0;
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';
$admin_notes = isset($_POST['admin_notes']) ? mysqli_real_escape_string($conn, $_POST['admin_notes']) : '';
$refund_amount = isset($_POST['refund_amount']) ? floatval($_POST['refund_amount']) : null;
$exchange_product_details = isset($_POST['exchange_product_details']) ? mysqli_real_escape_string($conn, $_POST['exchange_product_details']) : '';
$tracking_number = isset($_POST['tracking_number']) ? mysqli_real_escape_string($conn, $_POST['tracking_number']) : '';

if ($return_id <= 0 || empty($status)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'approved', 'product_received', 'product_exchanged', 'product_shipped', 'completed', 'refund_processing', 'refunded', 'rejected'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

// Build update query
$update_fields = ["status = '$status'"];

if ($status === 'product_received') {
    $update_fields[] = "product_received_at = NOW()";
}

if ($status === 'refunded') {
    $update_fields[] = "refund_processed_at = NOW()";
    if ($refund_amount !== null) {
        $update_fields[] = "refund_amount = $refund_amount";
    }
}

if (!empty($admin_notes)) {
    $update_fields[] = "admin_notes = '$admin_notes'";
}

// Handle exchange product details and tracking number
if ($status === 'product_exchanged') {
    if (!empty($exchange_product_details)) {
        // Store exchange details in admin_notes if column doesn't exist separately
        $exchange_note = "Exchange Product: " . $exchange_product_details;
        if (!empty($tracking_number)) {
            $exchange_note .= " | Tracking: " . $tracking_number;
        }
        if (!empty($admin_notes)) {
            $exchange_note .= " | Notes: " . $admin_notes;
        }
        $update_fields[] = "admin_notes = '" . mysqli_real_escape_string($conn, $exchange_note) . "'";
    }
}

$update_query = "UPDATE return_refund_requests SET " . implode(', ', $update_fields) . ", updated_at = NOW() WHERE id = $return_id";

if (mysqli_query($conn, $update_query)) {
    // If status is refunded, also update order payment status
    if ($status === 'refunded') {
        $order_query = "SELECT order_id FROM return_refund_requests WHERE id = $return_id";
        $order_result = mysqli_query($conn, $order_query);
        if ($order_result && mysqli_num_rows($order_result) > 0) {
            $order_data = mysqli_fetch_assoc($order_result);
            $order_update = "UPDATE orders SET payment_status = 'refunded', updated_at = NOW() WHERE id = {$order_data['order_id']}";
            mysqli_query($conn, $order_update);
        }
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Return status updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update return status']);
}
?>
