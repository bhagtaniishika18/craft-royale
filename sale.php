<?php
include "includes/db.php";
include "includes/header.php";

// Check which columns exist in products table
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Build query based on available columns
$has_discount_percent = in_array('discount_percent', $products_columns);
$has_mrp = in_array('mrp', $products_columns);
$has_status = in_array('status', $products_columns);
$has_stock = in_array('stock', $products_columns);

// Get products with discount more than 50%
// Calculate discount from mrp and price if discount_percent column doesn't exist
$select_fields = "p.*";
if ($has_discount_percent) {
    $calculated_discount = "CASE 
        WHEN p.discount_percent > 0 THEN p.discount_percent
        WHEN p.mrp > 0 AND p.price < p.mrp THEN ROUND(((p.mrp - p.price) / p.mrp) * 100, 0)
        ELSE 0
    END as calculated_discount";
} else {
    $calculated_discount = "CASE 
        WHEN p.mrp > 0 AND p.price < p.mrp THEN ROUND(((p.mrp - p.price) / p.mrp) * 100, 0)
        ELSE 0
    END as calculated_discount";
}

$sale_query = "SELECT 
    $select_fields,
    $calculated_discount
FROM products p
WHERE (
    " . ($has_discount_percent ? "(p.discount_percent > 50) OR" : "") . "
    (p.mrp > 0 AND p.price < p.mrp AND ((p.mrp - p.price) / p.mrp) * 100 > 50)
)";

// Add status filter - show active and out_of_stock products, exclude inactive and NULL
if ($has_status) {
    $sale_query .= " AND ((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
}

// Add stock filter - only show products with stock more than 5
if ($has_stock) {
    $sale_query .= " AND (p.stock > 5 OR p.stock IS NULL)";
}

$sale_query .= " ORDER BY calculated_discount DESC";

// Add created_at ordering if column exists
if (in_array('created_at', $products_columns)) {
    $sale_query .= ", p.created_at DESC";
} else {
    $sale_query .= ", p.id DESC";
}

$sale_result = mysqli_query($conn, $sale_query);

// Get total count for display
$total_sale_products = $sale_result ? mysqli_num_rows($sale_result) : 0;
?>

