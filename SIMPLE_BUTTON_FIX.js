// SIMPLE BUTTON FIX - Add this to header.php or create a separate file
// This ensures buttons work no matter what

(function() {
    'use strict';
    
    console.log('🔧 SIMPLE BUTTON FIX: Initializing...');
    
    // Function to make ALL cart buttons work
    function makeCartButtonsWork() {
        console.log('🔧 Making cart buttons work...');
        
        var cartContent = document.getElementById('cartSidebarContent');
        if (!cartContent) {
            console.warn('⚠️ cartSidebarContent not found');
            return;
        }
        
        // Remove ALL old listeners
        if (cartContent._simpleButtonHandler) {
            cartContent.removeEventListener('click', cartContent._simpleButtonHandler, true);
            cartContent.removeEventListener('click', cartContent._simpleButtonHandler, false);
        }
        
        // Create ONE simple handler for ALL buttons
        cartContent._simpleButtonHandler = function(e) {
            console.log('🖱️ Click detected:', e.target);
            
            // Find which button was clicked
            var btn = e.target.closest('.qty-increase, .qty-decrease, .qty-delete, .cart-sidebar-close');
            if (!btn) return;
            
            // Stop everything
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('✅ Button found:', btn.className);
            
            // Handle close button
            if (btn.classList.contains('cart-sidebar-close') || btn.id === 'cartSidebarCloseBtn') {
                console.log('❌ Close button clicked');
                if (typeof window.closeCartSidebar === 'function') {
                    window.closeCartSidebar();
                } else {
                    var sidebar = document.getElementById('cartSidebar');
                    var overlay = document.getElementById('cartSidebarOverlay');
                    if (sidebar) sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
                return false;
            }
            
            // Get product ID
            var productId = btn.getAttribute('data-product-id');
            if (!productId) {
                console.error('❌ No product ID found');
                return false;
            }
            
            // Handle increase
            if (btn.classList.contains('qty-increase')) {
                console.log('➕ Increase clicked for product:', productId);
                var input = document.getElementById('qty_' + productId);
                if (input) {
                    var qty = parseInt(input.value) || 1;
                    input.value = qty + 1;
                    console.log('✅ Quantity updated to:', input.value);
                }
                if (typeof window.updateCartQuantity === 'function') {
                    window.updateCartQuantity(productId, 'increase');
                } else {
                    console.error('❌ updateCartQuantity not found, making direct API call');
                    var fd = new FormData();
                    fd.append('product_id', productId);
                    fd.append('quantity', input ? parseInt(input.value) : 2);
                    fd.append('action', 'update');
                    fetch('update-cart.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(d => {
                        if (d.success && d.cart_html) {
                            var cc = document.getElementById('cartSidebarContent');
                            if (cc) {
                                cc.innerHTML = d.cart_html;
                                setTimeout(makeCartButtonsWork, 100);
                            }
                        }
                    });
                }
                return false;
            }
            
            // Handle decrease
            if (btn.classList.contains('qty-decrease')) {
                console.log('➖ Decrease clicked for product:', productId);
                var input = document.getElementById('qty_' + productId);
                if (input) {
                    var qty = parseInt(input.value) || 1;
                    input.value = Math.max(1, qty - 1);
                    console.log('✅ Quantity updated to:', input.value);
                }
                if (typeof window.updateCartQuantity === 'function') {
                    window.updateCartQuantity(productId, 'decrease');
                } else {
                    console.error('❌ updateCartQuantity not found, making direct API call');
                    var fd = new FormData();
                    fd.append('product_id', productId);
                    fd.append('quantity', input ? parseInt(input.value) : 1);
                    fd.append('action', 'update');
                    fetch('update-cart.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(d => {
                        if (d.success && d.cart_html) {
                            var cc = document.getElementById('cartSidebarContent');
                            if (cc) {
                                cc.innerHTML = d.cart_html;
                                setTimeout(makeCartButtonsWork, 100);
                            }
                        }
                    });
                }
                return false;
            }
            
            // Handle delete
            if (btn.classList.contains('qty-delete')) {
                console.log('🗑️ Delete clicked for product:', productId);
                var sessionKey = btn.getAttribute('data-session-key');
                var item = btn.closest('.cart-item');
                if (item) {
                    item.style.opacity = '0.5';
                    item.style.pointerEvents = 'none';
                }
                if (typeof window.removeCartProduct === 'function') {
                    window.removeCartProduct(productId, sessionKey);
                } else {
                    console.error('❌ removeCartProduct not found, making direct API call');
                    var fd = new FormData();
                    fd.append('product_id', productId);
                    fd.append('session_key', sessionKey);
                    fd.append('action', 'remove');
                    fetch('update-cart.php', {method: 'POST', body: fd})
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            if (typeof window.loadCartSidebar === 'function') {
                                window.loadCartSidebar();
                            } else {
                                location.reload();
                            }
                        }
                    });
                }
                return false;
            }
            
            return false;
        };
        
        // Attach with CAPTURE phase (fires first)
        cartContent.addEventListener('click', cartContent._simpleButtonHandler, true);
        console.log('✅ Simple button handler attached with CAPTURE phase');
        
        // Also attach with BUBBLE phase (backup)
        cartContent.addEventListener('click', cartContent._simpleButtonHandler, false);
        console.log('✅ Simple button handler attached with BUBBLE phase');
    }
    
    // Make it globally available
    window.makeCartButtonsWork = makeCartButtonsWork;
    
    // Run immediately
    makeCartButtonsWork();
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', makeCartButtonsWork);
    }
    
    // Run after delays (for dynamic content)
    setTimeout(makeCartButtonsWork, 100);
    setTimeout(makeCartButtonsWork, 500);
    setTimeout(makeCartButtonsWork, 1000);
    
    // Watch for innerHTML changes
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || mutation.type === 'attributes') {
                setTimeout(makeCartButtonsWork, 50);
            }
        });
    });
    
    // Observe cart content
    var cartContent = document.getElementById('cartSidebarContent');
    if (cartContent) {
        observer.observe(cartContent, {
            childList: true,
            subtree: true,
            attributes: false
        });
        console.log('✅ MutationObserver watching cartSidebarContent');
    }
    
    console.log('✅ SIMPLE BUTTON FIX: Initialized');
})();
