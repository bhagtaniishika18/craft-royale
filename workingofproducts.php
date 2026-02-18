<?php
include "includes/db.php";
include "includes/header.php";

// Get filter parameters
$selected_category = $_GET['category'] ?? '';
$selected_subcategory = $_GET['subcategory'] ?? '';
$selected_subcategory_slug = $_GET['subcategory'] ?? ''; // Can be slug or ID
$search_query = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Handle legacy URL format (cat=slug&sub=slug)
$catSlug = $_GET['cat'] ?? '';
$subSlug = $_GET['sub'] ?? '';

// Map URL slugs to subcategory names (for backward compatibility)
$subcategorySlugMap = [
    'french-wire-dabka' => 'French Wire / Dabka',
    'bullion-wire-nakshi' => 'Bullion Wire/Nakshi',
    'gijai-gimp-stiff' => 'Gijai / Gimp / Stiff',
    'mukaish-metal-strip' => 'Mukaish Metal Strip',
    'zari-threads' => 'Zari Threads',
    'badla-flat-metallic-threads' => 'Badla Flat Metallic Threads',
    'badla-dori-metallic-braided-cord' => 'Badla Dori/ Metallic Braided Cord',
    'cotton-threads' => 'Cotton Threads',
    'crochet-cotton-threads' => 'Crochet Cotton Threads',
    'art-silk-threads' => 'Art Silk Threads',
    'nylon-threads' => 'Nylon Threads',
    'sewing-threads' => 'Sewing Threads'
];

// If using legacy format, convert to new format
if ($subSlug && isset($subcategorySlugMap[$subSlug])) {
    $selected_subcategory_slug = $subcategorySlugMap[$subSlug];
    // Find the subcategory ID
    $subName = mysqli_real_escape_string($conn, $subcategorySlugMap[$subSlug]);
    $subFind = mysqli_query($conn, "SELECT id, category_id FROM subcategories WHERE subcategory_name = '$subName' LIMIT 1");
    if ($subFind && mysqli_num_rows($subFind) > 0) {
        $subData = mysqli_fetch_assoc($subFind);
        $selected_subcategory = $subData['id'];
        $selected_category = $subData['category_id'];
    }
}

// Fetch all categories
$categories_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$categories = [];
while ($cat = mysqli_fetch_assoc($categories_query)) {
    $categories[$cat['id']] = $cat;
}

