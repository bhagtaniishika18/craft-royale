<?php
session_start();
include "includes/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate total amount
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    if (is_array($item)) {
        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $subtotal += $item_price * $item_quantity;
    }
}

$free_shipping_threshold = 750;
$has_free_shipping = $subtotal >= $free_shipping_threshold;
$shipping_cost = $has_free_shipping ? 0 : 100;

$total = $subtotal + $shipping_cost;
$amount = $total * 100; // Convert to paise

// Razorpay API credentials (You need to add these to your config)
// For now, using placeholder - you should add these to a config file
$razorpay_key_id = 'YOUR_RAZORPAY_KEY_ID'; // Replace with your Razorpay Key ID
$razorpay_key_secret = 'YOUR_RAZORPAY_KEY_SECRET'; // Replace with your Razorpay Key Secret

// If you have Razorpay credentials in database or config, fetch them here
// For example:
// $config_query = "SELECT razorpay_key_id, razorpay_key_secret FROM site_config LIMIT 1";
// $config_result = mysqli_query($conn, $config_query);
// if ($config = mysqli_fetch_assoc($config_result)) {
//     $razorpay_key_id = $config['razorpay_key_id'];
//     $razorpay_key_secret = $config['razorpay_key_secret'];
// }

// Create order using Razorpay API
$order_data = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => 'order_' . time() . '_' . $_SESSION['user_id'],
    'notes' => [
        'user_id' => $_SESSION['user_id'],
        'cart_total' => $total
    ]
];

// Use cURL to create Razorpay order
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
curl_setopt($ch, CURLOPT_USERPWD, $razorpay_key_id . ':' . $razorpay_key_secret);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $order_response = json_decode($response, true);
    
    if (isset($order_response['id'])) {
        // Store order ID in session for verification
        $_SESSION['razorpay_order_id'] = $order_response['id'];
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_response['id'],
            'amount' => $amount,
            'currency' => 'INR',
            'key_id' => $razorpay_key_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create order',
            'error' => $order_response
        ]);
    }
} else {
    // If API call fails, return a mock order ID for testing
    // Remove this in production and handle errors properly
    $mock_order_id = 'order_' . time() . '_' . rand(1000, 9999);
    $_SESSION['razorpay_order_id'] = $mock_order_id;
    
    echo json_encode([
        'success' => true,
        'order_id' => $mock_order_id,
        'amount' => $amount,
        'currency' => 'INR',
        'key_id' => $razorpay_key_id,
        'note' => 'Using mock order for testing. Please configure Razorpay credentials.'
    ]);
}
?>
