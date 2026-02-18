<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'includes/db.php';
include 'includes/header.php';

$search = "";

if (isset($_GET['query'])) {
    $search = mysqli_real_escape_string($conn, $_GET['query']);
}

// Check which columns exist in products table
$products_columns = [];
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
if ($columns_check) {
    while ($col = mysqli_fetch_assoc($columns_check)) {
        $products_columns[] = $col['Field'];
    }
} else {
    // If table doesn't exist or query fails, show error or log it
    error_log("Search Query failed: " . mysqli_error($conn));
}

// Build search query - search by product_name, SKU, and description
// For full search page: include products that contain the term (broader search)
$search_conditions = [];

if (in_array('product_name', $products_columns)) {
    $search_conditions[] = "product_name LIKE '%$search%'";
} elseif (in_array('name', $products_columns)) {
    $search_conditions[] = "name LIKE '%$search%'";
}

// Add SKU search if column exists (contains)
if (in_array('sku', $products_columns)) {
    $search_conditions[] = "sku LIKE '%$search%'";
}

// Add description search if column exists (contains)
if (in_array('description', $products_columns)) {
    $search_conditions[] = "description LIKE '%$search%'";
}

if (empty($search_conditions)) {
    // Fallback if no searchable columns found
    $search_conditions[] = "id LIKE '%$search%'";
}

$where_clause = "WHERE (" . implode(" OR ", $search_conditions) . ")";

// Add status filter if column exists - show active and out_of_stock, exclude inactive and NULL
if (in_array('status', $products_columns)) {
    $where_clause .= " AND ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
}

// Order by: products starting with query first, then others
$order_by = "";
if (in_array('product_name', $products_columns)) {
    $order_by = "ORDER BY 
        CASE 
            WHEN product_name LIKE '$search%' THEN 1 
            WHEN product_name LIKE '%$search%' THEN 2 
            ELSE 3 
        END,
        product_name ASC";
} elseif (in_array('name', $products_columns)) {
    $order_by = "ORDER BY 
        CASE 
            WHEN name LIKE '$search%' THEN 1 
            WHEN name LIKE '%$search%' THEN 2 
            ELSE 3 
        END,
        name ASC";
} else {
    $order_by = "ORDER BY id DESC";
}

$query = "SELECT * FROM products $where_clause $order_by";

$result = mysqli_query($conn, $query);
$total_results = $result ? mysqli_num_rows($result) : 0;
?>

<section class="section" style="padding: 40px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="margin-bottom: 30px; color: #2b2b2b; font-size: 28px;">
            Search Results for "<span style="color: #2fc7b4;"><?php echo htmlspecialchars($search); ?></span>"
            <small style="display: block; font-size: 14px; color: #666; font-weight: normal; margin-top: 5px;">
                Found <?php echo $total_results; ?> product(s)
            </small>
        </h2>

        <?php if ($total_results > 0): ?>
            <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    $product_name = isset($row['product_name']) ? $row['product_name'] : (isset($row['name']) ? $row['name'] : 'Product');
                    $product_id = isset($row['id']) ? $row['id'] : 0;
                    $price = isset($row['price']) ? $row['price'] : 0;
                    $mrp = isset($row['mrp']) ? $row['mrp'] : 0;
                    $sku = isset($row['sku']) ? $row['sku'] : '';
                    
                    // Get product image
                    $image_path = 'assets/images/beads.jpg';
                    if (isset($row['image']) && !empty($row['image'])) {
                        if (file_exists('uploads/products/' . $row['image'])) {
                            $image_path = 'uploads/products/' . $row['image'];
                        }
                    }
                    
                    $discount = 0;
                    if ($mrp > $price && $mrp > 0) {
                        $discount = round((($mrp - $price) / $mrp) * 100);
                    }
                ?>
                    <div class="card" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; border: 2px solid transparent;" onclick="window.location.href='product.php?id=<?= $product_id ?>'">
                        <div class="card-image-wrapper" style="position: relative; width: 100%; padding-top: 100%; overflow: hidden; background: #f5f5f5;">
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product_name) ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" onerror="this.src='assets/images/beads.jpg'">
                            <?php if ($discount > 0): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background: #ff6b35; color: #fff; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">-<?= $discount ?>%</div>
                            <?php endif; ?>
                        </div>
                        <div class="card-content" style="padding: 15px;">
                            <h4 style="font-size: 14px; font-weight: 600; color: #2b2b2b; margin-bottom: 10px; line-height: 1.4; min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($product_name) ?>
                            </h4>
                            <?php if ($sku): ?>
                                <small style="color: #666; font-size: 11px; display: block; margin-bottom: 8px;">SKU: <?= htmlspecialchars($sku) ?></small>
                            <?php endif; ?>
                            <div class="card-pricing" style="margin-bottom: 12px;">
                                <?php if ($mrp > $price && $mrp > 0): ?>
                                    <span style="font-size: 13px; color: #999; text-decoration: line-through; margin-right: 8px;">₹<?= number_format($mrp, 2) ?></span>
                                <?php endif; ?>
                                <span style="font-size: 18px; font-weight: 700; color: #2fa76b;">₹<?= number_format($price, 2) ?></span>
                            </div>
                            <a href="product.php?id=<?= $product_id ?>" class="login-btn" style="display: inline-block; width: 100%; text-align: center; padding: 10px; background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">View Product</a>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 12px;">
                <i class="fa fa-search" style="font-size: 64px; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">No products found</h3>
                <p style="color: #999;">Try searching with different keywords or SKU</p>
                <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
