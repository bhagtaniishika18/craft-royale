<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Get wishlist items
$wishlist_items = [];

foreach ($_SESSION['wishlist'] as $product_id => $item) {
    if (!is_array($item)) continue;
    
    $wishlist_items[] = [
        'id' => $product_id,
        'name' => isset($item['name']) ? $item['name'] : 'Product',
        'price' => isset($item['price']) ? floatval($item['price']) : 0,
        'mrp' => isset($item['mrp']) ? floatval($item['mrp']) : 0,
        'image' => isset($item['image']) ? $item['image'] : '',
    ];
}
?>

<style>
    .wishlist-page {
        min-height: 80vh;
        background: linear-gradient(180deg, #fff5f5 0%, #ffffff 100%);
        padding: 40px 0;
    }

    .wishlist-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .wishlist-banner {
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 40px;
        background: #e91e63; /* Fallback */
        border-radius: 24px;
        box-shadow: 0 15px 50px rgba(233, 30, 99, 0.25);
    }

    .wishlist-hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .wishlist-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1;
        border-radius: 24px;
    }

    @keyframes floatHeart {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(10deg);
        }
    }

    .wishlist-banner-content {
        position: relative;
        z-index: 2;
    }

    .wishlist-banner h1 {
        font-size: 64px;
        font-weight: 800;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 4px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        animation: titleGlow 2s ease-in-out infinite;
    }

    @keyframes titleGlow {
        0%, 100% {
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
        50% {
            text-shadow: 2px 2px 20px rgba(255, 255, 255, 0.5), 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
    }

    .wishlist-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        margin-top: 20px;
    }

    .wishlist-breadcrumb a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .wishlist-breadcrumb a:hover {
        color: #fff;
    }

    .wishlist-content {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .wishlist-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .wishlist-header h2 {
        font-size: 28px;
        font-weight: 800;
        color: #2b2b2b;
        margin: 0;
    }

    .wishlist-header h2::before {
        content: '❤️ ';
    }

    .wishlist-count {
        font-size: 16px;
        color: #666;
    }

    .wishlist-count strong {
        color: #e91e63;
        font-size: 20px;
    }

    .empty-wishlist {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-wishlist-icon {
        font-size: 120px;
        color: #e91e63;
        opacity: 0.3;
        margin-bottom: 30px;
        animation: emptyHeartPulse 2s ease-in-out infinite;
    }

    @keyframes emptyHeartPulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.3;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.5;
        }
    }

    .empty-wishlist h3 {
        font-size: 32px;
        color: #2b2b2b;
        margin: 0 0 15px 0;
    }

    .empty-wishlist p {
        font-size: 18px;
        color: #666;
        margin: 0 0 30px 0;
    }

    .empty-wishlist .btn-shop {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
        color: #fff;
        padding: 15px 40px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        font-size: 16px;
        display: inline-block;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
    }

    .empty-wishlist .btn-shop:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4);
    }

    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .wishlist-item-card {
        position: relative;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        border: 2px solid transparent;
    }

    .wishlist-item-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(233, 30, 99, 0.2);
        border-color: #e91e63;
    }

    .wishlist-item-image-wrapper {
        position: relative;
        pointer-events: none;
        width: 100%;
        height: 250px;
        overflow: hidden;
        background: #f9f9f9;
    }

    .wishlist-item-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .wishlist-item-card:hover .wishlist-item-image {
        transform: scale(1.1);
    }

    .wishlist-item-remove {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc3545;
        font-size: 18px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 100 !important;
        pointer-events: auto !important;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    .wishlist-item-remove i {
        pointer-events: none;
    }

    .wishlist-item-remove:hover {
        background: #dc3545;
        color: #fff;
        transform: scale(1.1) rotate(90deg);
    }

    .wishlist-item-details {
        padding: 20px;
    }

    .wishlist-item-name {
        font-size: 16px;
        font-weight: 600;
        color: #2b2b2b;
        margin: 0 0 12px 0;
        line-height: 1.4;
        min-height: 44px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .wishlist-item-price {
        margin-bottom: 15px;
    }

    .wishlist-item-price .original {
        color: #999;
        text-decoration: line-through;
        font-size: 14px;
        margin-right: 10px;
    }

    .wishlist-item-price .current {
        color: #dc3545;
        font-weight: 700;
        font-size: 20px;
    }

    .wishlist-item-actions {
        display: flex;
        gap: 10px;
    }

    .wishlist-item-actions .btn-add-cart {
        flex: 1;
        background: linear-gradient(135deg, #2fc7b4 0%, #26a693 100%);
        color: #fff;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wishlist-item-actions .btn-add-cart:hover {
        background: linear-gradient(135deg, #26a693 0%, #2fc7b4 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
    }

    .wishlist-item-actions .btn-view {
        background: #f8f9fa;
        color: #2b2b2b;
        border: 2px solid #e0e0e0;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .wishlist-item-actions .btn-view:hover {
        background: #e9ecef;
        border-color: #2b2b2b;
        transform: translateY(-2px);
    }

    .wishlist-actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid #f0f0f0;
    }

    .wishlist-actions-bar .btn-clear {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wishlist-actions-bar .btn-clear:hover {
        background: linear-gradient(135deg, #ee5a6f 0%, #ff6b6b 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    /* Clear Wishlist Popup Styling */
    .clear-wishlist-popup {
        border-radius: 24px !important;
        background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%) !important;
        border: 2px solid rgba(255, 107, 107, 0.2) !important;
        box-shadow: 0 30px 80px rgba(0,0,0,0.3), 0 0 0 3px rgba(255, 107, 107, 0.1) !important;
        padding: 40px 35px !important;
    }

    .clear-wishlist-title {
        color: #ff6b6b !important;
        font-weight: 800 !important;
        font-size: 28px !important;
    }

    .clear-wishlist-content {
        color: #666 !important;
    }

    .clear-wishlist-confirm-btn {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%) !important;
        border: none !important;
        padding: 12px 30px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3) !important;
        transition: all 0.3s ease !important;
    }

    .clear-wishlist-confirm-btn:hover {
        background: linear-gradient(135deg, #ee5a6f 0%, #ff6b6b 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4) !important;
    }

    .clear-wishlist-cancel-btn {
        background: #6c757d !important;
        border: none !important;
        padding: 12px 30px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 16px !important;
    }

    .clear-wishlist-cancel-btn:hover {
        background: #5a6268 !important;
    }

    @keyframes trashShake {
        0%, 100% {
            transform: scale(1) rotate(0deg);
        }
        10%, 30%, 50%, 70%, 90% {
            transform: scale(1.1) rotate(-5deg);
        }
        20%, 40%, 60%, 80% {
            transform: scale(1.1) rotate(5deg);
        }
    }

    .btn-clear {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .btn-clear:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .wishlist-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .wishlist-banner h1 {
            font-size: 36px;
        }

        .wishlist-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }
</style>

<div class="wishlist-page">
    <div class="wishlist-container">
        <div class="wishlist-banner">
            <video class="wishlist-hero-video" autoplay muted loop playsinline>
                <source src="assets/images/wishlist.mp4" type="video/mp4">
            </video>
            <div class="wishlist-banner-content">
                <h1>My Wishlist ❤️</h1>
                <div class="wishlist-breadcrumb">
                    <a href="index.php">Home</a>
                    <span>❯</span>
                    <span>Wishlist</span>
                </div>
            </div>
        </div>

        <div class="wishlist-content">
            <div class="wishlist-header">
                <h2>Your Favorite Items</h2>
                <div class="wishlist-count">
                    <strong><?= count($wishlist_items) ?></strong> item<?= count($wishlist_items) != 1 ? 's' : '' ?> in your wishlist
                </div>
            </div>

            <?php if (empty($wishlist_items)): ?>
                <div class="empty-wishlist">
                    <div class="empty-wishlist-icon">❤️</div>
                    <h3>Your Wishlist is Empty</h3>
                    <p>Start adding items you love to your wishlist! ✨</p>
                    <a href="products.php" class="btn-shop">🛍️ Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_items as $item): 
                        $image_path = !empty($item['image']) ? 'uploads/products/' . $item['image'] : 'assets/images/beads.jpg';
                    ?>
                        <div class="wishlist-item-card" data-product-id="<?= $item['id'] ?>">
                            <div class="wishlist-item-image-wrapper">
                                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="wishlist-item-image" onerror="this.src='assets/images/beads.jpg'">
                                <button type="button" class="wishlist-item-remove" data-product-id="<?= $item['id'] ?>" title="Remove from wishlist">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="wishlist-item-details">
                                <h3 class="wishlist-item-name"><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="wishlist-item-price">
                                    <?php if ($item['mrp'] > $item['price']): ?>
                                        <span class="original">Rs. <?= number_format($item['mrp'], 2) ?></span>
                                    <?php endif; ?>
                                    <span class="current">Rs. <?= number_format($item['price'], 2) ?></span>
                                </div>
                                <div class="wishlist-item-actions">
                                    <button type="button" class="btn-add-cart" onclick="addToCartFromWishlist(<?= $item['id'] ?>, event)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <a href="product.php?id=<?= $item['id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="wishlist-actions-bar">
                    <button type="button" class="btn-clear" onclick="confirmClearWishlist()">
                        <i class="fas fa-trash"></i> Clear Wishlist
                    </button>
                    <a href="products.php" class="btn-view">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Ensure function is available immediately
(function() {
    'use strict';
    
// Override the global function for wishlist page - COMPLETE REMOVAL
window.removeFromWishlist = function(productId) {
    console.log('removeFromWishlist called with productId:', productId);
    
    if (!productId) {
        console.error('Product ID is required');
        return;
    }
    
    // Find the card element
    const card = document.querySelector(`[data-product-id="${productId}"]`);
    
    // Show loading state
    if (card) {
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';
    }
    
    // Make API call to remove from server FIRST (wait for confirmation)
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'remove');
    
    fetch('add-to-wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Remove wishlist response:', data);
        if (data.success) {
            // Update wishlist count in header immediately
            if (typeof window.updateWishlistCount === 'function') {
                window.updateWishlistCount();
            }
            
            // Show quick animation then reload to ensure complete sync
            if (card) {
                card.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                // Reload page after short animation to ensure everything is synced
                setTimeout(() => {
                    location.reload();
                }, 200);
            } else {
                // If card not found, reload immediately
                location.reload();
            }
        } else {
            // If server removal failed, reload to sync with server state
            console.error('Error removing from wishlist:', data.message);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error removing from wishlist:', error);
        // Reset card state on error
        if (card) {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        }
        // Reload to sync with server state
        location.reload();
    });
};

// Also define as regular function for onclick handlers
function removeFromWishlist(productId) {
    if (typeof window.removeFromWishlist === 'function') {
        window.removeFromWishlist(productId);
    }
}

// Add event listeners for remove buttons - SINGLE CLICK ONLY
(function() {
    // Use event delegation with capture phase to catch clicks early
    document.addEventListener('click', function(e) {
        // Check if clicked element is the button or icon inside it
        const removeButton = e.target.closest('.wishlist-item-remove');
        if (removeButton) {
            // Prevent all event propagation
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Get product ID from data attribute
            const productId = removeButton.getAttribute('data-product-id');
            
            if (productId && !removeButton.disabled) {
                // Immediately disable button to prevent double-click
                removeButton.disabled = true;
                removeButton.style.pointerEvents = 'none';
                removeButton.style.opacity = '0.5';
                
                // Call remove function immediately
                if (typeof window.removeFromWishlist === 'function') {
                    window.removeFromWishlist(parseInt(productId));
                } else {
                    // Fallback if function not ready
                    console.error('removeFromWishlist function not available');
                    removeButton.disabled = false;
                    removeButton.style.pointerEvents = 'auto';
                    removeButton.style.opacity = '1';
                }
            }
            return false;
        }
    }, true); // Capture phase - fires before bubbling
    
    // Also handle on DOMContentLoaded for any buttons already on page
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.wishlist-item-remove');
            buttons.forEach(button => {
                // Remove any existing onclick handlers
                button.removeAttribute('onclick');
                
                // Ensure data attribute is set
                if (!button.getAttribute('data-product-id')) {
                    const card = button.closest('[data-product-id]');
                    if (card) {
                        button.setAttribute('data-product-id', card.getAttribute('data-product-id'));
                    }
                }
            });
        });
    } else {
        // DOM already loaded
        const buttons = document.querySelectorAll('.wishlist-item-remove');
        buttons.forEach(button => {
            button.removeAttribute('onclick');
            if (!button.getAttribute('data-product-id')) {
                const card = button.closest('[data-product-id]');
                if (card) {
                    button.setAttribute('data-product-id', card.getAttribute('data-product-id'));
                }
            }
        });
    }
})();

})(); // End IIFE

