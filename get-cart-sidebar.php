<?php
// Start session and ensure we get fresh data
session_start();
// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Type: text/html; charset=UTF-8');
include "includes/db.php";

// Initialize cart items for display
$total_inclusive = 0;
$cart_total_items = 0;
$cart_items_display = [];

if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (!is_array($item)) continue;

        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;

        $cart_total_items += $item_quantity;
        $total_inclusive += $item_price * $item_quantity;

        $display_id = is_numeric($product_id) ? (int)$product_id : $product_id;
        
        $cart_items_display[] = [
            'id' => $display_id,
            'session_key' => $product_id,
            'name' => isset($item['name']) ? $item['name'] : 'Product',
            'price' => $item_price,
            'mrp' => isset($item['mrp']) ? floatval($item['mrp']) : 0,
            'image' => isset($item['image']) ? $item['image'] : '',
            'quantity' => $item_quantity,
            'total' => $item_price * $item_quantity
        ];
    }
}

$gst_amount = ($total_inclusive * 12) / 112;
$subtotal_exclusive = $total_inclusive - $gst_amount;

// Fetch recommended products
$recommended_products = [];
if (isset($conn)) {
    $cart_product_ids = [];
    foreach ($cart_items_display as $item) {
        $cart_product_ids[] = (int)$item['id'];
    }
    
    if (!empty($cart_product_ids)) {
        $exclude_ids = implode(',', array_map('intval', $cart_product_ids));
        $recommended_query = "SELECT * FROM products WHERE id NOT IN ($exclude_ids) AND status = 'active' ORDER BY RAND() LIMIT 6";
    } else {
        $recommended_query = "SELECT * FROM products WHERE status = 'active' ORDER BY RAND() LIMIT 6";
    }
    $recommended_result = mysqli_query($conn, $recommended_query);
    
    if ($recommended_result) {
        while ($product = mysqli_fetch_assoc($recommended_result)) {
            $recommended_products[] = [
                'id' => $product['id'],
                'name' => $product['product_name'] ?? $product['name'] ?? 'Product',
                'price' => floatval($product['price'] ?? 0),
                'mrp' => floatval($product['mrp'] ?? $product['price'] ?? 0),
                'image' => $product['image'] ?? ''
            ];
        }
    }
}
?>

<!-- ATTRACTIVE SHOPPING CART TITLE -->
<div class="cart-sidebar-title" style="
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 25px 20px;
    margin: 0 0 20px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
">
    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: rotate 20s linear infinite;"></div>
    <div style="position: relative; z-index: 1;">
        <div style="font-size: 36px; margin-bottom: 8px; animation: bounce 2s infinite;">🛍️</div>
        <h2 style="
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            font-family: 'Arial Black', sans-serif;
        ">SHOPPING CART</h2>
    </div>
</div>

<?php if (empty($cart_items_display)): ?>
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
                            <input type="number" id="qty_<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" class="qty-input" 
                                   data-product-id="<?= $item['id'] ?>" 
                                   data-session-key="<?= htmlspecialchars($item['session_key']) ?>" 
                                   data-price="<?= $item['price'] ?>" 
                                   readonly>
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
    <div class="cart-totals-section" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; padding: 20px; margin-top: 20px; border: 2px solid #dee2e6; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 2px dashed #dee2e6;">
            <span style="font-size: 15px; font-weight: 600; color: #495057;">Subtotal (excl. GST):</span>
            <span class="cart-subtotal" id="sidebarSubtotalExcl" style="font-size: 17px; font-weight: 700; color: #2b2b2b;">₹<?= number_format($subtotal_exclusive, 2) ?></span>
        </div>

        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 2px dashed #dee2e6;">
            <span style="font-size: 14px; font-weight: 500; color: #6c757d;">GST (12%):</span>
            <span class="cart-gst" id="sidebarGstAmount" style="font-size: 15px; font-weight: 600; color: #28a745;">₹<?= number_format($gst_amount, 2) ?></span>
        </div>

        <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0 0 0;">
            <span style="font-size: 18px; font-weight: 700; color: #2b2b2b; text-transform: uppercase; letter-spacing: 0.5px;">Total:</span>
            <span class="cart-total-amount" id="sidebarTotalIncl" style="font-size: 24px; font-weight: 800; color: #764ba2;">₹<?= number_format($total_inclusive, 2) ?></span>
        </div>
    </div>

    <!-- RECOMMENDED PRODUCTS -->
    <?php if (!empty($recommended_products)): ?>
        <div class="you-may-also-like-section" style="margin-top: 30px; padding: 0 10px;">
            <h3 style="font-size: 18px; margin-bottom: 15px; color: #495057; font-weight: 700;">You may also like</h3>
            <div class="recommended-products-container" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <?php foreach (array_slice($recommended_products, 0, 4) as $product): ?>
                    <div class="recommended-item" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'" style="cursor: pointer; background: #fff; border-radius: 10px; padding: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee;">
                        <img src="<?= !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/beads.jpg' ?>" style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        <div style="font-size: 12px; font-weight: 600; color: #333; height: 32px; overflow: hidden;"><?= htmlspecialchars($product['name']) ?></div>
                        <div style="color: #764ba2; font-weight: 700; font-size: 14px; margin-top: 5px;">₹<?= number_format($product['price'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

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

    <!-- JAVASCRIPT FOR DYNAMIC UPDATES -->
    <script>
    (function() {
        console.log('🔄 Initializing Sidebar JS...');

        window.calculateSidebarTotalInclusive = function() {
            let total = 0;
            const inputs = document.querySelectorAll('.cart-items-list .qty-input');
            inputs.forEach(input => {
                const price = parseFloat(input.getAttribute('data-price')) || 0;
                const qty = parseInt(input.value) || 0;
                const pid = input.getAttribute('data-product-id');
                const itemTotal = price * qty;
                total += itemTotal;
                
                // Update individual item total in UI
                const itemTotalEl = document.getElementById('item_total_' + pid);
                if (itemTotalEl) {
                    itemTotalEl.textContent = itemTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            });
            return total;
        };

        window.updateSidebarTotalsInstantly = function() {
            const totalInclusive = window.calculateSidebarTotalInclusive();
            const gst = (totalInclusive * 12) / 112;
            const subtotalExcl = totalInclusive - gst;
            
            const subEl = document.getElementById('sidebarSubtotalExcl');
            const gstEl = document.getElementById('sidebarGstAmount');
            const totalEl = document.getElementById('sidebarTotalIncl');
            
            if (subEl) subEl.textContent = '₹' + subtotalExcl.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (gstEl) gstEl.textContent = '₹' + gst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (totalEl) totalEl.textContent = '₹' + totalInclusive.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Dispatch event for any other listeners
            document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { total: totalInclusive } }));
        };
        
        // Define proxy for header.php
        window.updateSidebarSubtotalInstantly = window.updateSidebarTotalsInstantly;

        // All cart sidebar events are now handled by the globally defined
        // window.setupCartSidebarEvents() in header.php using event delegation.
        // This prevents multiple handlers from firing and causing double increments.
        if (typeof window.setupCartSidebarEvents === 'function') {
            console.log('🔧 Calling setupCartSidebarEvents from get-cart-sidebar.php');
            window.setupCartSidebarEvents(true); // Force setup to ensure events work
        } else {
            console.warn('⚠️ setupCartSidebarEvents function not found!');
        }

        // Initialize immediately
        window.updateSidebarTotalsInstantly();
    })();
    </script>
<?php endif; ?>
<script src="assets/js/cart-sidebar-enhancements.js?v=<?= time() ?>"></script>
