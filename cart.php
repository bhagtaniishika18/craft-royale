<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart totals
$subtotal = 0;
$total_items = 0;
$cart_items = [];

foreach ($_SESSION['cart'] as $product_id => $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $total_items += $item['quantity'];
    
    $cart_items[] = [
        'id' => $product_id,
        'name' => $item['name'],
        'price' => $item['price'],
        'mrp' => $item['mrp'] ?? 0,
        'image' => $item['image'],
        'quantity' => $item['quantity'],
        'total' => $item_total
    ];
}

// Calculate free shipping threshold
$free_shipping_threshold = 750;
$remaining_for_free_shipping = max(0, $free_shipping_threshold - $subtotal);

// Calculate discount
$discount = 0;
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
}

$total = $subtotal - $discount;
?>

<style>
    .cart-page {
        min-height: 80vh;
        background: #f9f9f9;
        padding: 40px 0;
    }

    .cart-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .cart-banner {
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 40px;
    }

    .cart-banner-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .cart-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }

    .cart-banner-content {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
    }

    .cart-banner h1 {
        font-size: 72px;
        font-weight: 800;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 4px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    }

    .cart-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 16px;
        color: #fff;
    }

    .cart-breadcrumb a {
        color: #fff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .cart-breadcrumb a:hover {
        color: #2fc7b4;
    }

    .cart-breadcrumb-separator {
        color: #fff;
        margin: 0 4px;
    }

    .cart-breadcrumb-current {
        color: #2fc7b4;
        font-weight: 600;
    }

    .cart-content {
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .cart-table thead {
        background: #f5f5f5;
        border-bottom: 2px solid #e0e0e0;
    }

    .cart-table th {
        padding: 15px;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 14px;
        color: #2b2b2b;
    }

    .cart-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: middle;
    }

    .cart-product {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cart-product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        background: #f5f5f5;
    }

    .cart-product-info {
        flex: 1;
    }

    .cart-product-name {
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .cart-product-color {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .cart-product-actions {
        display: flex;
        gap: 10px;
        margin-top: 8px;
    }

    .cart-product-actions a {
        color: #2fc7b4;
        text-decoration: none;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cart-product-actions a:hover {
        text-decoration: underline;
    }

    .cart-price {
        font-weight: 600;
        color: #2b2b2b;
    }

    .cart-price .original-price {
        color: #999;
        text-decoration: line-through;
        font-size: 14px;
        margin-right: 8px;
    }

    .cart-price .current-price {
        color: #dc3545;
        font-size: 16px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-btn {
        background: #f0f0f0;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .quantity-btn:hover {
        background: #e0e0e0;
    }

    .quantity-btn.remove {
        color: #dc3545;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px;
        font-weight: 600;
    }

    .cart-total {
        font-weight: 700;
        font-size: 18px;
        color: #2b2b2b;
    }







    .order-notes {
        margin: 30px 0;
    }

    .order-notes textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
    }

    .coupon-section {
        margin: 30px 0;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .coupon-section p {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 14px;
    }

    .coupon-input-group {
        display: flex;
        gap: 10px;
    }

    .coupon-input {
        flex: 1;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
    }

    .coupon-btn {
        padding: 12px 24px;
        background: #2fc7b4;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .coupon-btn:hover {
        background: #2fa76b;
    }

    .cart-summary {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 25px;
        margin-top: 30px;
    }

    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .cart-summary-label {
        font-weight: 600;
        color: #2b2b2b;
    }

    .cart-summary-value {
        font-weight: 700;
        font-size: 20px;
        color: #2b2b2b;
    }

    .checkout-btn {
        width: 100%;
        padding: 16px;
        background: #2fc7b4;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .checkout-btn:hover {
        background: #2fa76b;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
    }

    .tax-note {
        font-size: 12px;
        color: #666;
        margin-top: 10px;
        text-align: center;
    }

    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-cart i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
    }

    .empty-cart h2 {
        color: #2b2b2b;
        margin-bottom: 10px;
    }

    .empty-cart p {
        color: #666;
        margin-bottom: 30px;
    }

    .continue-shopping-btn {
        display: inline-block;
        padding: 12px 24px;
        background: #2fc7b4;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .continue-shopping-btn:hover {
        background: #2fa76b;
    }

    .shipping-estimate {
        margin: 30px 0;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .shipping-estimate h3 {
        margin: 0 0 15px 0;
        font-size: 18px;
        color: #2b2b2b;
    }

    .shipping-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .shipping-form-group {
        display: flex;
        flex-direction: column;
    }

    .shipping-form-group label {
        margin-bottom: 5px;
        font-weight: 600;
        color: #2b2b2b;
        font-size: 14px;
    }

    .shipping-form-group select,
    .shipping-form-group input {
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-family: inherit;
    }

    .estimate-btn {
        padding: 12px 24px;
        background: #2fc7b4;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .estimate-btn:hover {
        background: #2fa76b;
    }

    /* Login Popup */
    .login-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .login-popup.active {
        display: flex;
    }

    .login-popup-content {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        animation: popupSlideIn 0.3s ease;
    }

    @keyframes popupSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .login-popup-content h3 {
        margin: 0 0 15px 0;
        color: #2b2b2b;
        font-size: 24px;
    }

    .login-popup-content p {
        margin: 0 0 25px 0;
        color: #666;
        font-size: 16px;
    }

    .login-popup-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .login-popup-btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .login-popup-btn.login {
        background: #2fc7b4;
        color: #fff;
    }

    .login-popup-btn.login:hover {
        background: #2fa76b;
    }

    .login-popup-btn.cancel {
        background: #f0f0f0;
        color: #2b2b2b;
    }

    .login-popup-btn.cancel:hover {
        background: #e0e0e0;
    }

    @media (max-width: 768px) {
        .cart-table {
            display: block;
            overflow-x: auto;
        }

        .cart-product {
            flex-direction: column;
            align-items: flex-start;
        }

        .shipping-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="cart-page">
    <div class="cart-container">
        <div class="cart-banner">
            <video class="cart-banner-video" autoplay muted loop playsinline>
                <source src="assets/images//cart.mp4" type="video/mp4">
                <!-- Fallback image if video doesn't load -->
                <img src="assets/images/beads.jpg" alt="Shopping Cart Background">
            </video>
            <div class="cart-banner-content">
                <h1>SHOPPING CART</h1>
                <nav class="cart-breadcrumb">
                    <a href="index.php">Home</a> <span class="cart-breadcrumb-separator">></span> <span class="cart-breadcrumb-current">Shopping Cart</span>
                </nav>
            </div>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="cart-content">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <a href="products.php" class="continue-shopping-btn">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th>PRICE</th>
                            <th>QUANTITY</th>
                            <th>TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $image_path = !empty($item['image']) ? 'uploads/products/' . $item['image'] : 'assets/images/beads.jpg';
                        ?>
                            <tr data-product-id="<?= $item['id'] ?>">
                                <td>
                                    <div class="cart-product">
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-product-image" onerror="this.src='assets/images/beads.jpg'">
                                        <div class="cart-product-info">
                                            <div class="cart-product-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="cart-product-actions">
                                                <a href="#" onclick="editProduct(<?= $item['id'] ?>); return false;"><i class="fas fa-edit"></i> edit</a>
                                                <a href="#" onclick="removeProduct(<?= $item['id'] ?>); return false;"><i class="fas fa-trash"></i> delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cart-price">
                                        <?php if ($item['mrp'] > $item['price']): ?>
                                            <span class="original-price">₹<?= number_format($item['mrp'], 2) ?></span>
                                        <?php endif; ?>
                                        <span class="current-price">₹<?= number_format($item['price'], 2) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, 'decrease')">-</button>
                                        <input type="number" class="quantity-input" id="cart_qty_<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" onchange="updateQuantity(<?= $item['id'] ?>, 'update', this.value)">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, 'increase')">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="cart-total">₹<?= number_format($item['total'], 2) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="order-notes">
                    <label><strong>Add Order Note</strong></label>
                    <textarea name="order_note" placeholder="How can we help you?"></textarea>
                </div>

                <!-- Discount Code Section -->
                <?php include 'includes/discount-code-component.php'; ?>

                <?php if ($subtotal < 750): ?>
                <div class="shipping-estimate">
                    <h3>Estimate shipping</h3>
                    <form class="shipping-form" id="shippingForm">
                        <div class="shipping-form-group">
                            <label>Country</label>
                            <input type="text" name="country" value="India" readonly style="background: #f5f5f5; cursor: not-allowed;">
                        </div>
                        <div class="shipping-form-group">
                            <label>State</label>
                            <select name="province" id="stateSelect" required>
                                <option value="">Select State</option>
                                <!-- All 28 States in alphabetical order -->
                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
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
                                <!-- All 8 Union Territories in alphabetical order -->
                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                <option value="Chandigarh">Chandigarh</option>
                                <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                <option value="Ladakh">Ladakh</option>
                                <option value="Lakshadweep">Lakshadweep</option>
                                <option value="Puducherry">Puducherry</option>
                            </select>
                        </div>
                        <div class="shipping-form-group">
                            <label>Zip code</label>
                            <input type="text" name="zip_code" id="zipCode" placeholder="Enter zip code" pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        <button type="button" class="estimate-btn" onclick="estimateShipping()">ESTIMATE</button>
                    </form>
                    <div id="shippingResults" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0; color: #2b2b2b;">Shipping Rates</h4>
                        <div id="shippingRatesList"></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="cart-summary">
                    <div class="cart-summary-row">
                        <span class="cart-summary-label">Subtotal (excl. GST):</span>
                        <span class="cart-summary-value" id="cartSummarySubtotal" data-cart-subtotal="<?= $subtotal ?>" data-display-subtotal-excl>₹<?= number_format($subtotal - ($subtotal * 12 / 112), 2) ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="cart-summary-label">GST (12%):</span>
                        <span class="cart-summary-value" id="cartSummaryGst" data-display-gst>₹<?= number_format($subtotal * 12 / 112, 2) ?></span>
                    </div>
                    
                    <!-- Discount Row (shown/hidden by JavaScript) -->
                    <div class="cart-summary-row" id="discount-row" style="<?= $discount > 0 ? 'display: flex;' : 'display: none;' ?> color: #28a745; border-top: 1px dashed #e0e0e0; padding-top: 10px;">
                        <span class="cart-summary-label" style="color: #28a745;">
                            <i class="fas fa-tag"></i> Discount (<span id="discount-code-name"><?= $applied_discount ? htmlspecialchars($applied_discount['code']) : '' ?></span>):
                        </span>
                        <span class="cart-summary-value" id="discount-amount" data-display-discount style="color: #28a745;">-₹<?= number_format($discount, 2) ?></span>
                    </div>
                    
                    <div class="cart-summary-row" style="border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 10px;">
                        <span class="cart-summary-label" style="font-size: 20px;">TOTAL:</span>
                        <span class="cart-summary-value" id="cartSummaryTotal" data-display-total style="font-size: 26px; color: #764ba2;">₹<?= number_format($total, 2) ?></span>
                    </div>
                    <p class="tax-note" style="margin-top: 20px;">Tax included and shipping calculated at checkout</p>
                    <button class="checkout-btn" onclick="checkout()">
                        <i class="fas fa-shopping-cart"></i>
                        PROCEED TO CHECKOUT
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Login Popup -->
<div class="login-popup" id="loginPopup">
    <div class="login-popup-content">
        <h3>Login Required</h3>
        <p>Please login first to proceed with checkout.</p>
        <div class="login-popup-buttons">
            <button class="login-popup-btn login" onclick="goToLogin()">Login</button>
            <button class="login-popup-btn cancel" onclick="closeLoginPopup()">Cancel</button>
        </div>
    </div>
</div>

<script>
function updateQuantity(productId, action, value = null) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    let quantity = 1;
    const input = document.getElementById('cart_qty_' + productId) || document.querySelector(`tr[data-product-id="${productId}"] .quantity-input`);
    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
    
    if (!input) {
        console.error('Input not found for product:', productId);
        return false;
    }
    
    if (action === 'increase') {
        quantity = parseInt(input.value) + 1;
    } else if (action === 'decrease') {
        quantity = Math.max(1, parseInt(input.value) - 1);
    } else if (action === 'remove') {
        quantity = 0;
    } else if (action === 'update' && value !== null) {
        quantity = parseInt(value) || 1;
        if (quantity < 1) quantity = 1;
    }
    
    // Update input immediately for better UX
    if (quantity > 0) {
        input.value = quantity;
        
        // Update row total immediately
        if (row) {
            const priceElement = row.querySelector('.current-price');
            const totalElement = row.querySelector('.cart-total');
            
            if (priceElement && totalElement) {
                const priceText = priceElement.textContent.replace(/[^0-9.]/g, '');
                const price = parseFloat(priceText) || 0;
                const itemTotal = price * quantity;
                totalElement.textContent = '₹' + itemTotal.toFixed(2);
            }
        }
        
        // 🎯 REAL-TIME UPDATE - Update all totals instantly
        if (typeof updateSubtotalInstantly === 'function') updateSubtotalInstantly();
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('action', quantity === 0 ? 'remove' : 'update');
    
    fetch('update-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (quantity === 0) {
                // Product removed, reload to update cart
                location.reload();
            } else {
                // Update cart count in header
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                console.log('✅ Cart updated successfully without reload');
            }
        } else {
            alert(data.message || 'Error updating cart');
            location.reload(); // Reload to reset
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating cart');
        location.reload(); // Reload to reset
    });
    
    return false;
}

function removeProduct(productId) {
    if (confirm('Are you sure you want to remove this product from cart?')) {
        updateQuantity(productId, 'remove');
    }
}

// 🎯 REAL-TIME UPDATE FUNCTIONS - Execute on same click event
function calculateCurrentSubtotal() {
    let subtotal = 0;
    const cartRows = document.querySelectorAll('tr[data-product-id]');
    
    cartRows.forEach(row => {
        const priceElement = row.querySelector('.current-price');
        const quantityInput = row.querySelector('.quantity-input');
        
        if (priceElement && quantityInput) {
            const priceText = priceElement.textContent.replace(/[^0-9.]/g, '');
            const price = parseFloat(priceText) || 0;
            const qty = parseInt(quantityInput.value) || 0;
            subtotal += price * qty;
        }
    });
    
    return subtotal;
}

function updateSubtotalInstantly() {
    const totalInclusive = calculateCurrentSubtotal();
    const gst = (totalInclusive * 12) / 112;
    const subtotalExcl = totalInclusive - gst;
    
    const subtotalEl = document.getElementById('cartSummarySubtotal');
    const gstEl = document.getElementById('cartSummaryGst');
    const totalEl = document.getElementById('cartSummaryTotal');
    
    if (subtotalEl) subtotalEl.textContent = '₹' + subtotalExcl.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (gstEl) gstEl.textContent = '₹' + gst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (totalEl) totalEl.textContent = '₹' + totalInclusive.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    return totalInclusive;
}


function editProduct(productId) {
    window.location.href = 'product.php?id=' + productId;
}

function checkout() {
    // Check if user is logged in
    <?php if (!isset($_SESSION['user_id'])): ?>
        document.getElementById('loginPopup').classList.add('active');
    <?php else: ?>
        window.location.href = 'checkout.php';
    <?php endif; ?>
}

function goToLogin() {
    window.location.href = 'login.php';
}

function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('active');
}

