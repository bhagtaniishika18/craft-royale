<?php
session_start();
include "includes/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    header('Location: account.php');
    exit;
}

// Get form data
$first_name = isset($_POST['first_name']) ? mysqli_real_escape_string($conn, trim($_POST['first_name'])) : '';
$last_name = isset($_POST['last_name']) ? mysqli_real_escape_string($conn, trim($_POST['last_name'])) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : ($user['email'] ?? '');
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : ($user['phone'] ?? '');
$address = isset($_POST['address']) ? mysqli_real_escape_string($conn, trim($_POST['address'])) : '';
$apartment = isset($_POST['apartment']) ? mysqli_real_escape_string($conn, trim($_POST['apartment'])) : '';
$city = isset($_POST['city']) ? mysqli_real_escape_string($conn, trim($_POST['city'])) : '';
$state = isset($_POST['state']) ? mysqli_real_escape_string($conn, trim($_POST['state'])) : '';
$pin_code = isset($_POST['pin_code']) ? mysqli_real_escape_string($conn, trim($_POST['pin_code'])) : '';
$country = isset($_POST['country']) ? mysqli_real_escape_string($conn, trim($_POST['country'])) : 'India';
$shipping_method = isset($_POST['shipping_method']) ? mysqli_real_escape_string($conn, $_POST['shipping_method']) : 'prepaid';
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : '';

// Handle payment method
if ($payment_method === 'gift_card') {
    // Gift card covers full amount, no payment needed
    $payment_method = 'gift_card';
} elseif ($shipping_method === 'cod') {
    // If COD is selected as shipping method, set payment method to COD
    $payment_method = 'cod';
}

// Billing address
$billing_address = isset($_POST['billing_address']) ? mysqli_real_escape_string($conn, $_POST['billing_address']) : '';
$billing_same_as_shipping = isset($_POST['billing_address']) && $_POST['billing_address'] === 'same';

if ($billing_same_as_shipping) {
    $billing_address = $address . ($apartment ? ', ' . $apartment : '');
    $billing_city = $city;
    $billing_state = $state;
    $billing_pin_code = $pin_code;
} else {
    $billing_address = isset($_POST['billing_address']) ? mysqli_real_escape_string($conn, trim($_POST['billing_address'])) : $address;
    $billing_city = isset($_POST['billing_city']) ? mysqli_real_escape_string($conn, trim($_POST['billing_city'])) : $city;
    $billing_state = isset($_POST['billing_state']) ? mysqli_real_escape_string($conn, trim($_POST['billing_state'])) : $state;
    $billing_pin_code = isset($_POST['billing_pin_code']) ? mysqli_real_escape_string($conn, trim($_POST['billing_pin_code'])) : $pin_code;
}

// Payment details
$razorpay_payment_id = isset($_POST['razorpay_payment_id']) ? mysqli_real_escape_string($conn, $_POST['razorpay_payment_id']) : NULL;
$razorpay_order_id = isset($_POST['razorpay_order_id']) ? mysqli_real_escape_string($conn, $_POST['razorpay_order_id']) : NULL;
$razorpay_signature = isset($_POST['razorpay_signature']) ? mysqli_real_escape_string($conn, $_POST['razorpay_signature']) : NULL;

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
    empty($address) || empty($city) || empty($state) || empty($pin_code)) {
    header('Location: checkout.php?error=missing_fields');
    exit;
}

// Payment method is only required if not using gift card
if ($payment_method !== 'gift_card' && empty($payment_method)) {
    header('Location: checkout.php?error=missing_fields');
    exit;
}

// Calculate totals
$subtotal = 0;
$total_items = 0;
$validated_cart = [];

