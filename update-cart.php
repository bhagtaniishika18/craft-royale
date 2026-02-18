<?php
session_start();
include "includes/db.php";

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$action = isset($_POST['action']) ? $_POST['action'] : 'update';

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Check if product exists in cart
$product_found = false;
$actual_key = null;

$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : null;
if ($session_key !== null && $session_key !== '' && $session_key !== 'null' && $session_key !== 'undefined' && isset($_SESSION['cart'][$session_key])) {
    $product_found = true;
    $actual_key = $session_key;
} else {
    if (isset($_SESSION['cart'][$product_id])) {
        $product_found = true;
        $actual_key = $product_id;
    } else {
        $product_id_str = (string)$product_id;
        if (isset($_SESSION['cart'][$product_id_str])) {
            $product_found = true;
            $actual_key = $product_id_str;
        } else {
            foreach ($_SESSION['cart'] as $key => $item) {
                if (is_array($item)) {
                    $item_id = isset($item['id']) ? (int)$item['id'] : 0;
                    if ($item_id === $product_id) {
                        $product_found = true;
                        $actual_key = $key;
                        break;
                    }
                }
            }
        }
    }
}

if (!$product_found) {
    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    exit;
}

if ($action === 'remove') {
    unset($_SESSION['cart'][$actual_key]);
} else {
    // Update quantity
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$actual_key]);
    } else {
        $stock = $_SESSION['cart'][$actual_key]['stock'] ?? 999;
        if ($quantity > $stock) $quantity = $stock;
        $_SESSION['cart'][$actual_key]['quantity'] = $quantity;
    }
}

// Calculate totals
$total_inclusive = 0;
$total_items = 0;
$cart_items_display = [];

foreach ($_SESSION['cart'] as $key => $item) {
    if (is_array($item)) {
        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $item_total = $item_price * $item_quantity;
        $total_inclusive += $item_total;
        $total_items += $item_quantity;
        
        $cart_items_display[] = [
            'id' => isset($item['id']) ? $item['id'] : $key,
            'session_key' => $key,
            'name' => isset($item['name']) ? $item['name'] : 'Product',
            'price' => $item_price,
            'mrp' => isset($item['mrp']) ? floatval($item['mrp']) : 0,
            'image' => isset($item['image']) ? $item['image'] : '',
            'quantity' => $item_quantity,
            'total' => $item_total
        ];
    }
}

$gst_amount = ($total_inclusive * 12) / 112;
$subtotal_exclusive = $total_inclusive - $gst_amount;

// Generate cart HTML
ob_start();
if (empty($cart_items_display)):
?>
    <div class="empty-cart-sidebar" style="text-align: center; padding: 60px 20px; color: #666;">
        <i class="fas fa-shopping-cart" style="font-size: 64px; color: #ccc; margin-bottom: 20px; display: block;"></i>
        <p style="font-size: 18px; color: #666; margin: 0; font-weight: 500;">Your cart is empty</p>
    </div>
