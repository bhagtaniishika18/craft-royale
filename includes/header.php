<?php
// Fetch categories and subcategories from database
// Try to get connection from global scope first
if (!isset($conn)) {
    // Try different paths to db.php
    $possible_paths = [
        __DIR__ . "/db.php",
        dirname(__DIR__) . "/includes/db.php",
        "includes/db.php"
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            include $path;
            break;
        }
    }
}

// Only fetch if connection exists
$nav_categories = [];
$nav_subcategories_by_category = [];

if (isset($conn)) {
    // Check if current page is login.php to conditionally hide header parts
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_login_page = ($current_page == 'login.php');

    // Fetch all categories
    $nav_categories_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
    if ($nav_categories_query) {
        while ($cat = mysqli_fetch_assoc($nav_categories_query)) {
            $nav_categories[$cat['id']] = $cat;
        }
    }

    // Fetch all subcategories grouped by category
    $nav_subcategories_query = mysqli_query($conn, "SELECT s.*, c.category_name 
                                                    FROM subcategories s 
                                                    LEFT JOIN categories c ON s.category_id = c.id 
                                                    ORDER BY c.category_name, s.subcategory_name");
    if ($nav_subcategories_query) {
        while ($sub = mysqli_fetch_assoc($nav_subcategories_query)) {
            if (!isset($nav_subcategories_by_category[$sub['category_id']])) {
                $nav_subcategories_by_category[$sub['category_id']] = [];
            }
            $nav_subcategories_by_category[$sub['category_id']][] = $sub;
        }
    }
}

// Function to organize subcategories into columns (max 4 columns)
function organizeSubcategoriesIntoColumns($subcategories, $maxColumns = 4) {
    $total = count($subcategories);
    if ($total == 0) return [];
    
    $itemsPerColumn = ceil($total / $maxColumns);
    $columns = [];
    
    for ($i = 0; $i < $maxColumns; $i++) {
        $columns[$i] = array_slice($subcategories, $i * $itemsPerColumn, $itemsPerColumn);
        if (empty($columns[$i])) {
            unset($columns[$i]);
        }
    }
    
    return $columns;
}

// Calculate cart totals for sidebar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_subtotal = 0;
$cart_total_items = 0;
$cart_items_display = [];

if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (!is_array($item)) continue;
        
        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $item_total = $item_price * $item_quantity;
        
        $cart_subtotal += $item_total;
        $cart_total_items += $item_quantity;
        
        // Use the session key as the ID, but also store the item's id if it exists
        $display_id = is_numeric($product_id) ? (int)$product_id : $product_id;
        $item_id = isset($item['id']) ? (int)$item['id'] : $display_id;
        
        $cart_items_display[] = [
            'id' => $display_id,
            'item_id' => $item_id,
            'session_key' => $product_id, // Store the actual session key
            'name' => isset($item['name']) ? $item['name'] : 'Product',
            'price' => $item_price,
            'mrp' => isset($item['mrp']) ? floatval($item['mrp']) : 0,
            'image' => isset($item['image']) ? $item['image'] : '',
            'quantity' => $item_quantity,
            'total' => $item_total
        ];
    }
}

$free_shipping_threshold = 750;
$remaining_for_free_shipping = max(0, $free_shipping_threshold - $cart_subtotal);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Craft Royale</title>
    
    <!-- Favicon - Multiple sizes for better visibility -->
    <link rel="icon" type="image/png" sizes="192x192" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="128x128" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="96x96" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="64x64" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="48x48" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="cr_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="cr_logo.png">
    <link rel="shortcut icon" type="image/png" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="152x152" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="144x144" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="120x120" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="114x114" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="76x76" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="72x72" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="60x60" href="cr_logo.png">
    <link rel="apple-touch-icon" sizes="57x57" href="cr_logo.png">
    <link rel="apple-touch-icon" href="cr_logo.png">
    <!-- Removed invalid manifest to prevent syntax error -->

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- MAIN SITE CSS (keep your existing file) -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    
    <!-- Free Shipping Banner CSS - Animations for progress bar and truck -->
    <link rel="stylesheet" href="assets/css/free-shipping-banner.css">

    <!-- Free Shipping Manager Script - Load early for dynamic banner updates -->
    <script src="assets/js/free-shipping-manager.js?v=<?= time() ?>"></script>
    
    <!-- LIVE PROGRESS BAR - GUARANTEED TO WORK - Real-time updates on every click -->
    <script src="assets/js/cart-progress-live.js?v=<?= time() ?>"></script>

    <!-- INLINE HEADER CSS (SAFE – won't break anything) -->
    <style>
        /* Remove all whitespace before header */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* HEADER BASE */
        .main-header {
            background: #fff !important;
            border-bottom: 1px solid #eaeaea;
            margin: 0 !important;
            padding: 0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 999999 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* TOP BAR */
        .top-bar {
            background: #000;
            color: #fff;
            text-align: center;
            font-size: 13px;
            padding: 6px 0;
            position: relative;
            overflow: hidden;
        }

        .rotating-banner-text {
            position: relative;
            min-height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .banner-text {
            position: absolute;
            width: 100%;
            left: 0;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            white-space: nowrap;
            text-align: center;
        }

        .banner-text.active {
            opacity: 1;
            transform: translateY(0);
            position: relative;
        }

        /* HEADER ROW - Override external CSS - Compact like SS1 */
        .main-header {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .main-header .header-container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 10px 20px !important;
            display: grid !important;
            grid-template-columns: auto 1fr auto !important;
            align-items: center !important;
            gap: 15px !important;
            justify-content: unset !important;
            position: relative;
            z-index: 2000000;
        }
        
        .main-header .header-container > *:first-child {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        
        /* Remove whitespace before all modules */
        .main-header .header-container > * {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        /* LOGO - Override external CSS */
        .main-header .logo {
            display: flex !important;
            align-items: center !important;
            gap: 0 !important;
            position: relative !important;
            white-space: nowrap !important;
            margin: 0 !important;
            padding: 0 !important;
            min-width: fit-content !important;
        }

        .main-header .logo a {
            text-decoration: none !important;
            color: inherit !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0 !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .main-header .logo a:hover {
            transform: scale(1.02) !important;
        }

        .main-header .logo img {
            height: 60px !important;
            width: auto !important;
            max-width: 400px !important;
            min-width: 150px !important;
            object-fit: contain !important;
            transition: all 0.3s ease !important;
            flex-shrink: 0 !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            margin-right: -20px !important;
        }

        .main-header .logo h1 {
            font-size: 32px !important;
            font-weight: 700 !important;
            margin: 0 !important;
            padding: 0 !important;
            margin-left: -20px !important;
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0 !important;
            font-family: 'Segoe UI', sans-serif !important;
            white-space: nowrap !important;
            line-height: 1.2 !important;
            transform: none !important;
            animation: none !important;
        }

        .logo span {
            color: #2fc7b4;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            margin-left: 4px;
        }

        .logo span::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 8px;
            padding: 2px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .logo a:hover span {
            color: #2fa76b;
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.15), rgba(47, 167, 107, 0.15));
        }

        .logo a:hover span::before {
            opacity: 1;
        }

        /* SEARCH */
        .search-box {
            position: relative;
            display: flex;
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            max-width: 950px;
            width: 100%;
            margin: 0 auto;
        }

        .search-box:focus-within {
            border-color: #2fc7b4;
            box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
            transform: scale(1.02);
        }

        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: none;
            outline: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            background: #f9f9f9;
        }

        .search-box button {
            width: 52px;
            border: none;
            background: #2fc7b4;
            color: #fff;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .search-box button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .search-box button:hover::before {
            width: 300px;
            height: 300px;
        }

        .search-box button:hover {
            background: #2fa76b;
            transform: scale(1.1);
        }

        /* SEARCH RESULTS DROPDOWN */
        .search-results {
            position: absolute !important;
            top: calc(100% + 5px) !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            background: #fff !important;
            border: 1px solid #e0e0e0 !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12) !important;
            border-radius: 8px !important;
            padding: 4px 0 !important;
            display: none;
            visibility: hidden;
            opacity: 0;
            z-index: 99999 !important;
            max-height: 450px !important;
            overflow-y: auto !important;
            margin-top: 5px !important;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            padding: 4px 0;
        }

        .search-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.15s ease;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
        }

        .search-item:last-of-type {
            border-bottom: none;
        }

        .search-item:hover {
            background-color: #f8f9fa;
        }

        .search-item-image {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .search-item-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .search-item-name {
            font-size: 14px;
            font-weight: 500;
            color: #2b2b2b;
            line-height: 1.4;
            margin: 0;
        }

        .search-item-name strong {
            color: #2fc7b4;
            font-weight: 600;
        }

        .search-item-sku {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .search-item-price {
            font-size: 15px;
            font-weight: 600;
            color: #2fa76b;
            margin-top: 2px;
        }

        .search-more {
            display: block;
            padding: 14px 16px;
            text-align: center;
            background: #f8f9fa;
            color: #2fc7b4;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
            transition: all 0.2s ease;
            border-radius: 0 0 12px 12px;
        }

        .search-more:hover {
            background: #e9ecef;
            color: #2fc7b4;
        }

        /* ICONS */
        .header-icons {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            position: relative;
            margin-left: auto;
            width: auto;
            min-width: fit-content;
            justify-content: flex-end;
            z-index: 2000000;
            padding-right: 10px;
        }

        .header-icons a {
            color: #000;
            text-decoration: none;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
        }

        .header-icons a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 100%;
            height: 2px;
            background: #2fc7b4;
            transition: transform 0.3s ease;
        }

        .header-icons a:hover::after {
            transform: translateX(-50%) scaleX(1);
        }

        .header-icons a:hover {
            color: #2fc7b4;
            transform: translateY(-3px);
        }

        .header-icons a i {
            transition: all 0.3s ease;
            font-size: 18px;
            line-height: 1;
        }

        .header-icons a:hover i {
            transform: scale(1.2);
        }
        
        /* Ensure all icons are properly aligned */
        .header-icons a:not(.user-logged-in) {
            border-radius: 50%;
        }
        
        .header-icons a:not(.user-logged-in):hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .header-icons span {
            position: absolute;
            top: -6px;
            right: -10px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: #fff;
            font-size: 11px;
            padding: 2px 6px;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: badgePulse 2s ease-in-out infinite;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.4),
                        0 1px 3px rgba(0, 0, 0, 0.2);
            border: 2px solid #fff;
            z-index: 10;
        }

        @keyframes badgePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 3px 10px rgba(255, 107, 107, 0.4),
                            0 1px 3px rgba(0, 0, 0, 0.2);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 5px 15px rgba(255, 107, 107, 0.6),
                            0 2px 5px rgba(0, 0, 0, 0.3);
            }
        }
        
        /* Show badge only when count is greater than 0 */
        .header-icons span[data-count]:not([data-count="0"]) {
            display: flex !important;
        }

        .header-icons a:hover span {
            background: linear-gradient(135deg, #ff5252 0%, #e53935 100%);
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6),
                        0 2px 6px rgba(0, 0, 0, 0.3);
            animation: badgeBounce 0.6s ease-in-out;
        }

        @keyframes badgeBounce {
            0%, 100% {
                transform: scale(1.15) rotate(5deg);
            }
            50% {
                transform: scale(1.25) rotate(-5deg);
            }
        }

        /* Special styling for cart icon */
        #cartIcon {
            position: relative;
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1) 0%, rgba(47, 199, 180, 0.05) 100%);
            border-radius: 50%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            animation: cartIconEntrance 0.6s ease-out;
        }

        @keyframes cartIconEntrance {
            0% {
                opacity: 0;
                transform: scale(0.8) rotate(-10deg);
            }
            50% {
                transform: scale(1.1) rotate(5deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        #cartIcon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(47, 199, 180, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
            z-index: 0;
        }

        #cartIcon:hover::before {
            width: 50px;
            height: 50px;
        }

        #cartIcon i {
            position: relative;
            z-index: 1;
            color: #2fc7b4;
            filter: drop-shadow(0 2px 4px rgba(47, 199, 180, 0.3));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        #cartIcon:hover {
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.2) 0%, rgba(47, 199, 180, 0.15) 100%);
            border-color: rgba(47, 199, 180, 0.4);
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 6px 20px rgba(47, 199, 180, 0.3),
                        0 2px 8px rgba(47, 199, 180, 0.2);
        }

        #cartIcon:hover i {
            color: #26a693;
            transform: scale(1.15) rotate(-5deg);
            filter: drop-shadow(0 4px 8px rgba(47, 199, 180, 0.5));
        }

        /* LOGGED IN USER ICON - MATCHING REFERENCE DESIGN */
        .header-icons a.user-logged-in {
            position: relative;
            background: rgba(47, 167, 107, 0.15);
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 2px solid rgba(47, 167, 107, 0.25);
        }

        .header-icons a.user-logged-in i {
            color: #2fa76b !important;
            font-size: 20px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            font-weight: 600;
        }

        /* Green badge with checkmark in top-right */
        .header-icons a.user-logged-in::before {
            content: '✓';
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: #2fa76b;
            border: 2.5px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(47, 167, 107, 0.4);
            z-index: 2;
            line-height: 1;
        }

        .header-icons a.user-logged-in:hover {
            background: rgba(47, 167, 107, 0.25);
            border-color: rgba(47, 167, 107, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(47, 167, 107, 0.2);
        }

        .header-icons a.user-logged-in:hover i {
            color: #2fa76b;
            transform: scale(1.1);
        }

        .header-icons a.user-logged-in:hover::before {
            background: #2fc7b4;
            box-shadow: 0 3px 10px rgba(47, 199, 180, 0.5);
        }

        /* USER DROPDOWN WRAPPER */
        .user-dropdown-wrapper {
            position: relative;
        }

        /* USER DROPDOWN MENU - Hidden by default */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
            padding-left: 3px !important;
            padding-right: 3px !important;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: none !important;
            flex-direction: column;
            align-items: stretch;
            margin: 0 !important;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Only show dropdown when user is logged in (has user-logged-in-dropdown class) */
        .user-dropdown.user-logged-in-dropdown {
            display: flex !important;
        }

        /* Show dropdown on hover or when clicked (only if logged in) */
        .user-dropdown-wrapper:hover .user-dropdown.user-logged-in-dropdown,
        .user-dropdown.user-logged-in-dropdown.show {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0);
            display: flex !important;
        }

        .user-dropdown .dropdown-item {
            display: flex !important;
            align-items: center !important;
            gap: 0 !important;
            padding-top: 12px !important;
            padding-bottom: 12px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none !important;
            background: none;
            width: 100% !important;
            text-align: left;
            line-height: 1.5;
            margin: 0 !important;
            box-sizing: border-box;
        }

        .user-dropdown .dropdown-item i {
            width: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            color: #2fa76b;
            font-size: 16px;
            text-align: center;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px !important;
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .user-dropdown .dropdown-item:hover {
            background: rgba(47, 167, 107, 0.1);
            color: #2fa76b;
        }

        .user-dropdown .dropdown-item:hover i {
            color: #2fa76b;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }

        /* CATEGORY BAR */
        .category-bar {
            background: #2fc7b4;
        }

        .category-menu {
            max-width: 1300px;
            margin: auto;
            display: flex;
            gap: 24px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            align-items: center;
            flex-wrap: nowrap;
        }

        .category-menu a {
            color: #fff;
            text-decoration: none;
            position: relative;
            white-space: nowrap;
            display: inline-block;
            padding: 5px 0;
        }

        .category-menu-item {
            position: relative;
            white-space: nowrap;
        }

        .category-menu-item > a {
            display: inline-block;
            position: relative;
        }

        .category-menu-item > a::after {
            content: '';
            display: none;
        }

        .category-menu-item > a .badge {
            position: absolute;
            top: -8px;
            right: -15px;
        }

        .badge {
            background: #0bbcd6;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            white-space: nowrap;
            line-height: 1.2;
            margin-left: 5px;
        }

        .category-menu-item > a .badge {
            position: absolute;
            top: -8px;
            right: -15px;
        }

        /* DROPDOWN MENU */
        .category-menu-item {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            width: max-content;
            max-width: 1250px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border-radius: 8px;
            padding: 25px 30px;
            display: none;
            z-index: 1000;
            margin-top: 8px;
            animation: slideDown 0.3s ease-out;
            border: 1px solid rgba(47, 167, 107, 0.1);
            box-sizing: border-box;
            border-top: 3px solid #2fc7b4;
        }

        .category-menu-item:nth-child(2) .dropdown-menu { border-top-color: #9c27b0; }
        .category-menu-item:nth-child(3) .dropdown-menu { border-top-color: #ff6b9d; }
        .category-menu-item:nth-child(4) .dropdown-menu { border-top-color: #2fa76b; }
        .category-menu-item:nth-child(5) .dropdown-menu { border-top-color: #ffc107; }
        .category-menu-item:nth-child(6) .dropdown-menu { border-top-color: #ff9800; }
        .category-menu-item:nth-child(7) .dropdown-menu { border-top-color: #e91e63; }

        /* Column configurations for each dropdown - removed duplicate, using the one below */

        .category-menu-item:hover .dropdown-menu {
            display: block;
        }

        .category-menu-item:hover > a {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            padding: 5px 10px;
            margin: -5px -10px;
        }

        .dropdown-content {
            display: grid;
            gap: 35px;
            box-sizing: border-box;
        }

        .dropdown-column {
            min-width: 150px;
            max-width: 280px;
        }


        .dropdown-divider {
            grid-column: 1 / -1;
            height: 1px;
            background: #e0e0e0;
            margin: 12px 0;
            width: 100%;
        }

        .dropdown-column {
            width: 100%;
            box-sizing: border-box;
            min-width: 0;
        }

        .dropdown-column h3 {
            font-size: 15px;
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2fa76b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            box-sizing: border-box;
            line-height: 1.3;
        }

        .dropdown-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .dropdown-column ul li {
            margin-bottom: 6px;
        }

        .dropdown-column ul li a {
            color: #555;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
            display: block;
            padding: 4px 0;
            position: relative;
            padding-left: 0;
            line-height: 1.4;
        }

        .dropdown-column ul li a::before {
            content: '→';
            position: absolute;
            left: -15px;
            opacity: 0;
            transition: all 0.3s ease;
            color: #2fa76b;
        }

        .dropdown-column ul li a:hover {
            color: #2fa76b;
            padding-left: 15px;
            transform: translateX(5px);
        }

        .dropdown-column ul li a:hover::before {
            opacity: 1;
            left: 0;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* LOGIN BUTTON */
        .login-btn {
            background: #2fc7b4;
            color: #fff;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s ease;
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
            background: rgba(255,255,255,0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            background: #2fa76b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
        }
    </style>
<script>
// ============================================
// CRITICAL: Define cart functions IMMEDIATELY - before anything else
// These MUST be available globally before any onclick handlers execute

// PRODUCT PAGE FUNCTIONS - Define FIRST for global access
// These must be available before onclick handlers in product.php
// Quantity functions
if (typeof window.increaseQuantity === 'undefined') {
    window.increaseQuantity = function() {
        console.log('increaseQuantity called');
        const input = document.getElementById('quantity');
        if (!input) {
            console.error('Quantity input not found');
            return;
        }
        const max = parseInt(input.getAttribute('max')) || 999;
        const current = parseInt(input.value) || 1;
        if (current < max) {
            input.value = current + 1;
        }
    };
}

if (typeof window.decreaseQuantity === 'undefined') {
    window.decreaseQuantity = function() {
        console.log('decreaseQuantity called');
        const input = document.getElementById('quantity');
        if (!input) {
            console.error('Quantity input not found');
            return;
        }
        const current = parseInt(input.value);
        if (current > 1) {
            input.value = current - 1;
        }
    };
}

// ============================================
// CART FUNCTIONS - Define after product functions
// ============================================
// Define cart functions IMMEDIATELY in head - must be available before any onclick handlers
// Make openCartSidebar globally accessible immediately (early definition, will be overridden later)
window.openCartSidebar = window.openCartSidebar || function() {
    console.log('openCartSidebar called (early version)');
    function tryOpen() {
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartSidebarOverlay');
        
        if (sidebar && overlay) {
            console.log('Opening cart sidebar...');
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Load cart content
            if (typeof window.loadCartSidebar === 'function') {
                window.loadCartSidebar();
            } else {
                // Fallback: load directly
                const cartContent = document.getElementById('cartSidebarContent');
                if (cartContent) {
                    cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
                    fetch('get-cart-sidebar.php?t=' + Date.now())
                        .then(r => r.text())
                        .then(html => {
                            if (cartContent) {
                                cartContent.innerHTML = html;
                                // Execute any scripts in the inserted HTML
                                const scripts = cartContent.querySelectorAll('script');
                                scripts.forEach(script => {
                                    try {
                                        const newScript = document.createElement('script');
                                        newScript.textContent = script.textContent;
                                        document.head.appendChild(newScript);
                                        setTimeout(() => {
                                            if (newScript.parentNode) {
                                                newScript.parentNode.removeChild(newScript);
                                            }
                                        }, 100);
                                    } catch(e) {
                                        console.error('Error executing script:', e);
                                    }
                                });
                            }
                        })
                        .catch(() => {
                            if (cartContent) cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
                        });
                }
            }
            return true;
        }
        console.error('Cart sidebar elements not found!');
        return false;
    }
    
    // Try immediately
    if (tryOpen()) return;
    
    // Wait for DOM if loading
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!tryOpen()) {
                // Retry multiple times with increasing delays
                setTimeout(function() { if (!tryOpen()) {
                    setTimeout(function() { if (!tryOpen()) {
                        setTimeout(function() { tryOpen(); }, 300);
                    } }, 200);
                } }, 100);
            }
        });
        return;
    }
    
    // Retry with delays
    setTimeout(function() { if (!tryOpen()) {
        setTimeout(function() { if (!tryOpen()) {
            setTimeout(function() { tryOpen(); }, 300);
        } }, 200);
    } }, 100);
};

// Make closeCartSidebar globally accessible immediately - ROBUST VERSION
window.closeCartSidebar = window.closeCartSidebar || function() {
    try {
        console.log('🔒 closeCartSidebar called');
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartSidebarOverlay');
        
        if (sidebar) {
            sidebar.classList.remove('active');
            console.log('✅ Sidebar class removed');
        } else {
            console.warn('⚠️ Sidebar element not found');
        }
        
        if (overlay) {
            overlay.classList.remove('active');
            console.log('✅ Overlay class removed');
        } else {
            console.warn('⚠️ Overlay element not found');
        }
        
        document.body.style.overflow = '';
        console.log('✅ Body overflow reset');
        
        return false;
    } catch(err) {
        console.error('❌ Error in closeCartSidebar:', err);
        // Force close even on error
        try {
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartSidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        } catch(e) {
            console.error('❌ Critical error closing sidebar:', e);
        }
        return false;
    }
};

window.updateCartQuantity = function(productId, action, value = null, sessionKey = null) {
    if (!productId) return false;
    
    const input = document.getElementById('qty_' + productId);
    if (!input) return false;
    
    let quantity = parseInt(input.value) || 1;
    if (action === 'increase') quantity++;
    else if (action === 'decrease') quantity = Math.max(1, quantity - 1);
    else if (action === 'update') quantity = Math.max(1, parseInt(value) || 1);
    
    // Update UI immediately (Optimistic)
    input.value = quantity;
    
    // Trigger instant total recalculation if available
    if (typeof window.updateSidebarTotalsInstantly === 'function') {
        window.updateSidebarTotalsInstantly();
    } else if (typeof window.updateSidebarSubtotalInstantly === 'function') {
        window.updateSidebarSubtotalInstantly();
    }
    
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('quantity', quantity);
    fd.append('action', 'update');
    if (sessionKey) fd.append('session_key', sessionKey);
    
    fetch('update-cart.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update sidebar content if provided
            const content = document.getElementById('cartSidebarContent');
            if (content && data.cart_html) {
                // Before replacing content, save scroll position
                const scrollPos = content.scrollTop;
                content.innerHTML = data.cart_html;
                content.scrollTop = scrollPos;
                
                // Re-setup events for newly loaded content (delegation handles mostly, but just in case)
                if (typeof window.setupCartSidebarEvents === 'function') window.setupCartSidebarEvents(true);
            }
            if (typeof updateCartCount === 'function') updateCartCount();
            
            // Re-run totals update to ensure everything is in sync with server
            if (typeof window.updateSidebarTotalsInstantly === 'function') {
                window.updateSidebarTotalsInstantly();
            }
        }
    }).catch(e => console.error('Error updating cart:', e));
};

window.removeCartProduct = function(productId, sessionKey) {
    if (!productId) return;
    window.pendingDeleteProductId = productId;
    window.pendingDeleteSessionKey = sessionKey || null;
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    } else {
        // Direct delete if modal not found
        window.confirmDeleteItem();
    }
};

window.closeDeleteModal = window.closeDeleteModal || function() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Re-enable cart sidebar overlay after modal closes
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = '';
        }
    }
    window.pendingDeleteProductId = null;
    window.pendingDeleteSessionKey = null;
};

