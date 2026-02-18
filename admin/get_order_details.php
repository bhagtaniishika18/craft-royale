<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

// Fetch order details
$order_query = "SELECT o.*, u.first_name, u.last_name, u.email as user_email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id ORDER BY id";
$items_result = mysqli_query($conn, $items_query);
$order_items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    // Fix product image path
    $product_image = !empty($item['product_image']) ? $item['product_image'] : '../assets/images/placeholder.jpg';
    if (!empty($item['product_image'])) {
        // Check various possible paths
        if (file_exists('../uploads/products/' . $item['product_image'])) {
            $product_image = '../uploads/products/' . $item['product_image'];
        } elseif (file_exists('../products/' . $item['product_image'])) {
            $product_image = '../products/' . $item['product_image'];
        } elseif (file_exists('../' . $item['product_image'])) {
            $product_image = '../' . $item['product_image'];
        } elseif (strpos($item['product_image'], 'http') === 0) {
            // Already a full URL
            $product_image = $item['product_image'];
        } else {
            // Try with uploads/products prefix
            $product_image = '../uploads/products/' . $item['product_image'];
        }
    }
    $item['product_image'] = $product_image;
    $order_items[] = $item;
}

// Format date
$order['formatted_date'] = date('d M Y, h:i A', strtotime($order['created_at']));

echo json_encode([
    'status' => 'success',
    'order' => $order,
    'items' => $order_items
]);
?>