// Close popup when clicking outside
document.getElementById('loginPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLoginPopup();
    }
});

// Shipping estimation function
function estimateShipping() {
    const state = document.getElementById('stateSelect').value;
    const zipCode = document.getElementById('zipCode').value;
    const resultsDiv = document.getElementById('shippingResults');
    const ratesList = document.getElementById('shippingRatesList');
    
    if (!state || !zipCode) {
        alert('Please select state and enter zip code');
        return;
    }
    
    if (zipCode.length !== 6 || !/^\d{6}$/.test(zipCode)) {
        alert('Please enter a valid 6-digit zip code');
        return;
    }
    
    // Get cart subtotal from PHP variable
    const cartSubtotal = <?= $subtotal ?>;
    const freeShippingThreshold = 750;
    const hasFreeShipping = cartSubtotal >= freeShippingThreshold;
    
    // Set shipping rates based on free shipping eligibility
    const prepaidRate = hasFreeShipping ? 0 : 100;
    const codRate = hasFreeShipping ? 100 : 200;
    const startingRate = hasFreeShipping ? 0 : 100;
    
    // Calculate delivery days based on state (warehouse in Gujarat)
    const deliveryDays = calculateDeliveryDays(state);
    
    // Show shipping rates
    ratesList.innerHTML = `
        <p style="margin: 0 0 15px 0; color: #666;">
            We found 2 shipping rates available for ${zipCode}, ${state}, India, starting at Rs. ${startingRate.toFixed(2)}.
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="padding: 12px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #2b2b2b;">Prepaid</strong>
                        ${hasFreeShipping ? '<span style="color: #28a745; font-size: 11px; margin-left: 8px;">(FREE SHIPPING)</span>' : ''}
                        <div style="font-size: 12px; color: #666; margin-top: 4px;">
                            Estimated delivery: ${deliveryDays.min}-${deliveryDays.max} days
                        </div>
                    </div>
                    <strong style="color: #2fc7b4; font-size: 18px;">Rs. ${prepaidRate.toFixed(2)}</strong>
                </div>
            </div>
            <div style="padding: 12px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #2b2b2b;">Cash on Delivery</strong>
                        <div style="font-size: 12px; color: #666; margin-top: 4px;">
                            Estimated delivery: ${deliveryDays.min}-${deliveryDays.max} days
                        </div>
                    </div>
                    <strong style="color: #2fc7b4; font-size: 18px;">Rs. ${codRate.toFixed(2)}</strong>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Calculate delivery days based on state (warehouse in Gujarat)
function calculateDeliveryDays(state) {
    // Warehouse is in Gujarat
    const gujaratStates = ['Gujarat'];
    const nearbyStates = ['Maharashtra', 'Rajasthan', 'Madhya Pradesh', 'Goa', 'Dadra and Nagar Haveli and Daman and Diu'];
    const mediumStates = ['Delhi', 'Haryana', 'Punjab', 'Uttar Pradesh', 'Himachal Pradesh', 'Uttarakhand', 'Chhattisgarh', 'Odisha', 'Jharkhand', 'Bihar', 'West Bengal', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Tamil Nadu', 'Kerala'];
    const farStates = ['Assam', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Tripura', 'Arunachal Pradesh', 'Sikkim', 'Jammu and Kashmir', 'Ladakh'];
    const islandStates = ['Andaman and Nicobar Islands', 'Lakshadweep'];
    const unionTerritories = ['Chandigarh', 'Puducherry'];
    
    if (gujaratStates.includes(state)) {
        return { min: 2, max: 3 }; // Same state - fastest
    } else if (nearbyStates.includes(state)) {
        return { min: 3, max: 5 }; // Nearby states
    } else if (mediumStates.includes(state)) {
        return { min: 4, max: 7 }; // Medium distance
    } else if (farStates.includes(state)) {
        return { min: 6, max: 10 }; // Far states
    } else if (islandStates.includes(state)) {
        return { min: 8, max: 12 }; // Islands - longest
    } else if (unionTerritories.includes(state)) {
        return { min: 3, max: 6 }; // Union territories
    } else {
        return { min: 5, max: 8 }; // Default
    }
}
</script>



<?php include "includes/footer.php"; ?>