foreach ($_SESSION['cart'] as $product_id => $item) {
    if (!is_array($item)) continue;
    
    $product_id_int = (int)$product_id;
    if ($product_id_int <= 0) continue;
    
    // Verify product exists
    $product_check = mysqli_query($conn, "SELECT id, name, price, mrp, image, stock FROM products WHERE id = $product_id_int");
    $product = mysqli_fetch_assoc($product_check);
    
    if (!$product) {
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    
    $item_price = floatval($product['price']);
    $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
    $item_total = $item_price * $item_quantity;
    
    // Check stock
    $available_stock = (int)$product['stock'];
    if ($item_quantity > $available_stock) {
        $item_quantity = $available_stock;
    }
    
    if ($item_quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    
    $subtotal += $item_total;
    $total_items += $item_quantity;
    
    $validated_cart[$product_id] = [
        'id' => $product_id_int,
        'name' => $product['name'],
        'price' => $item_price,
        'mrp' => floatval($product['mrp']),
        'image' => $product['image'],
        'quantity' => $item_quantity
    ];
}

if (empty($validated_cart)) {
    header('Location: cart.php?error=empty_cart');
    exit;
}

// Calculate shipping
$free_shipping_threshold = 750;
$has_free_shipping = $subtotal >= $free_shipping_threshold;

// Check if gift card covers full amount - if so, free shipping
$gift_card_covers_full = false;
if (isset($_SESSION['applied_gift_card'])) {
    $applied_gift_card_check = $_SESSION['applied_gift_card'];
    $card_id_check = $applied_gift_card_check['card_id'];
    $balance_check_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = $card_id_check ORDER BY created_at DESC LIMIT 1";
    $balance_check_result = mysqli_query($conn, $balance_check_query);
    $balance_check_data = mysqli_fetch_assoc($balance_check_result);
    $current_balance_check = $balance_check_data ? $balance_check_data['remaining_balance'] : 0;
    
    if ($current_balance_check >= $subtotal) {
        $gift_card_covers_full = true;
        $has_free_shipping = true;
        $shipping_cost = 0;
        $shipping_method = 'prepaid'; // Force prepaid with free shipping
    } else {
        $shipping_cost = $has_free_shipping ? 0 : ($shipping_method === 'cod' ? ($has_free_shipping ? 100 : 200) : ($has_free_shipping ? 0 : 100));
    }
} else {
    $shipping_cost = $has_free_shipping ? 0 : ($shipping_method === 'cod' ? ($has_free_shipping ? 100 : 200) : ($has_free_shipping ? 0 : 100));
}

// No tax calculation
$tax_amount = 0;

// Check for applied gift card
$gift_card_discount = 0;
$gift_card_id = null;
$gift_card_used = false;
if (isset($_SESSION['applied_gift_card'])) {
    $applied_gift_card = $_SESSION['applied_gift_card'];
    $gift_card_id = $applied_gift_card['card_id'];
    
    // Verify gift card still exists and is active
    $card_check_query = "SELECT * FROM gift_cards WHERE id = $gift_card_id";
    $card_check_result = mysqli_query($conn, $card_check_query);
    $card_check = mysqli_fetch_assoc($card_check_result);
    
    if (!$card_check) {
        header('Location: checkout.php?error=gift_card_invalid');
        exit;
    }
    
    if ($card_check['status'] != 'active') {
        unset($_SESSION['applied_gift_card']);
        header('Location: checkout.php?error=gift_card_inactive&message=' . urlencode('Your gift card is no longer active. Please use a different payment method.'));
        exit;
    }
    
    // Check if card is expired
    $valid_till = strtotime($card_check['valid_till']);
    if ($valid_till < time()) {
        unset($_SESSION['applied_gift_card']);
        header('Location: checkout.php?error=gift_card_expired&message=' . urlencode('Your gift card has expired. Please use a different payment method.'));
        exit;
    }
    
    // Get current balance from transactions
    $balance_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = $gift_card_id ORDER BY created_at DESC LIMIT 1";
    $balance_result = mysqli_query($conn, $balance_query);
    $balance_data = mysqli_fetch_assoc($balance_result);
    $current_balance = $balance_data ? $balance_data['remaining_balance'] : $card_check['amount'];
    
    if ($current_balance <= 0) {
        unset($_SESSION['applied_gift_card']);
        header('Location: checkout.php?error=gift_card_empty&message=' . urlencode('Your gift card balance is zero. Please refill your gift card or use a different payment method.'));
        exit;
    }
    
    // Calculate amount before gift card (subtotal + shipping)
    $amount_before_gift_card = $subtotal + $shipping_cost;
    // Gift card discount can cover up to the full amount (subtotal + shipping)
    $gift_card_discount = min($current_balance, $amount_before_gift_card);
    
    // If gift card doesn't cover full amount and payment method is gift_card, show error
    if ($payment_method === 'gift_card' && $gift_card_discount < $amount_before_gift_card) {
        header('Location: checkout.php?error=gift_card_insufficient&message=' . urlencode('Your gift card balance (₹' . number_format($current_balance, 2) . ') is insufficient. Please add more funds or use a different payment method.'));
        exit;
    }
}

// Calculate total
$total = $subtotal + $shipping_cost - $gift_card_discount;
if ($total < 0) $total = 0;

// Generate order number
$order_number = 'CR' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Determine payment status
if ($payment_method === 'gift_card') {
    // Gift card covers full amount - paid
    $payment_status = 'paid';
} elseif ($payment_method === 'cod') {
    // Cash on Delivery - pending (payment on delivery)
    $payment_status = 'pending';
} else {
    // All other payment methods are considered paid
    // Razorpay (UPI/Card/Wallet/Net Banking), Credit Card, Bank Deposit
    $payment_status = 'paid';
}

// Insert order
$full_name = $first_name . ' ' . $last_name;
$full_address = $address . ($apartment ? ', ' . $apartment : '');

$order_query = "INSERT INTO orders (
    order_number, user_id, name, email, phone, address, city, state, pin_code, country,
    billing_address, billing_city, billing_state, billing_pin_code,
    shipping_method, payment_method, payment_status,
    razorpay_payment_id, razorpay_order_id, razorpay_signature,
    subtotal, shipping_cost, tax_amount, total_amount, order_status
) VALUES (
    '$order_number', $user_id, '$full_name', '$email', '$phone', '$full_address', '$city', '$state', '$pin_code', '$country',
    '$billing_address', '$billing_city', '$billing_state', '$billing_pin_code',
    '$shipping_method', '$payment_method', '$payment_status',
    " . ($razorpay_payment_id ? "'$razorpay_payment_id'" : "NULL") . ",
    " . ($razorpay_order_id ? "'$razorpay_order_id'" : "NULL") . ",
    " . ($razorpay_signature ? "'$razorpay_signature'" : "NULL") . ",
    $subtotal, $shipping_cost, $tax_amount, $total, 'pending'
)";

if (mysqli_query($conn, $order_query)) {
    $order_id = mysqli_insert_id($conn);
    
    // Insert order items
    foreach ($validated_cart as $product_id => $item) {
        $product_id_int = (int)$product_id;
        $product_name = mysqli_real_escape_string($conn, $item['name']);
        $product_image = mysqli_real_escape_string($conn, $item['image']);
        $quantity = (int)$item['quantity'];
        $unit_price = floatval($item['price']);
        $mrp = floatval($item['mrp']);
        $item_subtotal = $unit_price * $quantity;
        
        $order_item_query = "INSERT INTO order_items (
            order_id, product_id, product_name, product_image, quantity, unit_price, mrp, subtotal
        ) VALUES (
            $order_id, $product_id_int, '$product_name', '$product_image', $quantity, $unit_price, $mrp, $item_subtotal
        )";
        
        if (!mysqli_query($conn, $order_item_query)) {
            error_log("Order Item Insert Failed: " . mysqli_error($conn) . " Query: " . $order_item_query);
        }
        
        // Update product stock
        mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id = $product_id_int");
    }
    
    // Process gift card if applied
    if ($gift_card_discount > 0 && $gift_card_id) {
        // Get current balance (re-verify before deducting)
        $balance_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = $gift_card_id ORDER BY created_at DESC LIMIT 1";
        $balance_result = mysqli_query($conn, $balance_query);
        $balance_data = mysqli_fetch_assoc($balance_result);
        $current_balance = $balance_data ? $balance_data['remaining_balance'] : 0;
        
        // Double-check balance is sufficient
        if ($current_balance < $gift_card_discount) {
            // Rollback order
            mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");
            mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id");
            
            // Restore product stock
            foreach ($validated_cart as $product_id => $item) {
                $product_id_int = (int)$product_id;
                $quantity = (int)$item['quantity'];
                mysqli_query($conn, "UPDATE products SET stock = stock + $quantity WHERE id = $product_id_int");
            }
            
            header('Location: checkout.php?error=gift_card_insufficient&message=' . urlencode('Your gift card balance (₹' . number_format($current_balance, 2) . ') is insufficient for this order. Please refill your gift card or use a different payment method.'));
            exit;
        }
        
        // Calculate new balance
        $new_balance = max(0, $current_balance - $gift_card_discount);
        
        // Record transaction
        $transaction_query = "INSERT INTO gift_card_transactions (gift_card_id, order_id, amount_used, remaining_balance, transaction_type) 
                            VALUES ($gift_card_id, $order_id, $gift_card_discount, $new_balance, 'usage')";
        mysqli_query($conn, $transaction_query);
        
        // Update gift card status if balance is zero
        if ($new_balance <= 0) {
            mysqli_query($conn, "UPDATE gift_cards SET status = 'used', used_at = NOW(), used_by = $user_id WHERE id = $gift_card_id");
        }
        
        $gift_card_used = true;
    }
    
    // Clear cart and gift card from session
    $_SESSION['cart'] = [];
    if (isset($_SESSION['applied_gift_card'])) {
        unset($_SESSION['applied_gift_card']);
    }
    
    // Redirect to receipt page
    header("Location: view_receipt.php?id=$order_id");
    exit;
} else {
    // Error creating order
    header('Location: checkout.php?error=order_failed');
    exit;
}
?>