// Order Note Modal Functions
window.showOrderNoteModal = window.showOrderNoteModal || function() {
    const modal = document.getElementById('orderNoteModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        // Prevent cart sidebar overlay from closing sidebar when modal is open
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = 'none';
        }
        const textarea = document.getElementById('orderNoteText');
        if (textarea) {
            const savedNote = sessionStorage.getItem('cartOrderNote');
            if (savedNote) {
                textarea.value = savedNote;
            }
            setTimeout(() => textarea.focus(), 100);
        }
    }
};

window.closeOrderNoteModal = window.closeOrderNoteModal || function() {
    const modal = document.getElementById('orderNoteModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Re-enable cart sidebar overlay after modal closes
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = '';
        }
    }
};

window.saveOrderNote = window.saveOrderNote || function() {
    const textarea = document.getElementById('orderNoteText');
    if (textarea) {
        const note = textarea.value.trim();
        if (note) {
            sessionStorage.setItem('cartOrderNote', note);
            alert('Order note saved successfully! ✓');
        } else {
            sessionStorage.removeItem('cartOrderNote');
        }
        window.closeOrderNoteModal();
    }
};

// Estimate Modal Functions
window.showEstimateModal = window.showEstimateModal || function() {
    const modal = document.getElementById('estimateModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        // Prevent cart sidebar overlay from closing sidebar when modal is open
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = 'none';
        }
        const form = document.getElementById('estimateShippingForm');
        if (form) {
            form.reset();
            const stateSelect = document.getElementById('estimateStateSelect');
            const zipCode = document.getElementById('estimateZipCode');
            if (stateSelect) stateSelect.value = '';
            if (zipCode) zipCode.value = '';
        }
        const results = document.getElementById('estimateResults');
        if (results) {
            results.style.display = 'none';
        }
    }
};

window.closeEstimateModal = window.closeEstimateModal || function() {
    const modal = document.getElementById('estimateModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Re-enable cart sidebar overlay after modal closes
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = '';
        }
    }
};

