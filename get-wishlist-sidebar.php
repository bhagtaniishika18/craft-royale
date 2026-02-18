<?php
// Start session and ensure we get fresh data
session_start();
// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
include "includes/db.php";

// Get wishlist items
$wishlist_items_display = [];

if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0) {
    foreach ($_SESSION['wishlist'] as $product_id => $item) {
        if (!is_array($item)) continue;
        
        $wishlist_items_display[] = [
            'id' => $product_id,
            'name' => isset($item['name']) ? $item['name'] : 'Product',
            'price' => isset($item['price']) ? floatval($item['price']) : 0,
            'mrp' => isset($item['mrp']) ? floatval($item['mrp']) : 0,
            'image' => isset($item['image']) ? $item['image'] : '',
        ];
    }
}
?>

<?php if (empty($wishlist_items_display)): ?>
    <div class="empty-wishlist-sidebar">
        <i class="fas fa-heart"></i>
        <p>Your wishlist is empty</p>
        <p style="font-size: 14px; color: #666; margin-top: 10px;">Add items you love to your wishlist!</p>
    </div>
<?php else: ?>
    <div class="wishlist-items-list">
        <?php foreach ($wishlist_items_display as $item): 
            $image_path = !empty($item['image']) ? 'uploads/products/' . $item['image'] : 'assets/images/beads.jpg';
        ?>
            <div class="wishlist-item" data-product-id="<?= $item['id'] ?>">
                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="wishlist-item-image" onerror="this.src='assets/images/beads.jpg'">
                <div class="wishlist-item-details">
                    <div class="wishlist-item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="wishlist-item-price">
                        <?php if ($item['mrp'] > $item['price']): ?>
                            <span class="original">Rs. <?= number_format($item['mrp'], 2) ?></span>
                        <?php endif; ?>
                        <span class="current">Rs. <?= number_format($item['price'], 2) ?></span>
                    </div>
                    <div class="wishlist-item-actions">
                        <button type="button" class="wishlist-add-to-cart" onclick="event.preventDefault();event.stopPropagation();addToCartFromWishlist(<?= $item['id'] ?>);return false;">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button type="button" class="wishlist-remove" onclick="event.preventDefault();event.stopPropagation();removeFromWishlist(<?= $item['id'] ?>);return false;" title="Remove from wishlist">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wishlist-sidebar-footer">
        <div class="wishlist-sidebar-buttons">
            <button type="button" class="wishlist-sidebar-btn continue-shopping" onclick="event.preventDefault();if(typeof window.closeWishlistSidebar==='function'){window.closeWishlistSidebar();}return false;">🛒 Continue Shopping</button>
            <button type="button" class="wishlist-sidebar-btn clear-wishlist" onclick="event.preventDefault();event.stopPropagation();if(confirm('Are you sure you want to clear your wishlist?')){clearWishlist();}return false;">🗑️ Clear Wishlist</button>
        </div>
    </div>
<?php endif; ?>
