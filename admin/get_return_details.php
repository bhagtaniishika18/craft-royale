<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

include '../includes/db.php';

$return_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($return_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid return ID']);
    exit;
}

$query = "SELECT r.*, o.order_number, o.total_amount, o.name as customer_name, o.email as customer_email, o.phone as customer_phone
         FROM return_refund_requests r 
         JOIN orders o ON r.order_id = o.id 
         WHERE r.id = $return_id";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $return_data = mysqli_fetch_assoc($result);
    echo json_encode(['status' => 'success', 'return' => $return_data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Return request not found']);
}
?>
