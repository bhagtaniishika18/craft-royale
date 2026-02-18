<?php
include 'includes/db.php';
include 'includes/header.php';

// Check which columns exist
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
if ($columns_check) {
    while ($col = mysqli_fetch_assoc($columns_check)) {
        $products_columns[] = $col['Field'];
    }
}

$has_category_id = in_array('category_id', $products_columns);
$has_status = in_array('status', $products_columns);
$has_created_at = in_array('created_at', $products_columns);

$order_by = "ORDER BY id DESC";
if ($has_created_at) {
    $order_by = "ORDER BY created_at DESC";
}

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY category_name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Group products by category and subcategory
$categories_with_products = [];
$has_subcategory_id = in_array('subcategory_id', $products_columns);

if ($has_category_id && $categories_result && mysqli_num_rows($categories_result) > 0) {
    while ($category = mysqli_fetch_assoc($categories_result)) {
        $category_id = (int)$category['id'];
        
        // Get subcategories for this category
        $subcategories_query = "SELECT * FROM subcategories WHERE category_id = $category_id ORDER BY subcategory_name ASC";
        $subcategories_result = mysqli_query($conn, $subcategories_query);
        
        $subcategories_with_products = [];
        
        if ($has_subcategory_id && $subcategories_result && mysqli_num_rows($subcategories_result) > 0) {
            while ($subcategory = mysqli_fetch_assoc($subcategories_result)) {
                $subcategory_id = (int)$subcategory['id'];
                
                $where_parts = ["category_id = $category_id", "subcategory_id = $subcategory_id"];
                if ($has_status) {
                    // Show active and out_of_stock products, but exclude inactive and NULL
                    $where_parts[] = "((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
                }
                
                // Get last 2 products from this subcategory
                $subcat_query = "SELECT * FROM products WHERE " . implode(" AND ", $where_parts) . " $order_by LIMIT 2";
                $subcat_result = mysqli_query($conn, $subcat_query);
                
                $products = [];
                if ($subcat_result && mysqli_num_rows($subcat_result) > 0) {
                    while ($product = mysqli_fetch_assoc($subcat_result)) {
                        $products[] = $product;
                    }
                }
                
                // Only add subcategory if it has products
                if (!empty($products)) {
                    $subcategories_with_products[] = [
                        'subcategory' => $subcategory,
                        'products' => $products
                    ];
                }
            }
        }
        
        // If no subcategories, get products directly by category
        if (empty($subcategories_with_products)) {
            $where_parts = ["category_id = $category_id"];
            if ($has_status) {
                // Show active and out_of_stock products, but exclude inactive and NULL
                $where_parts[] = "((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
            }
            
            $cat_query = "SELECT * FROM products WHERE " . implode(" AND ", $where_parts) . " $order_by LIMIT 20";
            $cat_result = mysqli_query($conn, $cat_query);
            
            $products = [];
            if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                while ($product = mysqli_fetch_assoc($cat_result)) {
                    $products[] = $product;
                }
            }
            
            if (!empty($products)) {
                $subcategories_with_products[] = [
                    'subcategory' => ['subcategory_name' => $category['category_name']],
                    'products' => $products
                ];
            }
        }
        
        // Only add category if it has products
        if (!empty($subcategories_with_products)) {
            $categories_with_products[] = [
                'category' => $category,
                'subcategories' => $subcategories_with_products
            ];
        }
    }
}

