<?php
session_start();
include "includes/db.php";

// Check if user is logged in - MUST be before any output
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

// Check if cart is empty - MUST be before any output
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Now include header.php after all header redirects are done
include "includes/header.php";

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get cart items and calculate totals
// Only show products that actually exist in the database
$subtotal = 0;
$total_items = 0;
$cart_items = [];
$validated_cart = [];

foreach ($_SESSION['cart'] as $product_id => $item) {
    if (!is_array($item)) continue;
    
    // Validate product exists in database
    $product_id_int = (int)$product_id;
    if ($product_id_int <= 0) {
        // Invalid product ID, skip it
        continue;
    }
    
    // Check if product exists in database
    $product_check_query = "SELECT id, name, price, mrp, image, stock FROM products WHERE id = $product_id_int";
    $product_check_result = mysqli_query($conn, $product_check_query);
    $product_exists = mysqli_fetch_assoc($product_check_result);
    
    if (!$product_exists) {
        // Product doesn't exist in database, remove from cart and skip
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    
    // Use database values to ensure accuracy
    $item_price = isset($product_exists['price']) ? floatval($product_exists['price']) : (isset($item['price']) ? floatval($item['price']) : 0);
    $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
    $item_total = $item_price * $item_quantity;
    
    // Check stock availability
    $available_stock = isset($product_exists['stock']) ? (int)$product_exists['stock'] : 0;
    if ($item_quantity > $available_stock) {
        $item_quantity = $available_stock;
        $_SESSION['cart'][$product_id]['quantity'] = $item_quantity;
    }
    
    if ($item_quantity <= 0) {
        // No stock available, remove from cart
        unset($_SESSION['cart'][$product_id]);
        continue;
    }
    
    $subtotal += $item_total;
    $total_items += $item_quantity;
    
    // Use product name from database, fallback to session
    $product_name = !empty($product_exists['name']) ? $product_exists['name'] : (isset($item['name']) ? $item['name'] : 'Product');
    $product_image = !empty($product_exists['image']) ? $product_exists['image'] : (isset($item['image']) ? $item['image'] : '');
    $product_mrp = isset($product_exists['mrp']) ? floatval($product_exists['mrp']) : (isset($item['mrp']) ? floatval($item['mrp']) : 0);
    
    $cart_items[] = [
        'id' => $product_id_int,
        'name' => $product_name,
        'price' => $item_price,
        'mrp' => $product_mrp,
        'image' => $product_image,
        'quantity' => $item_quantity,
        'total' => $item_total
    ];
    
    // Keep validated item in session
    $validated_cart[$product_id] = [
        'id' => $product_id_int,
        'name' => $product_name,
        'price' => $item_price,
        'mrp' => $product_mrp,
        'image' => $product_image,
        'quantity' => $item_quantity,
        'stock' => $available_stock
    ];
}

// Update session cart with only validated products
$_SESSION['cart'] = $validated_cart;

// Calculate discount code (if any)
$discount = 0;
$applied_discount = null;
if (isset($_SESSION['applied_discount'])) {
    $discount_code = $_SESSION['applied_discount'];
    $discount = ($subtotal * $discount_code['percentage']) / 100;
    
    // Apply max discount limit if set
    if (isset($discount_code['max_discount_amount']) && $discount_code['max_discount_amount'] > 0) {
        $discount = min($discount, $discount_code['max_discount_amount']);
    }
    
    // Round to 2 decimal places
    $discount = round($discount, 2);
    
    // Update discount amount in session
    $_SESSION['applied_discount']['discount_amount'] = $discount;
    $applied_discount = $_SESSION['applied_discount'];
}

// Calculate shipping (Based on original subtotal)
$free_shipping_threshold = 750;
$has_free_shipping = $subtotal >= $free_shipping_threshold;
$shipping_cost = $has_free_shipping ? 0 : 100;

// Amount before gift card
$amount_before_gift_card = $subtotal - $discount + $shipping_cost;

// No tax calculation
$tax_amount = 0;

// Check for applied gift card
$gift_card_discount = 0;
$applied_gift_card = null;
$gift_card_covers_full_amount = false;

if (isset($_SESSION['applied_gift_card'])) {
    $applied_gift_card = $_SESSION['applied_gift_card'];
    
    // Verify gift card still exists and has balance
    $card_id = $applied_gift_card['card_id'];
    $card_check_query = "SELECT * FROM gift_cards WHERE id = $card_id";
    $card_check_result = mysqli_query($conn, $card_check_query);
    $card_check = mysqli_fetch_assoc($card_check_result);
    
    if (!$card_check || $card_check['status'] != 'active') {
        // Gift card no longer valid, remove from session
        unset($_SESSION['applied_gift_card']);
        $applied_gift_card = null;
        $total = $subtotal + $shipping_cost;
    } else {
        // Get current balance from transactions
        $balance_query = "SELECT remaining_balance FROM gift_card_transactions WHERE gift_card_id = $card_id ORDER BY created_at DESC LIMIT 1";
        $balance_result = mysqli_query($conn, $balance_query);
        $balance_data = mysqli_fetch_assoc($balance_result);
        $current_balance = $balance_data ? $balance_data['remaining_balance'] : $card_check['amount'];
        
        // Update session with current balance
        $_SESSION['applied_gift_card']['balance'] = $current_balance;
        $_SESSION['applied_gift_card']['discount'] = min($current_balance, $subtotal + $shipping_cost);
        
        // If gift card covers full amount, give free shipping
        if ($current_balance >= $subtotal) {
            // Gift card covers subtotal, give free shipping
            $shipping_cost = 0;
            $has_free_shipping = true;
            $amount_before_gift_card = $subtotal; // No shipping cost
            $gift_card_discount = min($current_balance, $amount_before_gift_card);
            
            // Check if gift card covers full amount
            if ($gift_card_discount >= $amount_before_gift_card) {
                $gift_card_covers_full_amount = true;
                $total = 0;
            } else {
                // Calculate total with partial gift card discount
                $total = $amount_before_gift_card - $gift_card_discount;
            }
        } else {
            // Gift card doesn't cover subtotal, calculate normally
            $amount_before_gift_card = $subtotal + $shipping_cost;
            $gift_card_discount = min($current_balance, $amount_before_gift_card);
            $total = $amount_before_gift_card - $gift_card_discount;
        }
    }
} else {
    // No gift card applied
    $total = $subtotal + $shipping_cost;
}
?>

<style>
    .checkout-page {
        min-height: 100vh;
        background: #f5f5f5;
        padding: 40px 0 80px;
    }

    .checkout-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .checkout-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        padding: 20px 0;
    }

    .checkout-logo {
        font-size: 24px;
        font-weight: 800;
        color: #2b2b2b;
        text-decoration: none;
    }

    .checkout-header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .checkout-header-right a {
        color: #0066cc;
        text-decoration: none;
        font-weight: 600;
    }

    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
        margin-top: 20px;
    }

    .checkout-form-section {
        background: #ffffff;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .checkout-section {
        padding: 30px;
        border-bottom: 1px solid #e5e5e5;
    }

    .checkout-section:last-child {
        border-bottom: none;
    }

    .checkout-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2b2b2b;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d0d0d0;
        border-radius: 6px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .form-group input[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    .checkbox-group input[type="checkbox"] {
        width: auto;
        margin: 0;
    }

    .checkbox-group label {
        margin: 0;
        font-weight: 400;
        font-size: 14px;
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 15px;
    }

    .radio-option {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
        margin-bottom: 10px;
    }

    .radio-option:hover {
        border-color: #0066cc;
    }

    .radio-option.selected,
    .radio-option:has(input[type="radio"]:checked) {
        border-color: #0066cc;
        background: #f0f7ff;
    }

    .radio-option input[type="radio"] {
        width: auto;
        margin: 0;
        cursor: pointer;
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        color: #0066cc;
        font-weight: 600;
    }

    .radio-option:has(input[type="radio"]:checked),
    .radio-option.selected {
        border-color: #0066cc;
        background: #f0f7ff;
    }

    .radio-label {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .radio-label-text {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .radio-label-text span {
        font-weight: 500;
        color: #2b2b2b;
    }

    .radio-label-price {
        font-weight: 600;
        color: #2b2b2b;
    }

    .payment-logos {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .payment-logos span {
        padding: 4px 8px;
        background: #f5f5f5;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .payment-logos img {
        height: 20px;
        object-fit: contain;
    }

    .bank-details {
        margin-top: 15px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.8;
    }

    .bank-details p {
        margin: 5px 0;
    }

    .secure-text {
        font-size: 13px;
        color: #666;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .payment-message {
        margin-top: 15px;
        padding: 20px;
        background: #f0f7ff;
        border: 1px solid #0066cc;
        border-radius: 8px;
        text-align: center;
    }

    .payment-message .redirect-icon {
        font-size: 40px;
        margin-bottom: 10px;
    }

    .payment-message p {
        margin: 0;
        font-size: 13px;
        color: #2b2b2b;
        line-height: 1.6;
    }

    .payment-form {
        margin-top: 15px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }

    .billing-address-form {
        margin-top: 15px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }

    /* Razorpay UPI Options Styles */
    .razorpay-upi-options {
        margin-top: 15px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
    }

    .upi-apps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .upi-app-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 15px 10px;
        background: #ffffff;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        font-weight: 600;
        color: #2b2b2b;
    }

    .upi-app-btn:hover {
        border-color: #0066cc;
        background: #f0f7ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
    }

    .upi-app-btn.selected {
        border-color: #0066cc;
        background: #f0f7ff;
    }

    .upi-app-icon {
        font-size: 32px;
        line-height: 1;
    }

    .payment-divider {
        text-align: center;
        margin: 20px 0;
        position: relative;
    }

    .payment-divider::before,
    .payment-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 45%;
        height: 1px;
        background: #e5e5e5;
    }

    .payment-divider::before {
        left: 0;
    }

    .payment-divider::after {
        right: 0;
    }

    .payment-divider span {
        background: #ffffff;
        padding: 0 15px;
        color: #666;
        font-size: 13px;
        font-weight: 600;
    }

    .other-payment-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .payment-option-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 20px;
        background: #ffffff;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 600;
        color: #2b2b2b;
    }

    .payment-option-btn:hover {
        border-color: #0066cc;
        background: #f0f7ff;
    }

    .payment-option-btn span {
        font-size: 18px;
    }
    
    /* Autocomplete Suggestions Styles */
    .autocomplete-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #d0d0d0;
        border-top: none;
        border-radius: 0 0 6px 6px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-top: -1px;
    }
    
    .autocomplete-suggestions .suggestion-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }
    
    .autocomplete-suggestions .suggestion-item:hover,
    .autocomplete-suggestions .suggestion-item.selected {
        background: #f0f7ff;
        color: #0066cc;
    }
    
    .autocomplete-suggestions .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .upi-vpa-input {
        padding: 15px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }

    @media (max-width: 768px) {
        .upi-apps-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .complete-order-btn {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 30px;
        box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
    }

    .complete-order-btn:hover {
        background: linear-gradient(135deg, #0052a3 0%, #0066cc 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
    }

    .checkout-footer-links {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #e5e5e5;
        flex-wrap: wrap;
    }

    .checkout-footer-links a {
        color: #666;
        text-decoration: none;
        font-size: 13px;
        transition: color 0.2s ease;
    }

    .checkout-footer-links a:hover {
        color: #0066cc;
    }

    /* Order Summary Styles */
    .order-summary {
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 170px;
        max-height: calc(100vh - 190px);
        overflow-y: auto;
    }

    .order-summary-title {
        font-size: 20px;
        font-weight: 700;
        color: #2b2b2b;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e5e5;
    }

    .order-items {
        margin-bottom: 25px;
    }

    .order-item {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .order-item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        position: relative;
    }

    .order-item-quantity {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #0066cc;
        color: #ffffff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }

    .order-item-details {
        flex: 1;
    }

    .order-item-name {
        font-size: 14px;
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .order-item-price {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
    }

    .order-item-price-original {
        font-size: 13px;
        color: #999;
        text-decoration: line-through;
    }

    .order-item-price-current {
        font-size: 15px;
        font-weight: 700;
        color: #dc3545;
    }

    .discount-section {
        margin: 25px 0;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .discount-input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .discount-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #d0d0d0;
        border-radius: 6px;
        font-size: 14px;
    }

    .discount-apply-btn {
        padding: 12px 20px;
        background: #2b2b2b;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .discount-apply-btn:hover {
        background: #1a1a1a;
    }

    .discount-applied {
        padding: 10px;
        background: #e8f5e9;
        border-radius: 6px;
        font-size: 13px;
        color: #2e7d32;
        font-weight: 600;
    }

    .order-summary-totals {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 2px solid #e5e5e5;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .summary-row-label {
        color: #666;
    }

    .summary-row-value {
        font-weight: 600;
        color: #2b2b2b;
    }

    .summary-row-total {
        font-size: 18px;
        font-weight: 700;
        color: #2b2b2b;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e5e5e5;
    }

    .summary-tax-note {
        font-size: 12px;
        color: #666;
        margin-top: 10px;
    }

    .summary-savings {
        margin-top: 15px;
        padding: 12px;
        background: #fff3cd;
        border-radius: 6px;
        text-align: center;
        font-weight: 700;
        color: #856404;
        font-size: 14px;
    }

    @media (max-width: 1024px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }

        .order-summary {
            position: relative;
            top: 0;
        }
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .checkout-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }
</style>

<div class="checkout-page">
    <div class="checkout-container">
        <div class="checkout-header">
            <a href="index.php" class="checkout-logo">Craft Royale</a>
            <div class="checkout-header-right">
                <a href="cart.php">
                    <i class="fas fa-shopping-bag"></i> Cart
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php">Sign in</a>
                <?php endif; ?>
            </div>
        </div>

        <?php 
        // Display error messages
        if (isset($_GET['error'])) {
            $error_type = $_GET['error'];
            $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
            
            if ($error_type === 'gift_card_empty' || $error_type === 'gift_card_insufficient') {
                $error_message = $error_message ?: 'Your gift card balance is insufficient. Please refill your gift card or use a different payment method.';
            } elseif ($error_type === 'gift_card_inactive') {
                $error_message = $error_message ?: 'Your gift card is no longer active. Please use a different payment method.';
            } elseif ($error_type === 'gift_card_expired') {
                $error_message = $error_message ?: 'Your gift card has expired. Please use a different payment method.';
            } elseif ($error_type === 'gift_card_invalid') {
                $error_message = $error_message ?: 'Invalid gift card. Please use a different payment method.';
            }
        ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_message) ?>
            <?php if ($error_type === 'gift_card_empty' || $error_type === 'gift_card_insufficient'): ?>
                <div style="margin-top: 10px;">
                    <a href="gift-cards.php" style="color: #721c24; text-decoration: underline; font-weight: 700;">Purchase a new gift card</a> or 
                    <a href="cart.php" style="color: #721c24; text-decoration: underline; font-weight: 700;">remove gift card</a> to use other payment methods.
                </div>
            <?php endif; ?>
        </div>
        <?php } ?>

        <div class="checkout-layout">
            <!-- Left Column: Checkout Form -->
            <div class="checkout-form-section">
                <form id="checkoutForm" method="POST" action="process-checkout.php">
                    <!-- Contact Section -->
                    <div class="checkout-section">
                        <h2 class="checkout-section-title">Contact</h2>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="email_news" name="email_news">
                            <label for="email_news">Email me with news and offers</label>
                        </div>
                    </div>

                    <!-- Delivery Section -->
                    <div class="checkout-section">
                        <h2 class="checkout-section-title">Delivery</h2>
                        <div class="form-group">
                            <label for="country">Country/Region</label>
                            <input type="text" id="country" name="country" value="India" readonly>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First name</label>
                                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last name</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="company">Company (optional)</label>
                            <input type="text" id="company" name="company">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="apartment">Apartment, suite, etc. (optional)</label>
                            <input type="text" id="apartment" name="apartment">
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="position: relative;">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" autocomplete="off" placeholder="Start typing city name..." required>
                                <div id="citySuggestions" class="autocomplete-suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 10000;"></div>
                            </div>
                            <div class="form-group" style="position: relative;">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" autocomplete="off" placeholder="Start typing state name..." required>
                                <div id="stateSuggestions" class="autocomplete-suggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 10000;"></div>
                            </div>
                        </div>
                        
                        <script>
                        // Immediate autocomplete setup (runs before DOMContentLoaded)
                        (function() {
                            // Wait a bit for DOM to be ready
                            setTimeout(function() {
                                const cityInput = document.getElementById('city');
                                const citySuggestions = document.getElementById('citySuggestions');
                                const stateInput = document.getElementById('state');
                                const stateSuggestions = document.getElementById('stateSuggestions');
                                
                                if (!cityInput || !citySuggestions || !stateInput || !stateSuggestions) {
                                    console.log('Autocomplete elements not found yet, will retry...');
                                    return;
                                }
                                
                                // Comprehensive city autocomplete
                                const cityStateMap = {
                                    'Ahmedabad': 'Gujarat', 'Surat': 'Gujarat', 'Vadodara': 'Gujarat', 'Rajkot': 'Gujarat', 
                                    'Bhavnagar': 'Gujarat', 'Jamnagar': 'Gujarat', 'Junagadh': 'Gujarat', 'Gandhinagar': 'Gujarat',
                                    'Anand': 'Gujarat', 'Nadiad': 'Gujarat', 'Bharuch': 'Gujarat', 'Navsari': 'Gujarat',
                                    'Surendranagar': 'Gujarat', 'Mehsana': 'Gujarat', 'Palanpur': 'Gujarat', 'Patan': 'Gujarat',
                                    'Porbandar': 'Gujarat', 'Veraval': 'Gujarat', 'Morbi': 'Gujarat', 'Bhuj': 'Gujarat',
                                    'Gondal': 'Gujarat', 'Jetpur': 'Gujarat', 'Dhoraji': 'Gujarat', 'Wankaner': 'Gujarat',
                                    'Amreli': 'Gujarat', 'Botad': 'Gujarat', 'Dahod': 'Gujarat', 'Godhra': 'Gujarat',
                                    'Himmatnagar': 'Gujarat', 'Modasa': 'Gujarat', 'Deesa': 'Gujarat', 'Kadi': 'Gujarat',
                                    'Kalol': 'Gujarat', 'Unjha': 'Gujarat', 'Visnagar': 'Gujarat',
                                    'Mumbai': 'Maharashtra', 'Pune': 'Maharashtra', 'Nagpur': 'Maharashtra', 'Nashik': 'Maharashtra',
                                    'Aurangabad': 'Maharashtra', 'Solapur': 'Maharashtra', 'Thane': 'Maharashtra', 'Kalyan': 'Maharashtra',
                                    'Vasai': 'Maharashtra', 'Panvel': 'Maharashtra', 'Navi Mumbai': 'Maharashtra', 'Pimpri-Chinchwad': 'Maharashtra',
                                    'Kolhapur': 'Maharashtra', 'Sangli': 'Maharashtra', 'Satara': 'Maharashtra', 'Ratnagiri': 'Maharashtra',
                                    'Jalgaon': 'Maharashtra', 'Dhule': 'Maharashtra', 'Nanded': 'Maharashtra', 'Latur': 'Maharashtra',
                                    'Amravati': 'Maharashtra', 'Akola': 'Maharashtra', 'Chandrapur': 'Maharashtra', 'Wardha': 'Maharashtra',
                                    'Yavatmal': 'Maharashtra', 'Bhusawal': 'Maharashtra', 'Ichalkaranji': 'Maharashtra', 'Jalna': 'Maharashtra',
                                    'Delhi': 'Delhi', 'New Delhi': 'Delhi', 'Gurgaon': 'Haryana', 'Noida': 'Uttar Pradesh',
                                    'Faridabad': 'Haryana', 'Ghaziabad': 'Uttar Pradesh', 'Meerut': 'Uttar Pradesh', 'Saharanpur': 'Uttar Pradesh',
                                    'Gurugram': 'Haryana', 'Sonipat': 'Haryana', 'Panipat': 'Haryana', 'Karnal': 'Haryana',
                                    'Bangalore': 'Karnataka', 'Mysore': 'Karnataka', 'Hubli': 'Karnataka', 'Mangalore': 'Karnataka',
                                    'Belgaum': 'Karnataka', 'Gulbarga': 'Karnataka', 'Davangere': 'Karnataka', 'Shimoga': 'Karnataka',
                                    'Bellary': 'Karnataka', 'Bijapur': 'Karnataka', 'Raichur': 'Karnataka', 'Tumkur': 'Karnataka',
                                    'Udupi': 'Karnataka', 'Manipal': 'Karnataka', 'Chitradurga': 'Karnataka', 'Hassan': 'Karnataka',
                                    'Chennai': 'Tamil Nadu', 'Coimbatore': 'Tamil Nadu', 'Madurai': 'Tamil Nadu', 'Tiruchirappalli': 'Tamil Nadu',
                                    'Salem': 'Tamil Nadu', 'Tirunelveli': 'Tamil Nadu', 'Erode': 'Tamil Nadu', 'Vellore': 'Tamil Nadu',
                                    'Thanjavur': 'Tamil Nadu', 'Tuticorin': 'Tamil Nadu', 'Dindigul': 'Tamil Nadu', 'Karur': 'Tamil Nadu',
                                    'Hyderabad': 'Telangana', 'Warangal': 'Telangana', 'Nizamabad': 'Telangana', 'Karimnagar': 'Telangana',
                                    'Ramagundam': 'Telangana', 'Khammam': 'Telangana', 'Mahbubnagar': 'Telangana', 'Nalgonda': 'Telangana',
                                    'Kolkata': 'West Bengal', 'Howrah': 'West Bengal', 'Durgapur': 'West Bengal', 'Asansol': 'West Bengal',
                                    'Siliguri': 'West Bengal', 'Bardhaman': 'West Bengal', 'Kharagpur': 'West Bengal', 'Malda': 'West Bengal',
                                    'Jaipur': 'Rajasthan', 'Jodhpur': 'Rajasthan', 'Kota': 'Rajasthan', 'Bikaner': 'Rajasthan',
                                    'Ajmer': 'Rajasthan', 'Udaipur': 'Rajasthan', 'Bhilwara': 'Rajasthan', 'Alwar': 'Rajasthan',
                                    'Sikar': 'Rajasthan', 'Pali': 'Rajasthan', 'Sri Ganganagar': 'Rajasthan', 'Bharatpur': 'Rajasthan',
                                    'Lucknow': 'Uttar Pradesh', 'Kanpur': 'Uttar Pradesh', 'Agra': 'Uttar Pradesh', 'Varanasi': 'Uttar Pradesh',
                                    'Allahabad': 'Uttar Pradesh', 'Bareilly': 'Uttar Pradesh', 'Aligarh': 'Uttar Pradesh', 'Moradabad': 'Uttar Pradesh',
                                    'Gorakhpur': 'Uttar Pradesh', 'Faizabad': 'Uttar Pradesh', 'Jhansi': 'Uttar Pradesh', 'Muzaffarnagar': 'Uttar Pradesh',
                                    'Mathura': 'Uttar Pradesh', 'Rampur': 'Uttar Pradesh', 'Shahjahanpur': 'Uttar Pradesh',
                                    'Amritsar': 'Punjab', 'Ludhiana': 'Punjab', 'Jalandhar': 'Punjab', 'Patiala': 'Punjab',
                                    'Bathinda': 'Punjab', 'Hoshiarpur': 'Punjab', 'Moga': 'Punjab', 'Pathankot': 'Punjab',
                                    'Sangrur': 'Punjab', 'Batala': 'Punjab', 'Muktsar': 'Punjab', 'Barnala': 'Punjab',
                                    'Bhopal': 'Madhya Pradesh', 'Indore': 'Madhya Pradesh', 'Gwalior': 'Madhya Pradesh', 'Jabalpur': 'Madhya Pradesh',
                                    'Ujjain': 'Madhya Pradesh', 'Sagar': 'Madhya Pradesh', 'Ratlam': 'Madhya Pradesh', 'Rewa': 'Madhya Pradesh',
                                    'Satna': 'Madhya Pradesh', 'Burhanpur': 'Madhya Pradesh', 'Khandwa': 'Madhya Pradesh', 'Chhindwara': 'Madhya Pradesh',
                                    'Patna': 'Bihar', 'Gaya': 'Bihar', 'Bhagalpur': 'Bihar', 'Muzaffarpur': 'Bihar',
                                    'Purnia': 'Bihar', 'Darbhanga': 'Bihar', 'Arrah': 'Bihar', 'Begusarai': 'Bihar',
                                    'Katihar': 'Bihar', 'Munger': 'Bihar', 'Chapra': 'Bihar', 'Saharsa': 'Bihar',
                                    'Bhubaneswar': 'Odisha', 'Cuttack': 'Odisha', 'Rourkela': 'Odisha', 'Berhampur': 'Odisha',
                                    'Sambalpur': 'Odisha', 'Puri': 'Odisha', 'Baleshwar': 'Odisha', 'Baripada': 'Odisha',
                                    'Guwahati': 'Assam', 'Silchar': 'Assam', 'Dibrugarh': 'Assam', 'Jorhat': 'Assam',
                                    'Nagaon': 'Assam', 'Tinsukia': 'Assam', 'Tezpur': 'Assam', 'Bongaigaon': 'Assam',
                                    'Thiruvananthapuram': 'Kerala', 'Kochi': 'Kerala', 'Kozhikode': 'Kerala', 'Thrissur': 'Kerala',
                                    'Kollam': 'Kerala', 'Kannur': 'Kerala', 'Alappuzha': 'Kerala', 'Palakkad': 'Kerala',
                                    'Kottayam': 'Kerala', 'Malappuram': 'Kerala', 'Manjeri': 'Kerala', 'Thalassery': 'Kerala',
                                    'Visakhapatnam': 'Andhra Pradesh', 'Vijayawada': 'Andhra Pradesh', 'Guntur': 'Andhra Pradesh', 'Nellore': 'Andhra Pradesh',
                                    'Rajahmundry': 'Andhra Pradesh', 'Kurnool': 'Andhra Pradesh', 'Tirupati': 'Andhra Pradesh', 'Kakinada': 'Andhra Pradesh',
                                    'Kadapa': 'Andhra Pradesh', 'Anantapur': 'Andhra Pradesh', 'Eluru': 'Andhra Pradesh', 'Ongole': 'Andhra Pradesh',
                                    'Raipur': 'Chhattisgarh', 'Bilaspur': 'Chhattisgarh', 'Durg': 'Chhattisgarh', 'Korba': 'Chhattisgarh',
                                    'Bhilai': 'Chhattisgarh', 'Rajnandgaon': 'Chhattisgarh', 'Jagdalpur': 'Chhattisgarh', 'Ambikapur': 'Chhattisgarh',
                                    'Dehradun': 'Uttarakhand', 'Haridwar': 'Uttarakhand', 'Roorkee': 'Uttarakhand', 'Haldwani': 'Uttarakhand',
                                    'Rudrapur': 'Uttarakhand', 'Kashipur': 'Uttarakhand', 'Rishikesh': 'Uttarakhand', 'Mussoorie': 'Uttarakhand',
                                    'Ranchi': 'Jharkhand', 'Jamshedpur': 'Jharkhand', 'Dhanbad': 'Jharkhand', 'Bokaro': 'Jharkhand',
                                    'Hazaribagh': 'Jharkhand', 'Giridih': 'Jharkhand', 'Deoghar': 'Jharkhand', 'Phusro': 'Jharkhand',
                                    'Chandigarh': 'Chandigarh', 'Imphal': 'Manipur', 'Aizawl': 'Mizoram', 'Shillong': 'Meghalaya',
                                    'Kohima': 'Nagaland', 'Agartala': 'Tripura', 'Gangtok': 'Sikkim', 'Itanagar': 'Arunachal Pradesh',
                                    'Panaji': 'Goa', 'Puducherry': 'Puducherry'
                                };
                                
                                const indianCities = Object.keys(cityStateMap);
                                const indianStates = ['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi', 'Puducherry'];
                                
                                // City autocomplete
                                cityInput.addEventListener('input', function(e) {
                                    const value = e.target.value.trim().toLowerCase();
                                    if (value.length === 0) {
                                        citySuggestions.style.display = 'none';
                                        return;
                                    }
                                    
                                    const matches = indianCities.filter(city => 
                                        city.toLowerCase().startsWith(value) || city.toLowerCase().includes(value)
                                    ).slice(0, 15);
                                    
                                    if (matches.length === 0) {
                                        citySuggestions.style.display = 'none';
                                        return;
                                    }
                                    
                                    citySuggestions.innerHTML = matches.map(city => {
                                        const state = cityStateMap[city] || '';
                                        return '<div class="suggestion-item" data-city="' + city + '" data-state="' + state + '">' + city + (state ? ' <span style="color: #666; font-size: 12px;">(' + state + ')</span>' : '') + '</div>';
                                    }).join('');
                                    
                                    citySuggestions.style.display = 'block';
                                });
                                
                                citySuggestions.addEventListener('click', function(e) {
                                    const item = e.target.closest('.suggestion-item');
                                    if (item) {
                                        cityInput.value = item.getAttribute('data-city');
                                        const state = item.getAttribute('data-state');
                                        if (state && stateInput) {
                                            stateInput.value = state;
                                        }
                                        citySuggestions.style.display = 'none';
                                    }
                                });
                                
                                // State autocomplete
                                stateInput.addEventListener('input', function(e) {
                                    const value = e.target.value.trim().toLowerCase();
                                    if (value.length === 0) {
                                        stateSuggestions.style.display = 'none';
                                        return;
                                    }
                                    
                                    const matches = indianStates.filter(state => 
                                        state.toLowerCase().startsWith(value)
                                    ).slice(0, 15);
                                    
                                    if (matches.length === 0) {
                                        stateSuggestions.style.display = 'none';
                                        return;
                                    }
                                    
                                    stateSuggestions.innerHTML = matches.map(state => 
                                        '<div class="suggestion-item" data-state="' + state + '">' + state + '</div>'
                                    ).join('');
                                    
                                    stateSuggestions.style.display = 'block';
                                });
                                
                                stateSuggestions.addEventListener('click', function(e) {
                                    const item = e.target.closest('.suggestion-item');
                                    if (item) {
                                        stateInput.value = item.getAttribute('data-state');
                                        stateSuggestions.style.display = 'none';
                                    }
                                });
                                
                                // Hide on outside click
                                document.addEventListener('click', function(e) {
                                    if (!cityInput.contains(e.target) && !citySuggestions.contains(e.target)) {
                                        citySuggestions.style.display = 'none';
                                    }
                                    if (!stateInput.contains(e.target) && !stateSuggestions.contains(e.target)) {
                                        stateSuggestions.style.display = 'none';
                                    }
                                });
                                
                                console.log('Autocomplete initialized');
                            }, 500);
                        })();
                        </script>
                        <div class="form-group">
                            <label for="pin_code">PIN code</label>
                            <input type="text" id="pin_code" name="pin_code" value="360001" pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" pattern="[0-9]{10}" maxlength="10" placeholder="10 digit mobile number" required>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="save_info" name="save_info">
                            <label for="save_info">Save this information for next time</label>
                        </div>
                    </div>

                    <!-- Shipping Method Section -->
                    <div class="checkout-section">
                        <h2 class="checkout-section-title">Shipping method</h2>
                        <?php if ($gift_card_covers_full_amount): ?>
                        <div style="padding: 15px; background: #f0f7ff; border: 1px solid #0066cc; border-radius: 8px; margin-bottom: 15px;">
                            <p style="margin: 0; font-size: 13px; color: #666;">🎁 Free shipping included with your gift card payment.</p>
                        </div>
                        <?php endif; ?>
                        <div class="radio-group">
                            <label class="radio-option" style="<?= $gift_card_covers_full_amount ? 'opacity: 0.6; cursor: not-allowed;' : '' ?>">
                                <input type="radio" name="shipping_method" value="prepaid" checked <?= $gift_card_covers_full_amount ? 'disabled' : 'required' ?>>
                                <div class="radio-label">
                                    <div class="radio-label-text">
                                        <span>Prepaid</span>
                                    </div>
                                    <div class="radio-label-price">
                                        <?php if ($has_free_shipping || $gift_card_covers_full_amount): ?>
                                            Free Shipping
                                        <?php else: ?>
                                            Rs. <?= number_format(100, 2) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                            <label class="radio-option" style="<?= $gift_card_covers_full_amount ? 'opacity: 0.6; cursor: not-allowed;' : '' ?>">
                                <input type="radio" name="shipping_method" value="cod" <?= $gift_card_covers_full_amount ? 'disabled' : 'required' ?>>
                                <div class="radio-label">
                                    <div class="radio-label-text">
                                        <span>Cash On Delivery</span>
                                    </div>
                                    <div class="radio-label-price">
                                        COD Handling Charges Rs. <?= number_format($has_free_shipping || $gift_card_covers_full_amount ? 100 : 200, 2) ?>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <?php if ($gift_card_covers_full_amount): ?>
                        <input type="hidden" name="shipping_method" value="prepaid">
                        <?php endif; ?>
                    </div>

                    <!-- Payment Section -->
                    <div class="checkout-section" id="paymentSection">
                        <h2 class="checkout-section-title">Payment</h2>
                        <p class="secure-text">All transactions are secure and encrypted.</p>
                        <?php if ($gift_card_covers_full_amount): ?>
                        <div style="padding: 15px; background: #f0f7ff; border: 1px solid #0066cc; border-radius: 8px; margin-bottom: 20px;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #2b2b2b; font-weight: 600;">🎁 Gift Card Payment</p>
                            <p style="margin: 0; font-size: 13px; color: #666;">Your gift card (<?= htmlspecialchars(substr($applied_gift_card['card_number'], -4)) ?>) covers the entire order amount. Payment fields are disabled.</p>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span style="color: #dc3545;">*</span></label>
                            <select id="payment_method" name="payment_method" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : 'required' ?>>
                                <option value="">Select Payment Method</option>
                                <option value="upi" <?= !$gift_card_covers_full_amount ? 'selected' : '' ?>>UPI</option>
                                <option value="credit_card">Card</option>
                                <option value="bank_deposit">Bank Deposit</option>
                                <option value="cod">Cash On Delivery (COD)</option>
                                <option value="gift_card" <?= $gift_card_covers_full_amount ? 'selected' : '' ?>>Gift Card</option>
                            </select>
                        </div>
                        
                        <!-- UPI Payment Form -->
                        <div id="upiOptions" class="payment-form" style="display: none;">
                            <div class="form-group">
                                <label for="upi_vpa">Enter UPI ID (example@upi) <span style="color: #dc3545;">*</span></label>
                                <input type="text" id="upi_vpa" name="upi_vpa" placeholder="Enter UPI ID (example@upi)" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Format: yourname@paytm, yourname@ybl, yourname@upi, etc.</small>
                                <div id="upiValidationMsg" style="margin-top: 5px; font-size: 12px;"></div>
                            </div>
                        </div>
                        
                        <!-- Credit Card Form -->
                        <div id="creditCardForm" class="payment-form" style="display: none;">
                            <div class="form-group">
                                <label for="card_number">Card Number (XXXX-XXXX-XXXX-XXXX) <span style="color: #dc3545;">*</span></label>
                                <div style="position: relative;">
                                    <input type="text" id="card_number" name="card_number" placeholder="Card Number (XXXX-XXXX-XXXX-XXXX)" maxlength="19" pattern="[0-9\s]+" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #666;">🔒</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="name_on_card">Name on Card <span style="color: #dc3545;">*</span></label>
                                <input type="text" id="name_on_card" name="name_on_card" placeholder="Name as it appears on card" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date (MM/YY) <span style="color: #dc3545;">*</span></label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="Expiry Date (MM/YY)" maxlength="5" pattern="[0-9]{2}/[0-9]{2}" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                                </div>
                                <div class="form-group">
                                    <label for="security_code">CVV <span style="color: #dc3545;">*</span></label>
                                    <div style="position: relative;">
                                        <input type="text" id="security_code" name="security_code" placeholder="CVV" maxlength="4" pattern="[0-9]+" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #666; cursor: help;" title="3 or 4 digit code on the back of your card">❓</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_type">Card Type <span style="color: #dc3545;">*</span></label>
                                <select id="card_type" name="card_type" <?= $gift_card_covers_full_amount ? 'disabled style="background: #f5f5f5; cursor: not-allowed;"' : '' ?>>
                                    <option value="">Select Card Type</option>
                                    <option value="visa">VISA</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="rupay">RuPay</option>
                                    <option value="amex">American Express</option>
                                    <option value="discover">Discover</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Bank Deposit Details -->
                        <div id="bankDetails" class="bank-details" style="display: none;">
                            <p><strong>Account Holder Name:</strong> Aarvak Garments Pvt Ltd</p>
                            <p><strong>Bank Name:</strong> Canara Bank</p>
                            <p><strong>Branch Address:</strong> 1A, 40, H-block, Noida, Gautham Budha Nagar Dist, Uttar Pradesh</p>
                            <p><strong>Account Number:</strong> 90491010009958</p>
                            <p><strong>IFSC Code:</strong> CNRB0002886</p>
                            <p><strong>SWIFT Code:</strong> CNRBINBBBFD</p>
                        </div>
                    </div>
                    
                        <!-- Gift Card Details Section -->
                        <div id="giftCardPaymentForm" class="payment-form" style="display: none;">
                            <div style="background: #fdfdfd; border: 2px solid #764ba2; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.1);">
                                <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #764ba2; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-gift"></i> Pay using Gift Card
                                </h4>
                                <?php include 'includes/gift-card-component.php'; ?>
                            </div>
                        </div>

                    <?php if ($gift_card_covers_full_amount): ?>
                    <input type="hidden" name="payment_method" value="gift_card">
                    <?php endif; ?>
                    
                    <script>
                    // Immediate payment method handler (runs before DOMContentLoaded)
                    (function() {
                        const pmSelect = document.getElementById('payment_method');
                        const upiDiv = document.getElementById('upiOptions');
                        const cardDiv = document.getElementById('creditCardForm');
                        const bankDiv = document.getElementById('bankDetails');
                        
                        // Check if payment is disabled (gift card covers full amount)
                        const isPaymentDisabled = pmSelect && pmSelect.disabled;
                        
                        function showPaymentForm() {
                            if (!pmSelect) return;
                            
                            const method = pmSelect.value;
                            
                            // Hide all
                            if (upiDiv) upiDiv.style.display = 'none';
                            if (cardDiv) cardDiv.style.display = 'none';
                            if (bankDiv) bankDiv.style.display = 'none';
                            const giftDiv = document.getElementById('giftCardPaymentForm');
                            if (giftDiv) giftDiv.style.display = 'none';
                            
                            // Show selected
                            if (method === 'upi' && upiDiv) {
                                upiDiv.style.display = 'block';
                            } else if (method === 'credit_card' && cardDiv) {
                                cardDiv.style.display = 'block';
                            } else if (method === 'bank_deposit' && bankDiv) {
                                bankDiv.style.display = 'block';
                            } else if (method === 'gift_card' && giftDiv) {
                                giftDiv.style.display = 'block';
                            }
                        }
                        
                        if (pmSelect && !isPaymentDisabled) {
                            pmSelect.addEventListener('change', showPaymentForm);
                            // Run immediately
                            setTimeout(showPaymentForm, 100);
                        }
                    })();
                    </script>

                    <!-- Billing Address Section -->
                    <div class="checkout-section">
                        <h2 class="checkout-section-title">Billing address</h2>
                        <div class="radio-group" id="billingChoiceGroup">
                            <label class="radio-option">
                                <input type="radio" name="billing_address_choice" value="same" checked required onchange="toggleBillingForm(false)">
                                <div class="radio-label">
                                    <div class="radio-label-text">
                                        <span>Same as shipping address</span>
                                    </div>
                                </div>
                            </label>
                            <label class="radio-option" id="differentBillingOption">
                                <input type="radio" name="billing_address_choice" value="different" required onchange="toggleBillingForm(true)">
                                <div class="radio-label">
                                    <div class="radio-label-text">
                                        <span>Use a different billing address</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <script>
                        function toggleBillingForm(show) {
                            const form = document.getElementById('billingAddressForm');
                            if (!form) return;
                            
                            form.style.display = show ? 'block' : 'none';
                            
                            // Handle required fields
                            const fields = form.querySelectorAll('input, select');
                            fields.forEach(field => {
                                const isOptional = field.placeholder && field.placeholder.toLowerCase().includes('optional');
                                if (show && !isOptional) {
                                    field.setAttribute('required', 'required');
                                } else {
                                    field.removeAttribute('required');
                                }
                            });
                        }
                        </script>
                        <div id="billingAddressForm" class="billing-address-form" style="display: none;">
                            <div class="form-group">
                                <label for="billing_country">Country/Region</label>
                                <select id="billing_country" name="billing_country">
                                    <option value="India" selected>India</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_first_name">First name</label>
                                    <input type="text" id="billing_first_name" name="billing_first_name" placeholder="First name">
                                </div>
                                <div class="form-group">
                                    <label for="billing_last_name">Last name</label>
                                    <input type="text" id="billing_last_name" name="billing_last_name" placeholder="Last name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billing_company">Company (optional)</label>
                                <input type="text" id="billing_company" name="billing_company" placeholder="Company (optional)">
                            </div>
                            <div class="form-group">
                                <label for="billing_address">Address</label>
                                <div style="position: relative;">
                                    <input type="text" id="billing_address" name="billing_address" placeholder="Address">
                                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #666;">🔍</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billing_apartment">Apartment, suite, etc. (optional)</label>
                                <input type="text" id="billing_apartment" name="billing_apartment" placeholder="Apartment, suite, etc. (optional)">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_city">City</label>
                                    <input type="text" id="billing_city" name="billing_city" placeholder="City">
                                </div>
                                <div class="form-group">
                                    <label for="billing_state">State</label>
                                    <select id="billing_state" name="billing_state">
                                        <option value="Gujarat" selected>Gujarat</option>
                                        <option value="Andhra Pradesh">Andhra Pradesh</option>
                                        <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                        <option value="Assam">Assam</option>
                                        <option value="Bihar">Bihar</option>
                                        <option value="Chhattisgarh">Chhattisgarh</option>
                                        <option value="Goa">Goa</option>
                                        <option value="Haryana">Haryana</option>
                                        <option value="Himachal Pradesh">Himachal Pradesh</option>
                                        <option value="Jharkhand">Jharkhand</option>
                                        <option value="Karnataka">Karnataka</option>
                                        <option value="Kerala">Kerala</option>
                                        <option value="Madhya Pradesh">Madhya Pradesh</option>
                                        <option value="Maharashtra">Maharashtra</option>
                                        <option value="Manipur">Manipur</option>
                                        <option value="Meghalaya">Meghalaya</option>
                                        <option value="Mizoram">Mizoram</option>
                                        <option value="Nagaland">Nagaland</option>
                                        <option value="Odisha">Odisha</option>
                                        <option value="Punjab">Punjab</option>
                                        <option value="Rajasthan">Rajasthan</option>
                                        <option value="Sikkim">Sikkim</option>
                                        <option value="Tamil Nadu">Tamil Nadu</option>
                                        <option value="Telangana">Telangana</option>
                                        <option value="Tripura">Tripura</option>
                                        <option value="Uttar Pradesh">Uttar Pradesh</option>
                                        <option value="Uttarakhand">Uttarakhand</option>
                                        <option value="West Bengal">West Bengal</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="billing_pin_code">PIN code</label>
                                    <input type="text" id="billing_pin_code" name="billing_pin_code" placeholder="PIN code" pattern="[0-9]{6}" maxlength="6">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billing_phone">Phone (optional)</label>
                                <div style="position: relative;">
                                    <input type="tel" id="billing_phone" name="billing_phone" placeholder="Phone (optional)">
                                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #666; cursor: help;" title="Phone number for billing">❓</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="complete-order-btn">PLACE ORDER</button>
                </form>

                <div class="checkout-footer-links">
                    <a href="#">Refund policy</a>
                    <a href="#">Shipping</a>
                    <a href="#">Privacy policy</a>
                    <a href="#">Terms of service</a>
                    <a href="#">Contact</a>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="order-summary">
                <h2 class="order-summary-title">Order Summary</h2>
                
                <div class="order-items">
                    <?php foreach ($cart_items as $item): 
                        $image_path = !empty($item['image']) ? 'uploads/products/' . $item['image'] : 'assets/images/beads.jpg';
                    ?>
                        <div class="order-item">
                            <div style="position: relative;">
                                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-image" onerror="this.src='assets/images/beads.jpg'">
                                <div class="order-item-quantity"><?= $item['quantity'] ?></div>
                            </div>
                            <div class="order-item-details">
                                <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="order-item-price">
                                    <?php if ($item['mrp'] > $item['price']): ?>
                                        <span class="order-item-price-original">Rs. <?= number_format($item['mrp'], 2) ?></span>
                                    <?php endif; ?>
                                    <span class="order-item-price-current">Rs. <?= number_format($item['price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php include 'includes/discount-code-component.php'; ?>

                <?php if ($applied_gift_card): ?>
                <div class="summary-row" id="gift-card-summary-row" style="background: #f0f7ff; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #0066cc;" data-gift-card-amount="<?= $gift_card_discount ?>">
                    <span class="summary-row-label" style="color: #0066cc; font-weight: 700;">🎁 Gift Card:</span>
                    <span class="summary-row-value" style="color: #0066cc; font-weight: 700;">- Rs. <span data-display-gift-card><?= number_format($gift_card_discount, 2) ?></span></span>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">Card: ****<?= htmlspecialchars(substr($applied_gift_card['card_number'] ?? '', -4)) ?></div>
                </div>
                <?php endif; ?>

                <div class="order-summary-totals">
                    <div class="summary-row">
                        <span class="summary-row-label">Subtotal (excl. GST):</span>
                        <span class="summary-row-value" data-display-subtotal-excl>Rs. <?= number_format($subtotal - ($subtotal * 12 / 112), 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">GST (12%):</span>
                        <span class="summary-row-value" data-display-gst>Rs. <?= number_format($subtotal * 12 / 112, 2) ?></span>
                    </div>
                    
                    <!-- Discount Row (shown/hidden by JavaScript) -->
                    <div class="summary-row" id="discount-row" style="<?= $discount > 0 ? 'display: flex;' : 'display: none;' ?> color: #28a745;">
                        <span class="summary-row-label" style="color: #28a745;">🏷️ Discount (<span id="discount-code-name"><?= $applied_discount ? htmlspecialchars($applied_discount['code']) : '' ?></span>):</span>
                        <span class="summary-row-value" id="discount-amount" data-display-discount>-Rs. <?= number_format($discount, 2) ?></span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-row-label">Shipping:</span>
                        <span class="summary-row-value" data-shipping-amount="<?= $shipping_cost ?>">
                            <?php if ($has_free_shipping): ?>
                                <span data-display-shipping>FREE</span>
                            <?php else: ?>
                                Rs. <span data-display-shipping><?= number_format($shipping_cost, 2) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="summary-row summary-row-total">
                        <span style="font-size: 20px;">TOTAL:</span>
                        <span style="font-size: 24px; color: #764ba2;" data-cart-subtotal="<?= $subtotal ?>" data-display-total>Rs. <?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method dropdown handler
    const paymentMethodSelect = document.getElementById('payment_method');
    const bankDetails = document.getElementById('bankDetails');
    const creditCardForm = document.getElementById('creditCardForm');
    const upiOptions = document.getElementById('upiOptions');
    
    // Function to update payment UI
    function updatePaymentUI() {
        const selectedMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
        
        // Hide all payment forms/messages
        if (bankDetails) bankDetails.style.display = 'none';
        if (creditCardForm) creditCardForm.style.display = 'none';
        if (upiOptions) upiOptions.style.display = 'none';
        
        // Remove required attributes from all payment fields
        const upiVPAField = document.getElementById('upi_vpa');
        const cardNumberField = document.getElementById('card_number');
        const nameOnCardField = document.getElementById('name_on_card');
        const expiryDateField = document.getElementById('expiry_date');
        const securityCodeField = document.getElementById('security_code');
        const cardTypeField = document.getElementById('card_type');
        
        if (upiVPAField) upiVPAField.removeAttribute('required');
        if (cardNumberField) cardNumberField.removeAttribute('required');
        if (nameOnCardField) nameOnCardField.removeAttribute('required');
        if (expiryDateField) expiryDateField.removeAttribute('required');
        if (securityCodeField) securityCodeField.removeAttribute('required');
        if (cardTypeField) cardTypeField.removeAttribute('required');
        
        // Show relevant form/message based on selection and set required fields
        if (selectedMethod === 'upi') {
            if (upiOptions) {
                upiOptions.style.display = 'block';
                if (upiVPAField) upiVPAField.setAttribute('required', 'required');
            }
        } else if (selectedMethod === 'credit_card') {
            if (creditCardForm) {
                creditCardForm.style.display = 'block';
                if (cardNumberField) cardNumberField.setAttribute('required', 'required');
                if (nameOnCardField) nameOnCardField.setAttribute('required', 'required');
                if (expiryDateField) expiryDateField.setAttribute('required', 'required');
                if (securityCodeField) securityCodeField.setAttribute('required', 'required');
                if (cardTypeField) cardTypeField.setAttribute('required', 'required');
            }
        } else if (selectedMethod === 'bank_deposit') {
            if (bankDetails) bankDetails.style.display = 'block';
        } else if (selectedMethod === 'gift_card') {
            const giftDiv = document.getElementById('giftCardPaymentForm');
            if (giftDiv) giftDiv.style.display = 'block';
        }
        // For COD, no additional fields needed
    }
    
    // Initialize payment UI - show UPI options by default since it's selected
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updatePaymentUI);
        // Call immediately to set initial state
        updatePaymentUI();
    }
    
    // Also run on window load as backup
    window.addEventListener('load', function() {
        if (paymentMethodSelect) {
            updatePaymentUI();
        }
    });
    
    // Phone number validation - only 10 digits
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = e.target.value.replace(/\D/g, '');
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
        
        phoneInput.addEventListener('keypress', function(e) {
            // Only allow numbers
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
    
    // Billing phone number validation
    const billingPhoneInput = document.getElementById('billing_phone');
    if (billingPhoneInput) {
        billingPhoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
    }
    
    // Card number formatting
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Expiry date formatting
    const expiryInput = document.getElementById('expiry_date');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // Indian States List
    const indianStates = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi', 'Puducherry'
    ];
    
    // Indian Cities with State Mapping (Comprehensive List)
    const cityStateMap = {
        // Gujarat
        'Ahmedabad': 'Gujarat', 'Surat': 'Gujarat', 'Vadodara': 'Gujarat', 'Rajkot': 'Gujarat', 
        'Bhavnagar': 'Gujarat', 'Jamnagar': 'Gujarat', 'Junagadh': 'Gujarat', 'Gandhinagar': 'Gujarat',
        'Anand': 'Gujarat', 'Nadiad': 'Gujarat', 'Bharuch': 'Gujarat', 'Navsari': 'Gujarat',
        'Surendranagar': 'Gujarat', 'Mehsana': 'Gujarat', 'Palanpur': 'Gujarat', 'Patan': 'Gujarat',
        'Porbandar': 'Gujarat', 'Veraval': 'Gujarat', 'Morbi': 'Gujarat', 'Bhuj': 'Gujarat',
        'Gondal': 'Gujarat', 'Jetpur': 'Gujarat', 'Dhoraji': 'Gujarat', 'Wankaner': 'Gujarat',
        'Amreli': 'Gujarat', 'Botad': 'Gujarat', 'Dahod': 'Gujarat', 'Godhra': 'Gujarat',
        'Himmatnagar': 'Gujarat', 'Modasa': 'Gujarat', 'Palanpur': 'Gujarat', 'Deesa': 'Gujarat',
        'Kadi': 'Gujarat', 'Kalol': 'Gujarat', 'Unjha': 'Gujarat', 'Visnagar': 'Gujarat',
        // Maharashtra
        'Mumbai': 'Maharashtra', 'Pune': 'Maharashtra', 'Nagpur': 'Maharashtra', 'Nashik': 'Maharashtra',
        'Aurangabad': 'Maharashtra', 'Solapur': 'Maharashtra', 'Thane': 'Maharashtra', 'Kalyan': 'Maharashtra',
        'Vasai': 'Maharashtra', 'Panvel': 'Maharashtra', 'Navi Mumbai': 'Maharashtra', 'Pimpri-Chinchwad': 'Maharashtra',
        'Kolhapur': 'Maharashtra', 'Sangli': 'Maharashtra', 'Satara': 'Maharashtra', 'Ratnagiri': 'Maharashtra',
        'Jalgaon': 'Maharashtra', 'Dhule': 'Maharashtra', 'Nanded': 'Maharashtra', 'Latur': 'Maharashtra',
        'Amravati': 'Maharashtra', 'Akola': 'Maharashtra', 'Chandrapur': 'Maharashtra', 'Wardha': 'Maharashtra',
        'Yavatmal': 'Maharashtra', 'Bhusawal': 'Maharashtra', 'Ichalkaranji': 'Maharashtra', 'Jalna': 'Maharashtra',
        'Beed': 'Maharashtra', 'Parbhani': 'Maharashtra', 'Osmanabad': 'Maharashtra', 'Nandurbar': 'Maharashtra',
        // Delhi/NCR
        'Delhi': 'Delhi', 'New Delhi': 'Delhi', 'Gurgaon': 'Haryana', 'Noida': 'Uttar Pradesh',
        'Faridabad': 'Haryana', 'Ghaziabad': 'Uttar Pradesh', 'Meerut': 'Uttar Pradesh', 'Saharanpur': 'Uttar Pradesh',
        'Gurugram': 'Haryana', 'Sonipat': 'Haryana', 'Panipat': 'Haryana', 'Karnal': 'Haryana',
        // Karnataka
        'Bangalore': 'Karnataka', 'Mysore': 'Karnataka', 'Hubli': 'Karnataka', 'Mangalore': 'Karnataka',
        'Belgaum': 'Karnataka', 'Gulbarga': 'Karnataka', 'Davangere': 'Karnataka', 'Shimoga': 'Karnataka',
        'Bellary': 'Karnataka', 'Bijapur': 'Karnataka', 'Raichur': 'Karnataka', 'Tumkur': 'Karnataka',
        'Udupi': 'Karnataka', 'Manipal': 'Karnataka', 'Chitradurga': 'Karnataka', 'Hassan': 'Karnataka',
        'Mandya': 'Karnataka', 'Chikmagalur': 'Karnataka', 'Kolar': 'Karnataka', 'Chikkaballapur': 'Karnataka',
        // Tamil Nadu
        'Chennai': 'Tamil Nadu', 'Coimbatore': 'Tamil Nadu', 'Madurai': 'Tamil Nadu', 'Tiruchirappalli': 'Tamil Nadu',
        'Salem': 'Tamil Nadu', 'Tirunelveli': 'Tamil Nadu', 'Erode': 'Tamil Nadu', 'Vellore': 'Tamil Nadu',
        'Thanjavur': 'Tamil Nadu', 'Tuticorin': 'Tamil Nadu', 'Dindigul': 'Tamil Nadu', 'Karur': 'Tamil Nadu',
        'Nagercoil': 'Tamil Nadu', 'Kanchipuram': 'Tamil Nadu', 'Kumbakonam': 'Tamil Nadu', 'Tiruppur': 'Tamil Nadu',
        // Telangana
        'Hyderabad': 'Telangana', 'Warangal': 'Telangana', 'Nizamabad': 'Telangana', 'Karimnagar': 'Telangana',
        'Ramagundam': 'Telangana', 'Khammam': 'Telangana', 'Mahbubnagar': 'Telangana', 'Nalgonda': 'Telangana',
        'Adilabad': 'Telangana', 'Siddipet': 'Telangana', 'Suryapet': 'Telangana', 'Miryalaguda': 'Telangana',
        // West Bengal
        'Kolkata': 'West Bengal', 'Howrah': 'West Bengal', 'Durgapur': 'West Bengal', 'Asansol': 'West Bengal',
        'Siliguri': 'West Bengal', 'Bardhaman': 'West Bengal', 'Kharagpur': 'West Bengal', 'Malda': 'West Bengal',
        'Jalpaiguri': 'West Bengal', 'Krishnanagar': 'West Bengal', 'Berhampore': 'West Bengal', 'Raiganj': 'West Bengal',
        // Rajasthan
        'Jaipur': 'Rajasthan', 'Jodhpur': 'Rajasthan', 'Kota': 'Rajasthan', 'Bikaner': 'Rajasthan',
        'Ajmer': 'Rajasthan', 'Udaipur': 'Rajasthan', 'Bhilwara': 'Rajasthan', 'Alwar': 'Rajasthan',
        'Sikar': 'Rajasthan', 'Pali': 'Rajasthan', 'Sri Ganganagar': 'Rajasthan', 'Bharatpur': 'Rajasthan',
        'Hanumangarh': 'Rajasthan', 'Churu': 'Rajasthan', 'Jhunjhunu': 'Rajasthan', 'Nagaur': 'Rajasthan',
        // Uttar Pradesh
        'Lucknow': 'Uttar Pradesh', 'Kanpur': 'Uttar Pradesh', 'Agra': 'Uttar Pradesh', 'Varanasi': 'Uttar Pradesh',
        'Allahabad': 'Uttar Pradesh', 'Bareilly': 'Uttar Pradesh', 'Aligarh': 'Uttar Pradesh', 'Moradabad': 'Uttar Pradesh',
        'Saharanpur': 'Uttar Pradesh', 'Gorakhpur': 'Uttar Pradesh', 'Faizabad': 'Uttar Pradesh', 'Jhansi': 'Uttar Pradesh',
        'Muzaffarnagar': 'Uttar Pradesh', 'Mathura': 'Uttar Pradesh', 'Rampur': 'Uttar Pradesh', 'Shahjahanpur': 'Uttar Pradesh',
        // Punjab
        'Amritsar': 'Punjab', 'Ludhiana': 'Punjab', 'Jalandhar': 'Punjab', 'Patiala': 'Punjab',
        'Bathinda': 'Punjab', 'Hoshiarpur': 'Punjab', 'Moga': 'Punjab', 'Pathankot': 'Punjab',
        'Sangrur': 'Punjab', 'Batala': 'Punjab', 'Muktsar': 'Punjab', 'Barnala': 'Punjab',
        // Madhya Pradesh
        'Bhopal': 'Madhya Pradesh', 'Indore': 'Madhya Pradesh', 'Gwalior': 'Madhya Pradesh', 'Jabalpur': 'Madhya Pradesh',
        'Ujjain': 'Madhya Pradesh', 'Sagar': 'Madhya Pradesh', 'Ratlam': 'Madhya Pradesh', 'Rewa': 'Madhya Pradesh',
        'Satna': 'Madhya Pradesh', 'Burhanpur': 'Madhya Pradesh', 'Khandwa': 'Madhya Pradesh', 'Chhindwara': 'Madhya Pradesh',
        // Bihar
        'Patna': 'Bihar', 'Gaya': 'Bihar', 'Bhagalpur': 'Bihar', 'Muzaffarpur': 'Bihar',
        'Purnia': 'Bihar', 'Darbhanga': 'Bihar', 'Arrah': 'Bihar', 'Begusarai': 'Bihar',
        'Katihar': 'Bihar', 'Munger': 'Bihar', 'Chapra': 'Bihar', 'Saharsa': 'Bihar',
        // Odisha
        'Bhubaneswar': 'Odisha', 'Cuttack': 'Odisha', 'Rourkela': 'Odisha', 'Berhampur': 'Odisha',
        'Sambalpur': 'Odisha', 'Puri': 'Odisha', 'Baleshwar': 'Odisha', 'Baripada': 'Odisha',
        // Assam
        'Guwahati': 'Assam', 'Silchar': 'Assam', 'Dibrugarh': 'Assam', 'Jorhat': 'Assam',
        'Nagaon': 'Assam', 'Tinsukia': 'Assam', 'Tezpur': 'Assam', 'Bongaigaon': 'Assam',
        // Kerala
        'Thiruvananthapuram': 'Kerala', 'Kochi': 'Kerala', 'Kozhikode': 'Kerala', 'Thrissur': 'Kerala',
        'Kollam': 'Kerala', 'Kannur': 'Kerala', 'Alappuzha': 'Kerala', 'Palakkad': 'Kerala',
        'Kottayam': 'Kerala', 'Malappuram': 'Kerala', 'Manjeri': 'Kerala', 'Thalassery': 'Kerala',
        // Andhra Pradesh
        'Visakhapatnam': 'Andhra Pradesh', 'Vijayawada': 'Andhra Pradesh', 'Guntur': 'Andhra Pradesh', 'Nellore': 'Andhra Pradesh',
        'Rajahmundry': 'Andhra Pradesh', 'Kurnool': 'Andhra Pradesh', 'Tirupati': 'Andhra Pradesh', 'Kakinada': 'Andhra Pradesh',
        'Kadapa': 'Andhra Pradesh', 'Anantapur': 'Andhra Pradesh', 'Eluru': 'Andhra Pradesh', 'Ongole': 'Andhra Pradesh',
        // Chhattisgarh
        'Raipur': 'Chhattisgarh', 'Bilaspur': 'Chhattisgarh', 'Durg': 'Chhattisgarh', 'Korba': 'Chhattisgarh',
        'Bhilai': 'Chhattisgarh', 'Rajnandgaon': 'Chhattisgarh', 'Jagdalpur': 'Chhattisgarh', 'Ambikapur': 'Chhattisgarh',
        // Uttarakhand
        'Dehradun': 'Uttarakhand', 'Haridwar': 'Uttarakhand', 'Roorkee': 'Uttarakhand', 'Haldwani': 'Uttarakhand',
        'Rudrapur': 'Uttarakhand', 'Kashipur': 'Uttarakhand', 'Rishikesh': 'Uttarakhand', 'Mussoorie': 'Uttarakhand',
        // Jharkhand
        'Ranchi': 'Jharkhand', 'Jamshedpur': 'Jharkhand', 'Dhanbad': 'Jharkhand', 'Bokaro': 'Jharkhand',
        'Hazaribagh': 'Jharkhand', 'Giridih': 'Jharkhand', 'Deoghar': 'Jharkhand', 'Phusro': 'Jharkhand',
        // Others
        'Chandigarh': 'Chandigarh', 'Imphal': 'Manipur', 'Aizawl': 'Mizoram', 'Shillong': 'Meghalaya',
        'Kohima': 'Nagaland', 'Agartala': 'Tripura', 'Gangtok': 'Sikkim', 'Itanagar': 'Arunachal Pradesh',
        'Panaji': 'Goa', 'Puducherry': 'Puducherry', 'Kavaratti': 'Lakshadweep', 'Port Blair': 'Andaman and Nicobar Islands'
    };
    
    // Create cities array from map
    const indianCities = Object.keys(cityStateMap);
    
    // Autocomplete function for cities (with state auto-fill)
    function setupCityAutocomplete(inputId, suggestionsId, stateInputId) {
        const input = document.getElementById(inputId);
        const suggestionsDiv = document.getElementById(suggestionsId);
        const stateInput = document.getElementById(stateInputId);
        let selectedIndex = -1;
        
        if (!input || !suggestionsDiv) return;
        
        input.addEventListener('input', function(e) {
            const value = e.target.value.trim();
            selectedIndex = -1;
            
            if (value.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            const valueLower = value.toLowerCase();
            
            // Filter matching cities (case-insensitive, contains or starts with)
            const matches = indianCities.filter(city => {
                const cityLower = city.toLowerCase();
                return cityLower.startsWith(valueLower) || cityLower.includes(valueLower);
            }).slice(0, 20); // Show more suggestions
            
            if (matches.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            // Display suggestions with state info
            suggestionsDiv.innerHTML = matches.map((city, index) => {
                const state = cityStateMap[city] || '';
                return `<div class="suggestion-item" data-index="${index}" data-value="${city}" data-state="${state}">${city}${state ? ' <span style="color: #666; font-size: 12px;">(' + state + ')</span>' : ''}</div>`;
            }).join('');
            
            suggestionsDiv.style.display = 'block';
            suggestionsDiv.style.zIndex = '10000';
        });
        
        // Handle suggestion clicks
        suggestionsDiv.addEventListener('click', function(e) {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                const city = item.getAttribute('data-value');
                const state = item.getAttribute('data-state');
                input.value = city;
                if (stateInput && state) {
                    stateInput.value = state;
                }
                suggestionsDiv.style.display = 'none';
                input.focus();
            }
        });
        
        // Handle keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = suggestionsDiv.querySelectorAll('.suggestion-item');
            
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                items.forEach((item, idx) => {
                    item.classList.toggle('selected', idx === selectedIndex);
                });
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                items.forEach((item, idx) => {
                    item.classList.toggle('selected', idx === selectedIndex);
                });
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                const city = items[selectedIndex].getAttribute('data-value');
                const state = items[selectedIndex].getAttribute('data-state');
                input.value = city;
                if (stateInput && state) {
                    stateInput.value = state;
                }
                suggestionsDiv.style.display = 'none';
            } else if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
    }
    
    // Autocomplete function for states
    function setupAutocomplete(inputId, suggestionsId, dataArray) {
        const input = document.getElementById(inputId);
        const suggestionsDiv = document.getElementById(suggestionsId);
        let selectedIndex = -1;
        
        if (!input || !suggestionsDiv) return;
        
        input.addEventListener('input', function(e) {
            const value = e.target.value.trim().toLowerCase();
            selectedIndex = -1;
            
            if (value.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            // Filter matching items
            const matches = dataArray.filter(item => 
                item.toLowerCase().startsWith(value)
            ).slice(0, 15);
            
            if (matches.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
            
            // Display suggestions
            suggestionsDiv.innerHTML = matches.map((item, index) => 
                `<div class="suggestion-item" data-index="${index}" data-value="${item}">${item}</div>`
            ).join('');
            
            suggestionsDiv.style.display = 'block';
        });
        
        // Handle suggestion clicks
        suggestionsDiv.addEventListener('click', function(e) {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                input.value = item.getAttribute('data-value');
                suggestionsDiv.style.display = 'none';
                input.focus();
            }
        });
        
        // Handle keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = suggestionsDiv.querySelectorAll('.suggestion-item');
            
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                items.forEach((item, idx) => {
                    item.classList.toggle('selected', idx === selectedIndex);
                });
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                items.forEach((item, idx) => {
                    item.classList.toggle('selected', idx === selectedIndex);
                });
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                input.value = items[selectedIndex].getAttribute('data-value');
                suggestionsDiv.style.display = 'none';
            } else if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
    }
    
    // Setup autocomplete for city (with state auto-fill) and state
    // Make sure elements exist before setting up
    const cityInput = document.getElementById('city');
    const citySuggestions = document.getElementById('citySuggestions');
    const stateInput = document.getElementById('state');
    const stateSuggestions = document.getElementById('stateSuggestions');
    
    if (cityInput && citySuggestions) {
        setupCityAutocomplete('city', 'citySuggestions', 'state');
    }
    
    if (stateInput && stateSuggestions) {
        setupAutocomplete('state', 'stateSuggestions', indianStates);
    }
    
    // Also setup for billing address if exists
    const billingCityInput = document.getElementById('billing_city');
    const billingStateInput = document.getElementById('billing_state');
    if (billingCityInput) {
        const billingCitySuggestions = document.createElement('div');
        billingCitySuggestions.id = 'billingCitySuggestions';
        billingCitySuggestions.className = 'autocomplete-suggestions';
        billingCitySuggestions.style.display = 'none';
        billingCityInput.parentElement.style.position = 'relative';
        billingCityInput.parentElement.appendChild(billingCitySuggestions);
        setupCityAutocomplete('billing_city', 'billingCitySuggestions', 'billing_state');
    }
    
    if (billingStateInput && billingStateInput.tagName === 'SELECT') {
        // Convert select to input for billing state
        const billingStateInputNew = document.createElement('input');
        billingStateInputNew.type = 'text';
        billingStateInputNew.id = 'billing_state';
        billingStateInputNew.name = 'billing_state';
        billingStateInputNew.className = billingStateInput.className;
        billingStateInputNew.placeholder = 'Start typing state name...';
        billingStateInputNew.autocomplete = 'off';
        billingStateInput.parentElement.replaceChild(billingStateInputNew, billingStateInput);
        
        const billingStateSuggestions = document.createElement('div');
        billingStateSuggestions.id = 'billingStateSuggestions';
        billingStateSuggestions.className = 'autocomplete-suggestions';
        billingStateSuggestions.style.display = 'none';
        billingStateInputNew.parentElement.style.position = 'relative';
        billingStateInputNew.parentElement.appendChild(billingStateSuggestions);
        setupAutocomplete('billing_state', 'billingStateSuggestions', indianStates);
    }
    
    // UPI App Selection
    // UPI App Selection and Validation
    const upiAppButtons = document.querySelectorAll('.upi-app-btn');
    const upiVPAInput = document.getElementById('upiVPAInput');
    const upiVPAField = document.getElementById('upi_vpa');
    const upiValidationMsg = document.getElementById('upiValidationMsg');
    let selectedUPIApp = null;
    
    // UPI Validation Function
    function validateUPI(upiId) {
        // UPI ID format: username@provider
        // Valid providers: paytm, ybl, upi, okaxis, okicici, axl, payu, etc.
        const upiPattern = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;
        
        if (!upiId || upiId.trim() === '') {
            return { valid: false, message: 'UPI ID is required' };
        }
        
        const trimmedUPI = upiId.trim();
        
        if (!upiPattern.test(trimmedUPI)) {
            return { valid: false, message: 'Invalid UPI ID format. Use format: yourname@paytm' };
        }
        
        // Check common UPI providers
        const validProviders = ['paytm', 'ybl', 'upi', 'okaxis', 'okicici', 'axl', 'payu', 'bhim', 'phonepe', 'googlepay', 'gpay'];
        const provider = trimmedUPI.split('@')[1].toLowerCase();
        
        if (!validProviders.includes(provider)) {
            return { valid: false, message: 'Invalid UPI provider. Use: @paytm, @ybl, @upi, @okaxis, @okicici, etc.' };
        }
        
        return { valid: true, message: 'Valid UPI ID ✓' };
    }
    
    // UPI ID validation on input
    if (upiVPAField) {
        upiVPAField.addEventListener('input', function(e) {
            const upiId = e.target.value;
            const validation = validateUPI(upiId);
            
            if (upiId.length > 0) {
                if (validation.valid) {
                    upiValidationMsg.innerHTML = '<span style="color: #28a745;">✓ ' + validation.message + '</span>';
                    upiVPAField.style.borderColor = '#28a745';
                } else {
                    upiValidationMsg.innerHTML = '<span style="color: #dc3545;">✗ ' + validation.message + '</span>';
                    upiVPAField.style.borderColor = '#dc3545';
                }
            } else {
                upiValidationMsg.innerHTML = '';
                upiVPAField.style.borderColor = '';
            }
        });
        
        upiVPAField.addEventListener('blur', function(e) {
            const upiId = e.target.value;
            if (upiId.length > 0) {
                const validation = validateUPI(upiId);
                if (!validation.valid) {
                    upiVPAField.setCustomValidity(validation.message);
                } else {
                    upiVPAField.setCustomValidity('');
                }
            }
        });
    }
    
    // UPI App button selection
    upiAppButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove selected class from all buttons
            upiAppButtons.forEach(b => b.classList.remove('selected'));
            
            // Add selected class to clicked button
            this.classList.add('selected');
            selectedUPIApp = this.getAttribute('data-upi-app');
            
            // UPI input is always visible and required
            if (upiVPAField) {
                upiVPAField.required = true;
                upiVPAField.focus();
            }
        });
    });
    
    // Track if a Razorpay payment method button was clicked
    let razorpayMethodClicked = null;
    
    // Global function to restore button state (prevents freezing)
    function restoreButtonState() {
        const payNowBtn = document.querySelector('button[type="submit"]');
        if (payNowBtn) {
            payNowBtn.disabled = false;
            payNowBtn.innerHTML = 'PLACE ORDER';
        }
        
        // Clean up any overlays
        setTimeout(() => {
            const razorpayOverlays = document.querySelectorAll('.razorpay-container, .razorpay-overlay, [class*="razorpay"]');
            razorpayOverlays.forEach(overlay => {
                if (overlay && overlay.parentNode) {
                    overlay.remove();
                }
            });
            
            const backdrops = document.querySelectorAll('.razorpay-checkout-backdrop, .swal2-backdrop-show');
            backdrops.forEach(backdrop => backdrop.remove());
            
            document.body.style.overflow = '';
            document.body.style.pointerEvents = '';
            document.body.removeAttribute('aria-hidden');
            document.body.focus();
        }, 50);
    }
    
    // Handle "PLACE ORDER" button click
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        // Check if gift card covers full amount
        const giftCardPayment = document.querySelector('input[name="payment_method"][value="gift_card"]');
        if (giftCardPayment) {
            // Gift card covers full amount, no payment validation needed
            // Show confirmation that amount will be deducted
            if (!confirm('Amount will be deducted from your gift card when you place this order. Continue?')) {
                e.preventDefault();
                return false;
            }
            return; // Allow form to submit
        }
        
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        const paymentMethodSelect = document.getElementById('payment_method');
        
        // Check dropdown if radio buttons not found
        if (!selectedPayment && paymentMethodSelect) {
            const paymentMethod = paymentMethodSelect.value;
            if (!paymentMethod || paymentMethod === '') {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }
        } else if (!selectedPayment) {
            e.preventDefault();
            alert('Please select a payment method');
            return;
        }
        
        const paymentMethod = selectedPayment ? selectedPayment.value : paymentMethodSelect.value;
        
        // Validate UPI payment
        if (paymentMethod === 'upi') {
            const upiVPA = document.getElementById('upi_vpa')?.value.trim();
            if (!upiVPA) {
                e.preventDefault();
                alert('Please enter your UPI ID');
                document.getElementById('upi_vpa')?.focus();
                return;
            }
            
            const validation = validateUPI(upiVPA);
            if (!validation.valid) {
                e.preventDefault();
                alert(validation.message);
                document.getElementById('upi_vpa')?.focus();
                return;
            }
        }
        
        // If a Razorpay method button was already clicked, don't process form submission
        if (razorpayMethodClicked) {
            e.preventDefault();
            return; // Payment already initiated via button
        }
        
        // For non-Razorpay methods (UPI, COD, Credit Card, Bank Deposit), allow form to submit normally
        if (paymentMethod !== 'razorpay') {
            // Show loading state
            const payNowBtn = document.querySelector('button[type="submit"]');
            if (payNowBtn) {
                payNowBtn.disabled = true;
                payNowBtn.innerHTML = 'Processing...';
            }
            
            // Validate form fields for non-Razorpay methods
            if (paymentMethod === 'credit_card') {
                const cardNumber = document.getElementById('card_number')?.value.trim();
                const expiryDate = document.getElementById('expiry_date')?.value.trim();
                const securityCode = document.getElementById('security_code')?.value.trim();
                const nameOnCard = document.getElementById('name_on_card')?.value.trim();
                
                if (!cardNumber || !expiryDate || !securityCode || !nameOnCard) {
                    e.preventDefault();
                    alert('Please fill in all credit card details');
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = 'PLACE ORDER';
                    }
                    return;
                }
                
                // Validate card number
                const cardNumberDigits = cardNumber.replace(/\s/g, '');
                if (cardNumberDigits.length < 13 || cardNumberDigits.length > 19 || !/^\d+$/.test(cardNumberDigits)) {
                    e.preventDefault();
                    alert('Please enter a valid card number');
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = 'PLACE ORDER';
                    }
                    return;
                }
                
                // Validate expiry date
                if (!/^\d{2}\s*\/\s*\d{2}$/.test(expiryDate)) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY)');
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = 'PLACE ORDER';
                    }
                    return;
                }
                
                // Validate security code
                if (!/^\d{3,4}$/.test(securityCode)) {
                    e.preventDefault();
                    alert('Please enter a valid security code (CVV/CVC)');
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = 'PLACE ORDER';
                    }
                    return;
                }
            }
            
            // Allow form to submit normally - don't prevent default
            // Form will submit to process-checkout.php which will redirect to view_receipt.php
            return; // Let the form submit naturally
        }
        
        // For Razorpay, prevent default and handle via JavaScript
        e.preventDefault();
        
        // Safety timeout - restore button after 30 seconds if something goes wrong
        const safetyTimeout = setTimeout(() => {
            console.warn('Payment process timeout - restoring button');
            restoreButtonState();
            razorpayMethodClicked = null;
            selectedUPIApp = null;
        }, 30000);
        
        // Clear timeout on successful payment
        window.clearPaymentTimeout = function() {
            clearTimeout(safetyTimeout);
        };
        
        if (paymentMethod === 'razorpay') {
            // Check if UPI app is selected for UPI payment
            if (selectedUPIApp) {
                if (selectedUPIApp === 'other') {
                    const vpa = upiVPAField?.value.trim();
                    if (!vpa || !/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/.test(vpa)) {
                        alert('Please enter a valid UPI ID (e.g., yourname@paytm)');
                        return;
                    }
                    initiateRazorpayUPI(vpa);
                } else {
                    initiateRazorpayUPI(selectedUPIApp);
                }
            } else {
                // No UPI app selected - prompt user to select payment method
                alert('Please select a UPI app (PhonePe, Google Pay, Paytm, etc.) to proceed with payment');
                return;
            }
        } else if (paymentMethod === 'credit_card') {
            // Validate credit card form
            const cardNumber = document.getElementById('card_number')?.value.trim();
            const expiryDate = document.getElementById('expiry_date')?.value.trim();
            const securityCode = document.getElementById('security_code')?.value.trim();
            const nameOnCard = document.getElementById('name_on_card')?.value.trim();
            
            if (!cardNumber || !expiryDate || !securityCode || !nameOnCard) {
                alert('Please fill in all credit card details');
                return;
            }
            
            // Validate card number (basic validation - 13-19 digits)
            const cardNumberDigits = cardNumber.replace(/\s/g, '');
            if (cardNumberDigits.length < 13 || cardNumberDigits.length > 19 || !/^\d+$/.test(cardNumberDigits)) {
                alert('Please enter a valid card number');
                return;
            }
            
            // Validate expiry date format (MM/YY)
            if (!/^\d{2}\s*\/\s*\d{2}$/.test(expiryDate)) {
                alert('Please enter a valid expiry date (MM/YY)');
                return;
            }
            
            // Validate security code (3-4 digits)
            if (!/^\d{3,4}$/.test(securityCode)) {
                alert('Please enter a valid security code (CVV/CVC)');
                return;
            }
            
    });
    
    // Function to map UPI app names to Razorpay format
    function mapUPIAppToRazorpay(appName) {
        const appMap = {
            'phonepe': 'phonepe',
            'googlepay': 'googlepay',
            'paytm': 'paytm',
            'bhim': 'bhim',
            'amazonpay': 'amazonpay'
        };
        return appMap[appName] || null;
    }
    
    // Function to initiate Razorpay UPI payment (opens in modal, no page redirect)
    function initiateRazorpayUPI(upiAppOrVPA) {
        console.log('Initiating UPI payment with:', upiAppOrVPA);
        
        // Check if Razorpay is loaded
        if (typeof Razorpay === 'undefined') {
            alert('Payment gateway is loading. Please wait a moment and try again.');
            selectedUPIApp = null;
            return;
        }
        
        // Show loading state
        const payNowBtn = document.querySelector('button[type="submit"]');
        const originalBtnText = payNowBtn ? payNowBtn.innerHTML : '';
        if (payNowBtn) {
            payNowBtn.disabled = true;
            payNowBtn.innerHTML = 'Processing...';
        }
        
        // Create order first
        const formData = new FormData();
        formData.append('action', 'create_razorpay_order');
        formData.append('amount', <?= ($total * 100) ?>); // Amount in paise
        formData.append('currency', 'INR');
        
        fetch('create-razorpay-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Invalid response from server. Please check your Razorpay configuration.');
                });
            }
            return response.json();
        })
        .then(data => {
            // Restore button
            if (payNowBtn) {
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = originalBtnText;
            }
            
            if (data.success && data.order_id) {
                // Initialize Razorpay Checkout (opens in modal overlay on same page, no redirect)
                const options = {
                    key: data.key_id, // Your Razorpay Key ID
                    amount: data.amount,
                    currency: data.currency,
                    name: 'Craft Royale',
                    description: 'Order Payment',
                    order_id: data.order_id,
                    handler: function(response) {
                        // Handle successful payment
                        handlePaymentSuccess(response, 'razorpay_upi');
                    },
                    prefill: {
                        name: '<?= htmlspecialchars($user['name'] ?? '') ?>',
                        email: '<?= htmlspecialchars($user['email'] ?? '') ?>',
                        contact: '<?= htmlspecialchars($user['phone'] ?? '') ?>'
                    },
                    theme: {
                        color: '#0066cc'
                    },
                    modal: {
                        ondismiss: function() {
                            console.log('Payment modal closed');
                            selectedUPIApp = null; // Reset on dismissal
                            restoreButtonState();
                        }
                    },
                    // Enable UPI payment method
                    method: {
                        upi: true
                    },
                    notes: {
                        upi_app: upiAppOrVPA,
                        selected_app: upiAppOrVPA
                    }
                };
                
                // If custom UPI VPA is provided (for "Other UPI"), prefill it
                if (upiAppOrVPA && upiAppOrVPA.includes('@')) {
                    options.prefill.upi = upiAppOrVPA;
                    console.log('Using custom UPI VPA:', upiAppOrVPA);
                } else {
                    // Map UPI app name to Razorpay format
                    const mappedApp = mapUPIAppToRazorpay(upiAppOrVPA);
                    if (mappedApp) {
                        // For specific UPI apps, we can try to pre-select them
                        // Note: Razorpay will show all UPI apps, but we can hint at the preferred one
                        options.notes.preferred_upi_app = mappedApp;
                        console.log('Preferred UPI app:', mappedApp);
                    }
                }
                
                // Open Razorpay checkout modal (stays on same page, no redirect)
                // User can select PhonePe, Google Pay, Paytm, etc. in the modal
                try {
                    const razorpay = new Razorpay(options);
                    razorpay.on('payment.failed', function(response) {
                        const errorMsg = response.error?.description || response.error?.reason || 'Payment failed. Please try again.';
                        
                        // CRITICAL: Clean up everything to prevent freezing
                        selectedUPIApp = null;
                        
                        // Remove any Razorpay modal overlays
                        setTimeout(() => {
                            const razorpayOverlays = document.querySelectorAll('.razorpay-container, .razorpay-overlay, [class*="razorpay"]');
                            razorpayOverlays.forEach(overlay => {
                                if (overlay && overlay.parentNode) {
                                    overlay.remove();
                                }
                            });
                            
                            // Remove any backdrop
                            const backdrops = document.querySelectorAll('.razorpay-checkout-backdrop, .swal2-backdrop-show');
                            backdrops.forEach(backdrop => backdrop.remove());
                            
                            // Restore body overflow
                            document.body.style.overflow = '';
                            document.body.style.pointerEvents = '';
                            
                            // Remove aria-hidden from body
                            document.body.removeAttribute('aria-hidden');
                            
                            // Remove focus from any hidden elements
                            const allInputs = document.querySelectorAll('input, textarea, select, button');
                            allInputs.forEach(input => {
                                if (input === document.activeElement && input.closest('.razorpay-container')) {
                                    input.blur();
                                }
                            });
                            
                            // Force body focus
                            document.body.focus();
                        }, 100);
                        
                        // Restore button immediately
                        if (payNowBtn) {
                            payNowBtn.disabled = false;
                            payNowBtn.innerHTML = originalBtnText;
                        }
                        
                        // Show error message
                        alert('Payment failed: ' + errorMsg);
                    });
                    razorpay.open(); // Opens modal overlay, user selects UPI app inside
                    console.log('Razorpay modal opened for UPI payment');
                } catch (error) {
                    console.error('Razorpay initialization error:', error);
                    alert('Error initializing payment. Please check your Razorpay configuration.');
                    selectedUPIApp = null;
                    // Restore button
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = originalBtnText;
                    }
                }
            } else {
                const errorMsg = data.message || 'Error creating payment order. Please check your Razorpay API credentials.';
                alert(errorMsg);
                selectedUPIApp = null;
            }
        })
        .catch(error => {
            console.error('Payment initiation error:', error);
            const errorMsg = error.message || 'Error initiating payment. Please try again.';
            alert(errorMsg);
            selectedUPIApp = null;
            // Restore button
            if (payNowBtn) {
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = originalBtnText;
            }
        });
    }
    
    // Function to initiate Razorpay payment for cards/wallets/netbanking (opens in modal)
    function initiateRazorpayPayment(method) {
        // Check if Razorpay is loaded
        if (typeof Razorpay === 'undefined') {
            alert('Payment gateway is loading. Please wait a moment and try again.');
            razorpayMethodClicked = null;
            return;
        }
        
        // Show loading state
        const payNowBtn = document.querySelector('button[type="submit"]');
        const originalBtnText = payNowBtn ? payNowBtn.innerHTML : '';
        if (payNowBtn) {
            payNowBtn.disabled = true;
            payNowBtn.innerHTML = 'Processing...';
        }
        
        const formData = new FormData();
        formData.append('action', 'create_razorpay_order');
        formData.append('amount', <?= ($total * 100) ?>);
        formData.append('currency', 'INR');
        
        fetch('create-razorpay-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Invalid response from server. Please check your Razorpay configuration.');
                });
            }
            return response.json();
        })
        .then(data => {
            // Restore button
            if (payNowBtn) {
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = originalBtnText;
            }
            
            if (data.success && data.order_id) {
                // Store the method for later use
                const currentMethod = method;
                
                const options = {
                    key: data.key_id,
                    amount: data.amount,
                    currency: data.currency,
                    name: 'Craft Royale',
                    description: 'Order Payment',
                    order_id: data.order_id,
                    handler: function(response) {
                        // Pass the method to success handler
                        handlePaymentSuccess(response, currentMethod);
                    },
                    prefill: {
                        name: '<?= htmlspecialchars($user['name'] ?? '') ?>',
                        email: '<?= htmlspecialchars($user['email'] ?? '') ?>',
                        contact: '<?= htmlspecialchars($user['phone'] ?? '') ?>'
                    },
                    theme: {
                        color: '#0066cc'
                    },
                    modal: {
                        ondismiss: function() {
                            console.log('Payment modal closed');
                            razorpayMethodClicked = null; // Reset flag when modal is dismissed
                            restoreButtonState();
                        }
                    },
                    // Properly configure payment method
                    method: {
                        [method]: true
                    }
                };
                
                try {
                    const razorpay = new Razorpay(options);
                    razorpay.on('payment.failed', function(response) {
                        const errorMsg = response.error?.description || response.error?.reason || 'Payment failed. Please try again.';
                        
                        // CRITICAL: Clean up everything to prevent freezing
                        razorpayMethodClicked = null;
                        
                        // Remove any Razorpay modal overlays
                        setTimeout(() => {
                            const razorpayOverlays = document.querySelectorAll('.razorpay-container, .razorpay-overlay, [class*="razorpay"]');
                            razorpayOverlays.forEach(overlay => {
                                if (overlay && overlay.parentNode) {
                                    overlay.remove();
                                }
                            });
                            
                            // Remove any backdrop
                            const backdrops = document.querySelectorAll('.razorpay-checkout-backdrop, .swal2-backdrop-show');
                            backdrops.forEach(backdrop => backdrop.remove());
                            
                            // Restore body overflow
                            document.body.style.overflow = '';
                            document.body.style.pointerEvents = '';
                            
                            // Remove aria-hidden from body
                            document.body.removeAttribute('aria-hidden');
                            
                            // Remove focus from any hidden elements
                            const allInputs = document.querySelectorAll('input, textarea, select, button');
                            allInputs.forEach(input => {
                                if (input === document.activeElement && input.closest('.razorpay-container')) {
                                    input.blur();
                                }
                            });
                            
                            // Force body focus
                            document.body.focus();
                        }, 100);
                        
                        // Restore button immediately
                        if (payNowBtn) {
                            payNowBtn.disabled = false;
                            payNowBtn.innerHTML = originalBtnText;
                        }
                        
                        // Show error message
                        alert('Payment failed: ' + errorMsg);
                    });
                    razorpay.open(); // Opens in modal overlay, no page redirect
                } catch (error) {
                    console.error('Razorpay initialization error:', error);
                    razorpayMethodClicked = null;
                    
                    // Clean up any overlays
                    setTimeout(() => {
                        document.body.style.overflow = '';
                        document.body.style.pointerEvents = '';
                        document.body.removeAttribute('aria-hidden');
                    }, 50);
                    
                    // Restore button
                    if (payNowBtn) {
                        payNowBtn.disabled = false;
                        payNowBtn.innerHTML = originalBtnText;
                    }
                    
                    alert('Error initializing payment. Please check your Razorpay configuration.');
                }
            } else {
                const errorMsg = data.message || 'Error creating payment order. Please check your Razorpay API credentials.';
                alert(errorMsg);
                razorpayMethodClicked = null;
            }
        })
        .catch(error => {
            console.error('Payment initiation error:', error);
            const errorMsg = error.message || 'Error initiating payment. Please try again.';
            alert(errorMsg);
            razorpayMethodClicked = null;
            // Restore button
            if (payNowBtn) {
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = originalBtnText;
            }
        });
    }
    
    // Handle payment success
    function handlePaymentSuccess(response, paymentMethod) {
        console.log('Payment successful:', response);
        
        // Submit form with payment details
        const form = document.getElementById('checkoutForm');
        if (!form) {
            alert('Error: Form not found. Please refresh the page.');
            razorpayMethodClicked = null;
            return;
        }
        
        // Remove any existing payment inputs to avoid duplicates
        const existingInputs = form.querySelectorAll('input[name="razorpay_payment_id"], input[name="razorpay_order_id"], input[name="razorpay_signature"], input[name="payment_method_used"]');
        existingInputs.forEach(input => input.remove());
        
        const paymentInput = document.createElement('input');
        paymentInput.type = 'hidden';
        paymentInput.name = 'razorpay_payment_id';
        paymentInput.value = response.razorpay_payment_id;
        form.appendChild(paymentInput);
        
        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'razorpay_order_id';
        orderInput.value = response.razorpay_order_id;
        form.appendChild(orderInput);
        
        const signatureInput = document.createElement('input');
        signatureInput.type = 'hidden';
        signatureInput.name = 'razorpay_signature';
        signatureInput.value = response.razorpay_signature;
        form.appendChild(signatureInput);
        
        // Add payment method indicator
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = 'payment_method_used';
        methodInput.value = paymentMethod || razorpayMethodClicked || 'razorpay';
        form.appendChild(methodInput);
        
        // Reset the flag
        razorpayMethodClicked = null;
        
        // Submit to process checkout
        form.submit();
    }
});
</script>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="assets/js/discount-code.js"></script>
<script src="assets/js/gift-card.js"></script>

<?php include "includes/footer.php"; ?>