<style>
    .sale-hero {
        padding: 100px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sale-hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
        min-width: 100%;
        min-height: 100%;
    }
    
    .sale-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1;
    }
    
    .sale-hero-content {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .sale-hero h1 {
        font-size: 72px;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 4px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 15px rgba(0, 0, 0, 0.5);
    }
    
    .sale-hero p {
        font-size: 24px;
        font-weight: 500;
        color: #fff;
        margin: 0;
        text-shadow: 1px 1px 10px rgba(0, 0, 0, 0.5);
    }
    
    .sale-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 20px;
    }
    
    .sale-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .sale-header h2 {
        font-size: 32px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0;
    }
    
    .sale-count {
        font-size: 16px;
        color: #666;
        background: linear-gradient(135deg, rgba(233, 30, 99, 0.1), rgba(194, 24, 91, 0.1));
        padding: 10px 20px;
        border-radius: 25px;
        border-left: 3px solid #e91e63;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .product-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(233, 30, 99, 0.25);
        border-color: rgba(233, 30, 99, 0.2);
    }
    
    .product-image-wrapper {
        position: relative;
        width: 100%;
        height: 250px;
        overflow: hidden;
        background: linear-gradient(135deg, #ffe0e6, #fff0f5);
    }
    
    .product-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .product-card:hover .product-image-wrapper img {
        transform: scale(1.1);
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #e91e63, #c2185b);
        color: #fff;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 700;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    .product-badge.sold-out {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        animation: none;
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-category-tag {
        font-size: 12px;
        color: #e91e63;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 44px;
    }
    
    .product-pricing {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .product-mrp {
        font-size: 14px;
        color: #999;
        text-decoration: line-through;
    }
    
    .product-price {
        font-size: 20px;
        font-weight: 700;
        color: #e91e63;
    }
    
    .product-actions {
        margin-top: 15px;
    }
    
    .btn-add-cart {
        width: 100%;
        padding: 12px 20px;
        background: linear-gradient(135deg, #e91e63, #c2185b);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-add-cart:hover {
        background: linear-gradient(135deg, #c2185b, #e91e63);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.3);
    }
    
    .btn-coming-soon {
        width: 100%;
        padding: 12px 20px;
        background: #e0e0e0;
        color: #999;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: not-allowed;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .empty-products {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }
    
    .empty-products i {
        font-size: 64px;
        margin-bottom: 20px;
        color: #ddd;
    }
    
    .empty-products h3 {
        font-size: 24px;
        margin: 0 0 10px 0;
        color: #666;
    }
    
    .empty-products p {
        font-size: 16px;
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .sale-hero h1 {
            font-size: 32px;
        }
        
        .sale-hero p {
            font-size: 16px;
        }
        
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .sale-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<!-- HERO SECTION -->
<section class="sale-hero">
    <video class="sale-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/sale.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="Sale Background">
    </video>
    <div class="sale-hero-content">
        <h1>🔥 MEGA SALE 🔥</h1>
        <p>Amazing deals with discounts over 50% off!</p>
    </div>
</section>

<!-- SALE PRODUCTS CONTENT -->
<div class="sale-container">
    <div class="sale-header">
        <h2>Sale Products</h2>
        <div class="sale-count">
            <i class="fas fa-tag"></i> <?= $total_sale_products ?> Products on Sale
        </div>
    </div>
    
    <?php if ($sale_result && mysqli_num_rows($sale_result) > 0): ?>
        <div class="products-grid">
            <?php while ($product = mysqli_fetch_assoc($sale_result)): 
                // Get discount from calculated_discount or discount_percent or calculate from mrp/price
                $discount = 0;
                if (isset($product['calculated_discount'])) {
                    $discount = $product['calculated_discount'];
                } elseif (isset($product['discount_percent']) && $product['discount_percent'] > 0) {
                    $discount = $product['discount_percent'];
                } elseif (isset($product['mrp']) && isset($product['price']) && $product['mrp'] > 0 && $product['price'] < $product['mrp']) {
                    $discount = round((($product['mrp'] - $product['price']) / $product['mrp']) * 100, 0);
                }
                $product_status = $product['status'] ?? 'active';
                $product_stock = $product['stock'] ?? 0;
                $isOutOfStock = ($product_status == 'out_of_stock' || $product_stock <= 0);
                
                // Handle image path
                $product_image = $product['image'] ?? $product['product_image'] ?? '';
                if (!empty($product_image)) {
                    $imagePath = 'uploads/products/' . $product_image;
                    if (!file_exists($imagePath)) {
                        $imagePath = 'assets/images/beads.jpg';
                    }
                } else {
                    $imagePath = 'assets/images/beads.jpg';
                }
            ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                    <div class="product-image-wrapper">
                        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?>" onerror="this.src='assets/images/beads.jpg'">
                        <?php if ($discount > 0): ?>
                            <div class="product-badge">-<?= round($discount) ?>% OFF</div>
                        <?php elseif ($isOutOfStock): ?>
                            <div class="product-badge sold-out">Sold Out</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
                            <div class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></div>
                        <?php endif; ?>
                        <div class="product-name"><?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?></div>
                        <div class="product-pricing">
                            <?php 
                            $product_mrp = $product['mrp'] ?? 0;
                            $product_price = $product['price'] ?? 0;
                            if ($product_mrp > $product_price && $product_mrp > 0): 
                            ?>
                                <span class="product-mrp">₹<?= number_format($product_mrp, 2) ?></span>
                            <?php endif; ?>
                            <span class="product-price">₹<?= number_format($product_price, 2) ?></span>
                        </div>
                        <div class="product-actions">
                            <?php if ($isOutOfStock): ?>
                                <button class="btn-coming-soon" disabled>COMING SOON</button>
                            <?php else: ?>
                                <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">ADD TO CART</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-products">
            <i class="fas fa-tag"></i>
            <h3>No Sale Products Available</h3>
            <p>Products with discounts over 50% will appear here.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCart(productId) {
    event.stopPropagation();
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = 'ADDING...';
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                const cartCount = document.getElementById('cartCount');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                    cartCount.setAttribute('data-count', data.cart_count);
                }
            }
            
            const cartContent = document.getElementById('cartSidebarContent');
            if (cartContent && data.cart_html) {
                cartContent.innerHTML = data.cart_html;
                
                setTimeout(() => {
                    const sidebar = document.getElementById('cartSidebar');
                    if (sidebar) {
                        sidebar.removeAttribute('data-events-attached');
                    }
                    if (typeof attachDirectCartButtonListeners === 'function') {
                        attachDirectCartButtonListeners();
                    }
                    if (typeof window.setupCartSidebarEvents === 'function') {
                        window.setupCartSidebarEvents();
                    }
                }, 50);
            } else if (typeof window.loadCartSidebar === 'function') {
                window.loadCartSidebar();
            }
            
            // Open the sidebar - ensure it opens
            setTimeout(function() {
                // Try multiple methods to ensure sidebar opens
                if (typeof window.openCartSidebar === 'function') {
                    window.openCartSidebar();
                } else if (typeof openCartSidebar === 'function') {
                    openCartSidebar();
                } else {
                    // Fallback: try to open sidebar directly
                    const sidebar = document.getElementById('cartSidebar');
                    const overlay = document.getElementById('cartSidebarOverlay');
                    if (sidebar && overlay) {
                        sidebar.classList.add('active');
                        overlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        // If elements not found, wait a bit and try again
                        setTimeout(function() {
                            const sidebar = document.getElementById('cartSidebar');
                            const overlay = document.getElementById('cartSidebarOverlay');
                            if (sidebar && overlay) {
                                sidebar.classList.add('active');
                                overlay.classList.add('active');
                                document.body.style.overflow = 'hidden';
                            }
                        }, 200);
                    }
                }
            }, 150);
            
            btn.innerHTML = originalText;
            btn.disabled = false;
        } else {
            alert('Failed to add product to cart. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php include 'includes/footer.php'; ?>
