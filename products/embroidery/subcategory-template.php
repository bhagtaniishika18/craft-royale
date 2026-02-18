<style>
    /* Hero Banner */
    .subcategory-hero-banner {
        position: relative;
        width: 100%;
        min-height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .subcategory-hero-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .subcategory-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1;
    }

    .subcategory-hero-content {
        position: relative;
        z-index: 2;
        max-width: 1400px;
        width: 100%;
        padding: 0 20px;
        text-align: center;
    }

    .subcategory-hero-title {
        font-size: 56px;
        font-weight: 900;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
        line-height: 1.1;
    }

    .subcategory-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 16px;
        color: #ffffff;
        flex-wrap: wrap;
    }

    .subcategory-breadcrumb a {
        color: #ffffff;
        text-decoration: none;
    }

    .subcategory-breadcrumb .breadcrumb-separator {
        color: #ffffff;
        margin: 0 4px;
    }

    .subcategory-breadcrumb .breadcrumb-current {
        color: #2fc7b4;
        font-weight: 600;
    }

    /* Products Section */
    .products-main-section {
        background: #ffffff;
        padding: 40px 0;
    }

    .products-container-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .products-header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e5e5;
    }

    .products-page-title {
        font-size: 24px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0;
        text-transform: capitalize;
    }

    .products-controls {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .products-count-text {
        font-size: 14px;
        color: #666666;
    }

    .products-sort-wrapper select {
        padding: 8px 30px 8px 12px;
        border: 1px solid #cccccc;
        border-radius: 4px;
        font-size: 14px;
        color: #333333;
        background: #ffffff;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23333' d='M5 6L0 0h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .products-sort-wrapper select:focus {
        outline: none;
        border-color: #2fc7b4;
    }

    /* Products Grid */
    .products-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .product-item-card {
        background: #ffffff;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #e0e0e0;
    }

    .product-item-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .product-image-container {
        position: relative;
        width: 100%;
        padding-top: 100%;
        overflow: hidden;
        background: #f5f5f5;
    }

    .product-image-container img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff6b35;
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        z-index: 2;
        min-width: 45px;
        text-align: center;
    }

    .product-soldout-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        z-index: 2;
    }

    .product-details {
        padding: 15px;
    }

    .product-title {
        font-size: 14px;
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 10px;
        line-height: 1.4;
        min-height: 40px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price-section {
        margin-bottom: 12px;
    }

    .product-old-price {
        font-size: 13px;
        color: #999999;
        text-decoration: line-through;
        margin-right: 8px;
    }

    .product-new-price {
        font-size: 18px;
        font-weight: 700;
        color: #2fa76b;
    }

    .product-action-button {
        width: 100%;
        background: #2fc7b4;
        color: #ffffff;
        border: none;
        padding: 11px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
    }

    .product-action-button:hover {
        background: #2fa76b;
    }

    .product-action-button:disabled {
        background: #999999;
        cursor: not-allowed;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 40px;
        padding-top: 30px;
    }

    .pagination-container a,
    .pagination-container span {
        padding: 8px 14px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .pagination-container a:hover {
        border-color: #2fc7b4;
        color: #2fc7b4;
    }

    .pagination-container .active-page {
        background: #2fc7b4;
        color: #fff;
        border-color: #2fc7b4;
    }

    .no-products-message {
        text-align: center;
        padding: 60px 20px;
    }

    .no-products-message i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
    }

    .no-products-message h3 {
        color: #999;
        margin-bottom: 10px;
    }

    @media (max-width: 968px) {
        .subcategory-hero-title {
            font-size: 42px;
        }
        .products-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
    }

    @media (max-width: 480px) {
        .subcategory-hero-title {
            font-size: 32px;
            letter-spacing: 2px;
        }
        .products-grid-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- HERO BANNER -->
<?php 
// Debug: Ensure banner_image is set
if (empty($banner_image)) {
    $banner_image = '../../assets/images/frenchwirebanner.jpg?v=' . time();
}
?>
<section class="subcategory-hero-banner">
    <div class="subcategory-hero-image" style="background-image: url('<?= htmlspecialchars($banner_image) ?>');">
        <div class="subcategory-hero-overlay"></div>
    </div>
    <div class="subcategory-hero-content">
        <h1 class="subcategory-hero-title"><?= htmlspecialchars(strtoupper($subcategory_info['subcategory_name'])) ?></h1>
        <nav class="subcategory-breadcrumb">
            <a href="../../index.php">Home</a> 
            <span class="breadcrumb-separator">></span> 
            <a href="../../products.php?category=<?= $category_id ?>"><?= htmlspecialchars($category_name) ?></a>
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-current"><?= htmlspecialchars($subcategory_info['subcategory_name']) ?></span>
        </nav>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<section class="products-main-section">
    <div class="products-container-wrapper">
        <div class="products-header-section">
            <h1 class="products-page-title"><?= htmlspecialchars($subcategory_info['subcategory_name']) ?></h1>
            <div class="products-controls">
                <div class="products-count-text">
                    Showing <?= $total_products ?> product<?= $total_products != 1 ? 's' : '' ?>
                </div>
                <div class="products-sort-wrapper">
                    <form method="get" style="display: inline;">
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Date, new to old</option>
                            <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name: A to Z</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
        <div class="products-grid-container">
            <?php while ($product = mysqli_fetch_assoc($products_result)): 
                // DOUBLE-CHECK: Verify product belongs to this subcategory
                $product_subcategory_id = isset($product['subcategory_id']) ? (int)$product['subcategory_id'] : 0;
                if ($product_subcategory_id !== $subcategory_id || $product_subcategory_id <= 0) {
                    // Skip this product - it doesn't belong to this subcategory
                    continue;
                }
                
                $discount = $product['discount_percent'] ?? 0;
                $product_status = $product['status'] ?? 'active';
                $product_stock = $product['stock'] ?? 0;
                $isOutOfStock = ($product_status == 'out_of_stock' || $product_stock <= 0);
                
                // Handle image path - use productsimg/embroidery/ folder
                $product_image = $product['image'] ?? $product['product_image'] ?? '';
                if (!empty($product_image)) {
                    $imagePath = '../../productsimg/embroidery/' . $product_image;
                    // Check if file exists, if not try uploads/products
                    if (!file_exists($imagePath)) {
                        $imagePath = '../../uploads/products/' . $product_image;
                        if (!file_exists($imagePath)) {
                            $imagePath = '../../assets/images/beads.jpg';
                        }
                    }
                } else {
                    $imagePath = '../../assets/images/beads.jpg';
                }
            ?>
                <div class="product-item-card" onclick="window.location.href='../../product.php?id=<?= $product['id'] ?>'">
                    <div class="product-image-container">
                        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?>" onerror="this.src='../../assets/images/beads.jpg'">
                        <?php if ($discount > 0): ?>
                            <div class="product-discount-badge">-<?= $discount ?>%</div>
                        <?php elseif ($isOutOfStock): ?>
                            <div class="product-soldout-badge">Sold Out</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <div class="product-title"><?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?></div>
                        <div class="product-price-section">
                            <?php 
                            $product_mrp = $product['mrp'] ?? 0;
                            $product_price = $product['price'] ?? 0;
                            if ($product_mrp > $product_price && $product_mrp > 0): 
                            ?>
                                <span class="product-old-price">₹<?= number_format($product_mrp, 2) ?></span>
                            <?php endif; ?>
                            <span class="product-new-price">₹<?= number_format($product_price, 2) ?></span>
                        </div>
                        <button class="product-action-button" <?= $isOutOfStock ? 'disabled' : '' ?> onclick="event.stopPropagation(); <?= $isOutOfStock ? '' : 'addToCart(' . $product['id'] . ')' ?>">
                            <?= $isOutOfStock ? 'COMING SOON' : 'ADD TO CART' ?>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active-page"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php else: ?>
            <div class="no-products-message">
                <i class="fas fa-box-open"></i>
                <h3>No products found</h3>
                <p>Products will appear here once added from the admin panel.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function addToCart(productId) {
    event.stopPropagation();
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = 'ADDING...';
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('../../add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in header
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                const cartCount = document.getElementById('cartCount');
                if (cartCount) {
                    cartCount.textContent = data.cart_count || 0;
                    cartCount.setAttribute('data-count', data.cart_count || 0);
                }
            }
            
            // Open cart sidebar after small delay to ensure cart is updated
            setTimeout(function() {
                if (typeof window.openCartSidebar === 'function') {
                    window.openCartSidebar();
                } else if (typeof openCartSidebar === 'function') {
                    openCartSidebar();
                } else {
                    // Fallback: open sidebar directly
                    const sidebar = document.getElementById('cartSidebar');
                    const overlay = document.getElementById('cartSidebarOverlay');
                    if (sidebar && overlay) {
                        sidebar.classList.add('active');
                        overlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                        // Load content from get-cart-sidebar.php
                        const cartContent = document.getElementById('cartSidebarContent');
                        if (cartContent) {
                            const basePath = '../../';
                            fetch(basePath + 'get-cart-sidebar.php?t=' + Date.now())
                                .then(r => r.text())
                                .then(html => {
                                    if (cartContent && html) {
                                        cartContent.innerHTML = html;
                                    }
                                })
                                .catch(err => console.error('Error loading cart:', err));
                        }
                    }
                }
            }, 100);
            
            // Show success message
            btn.innerHTML = '✓ ADDED';
            btn.style.background = '#28a745';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            alert(data.message || 'Error adding product to cart');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php include "../../includes/footer.php"; ?>


