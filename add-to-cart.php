<?php
session_start();
include "includes/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($quantity <= 0) $quantity = 1;

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Fetch product details
$query = "SELECT * FROM products WHERE id = $product_id AND status = 'active'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
    exit;
}

$stock = isset($product['stock']) ? (int)$product['stock'] : 0;
if ($stock <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product out of stock']);
    exit;
}

$product_key = $product_id;
if (isset($_SESSION['cart'][$product_key])) {
    $new_quantity = $_SESSION['cart'][$product_key]['quantity'] + $quantity;
    if ($new_quantity > $stock) $new_quantity = $stock;
    $_SESSION['cart'][$product_key]['quantity'] = $new_quantity;
} else {
    $_SESSION['cart'][$product_key] = [
        'id' => $product_id,
        'name' => $product['product_name'] ?? $product['name'] ?? 'Product',
        'price' => floatval($product['price'] ?? 0),
        'mrp' => floatval($product['mrp'] ?? $product['price'] ?? 0),
        'image' => $product['image'] ?? '',
        'quantity' => $quantity,
        'stock' => $stock
    ];
}

// Calculate totals
$total_inclusive = 0;
$total_items = 0;
$cart_items_display = [];

foreach ($_SESSION['cart'] as $key => $item) {
    if (is_array($item)) {
        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $total_inclusive += $item_price * $item_quantity;
        $total_items += $item_quantity;
        
        $cart_items_display[] = [
            'id' => $item['id'],
            'session_key' => $key,
            'name' => $item['name'],
            'price' => $item_price,
            'mrp' => $item['mrp'],
            'image' => $item['image'],
            'quantity' => $item_quantity,
            'total' => $item_price * $item_quantity
        ];
    }
}

$gst_amount = ($total_inclusive * 12) / 112;
$subtotal_exclusive = $total_inclusive - $gst_amount;

ob_start();
if (empty($cart_items_display)):
?>
    <div class="empty-cart-sidebar"><p>Your cart is empty</p></div>
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
                            <button type="button" class="qty-delete" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>" title="Delete item">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="qty-decrease" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>">-</button>
                            <input type="number" id="qty_<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" class="qty-input" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>" data-price="<?= $item['price'] ?>" readonly>
                            <button type="button" class="qty-increase" data-product-id="<?= $item['id'] ?>" data-session-key="<?= htmlspecialchars($item['session_key']) ?>">+</button>
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
<?php endif;
$cart_html = ob_get_clean();

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $total_items,
    'cart_html' => $cart_html
]);