function addToCartFromWishlist(productId, event) {
    console.log('addToCartFromWishlist called with productId:', productId, 'event:', event);
    
    if (!productId) {
        console.error('Product ID is required');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Product ID is missing',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Error: Product ID is missing');
        }
        return;
    }
    
    // Disable button to prevent multiple clicks
    let button = null;
    if (event && event.target) {
        button = event.target.closest('.btn-add-cart') || event.target.closest('button');
    }
    if (!button) {
        // Find button by onclick attribute - more robust search
        const allButtons = document.querySelectorAll('button.btn-add-cart, .btn-add-cart');
        for (let btn of allButtons) {
            const onclick = btn.getAttribute('onclick');
            if (onclick && onclick.includes(`addToCartFromWishlist(${productId})`)) {
                button = btn;
                break;
            }
        }
    }
    
    const originalText = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        button.style.pointerEvents = 'none';
        button.style.opacity = '0.7';
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    console.log('Sending add to cart request for productId:', productId);
    
    fetch('add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Add to cart response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Add to cart response data:', data);
        if (data.success) {
            // Update cart count in header
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount();
            } else if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            
            // Update cart sidebar
            const cartContent = document.getElementById('cartSidebarContent');
            if (cartContent && data.cart_html) {
                cartContent.innerHTML = data.cart_html;
                console.log('Cart sidebar updated');
            }
            
            // Open cart sidebar after small delay to ensure cart is updated
            setTimeout(function() {
                if (typeof window.openCartSidebar === 'function') {
                    window.openCartSidebar();
                } else {
                    // Fallback: open sidebar directly
                    const sidebar = document.getElementById('cartSidebar');
                    const overlay = document.getElementById('cartSidebarOverlay');
                    if (sidebar && overlay) {
                        sidebar.classList.add('active');
                        overlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                }
            }, 100);
            
            // Reset button state
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
                button.style.pointerEvents = 'auto';
                button.style.opacity = '1';
            }
        } else {
            // Reset button on error
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
                button.style.pointerEvents = 'auto';
                button.style.opacity = '1';
            }
            const errorMsg = data.message || 'Error adding product to cart';
            console.error('Add to cart failed:', errorMsg);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonColor: '#ff6b6b'
                });
            } else {
                alert(errorMsg);
            }
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        // Reset button on error
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
            button.style.pointerEvents = 'auto';
            button.style.opacity = '1';
        }
        const errorMsg = 'Error adding product to cart. Please try again.';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                confirmButtonColor: '#ff6b6b'
            });
        } else {
            alert(errorMsg);
        }
    });
}

