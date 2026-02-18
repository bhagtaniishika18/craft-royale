<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

$card_number = isset($_POST['card_number']) ? mysqli_real_escape_string($conn, strtoupper(trim($_POST['card_number']))) : '';
$pin = isset($_POST['pin']) ? mysqli_real_escape_string($conn, trim($_POST['pin'])) : '';

if (empty($card_number) || empty($pin)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both card number and PIN']);
    exit;
}

// Format card number (remove dashes and spaces for database lookup)
$card_number_clean = str_replace(['-', ' '], '', $card_number);

// Check gift card - Case-insensitive and robust against spaces/dashes
$query = "SELECT * FROM gift_cards 
          WHERE LOWER(TRIM(REPLACE(REPLACE(card_number, '-', ''), ' ', ''))) = LOWER('$card_number_clean') 
          AND LOWER(TRIM(pin)) = LOWER('$pin')";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$gift_card = mysqli_fetch_assoc($result);

if (!$gift_card) {
    echo json_encode(['success' => false, 'message' => 'Gift card code you entered is not valid or doesn\'t exist.']);
    exit;
}

// Check if card is active
if ($gift_card['status'] != 'active') {
    echo json_encode(['success' => false, 'message' => 'This gift card is currently ' . $gift_card['status'] . ' and cannot be used.']);
    exit;
}

// Check if card is expired
$valid_till = strtotime($gift_card['valid_till']);
if ($valid_till < time()) {
    echo json_encode(['success' => false, 'message' => 'This gift card expired on ' . date('d M Y', $valid_till)]);
    exit;
}

// Get remaining balance
$balance_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = {$gift_card['id']} ORDER BY created_at DESC LIMIT 1";
$balance_result = mysqli_query($conn, $balance_query);
$balance_data = mysqli_fetch_assoc($balance_result);
$balance = $balance_data ? $balance_data['remaining_balance'] : $gift_card['amount'];

if ($balance <= 0) {
    echo json_encode(['success' => false, 'message' => 'Gift card balance is zero']);
    exit;
}

// Calculate cart total (subtotal only, shipping will be added later)
$cart_subtotal = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
            $cart_subtotal += $item['price'] * $item['quantity'];
        }
    }
}

if ($cart_subtotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate discount - gift card can cover subtotal (shipping will be handled at checkout)
// The discount will be applied to subtotal, and can cover shipping too if balance allows
$discount = min($balance, $cart_subtotal);

// Store gift card info in session
$_SESSION['applied_gift_card'] = [
    'card_id' => $gift_card['id'],
    'card_number' => $gift_card['card_number'],
    'discount' => $discount,
    'balance' => $balance
];

echo json_encode([
    'success' => true,
    'discount' => $discount,
    'message' => 'Gift card applied successfully'
]);
?>
