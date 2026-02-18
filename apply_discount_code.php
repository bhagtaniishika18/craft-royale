<?php
session_start();
header('Content-Type: application/json');
include 'includes/db.php';

$response = ['success' => false, 'message' => '', 'discount' => 0, 'code_data' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $cart_total = floatval($_POST['cart_total'] ?? 0);
    
    if (empty($code)) {
        $response['message'] = 'Please enter a discount code';
        echo json_encode($response);
        exit;
    }
    
    if ($cart_total <= 0) {
        $response['message'] = 'Invalid cart total';
        echo json_encode($response);
        exit;
    }
    
    // Get discount code from database
    $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND status = 'active'");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Invalid or inactive discount code';
        echo json_encode($response);
        exit;
    }
    
    $discount_code = $result->fetch_assoc();
    
    // Validate date range
    $today = date('Y-m-d');
    if ($today < $discount_code['valid_from']) {
        $response['message'] = 'This discount code is not yet valid. Valid from: ' . date('d M Y', strtotime($discount_code['valid_from']));
        echo json_encode($response);
        exit;
    }
    
    if ($today > $discount_code['valid_until']) {
        $response['message'] = 'This discount code has expired on ' . date('d M Y', strtotime($discount_code['valid_until']));
        echo json_encode($response);
        exit;
    }
    
    // Validate minimum order amount
    if ($cart_total < $discount_code['min_order_amount']) {
        $response['message'] = 'Minimum order amount of ₹' . number_format($discount_code['min_order_amount'], 2) . ' required to use this code';
        echo json_encode($response);
        exit;
    }
    
    // Check usage limit
    if ($discount_code['usage_limit'] !== null && $discount_code['times_used'] >= $discount_code['usage_limit']) {
        $response['message'] = 'This discount code has reached its usage limit';
        echo json_encode($response);
        exit;
    }
    
    // Calculate discount
    $discount_amount = ($cart_total * $discount_code['discount_percentage']) / 100;
    
    // Apply max discount limit if set
    if ($discount_code['max_discount_amount'] !== null && $discount_amount > $discount_code['max_discount_amount']) {
        $discount_amount = $discount_code['max_discount_amount'];
    }
    
    // Round to 2 decimal places
    $discount_amount = round($discount_amount, 2);
    
    // Success response
    $response['success'] = true;
    $response['message'] = 'Discount code applied successfully!';
    $response['discount'] = $discount_amount;
    $response['code_data'] = [
        'id' => $discount_code['id'],
        'code' => $discount_code['code'],
        'percentage' => $discount_code['discount_percentage'],
        'discount_amount' => $discount_amount,
        'description' => $discount_code['description']
    ];
    
    // Store in session for checkout
    $_SESSION['applied_discount'] = $response['code_data'];
    
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