// Fallback: show all recent products
if (empty($categories_with_products)) {
    $fallback_query = "SELECT * FROM products $order_by LIMIT 20";
    $fallback_result = mysqli_query($conn, $fallback_query);
    
    if ($fallback_result && mysqli_num_rows($fallback_result) > 0) {
        $all_products = [];
        while ($product = mysqli_fetch_assoc($fallback_result)) {
            $all_products[] = $product;
        }
        
        if (!empty($all_products)) {
            $categories_with_products[] = [
                'category' => ['category_name' => 'New Arrivals', 'id' => 0],
                'subcategories' => [
                    [
                        'subcategory' => ['subcategory_name' => 'New Arrivals'],
                        'products' => $all_products
                    ]
                ]
            ];
        }
    }
}
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/new_arrivals.mp4" type="video/mp4">
        <img src="assets/images/beads.jpg" alt="New Arrivals Background">
    </video>
    <div class="embroidery-hero-content">
       <!-- <h1 class="embroidery-hero-title">NEW ARRIVALS</h1> -->
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">New Arrivals</span>
        </nav>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<section class="section">
    <?php if (!empty($categories_with_products)): ?>
        <?php foreach ($categories_with_products as $index => $item): 
            $category = $item['category'];
            $subcategories = $item['subcategories'] ?? [];
            $category_id = 'category-' . ($category['id'] ?? $index);
        ?>
            <div class="category-section-wrapper" id="<?= $category_id ?>" data-category="<?= $category_id ?>">
                <h3 class="category-title">
                    <?= htmlspecialchars($category['category_name'] ?? 'Products') ?>
                </h3>
                
                <?php foreach ($subcategories as $subcat_item): 
                    $subcategory = $subcat_item['subcategory'];
                    $products = $subcat_item['products'];
                ?>
                    <div class="subcategory-section" style="margin-bottom: 50px;">
                        <div class="card-grid">
                            <?php foreach ($products as $p): 
                                $product_image = $p['image'] ?? $p['product_image'] ?? '';
                                if (!empty($product_image)) {
                                    $imagePath = 'uploads/products/' . $product_image;
                                    if (!file_exists($imagePath)) {
                                        $imagePath = 'assets/images/beads.jpg';
                                    }
                                } else {
                                    $imagePath = 'assets/images/beads.jpg';
                                }
                                
                                $product_name = htmlspecialchars($p['name'] ?? $p['product_name'] ?? 'Product');
                                $product_price = number_format($p['price'] ?? 0, 2);
                                $product_mrp = isset($p['mrp']) && $p['mrp'] > $p['price'] ? number_format($p['mrp'], 2) : null;
                                $discount = isset($p['discount_percent']) ? $p['discount_percent'] : 0;
                            ?>
                                <div class="card">
                                    <div class="card-image-wrapper">
                                        <img src="<?= $imagePath ?>" alt="<?= $product_name ?>" onerror="this.src='assets/images/beads.jpg'">
                                        <?php if ($discount > 0): ?>
                                            <div class="product-badge">-<?= $discount ?>%</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-content">
                                        <h4><?= $product_name ?></h4>
                                        <div class="card-pricing">
                                            <?php if ($product_mrp): ?>
                                                <span class="card-mrp">₹<?= $product_mrp ?></span>
                                            <?php endif; ?>
                                            <span class="card-price">₹<?= $product_price ?></span>
                                        </div>
                                        <a href="product.php?id=<?= $p['id']; ?>" class="login-btn">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: #999;">
            <i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; color: #ddd;"></i>
            <p style="font-size: 18px; margin: 0;">No products found</p>
            <p style="font-size: 14px; margin-top: 10px; color: #bbb;">Please add products to the database.</p>
        </div>
    <?php endif; ?>
</section>

<style>
/* EMBROIDERY HERO BANNER */
.embroidery-hero-banner {
    padding: 80px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.embroidery-hero-video {
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

.embroidery-hero-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.embroidery-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
}

.embroidery-hero-title {
    font-size: 72px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 4px;
    margin: 0 0 20px 0;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
}

.breadcrumb {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 16px;
    color: #fff;
}

.breadcrumb a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #2fc7b4;
}

.breadcrumb-separator {
    color: #fff;
    margin: 0 4px;
}

.breadcrumb-current {
    color: #2fc7b4;
    font-weight: 600;
}

/* Override any animations that might hide content */
.section {
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
    background: #ffffff;
    padding: 60px 20px !important;
    max-width: 1200px;
    margin: 0 auto;
}

.category-section-wrapper {
    margin-bottom: 60px;
    padding: 40px 0;
    background: #ffffff;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
}