// Fetch all subcategories with their category info
$subcategories_query = mysqli_query($conn, "SELECT s.*, c.category_name 
                                               FROM subcategories s 
                                               LEFT JOIN categories c ON s.category_id = c.id 
                                               ORDER BY c.category_name, s.subcategory_name");
$subcategories = [];
$subcategories_by_category = [];
while ($sub = mysqli_fetch_assoc($subcategories_query)) {
    $subcategories[$sub['id']] = $sub;
    if (!isset($subcategories_by_category[$sub['category_id']])) {
        $subcategories_by_category[$sub['category_id']] = [];
    }
    $subcategories_by_category[$sub['category_id']][] = $sub;
}

// Check which columns exist in products table
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Build products query - only add filters if columns exist
$where_conditions = [];
if (in_array('status', $products_columns)) {
    // Show active and out_of_stock products, but exclude inactive and NULL status
    $where_conditions[] = "((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
}

if ($selected_category && in_array('category_id', $products_columns)) {
    $selected_category = (int)$selected_category;
    $where_conditions[] = "p.category_id = '$selected_category'";
}

if ($selected_subcategory && in_array('subcategory_id', $products_columns)) {
    // Check if it's a numeric ID or a name/slug
    if (is_numeric($selected_subcategory)) {
        $selected_subcategory = (int)$selected_subcategory;
        $where_conditions[] = "p.subcategory_id = '$selected_subcategory'";
    } else {
        // It's a name or slug, find the ID
        $subName = mysqli_real_escape_string($conn, $selected_subcategory);
        $subFind = mysqli_query($conn, "SELECT id FROM subcategories WHERE subcategory_name = '$subName' OR subcategory_slug = '$subName' LIMIT 1");
        if ($subFind && mysqli_num_rows($subFind) > 0) {
            $subData = mysqli_fetch_assoc($subFind);
            $where_conditions[] = "p.subcategory_id = '{$subData['id']}'";
            $selected_subcategory = $subData['id']; // Update to ID for display
        }
    }
}

if ($search_query) {
    $search_escaped = mysqli_real_escape_string($conn, $search_query);
    $search_conditions = [];
    if (in_array('name', $products_columns)) {
        $search_conditions[] = "p.name LIKE '%$search_escaped%'";
    }
    if (in_array('description', $products_columns)) {
        $search_conditions[] = "p.description LIKE '%$search_escaped%'";
    }
    if (in_array('sku', $products_columns)) {
        $search_conditions[] = "p.sku LIKE '%$search_escaped%'";
    }
    if (!empty($search_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
    }
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Sorting - check which columns exist
$order_by = "ORDER BY p.id DESC"; // Default fallback
if (in_array('created_at', $products_columns)) {
    $order_by = "ORDER BY p.created_at DESC";
}

switch ($sort) {
    case 'price_low':
        if (in_array('price', $products_columns)) {
            $order_by = "ORDER BY p.price ASC";
        }
        break;
    case 'price_high':
        if (in_array('price', $products_columns)) {
            $order_by = "ORDER BY p.price DESC";
        }
        break;
    case 'name':
        if (in_array('name', $products_columns)) {
            $order_by = "ORDER BY p.name ASC";
        }
        break;
    case 'newest':
    default:
        if (in_array('created_at', $products_columns)) {
            $order_by = "ORDER BY p.created_at DESC";
        } else {
            $order_by = "ORDER BY p.id DESC";
        }
        break;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total products count - use alias to match WHERE conditions
$count_query = "SELECT COUNT(*) as total FROM products p";
if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}
$count_result = mysqli_query($conn, $count_query);
if ($count_result) {
    $count_data = mysqli_fetch_assoc($count_result);
    $total_products = $count_data ? $count_data['total'] : 0;
} else {
    // If query fails, try without WHERE conditions
    $count_query = "SELECT COUNT(*) as total FROM products";
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result) {
        $count_data = mysqli_fetch_assoc($count_result);
        $total_products = $count_data ? $count_data['total'] : 0;
    } else {
        $total_products = 0;
    }
}
$total_pages = $total_products > 0 ? ceil($total_products / $per_page) : 0;

// Get products - only join if columns exist
$products_query = "SELECT p.*";
$has_category_id = in_array('category_id', $products_columns);
$has_subcategory_id = in_array('subcategory_id', $products_columns);

if ($has_category_id) {
    $products_query .= ", c.category_name";
}
if ($has_subcategory_id) {
    $products_query .= ", s.subcategory_name";
}

$products_query .= " FROM products p";

if ($has_category_id) {
    $products_query .= " LEFT JOIN categories c ON p.category_id = c.id";
}
if ($has_subcategory_id) {
    $products_query .= " LEFT JOIN subcategories s ON p.subcategory_id = s.id";
}
                   
if (!empty($where_conditions)) {
    $products_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$products_query .= " $order_by LIMIT $per_page OFFSET $offset";

$products_result = mysqli_query($conn, $products_query);
if (!$products_result) {
    // Log the error for debugging
    error_log("Products query failed: " . mysqli_error($conn));
    error_log("Query: " . $products_query);
    
    // If query fails, try without joins and simpler conditions
    $simple_conditions = [];
    if (in_array('status', $products_columns)) {
        // Show active and out_of_stock products, but exclude inactive and NULL status
        $simple_conditions[] = "((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    }
    
    // Add category filter if it exists
    if ($selected_category && in_array('category_id', $products_columns)) {
        $simple_conditions[] = "category_id = " . (int)$selected_category;
    }
    
    $products_query = "SELECT * FROM products";
    if (!empty($simple_conditions)) {
        $products_query .= " WHERE " . implode(" AND ", $simple_conditions);
    }
    $products_query .= " $order_by LIMIT $per_page OFFSET $offset";
    $products_result = mysqli_query($conn, $products_query);
    
    if (!$products_result) {
        error_log("Fallback query also failed: " . mysqli_error($conn));
        $products_result = false;
    }
}

// Get subcategory info for banner
$subcategory_info = null;
if ($selected_subcategory) {
    $subId = is_numeric($selected_subcategory) ? (int)$selected_subcategory : null;
    if ($subId && isset($subcategories[$subId])) {
        $subcategory_info = $subcategories[$subId];
    } else {
        // Try to find by name if it's not numeric
        $subName = mysqli_real_escape_string($conn, $selected_subcategory);
        $subFind = mysqli_query($conn, "SELECT s.*, c.category_name, c.id as category_id 
                                         FROM subcategories s 
                                         LEFT JOIN categories c ON s.category_id = c.id 
                                         WHERE s.subcategory_name = '$subName' OR s.subcategory_slug = '$subName' 
                                         LIMIT 1");
        if ($subFind && mysqli_num_rows($subFind) > 0) {
            $subcategory_info = mysqli_fetch_assoc($subFind);
            $selected_subcategory = $subcategory_info['id'];
            $selected_category = $subcategory_info['category_id'];
        }
    }
}
?>

<!-- HERO BANNER (Only show for subcategory pages) -->
<?php if ($subcategory_info): 
    // Map subcategory names to image paths (include variations)
    $subcategory_image_map = [
        'French Wire / Dabka' => 'assets/images/frenchwirebanner.jpg',
        'French Wire/Dabka' => 'assets/images/frenchwirebanner.jpg',
        'French Wire/ Dabka' => 'assets/images/frenchwirebanner.jpg',
        'French Wire /Dabka' => 'assets/images/frenchwirebanner.jpg',
        'Bullion Wire/Nakshi' => 'assets/images/embroidery/bullion.jpg',
        'Gijai / Gimp / Stiff' => 'assets/images/embroidery/gijai.jpg',
        'Mukaish Metal Strip' => 'assets/images/embroidery/mukaish.jpg',
        'Zari Threads' => 'assets/images/embroidery/zari-threads.jpg',
        'Badla Flat Metallic Threads' => 'assets/images/embroidery/badla-flat.jpg',
        'Badla Dori/ Metallic Braided Cord' => 'assets/images/embroidery/badla-dori.jpg',
        'Cotton Threads' => 'assets/images/embroidery/cotton-threads.jpg',
        'Art Silk Threads' => 'assets/images/embroidery/art-silk.jpg',
        'Nylon Threads' => 'assets/images/embroidery/nylon-threads.jpg',
        'Sewing Threads' => 'assets/images/embroidery/sewing-threads.jpg',
        'Alphabet Beads' => 'assets/images/banner_subc/ab.jpg',
        'Crystal Pearls' => 'assets/images/banner_subc/cp.jpg',
        'Czech Preciosa Seed beads' => 'assets/images/banner_subc/cpsb.jpg',
        'Evil Eye Beads' => 'assets/images/banner_subc/ee.jpg',
        'Faceted Czech Beads' => 'assets/images/banner_subc/fcb.jpg',
        'Glass Pearls' => 'assets/images/banner_subc/gp.jpg',
        'Grade A Bugle Beads' => 'assets/images/banner_subc/ga.jpg',
        'Grade A Cylinder Beads' => 'assets/images/banner_subc/gacb.jpg',
        'Grade A Seed Beads' => 'assets/images/banner_subc/gasb.jpg',
        'M.G.B Bugle Beads' => 'assets/images/banner_subc/mgb.jpg',
        'Metal Beads/Pipes' => 'assets/images/banner_subc/mbp.jpg',
        'MGB 2Cut Beads' => 'assets/images/banner_subc/mgb2cut.jpg',
        'Miyuki 2Cut Beads' => 'assets/images/banner_subc/mb.jpg',
        'Miyuki Seed Beads' => 'assets/images/banner_subc/miyu.jpg',
        'Natural Stone Beads' => 'assets/images/banner_subc/nsb.jpg',
        'Preciosa 2 Cut Beads'=>  'assets/images/banner_subc/m1.jpg',
        'Tila Beads' => 'assets/images/banner_subc/tb.jpg',
        'Toho Hex 2Cut Beads' => 'assets/images/banner_subc/p1.jpg',
        'Crochet Thread Rings'=> 'assets/images/banner_subc/ctr.jpg',
        'Pom Poms' => 'assets/images/banner_subc/pp.jpg',
        'Raffia Ribbon'=> 'assets/images/banner_subc/rr.jpg',
        'Ribbon'=> 'assets/images/banner_subc/r.jpg',
        'Seashells'=> 'assets/images/banner_subc/ss.jpg',
        'Shisha Mirror/Sticker'=> 'assets/images/banner_subc/sms.jpg',
        'Tassels/Fringes/Latkan'=> 'assets/images/banner_subc/tfl.jpg',
        'Cabochon Stones'=> 'assets/images/banner_subc/cs.jpg',
        'Cotton Laces'=> 'assets/images/banner_subc/cl.jpg',
        'Embroidery Lace'=> 'assets/images/banner_subc/el.jpg',
        'Embroidery Patches'=> 'assets/images/banner_subc/ep.jpg',
        'Hotfix Crystals'=> 'assets/images/banner_subc/hc.jpg',
        'Kundan Stones'=> 'assets/images/banner_subc/ks.jpg',
        'Metal Sequins'=> 'assets/images/banner_subc/ms.jpg',
        'MOP Buttons'=> 'assets/images/banner_subc/mp.jpg',
        'Rhinestones Cup Chains'=> 'assets/images/banner_subc/rcc.jpg',
        'Sew on Crystals'=> 'assets/images/banner_subc/soc.jpg',
        'Sew on Rhinestones'=> 'assets/images/banner_subc/sor.jpg',
        'Sew on Sequins'=> 'assets/images/banner_subc/sos.jpg',
        'Snap Buttons'=> 'assets/images/banner_subc/sb.jpg',
        'Studs/Spikes'=> 'assets/images/banner_subc/ss1.jpg',
        'Swarovski Hot Fix'=> 'assets/images/banner_subc/shf.jpg',
        'Wooden Buttons'=> 'assets/images/banner_subc/wb.jpg',
        'Rivoli/Chatons'=> 'assets/images/banner_subc/rs.jpg',
        'Badla Flat Metallic Threads'=> 'assets/images/banner_subc/bfmt.jpg',
        'Bullion Wire/ Nakshi'=> 'assets/images/banner_subc/bwr.jpg',
        'Crochet Cotton Threads'=> 'assets/images/banner_subc/ct.jpg',
        'Gijai/ Gimp/ Stiff'=> 'assets/images/banner_subc/ggs.jpg',
        'Waxed Cotton Cord'=> 'assets/images/banner_subc/wct.jpg',
        'Beading Wire'=> 'assets/images/banner_subc/bw.jpg',
        'Bezel Connector'=> 'assets/images/banner_subc/bc.jpg',
        'Brooch'=> 'assets/images/banner_subc/b.jpg',
        'Charms'=> 'assets/images/banner_subc/c.jpg',
        'Filigree Stamping'=> 'assets/images/banner_subc/fs.jpg',
        'Jewelry Findings'=> 'assets/images/banner_subc/jf.jpg',
        'Metal Chains'=> 'assets/images/banner_subc/mc.jpg',
        'Nylon Jewelry Cord'=> 'assets/images/banner_subc/nc.jpg',
        'Satin Cord/ Malai Dori'=> 'assets/images/banner_subc/sc.jpg',

    ];
    
    $subcategory_name = trim($subcategory_info['subcategory_name']);
    
    // Try exact match first
    $banner_image = isset($subcategory_image_map[$subcategory_name]) 
        ? $subcategory_image_map[$subcategory_name] 
        : null;
    
    // If not found, try case-insensitive match with normalized spaces
    if (!$banner_image) {
        $normalized_name = preg_replace('/\s+/', ' ', trim($subcategory_name));
        foreach ($subcategory_image_map as $key => $image) {
            $normalized_key = preg_replace('/\s+/', ' ', trim($key));
            if (strcasecmp($normalized_key, $normalized_name) === 0) {
                $banner_image = $image;
                break;
            }
        }
    }
    
    // If still not found, try partial match (contains "French Wire")
    if (!$banner_image) {
        if (stripos($subcategory_name, 'French Wire') !== false || stripos($subcategory_name, 'Dabka') !== false) {
            $banner_image = 'assets/images/frenchwirebanner.jpg';
        }
    }
    
    // Final fallback
    if (!$banner_image) {
        $banner_image = 'assets/images/beads.jpg';
    }
    
    // Check if this subcategory should use video (only for Miyuki 2Cut Beads)
    $use_video = false;
    $banner_video = null;
    
    // Map subcategories that should use video
    $subcategory_video_map = [
    ];
    
    // Check if current subcategory should use video
    $normalized_subcategory = preg_replace('/\s+/', ' ', trim($subcategory_name));
    foreach ($subcategory_video_map as $key => $video_path) {
        $normalized_key = preg_replace('/\s+/', ' ', trim($key));
        if (stripos($normalized_subcategory, $normalized_key) !== false || 
            stripos($normalized_subcategory, 'Miyuki') !== false && 
            (stripos($normalized_subcategory, '2Cut') !== false || stripos($normalized_subcategory, '2 Cut') !== false)) {
            $use_video = true;
            $banner_video = $video_path;
            break;
        }
    }
    
    // Ensure path starts correctly (relative to root)
    if (strpos($banner_image, '/') !== 0 && strpos($banner_image, 'http') !== 0) {
        // Path is already relative, which is correct
    }
    // Add cache buster to force reload
    $banner_image .= '?v=' . time();
    if ($banner_video) {
        $banner_video .= '?v=' . time();
    }
?>
<section class="subcategory-hero-banner">
    <?php if ($use_video && $banner_video): ?>
        <!-- Video Banner for Miyuki 2Cut Beads -->
        <video class="subcategory-hero-video" autoplay muted loop playsinline>
            <source src="<?= htmlspecialchars($banner_video) ?>" type="video/mp4">
            <!-- Fallback image if video doesn't load -->
            <img src="<?= htmlspecialchars($banner_image) ?>" alt="<?= htmlspecialchars($subcategory_info['subcategory_name']) ?> Background" style="width: 100%; height: 100%; object-fit: cover;">
        </video>
    <?php else: ?>
        <!-- Image Banner for other subcategories -->
        <div class="subcategory-hero-image" style="background-image: url('<?= htmlspecialchars($banner_image) ?>');"></div>
    <?php endif; ?>
    <div class="subcategory-hero-overlay"></div>
    <div class="subcategory-hero-content">
        <h1 class="subcategory-hero-title"><?= htmlspecialchars(strtoupper($subcategory_info['subcategory_name'])) ?></h1>
        <nav class="subcategory-breadcrumb">
            <a href="index.php">Home</a> 
            <span class="breadcrumb-separator">></span> 
            <?php if ($selected_category && isset($categories[$selected_category])): ?>
                <a href="products.php?category=<?= $selected_category ?>"><?= htmlspecialchars($categories[$selected_category]['category_name']) ?></a>
                <span class="breadcrumb-separator">></span>
            <?php endif; ?>
            <span class="breadcrumb-current"><?= htmlspecialchars($subcategory_info['subcategory_name']) ?></span>
        </nav>
    </div>
</section>
<?php endif; ?>

<style>
    /* Subcategory Hero Banner */
    .subcategory-hero-banner {
        position: relative;
        width: 100%;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 0;
    }

    .subcategory-hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
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
        z-index: 1;
    }

    .subcategory-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
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
        font-size: 48px;
        font-weight: 800;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
    }

    .subcategory-breadcrumb {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 16px;
        color: #fff;
        flex-wrap: wrap;
    }

    .subcategory-breadcrumb a {
        color: #fff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .subcategory-breadcrumb a:hover {
        color: #2fc7b4;
    }

    .subcategory-breadcrumb .breadcrumb-separator {
        color: #fff;
        margin: 0 4px;
    }

    .subcategory-breadcrumb .breadcrumb-current {
        color: #2fc7b4;
        font-weight: 600;
    }

    .products-unified-page {
        min-height: 80vh;
        background: #f9f9f9;
        padding: 40px 0;
    }

    /* Breadcrumb Navigation */
    .products-breadcrumb-container {
        max-width: 1400px;
        margin: 0 auto 20px;
        padding: 0 20px;
    }

    .products-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
        padding: 15px 0;
    }

    .products-breadcrumb a {
        color: #666;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .products-breadcrumb a:hover {
        color: #2fc7b4;
    }

    .products-breadcrumb .breadcrumb-separator {
        color: #999;
        margin: 0 4px;
    }

    .products-breadcrumb .breadcrumb-current {
        color: #2b2b2b;
        font-weight: 600;
    }

    .products-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Main Content */
    .products-main {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .products-title {
        font-size: 28px;
        font-weight: 800;
        color: #2b2b2b;
        margin: 0;
    }

    .products-toolbar {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .products-count {
        font-size: 14px;
        color: #666;
    }

    .products-sort select {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        background: #fff;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #2fc7b4;
    }

    .product-image-wrapper {
        position: relative;
        width: 100%;
        padding-top: 100%;
        overflow: hidden;
        background: #f5f5f5;
    }

    .product-image-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-image-wrapper img {
        transform: scale(1.1);
    }

    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff6b35;
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        z-index: 2;
    }

    .product-badge.sold-out {
        background: #dc3545;
    }

    .product-info {
        padding: 15px;
    }

    .product-category-tag {
        font-size: 11px;
        color: #2fc7b4;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .product-name {
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

    .product-pricing {
        margin-bottom: 12px;
    }

    .product-mrp {
        font-size: 13px;
        color: #999;
        text-decoration: line-through;
        margin-right: 8px;
    }

    .product-price {
        font-size: 18px;
        font-weight: 700;
        color: #2fa76b;
    }

    .product-actions {
        display: flex;
        gap: 8px;
        position: relative;
        z-index: 100;
        pointer-events: auto;
    }

    .btn-add-cart {
        flex: 1;
        background: #2fc7b4;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        z-index: 100;
        pointer-events: auto;
    }

    .btn-add-cart:hover {
        background: #2fa76b;
        transform: translateY(-2px);
    }

    .btn-add-cart:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .btn-coming-soon {
        flex: 1;
        background: #999;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: not-allowed;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 40px;
    }

    .pagination a,
    .pagination span {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        text-decoration: none;
        color: #2b2b2b;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        border-color: #2fc7b4;
        color: #2fc7b4;
    }

    .pagination .current {
        background: #2fc7b4;
        color: #fff;
        border-color: #2fc7b4;
    }

    .empty-products {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-products i {
        font-size: 64px;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-products h3 {
        color: #999;
        margin-bottom: 10px;
    }

    @media (max-width: 968px) {
        .subcategory-hero-title {
            font-size: 36px;
        }

        .subcategory-breadcrumb {
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .subcategory-hero-title {
            font-size: 28px;
            letter-spacing: 1px;
        }

        .subcategory-breadcrumb {
            font-size: 12px;
        }
    }
</style>

<div class="products-unified-page">
    <!-- Breadcrumb Navigation (Only show if no banner) -->
    <?php if ((!$subcategory_info) && ($selected_subcategory || $selected_category)): ?>
    <div class="products-breadcrumb-container">
        <nav class="products-breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb-separator">></span>
            <?php if ($selected_category && isset($categories[$selected_category])): ?>
                <a href="products.php?category=<?= $selected_category ?>"><?= htmlspecialchars($categories[$selected_category]['category_name']) ?></a>
            <?php endif; ?>
            <?php if ($selected_subcategory): ?>
                <?php if ($selected_category): ?>
                    <span class="breadcrumb-separator">></span>
                <?php endif; ?>
                <?php
                $subId = is_numeric($selected_subcategory) ? (int)$selected_subcategory : null;
                $subcategory_name = '';
                if ($subId && isset($subcategories[$subId])) {
                    $subcategory_name = $subcategories[$subId]['subcategory_name'];
                } elseif (isset($subcategorySlugMap[$selected_subcategory_slug])) {
                    $subcategory_name = $subcategorySlugMap[$selected_subcategory_slug];
                } else {
                    $subcategory_name = $selected_subcategory_slug;
                }
                ?>
                <span class="breadcrumb-current"><?= htmlspecialchars($subcategory_name) ?></span>
            <?php elseif ($selected_category && isset($categories[$selected_category])): ?>
                <span class="breadcrumb-current"><?= htmlspecialchars($categories[$selected_category]['category_name']) ?></span>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
    
    <div class="products-container">
        <!-- Main Products Area -->
        <main class="products-main">
            <div class="products-header">
                <h1 class="products-title">
                    <?php
                    if ($selected_subcategory) {
                        $subId = is_numeric($selected_subcategory) ? (int)$selected_subcategory : null;
                        if ($subId && isset($subcategories[$subId])) {
                            echo htmlspecialchars($subcategories[$subId]['subcategory_name']);
                        } elseif (isset($subcategorySlugMap[$selected_subcategory_slug])) {
                            echo htmlspecialchars($subcategorySlugMap[$selected_subcategory_slug]);
                        } else {
                            echo htmlspecialchars($selected_subcategory_slug);
                        }
                    } elseif ($selected_category && isset($categories[$selected_category])) {
                        echo htmlspecialchars($categories[$selected_category]['category_name']);
                    } elseif ($search_query) {
                        echo "Search Results for: " . htmlspecialchars($search_query);
                    } else {
                        echo "All Products";
                    }
                    ?>
                </h1>
                <div class="products-toolbar">
                    <div class="products-count">
                        Showing <?= $total_products ?> product<?= $total_products != 1 ? 's' : '' ?>
                    </div>
                    <div class="products-sort">
                        <form method="get" style="display: inline;">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="category" value="<?= $selected_category ?>">
                            <?php endif; ?>
                            <?php if ($selected_subcategory): ?>
                                <input type="hidden" name="subcategory" value="<?= $selected_subcategory ?>">
                            <?php endif; ?>
                            <?php if ($search_query): ?>
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                            <?php endif; ?>
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
<div class="products-grid">
                    <?php while ($product = mysqli_fetch_assoc($products_result)): 
                        $discount = $product['discount_percent'] ?? 0;
                        $product_status = $product['status'] ?? 'active';
                        $product_stock = $product['stock'] ?? 0;
                        $isOutOfStock = ($product_status == 'out_of_stock' || $product_stock <= 0);
                        $isInactive = ($product_status == 'inactive');
                        
                        // Handle image path - check multiple possible column names
                        $product_image = $product['image'] ?? $product['product_image'] ?? '';
                        if (!empty($product_image)) {
                            $imagePath = 'uploads/products/' . $product_image;
                            // Check if file exists, if not use fallback
                            if (!file_exists($imagePath)) {
                                $imagePath = 'assets/images/beads.jpg';
                            }
                        } else {
                            $imagePath = 'assets/images/beads.jpg';
                        }
                    ?>
                        <div class="product-card" data-product-id="<?= $product['id'] ?>">
                            <div class="product-image-wrapper" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'" style="cursor: pointer;">
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?>" onerror="this.src='assets/images/beads.jpg'">
                                <?php if ($product_status == 'out_of_stock'): ?>
                                    <div class="product-badge sold-out">Out of Stock</div>
                                <?php elseif ($discount > 0): ?>
                                    <div class="product-badge">-<?= $discount ?>%</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
                                    <div class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></div>
                                <?php endif; ?>
                                <div class="product-name" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'" style="cursor: pointer;"><?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?></div>
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
                                    <?php if ($product_status == 'out_of_stock'): ?>
                                        <button class="btn-coming-soon" disabled>OUT OF STOCK</button>
                                    <?php else: ?>
                                        <button type="button" class="btn-add-cart" data-product-id="<?= $product['id'] ?>" style="position:relative;z-index:10;">ADD TO CART</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?= $i ?></span>
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
                <div class="empty-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// Make addToCart globally accessible
window.addToCart = function(productId, event) {
    console.log('🛒 addToCart called for product:', productId);
    
    // Get the event object - use passed event or global event
    const e = event || (typeof window.event !== 'undefined' ? window.event : null);
    if (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        console.log('✅ Event prevented and stopped');
    }
    
    const btn = e ? (e.target.closest('.btn-add-cart') || e.target) : document.querySelector('.btn-add-cart[data-product-id="' + productId + '"]');
    if (!btn) {
        console.error('❌ Add to cart button not found');
        return false;
    }
    const originalText = btn.innerHTML;
    
    // Disable button and show loading
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
            // Update cart count in header
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                const cartCount = document.getElementById('cartCount');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                    cartCount.setAttribute('data-count', data.cart_count);
                }
            }
            
            // Open the sidebar after small delay to ensure cart is updated
            console.log('🛒 Opening cart sidebar after adding product...');
            
            // Small delay to ensure cart is updated in session, then open sidebar
            setTimeout(function() {
                // Use openCartSidebar which loads fresh content from get-cart-sidebar.php
                if (typeof window.openCartSidebar === 'function') {
                    console.log('🔄 Calling window.openCartSidebar() to load from get-cart-sidebar.php...');
                    // This will open the sidebar AND load fresh content from get-cart-sidebar.php
                    window.openCartSidebar();
            } else {
                // Fallback: manually open sidebar and load content
                console.warn('⚠️ openCartSidebar not found, using fallback...');
                const sidebar = document.getElementById('cartSidebar');
                const overlay = document.getElementById('cartSidebarOverlay');
                const cartContent = document.getElementById('cartSidebarContent');
                
                if (sidebar && overlay) {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    // Load content from get-cart-sidebar.php
                    if (cartContent && typeof window.loadCartSidebar === 'function') {
                        window.loadCartSidebar();
                    } else if (cartContent) {
                        // Direct fetch from get-cart-sidebar.php
                        cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
                        const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                        fetch(basePath + 'get-cart-sidebar.php?t=' + Date.now())
                            .then(r => r.text())
                            .then(html => {
                                if (cartContent && html) {
                                    cartContent.innerHTML = html;
                                }
                            })
                            .catch(err => {
                                console.error('Error loading cart sidebar:', err);
                                if (cartContent) {
                                    cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Error loading cart</p></div>';
                                }
                            });
                    }
                }
            }
            }, 100); // Small delay to ensure cart is updated
            
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
        console.error('❌ Error:', error);
        alert('Error adding product to cart');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
    
    return false; // Prevent any default behavior
};

// Also create a local reference for backward compatibility
var addToCart = window.addToCart;

// Attach event listeners to all add to cart buttons when page loads
function setupAddToCartButtons() {
    console.log('🔧 Setting up add to cart buttons...');
    const addToCartButtons = document.querySelectorAll('.btn-add-cart[data-product-id]');
    console.log('Found', addToCartButtons.length, 'buttons with data-product-id');
    
    if (addToCartButtons.length === 0) {
        console.warn('⚠️ No buttons found! Searching for all .btn-add-cart buttons...');
        const allButtons = document.querySelectorAll('.btn-add-cart');
        console.log('Found', allButtons.length, 'total .btn-add-cart buttons');
    }
    
    addToCartButtons.forEach(function(btn, index) {
        const productId = btn.getAttribute('data-product-id');
        console.log('Button ' + (index + 1) + ': productId =', productId);
        
        if (productId) {
            // Remove any existing onclick
            btn.removeAttribute('onclick');
            
            // Remove old event listeners by cloning
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            // Add click event listener with capture phase
            newBtn.addEventListener('click', function(e) {
                console.log('🛒 Button click event fired for product:', productId);
                
                // Stop ALL event propagation immediately
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                e.cancelBubble = true;
                
                // Prevent default action
                if (e.defaultPrevented === false) {
                    e.preventDefault();
                }
                
                // Also stop the parent's onclick by temporarily disabling pointer events
                const productCard = newBtn.closest('.product-card');
                if (productCard) {
                    // Remove onclick from parent to prevent redirect
                    productCard.removeAttribute('onclick');
                    productCard.style.pointerEvents = 'none';
                    setTimeout(() => {
                        productCard.style.pointerEvents = '';
                    }, 1000);
                }
                
                console.log('✅ Event stopped, calling addToCart...');
                console.log('addToCart type:', typeof window.addToCart);
                
                // Call the function - it should be globally available
                if (typeof window.addToCart === 'function') {
                    try {
                        window.addToCart(productId, e);
                    } catch(err) {
                        console.error('❌ Error calling addToCart:', err);
                        alert('Error adding to cart: ' + err.message);
                    }
                } else {
                    console.error('❌ addToCart function not found!');
                    console.error('Available functions:', Object.keys(window).filter(k => k.includes('Cart')));
                    alert('Error: Add to cart function not available. Please refresh the page.');
                }
                
                // Return false to prevent any default behavior
                return false;
            }, true); // Use capture phase to catch event early
            
            // Also prevent on mouse down to catch it even earlier
            newBtn.addEventListener('mousedown', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }, true);
            
            console.log('✅ Button ' + (index + 1) + ' setup complete');
        } else {
            console.warn('⚠️ Button ' + (index + 1) + ' has no product ID!');
        }
    });
    console.log('✅ Setup complete for', addToCartButtons.length, 'buttons');
}

// Run setup after addToCart is defined (it's defined above, so this should work)
// But add a small delay to ensure DOM is ready
// Wrap in try-catch to prevent errors from breaking the page
try {
    (function() {
        function init() {
            try {
                if (typeof window.addToCart === 'function') {
                    console.log('✅ addToCart function is available, setting up buttons...');
                    setupAddToCartButtons();
                } else {
                    console.warn('⚠️ addToCart not yet available, retrying...');
                    setTimeout(init, 50);
                }
            } catch(err) {
                console.error('Error in init:', err);
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(init, 50);
            });
        } else {
            setTimeout(init, 50);
        }
        
        // Also run after a longer delay to catch dynamically added buttons
        setTimeout(function() {
            try {
                if (typeof window.addToCart === 'function') {
                    setupAddToCartButtons();
                }
            } catch(err) {
                console.error('Error in delayed setup:', err);
            }
        }, 1000);
    })();
} catch(err) {
    console.error('Critical error setting up add to cart buttons:', err);
    // Fallback: try to set up buttons directly after a delay
    setTimeout(function() {
        try {
            if (typeof window.addToCart === 'function') {
                setupAddToCartButtons();
            }
        } catch(e) {
            console.error('Fallback setup also failed:', e);
        }
    }, 2000);
}

// Auto-submit search on Enter key
const searchFilterInput = document.querySelector('.search-filter input');
if (searchFilterInput) {
    searchFilterInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
}
</script>

<?php include "includes/footer.php"; ?>
