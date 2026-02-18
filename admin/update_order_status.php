<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

include '../includes/db.php';

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$order_status = isset($_POST['order_status']) ? mysqli_real_escape_string($conn, $_POST['order_status']) : '';

if ($order_id <= 0 || empty($order_status)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Validate order status
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($order_status, $valid_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

// Check if order is already delivered - prevent status changes
$check_query = "SELECT order_status FROM orders WHERE id = $order_id";
$check_result = mysqli_query($conn, $check_query);
if ($check_result && mysqli_num_rows($check_result) > 0) {
    $current_order = mysqli_fetch_assoc($check_result);
    if ($current_order['order_status'] === 'delivered') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot change status of delivered orders']);
        exit;
    }
}

// Update order status
$update_query = "UPDATE orders SET order_status = '$order_status', updated_at = NOW() WHERE id = $order_id";
if (mysqli_query($conn, $update_query)) {
    echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update order status']);
}
?>
