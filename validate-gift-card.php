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

// Format card number (remove dashes for database lookup)
$card_number_clean = str_replace('-', '', $card_number);

// Check gift card
$query = "SELECT * FROM gift_cards WHERE REPLACE(card_number, '-', '') = '$card_number_clean' AND pin = '$pin'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$gift_card = mysqli_fetch_assoc($result);

if (!$gift_card) {
    echo json_encode(['success' => false, 'message' => 'Invalid gift card number or PIN']);
    exit;
}

// Check if card is active
if ($gift_card['status'] != 'active') {
    echo json_encode(['success' => false, 'message' => 'Gift card is not active']);
    exit;
}

// Check if card is expired
$valid_till = strtotime($gift_card['valid_till']);
if ($valid_till < time()) {
    echo json_encode(['success' => false, 'message' => 'Gift card has expired']);
    exit;
}

// Get remaining balance from transactions
$balance_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = {$gift_card['id']} ORDER BY created_at DESC LIMIT 1";
$balance_result = mysqli_query($conn, $balance_query);
$balance_data = mysqli_fetch_assoc($balance_result);

$balance = $balance_data ? $balance_data['remaining_balance'] : $gift_card['amount'];

if ($balance <= 0) {
    echo json_encode(['success' => false, 'message' => 'Gift card balance is zero']);
    exit;
}

echo json_encode([
    'success' => true,
    'balance' => $balance,
    'valid_till' => date('d/m/Y', strtotime($gift_card['valid_till'])),
    'card_id' => $gift_card['id']
]);
?>
