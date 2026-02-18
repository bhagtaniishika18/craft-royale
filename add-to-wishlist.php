<?php
// Prevent any output before JSON
ob_start();
session_start();
include "includes/db.php";

// Clear any output that might have been generated
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'add'; // 'add' or 'remove'

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Fetch product details
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

$query = "SELECT * FROM products WHERE id = $product_id";
if (in_array('status', $products_columns)) {
    $query .= " AND status = 'active'";
}

$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if ($action === 'add') {
    // Check if product already in wishlist
    if (isset($_SESSION['wishlist'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
        exit;
    }
    
    // Add to wishlist
    $_SESSION['wishlist'][$product_id] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => isset($product['sale_price']) && $product['sale_price'] > 0 ? floatval($product['sale_price']) : floatval($product['price']),
        'mrp' => floatval($product['price']),
        'image' => isset($product['image']) ? $product['image'] : '',
    ];
    
    $message = 'Product added to wishlist!';
} else if ($action === 'remove') {
    // Remove from wishlist
    if (isset($_SESSION['wishlist'][$product_id])) {
        unset($_SESSION['wishlist'][$product_id]);
        $message = 'Product removed from wishlist!';
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not in wishlist']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Calculate wishlist count
$wishlist_count = count($_SESSION['wishlist']);

echo json_encode([
    'success' => true,
    'message' => $message,
    'wishlist_count' => $wishlist_count,
    'action' => $action
]);
?>