@keyframes gradientBorder {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.beautiful-dropdown-small {
    padding: 12px 45px 12px 20px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    border: 2px solid #2fc7b4 !important;
    border-radius: 25px !important;
    background: linear-gradient(135deg, #ffffff, #f0fdf4) !important;
    color: #2b2b2b !important;
    cursor: pointer !important;
    outline: none !important;
    min-width: 220px !important;
    max-width: 250px !important;
    box-shadow: 0 6px 20px rgba(47, 199, 180, 0.25), inset 0 2px 5px rgba(255, 255, 255, 0.8) !important;
    transition: all 0.3s ease !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: none !important;
    position: relative !important;
    z-index: 10 !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.beautiful-dropdown-small:hover {
    border-color: #2fa76b !important;
    box-shadow: 0 8px 25px rgba(47, 167, 107, 0.35), inset 0 2px 5px rgba(255, 255, 255, 0.9) !important;
    transform: translateY(-2px) !important;
    background: linear-gradient(135deg, #ffffff, #e8f8f0) !important;
}

.beautiful-dropdown-small:focus {
    border-color: #e91e63 !important;
    box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4), inset 0 2px 5px rgba(255, 255, 255, 0.9) !important;
    outline: none !important;
    background: linear-gradient(135deg, #ffffff, #fff5f5) !important;
}

.beautiful-dropdown-small option {
    padding: 12px 15px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    color: #2b2b2b !important;
    border: none !important;
}


.category-title {
    font-size: 28px;
    font-weight: 700;
    color: #2b2b2b;
    margin-bottom: 30px;
    text-align: left;
    text-transform: capitalize;
    letter-spacing: 0.5px;
    position: relative;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
}

.title-underline {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #2fa76b, #2fc7b4, #e91e63);
    border-radius: 2px;
    animation: expandLine 0.6s ease-out;
}

@keyframes expandLine {
    from {
        width: 0;
    }
    to {
        width: 100px;
    }
}

.subcategory-section {
    margin-bottom: 60px;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
}

.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: grid !important;
}

.card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    animation: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
    max-width: 220px;
    margin: 0 auto;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2fa76b, #2fc7b4, #e91e63);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(47, 167, 107, 0.25);
    border-color: rgba(47, 167, 107, 0.3);
}

.card:hover::before {
    opacity: 1;
}

.card:nth-child(3n+1) {
    border-top-color: #2fa76b;
}

.card:nth-child(3n+2) {
    border-top-color: #2fc7b4;
}

.card:nth-child(3n+3) {
    border-top-color: #e91e63;
}

.card-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #e8f8f0 0%, #f0fdf4 50%, #fff5f5 100%);
}

.card-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.card:hover .card-image-wrapper img {
    transform: scale(1.1);
}

.product-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #e91e63, #c2185b);
    color: #fff;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    z-index: 2;
    box-shadow: 0 4px 10px rgba(233, 30, 99, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-content {
    padding: 15px;
}

.card-content h4 {
    font-size: 14px;
    font-weight: 600;
    color: #2b2b2b;
    margin: 0 0 10px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 36px;
}

.card-pricing {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.card-mrp {
    font-size: 14px;
    color: #999;
    text-decoration: line-through;
}

.card-price {
    font-size: 18px;
    font-weight: 700;
    color: #2fa76b;
}

.login-btn {
    width: 100%;
    padding: 10px 15px;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    box-shadow: 0 4px 15px rgba(47, 167, 107, 0.3);
    position: relative;
    overflow: hidden;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.login-btn:hover {
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
}

.login-btn:hover::before {
    width: 300px;
    height: 300px;
}

@media (max-width: 768px) {
    .card-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .subcategory-title {
        font-size: 20px;
    }
}
</style>

<script>
// Category filter functionality with dropdown
function filterCategoryByDropdown(categoryId) {
    console.log('Filtering category:', categoryId);
    
    // Remove any existing no products message
    const existingMsg = document.getElementById('noProductsMessage');
    if (existingMsg) {
        existingMsg.remove();
    }
    
    const allSections = document.querySelectorAll('.category-section-wrapper');
    console.log('Total sections found:', allSections.length);
    
    if (categoryId === 'all') {
        // Show all category sections
        allSections.forEach((section, index) => {
            section.style.display = 'block';
            section.style.opacity = '0';
            setTimeout(() => {
                section.style.opacity = '1';
                section.style.transition = 'opacity 0.5s ease';
            }, index * 50);
        });
        
        // Scroll to top smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        // Hide all category sections first
        allSections.forEach(section => {
            section.style.display = 'none';
            section.style.opacity = '0';
        });
        
        // Show selected category with animation
        const selectedSection = document.getElementById(categoryId);
        console.log('Selected section:', selectedSection);
        
        if (selectedSection) {
            selectedSection.style.display = 'block';
            selectedSection.style.opacity = '0';
            selectedSection.style.transition = 'opacity 0.5s ease';
            
            setTimeout(() => {
                selectedSection.style.opacity = '1';
                // Scroll to the selected section
                setTimeout(() => {
                    selectedSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }, 50);
        } else {
            // If category section doesn't exist (no products), show message
            const section = document.querySelector('.section');
            if (section) {
                const msg = document.createElement('div');
                msg.id = 'noProductsMessage';
                msg.style.cssText = 'text-align: center; padding: 60px 20px; color: #999; background: linear-gradient(135deg, #f0fdf4, #fff5f5); border-radius: 20px; margin: 40px 0;';
                msg.innerHTML = '<i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; color: #ddd;"></i><p style="font-size: 18px; margin: 0;">No products found in this category</p>';
                section.appendChild(msg);
            }
        }
    }
}

// Keep content visible - no timers or hiding
document.addEventListener('DOMContentLoaded', function() {
    // Force content to stay visible
    function keepVisible() {
        const section = document.querySelector('.section');
        if (section) {
            section.style.opacity = '1';
            section.style.visibility = 'visible';
            section.style.display = 'block';
            section.style.animation = 'none';
        }
        
        const cards = document.querySelectorAll('.card, .card-grid, .category-section-wrapper');
        cards.forEach(card => {
            card.style.opacity = '1';
            card.style.visibility = 'visible';
            if (card.classList.contains('card-grid')) {
                card.style.display = 'grid';
            } else if (card.classList.contains('category-section-wrapper')) {
                card.style.display = 'block';
            } else {
                card.style.display = '';
            }
            card.style.animation = 'none';
        });
    }
    
    // Run immediately
    keepVisible();
    
    // Initialize - show all categories by default
    const allSections = document.querySelectorAll('.category-section-wrapper');
    allSections.forEach(section => {
        section.style.display = 'block';
        section.style.opacity = '1';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