function confirmClearWishlist() {
    console.log('confirmClearWishlist called');
    // Check if SweetAlert2 is available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: false,
            title: '🗑️ Clear Wishlist?',
            html: '<div style="font-size: 80px; margin: 20px 0; animation: trashShake 0.5s ease-in-out; line-height: 1;">🗑️</div><p style="font-size: 18px; color: #666; margin: 15px 0; padding: 0;">Are you sure you want to remove <strong>all items</strong> from your wishlist?</p><p style="font-size: 14px; color: #999; margin-top: 10px; padding: 0;">This action cannot be undone! 💔</p>',
            showConfirmButton: true,
            confirmButtonText: '🗑️ Yes, Clear All',
            confirmButtonColor: '#ff6b6b',
            showCancelButton: true,
            cancelButtonText: '❌ Cancel',
            cancelButtonColor: '#6c757d',
            background: 'linear-gradient(135deg, #ffffff 0%, #fff5f5 100%)',
            customClass: {
                popup: 'clear-wishlist-popup',
                title: 'clear-wishlist-title',
                htmlContainer: 'clear-wishlist-content',
                confirmButton: 'clear-wishlist-confirm-btn',
                cancelButton: 'clear-wishlist-cancel-btn'
            },
            allowOutsideClick: true,
            allowEscapeKey: true
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('User confirmed clear wishlist');
                // Close the confirmation modal first
                Swal.close();
                
                // Show loading state
                Swal.fire({
                    title: 'Clearing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Clear wishlist directly
                fetch('clear-wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => {
                    console.log('Clear wishlist response:', response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Clear wishlist data:', data);
                    if (data.success) {
                        // Update wishlist count in header
                        if (typeof window.updateWishlistCount === 'function') {
                            window.updateWishlistCount();
                        }
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: '✨ Wishlist Cleared!',
                            text: 'All items have been removed from your wishlist.',
                            timer: 2000,
                            showConfirmButton: false,
                            background: 'linear-gradient(135deg, #ffffff 0%, #e8f5e9 100%)'
                        }).then(() => {
                            // Reload page to show empty wishlist
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to clear wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error clearing wishlist:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to clear wishlist. Please try again.',
                        confirmButtonColor: '#ff6b6b'
                    });
                });
            }
        });
    } else {
        // Fallback to basic confirm if SweetAlert2 not available
        if (confirm('Are you sure you want to clear your entire wishlist? ❤️')) {
            fetch('clear-wishlist.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error clearing wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error clearing wishlist');
            });
        }
    }
}

// Legacy function - kept for compatibility
function clearWishlist() {
    console.log('Legacy clearWishlist called, redirecting to confirmClearWishlist');
    confirmClearWishlist();
}

// Add fade out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include "includes/footer.php"; ?>