window.estimateShippingSidebar = window.estimateShippingSidebar || function() {
    const state = document.getElementById('estimateStateSelect');
    const zipCode = document.getElementById('estimateZipCode');
    const resultsDiv = document.getElementById('estimateResults');
    const ratesList = document.getElementById('estimateRatesList');
    
    if (!state || !zipCode || !resultsDiv || !ratesList) {
        alert('Error: Form elements not found');
        return;
    }
    
    const stateValue = state.value;
    const zipValue = zipCode.value.trim();
    
    if (!stateValue || !zipValue) {
        alert('Please select province and enter zip code');
        return;
    }
    
    if (zipValue.length !== 6 || !/^\d{6}$/.test(zipValue)) {
        alert('Please enter a valid 6-digit zip code');
        return;
    }
    
    // Get cart subtotal from the sidebar
    let cartSubtotal = 0;
    const subtotalElement = document.querySelector('.cart-subtotal span:last-child');
    if (subtotalElement) {
        const subtotalText = subtotalElement.textContent.trim();
        const match = subtotalText.match(/Rs\.\s*([\d,]+\.?\d*)/);
        if (match) {
            cartSubtotal = parseFloat(match[1].replace(/,/g, ''));
        }
    }
    
    // Check if free shipping applies (>= 750)
    const freeShippingThreshold = 750;
    const hasFreeShipping = cartSubtotal >= freeShippingThreshold;
    
    // Set shipping rates based on free shipping eligibility
    const prepaidRate = hasFreeShipping ? 0 : 100;
    const codRate = hasFreeShipping ? 100 : 200;
    const startingRate = hasFreeShipping ? 0 : 100;
    
    const deliveryDays = calculateDeliveryDaysSidebar(stateValue);
    
    ratesList.innerHTML = `
        <p style="margin: 0 0 15px 0; color: #666; font-size: 13px;">
            We found shipping rates ${zipValue}, ${stateValue}, India, starting at Rs. ${startingRate.toFixed(2)}
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="padding: 12px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #2b2b2b; font-size: 14px;">Prepaid at Rs. ${prepaidRate.toFixed(2)}</strong>
                        ${hasFreeShipping ? '<span style="color: #28a745; font-size: 11px; margin-left: 8px;">(FREE SHIPPING)</span>' : ''}
                        <div style="font-size: 12px; color: #666; margin-top: 4px;">
                            Estimated delivery: ${deliveryDays.min}-${deliveryDays.max} days
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 12px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #2b2b2b; font-size: 14px;">Cash on Delivery at Rs. ${codRate.toFixed(2)}</strong>
                        <div style="font-size: 12px; color: #666; margin-top: 4px;">
                            Estimated delivery: ${deliveryDays.min}-${deliveryDays.max} days
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
};

function calculateDeliveryDaysSidebar(state) {
    const gujaratStates = ['Gujarat'];
    const nearbyStates = ['Maharashtra', 'Rajasthan', 'Madhya Pradesh', 'Goa', 'Dadra and Nagar Haveli and Daman and Diu'];
    const mediumStates = ['Delhi', 'Haryana', 'Punjab', 'Uttar Pradesh', 'Himachal Pradesh', 'Uttarakhand', 'Chhattisgarh', 'Odisha', 'Jharkhand', 'Bihar', 'West Bengal', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Tamil Nadu', 'Kerala'];
    const farStates = ['Assam', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Tripura', 'Arunachal Pradesh', 'Sikkim', 'Jammu and Kashmir', 'Ladakh'];
    const islandStates = ['Andaman and Nicobar Islands', 'Lakshadweep'];
    const unionTerritories = ['Chandigarh', 'Puducherry'];
    
    if (gujaratStates.includes(state)) {
        return { min: 2, max: 3 };
    } else if (nearbyStates.includes(state)) {
        return { min: 3, max: 5 };
    } else if (mediumStates.includes(state)) {
        return { min: 4, max: 7 };
    } else if (farStates.includes(state)) {
        return { min: 6, max: 10 };
    } else if (islandStates.includes(state)) {
        return { min: 8, max: 12 };
    } else if (unionTerritories.includes(state)) {
        return { min: 3, max: 6 };
    } else {
        return { min: 5, max: 8 };
    }
}

// Coupon Modal Functions
window.showCouponModal = window.showCouponModal || function() {
    const modal = document.getElementById('couponModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        const input = document.getElementById('couponCodeInput');
        if (input) {
            const savedCoupon = sessionStorage.getItem('cartCouponCode');
            if (savedCoupon) {
                input.value = savedCoupon;
            }
            setTimeout(() => input.focus(), 100);
        }
    }
};

window.closeCouponModal = window.closeCouponModal || function() {
    const modal = document.getElementById('couponModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Re-enable cart sidebar overlay after modal closes
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = '';
        }
    }
};

window.saveCouponCode = window.saveCouponCode || function() {
    const input = document.getElementById('couponCodeInput');
    if (input) {
        const couponCode = input.value.trim();
        if (couponCode) {
            sessionStorage.setItem('cartCouponCode', couponCode);
            alert('Coupon code saved! It will be applied on checkout page. ✓');
        } else {
            sessionStorage.removeItem('cartCouponCode');
        }
        window.closeCouponModal();
    }
};

// Gift Card Modal Functions
window.showGiftCardModal = window.showGiftCardModal || function() {
    const modal = document.getElementById('giftCardModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        const cardInput = document.getElementById('giftCardNumberInput');
        if (cardInput) {
            // Clear previous values
            cardInput.value = '';
            document.getElementById('giftCardPinInput').value = '';
            document.getElementById('giftCardMessage').style.display = 'none';
            document.getElementById('giftCardInfo').style.display = 'none';
            setTimeout(() => cardInput.focus(), 100);
        }
    }
};

window.closeGiftCardModal = window.closeGiftCardModal || function() {
    const modal = document.getElementById('giftCardModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        const overlay = document.getElementById('cartSidebarOverlay');
        if (overlay) {
            overlay.style.pointerEvents = '';
        }
    }
};

window.validateGiftCard = window.validateGiftCard || function() {
    const cardNumber = document.getElementById('giftCardNumberInput').value.trim().toUpperCase().replace(/\s/g, '').replace(/-/g, '');
    const pin = document.getElementById('giftCardPinInput').value.trim();
    const messageDiv = document.getElementById('giftCardMessage');
    const infoDiv = document.getElementById('giftCardInfo');
    
    if (!cardNumber || cardNumber.length < 12) {
        return;
    }
    
    if (!pin || pin.length < 4) {
        return;
    }
    
    // Format card number (add dashes)
    const formattedCard = cardNumber.match(/.{1,4}/g).join('-');
    document.getElementById('giftCardNumberInput').value = formattedCard;
    
    // Show loading
    messageDiv.style.display = 'block';
    messageDiv.style.background = '#fff3cd';
    messageDiv.style.color = '#856404';
    messageDiv.textContent = '⏳ Validating gift card...';
    
    fetch('validate-gift-card.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'card_number=' + encodeURIComponent(cardNumber) + '&pin=' + encodeURIComponent(pin)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.style.background = '#d4edda';
            messageDiv.style.color = '#155724';
            messageDiv.textContent = '✓ Valid gift card!';
            infoDiv.style.display = 'block';
            document.getElementById('giftCardBalance').textContent = '₹' + parseFloat(data.balance).toFixed(2);
            document.getElementById('giftCardValidTill').textContent = data.valid_till;
        } else {
            messageDiv.style.background = '#f8d7da';
            messageDiv.style.color = '#721c24';
            messageDiv.textContent = '✗ ' + data.message;
            infoDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.style.background = '#f8d7da';
        messageDiv.style.color = '#721c24';
        messageDiv.textContent = '✗ Error validating gift card. Please try again.';
        infoDiv.style.display = 'none';
    });
};

window.applyGiftCard = window.applyGiftCard || function() {
    const cardNumber = document.getElementById('giftCardNumberInput').value.trim().toUpperCase().replace(/\s/g, '').replace(/-/g, '');
    const pin = document.getElementById('giftCardPinInput').value.trim();
    const btn = document.getElementById('applyGiftCardBtn');
    
    if (!cardNumber || cardNumber.length < 12) {
        alert('Please enter a valid gift card number');
        return;
    }
    
    if (!pin || pin.length < 4) {
        alert('Please enter a valid PIN (4-10 digits)');
        return;
    }
    
    // Disable button
    if (btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Applying...';
    }
    
    fetch('apply-gift-card.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'card_number=' + encodeURIComponent(cardNumber) + '&pin=' + encodeURIComponent(pin)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('🎉 Gift card applied successfully! Discount: ₹' + parseFloat(data.discount).toFixed(2));
            window.closeGiftCardModal();
            // Reload cart sidebar
            if (typeof window.loadCartSidebar === 'function') {
                window.loadCartSidebar();
            } else if (typeof window.updateCartSidebar === 'function') {
                window.updateCartSidebar();
            } else {
                // Try to reload cart via fetch
                fetch('get-cart-sidebar.php')
                    .then(r => r.text())
                    .then(html => {
                        const sidebar = document.getElementById('cartSidebarContent');
                        if (sidebar) sidebar.innerHTML = html;
                    })
                    .catch(() => location.reload());
            }
        } else {
            alert('Error: ' + data.message);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '🎁 Apply Gift Card';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error applying gift card. Please try again.');
        if (btn) {
            btn.disabled = false;
            btn.textContent = '🎁 Apply Gift Card';
        }
    });
};

window.removeGiftCard = window.removeGiftCard || function() {
    if (confirm('Remove applied gift card?')) {
        fetch('remove-gift-card.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gift card removed successfully');
                if (typeof window.loadCartSidebar === 'function') {
                    window.loadCartSidebar();
                } else {
                    location.reload();
                }
            }
        })
        .catch(() => location.reload());
    }
};

// Close modals when clicking outside and ESC key
document.addEventListener('DOMContentLoaded', function() {
    const modals = ['orderNoteModal', 'estimateModal', 'couponModal', 'giftCardModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                // Only close if clicking directly on the modal backdrop, not on child elements
                if (e.target === this) {
                    if (modalId === 'orderNoteModal' && typeof window.closeOrderNoteModal === 'function') {
                        window.closeOrderNoteModal();
                    } else if (modalId === 'estimateModal' && typeof window.closeEstimateModal === 'function') {
                        window.closeEstimateModal();
                    } else if (modalId === 'couponModal' && typeof window.closeCouponModal === 'function') {
                        window.closeCouponModal();
                    } else if (modalId === 'giftCardModal' && typeof window.closeGiftCardModal === 'function') {
                        window.closeGiftCardModal();
                    }
                }
            });
        }
    });
    
    // Handle ESC key for all cart feature modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const orderNoteModal = document.getElementById('orderNoteModal');
            const estimateModal = document.getElementById('estimateModal');
            const couponModal = document.getElementById('couponModal');
            const giftCardModal = document.getElementById('giftCardModal');
            
            if (orderNoteModal && orderNoteModal.classList.contains('show') && typeof window.closeOrderNoteModal === 'function') {
                window.closeOrderNoteModal();
            } else if (estimateModal && estimateModal.classList.contains('show') && typeof window.closeEstimateModal === 'function') {
                window.closeEstimateModal();
            } else if (couponModal && couponModal.classList.contains('show') && typeof window.closeCouponModal === 'function') {
                window.closeCouponModal();
            } else if (giftCardModal && giftCardModal.classList.contains('show') && typeof window.closeGiftCardModal === 'function') {
                window.closeGiftCardModal();
            }
        }
    });
    
    // Auto-format gift card number input
    const cardInput = document.getElementById('giftCardNumberInput');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '').replace(/-/g, '').toUpperCase();
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join('-');
            }
            this.value = value;
        });
    }
    
    // Auto-validate when PIN is entered
    const pinInput = document.getElementById('giftCardPinInput');
    if (pinInput) {
        pinInput.addEventListener('blur', function() {
            const cardNumber = document.getElementById('giftCardNumberInput').value.replace(/-/g, '');
            if (this.value.length >= 4 && cardNumber.length >= 12 && typeof window.validateGiftCard === 'function') {
                window.validateGiftCard();
            }
        });
    }
});

window.confirmDeleteItem = window.confirmDeleteItem || function() {
    const productId = window.pendingDeleteProductId;
    const sessionKey = window.pendingDeleteSessionKey;
    if (!productId) {
        window.closeDeleteModal();
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'remove');
    if (sessionKey && sessionKey !== null && sessionKey !== 'null' && sessionKey !== 'undefined' && sessionKey !== '') {
        formData.append('session_key', sessionKey);
    }
    
    // Show loading state
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '⏳ Removing...';
    }
    
    fetch('update-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Expected JSON but got:', text.substring(0, 100));
                throw new Error('Invalid response format');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Update cart count first
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            // Close modal
            window.closeDeleteModal();
            
            // Update cart sidebar immediately with HTML from response
            const cartContent = document.getElementById('cartSidebarContent');
            if (cartContent && data.cart_html) {
                console.log('✅ Updating cart sidebar with new HTML (may show empty cart)');
                cartContent.innerHTML = data.cart_html;
                
                // Execute scripts in the new HTML (if any)
                const scripts = cartContent.querySelectorAll('script');
                scripts.forEach(script => {
                    try {
                        const newScript = document.createElement('script');
                        newScript.textContent = script.textContent;
                        document.head.appendChild(newScript);
                        setTimeout(() => {
                            if (newScript.parentNode) {
                                newScript.parentNode.removeChild(newScript);
                            }
                        }, 100);
                        if (script.textContent) {
                            eval(script.textContent);
                        }
                    } catch (e) {
                        console.error('Error executing script:', e);
                    }
                });
                
                // Reattach event listeners via delegation (checked for uniqueness inside function)
                setTimeout(() => {
                    if (typeof window.setupCartSidebarEvents === 'function') {
                        window.setupCartSidebarEvents();
                    }
                }, 100);
            } else if (typeof window.loadCartSidebar === 'function') {
                // Fallback to loading if HTML not provided
                console.log('⚠️ No cart_html in response, loading from server...');
                window.loadCartSidebar();
            } else {
                // Last resort: reload page
                console.log('⚠️ Reloading page to show updated cart...');
                location.reload();
            }
        } else {
            window.closeDeleteModal();
            // Silently reload to show current state
            if (typeof window.loadCartSidebar === 'function') {
                window.loadCartSidebar();
            }
        }
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '🗑️ Yes, Remove';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.closeDeleteModal();
        // Silently reload cart sidebar
        if (typeof window.loadCartSidebar === 'function') {
            window.loadCartSidebar();
        }
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '🗑️ Yes, Remove';
        }
    });
    
    window.pendingDeleteProductId = null;
    window.pendingDeleteSessionKey = null;
};

// Close modal on overlay click and ESC key
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteConfirmModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            // Only close if clicking directly on the modal backdrop, not on child elements
            if (e.target === this) {
                window.closeDeleteModal();
            }
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal && modal.classList.contains('show')) {
                window.closeDeleteModal();
            }
        }
    });
});
</script>
</head>

<body style="margin: 0; padding: 0;">

<!-- PRELOADER -->
<div id="cr-preloader">
    <div class="preloader-content">
        <video autoplay loop muted playsinline>
            <source src="animations/p.webm" type="video/webm">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<style>
/* PRELOADER STYLES */
#cr-preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
    backdrop-filter: blur(10px); /* Glassmorphism blur */
    z-index: 999999999; /* Max priority */
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.8s cubic-bezier(0.77, 0, 0.175, 1), visibility 0.8s;
}

.preloader-content {
    width: 500px; /* Reduced from full screen */
    max-width: 90vw;
    height: 500px;
    max-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(1);
    animation: preloaderPulse 2.5s ease-in-out infinite;
}

#cr-preloader video {
    width: 100%;
    height: auto;
    object-fit: contain; /* Fitting within the container instead of covering */
    mix-blend-mode: multiply; /* Removes white background from the video */
    filter: drop-shadow(0 10px 30px rgba(47, 167, 107, 0.1));
}

@keyframes preloaderPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.9; }
}

/* Hide preloader state */
body.loaded #cr-preloader {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
</style>

<script>
window.addEventListener('load', function() {
    // Elegant delay to ensure the animation is visible but not annoying
    setTimeout(function() {
        document.body.classList.add('loaded');
        // Clean up DOM after transition
        setTimeout(function() {
            const preloader = document.getElementById('cr-preloader');
            if (preloader) preloader.style.display = 'none';
        }, 1000);
    }, 1200); 
});
</script>


<?php if (!$is_login_page): ?>
<header class="main-header" style="margin: 0; padding: 0; position: sticky; top: 0; z-index: 9999999; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <!-- TOP BAR -->
    <div class="top-bar" style="margin: 0; padding: 6px 0;">
        <div id="rotatingBanner" class="rotating-banner-text">
            <span class="banner-text active">🚚 Free Shipping in India on orders above ₹750</span>
        </div>
    </div>
    <script>
    // Rotating Banner - Initialize after element exists
    (function() {
        const captions = [
            '🚚 Free Shipping in India on orders above ₹750',
            '✨ Premium Quality Craft Supplies | Shop Now & Save Big!',
            '🎨 Discover Amazing Embroidery Threads & Accessories',
            '🔥 New Arrivals Every Week | Stay Creative!',
            '💎 Best Prices Guaranteed | Quality You Can Trust',
            '🌟 Join Thousands of Happy Crafters | Start Your Journey',
            '🎁 Special Discounts on Bulk Orders | Contact Us Today',
            '🏆 India\'s #1 Craft Supplies Store | Fast & Reliable',
            '💖 Handpicked Products | Perfect for Your Projects',
            '⚡ Express Delivery Available | Order Now & Get It Fast!'
        ];

        let currentIndex = 0;
        let rotationInterval = null;

        function rotateBanner() {
            const bannerContainer = document.getElementById('rotatingBanner');
            if (!bannerContainer) return;

            const currentText = bannerContainer.querySelector('.banner-text.active');
            if (!currentText) return;

            // Fade out current
            currentText.classList.remove('active');
            
            setTimeout(() => {
                if (currentText.parentNode) {
                    currentText.remove();
                }
                
                // Move to next caption
                currentIndex = (currentIndex + 1) % captions.length;
                
                // Create and add new text
                const newText = document.createElement('span');
                newText.className = 'banner-text';
                newText.textContent = captions[currentIndex];
                bannerContainer.appendChild(newText);
                
                // Trigger fade in
                setTimeout(() => {
                    newText.classList.add('active');
                }, 10);
            }, 500);
        }

        function startRotation() {
            const bannerContainer = document.getElementById('rotatingBanner');
            if (bannerContainer && !rotationInterval) {
                rotationInterval = setInterval(rotateBanner, 5000);
            }
        }

        // Start rotation when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startRotation);
        } else {
            startRotation();
        }
    })();
    </script>

    <!-- MAIN HEADER -->
    <div class="header-container" style="margin: 0; padding: 10px 0;">

        <!-- LOGO -->
        <div class="logo">
            <a href="index.php" style="text-decoration: none; color: inherit;">
                <img src="cr_logo.png" alt="Craft Royale Logo" title="Craft Royale">
                <h1>Craft <span>Royale</span></h1>
            </a>
        </div>

        <!-- SEARCH -->
        <form action="search.php" method="GET" class="search-box-form" id="searchForm" style="display: flex; width: 100%; max-width: 950px; margin: 0 auto;">
            <div class="search-box" style="flex: 1; position: relative;">
                <input type="text" id="searchInput" name="query" placeholder="Search for products by name or SKU..." autocomplete="off" style="position: relative; z-index: 1;">

                <!-- LIVE SEARCH DROPDOWN -->
                <div id="searchResults" class="search-results" style="position: absolute !important; top: calc(100% + 5px) !important; left: 0 !important; right: 0 !important; width: 100% !important; background: #fff !important; border: 1px solid #e0e0e0 !important; border-radius: 8px !important; box-shadow: 0 4px 20px rgba(0,0,0,0.12) !important; display: none; visibility: hidden; opacity: 0; z-index: 99999 !important; max-height: 450px !important; overflow-y: auto !important; margin-top: 5px !important; transition: opacity 0.2s ease;"></div>
                
                <button type="submit" style="position: relative; z-index: 1;"><i class="fa fa-search"></i></button>
            </div>
        </form>
        
        <script>
        // SEARCH FUNCTIONALITY - Auto-suggestions after 2 characters
        console.log('🔍 Initializing search functionality...');
        
        (function() {
            var searchTimeout = null;
            var isInitialized = false;
            
            function setupSearch() {
                if (isInitialized) return;
                
                var input = document.getElementById('searchInput');
                var results = document.getElementById('searchResults');
                
                if (!input || !results) {
                    console.log('Search elements not found, retrying...');
                    setTimeout(setupSearch, 100);
                    return;
                }
                
                console.log('✅ Search elements found, setting up...');
                isInitialized = true;
                
                input.addEventListener('input', function(e) {
                    var q = this.value.trim();
                    console.log('Input event:', q, 'length:', q.length);
                    
                    if (searchTimeout) clearTimeout(searchTimeout);
                    
                    if (q.length < 2) {
                        results.style.setProperty('display', 'none', 'important');
                        results.style.setProperty('visibility', 'hidden', 'important');
                        results.style.setProperty('opacity', '0', 'important');
                        results.innerHTML = '';
                        return;
                    }
                    
                    console.log('Showing loading for:', q);
                    results.innerHTML = '<div style="padding:15px;text-align:center;color:#999;"><i class="fa fa-spinner fa-spin"></i> Searching...</div>';
                    results.style.setProperty('display', 'block', 'important');
                    results.style.setProperty('visibility', 'visible', 'important');
                    results.style.setProperty('opacity', '1', 'important');
                    
                    searchTimeout = setTimeout(function() {
                        console.log('Fetching suggestions for:', q);
                        fetch('ajax-search.php?q=' + encodeURIComponent(q))
                            .then(function(response) {
                                console.log('Response status:', response.status);
                                return response.text();
                            })
                            .then(function(html) {
                                console.log('Received HTML, length:', html.length);
                                if (html && html.trim().length > 0) {
                                    results.innerHTML = html;
                                    results.style.setProperty('display', 'block', 'important');
                                    results.style.setProperty('visibility', 'visible', 'important');
                                    results.style.setProperty('opacity', '1', 'important');
                                    console.log('✅ Suggestions displayed');
                                } else {
                                    results.style.setProperty('display', 'none', 'important');
                                    results.style.setProperty('visibility', 'hidden', 'important');
                                    results.style.setProperty('opacity', '0', 'important');
                                }
                            })
                            .catch(function(error) {
                                console.error('Fetch error:', error);
                                results.innerHTML = '<div style="padding:15px;color:red;">Error loading suggestions</div>';
                                results.style.setProperty('display', 'block', 'important');
                                results.style.setProperty('visibility', 'visible', 'important');
                                results.style.setProperty('opacity', '1', 'important');
                            });
                    }, 200);
                });
                
                document.addEventListener('click', function(e) {
                    if (input && results && !input.contains(e.target) && !results.contains(e.target)) {
                        results.style.setProperty('display', 'none', 'important');
                        results.style.setProperty('visibility', 'hidden', 'important');
                        results.style.setProperty('opacity', '0', 'important');
                    }
                });
            }
            
            // Run immediately
            setupSearch();
            
            // Also run on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupSearch);
            } else {
                setTimeout(setupSearch, 100);
            }
        })();
        </script>

        <!-- ICONS -->
        <div class="header-icons">
            <a href="javascript:void(0)" onclick="openReels()" class="reels-trigger" title="Royale Reels" style="margin-right: 15px; display: inline-flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <path d="M3 9h18"></path>
                    <path d="M9 3v6"></path>
                    <path d="M15 3v6"></path>
                    <path d="M10 13l5 3-5 3v-6z"></path>
                </svg>
            </a>

            <div class="user-dropdown-wrapper">
                <a href="#" id="loginIcon" onclick="openLogin()"><i class="fa-regular fa-user"></i></a>
                <!-- User Dropdown Menu (only shown when logged in) -->
                <div id="userDropdown" class="user-dropdown" style="display: none;">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fa-solid fa-user"></i> Profile
                    </a>
                    <a href="account.php" class="dropdown-item">
                        <i class="fa-solid fa-shopping-bag"></i> Orders
                    </a>
                    <a href="#" class="dropdown-item logout-link" id="logoutLink">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            <a href="#" id="wishlistIcon" onclick="event.preventDefault(); event.stopPropagation(); if(typeof window.openWishlistSidebar === 'function') { window.openWishlistSidebar(); } else { window.location.href='wishlist.php'; } return false;"><i class="fa-regular fa-heart"></i><span id="wishlistCount" data-count="0"><?php 
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $wishlist_count = 0;
                if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
                    $wishlist_count = count($_SESSION['wishlist']);
                }
                if ($wishlist_count > 0) {
                    echo $wishlist_count;
                }
            ?></span></a>
            <a href="#" id="cartIcon" onclick="console.log('Cart icon clicked'); event.preventDefault(); event.stopPropagation(); if(typeof window.openCartSidebar === 'function') { console.log('Calling window.openCartSidebar'); window.openCartSidebar(); } else if(typeof openCartSidebar === 'function') { console.log('Calling openCartSidebar (local)'); openCartSidebar(); } else { console.log('Fallback: redirecting to cart.php'); window.location.href='cart.php'; } return false;"><i class="fa-solid fa-bag-shopping"></i><span id="cartCount" data-count="0"><?php 
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $cart_count = 0;
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['quantity'];
                    }
                }
                if ($cart_count > 0) {
                    echo $cart_count;
                }
            ?></span></a>
        </div>

    </div>

    <!-- CATEGORY MENU -->
    <div class="category-bar">
        <nav class="category-menu">
            <?php 
            // Map category names to static page files
            $category_page_map = [
                'Embroidery' => 'embroidery.php',
                'Beads' => 'beads.php',
                'Embellishment' => 'embellishment.php',
                'Embellishments' => 'embellishment.php',
                'Jewelry Making' => 'jewelry.php',
                'Craft Supplies' => 'craft-supplies.php',
                'DIY/Tools' => 'diy-tools.php',
                'Diy/Tools' => 'diy-tools.php',
                'Tools / Catalog' => 'diy-tools.php',
                'Tools/Catalog' => 'diy-tools.php',
                'DIY Tools' => 'diy-tools.php',
                'DIY-Tools' => 'diy-tools.php'
            ];
            
            $category_index = 0;
            foreach ($nav_categories as $cat_id => $category): 
                $category_index++;
                $subcategories = isset($nav_subcategories_by_category[$cat_id]) ? $nav_subcategories_by_category[$cat_id] : [];
                $has_subcategories = !empty($subcategories);
                
                // Determine number of columns based on subcategory count
                $sub_count = count($subcategories);
                $num_columns = 3; // Default
                if ($sub_count > 15) $num_columns = 4;
                elseif ($sub_count > 8) $num_columns = 3;
                elseif ($sub_count > 0) $num_columns = min(3, max(1, ceil($sub_count / 5)));
                
                // Organize subcategories into columns
                $sub_columns = organizeSubcategoriesIntoColumns($subcategories, $num_columns);
                
                // Get the static page URL or fallback to products.php
                $category_name = $category['category_name'];
                
                // Try exact match first
                $category_url = isset($category_page_map[$category_name]) ? $category_page_map[$category_name] : null;
                
                // If no exact match, try case-insensitive match
                if (!$category_url) {
                    foreach ($category_page_map as $map_name => $map_url) {
                        if (strcasecmp($category_name, $map_name) === 0) {
                            $category_url = $map_url;
                            break;
                        }
                    }
                }
                
                // If still no match, try partial match (contains "DIY" or "Tools")
                if (!$category_url) {
                    $category_lower = strtolower($category_name);
                    if (strpos($category_lower, 'diy') !== false || strpos($category_lower, 'tools') !== false) {
                        // Check if it contains both DIY and Tools
                        if (strpos($category_lower, 'diy') !== false && strpos($category_lower, 'tools') !== false) {
                            $category_url = 'diy-tools.php';
                        }
                    }
                }
                
                // Final fallback to products.php
                if (!$category_url) {
                    $category_url = "products.php?category=" . $cat_id;
                }
            ?>
                <div class="category-menu-item">
                    <a href="<?= $category_url ?>">
                        <?= htmlspecialchars($category_name) ?>
                        <?php if ($category_index == 1): ?>
                            <span class="badge">Hot</span>
                        <?php endif; ?>
                    </a>
                    <?php if ($has_subcategories): ?>
                        <div class="dropdown-menu">
                            <div class="dropdown-content" style="grid-template-columns: repeat(<?= $num_columns ?>, auto);">
                                <?php foreach ($sub_columns as $column_index => $column_subs): ?>
                                    <div class="dropdown-column">
                                        <?php if (!empty($column_subs)): ?>
                                            <ul>
                                                <?php foreach ($column_subs as $sub): 
                                                    $sub_slug = !empty($sub['subcategory_slug']) ? $sub['subcategory_slug'] : strtolower(str_replace(' ', '-', $sub['subcategory_name']));
                                                ?>
                                                    <li>
                                                        <a href="products.php?category=<?= $cat_id ?>&subcategory=<?= $sub['id'] ?>">
                                                            <?= htmlspecialchars($sub['subcategory_name']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <!-- Static menu items -->
            <div class="category-menu-item">
                <a href="sale.php">Sale</a>
            </div>
            <div class="category-menu-item">
                <a href="new-arrivals.php">New Arrivals<span class="badge">New</span></a>
            </div>
            <a href="best-sellers.php">Best Sellers</a>
            <a href="blog.php">Blog</a>
            <a href="tutorial.php"> Tutorials</a>
            <div class="category-menu-item">
                <a href="gift-cards.php">Gift Cards</a>
            </div>
        </nav>
    </div>
</header>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* Beautiful Colorful Logout Popup Styles */
.logout-swal-popup {
    border-radius: 30px !important;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #f0fdf4 100%) !important;
    border: 3px solid rgba(47, 167, 107, 0.4) !important;
    box-shadow: 0 30px 100px rgba(47, 167, 107, 0.3), 
                0 0 0 1px rgba(47, 167, 107, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
    padding: 60px 50px 50px !important;
    position: relative !important;
    overflow: hidden !important;
    animation: popupZoom 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    max-width: 550px !important;
}

.logout-swal-popup::before {
    content: '' !important;
    position: absolute !important;
    top: -50px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 120px !important;
    height: 120px !important;
    background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 50%, #ff6b9d 100%) !important;
    border-radius: 50% !important;
    box-shadow: 0 10px 40px rgba(47, 167, 107, 0.5),
                0 0 0 8px rgba(255, 255, 255, 0.9),
                0 0 0 12px rgba(47, 167, 107, 0.2) !important;
    z-index: 1 !important;
    animation: floatIcon 3s ease-in-out infinite !important;
}

.logout-swal-popup::after {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: 
        radial-gradient(circle at 15% 25%, rgba(47, 167, 107, 0.12) 0%, transparent 50%),
        radial-gradient(circle at 85% 75%, rgba(47, 199, 180, 0.12) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(255, 107, 157, 0.08) 0%, transparent 60%) !important;
    pointer-events: none !important;
    z-index: 0 !important;
    border-radius: 30px !important;
}

.logout-swal-title {
    font-size: 32px !important;
    font-weight: 800 !important;
    color: #1a1a1a !important;
    position: relative !important;
    z-index: 2 !important;
    letter-spacing: -0.5px !important;
    margin: 20px 0 20px 0 !important;
    padding-top: 10px !important;
    text-align: center !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
}

.logout-swal-content {
    font-size: 18px !important;
    color: #444 !important;
    line-height: 1.8 !important;
    position: relative !important;
    z-index: 2 !important;
    margin-top: 20px !important;
    text-align: center !important;
    font-weight: 500 !important;
}

.logout-swal-confirm {
    background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 50%, #2fa76b 100%) !important;
    background-size: 200% 200% !important;
    border-radius: 50px !important;
    padding: 16px 50px !important;
    font-size: 17px !important;
    font-weight: 700 !important;
    text-transform: none !important;
    letter-spacing: 0.8px !important;
    box-shadow: 0 8px 25px rgba(47, 167, 107, 0.4),
                0 4px 10px rgba(47, 167, 107, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 2 !important;
    border: none !important;
    margin-top: 15px !important;
    color: #ffffff !important;
    animation: gradientShift 3s ease infinite !important;
}

.logout-swal-confirm:hover {
    background-position: 100% 0 !important;
    transform: translateY(-4px) scale(1.05) !important;
    box-shadow: 0 12px 35px rgba(47, 167, 107, 0.5),
                0 6px 15px rgba(47, 167, 107, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
}

.logout-swal-confirm:active {
    transform: translateY(-2px) scale(1.02) !important;
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4) !important;
}

.logout-swal-cancel {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    border-radius: 50px !important;
    padding: 16px 50px !important;
    font-size: 17px !important;
    font-weight: 700 !important;
    text-transform: none !important;
    letter-spacing: 0.8px !important;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    z-index: 2 !important;
    border: 2px solid #dee2e6 !important;
    margin-top: 15px !important;
}

.logout-swal-cancel:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
    transform: translateY(-3px) scale(1.03) !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
    border-color: #ced4da !important;
}

.logout-swal-cancel:active {
    transform: translateY(-1px) scale(1.01) !important;
}

.logout-swal-close {
    color: #999 !important;
    font-size: 28px !important;
    font-weight: 300 !important;
    transition: all 0.2s ease !important;
    opacity: 0.7 !important;
}

.logout-swal-close:hover {
    color: #dc3545 !important;
    opacity: 1 !important;
    transform: rotate(90deg) scale(1.1) !important;
}

@keyframes popupZoom {
    0% {
        opacity: 0;
        transform: scale(0.7) translateY(-30px) rotate(-5deg);
    }
    60% {
        transform: scale(1.05) translateY(5px) rotate(2deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0) rotate(0deg);
    }
}

@keyframes floatIcon {
    0%, 100% {
        transform: translateX(-50%) translateY(0) rotate(0deg);
    }
    50% {
        transform: translateX(-50%) translateY(-12px) rotate(10deg);
    }
}

@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* SweetAlert backdrop */
.swal2-backdrop-show {
    background: rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: blur(5px) !important;
}

/* CRITICAL: Prevent SweetAlert from blocking page scroll */
body.swal2-shown {
    overflow: auto !important;
    padding-right: 0 !important;
}

body.swal2-height-auto {
    height: auto !important;
}

/* Ensure SweetAlert container doesn't block interactions */
.swal2-container {
    pointer-events: none !important;
}

.swal2-container .swal2-popup {
    pointer-events: auto !important;
}

.swal2-container .swal2-backdrop {
    pointer-events: auto !important;
}

/* ================= CUSTOM LOGOUT MODAL - PROFESSIONAL STYLING ================= */
.logout-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.logout-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.logout-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(47, 167, 107, 0.1);
    max-width: 480px;
    width: 90%;
    padding: 0;
    position: relative;
    transform: scale(0.9) translateY(-20px);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: hidden;
}

.logout-modal-overlay.show .logout-modal {
    transform: scale(1) translateY(0);
}

.logout-modal::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2fa76b, #2fc7b4, #2fa76b);
    background-size: 200% 100%;
    animation: shimmer 2s linear infinite;
}

@keyframes shimmer {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.logout-modal-header {
    padding: 40px 40px 20px;
    text-align: center;
    position: relative;
}

.logout-modal-icon {
    font-size: 80px;
    margin-bottom: 20px;
    display: block;
    animation: wave 2s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-20deg); }
}

.logout-modal-title {
    font-size: 28px;
    font-weight: 700;
    color: #2b2b2b;
    margin: 0 0 15px 0;
    background: linear-gradient(135deg, #2b2b2b 0%, #2fa76b 50%, #2fc7b4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 0.5px;
}

.logout-modal-message {
    font-size: 16px;
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.logout-modal-message strong {
    color: #2fa76b;
    font-size: 18px;
    font-weight: 600;
}

.logout-modal-footer {
    padding: 20px 40px 40px;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.logout-modal-btn {
    padding: 14px 35px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 140px;
    position: relative;
    overflow: hidden;
}

.logout-modal-btn-confirm {
    background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
    color: #ffffff;
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.3);
}

.logout-modal-btn-confirm:hover {
    background: linear-gradient(135deg, #2fc7b4 0%, #2fa76b 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(47, 167, 107, 0.4);
}

.logout-modal-btn-confirm:active {
    transform: translateY(0);
}

.logout-modal-btn-cancel {
    background: #f5f5f5;
    color: #666;
    border: 2px solid #e0e0e0;
}

.logout-modal-btn-cancel:hover {
    background: #e8e8e8;
    border-color: #d0d0d0;
    transform: translateY(-2px);
}

.logout-modal-btn-cancel:active {
    transform: translateY(0);
}

.logout-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #999;
    transition: all 0.3s ease;
}

.logout-modal-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #666;
    transform: rotate(90deg);
}

/* Responsive */
@media (max-width: 480px) {
    .logout-modal {
        width: calc(100% - 40px);
        max-width: none;
    }
    
    .logout-modal-header {
        padding: 30px 25px 15px;
    }
    
    .logout-modal-icon {
        font-size: 60px;
    }
    
    .logout-modal-title {
        font-size: 24px;
    }
    
    .logout-modal-message {
        font-size: 14px;
    }
    
    .logout-modal-footer {
        padding: 15px 25px 30px;
        flex-direction: column;
    }
    
    .logout-modal-btn {
        width: 100%;
    }
}

/* Responsive */
@media (max-width: 480px) {
    .logout-swal-popup {
        padding: 50px 30px 40px !important;
        max-width: calc(100% - 40px) !important;
        border-radius: 25px !important;
    }
    
    .logout-swal-title {
        font-size: 26px !important;
        margin: 15px 0 15px 0 !important;
    }
    
    .logout-swal-content {
        font-size: 16px !important;
    }
    
    .logout-swal-confirm,
    .logout-swal-cancel {
        padding: 14px 35px !important;
        font-size: 16px !important;
    }
}
</style>
<script src="assets/js/auth-modals.js?v=<?= time() ?>"></script>
<script>
// Centralized logout handler - SINGLE handler to prevent conflicts
(function() {
    function setupLogout() {
        var link = document.getElementById('logoutLink');
        if (link) {
            // Remove any existing handlers first
            var newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            link = newLink;
            
            // Attach SINGLE handler
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Ensure logout link loses focus before modal opens
                if (document.activeElement === link) {
                    link.blur();
                }
                
                // Remove aria-hidden from header if it exists
                var header = document.querySelector('.main-header, header.main-header');
                if (header) {
                    header.removeAttribute('aria-hidden');
                }
                
                // Close dropdown
                var dropdown = document.getElementById('userDropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
                
                // Use centralized logout handler
                if (typeof handleLogout === 'function') {
                    handleLogout(e);
                } else {
                    // Fallback - direct redirect
                    window.location.href = 'logout.php';
                }
                return false;
            }, true);
        }
    }
    
    // Setup when ready - multiple attempts to ensure it works
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLogout);
    } else {
        setupLogout();
    }
    
    // Also try after delays
    setTimeout(setupLogout, 100);
    setTimeout(setupLogout, 500);
})();
</script>

<!-- SHOPPING CART SIDEBAR -->
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-sidebar-header">
        <div class="cart-logo-container">
            <h2>SHOPPING CART</h2>
        </div>
        <button class="cart-sidebar-close" id="cartSidebarCloseBtn" type="button">&times;</button>
    </div>
    <div class="cart-sidebar-content" id="cartSidebarContent">
        <div style="text-align:center;padding:20px;">
            <i class="fas fa-spinner fa-spin"></i> Loading cart...
        </div>
    </div>
</div>
<div class="cart-sidebar-overlay" id="cartSidebarOverlay" onclick="event.preventDefault();if(typeof window.closeCartSidebar==='function'){window.closeCartSidebar();}return false;"></div>

<!-- WISHLIST SIDEBAR -->
<div id="wishlistSidebar" class="wishlist-sidebar">
    <div class="wishlist-sidebar-header">
        <h2>MY WISHLIST ❤️</h2>
        <button class="wishlist-sidebar-close" onclick="event.preventDefault();event.stopPropagation();if(typeof window.closeWishlistSidebar==='function'){window.closeWishlistSidebar();}return false;">&times;</button>
    </div>
    <div class="wishlist-sidebar-content" id="wishlistSidebarContent">
        <?php 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
                                <button type="button" class="wishlist-add-to-cart" onclick="event.preventDefault();event.stopPropagation();if(typeof window.addToCartFromWishlist==='function'){window.addToCartFromWishlist(<?= $item['id'] ?>);}return false;">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                                <button type="button" class="wishlist-remove" onclick="event.preventDefault();event.stopPropagation();if(typeof window.removeFromWishlist==='function'){window.removeFromWishlist(<?= $item['id'] ?>);}return false;" title="Remove from wishlist">
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
                    <button type="button" class="wishlist-sidebar-btn clear-wishlist" onclick="event.preventDefault();event.stopPropagation();if(confirm('Are you sure you want to clear your wishlist?')){if(typeof window.clearWishlist==='function'){window.clearWishlist();}}return false;">🗑️ Clear Wishlist</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="wishlist-sidebar-overlay" id="wishlistSidebarOverlay" onclick="event.preventDefault();if(typeof window.closeWishlistSidebar==='function'){window.closeWishlistSidebar();}return false;"></div>

<!-- Order Note Modal -->
<div id="orderNoteModal" class="cart-feature-modal" style="z-index: 2000000 !important;">
    <div class="cart-feature-modal-popup order-note-modal-popup">
        <div class="cart-feature-modal-header order-note-modal-header">
            <div class="cart-feature-modal-icon order-note-modal-icon">📝</div>
            <h2 class="cart-feature-modal-title">Add Order Note ✨</h2>
            <p class="cart-feature-modal-subtitle">Share any special instructions with us!</p>
        </div>
        <div class="cart-feature-modal-body">
            <textarea id="orderNoteText" placeholder="How can we help you? 💬" rows="5"></textarea>
        </div>
        <div class="cart-feature-modal-footer">
            <button type="button" class="cart-feature-btn cancel-btn" onclick="if(typeof window.closeOrderNoteModal==='function'){window.closeOrderNoteModal();}return false;">❌ Cancel</button>
            <button type="button" class="cart-feature-btn save-btn order-note-save-btn" onclick="if(typeof window.saveOrderNote==='function'){window.saveOrderNote();}return false;">💾 Save Note</button>
        </div>
    </div>
</div>

<!-- Estimate Shipping Modal -->
<div id="estimateModal" class="cart-feature-modal" style="z-index: 2000000 !important;">
    <div class="cart-feature-modal-popup estimate-modal-popup">
        <div class="cart-feature-modal-header estimate-modal-header">
            <div class="cart-feature-modal-icon estimate-modal-icon">🚚</div>
            <h2 class="cart-feature-modal-title">Estimate Shipping 📍</h2>
            <p class="cart-feature-modal-subtitle">Get shipping rates for your location!</p>
        </div>
        <div class="cart-feature-modal-body">
            <form id="estimateShippingForm">
                <div class="estimate-form-group">
                    <label>🌍 Country</label>
                    <input type="text" name="country" value="India" readonly style="background: #f5f5f5; cursor: not-allowed;">
                </div>
                <div class="estimate-form-group">
                    <label>📍 Province</label>
                    <select name="province" id="estimateStateSelect" required>
                        <option value="">Select Province</option>
                        <option value="Andhra Pradesh">Andhra Pradesh</option>
                        <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                        <option value="Assam">Assam</option>
                        <option value="Bihar">Bihar</option>
                        <option value="Chhattisgarh">Chhattisgarh</option>
                        <option value="Goa">Goa</option>
                        <option value="Gujarat">Gujarat</option>
                        <option value="Haryana">Haryana</option>
                        <option value="Himachal Pradesh">Himachal Pradesh</option>
                        <option value="Jharkhand">Jharkhand</option>
                        <option value="Karnataka">Karnataka</option>
                        <option value="Kerala">Kerala</option>
                        <option value="Madhya Pradesh">Madhya Pradesh</option>
                        <option value="Maharashtra">Maharashtra</option>
                        <option value="Manipur">Manipur</option>
                        <option value="Meghalaya">Meghalaya</option>
                        <option value="Mizoram">Mizoram</option>
                        <option value="Nagaland">Nagaland</option>
                        <option value="Odisha">Odisha</option>
                        <option value="Punjab">Punjab</option>
                        <option value="Rajasthan">Rajasthan</option>
                        <option value="Sikkim">Sikkim</option>
                        <option value="Tamil Nadu">Tamil Nadu</option>
                        <option value="Telangana">Telangana</option>
                        <option value="Tripura">Tripura</option>
                        <option value="Uttar Pradesh">Uttar Pradesh</option>
                        <option value="Uttarakhand">Uttarakhand</option>
                        <option value="West Bengal">West Bengal</option>
                        <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                        <option value="Chandigarh">Chandigarh</option>
                        <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                        <option value="Delhi">Delhi</option>
                        <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                        <option value="Ladakh">Ladakh</option>
                        <option value="Lakshadweep">Lakshadweep</option>
                        <option value="Puducherry">Puducherry</option>
                    </select>
                </div>
                <div class="estimate-form-group">
                    <label>📮 Zip code</label>
                    <input type="text" name="zip_code" id="estimateZipCode" placeholder="Enter 6-digit zip code" pattern="[0-9]{6}" maxlength="6" required>
                </div>
            </form>
            <div id="estimateResults" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                <h4 style="margin: 0 0 15px 0; color: #2b2b2b; font-size: 14px;">We found shipping rates</h4>
                <div id="estimateRatesList"></div>
            </div>
        </div>
        <div class="cart-feature-modal-footer">
            <button type="button" class="cart-feature-btn cancel-btn" onclick="if(typeof window.closeEstimateShippingModal==='function'){window.closeEstimateShippingModal();}return false;">❌ Cancel</button>
            <button type="button" class="cart-feature-btn save-btn estimate-save-btn" onclick="if(typeof window.estimateShippingSidebar==='function'){window.estimateShippingSidebar();}return false;">🚚 Get Estimate</button>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div id="couponModal" class="cart-feature-modal" style="z-index: 2000000 !important;">
    <div class="cart-feature-modal-popup coupon-modal-popup">
        <div class="cart-feature-modal-header coupon-modal-header">
            <div class="cart-feature-modal-icon coupon-modal-icon">🎟️</div>
            <h2 class="cart-feature-modal-title">Add A Coupon 🎁</h2>
            <p class="cart-feature-modal-subtitle">Enter your coupon code to save!</p>
        </div>
        <div class="cart-feature-modal-body">
            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px; text-align: center;">💡 Coupon code will work on checkout page</p>
            <input type="text" id="couponCodeInput" placeholder="Enter coupon code here... 🎫" style="width: 100%; padding: 14px; border: 2px solid #f9a8d4; border-radius: 10px; font-size: 15px; text-align: center; font-weight: 600; box-sizing: border-box;">
        </div>
        <div class="cart-feature-modal-footer">
            <button type="button" class="cart-feature-btn cancel-btn" onclick="if(typeof window.closeCouponModal==='function'){window.closeCouponModal();}return false;">❌ Cancel</button>
            <button type="button" class="cart-feature-btn save-btn coupon-save-btn" onclick="if(typeof window.saveCouponCode==='function'){window.saveCouponCode();}return false;">🎟️ Apply Coupon</button>
        </div>
    </div>
</div>

<!-- Gift Card Modal -->
<div id="giftCardModal" class="cart-feature-modal" style="z-index: 2000000 !important;">
    <div class="cart-feature-modal-popup coupon-modal-popup">
        <div class="cart-feature-modal-header coupon-modal-header">
            <div class="cart-feature-modal-icon coupon-modal-icon">🎁</div>
            <h2 class="cart-feature-modal-title">Use Gift Card 💳</h2>
            <p class="cart-feature-modal-subtitle">Enter your gift card details to apply discount!</p>
        </div>
        <div class="cart-feature-modal-body">
            <div id="giftCardMessage" style="margin: 0 0 15px 0; padding: 10px; border-radius: 8px; font-size: 14px; text-align: center; display: none;"></div>
            <input type="text" id="giftCardNumberInput" placeholder="Enter Gift Card Number (e.g., XXXX-XXXX-XXXX-XXXX)" style="width: 100%; padding: 14px; border: 2px solid #e91e63; border-radius: 10px; font-size: 15px; text-align: center; font-weight: 600; box-sizing: border-box; margin-bottom: 15px; text-transform: uppercase;">
            <input type="text" id="giftCardPinInput" placeholder="Enter PIN (4-10 digits)" maxlength="10" style="width: 100%; padding: 14px; border: 2px solid #e91e63; border-radius: 10px; font-size: 15px; text-align: center; font-weight: 600; box-sizing: border-box;">
            <div id="giftCardInfo" style="margin-top: 15px; padding: 15px; background: #f0f0f0; border-radius: 8px; display: none;">
                <div style="font-size: 13px; color: #666; margin-bottom: 8px;"><strong>Card Balance:</strong> <span id="giftCardBalance">₹0.00</span></div>
                <div style="font-size: 13px; color: #666;"><strong>Valid Till:</strong> <span id="giftCardValidTill">-</span></div>
            </div>
        </div>
        <div class="cart-feature-modal-footer">
            <button type="button" class="cart-feature-btn cancel-btn" onclick="if(typeof window.closeGiftCardModal==='function'){window.closeGiftCardModal();}return false;">❌ Cancel</button>
            <button type="button" class="cart-feature-btn save-btn" id="applyGiftCardBtn" onclick="if(typeof window.applyGiftCard==='function'){window.applyGiftCard();}return false;">🎁 Apply Gift Card</button>
        </div>
    </div>
</div>

<!-- Beautiful Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="delete-confirm-modal" style="z-index: 2000000 !important;">
    <div class="delete-modal-popup">
        <div class="delete-modal-header">
            <div class="delete-modal-icon">🗑️</div>
            <h2 class="delete-modal-title">Remove Item? 🛒</h2>
            <p class="delete-modal-message">Are you sure you want to remove this item from your cart? 😢<br>This action cannot be undone.</p>
        </div>
        <div class="delete-modal-body">
            <div class="delete-modal-actions">
                <button class="delete-modal-btn delete-modal-btn-cancel" onclick="if(typeof window.closeDeleteModal==='function'){window.closeDeleteModal();}return false;">❌ Cancel</button>
                <button class="delete-modal-btn delete-modal-btn-confirm" id="deleteConfirmBtn" onclick="if(typeof window.confirmDeleteItem==='function'){window.confirmDeleteItem();}return false;">🗑️ Yes, Remove</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Cart Sidebar Styles - Clean Light Design */
    .cart-sidebar {
        position: fixed !important;
        top: 0 !important;
        right: -450px !important;
        width: 450px !important;
        max-width: 450px !important;
        height: 100vh !important;
        background: #ffffff !important;
        color: #333 !important;
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15) !important;
        z-index: 1000000 !important; /* CRITICAL: Above header and all page elements */
        transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    .cart-sidebar.active {
        right: 0 !important;
        animation: slideInRight 0.4s ease-out;
        visibility: visible !important;
        opacity: 1 !important;
        display: flex !important;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .cart-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px); /* Added blur for professional look */
        z-index: 999999 !important; /* CRITICAL: Above header but below cart */
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none; /* Allow clicks to pass through when not active */
    }

    .cart-sidebar-overlay.active {
        display: block !important;
        opacity: 1 !important;
        pointer-events: auto; /* Block clicks on overlay when active */
    }
    
    /* Ensure sidebar is above overlay */
    .cart-sidebar {
        position: relative;
        z-index: 1000000 !important;
    }
    
    .cart-sidebar * {
        position: relative;
        pointer-events: auto !important;
    }

    .cart-sidebar-header {
        background: #ffffff !important;
        color: #333 !important;
        padding: 20px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-bottom: 1px solid #e0e0e0 !important;
        position: relative !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .cart-logo-container {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        width: 100% !important;
        overflow: hidden !important;
    }
    
    .cart-logo-icon {
        font-size: 24px !important;
        color: #333 !important;
    }
    
    .cart-sidebar-header h2 {
        font-size: 24px !important;
        font-weight: 800 !important;
        margin: 0 !important;
        color: #fff !important;
    }
    
    .cart-sidebar-header h2 span {
        color: #2fc7b4 !important;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    .cart-sidebar-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: headerShine 3s infinite;
    }

    @keyframes headerShine {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    .cart-sidebar-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        z-index: 1;
    }
    
    .cart-sidebar-header h2 span {
        color: #333;
    }

    .cart-sidebar-close {
        background: transparent;
        border: none;
        width: 30px;
        height: 30px;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        padding: 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10002 !important;
        position: relative;
        line-height: 1;
        pointer-events: auto !important;
    }

    .cart-sidebar-close:hover {
        color: #333;
        transform: scale(1.1);
    }

    .cart-sidebar-content {
        flex: 1 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        background: #ffffff !important;
        color: #333 !important;
        /* Show scrollbar but make it beautiful */
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: #20c997 #f0f0f0; /* Firefox */
        -ms-overflow-style: auto; /* IE and Edge */
    }

    .cart-sidebar-content::-webkit-scrollbar {
        width: 8px; /* Chrome, Safari and Opera */
    }
    
    .cart-sidebar-content::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 10px;
    }
    
    .cart-sidebar-content::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #20c997 0%, #17a2b8 100%);
        border-radius: 10px;
        border: 2px solid #f0f0f0;
    }
    
    .cart-sidebar-content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #17a2b8 0%, #138496 100%);
    }

    .cart-loading {
        text-align: center;
        padding: 40px;
        color: rgba(255,255,255,0.6);
    }
    
    .empty-cart-sidebar {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-cart-sidebar i {
        font-size: 64px;
        color: #ccc;
        margin-bottom: 20px;
        display: block;
    }
    
    .empty-cart-sidebar p {
        font-size: 16px;
        color: #666;
        margin: 0;
    }

    .free-shipping-cart {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 2px solid #c3e6cb;
        border-radius: 12px;
        padding: 16px 20px;
        margin: 0 0 20px 0;
        font-size: 14px;
        color: #155724;
        line-height: 1.6;
        position: relative;
        overflow: hidden;
        overflow-x: hidden;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .cart-item {
        display: flex;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8e8e8;
        background: #fff;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        transition: all 0.3s ease;
        margin-bottom: 8px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
        display: flex;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8e8e8;
        background: #fff;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        transition: all 0.3s ease;
        margin-bottom: 8px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .cart-item:hover {
        background: #fafafa;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .cart-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .cart-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        background: #f5f5f5;
        flex-shrink: 0;
    }

    .cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .cart-item-name {
        font-weight: 500;
        color: #333;
        font-size: 14px;
        margin-bottom: 8px;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .cart-item-color {
        font-size: 12px;
        color: rgba(255,255,255,0.6);
        margin-bottom: 8px;
    }

    .cart-item-price {
        margin-bottom: 8px;
    }

    .cart-item-price .original {
        color: #999;
        text-decoration: line-through;
        font-size: 12px;
        margin-right: 8px;
    }

    .cart-item-price .current {
        color: #333;
        font-weight: 600;
        font-size: 14px;
    }

    .cart-item-quantity-wrapper {
        margin-top: 12px;
        position: relative;
        z-index: 5;
    }

    .cart-item-quantity {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1.5px solid #e9ecef;
        overflow: hidden;
        width: fit-content;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        margin-top: 10px;
    }

    .cart-item-quantity .qty-delete {
        background: #fff;
        border: none;
        border-right: 1.5px solid #e9ecef;
        cursor: pointer !important;
        color: #ff6b6b;
        font-size: 13px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .cart-item-quantity .qty-delete:hover {
        background: #fff5f5;
        color: #fa5252;
    }

    .cart-item-quantity button:not(.qty-delete) {
        background: #fff;
        border: none;
        width: 38px;
        height: 38px;
        cursor: pointer !important;
        pointer-events: auto !important;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        color: #495057;
        flex-shrink: 0;
        user-select: none;
    }

    .cart-item-quantity .qty-decrease {
        border-right: 1.5px solid #e9ecef;
    }

    .cart-item-quantity .qty-increase {
        border-left: 1.5px solid #e9ecef;
    }

    .cart-item-quantity button:not(.qty-delete):hover {
        background: #f1f3f5;
        color: #2fc7b4;
        transform: scale(1.05);
    }

    .cart-item-quantity button:not(.qty-delete):active {
        background: #e9ecef;
        transform: scale(0.95);
    }
    
    .cart-item-quantity .qty-delete {
        pointer-events: auto !important;
        user-select: none;
    }
    
    .cart-item-quantity .qty-delete:active {
        transform: scale(0.95);
    }

    .cart-item-quantity input,
    .cart-item-quantity .qty-input {
        width: 45px;
        height: 38px;
        text-align: center;
        border: none;
        background: #fff;
        color: #212529;
        padding: 0;
        font-size: 15px;
        font-weight: 700;
        pointer-events: none;
        flex-grow: 1;
    }

    .cart-item-quantity input:focus,
    .cart-item-quantity .qty-input:focus {
        outline: none;
        border-color: #2fc7b4;
        box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
        background: #fff;
    }

    /* Hide number input spinners (triangles) */
    /* Hide number input spinners (triangles) */
    .cart-item-quantity input[type="number"]::-webkit-outer-spin-button,
    .cart-item-quantity input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
        display: none !important;
    }

    .cart-item-quantity input[type="number"] {
        -moz-appearance: textfield !important;
    }
    
    /* Also hide for cart page */
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
        display: none !important;
    }
    
    .qty-input {
        -moz-appearance: textfield !important;
    }

    .cart-sidebar-footer {
        padding: 20px;
        border-top: 1px solid #e0e0e0;
        background: #ffffff;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .cart-subtotal {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-weight: 600;
        font-size: 16px;
        color: #333;
        padding: 0;
    }

    .cart-subtotal span:last-child {
        color: #333;
        font-size: 16px;
        font-weight: 700;
    }

    .cart-tax-note {
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
        text-align: center;
        padding: 8px 12px;
        background: rgba(47, 199, 180, 0.1);
        border-radius: 8px;
        border: 1px solid rgba(47, 199, 180, 0.2);
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.5s ease-out;
        transition: all 0.3s ease;
    }

    .cart-tax-note::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(47, 199, 180, 0.1), transparent);
        animation: shimmerText 3s infinite;
    }

    @keyframes shimmerText {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    .cart-tax-note:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        border-color: #2fc7b4;
    }

    /* You May Also Like Section */
    .you-may-also-like-section {
        padding: 20px;
        border-top: 1px solid #e0e0e0;
        background: #ffffff;
        margin-top: auto;
    }

    .you-may-also-like-title {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0 0 15px 0;
        text-transform: none;
    }

    .recommended-products-carousel {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .recommended-products-wrapper {
        overflow: hidden;
        width: 100%;
        position: relative;
    }

    .recommended-products-track {
        display: flex;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        gap: 15px;
        width: max-content;
    }

    .recommended-product-item {
        flex: 0 0 calc(50% - 7.5px);
        min-width: calc(50% - 7.5px);
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        box-sizing: border-box;
    }

    .recommended-product-image-wrapper {
        width: 100%;
        height: 120px;
        overflow: hidden;
        border-radius: 6px;
        cursor: pointer;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
    }

    .recommended-product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .recommended-product-image-wrapper:hover .recommended-product-image {
        transform: scale(1.05);
    }

    .recommended-product-info {
        position: relative;
    }

    .recommended-product-name {
        font-size: 12px;
        color: #333;
        font-weight: 600;
        margin-bottom: 5px;
        cursor: pointer;
        line-height: 1.3;
        transition: color 0.2s ease;
    }

    .recommended-product-name:hover {
        color: #2fc7b4;
    }

    .recommended-product-price {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .recommended-price-original {
        font-size: 11px;
        color: #999;
        text-decoration: line-through;
    }

    .recommended-price-current {
        font-size: 13px;
        color: #dc3545;
        font-weight: 700;
    }

    .recommended-product-view-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #2fc7b4;
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(47, 199, 180, 0.3);
    }

    .recommended-product-view-btn:hover {
        background: #26a693;
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(47, 199, 180, 0.4);
    }

    .carousel-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #e0e0e0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 20px;
        color: #333;
        z-index: 10;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .carousel-nav:hover {
        background: #2fc7b4;
        color: #fff;
        border-color: #2fc7b4;
        box-shadow: 0 4px 10px rgba(47, 199, 180, 0.3);
    }

    .carousel-prev {
        left: -16px;
    }

    .carousel-next {
        right: -16px;
    }

    .carousel-indicators {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 12px;
    }

    .carousel-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .carousel-indicator.active {
        background: #2fc7b4;
        width: 24px;
        border-radius: 4px;
    }

    /* Specially For You Discount */
    .specially-for-you-discount {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #dc3545;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 12px;
        padding: 8px 12px;
        background: #fff3f3;
        border-radius: 6px;
        border: 1px solid #ffcccc;
    }

    .specially-for-you-discount i {
        font-size: 16px;
    }

    /* Subtotal Calculation */
    .cart-subtotal-calculation {
        margin-bottom: 15px;
    }

    .subtotal-line {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 13px;
        color: #666;
    }

    .subtotal-minus {
        color: #dc3545;
        font-weight: 600;
    }

    .cart-sidebar-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    .cart-sidebar-btn {
        padding: 14px 20px;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-align: center;
        text-decoration: none;
        display: block;
        position: relative;
        overflow: hidden;
        overflow-x: hidden;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
    }

    .cart-sidebar-btn::before {
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

    .cart-sidebar-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .cart-sidebar-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .cart-sidebar-btn.view-cart {
        background: #f8f9fa;
        color: #333;
        border: 1px solid #e0e0e0;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .cart-sidebar-btn.view-cart:hover {
        background: #e9ecef;
        border-color: #ccc;
    }

    .cart-sidebar-btn.continue-shopping {
        display: none;
    }

    .cart-sidebar-btn.checkout {
        background: #2fc7b4;
        color: #fff;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
        font-weight: 700;
        border: none;
    }

    .cart-sidebar-btn.checkout:hover {
        background: #26a693;
    }

    .empty-cart-sidebar {
        text-align: center;
        padding: 60px 20px;
        color: rgba(255,255,255,0.7);
    }

    .empty-cart-sidebar i {
        font-size: 64px;
        color: rgba(255,255,255,0.3);
        margin-bottom: 20px;
        display: block;
    }
    
    .empty-cart-sidebar p {
        font-size: 16px;
        color: rgba(255,255,255,0.6);
        margin: 0;
    }

    @media (max-width: 768px) {
        .cart-sidebar {
            width: 100%;
            right: -100%;
        }
        
        .recommended-product-item {
            flex: 0 0 calc(100% - 15px);
            min-width: calc(100% - 15px);
        }
        
        .carousel-prev,
        .carousel-next {
            display: none;
        }
        
        .you-may-also-like-section {
            padding: 15px;
        }
        
        .specially-for-you-discount {
            font-size: 12px;
            padding: 6px 10px;
        }
    }

    /* Wishlist Sidebar Styles */
    .wishlist-sidebar {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        max-width: 450px;
        height: 100vh;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
        z-index: 10001;
        transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        overflow-x: hidden !important;
        box-sizing: border-box;
    }

    .wishlist-sidebar.active {
        right: 0 !important;
        animation: slideInRight 0.4s ease-out;
    }

    .wishlist-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .wishlist-sidebar-overlay.active {
        display: block !important;
        opacity: 1 !important;
    }

    .wishlist-sidebar-header {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
        color: #fff;
        padding: 25px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #e0e0e0;
        box-shadow: 0 2px 10px rgba(233, 30, 99, 0.2);
        position: relative;
        overflow: hidden;
        overflow-x: hidden;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
    }

    .wishlist-sidebar-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 2px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .wishlist-sidebar-close {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        width: 35px;
        height: 35px;
        font-size: 20px;
        color: #fff;
        cursor: pointer;
        padding: 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        position: relative;
    }

    .wishlist-sidebar-close:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: rotate(90deg) scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .wishlist-sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .wishlist-sidebar-content::-webkit-scrollbar {
        display: none;
    }

    .empty-wishlist-sidebar {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .empty-wishlist-sidebar i {
        font-size: 48px;
        color: #e91e63;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .wishlist-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .wishlist-item:last-child {
        border-bottom: none;
    }

    .wishlist-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .wishlist-item-details {
        flex: 1;
        min-width: 0;
    }

    .wishlist-item-name {
        font-weight: 600;
        color: #2b2b2b;
        font-size: 14px;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .wishlist-item-price {
        margin-bottom: 12px;
    }

    .wishlist-item-price .original {
        color: #999;
        text-decoration: line-through;
        font-size: 12px;
        margin-right: 8px;
    }

    .wishlist-item-price .current {
        color: #dc3545;
        font-weight: 700;
        font-size: 16px;
    }

    .wishlist-item-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .wishlist-add-to-cart {
        flex: 1;
        background: linear-gradient(135deg, #2fc7b4 0%, #26a693 100%);
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wishlist-add-to-cart:hover {
        background: linear-gradient(135deg, #26a693 0%, #2fc7b4 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
    }

    .wishlist-remove {
        background: #f8f9fa;
        color: #dc3545;
        border: 1px solid #e0e0e0;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wishlist-remove:hover {
        background: #dc3545;
        color: #fff;
        transform: translateY(-2px);
    }

    .wishlist-sidebar-footer {
        padding: 25px 20px;
        border-top: 2px solid #e0e0e0;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .wishlist-sidebar-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    .wishlist-sidebar-btn {
        padding: 14px 20px;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-align: center;
        text-decoration: none;
        display: block;
        position: relative;
        overflow: hidden;
        overflow-x: hidden;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
    }

    .wishlist-sidebar-btn.continue-shopping {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }

    .wishlist-sidebar-btn.continue-shopping:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    }

    .wishlist-sidebar-btn.clear-wishlist {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: #fff;
    }

    .wishlist-sidebar-btn.clear-wishlist:hover {
        background: linear-gradient(135deg, #ee5a6f 0%, #ff6b6b 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3);
    }

    @media (max-width: 768px) {
        .wishlist-sidebar {
            width: 100%;
            right: -100%;
        }
    }

    /* Beautiful Delete Confirmation Modal */
    .delete-confirm-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 2000000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }

    .delete-confirm-modal.show {
        display: flex;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .delete-modal-popup {
        background: #ffffff;
        border-radius: 24px;
        padding: 0;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(220, 53, 69, 0.4);
        animation: popupZoom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        border: 3px solid rgba(220, 53, 69, 0.2);
        position: relative;
    }

    @keyframes popupZoom {
        from {
            transform: scale(0.8) rotate(-2deg);
            opacity: 0;
        }
        to {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    .delete-modal-popup::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #dc3545, #e74c3c, #dc3545);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .delete-modal-header {
        padding: 35px 30px 25px;
        text-align: center;
        background: #ffffff;
        border-bottom: 2px solid rgba(220, 53, 69, 0.1);
    }

    .delete-modal-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        animation: iconBounce 2s ease-in-out infinite;
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: #fff;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    }

    @keyframes iconBounce {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }

    .delete-modal-title {
        font-size: 26px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 12px 0;
    }

    .delete-modal-message {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
        margin: 0;
    }

    .delete-modal-body {
        padding: 30px;
        background: #ffffff;
    }

    .delete-modal-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 25px;
    }

    .delete-modal-btn {
        padding: 14px 35px;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        min-width: 130px;
        position: relative;
        overflow: hidden;
    }

    .delete-modal-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .delete-modal-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .delete-modal-btn-confirm {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: #fff;
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    }

    .delete-modal-btn-confirm:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }

    .delete-modal-btn-cancel {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #dee2e6;
    }

    .delete-modal-btn-cancel:hover {
        background: #e9ecef;
        transform: translateY(-3px);
    }

    /* Cart Action Icons - Beautiful & Animated */
    .cart-action-icons {
        display: flex;
        justify-content: center;
        gap: 25px;
        padding: 25px 20px;
        margin: 20px 0;
        border-radius: 20px;
        background: rgba(47, 199, 180, 0.1);
        border: 1px solid rgba(47, 199, 180, 0.2);
        position: relative;
        overflow: hidden;
        overflow-x: hidden;
        overflow-y: hidden;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
    }

    .cart-action-icons::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    .cart-action-icon {
        cursor: pointer !important;
        pointer-events: auto !important;
        z-index: 10001 !important;
        position: relative;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        color: rgba(255,255,255,0.9);
        font-size: 20px;
        position: relative;
        overflow: visible;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .cart-action-icon::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
        z-index: 0;
    }

    .cart-action-icon:hover::before {
        width: 80px;
        height: 80px;
    }

    .cart-action-icon .icon-emoji {
        font-size: 28px;
        line-height: 1;
        display: block;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.15));
        position: relative;
        z-index: 1;
        pointer-events: none; /* Allow clicks to pass through to button */
    }

    .cart-action-icon i {
        display: none;
    }

    /* First Icon - Order Note (Purple/Lavender theme) */
    .cart-action-icon:nth-child(1) {
        background: rgba(147, 51, 234, 0.2);
        border-color: rgba(147, 51, 234, 0.4);
    }

    .cart-action-icon:nth-child(1)::before {
        background: rgba(147, 51, 234, 0.3);
    }

    .cart-action-icon:nth-child(1):hover {
        background: rgba(147, 51, 234, 0.3);
        border-color: rgba(147, 51, 234, 0.6);
        transform: translateY(-8px) scale(1.15);
        box-shadow: 0 12px 30px rgba(147, 51, 234, 0.5);
    }

    .cart-action-icon:nth-child(1):hover .icon-emoji {
        transform: scale(1.25) rotate(-5deg);
        filter: drop-shadow(0 5px 10px rgba(147, 51, 234, 0.3));
    }

    /* Second Icon - Estimate Shipping (Orange/Yellow theme) */
    .cart-action-icon:nth-child(2) {
        background: rgba(249, 115, 22, 0.2);
        border-color: rgba(249, 115, 22, 0.4);
    }

    .cart-action-icon:nth-child(2)::before {
        background: rgba(249, 115, 22, 0.3);
    }

    .cart-action-icon:nth-child(2):hover {
        background: rgba(249, 115, 22, 0.3);
        border-color: rgba(249, 115, 22, 0.6);
        transform: translateY(-8px) scale(1.15);
        box-shadow: 0 12px 30px rgba(249, 115, 22, 0.5);
    }

    .cart-action-icon:nth-child(2):hover .icon-emoji {
        transform: scale(1.25) rotate(5deg);
        filter: drop-shadow(0 5px 10px rgba(249, 115, 22, 0.3));
    }

    /* Third Icon - Coupon (Pink theme) */
    .cart-action-icon:nth-child(3) {
        background: rgba(236, 72, 153, 0.2);
        border-color: rgba(236, 72, 153, 0.4);
    }

    .cart-action-icon:nth-child(3)::before {
        background: rgba(236, 72, 153, 0.3);
    }

    .cart-action-icon:nth-child(3):hover {
        background: rgba(236, 72, 153, 0.3);
        border-color: rgba(236, 72, 153, 0.6);
        transform: translateY(-8px) scale(1.15);
        box-shadow: 0 12px 30px rgba(236, 72, 153, 0.5);
    }

    .cart-action-icon:nth-child(3):hover .icon-emoji {
        transform: scale(1.25) rotate(-5deg);
        filter: drop-shadow(0 5px 10px rgba(236, 72, 153, 0.3));
    }

    /* Subtle bounce animation on load */
    @keyframes iconBounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-4px);
        }
    }

    .cart-action-icon:nth-child(1) {
        animation: iconBounce 3s ease-in-out infinite;
        animation-delay: 0s;
    }

    .cart-action-icon:nth-child(2) {
        animation: iconBounce 3s ease-in-out infinite;
        animation-delay: 0.4s;
    }

    .cart-action-icon:nth-child(3) {
        animation: iconBounce 3s ease-in-out infinite;
        animation-delay: 0.8s;
    }

    /* Fourth Icon - Gift Card (Yellow/Gold theme) */
    .cart-action-icon:nth-child(4) {
        background: rgba(234, 179, 8, 0.2);
        border-color: rgba(234, 179, 8, 0.4);
    }

    .cart-action-icon:nth-child(4)::before {
        background: rgba(234, 179, 8, 0.3);
    }

    .cart-action-icon:nth-child(4):hover {
        background: rgba(234, 179, 8, 0.3);
        border-color: rgba(234, 179, 8, 0.6);
        transform: translateY(-8px) scale(1.15);
        box-shadow: 0 12px 30px rgba(234, 179, 8, 0.5);
    }

    .cart-action-icon:nth-child(4):hover .icon-emoji {
        transform: scale(1.25) rotate(5deg);
        filter: drop-shadow(0 5px 10px rgba(234, 179, 8, 0.3));
    }

    .cart-action-icon:nth-child(4) {
        animation: iconBounce 3s ease-in-out infinite;
        animation-delay: 1.2s;
    }

    /* Cart Feature Modals - Beautiful & Animated */
    .cart-feature-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 2000000 !important; /* CRITICAL: Above cart sidebar (1000000) */
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    /* Delete Confirmation Modal - Same z-index as feature modals */
    .delete-confirm-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 2000000 !important; /* CRITICAL: Above cart sidebar (1000000) */
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .delete-confirm-modal.show {
        display: flex !important;
    }
    
    .delete-modal-popup {
        background: #ffffff;
        border-radius: 24px;
        padding: 0;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: popupZoom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 3px solid rgba(220, 53, 69, 0.2);
        position: relative;
        overflow: hidden;
    }

    .cart-feature-modal.show {
        display: flex;
    }

    .cart-feature-modal-popup {
        background: #ffffff;
        border-radius: 24px;
        padding: 0;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: popupZoom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 3px solid rgba(0, 0, 0, 0.1);
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Order Note Modal - Purple/Lavender Theme */
    .order-note-modal-popup {
        border-color: rgba(147, 51, 234, 0.2);
        box-shadow: 0 20px 60px rgba(147, 51, 234, 0.3);
    }

    .order-note-modal-popup::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #9333ea, #a855f7, #9333ea);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    .order-note-modal-header {
        padding: 35px 30px 25px;
        text-align: center;
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border-bottom: 2px solid rgba(147, 51, 234, 0.1);
    }

    .order-note-modal-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        animation: iconBounce 2s ease-in-out infinite;
        background: linear-gradient(135deg, #9333ea, #a855f7);
        color: #fff;
        box-shadow: 0 8px 25px rgba(147, 51, 234, 0.3);
    }

    /* Estimate Modal - Orange/Yellow Theme */
    .estimate-modal-popup {
        border-color: rgba(251, 146, 60, 0.2);
        box-shadow: 0 20px 60px rgba(251, 146, 60, 0.3);
    }

    .estimate-modal-popup::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #fb923c, #f97316, #fb923c);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    .estimate-modal-header {
        padding: 35px 30px 25px;
        text-align: center;
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-bottom: 2px solid rgba(251, 146, 60, 0.1);
    }

    .estimate-modal-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        animation: iconBounce 2s ease-in-out infinite;
        background: linear-gradient(135deg, #fb923c, #f97316);
        color: #fff;
        box-shadow: 0 8px 25px rgba(251, 146, 60, 0.3);
    }

    /* Coupon Modal - Pink Theme */
    .coupon-modal-popup {
        border-color: rgba(236, 72, 153, 0.2);
        box-shadow: 0 20px 60px rgba(236, 72, 153, 0.3);
    }

    .coupon-modal-popup::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ec4899, #f472b6, #ec4899);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    .coupon-modal-header {
        padding: 35px 30px 25px;
        text-align: center;
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
        border-bottom: 2px solid rgba(236, 72, 153, 0.1);
    }

    .coupon-modal-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        animation: iconBounce 2s ease-in-out infinite;
        background: linear-gradient(135deg, #ec4899, #f472b6);
        color: #fff;
        box-shadow: 0 8px 25px rgba(236, 72, 153, 0.3);
    }

    .cart-feature-modal-header {
        padding: 35px 30px 25px;
        text-align: center;
        background: #ffffff;
    }

    .cart-feature-modal-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        animation: iconBounce 2s ease-in-out infinite;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .cart-feature-modal-title {
        font-size: 26px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 12px 0;
    }

    .cart-feature-modal-subtitle {
        font-size: 15px;
        color: #666;
        line-height: 1.5;
        margin: 0;
    }

    .cart-feature-modal-body {
        padding: 30px;
        background: #ffffff;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .cart-feature-modal-body textarea {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 15px;
        font-family: inherit;
        resize: vertical;
        min-height: 120px;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .cart-feature-modal-body textarea:focus {
        outline: none;
        border-color: #9333ea;
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
        transform: scale(1.01);
    }

    /* Order Note Textarea - Purple Theme */
    .order-note-modal-popup textarea {
        border-color: #e8d5ff;
    }

    .order-note-modal-popup textarea:hover {
        border-color: #c084fc;
    }

    .order-note-modal-popup textarea:focus {
        border-color: #9333ea !important;
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15) !important;
    }

    /* Coupon Input Styling */
    #couponCodeInput {
        transition: all 0.3s ease;
        border-color: #f9a8d4 !important;
    }

    #couponCodeInput:hover {
        border-color: #ec4899 !important;
    }

    /* Gift Card Modal - Yellow/Gold Theme */
    #giftCardModal .cart-feature-modal-popup {
        border-color: rgba(234, 179, 8, 0.2);
        box-shadow: 0 20px 60px rgba(234, 179, 8, 0.3);
    }

    #giftCardModal .cart-feature-modal-popup::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #eab308, #facc15, #eab308);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    #giftCardModal .cart-feature-modal-header {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
        border-bottom: 2px solid rgba(234, 179, 8, 0.1);
    }

    #giftCardModal .cart-feature-modal-icon {
        background: linear-gradient(135deg, #eab308, #facc15);
        color: #fff;
        box-shadow: 0 8px 25px rgba(234, 179, 8, 0.3);
    }

    #giftCardNumberInput,
    #giftCardPinInput {
        transition: all 0.3s ease;
        border-color: #e91e63 !important;
    }

    #giftCardNumberInput:hover,
    #giftCardPinInput:hover {
        border-color: #c2185b !important;
        transform: scale(1.02);
    }

    #giftCardNumberInput:focus,
    #giftCardPinInput:focus {
        border-color: #e91e63 !important;
        box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.15) !important;
        transform: scale(1.02);
    }
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
    }

    #couponCodeInput:focus {
        outline: none;
        border-color: #ec4899 !important;
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
        transform: scale(1.02);
    }

    .estimate-form-group {
        margin-bottom: 20px;
    }

    .estimate-form-group label {
        display: block;
        margin-bottom: 10px;
        font-size: 15px;
        font-weight: 600;
        color: #2b2b2b;
    }

    .estimate-form-group input,
    .estimate-form-group select {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        font-family: inherit;
        box-sizing: border-box;
        transition: all 0.3s ease;
        background: #fff;
    }

    .estimate-form-group input:hover,
    .estimate-form-group select:hover {
        border-color: #fb923c;
    }

    .estimate-form-group input:focus,
    .estimate-form-group select:focus {
        outline: none;
        border-color: #fb923c;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.1);
    }

    .cart-feature-modal-footer {
        padding: 25px 30px;
        border-top: 2px solid rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 15px;
        justify-content: center;
        background: #ffffff;
        flex-shrink: 0;
        position: relative;
        z-index: 10;
    }

    .cart-feature-btn {
        padding: 14px 35px;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 130px;
        position: relative;
        overflow: hidden;
    }

    .cart-feature-btn::before {
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

    .cart-feature-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .cart-feature-btn.cancel-btn {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #dee2e6;
    }

    .cart-feature-btn.cancel-btn:hover {
        background: #e9ecef;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Order Note Save Button - Purple */
    .order-note-save-btn {
        background: linear-gradient(135deg, #9333ea, #a855f7);
        color: #fff;
        box-shadow: 0 6px 20px rgba(147, 51, 234, 0.3);
    }

    .order-note-save-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4);
    }

    /* Estimate Save Button - Orange */
    .estimate-save-btn {
        background: linear-gradient(135deg, #fb923c, #f97316);
        color: #fff;
        box-shadow: 0 6px 20px rgba(251, 146, 60, 0.3);
    }

    .estimate-save-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(251, 146, 60, 0.4);
    }

    /* Coupon Save Button - Pink */
    .coupon-save-btn {
        background: linear-gradient(135deg, #ec4899, #f472b6);
        color: #fff;
        box-shadow: 0 6px 20px rgba(236, 72, 153, 0.3);
    }

    .coupon-save-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
    }

</style>

<script>
// Define cart functions FIRST - before anything else, so they're always available
    // Definitions removed to prevent duplicates

// Make functions globally accessible - Final definition that opens sidebar properly
window.openCartSidebar = function() {
    console.log('=== openCartSidebar called ===');
    function doOpenCartSidebar() {
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartSidebarOverlay');
        const cartContent = document.getElementById('cartSidebarContent');
        
        console.log('Elements check:', {
            sidebar: !!sidebar,
            overlay: !!overlay,
            cartContent: !!cartContent
        });
        
        if (!sidebar) {
            console.error('❌ Cart sidebar element not found!');
            alert('Cart sidebar not found. Please refresh the page.');
            return false;
        }
        
        if (!overlay) {
            console.error('❌ Cart sidebar overlay not found!');
        }
        
        console.log('✅ Opening cart sidebar...');
        
        // Show sidebar
        sidebar.classList.add('active');
        if (overlay) {
            overlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
        
        // Always load fresh content from get-cart-sidebar.php when opening
        // Show loading state
        if (cartContent) {
            cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
        }
        
            // Load cart content from get-cart-sidebar.php
            console.log('🔄 Loading cart content from get-cart-sidebar.php...');
            // Use root-relative path - get-cart-sidebar.php is in the root directory
            // Get the current path and extract the base directory
            const currentPath = window.location.pathname;
            // Find the base directory (everything before the filename)
            const lastSlash = currentPath.lastIndexOf('/');
            const basePath = currentPath.substring(0, lastSlash + 1);
            // Build URL - if basePath is just '/', use empty string, otherwise use basePath
            const url = (basePath === '/' ? '' : basePath) + 'get-cart-sidebar.php?t=' + Date.now() + '&r=' + Math.random();
            console.log('📡 Fetch URL:', url);
            console.log('📡 Current path:', currentPath);
            console.log('📡 Base path:', basePath);
            console.log('📡 Full URL:', window.location.origin + url);
        
        fetch(url, {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('📥 Response status:', response.status, response.statusText);
            console.log('📥 Response headers:', response.headers);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ' ' + response.statusText);
            }
            return response.text();
        })
        .then(html => {
            console.log('✅ Cart HTML received, length:', html ? html.length : 0);
            console.log('✅ First 200 chars:', html ? html.substring(0, 200) : 'empty');
            if (cartContent) {
                if (html && html.trim()) {
                    cartContent.innerHTML = html;
                    console.log('✅ Cart sidebar content updated successfully!');
                    
                    // CRITICAL: Recalculate totals after HTML is loaded
                    setTimeout(function() {
                        if (typeof window.updateSidebarSubtotalInstantly === 'function') {
                            window.updateSidebarSubtotalInstantly();
                            console.log('✅ Cart totals recalculated after load');
                        } else {
                            console.warn('⚠️ updateSidebarSubtotalInstantly not found');
                        }
                        
                        // CRITICAL: Setup event handlers after content is loaded
                        if (typeof window.setupCartSidebarEvents === 'function') {
                            window.setupCartSidebarEvents(true); // Force re-setup
                            console.log('✅ Cart event handlers attached after load');
                        } else {
                            console.warn('⚠️ setupCartSidebarEvents not found');
                        }
                    }, 200);
                } else {
                    console.warn('⚠️ Empty or invalid HTML received');
                    cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
                }
            }
        })
        .catch(err => {
            console.error('❌ Error loading cart:', err);
            console.error('❌ Error details:', err.message, err.stack);
            if (cartContent) {
                cartContent.innerHTML = '<div style="text-align:center;padding:20px;color:#dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading cart: ' + err.message + '</p><p style="font-size:12px;margin-top:10px;">Please refresh the page.</p></div>';
            }
        });
        
        return true;
    }
    
    // Try to open immediately
    if (doOpenCartSidebar()) {
        return;
    }
    
    // If DOM is still loading, wait for it
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                doOpenCartSidebar();
            }, 100);
        });
        return;
    }
    
    // If elements not found, wait a bit and try again (multiple retries)
    setTimeout(function() {
        if (!doOpenCartSidebar()) {
            setTimeout(function() {
                if (!doOpenCartSidebar()) {
                    setTimeout(function() {
                        doOpenCartSidebar();
                    }, 300);
                }
            }, 200);
        }
    }, 100);
};

function openCartSidebar() {
    window.openCartSidebar();
}

// Make functions globally accessible
// Ensure closeCartSidebar is defined (don't override if already defined with try-catch)
if (!window.closeCartSidebar || !window.closeCartSidebar.toString().includes('try')) {
    window.closeCartSidebar = function() {
        try {
            console.log('🔒 closeCartSidebar called');
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartSidebarOverlay');
            if (sidebar) {
                sidebar.classList.remove('active');
                console.log('✅ Sidebar closed');
            }
            if (overlay) {
                overlay.classList.remove('active');
                console.log('✅ Overlay closed');
            }
            document.body.style.overflow = '';
            return false;
        } catch(err) {
            console.error('❌ Error in closeCartSidebar:', err);
            // Force close on error
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartSidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
            return false;
        }
    };
}

function closeCartSidebar() {
    return window.closeCartSidebar();
}

// Recommended Products Carousel Functions
function scrollRecommendedProducts(direction) {
    const track = document.getElementById('recommendedProductsTrack');
    if (!track) return;
    
    const items = track.querySelectorAll('.recommended-product-item');
    if (items.length === 0) return;
    
    const itemWidth = items[0].offsetWidth + 15; // width + gap
    const visibleItems = 2; // Show 2 items at a time
    const currentTransform = track.style.transform || 'translateX(0px)';
    const currentX = parseInt((currentTransform.match(/-?\d+/) || [0])[0]);
    
    let newX;
    if (direction === 'next') {
        newX = currentX - (itemWidth * visibleItems);
        const maxScroll = -(itemWidth * (items.length - visibleItems));
        newX = Math.max(newX, maxScroll);
    } else {
        newX = currentX + (itemWidth * visibleItems);
        newX = Math.min(newX, 0);
    }
    
    track.style.transform = 'translateX(' + newX + 'px)';
    updateCarouselIndicators(newX, itemWidth, visibleItems, items.length);
}

function goToRecommendedSlide(index) {
    const track = document.getElementById('recommendedProductsTrack');
    if (!track) return;
    
    const items = track.querySelectorAll('.recommended-product-item');
    if (items.length === 0) return;
    
    const itemWidth = items[0].offsetWidth + 15;
    const visibleItems = 2;
    const newX = -(itemWidth * visibleItems * index);
    const maxScroll = -(itemWidth * (items.length - visibleItems));
    const finalX = Math.max(newX, maxScroll);
    
    track.style.transform = 'translateX(' + finalX + 'px)';
    updateCarouselIndicators(finalX, itemWidth, visibleItems, items.length);
}

function updateCarouselIndicators(currentX, itemWidth, visibleItems, totalItems) {
    const indicators = document.querySelectorAll('.carousel-indicator');
    if (indicators.length === 0) return;
    
    const slideIndex = Math.abs(Math.round(currentX / (itemWidth * visibleItems)));
    const maxSlide = Math.ceil((totalItems - visibleItems) / visibleItems);
    const activeIndex = Math.min(slideIndex, maxSlide);
    
    indicators.forEach((indicator, index) => {
        if (index === activeIndex) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

// Make carousel functions globally accessible
window.scrollRecommendedProducts = scrollRecommendedProducts;
window.goToRecommendedSlide = goToRecommendedSlide;

// Function to load cart sidebar content via AJAX
window.loadCartSidebar = function() {
    // Don't try to load cart sidebar on checkout page (it doesn't exist there)
    if (window.location.pathname.includes('checkout.php')) {
        return;
    }
    
    const cartContent = document.getElementById('cartSidebarContent');
    if (!cartContent) {
        console.error('cartSidebarContent element not found');
        return;
    }
    
    // Keep sidebar open if it's already open
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartSidebarOverlay');
    const wasOpen = sidebar && sidebar.classList.contains('active');
    
    // Always show loading state
    cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
    
    // Add cache-busting parameter to ensure fresh content
    const timestamp = new Date().getTime();
    const random = Math.random();
    // Use absolute path from root to avoid path issues
    const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    const url = basePath + 'get-cart-sidebar.php?t=' + timestamp + '&r=' + random + '&_=' + Date.now();
    
    console.log('🔄 loadCartSidebar: Loading from:', url);
    console.log('🔄 loadCartSidebar: Base path:', basePath);
    console.log('🔄 loadCartSidebar: Full URL:', window.location.origin + url);
    
    fetch(url, {
        method: 'GET',
        cache: 'no-store',
        credentials: 'same-origin',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('📥 loadCartSidebar: Response status:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
            }
            return response.text();
        })
        .then(html => {
            console.log('✅ loadCartSidebar: Received HTML length:', html ? html.length : 0);
            console.log('✅ loadCartSidebar: First 200 chars:', html ? html.substring(0, 200) : 'empty');
            if (html && html.trim() !== '') {
                // Force update by clearing first
                cartContent.innerHTML = '';
                // Use requestAnimationFrame for smooth update
                requestAnimationFrame(() => {
                    cartContent.innerHTML = html;
                    console.log('✅ loadCartSidebar: Cart sidebar content updated');
                    
                    // Keep sidebar open if it was open
                    if (wasOpen && sidebar && overlay) {
                        sidebar.classList.add('active');
                        overlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                    
                    // Execute scripts in the loaded HTML
                    const scripts = cartContent.querySelectorAll('script');
                    console.log('📜 loadCartSidebar: Found', scripts.length, 'script(s) to execute');
                    scripts.forEach((script, index) => {
                        try {
                            console.log('📜 loadCartSidebar: Executing script', index + 1);
                            const newScript = document.createElement('script');
                            newScript.textContent = script.textContent;
                            document.head.appendChild(newScript);
                            setTimeout(() => {
                                if (newScript.parentNode) {
                                    newScript.parentNode.removeChild(newScript);
                                }
                            }, 100);
                            if (script.textContent) {
                                eval(script.textContent);
                            }
                        } catch (e) {
                            console.error('❌ loadCartSidebar: Error executing script:', e);
                        }
                    });
                    
                    // Reattach event listeners after content update
                    setTimeout(() => {
                        if (typeof window.setupCartSidebarEvents === 'function') {
                            window.setupCartSidebarEvents();
                        }
                    }, 50);
                });
            } else {
                console.warn('⚠️ loadCartSidebar: Empty HTML received from get-cart-sidebar.php');
                cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
            }
        })
        .catch(error => {
            console.error('❌ loadCartSidebar: Error loading cart sidebar:', error);
            console.error('❌ loadCartSidebar: Error details:', error.message, error.stack);
            cartContent.innerHTML = '<div style="text-align:center;padding:20px;color:#dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading cart: ' + error.message + '</p><p style="font-size:12px;margin-top:10px;">Please refresh the page.</p></div>';
        });
};

// Proxy function for backward compatibility
window.makeCartButtonsWork = function() {
    if (typeof window.setupCartSidebarEvents === 'function') {
        window.setupCartSidebarEvents();
    }
};

// Function to attach direct event listeners to cart buttons
function attachDirectCartButtonListeners() {
    // Check if cart sidebar exists - if not, return early (e.g., on checkout page)
    const cartSidebar = document.getElementById('cartSidebar');
    if (!cartSidebar) return;
    
    // Delegation is now the primary method, so direct listeners are mostly for backup
    // but they can conflict if they stop propagation.
}

function handleIncreaseClick(e) {
    e.preventDefault();
    e.stopPropagation();
    const productId = this.getAttribute('data-product-id');
    if (productId && typeof window.updateCartQuantity === 'function') {
        window.updateCartQuantity(productId, 'increase');
    }
    return false;
}

function handleDecreaseClick(e) {
    e.preventDefault();
    e.stopPropagation();
    const productId = this.getAttribute('data-product-id');
    if (productId && typeof window.updateCartQuantity === 'function') {
        window.updateCartQuantity(productId, 'decrease');
    }
    return false;
}

function handleDeleteClick(e) {
    e.preventDefault();
    e.stopPropagation();
    const productId = this.getAttribute('data-product-id') || this.closest('[data-product-id]')?.getAttribute('data-product-id');
    if (productId && typeof window.removeCartProduct === 'function') {
        window.removeCartProduct(productId);
    }
    return false;
}

// Function to refresh cart sidebar (reloads page to get updated cart)
function refreshCartSidebar() {
    // Try to load via AJAX first
    if (typeof window.loadCartSidebar === 'function') {
        window.loadCartSidebar();
    } else {
        // Fallback: reload page
    window.location.reload();
    }
}

// Define cart functions first - Make sure they're globally accessible
// Definitions removed to prevent duplicates

// removeCartProduct is already defined in head section with beautiful modal
</script>

<script>
window.editCartProduct = function(productId) {
    if (typeof closeCartSidebar === 'function') {
        closeCartSidebar();
    }
    window.location.href = 'product.php?id=' + productId;
};

// Make checkout function globally accessible
window.checkoutFromSidebar = function() {
    <?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])): 
    ?>
        if (typeof closeCartSidebar === 'function') {
            closeCartSidebar();
        }
        if (typeof openLogin === 'function') {
            openLogin();
        } else {
            window.location.href = 'login.php';
        }
        return;
    <?php else: ?>
        if (typeof closeCartSidebar === 'function') {
            closeCartSidebar();
        }
        window.location.href = 'checkout.php';
        return;
    <?php endif; ?>
};

// Set up event delegation for cart sidebar - ENHANCED VERSION
window.setupCartSidebarEvents = function(force = false) {
    console.log('🔧 setupCartSidebarEvents called, force:', force);
    const sidebar = document.getElementById('cartSidebar');
    if (!sidebar) {
        console.warn('⚠️ Cart sidebar element not found');
        return;
    }
    
    // Remove the restrictive guard - always set up events when called
    console.log('✅ Setting up cart sidebar events');
    
    const cartClickHandler = function(e) {
        console.log('🖱️ Cart sidebar clicked:', e.target);
        const t = e.target;
        const delBtn = t.closest('.qty-delete');
        const incBtn = t.closest('.qty-increase');
        const decBtn = t.closest('.qty-decrease');
        const closeBtn = t.closest('#cartSidebarCloseBtn, .cart-sidebar-close');
        
        if (closeBtn) {
            console.log('🚪 Close button clicked');
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.closeCartSidebar === 'function') window.closeCartSidebar();
        } else if (delBtn) {
            console.log('🗑️ Delete button clicked');
            e.preventDefault();
            e.stopPropagation();
            const pid = delBtn.getAttribute('data-product-id');
            const sk = delBtn.getAttribute('data-session-key');
            console.log('Product ID:', pid, 'Session Key:', sk);
            if (typeof window.removeCartProduct === 'function') window.removeCartProduct(pid, sk);
        } else if (incBtn) {
            console.log('➕ Increase button clicked');
            e.preventDefault();
            e.stopPropagation();
            const pid = incBtn.getAttribute('data-product-id');
            const sk = incBtn.getAttribute('data-session-key');
            console.log('Product ID:', pid, 'Session Key:', sk);
            if (typeof window.updateCartQuantity === 'function') {
                window.updateCartQuantity(pid, 'increase', null, sk);
            } else {
                console.error('❌ updateCartQuantity function not found');
            }
        } else if (decBtn) {
            console.log('➖ Decrease button clicked');
            e.preventDefault();
            e.stopPropagation();
            const pid = decBtn.getAttribute('data-product-id');
            const sk = decBtn.getAttribute('data-session-key');
            console.log('Product ID:', pid, 'Session Key:', sk);
            if (typeof window.updateCartQuantity === 'function') {
                window.updateCartQuantity(pid, 'decrease', null, sk);
            } else {
                console.error('❌ updateCartQuantity function not found');
            }
        }
    };

    // Always remove old handler if it exists
    if (sidebar._lastHandler) {
        console.log('🔄 Removing old event handler');
        sidebar.removeEventListener('click', sidebar._lastHandler);
    }
    
    console.log('✅ Attaching new event handler');
    sidebar.addEventListener('click', cartClickHandler);
    sidebar._lastHandler = cartClickHandler;
    
    console.log('✅ Cart sidebar events setup complete');
};


// Initialize on page load
(function() {
    function attachEvents() {
        // Only attach cart sidebar events if cart sidebar exists (not on checkout page)
        const cartSidebar = document.getElementById('cartSidebar');
        if (cartSidebar) {
            // Attach direct listeners first
            attachDirectCartButtonListeners();
            // Then setup event delegation
            if (typeof window.setupCartSidebarEvents === 'function') {
                window.setupCartSidebarEvents();
            }
        }
    }
    
    // Try immediately, or wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachEvents);
    } else {
        attachEvents();
    }
})();

// Update cart count in header
function updateCartCount() {
    fetch('get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
                cartCount.setAttribute('data-count', data.count || 0);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Wishlist Functions
window.updateWishlistCount = function() {
    fetch('get-wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            const wishlistCount = document.getElementById('wishlistCount');
            if (wishlistCount) {
                if (data.count > 0) {
                    wishlistCount.textContent = data.count;
                    wishlistCount.setAttribute('data-count', data.count);
                    wishlistCount.style.display = 'flex';
                } else {
                    wishlistCount.textContent = '';
                    wishlistCount.setAttribute('data-count', '0');
                    wishlistCount.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error:', error));
};

window.openWishlistSidebar = function() {
    function doOpenWishlistSidebar() {
        const sidebar = document.getElementById('wishlistSidebar');
        const overlay = document.getElementById('wishlistSidebarOverlay');
        
        if (sidebar && overlay) {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Load wishlist content
            if (typeof window.loadWishlistSidebar === 'function') {
                window.loadWishlistSidebar();
            }
            return true;
        }
        return false;
    }
    
    // If DOM is still loading, wait for it
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!doOpenWishlistSidebar()) {
                // Retry with delays
                setTimeout(function() { if (!doOpenWishlistSidebar()) {
                    setTimeout(function() { if (!doOpenWishlistSidebar()) {
                        setTimeout(function() { doOpenWishlistSidebar(); }, 300);
                    }, 200);
                }, 100);
            }
        });
        return;
    }
    
    // Try to open immediately
    if (doOpenWishlistSidebar()) {
        return;
    }
    
    // If elements not found, wait a bit and try again (multiple retries)
    setTimeout(function() {
        if (doOpenWishlistSidebar()) {
            return;
        }
        
        // Retry again
        setTimeout(function() {
            if (doOpenWishlistSidebar()) {
                return;
            }
            
            // Final retry
            setTimeout(function() {
                doOpenWishlistSidebar();
            }, 300);
        }, 200);
    }, 100);
};

window.closeWishlistSidebar = function() {
    const sidebar = document.getElementById('wishlistSidebar');
    const overlay = document.getElementById('wishlistSidebarOverlay');
    if (sidebar) {
        sidebar.classList.remove('active');
    }
    if (overlay) {
        overlay.classList.remove('active');
    }
    document.body.style.overflow = '';
    return false;
};

window.loadWishlistSidebar = function() {
    const wishlistContent = document.getElementById('wishlistSidebarContent');
    if (!wishlistContent) return;
    
    const timestamp = new Date().getTime();
    const random = Math.random();
    const url = 'get-wishlist-sidebar.php?t=' + timestamp + '&r=' + random + '&_=' + Date.now();
    
    fetch(url, {
        method: 'GET',
        cache: 'no-store',
        credentials: 'same-origin',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        }
    })
    .then(response => response.text())
    .then(html => {
        if (wishlistContent) {
            wishlistContent.innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Error loading wishlist:', error);
    });
};

window.removeFromWishlist = function(productId) {
    var formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'remove');
    
    fetch('add-to-wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.updateWishlistCount();
            window.loadWishlistSidebar();
        } else {
            alert(data.message || 'Error removing product from wishlist');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error removing product from wishlist');
    });
};

window.clearWishlist = function() {
    fetch('clear-wishlist.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.updateWishlistCount();
            window.loadWishlistSidebar();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Wishlist Cleared!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } else {
            alert(data.message || 'Error clearing wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error clearing wishlist');
    });
};

// Global addToCart function - provides default behavior for all pages
// Pages can override this if they need custom behavior, but this ensures sidebar always opens
if (typeof window.addToCart === 'undefined') {
    window.addToCart = function(productId, quantity, event) {
        console.log('🛒 Global addToCart called for product:', productId);
        
        // Get quantity (default to 1 if not provided)
        const qty = quantity || 1;
        
        // Get event object
        const e = event || (typeof window.event !== 'undefined' ? window.event : null);
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        }
        
        // Find button
        let btn = null;
        if (e && e.target) {
            btn = e.target.closest('.btn-add-cart') || e.target.closest('button') || e.target;
        }
        if (!btn) {
            btn = document.querySelector('.btn-add-cart[data-product-id="' + productId + '"]') || 
                  document.querySelector('.btn-add-cart');
        }
        
        const originalText = btn ? btn.innerHTML : '';
        const originalDisabled = btn ? btn.disabled : false;
        
        // Disable button and show loading
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'ADDING...';
        }
        
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', qty);
        
        fetch('add-to-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
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
                
                // Reset button
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = originalDisabled;
                }
            } else {
                alert(data.message || 'Error adding product to cart');
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = originalDisabled;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding product to cart');
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = originalDisabled;
            }
        });
        
        return false;
    };
}

window.addToCartFromWishlist = function(productId) {
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
            // Update cart count
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            // Update cart sidebar
            const cartContent = document.getElementById('cartSidebarContent');
            if (cartContent && data.cart_html) {
                cartContent.innerHTML = data.cart_html;
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
            // Close wishlist sidebar
            if (typeof window.closeWishlistSidebar === 'function') {
                window.closeWishlistSidebar();
            }
        } else {
            alert(data.message || 'Error adding product to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
    });
};

</script>

<!-- LOGIN MODAL -->
<div id="loginModal" class="auth-modal-overlay">
    <div class="auth-modal">
        <span class="auth-modal-close" onclick="closeLoginModal()">&times;</span>
        <h2>LOGIN</h2>
        <form id="loginForm" onsubmit="handleLogin(event); return false;">
            <input type="email" name="email" placeholder="Email *" required>
            <input type="password" name="password" placeholder="Password *" required>
            <a href="forgot-password.php" class="forgot-password-link">Forgot your password?</a>
            <button type="submit" class="auth-submit-btn">Sign In</button>
        </form>
        <p class="auth-switch-text">New customer? <a href="#" onclick="switchToRegister()">Create your account</a></p>
    </div>
</div>

<!-- REGISTER MODAL -->
<div id="registerModal" class="auth-modal-overlay">
    <div class="auth-modal">
        <span class="auth-modal-close" onclick="closeRegisterModal()">&times;</span>
        <h2>REGISTER</h2>
        <form id="registerForm" onsubmit="handleRegister(event); return false;">
            <input type="text" name="first_name" placeholder="First Name *" required>
            <input type="text" name="last_name" placeholder="Last Name *" required>
            <input type="tel" name="mobile_no" placeholder="Mobile No *" maxlength="10" pattern="[0-9]{10}" title="Please enter exactly 10 digits" required>
            <input type="email" name="email" placeholder="Email *" required>
            <input type="password" name="password" placeholder="Password *" required>
            <input type="text" name="city" placeholder="City *" required>
            <input type="text" name="state" placeholder="State *" required>
            <input type="text" name="zipcode" placeholder="Zipcode *" required>
            <p class="privacy-text">Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <a href="#" class="privacy-link">privacy_policy</a>.</p>
            <button type="submit" class="auth-submit-btn">Register</button>
        </form>
        <p class="auth-switch-text">Already have an account? <a href="#" onclick="switchToLogin()">Login</a></p>
    </div>
</div>

<!-- SIMPLE BUTTON FIX - Ensures cart buttons always work -->
<script>
(function() {
    'use strict';
    console.log('🔧 SIMPLE BUTTON FIX: Initializing...');
    
    // Ensure removeCartProduct is ALWAYS available
    // Definition removed to prevent duplicates
    
    // Also handle close button on sidebar header (outside content area)
    function attachCloseButtonHandler() {
        var closeBtn = document.getElementById('cartSidebarCloseBtn');
        var sidebar = document.getElementById('cartSidebar');
        if (closeBtn && !closeBtn.hasAttribute('data-simple-listener')) {
            closeBtn.setAttribute('data-simple-listener', 'true');
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('❌ Close button clicked (sidebar header - direct listener)');
                try {
                    if (typeof window.closeCartSidebar === 'function') {
                        window.closeCartSidebar();
                    } else {
                        console.warn('⚠️ closeCartSidebar not found, closing manually');
                        var sidebar = document.getElementById('cartSidebar');
                        var overlay = document.getElementById('cartSidebarOverlay');
                        if (sidebar) {
                            sidebar.classList.remove('active');
                            console.log('✅ Sidebar closed manually');
                        }
                        if (overlay) {
                            overlay.classList.remove('active');
                            console.log('✅ Overlay closed manually');
                        }
                        document.body.style.overflow = '';
                    }
                } catch(err) {
                    console.error('❌ Error in close button handler:', err);
                    // Force close on error
                    var sidebar = document.getElementById('cartSidebar');
                    var overlay = document.getElementById('cartSidebarOverlay');
                    if (sidebar) sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
                return false;
            }, true); // Capture phase to catch early
        }
        // Also attach to sidebar header for clicks on X (more reliable)
        if (sidebar && !sidebar.hasAttribute('data-close-handler')) {
            sidebar.setAttribute('data-close-handler', 'true');
            sidebar.addEventListener('click', function(e) {
                var target = e.target;
                var isClose = target.classList.contains('cart-sidebar-close') || 
                             target.id === 'cartSidebarCloseBtn' || 
                             (target.textContent && (target.textContent.trim() === '×' || target.textContent.includes('×'))) ||
                             target.closest('.cart-sidebar-close') ||
                             target.closest('#cartSidebarCloseBtn');
                
                if (isClose) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    console.log('❌ Close clicked via sidebar header');
                    try {
                        if (typeof window.closeCartSidebar === 'function') {
                            window.closeCartSidebar();
                        } else {
                            console.warn('⚠️ closeCartSidebar not found, closing manually');
                            sidebar.classList.remove('active');
                            var overlay = document.getElementById('cartSidebarOverlay');
                            if (overlay) overlay.classList.remove('active');
                            document.body.style.overflow = '';
                        }
                    } catch(err) {
                        console.error('❌ Error closing sidebar:', err);
                        // Force close on error
                        sidebar.classList.remove('active');
                        var overlay = document.getElementById('cartSidebarOverlay');
                        if (overlay) overlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                    return false;
                }
            }, true); // Use capture phase to catch early
        }
    }
    
    attachCloseButtonHandler();
    setTimeout(attachCloseButtonHandler, 100);
    setTimeout(attachCloseButtonHandler, 500);
    
    // Also attach direct listeners to icon buttons as backup
    function attachIconButtonListeners() {
        var icons = ['orderNoteIcon', 'estimateIcon', 'couponIcon', 'giftCardIcon'];
        var functions = ['showOrderNoteModal', 'showEstimateShippingModal', 'showCouponModal', 'showGiftCardModal'];
        var names = ['Order Note', 'Estimate Shipping', 'Coupon', 'Gift Card'];
        var actions = ['orderNote', 'estimate', 'coupon', 'giftCard'];
        
        icons.forEach(function(iconId, index) {
            var btn = document.getElementById(iconId);
            if (btn && !btn.hasAttribute('data-icon-listener')) {
                btn.setAttribute('data-icon-listener', 'true');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    try {
                        const funcName = functions[index];
                        if (typeof window[funcName] === 'function') {
                            window[funcName]();
                            console.log('✅', names[index], 'modal opened');
                        } else {
                            console.error('❌', funcName, 'not found');
                            // If NOT found, don't stop propagation so inline onclick can work
                            return true;
                        }
                    } catch(err) {
                        console.error('❌ Error opening', names[index], 'modal:', err);
                    }
                    e.stopImmediatePropagation();
                    return false;
                }, true); // Capture phase
                console.log('✅ Direct listener attached to', iconId);
            }
        });
        
        // Also attach by data-action attribute
        document.querySelectorAll('.cart-action-icon[data-action]').forEach(function(btn) {
            var action = btn.getAttribute('data-action');
            if (action && !btn.hasAttribute('data-action-listener')) {
                btn.setAttribute('data-action-listener', 'true');
                var funcMap = {
                    'orderNote': 'showOrderNoteModal',
                    'estimate': 'showEstimateShippingModal',
                    'coupon': 'showCouponModal',
                    'giftCard': 'showGiftCardModal'
                };
                if (funcMap[action]) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        console.log('🎯 Icon button clicked by data-action:', action);
                        try {
                            if (typeof window[funcMap[action]] === 'function') {
                                window[funcMap[action]]();
                                console.log('✅ Modal opened for', action);
                            }
                        } catch(err) {
                            console.error('❌ Error:', err);
                        }
                        return false;
                    }, true);
                }
            }
        });
    }
    
    attachIconButtonListeners();
    setTimeout(attachIconButtonListeners, 100);
    setTimeout(attachIconButtonListeners, 500);
    
    var observer = new MutationObserver(function() {
        if (typeof window.setupCartSidebarEvents === 'function') {
            window.setupCartSidebarEvents();
        }
        setTimeout(attachCloseButtonHandler, 50);
        setTimeout(attachIconButtonListeners, 50);
    });
    var cartContent = document.getElementById('cartSidebarContent');
    if (cartContent) {
        observer.observe(cartContent, {childList: true, subtree: true});
    }
    console.log('✅ SIMPLE BUTTON FIX: Initialized');
    
    // ============================================
    // CART ACTION ICONS FUNCTIONALITY
    // ============================================
    
    // Modal HTML templates
    const modalStyles = `
        <style>
            .cart-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 100000;
                animation: fadeIn 0.3s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            .cart-modal {
                background: #fff;
                border-radius: 12px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease;
            }
            
            @keyframes slideUp {
                from {
                    transform: translateY(50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .cart-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #e0e0e0;
            }
            
            .cart-modal-title {
                font-size: 24px;
                font-weight: 700;
                color: #2b2b2b;
                margin: 0;
            }
            
            .cart-modal-close {
                background: none;
                border: none;
                font-size: 28px;
                color: #666;
                cursor: pointer;
                padding: 0;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
            }
            
            .cart-modal-close:hover {
                background: #f0f0f0;
                color: #2b2b2b;
            }
            
            .cart-modal-body {
                margin-bottom: 20px;
            }
            
            .cart-modal-input,
            .cart-modal-textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 15px;
                font-family: inherit;
                transition: all 0.3s ease;
                box-sizing: border-box;
            }
            
            .cart-modal-input:focus,
            .cart-modal-textarea:focus {
                outline: none;
                border-color: #2fc7b4;
                box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
            }
            
            .cart-modal-textarea {
                min-height: 120px;
                resize: vertical;
            }
            
            .cart-modal-label {
                display: block;
                font-weight: 600;
                color: #2b2b2b;
                margin-bottom: 8px;
                font-size: 14px;
            }
            
            .cart-modal-button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #2fc7b4 0%, #2fa76b 100%);
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 10px;
            }
            
            .cart-modal-button:hover {
                background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
            }
            
            .cart-modal-info {
                background: #e3f2fd;
                border-left: 4px solid #2196f3;
                padding: 12px;
                border-radius: 4px;
                margin-bottom: 15px;
                font-size: 14px;
                color: #0d47a1;
            }
        </style>
    `;
    
    // Add modal styles to document
    if (!document.getElementById('cart-modal-styles')) {
        const styleEl = document.createElement('div');
        styleEl.id = 'cart-modal-styles';
        styleEl.innerHTML = modalStyles;
        document.head.appendChild(styleEl);
    }
    
    // Function to create and show modal
    function showModal(title, content, onSubmit) {
        // Remove any existing modal
        const existingModal = document.querySelector('.cart-modal-overlay');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modal = document.createElement('div');
        modal.className = 'cart-modal-overlay';
        modal.innerHTML = `
            <div class="cart-modal">
                <div class="cart-modal-header">
                    <h2 class="cart-modal-title">${title}</h2>
                    <button class="cart-modal-close" onclick="this.closest('.cart-modal-overlay').remove()">&times;</button>
                </div>
                <div class="cart-modal-body">
                    ${content}
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        // Close on Escape key
        const escapeHandler = function(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
        
        // Attach submit handler if provided
        if (onSubmit) {
            const submitBtn = modal.querySelector('.cart-modal-button');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    onSubmit(modal);
                });
            }
        }
        
        return modal;
    }
    
    // 1. Order Note Modal
    window.showOrderNoteModal = function() {
        console.log('📝 Opening Order Note modal');
        const content = `
            <div class="cart-modal-info">
                💡 Add special instructions for your order. This will be included with your order details.
            </div>
            <label class="cart-modal-label">Order Note</label>
            <textarea class="cart-modal-textarea" id="orderNoteInput" placeholder="Enter any special instructions for your order..."></textarea>
            <button class="cart-modal-button">Save Note</button>
        `;
        
        showModal('📝 Add Order Note', content, function(modal) {
            const note = document.getElementById('orderNoteInput').value;
            if (note.trim()) {
                // Store in session storage
                sessionStorage.setItem('orderNote', note);
                alert('✅ Order note saved successfully!');
                modal.remove();
            } else {
                alert('⚠️ Please enter a note before saving.');
            }
        });
        
        // Pre-fill if note exists
        setTimeout(function() {
            const savedNote = sessionStorage.getItem('orderNote');
            if (savedNote) {
                document.getElementById('orderNoteInput').value = savedNote;
            }
        }, 100);
    };
    
    // 2. Estimate Shipping Modal
    window.showEstimateShippingModal = function() {
        console.log('🚚 Opening Estimate Shipping modal');
        const content = `
            <div class="cart-modal-info">
                📦 Enter your location to estimate shipping costs and delivery time.
            </div>
            <label class="cart-modal-label">Pincode</label>
            <input type="text" class="cart-modal-input" id="pincodeInput" placeholder="Enter 6-digit pincode" maxlength="6" pattern="[0-9]{6}">
            <button class="cart-modal-button">Estimate Shipping</button>
            <div id="shippingEstimateResult" style="margin-top: 15px;"></div>
        `;
        
        showModal('🚚 Estimate Shipping', content, function(modal) {
            const pincode = document.getElementById('pincodeInput').value;
            if (pincode && /^[0-9]{6}$/.test(pincode)) {
                // Simulate shipping estimation
                const resultDiv = document.getElementById('shippingEstimateResult');
                resultDiv.innerHTML = `
                    <div style="background: #d4edda; border: 1px solid #28a745; border-radius: 8px; padding: 15px; margin-top: 10px;">
                        <strong style="color: #155724;">✅ Shipping Available</strong>
                        <div style="margin-top: 10px; color: #155724;">
                            <div>📍 Pincode: ${pincode}</div>
                            <div>💰 Shipping Cost: ₹100 (Free above ₹750)</div>
                            <div>⏱️ Estimated Delivery: 3-5 business days</div>
                        </div>
                    </div>
                `;
            } else {
                alert('⚠️ Please enter a valid 6-digit pincode.');
            }
        });
    };
    
    // 3. Coupon Modal
    window.showCouponModal = function() {
        console.log('🎟️ Opening Coupon modal');
        const content = `
            <div class="cart-modal-info">
                🎉 Enter your coupon code to get a discount on your order.
            </div>
            <label class="cart-modal-label">Coupon Code</label>
            <input type="text" class="cart-modal-input" id="couponInput" placeholder="Enter coupon code" style="text-transform: uppercase;">
            <button class="cart-modal-button">Apply Coupon</button>
            <div id="couponResult" style="margin-top: 15px;"></div>
        `;
        
        showModal('🎟️ Apply Coupon', content, function(modal) {
            const coupon = document.getElementById('couponInput').value.trim().toUpperCase();
            const resultDiv = document.getElementById('couponResult');
            
            if (coupon) {
                // Simulate coupon validation (you can replace with actual API call)
                const validCoupons = {
                    'SAVE10': { discount: 10, type: 'percentage' },
                    'FLAT50': { discount: 50, type: 'fixed' },
                    'WELCOME': { discount: 15, type: 'percentage' }
                };
                
                if (validCoupons[coupon]) {
                    const couponData = validCoupons[coupon];
                    const discountText = couponData.type === 'percentage' 
                        ? `${couponData.discount}% OFF` 
                        : `₹${couponData.discount} OFF`;
                    
                    resultDiv.innerHTML = `
                        <div style="background: #d4edda; border: 1px solid #28a745; border-radius: 8px; padding: 15px;">
                            <strong style="color: #155724;">✅ Coupon Applied Successfully!</strong>
                            <div style="margin-top: 10px; color: #155724;">
                                <div>🎟️ Code: ${coupon}</div>
                                <div>💰 Discount: ${discountText}</div>
                            </div>
                        </div>
                    `;
                    sessionStorage.setItem('appliedCoupon', coupon);
                } else {
                    resultDiv.innerHTML = `
                        <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 15px;">
                            <strong style="color: #721c24;">❌ Invalid Coupon Code</strong>
                            <div style="margin-top: 5px; color: #721c24; font-size: 14px;">
                                Please check the code and try again.
                            </div>
                        </div>
                    `;
                }
            } else {
                alert('⚠️ Please enter a coupon code.');
            }
        });
    };
    
    // 4. Gift Card Modal
    window.showGiftCardModal = function() {
        console.log('🎁 Opening Gift Card modal');
        const content = `
            <div class="cart-modal-info">
                🎁 Redeem your gift card to get instant discount on your order.
            </div>
            <label class="cart-modal-label">Gift Card Number</label>
            <input type="text" class="cart-modal-input" id="giftCardInput" placeholder="Enter gift card number" maxlength="16">
            <label class="cart-modal-label" style="margin-top: 15px;">PIN</label>
            <input type="password" class="cart-modal-input" id="giftCardPinInput" placeholder="Enter PIN" maxlength="4">
            <button class="cart-modal-button">Apply Gift Card</button>
            <div id="giftCardResult" style="margin-top: 15px;"></div>
        `;
        
        showModal('🎁 Use Gift Card', content, function(modal) {
            const cardNumber = document.getElementById('giftCardInput').value.trim();
            const pin = document.getElementById('giftCardPinInput').value.trim();
            const resultDiv = document.getElementById('giftCardResult');
            
            if (cardNumber && pin) {
                // Simulate gift card validation (replace with actual API call)
                if (cardNumber.length >= 10 && pin.length === 4) {
                    resultDiv.innerHTML = `
                        <div style="background: #d4edda; border: 1px solid #28a745; border-radius: 8px; padding: 15px;">
                            <strong style="color: #155724;">✅ Gift Card Applied!</strong>
                            <div style="margin-top: 10px; color: #155724;">
                                <div>🎁 Card: ****${cardNumber.slice(-4)}</div>
                                <div>💰 Balance: ₹500.00</div>
                                <div>✨ Discount Applied: ₹500.00</div>
                            </div>
                        </div>
                    `;
                    sessionStorage.setItem('appliedGiftCard', cardNumber);
                } else {
                    resultDiv.innerHTML = `
                        <div style="background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; padding: 15px;">
                            <strong style="color: #721c24;">❌ Invalid Gift Card</strong>
                            <div style="margin-top: 5px; color: #721c24; font-size: 14px;">
                                Please check your card number and PIN.
                            </div>
                        </div>
                    `;
                }
            } else {
                alert('⚠️ Please enter both gift card number and PIN.');
            }
        });
    };
    
    // Attach event listeners to cart action icons
    function attachCartActionIconListeners() {
        const orderNoteIcon = document.getElementById('orderNoteIcon');
        const estimateIcon = document.getElementById('estimateIcon');
        const couponIcon = document.getElementById('couponIcon');
        const giftCardIcon = document.getElementById('giftCardIcon');
        
        if (orderNoteIcon && !orderNoteIcon.hasAttribute('data-listener-attached')) {
            orderNoteIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.showOrderNoteModal();
            });
            orderNoteIcon.setAttribute('data-listener-attached', 'true');
            console.log('✅ Order Note icon listener attached');
        }
        
        if (estimateIcon && !estimateIcon.hasAttribute('data-listener-attached')) {
            estimateIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.showEstimateShippingModal();
            });
            estimateIcon.setAttribute('data-listener-attached', 'true');
            console.log('✅ Estimate Shipping icon listener attached');
        }
        
        if (couponIcon && !couponIcon.hasAttribute('data-listener-attached')) {
            couponIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.showCouponModal();
            });
            couponIcon.setAttribute('data-listener-attached', 'true');
            console.log('✅ Coupon icon listener attached');
        }
        
        if (giftCardIcon && !giftCardIcon.hasAttribute('data-listener-attached')) {
            giftCardIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.showGiftCardModal();
            });
            giftCardIcon.setAttribute('data-listener-attached', 'true');
            console.log('✅ Gift Card icon listener attached');
        }
    }
    
    // Attach listeners on page load and when cart updates
    setTimeout(attachCartActionIconListeners, 500);
    
    // Re-attach when cart sidebar content changes
    if (typeof observer !== 'undefined' && observer) {
        const originalObserverCallback = observer.callback;
        observer.disconnect();
        observer = new MutationObserver(function() {
            if (typeof window.setupCartSidebarEvents === 'function') {
                window.setupCartSidebarEvents();
            }
            setTimeout(attachCloseButtonHandler, 50);
            setTimeout(attachIconButtonListeners, 50);
            // attachCartActionIconListeners is redundant with attachIconButtonListeners
        });
        var cartContent = document.getElementById('cartSidebarContent');
        if (cartContent) {
            observer.observe(cartContent, {childList: true, subtree: true});
        }
    }
    
    console.log('✅ Cart Action Icons functionality initialized');
    
    // ========== MODAL FUNCTIONS WITH Z-INDEX ENFORCEMENT ==========
    
    // Order Note Modal
    window.showOrderNoteModal = function() {
        console.log('📝 Opening Order Note Modal');
        const modal = document.getElementById('orderNoteModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
            modal.style.zIndex = '2000000'; // Force z-index
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeOrderNoteModal = function() {
        const modal = document.getElementById('orderNoteModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    window.saveOrderNote = function() {
        const noteText = document.getElementById('orderNoteText');
        if (noteText && noteText.value.trim()) {
            // Save to session or localStorage
            sessionStorage.setItem('orderNote', noteText.value);
            alert('✅ Order note saved!');
            window.closeOrderNoteModal();
        } else {
            alert('⚠️ Please enter a note');
        }
    };
    
    // Estimate Shipping Modal
    window.showEstimateShippingModal = function() {
        console.log('🚚 Opening Estimate Shipping Modal');
        const modal = document.getElementById('estimateModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
            modal.style.zIndex = '2000000'; // Force z-index
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeEstimateShippingModal = function() {
        const modal = document.getElementById('estimateModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    // Coupon Modal
    window.showCouponModal = function() {
        console.log('🎟️ Opening Coupon Modal');
        const modal = document.getElementById('couponModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
            modal.style.zIndex = '2000000'; // Force z-index
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeCouponModal = function() {
        const modal = document.getElementById('couponModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    window.applyCoupon = function() {
        const couponInput = document.getElementById('couponCodeInput');
        if (couponInput && couponInput.value.trim()) {
            // Apply coupon logic here
            alert('✅ Coupon "' + couponInput.value + '" applied!');
            window.closeCouponModal();
        } else {
            alert('⚠️ Please enter a coupon code');
        }
    };
    
    // Gift Card Modal
    window.showGiftCardModal = function() {
        console.log('🎁 Opening Gift Card Modal');
        const modal = document.getElementById('giftCardModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
            modal.style.zIndex = '2000000'; // Force z-index
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeGiftCardModal = function() {
        const modal = document.getElementById('giftCardModal');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    window.applyGiftCard = function() {
        const giftCardInput = document.getElementById('giftCardCodeInput');
        if (giftCardInput && giftCardInput.value.trim()) {
            // Apply gift card logic here
            alert('✅ Gift Card "' + giftCardInput.value + '" applied!');
            window.closeGiftCardModal();
        } else {
            alert('⚠️ Please enter a gift card code');
        }
    };
    
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('cart-feature-modal') || e.target.classList.contains('delete-confirm-modal')) {
            e.target.classList.remove('show');
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    console.log('✅ All modal functions initialized with z-index enforcement');
})();
</script>