<?php else: ?>
    <div class="cart-items-list">
        <?php foreach ($cart_items_display as $item): 
            $image_path = !empty($item['image']) ? 'uploads/products/' . $item['image'] : 'assets/images/beads.jpg';
        ?>
            <div class="cart-item" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>">
                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image" onerror="this.src='assets/images/beads.jpg'">
                <div class="cart-item-details">
                    <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="cart-item-price">
                        <?php if ($item['mrp'] > $item['price']): ?>
                            <span class="original">₹<?= number_format($item['mrp'], 2) ?></span>
                        <?php endif; ?>
                        <span class="current">₹<?= number_format($item['price'], 2) ?></span>
                    </div>
                    <div class="cart-item-quantity-wrapper">
                        <div class="cart-item-quantity">
                            <button type="button" class="qty-delete" 
                                    data-product-id="<?= $item['id'] ?>" 
                                    data-session-key="<?= htmlspecialchars($item['session_key']) ?>" 
                                    onclick="if(typeof window.removeCartProduct==='function'){window.removeCartProduct(<?= $item['id'] ?>,'<?= htmlspecialchars($item['session_key']) ?>');return false;}else{console.error('removeCartProduct not found');}"
                                    title="Delete item">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="qty-decrease" 
                                    data-product-id="<?= $item['id'] ?>" 
                                    data-session-key="<?= htmlspecialchars($item['session_key']) ?>"
                                    onclick="if(typeof window.updateCartQuantity==='function'){window.updateCartQuantity(<?= $item['id'] ?>,'decrease',null,'<?= htmlspecialchars($item['session_key']) ?>');return false;}else{console.error('updateCartQuantity not found');}">-</button>
                            <input type="number" id="qty_<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" class="qty-input" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>" data-price="<?= $item['price'] ?>" readonly>
                            <button type="button" class="qty-increase" 
                                    data-product-id="<?= $item['id'] ?>" 
                                    data-session-key="<?= htmlspecialchars($item['session_key']) ?>"
                                    onclick="if(typeof window.updateCartQuantity==='function'){window.updateCartQuantity(<?= $item['id'] ?>,'increase',null,'<?= htmlspecialchars($item['session_key']) ?>');return false;}else{console.error('updateCartQuantity not found');}">+</button>
                        </div>
                        <div class="cart-item-subtotal" style="font-size: 13px; font-weight: 700; color: #764ba2; margin-top: 8px;">
                            Item Total: ₹<span class="item-total-val" id="item_total_<?= $item['id'] ?>"><?= number_format($item['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SUBTOTAL SECTION -->
    <div class="cart-totals-section" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; padding: 20px; margin-top: 20px; border: 2px solid #dee2e6;">
        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 2px dashed #dee2e6;">
            <span style="font-size: 16px; font-weight: 600; color: #495057;">Subtotal (excl. GST):</span>
            <span class="cart-subtotal" id="sidebarSubtotalExcl" style="font-size: 18px; font-weight: 700; color: #2b2b2b;">₹<?= number_format($subtotal_exclusive, 2) ?></span>
        </div>

        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 2px dashed #dee2e6;">
            <span style="font-size: 14px; font-weight: 500; color: #6c757d;">GST (12%):</span>
            <span class="cart-gst" id="sidebarGstAmount" style="font-size: 15px; font-weight: 600; color: #28a745;">₹<?= number_format($gst_amount, 2) ?></span>
        </div>

        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0 0 0;">
            <span style="font-size: 18px; font-weight: 700; color: #2b2b2b; text-transform: uppercase;">Total:</span>
            <span class="cart-total-amount" id="sidebarTotalIncl" style="font-size: 24px; font-weight: 800; color: #764ba2;">₹<?= number_format($total_inclusive, 2) ?></span>
        </div>
    </div>

    <!-- CART ACTION ICONS -->
    <div class="cart-action-icons">
        <button type="button" id="orderNoteIcon" class="cart-action-icon" onclick="window.showOrderNoteModal()" title="Add Note"><span class="icon-emoji">📝</span></button>
        <button type="button" id="estimateIcon" class="cart-action-icon" onclick="window.showEstimateShippingModal()" title="Shipping"><span class="icon-emoji">🚚</span></button>
        <button type="button" id="couponIcon" class="cart-action-icon" onclick="window.showCouponModal()" title="Coupon"><span class="icon-emoji">🎟️</span></button>
        <button type="button" id="giftCardIcon" class="cart-action-icon" onclick="window.showGiftCardModal()" title="Gift Card"><span class="icon-emoji">🎁</span></button>
    </div>

    <div class="cart-sidebar-footer" style="padding: 20px; border-top: 1px solid #eee;">
        <div class="cart-sidebar-buttons" style="display: flex; flex-direction: column; gap: 10px;">
            <a href="cart.php" class="cart-sidebar-btn view-cart" style="text-align: center; background: #eee; color: #333; padding: 15px; border-radius: 10px; font-weight: 700;">VIEW CART</a>
            <button type="button" class="cart-sidebar-btn checkout" onclick="location.href='checkout.php'" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px; border-radius: 10px; font-weight: 700;">CHECK OUT</button>
        </div>
    </div>
<?php endif;
$cart_html = ob_get_clean();

echo json_encode([
    'success' => true,
    'subtotal' => $total_inclusive, // We return inclusive total as subtotal for compatibility with older scripts
    'cart_count' => $total_items,
    'cart_html' => $cart_html
]);
