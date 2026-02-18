# 🎟️ DISCOUNT CODE INTEGRATION GUIDE

## ✅ Files Created for Cart Integration

### 1. JavaScript Handler
**File:** `assets/js/discount-code.js`
- Manages discount code application
- Real-time cart total updates
- Session management
- Error handling

### 2. API Endpoints
**Files:**
- `get_applied_discount.php` - Get current discount from session
- `remove_discount_code.php` - Remove applied discount
- `apply_discount_code.php` - Validate and apply discount (already created)

### 3. UI Component
**File:** `includes/discount-code-component.php`
- Beautiful discount input form
- Applied discount badge
- Discount row for cart summary
- Responsive design

---

## 🚀 How to Integrate into Cart Page

### Step 1: Add to Cart Page (`cart.php`)

**Add after the cart items table, before the cart summary:**

```php
<?php include 'includes/discount-code-component.php'; ?>
```

### Step 2: Update Cart Summary

**Find your cart summary section and add these data attributes:**

```html
<!-- Subtotal -->
<div class="cart-summary-row">
    <span>Subtotal:</span>
    <span data-cart-subtotal="<?php echo $subtotal; ?>" data-display-subtotal>
        ₹<?php echo number_format($subtotal, 2); ?>
    </span>
</div>

<!-- Discount Row (automatically shown/hidden) -->
<?php 
$discount = 0;
if (isset($_SESSION['applied_discount'])) {
    $discount = $_SESSION['applied_discount']['discount_amount'];
}
?>

<!-- Total -->
<div class="cart-summary-row total">
    <span>Total:</span>
    <span data-display-total>
        ₹<?php echo number_format($subtotal - $discount, 2); ?>
    </span>
</div>
```

---

## 🛒 Integration Example for Cart.php

### Complete Integration Code:

```php
<?php
session_start();
include "includes/db.php";

// Calculate cart totals
$subtotal = 0;
// ... your existing cart calculation code ...

// Calculate discount
$discount = 0;
if (isset($_SESSION['applied_discount'])) {
    $discount_code = $_SESSION['applied_discount'];
    $discount = ($subtotal * $discount_code['percentage']) / 100;
    
    // Apply max discount limit if set
    if (isset($discount_code['max_discount_amount']) && $discount_code['max_discount_amount'] > 0) {
        $discount = min($discount, $discount_code['max_discount_amount']);
    }
    
    // Update discount amount in session
    $_SESSION['applied_discount']['discount_amount'] = $discount;
}

$total = $subtotal - $discount;
?>

<!-- In your HTML -->
<div class="cart-summary">
    <h3>Cart Summary</h3>
    
    <div class="summary-row">
        <span>Subtotal:</span>
        <span data-cart-subtotal="<?php echo $subtotal; ?>" data-display-subtotal>
            ₹<?php echo number_format($subtotal, 2); ?>
        </span>
    </div>
    
    <!-- Discount Code Component -->
    <?php include 'includes/discount-code-component.php'; ?>
    
    <div class="summary-row total">
        <span>Total:</span>
        <span data-display-total>
            ₹<?php echo number_format($total, 2); ?>
        </span>
    </div>
    
    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
</div>
```

---

## 🎨 Features

### Visual Design
- ✅ Beautiful gradient input field
- ✅ Green success badge when applied
- ✅ Red remove button
- ✅ Smooth animations
- ✅ Responsive layout

### Functionality
- ✅ Real-time validation
- ✅ Instant cart total updates
- ✅ Session persistence
- ✅ Error messages
- ✅ Success feedback
- ✅ Easy removal

### Validation
- ✅ Code exists and is active
- ✅ Within valid date range
- ✅ Meets minimum order amount
- ✅ Under usage limit
- ✅ Correct percentage calculation
- ✅ Max discount cap

---

## 📱 How It Works

### User Flow:

1. **User enters code** (e.g., FIRSTSALE)
2. **Clicks "Apply"** button
3. **JavaScript validates** with server
4. **Server checks:**
   - Code exists
   - Is active
   - Valid dates
   - Min order met
   - Usage limit
5. **If valid:**
   - Discount calculated
   - Stored in session
   - UI updates instantly
   - Totals recalculated
6. **User sees:**
   - Green success badge
   - Discount amount
   - Updated total

---

## 🔧 Technical Details

### Session Storage:

```php
$_SESSION['applied_discount'] = [
    'id' => 1,
    'code' => 'FIRSTSALE',
    'percentage' => 10,
    'discount_amount' => 100.00,
    'description' => 'First Sale - 10% Off'
];
```

### Discount Calculation:

```php
// Calculate discount
$discount = ($subtotal * $percentage) / 100;

// Apply max discount cap
if ($max_discount_amount && $discount > $max_discount_amount) {
    $discount = $max_discount_amount;
}

// Final total
$total = $subtotal - $discount;
```

---

## 🎯 Integration Checklist

### Cart Page (`cart.php`):
- [ ] Include discount component
- [ ] Add data attributes to subtotal
- [ ] Add data attributes to total
- [ ] Include discount row
- [ ] Test discount application
- [ ] Test discount removal

### Checkout Page (`checkout.php`):
- [ ] Include discount component
- [ ] Add data attributes to subtotal
- [ ] Add data attributes to total
- [ ] Include discount row
- [ ] Apply discount to final order
- [ ] Track usage after order

### Cart Sidebar:
- [ ] Include discount component (optional)
- [ ] Add data attributes
- [ ] Test in sidebar

---

## 📊 Example Display

### Before Discount Applied:
```
┌────────────────────────────────┐
│  [Enter code] [Apply Button]   │
├────────────────────────────────┤
│  Subtotal:        ₹1,000.00   │
│  Total:           ₹1,000.00   │
└────────────────────────────────┘
```

### After Discount Applied (FIRSTSALE - 10%):
```
┌────────────────────────────────┐
│  ✓ FIRSTSALE                   │
│  10% OFF Applied    [Remove]   │
├────────────────────────────────┤
│  Subtotal:        ₹1,000.00   │
│  Discount (FIRSTSALE): -₹100.00│
│  Total:           ₹900.00     │
└────────────────────────────────┘
```

---

## 🚀 Quick Start

### 1. Add to Cart Page:

```php
<!-- After cart table, before summary -->
<?php include 'includes/discount-code-component.php'; ?>
```

### 2. Update Summary Section:

```php
<div data-cart-subtotal="<?php echo $subtotal; ?>" data-display-subtotal>
    ₹<?php echo number_format($subtotal, 2); ?>
</div>

<div data-display-total>
    ₹<?php echo number_format($total, 2); ?>
</div>
```

### 3. Test:

1. Add products to cart
2. Go to cart page
3. Enter code: `FIRSTSALE`
4. Click Apply
5. See discount applied!

---

## ✅ Summary

**Created:**
- ✅ JavaScript discount manager
- ✅ API endpoints (get, apply, remove)
- ✅ Beautiful UI component
- ✅ Session management
- ✅ Real-time updates

**Features:**
- ✅ Instant validation
- ✅ Smooth animations
- ✅ Error handling
- ✅ Mobile responsive
- ✅ Easy integration

**Ready to use in:**
- ✅ Cart page
- ✅ Checkout page
- ✅ Cart sidebar

---

**Last Updated:** 2026-02-12 23:52 IST  
**Status:** ✅ READY FOR INTEGRATION
