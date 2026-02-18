<?php
include "includes/db.php";

// Check for product ID first - before any output
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Check which columns exist in products table
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

$has_status = in_array('status', $products_columns);
$has_category_id = in_array('category_id', $products_columns);
$has_subcategory_id = in_array('subcategory_id', $products_columns);

// Build query - only join if columns exist
$query = "SELECT p.*";
if ($has_category_id) {
    $query .= ", c.category_name";
}
if ($has_subcategory_id) {
    $query .= ", s.subcategory_name";
}

$query .= " FROM products p";

if ($has_category_id) {
    $query .= " LEFT JOIN categories c ON p.category_id = c.id";
}
if ($has_subcategory_id) {
    $query .= " LEFT JOIN subcategories s ON p.subcategory_id = s.id";
}

$query .= " WHERE p.id = $product_id";
          
if ($has_status) {
    // Allow viewing active and out_of_stock products, but not inactive or NULL
    $query .= " AND ((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    // If query fails, try without joins
    $query = "SELECT * FROM products WHERE id = $product_id";
    if ($has_status) {
        // Allow viewing active and out_of_stock products, but not inactive or NULL
        $query .= " AND ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    }
    $result = mysqli_query($conn, $query);
}

$product = mysqli_fetch_assoc($result);

// Check if product exists - before any output
if (!$product) {
    header("Location: index.php");
    exit();
}

// Now include header after all redirects are done
include "includes/header.php";

// Get additional images
$additional_images = [];
if (!empty($product['images'])) {
    // Try to decode JSON
    $decoded = json_decode($product['images'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $additional_images = array_filter($decoded, function($img) {
            return !empty($img) && is_string($img);
        });
    } else {
        // If not JSON, try to split by comma or treat as single value
        $images_str = trim($product['images']);
        if (!empty($images_str)) {
            // Try splitting by comma
            $split_images = explode(',', $images_str);
            $additional_images = array_filter($split_images, function($img) {
                return !empty(trim($img));
            });
            // Trim whitespace
            $additional_images = array_map('trim', $additional_images);
        }
    }
}

// Get related products (same category) - only if category_id exists
$related_products = null;
if ($has_category_id && isset($product['category_id']) && !empty($product['category_id'])) {
    $related_query = "SELECT * FROM products 
                      WHERE category_id = '{$product['category_id']}' 
                      AND id != $product_id";
                      
    if ($has_status) {
        // Show active and out_of_stock products, but exclude inactive and NULL
        $related_query .= " AND ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    }
    
    $related_query .= " LIMIT 4";
    $related_products = mysqli_query($conn, $related_query);
}
?>

<style>
    .product-detail-page {
        min-height: 80vh;
        background: #f9f9f9;
        padding: 40px 0;
    }

    .product-container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .breadcrumbs {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }

    .breadcrumbs a {
        color: #2fc7b4;
        text-decoration: none;
    }

    .breadcrumbs a:hover {
        text-decoration: underline;
    }

    .product-detail {
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 40px;
    }

    .product-main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    .product-images {
        position: relative;
    }

    .product-main-image {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        background: #f5f5f5;
        position: relative;
        cursor: zoom-in;
        margin-bottom: 15px;
    }

    .product-main-image img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.3s ease;
    }

    .product-main-image:hover img {
        transform: scale(1.5);
    }


    .product-info {
        position: sticky;
        top: 170px;
        height: fit-content;
        z-index: 10;
    }

    .product-title {
        font-size: 32px;
        font-weight: 800;
        color: #2b2b2b;
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #ff6b35;
        color: #fff;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 18px;
        font-weight: 800;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        letter-spacing: 0.5px;
    }

    .product-pricing {
        margin-bottom: 25px;
    }

    .product-mrp {
        font-size: 16px;
        color: #999;
        text-decoration: line-through;
        display: block;
        margin-bottom: 5px;
    }

    .product-price {
        font-size: 28px;
        font-weight: 800;
        color: #dc3545;
    }

    .product-meta {
        margin-bottom: 25px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .product-meta-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .product-meta-item:last-child {
        border-bottom: none;
    }

    .product-meta-label {
        font-weight: 600;
        color: #666;
    }

    .product-meta-value {
        color: #2b2b2b;
    }

    .product-category-tag {
        display: inline-block;
        background: #2fc7b4;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 10px;
    }

    .product-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .quantity-selector button {
        background: #f0f0f0;
        border: none;
        padding: 12px 16px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .quantity-selector button:hover {
        background: #e0e0e0;
    }

    .quantity-selector input {
        width: 60px;
        border: none;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        padding: 12px 0;
    }

    .btn-add-cart {
        flex: 1;
        background: #2fc7b4;
        color: #fff;
        border: none;
        padding: 14px 24px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-add-cart:hover {
        background: #2fa76b;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
    }

    .btn-add-cart:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .product-secondary-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 2px solid transparent;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .btn-secondary:not(.wishlist-added) {
        /* Normal state - ensure it looks normal when not added */
        background: #f0f0f0;
        color: #333;
        border: 2px solid transparent;
    }

    .btn-secondary.wishlist-added {
        background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%) !important;
        color: #e91e63 !important;
        border: 2px solid #e91e63 !important;
        font-weight: 700 !important;
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1) !important;
    }

    .btn-secondary.wishlist-added:hover {
        background: linear-gradient(135deg, #ffe5e5 0%, #ffd5d5 100%) !important;
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(233, 30, 99, 0.4) !important;
    }

    .btn-secondary.wishlist-added i,
    .btn-secondary.wishlist-added .fa-heart,
    .btn-secondary.wishlist-added .fas {
        color: #e91e63 !important;
    }
    
    /* Ensure filled heart is visible */
    .btn-secondary.wishlist-added .fas.fa-heart {
        color: #e91e63 !important;
        display: inline-block !important;
    }

    @keyframes wishlistGlow {
        0%, 100% {
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1);
        }
        50% {
            box-shadow: 0 6px 25px rgba(233, 30, 99, 0.4), 0 0 0 6px rgba(233, 30, 99, 0.15);
        }
    }

    @keyframes wishlistPulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes heartBeat {
        0%, 100% {
            transform: scale(1);
        }
        25% {
            transform: scale(1.2);
        }
        50% {
            transform: scale(1.1);
        }
        75% {
            transform: scale(1.15);
        }
    }

    @keyframes heartBreak {
        0% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
        25% {
            transform: scale(1.1) rotate(-5deg);
        }
        50% {
            transform: scale(1.15) rotate(5deg);
            opacity: 0.9;
        }
        75% {
            transform: scale(1.1) rotate(-3deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    @keyframes lockShake {
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

    @keyframes lockPulse {
        0%, 100% {
            transform: scale(1);
            filter: drop-shadow(0 0 5px rgba(233, 30, 99, 0.5));
        }
        50% {
            transform: scale(1.15);
            filter: drop-shadow(0 0 15px rgba(233, 30, 99, 0.8));
        }
    }

    /* Login Required Popup Styling */
    .login-required-popup {
        border-radius: 24px !important;
        background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%) !important;
        border: 3px solid #e91e63 !important;
        box-shadow: 0 20px 60px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1) !important;
        padding: 40px 35px !important;
        animation: loginModalEntrance 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
    }

    .login-required-popup * {
        overflow-x: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .login-required-popup *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .login-required-content {
        overflow: hidden !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        max-width: 100% !important;
        word-wrap: break-word !important;
    }

    .login-required-content * {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }

    .login-required-title {
        font-size: 28px !important;
        font-weight: 800 !important;
        color: #e91e63 !important;
        margin-bottom: 20px !important;
        text-align: center !important;
    }

    .login-required-content {
        text-align: center !important;
        padding: 0 !important;
        overflow: hidden !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        max-width: 100% !important;
        word-wrap: break-word !important;
    }

    .login-required-content * {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }

    /* Hide scrollbars for login required popup container */
    .swal2-container .login-required-popup,
    .swal2-container .login-required-popup * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        overflow-x: hidden !important;
    }

    .swal2-container .login-required-popup *::-webkit-scrollbar,
    .swal2-container .login-required-popup::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .login-required-content div {
        animation: lockPulse 2s ease-in-out infinite !important;
    }

    .login-required-btn {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 14px 35px !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4) !important;
        transition: all 0.3s ease !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        pointer-events: auto !important;
        cursor: pointer !important;
        opacity: 1 !important;
    }

    .login-required-btn:disabled,
    .login-required-btn.swal2-confirm:disabled,
    .swal2-confirm.login-required-btn:disabled,
    .swal2-confirm.login-required-btn.swal2-loading {
        opacity: 1 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
    }
    
    /* Prevent SweetAlert from disabling the button */
    .swal2-container .swal2-confirm.login-required-btn {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    
    .swal2-container .swal2-confirm.login-required-btn.swal2-loading {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .login-required-btn:hover {
        background: linear-gradient(135deg, #c2185b 0%, #e91e63 100%) !important;
        transform: translateY(-3px) scale(1.05) !important;
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.5) !important;
    }

    /* Close button styling for login required popup */
    .login-close-btn,
    .swal2-close.login-close-btn,
    .swal2-container .swal2-close,
    .swal2-container .login-required-popup ~ .swal2-close,
    .swal2-container .swal2-close[class*="login"] {
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%) !important;
        border: 2px solid #e91e63 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        z-index: 10001 !important;
        box-shadow: 0 3px 10px rgba(233, 30, 99, 0.3) !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    
    /* Ensure close button is always clickable */
    .swal2-container .swal2-close:hover,
    .login-close-btn:hover,
    .swal2-container .swal2-close.login-close-btn:hover {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
        border-color: #c2185b !important;
        transform: scale(1.15) rotate(90deg) !important;
        box-shadow: 0 5px 15px rgba(233, 30, 99, 0.5) !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    
    /* Force close button to be clickable */
    .swal2-container .swal2-close * {
        pointer-events: none !important;
    }
    
    .swal2-container .swal2-close {
        pointer-events: auto !important;
    }

    .login-close-btn:hover {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
        border-color: #c2185b !important;
        transform: scale(1.15) rotate(90deg) !important;
        box-shadow: 0 5px 15px rgba(233, 30, 99, 0.5) !important;
    }

    .login-close-icon {
        color: #e91e63 !important;
        font-size: 22px !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        transition: all 0.3s ease !important;
        display: block !important;
        pointer-events: none !important;
    }

    .login-close-btn:hover .login-close-icon {
        color: #ffffff !important;
        transform: scale(1.1) !important;
    }

    @keyframes loginModalEntrance {
        0% {
            opacity: 0;
            transform: scale(0.8) rotate(-5deg);
        }
        50% {
            transform: scale(1.05) rotate(2deg);
        }
        100% {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
    }

    @keyframes wishlistModalEntrance {
        0% {
            opacity: 0;
            transform: scale(0.8) rotate(-5deg);
        }
        50% {
            transform: scale(1.05) rotate(2deg);
        }
        100% {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
    }

    .wishlist-success-popup {
        border-radius: 20px !important;
        box-shadow: 0 10px 40px rgba(220, 53, 69, 0.3) !important;
        border: 3px solid rgba(220, 53, 69, 0.2) !important;
        width: 500px !important;
        max-width: 90vw !important;
        padding: 40px 35px !important;
        overflow: hidden !important;
    }

    .wishlist-success-popup .swal2-html-container {
        overflow: hidden !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .wishlist-success-popup .swal2-content {
        overflow: hidden !important;
        max-height: none !important;
    }

    .wishlist-success-title {
        color: #dc3545 !important;
        font-weight: 800 !important;
        font-size: 24px !important;
        margin-bottom: 20px !important;
        padding: 0 !important;
    }

    .wishlist-success-content {
        color: #666 !important;
        overflow: hidden !important;
        max-height: none !important;
        word-wrap: break-word !important;
    }

    /* Hide scrollbars for wishlist modals */
    .wishlist-success-popup,
    .wishlist-success-popup * {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
    }

    .wishlist-success-popup *::-webkit-scrollbar,
    .wishlist-success-popup::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Ensure modal container doesn't scroll */
    .swal2-container .wishlist-success-popup {
        max-height: none !important;
        height: auto !important;
    }

    .wishlist-confirm-btn {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
        border: none !important;
        padding: 12px 30px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3) !important;
    }

    .wishlist-confirm-btn:hover {
        background: linear-gradient(135deg, #c2185b 0%, #e91e63 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.4) !important;
    }

    .wishlist-close-btn {
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%) !important;
        border: 3px solid #e91e63 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        cursor: pointer !important;
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        z-index: 10000 !important;
        box-shadow: 0 3px 12px rgba(233, 30, 99, 0.3) !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    .wishlist-close-btn:hover {
        background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%) !important;
        border-color: #c2185b !important;
        transform: scale(1.2) rotate(90deg) !important;
        box-shadow: 0 6px 20px rgba(233, 30, 99, 0.5) !important;
    }

    .wishlist-close-btn:hover .wishlist-close-icon {
        color: #fff !important;
        transform: scale(1.1) !important;
    }

    .wishlist-close-icon {
        color: #e91e63 !important;
        font-size: 26px !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        transition: all 0.3s ease !important;
        display: block !important;
        pointer-events: none !important;
    }

    /* Ensure close button is always visible and clickable */
    .swal2-close {
        opacity: 1 !important;
        pointer-events: auto !important;
        z-index: 10000 !important;
        cursor: pointer !important;
    }
    
    .swal2-close:hover {
        opacity: 0.8 !important;
    }
    
    /* Ensure close button works even with custom styling */
    .wishlist-close-btn.swal2-close {
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .shipping-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        background: rgba(47, 199, 180, 0.1);
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .shipping-info i {
        color: #2fc7b4;
        font-size: 20px;
    }

    .product-accordion {
        margin-top: 40px;
    }

    .accordion-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
        background: #fff;
    }

    .accordion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        background: #f5f5f5;
        cursor: pointer;
        transition: all 0.3s ease;
        user-select: none;
    }

    .accordion-header:hover {
        background: #eeeeee;
    }

    .accordion-header.active {
        background: #f0f0f0;
        border-bottom: 1px solid #e0e0e0;
    }

    .accordion-title {
        font-size: 16px;
        font-weight: 600;
        color: #2b2b2b;
        margin: 0;
    }

    .accordion-toggle {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #2b2b2b;
        color: #fff;
        border-radius: 4px;
        font-size: 18px;
        font-weight: bold;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .accordion-header:hover .accordion-toggle {
        background: #2fc7b4;
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        padding: 0 20px;
    }

    .accordion-content.active {
        max-height: 2000px;
        padding: 20px;
    }

    .accordion-content p {
        margin: 0;
        line-height: 1.8;
        color: #555;
    }

    .accordion-content a {
        color: #2fc7b4;
        text-decoration: none;
    }

    .accordion-content a:hover {
        text-decoration: underline;
    }

    .related-products {
        margin-top: 60px;
    }

    .section-title {
        font-size: 28px;
        font-weight: 800;
        color: #2b2b2b;
        margin-bottom: 30px;
        text-align: center;
    }

    .related-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
    }

    .related-product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .related-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .related-product-image {
        width: 100%;
        padding-top: 100%;
        position: relative;
        overflow: hidden;
        background: #f5f5f5;
    }

    .related-product-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-product-info {
        padding: 15px;
    }

    .related-product-name {
        font-size: 14px;
        font-weight: 600;
        color: #2b2b2b;
        margin-bottom: 10px;
    }

    .related-product-price {
        font-size: 18px;
        font-weight: 700;
        color: #2fa76b;
    }

    @media (max-width: 768px) {
        .product-main {
            grid-template-columns: 1fr;
        }
        .zoom-result {
            display: none !important;
        }
    }
</style>

<div class="product-detail-page">
    <div class="product-container">
        <div class="breadcrumbs">
            <a href="index.php">Home</a> 
            <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?> > <a href="#"><?= htmlspecialchars($product['category_name']) ?></a><?php endif; ?>
            <?php if (isset($product['subcategory_name']) && !empty($product['subcategory_name'])): ?> > <a href="#"><?= htmlspecialchars($product['subcategory_name']) ?></a><?php endif; ?>
            > <span><?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?></span>
        </div>

        <div class="product-detail">
            <div class="product-main">
                <div class="product-images">
                    <?php 
                    // Define image variables first
                    $main_image = $product['image'] ?? $product['product_image'] ?? '';
                    $main_image_path = !empty($main_image) ? 'uploads/products/' . $main_image : 'assets/images/beads.jpg';
                    if (!empty($main_image) && !file_exists($main_image_path)) {
                        $main_image_path = 'assets/images/beads.jpg';
                    }
                    ?>
                    <div class="product-main-image">
                        <?php 
                        $product_status = $product['status'] ?? 'active';
                        if ($product_status == 'out_of_stock'): ?>
                            <div class="product-badge" style="background: #dc3545;">Out of Stock</div>
                        <?php elseif (isset($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                            <div class="product-badge">-<?= $product['discount_percent'] ?>%</div>
                        <?php endif; ?>
                        <img id="mainProductImage" src="<?= $main_image_path ?>" alt="<?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?>" onerror="this.src='assets/images/beads.jpg'">
                    </div>
                </div>

                <div class="product-info">
                    <h1 class="product-title"><?= htmlspecialchars($product['name'] ?? $product['product_name'] ?? 'Product') ?></h1>

                    <div class="product-pricing">
                        <?php 
                        $product_mrp = $product['mrp'] ?? 0;
                        $product_price = $product['price'] ?? 0;
                        if ($product_mrp > $product_price && $product_mrp > 0): 
                        ?>
                            <span class="product-mrp">M.R.P (Incl. of all Taxes): ₹<?= number_format($product_mrp, 2) ?></span>
                        <?php endif; ?>
                        <div class="product-price">₹<?= number_format($product_price, 2) ?></div>
                    </div>

                    <div class="product-meta">
                        <?php if (isset($product['country_of_origin']) && !empty($product['country_of_origin'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Country of Origin:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['country_of_origin']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="product-meta-item">
                            <span class="product-meta-label">Availability:</span>
                            <span class="product-meta-value">
                                <?php 
                                $product_status = $product['status'] ?? 'active';
                                $product_stock = $product['stock'] ?? 0;
                                if ($product_status == 'out_of_stock') {
                                    echo '<span style="color: #dc3545; font-weight: 600;">Out of Stock</span>';
                                } elseif ($product_stock > 0) {
                                    echo $product_stock . ' in stock';
                                } else {
                                    echo '<span style="color: #dc3545; font-weight: 600;">Out of Stock</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <?php if (isset($product['sku']) && !empty($product['sku'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">SKU:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['sku']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Categories:</span>
                                <span class="product-meta-value">
                                    <span class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                                    <?php if (isset($product['subcategory_name']) && !empty($product['subcategory_name'])): ?>
                                        <span class="product-category-tag"><?= htmlspecialchars($product['subcategory_name']) ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($product['color']) && !empty($product['color'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Color:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['color']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($product['size']) && !empty($product['size'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Size:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['size']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($product['material']) && !empty($product['material'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Material:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['material']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($product['weight']) && !empty($product['weight'])): ?>
                            <div class="product-meta-item">
                                <span class="product-meta-label">Weight:</span>
                                <span class="product-meta-value"><?= htmlspecialchars($product['weight']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-actions">
                            <div class="quantity-selector">
                            <button type="button" onclick="window.decreaseQuantity()">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?? 0 ?>">
                            <button type="button" onclick="window.increaseQuantity()">+</button>
                        </div>
                        <?php 
                        $product_status = $product['status'] ?? 'active';
                        $product_stock = $product['stock'] ?? 0;
                        $isOutOfStock = ($product_status == 'out_of_stock' || $product_stock <= 0);
                        ?>
                        <button type="button" class="btn-add-cart" onclick="if(event){event.preventDefault();event.stopPropagation();event.stopImmediatePropagation();}window.addToCart(<?= $product['id'] ?>);return false;" <?= $isOutOfStock ? 'disabled' : '' ?>>
                            <?= $isOutOfStock ? 'OUT OF STOCK' : 'ADD TO CART' ?>
                        </button>
                    </div>

                    <div class="product-secondary-actions">
                        <button type="button" class="btn-secondary" onclick="window.addToWishlist(<?= $product['id'] ?>)">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                    </div>

                    <div class="shipping-info">
                        <i class="fas fa-truck"></i>
                        <span>Free Shipping In India On Order Of Rs 750/- Or Above.</span>
                    </div>

                    <div class="product-accordion">
                        <div class="accordion-item">
                            <div class="accordion-header" onclick="window.toggleAccordion('description')">
                                <h3 class="accordion-title">Description</h3>
                                <span class="accordion-toggle" id="description-toggle">+</span>
                            </div>
                            <div class="accordion-content" id="description-content">
                                <?php if (isset($product['description']) && !empty($product['description'])): ?>
                                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                                <?php else: ?>
                                    <p>No description available for this product.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <div class="accordion-header" onclick="window.toggleAccordion('ask')">
                                <h3 class="accordion-title">Ask a Question</h3>
                                <span class="accordion-toggle" id="ask-toggle">+</span>
                            </div>
                            <div class="accordion-content" id="ask-content">
                                <p>Have a question about this product? Please contact us at <a href="mailto:care@craftroyale.com">care@craftroyale.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($related_products && mysqli_num_rows($related_products) > 0): ?>
            <div class="related-products">
                <h2 class="section-title">You may also like</h2>
                <div class="related-products-grid">
                    <?php 
                    mysqli_data_seek($related_products, 0); // Reset pointer
                    while ($related = mysqli_fetch_assoc($related_products)): 
                        $related_image = $related['image'] ?? $related['product_image'] ?? '';
                        $relatedImage = !empty($related_image) ? 'uploads/products/' . $related_image : 'assets/images/beads.jpg';
                        if (!empty($related_image) && !file_exists($relatedImage)) {
                            $relatedImage = 'assets/images/beads.jpg';
                        }
                    ?>
                        <div class="related-product-card" onclick="window.location.href='product.php?id=<?= $related['id'] ?>'">
                            <div class="related-product-image">
                                <img src="<?= $relatedImage ?>" alt="<?= htmlspecialchars($related['name'] ?? $related['product_name'] ?? 'Product') ?>" onerror="this.src='assets/images/beads.jpg'">
                            </div>
                            <div class="related-product-info">
                                <div class="related-product-name"><?= htmlspecialchars($related['name'] ?? $related['product_name'] ?? 'Product') ?></div>
                                <div class="related-product-price">₹<?= number_format($related['price'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
// ============================================
// CRITICAL: Define ALL functions FIRST before any other code
// ============================================
console.log('Product page script loading...');

// Define toggleAccordion function FIRST
if (typeof window.toggleAccordion === 'undefined') {
    window.toggleAccordion = function(section) {
        // Get the header from the event or find it
        let header = null;
        if (typeof event !== 'undefined' && event && event.currentTarget) {
            header = event.currentTarget;
        } else if (typeof event !== 'undefined' && event && event.target) {
            header = event.target.closest('.accordion-header');
        } else {
            // Fallback: find by section
            const headers = document.querySelectorAll('.accordion-header');
            headers.forEach(h => {
                if (h.getAttribute('onclick') && h.getAttribute('onclick').includes(section)) {
                    header = h;
                }
            });
        }
        
        if (!header) {
            console.error('Accordion header not found for section:', section);
            return;
        }
        
        const content = document.getElementById(section + '-content');
        const toggle = document.getElementById(section + '-toggle');
        
        if (!content || !toggle) {
            console.error('Content or toggle not found for section:', section);
            return;
        }
        
        // Toggle active class
        const isActive = content.classList.contains('active');
        
        if (isActive) {
            // Collapse
            content.classList.remove('active');
            header.classList.remove('active');
            toggle.textContent = '+';
        } else {
            // Expand
            content.classList.add('active');
            header.classList.add('active');
            toggle.textContent = '−';
        }
    };
}

// Define addToCart function - MUST be defined immediately
if (typeof window.addToCart === 'undefined') {
    window.addToCart = function(productId) {
        console.log('🛒 addToCart called with productId:', productId);
        
        // Get the event object and prevent default
        const e = typeof event !== 'undefined' ? event : window.event;
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('✅ Event prevented and stopped');
        }
        
        if (!productId) {
            console.error('❌ Product ID is required');
            alert('Error: Product ID is missing');
            return false;
        }
        const quantityInput = document.getElementById('quantity');
        if (!quantityInput) {
            console.error('❌ Quantity input not found');
            return false;
        }
        const quantity = quantityInput.value;
        
        // Get button from event or find it
        let btn = null;
        if (e && e.target) {
            btn = e.target.closest('.btn-add-cart') || e.target;
        }
        if (!btn) {
            btn = document.querySelector('.btn-add-cart');
        }
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
        formData.append('quantity', quantity);
        
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
                
                // Show success message
                btn.innerHTML = '✓ ADDED TO CART';
                btn.style.background = '#28a745';
                
                // Open the sidebar IMMEDIATELY after adding to cart
                console.log('🛒 Opening cart sidebar after adding product...');
                
                // Open sidebar immediately - try multiple methods
                function openSidebarNow() {
                    // Wait for DOM to be ready if needed
                    function tryOpen() {
                        const sidebar = document.getElementById('cartSidebar');
                        const overlay = document.getElementById('cartSidebarOverlay');
                        const cartContent = document.getElementById('cartSidebarContent');
                        
                        if (!sidebar || !overlay) {
                            console.warn('⚠️ Sidebar elements not found yet, retrying...');
                            return false;
                        }
                        
                        console.log('✅ Sidebar elements found!', {
                            sidebar: !!sidebar,
                            overlay: !!overlay,
                            cartContent: !!cartContent
                        });
                        
                        return { sidebar, overlay, cartContent };
                    }
                    
                    // Try immediately
                    let elements = tryOpen();
                    if (!elements) {
                        // Retry after short delay
                        setTimeout(() => {
                            elements = tryOpen();
                            if (!elements) {
                                console.error('❌ Sidebar elements not found after retry!');
                                alert('Cart sidebar not available. Please refresh the page.');
                                return false;
                            }
                            doOpen(elements);
                        }, 100);
                        return false;
                    }
                    
                    return doOpen(elements);
                }
                
                function doOpen(elements) {
                    const { sidebar, overlay, cartContent } = elements;
                    
                    // Open sidebar immediately
                    console.log('✅ Opening sidebar directly...');
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    // Load content from get-cart-sidebar.php
                    if (cartContent) {
                        cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
                        
                        // Direct fetch from get-cart-sidebar.php
                        const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                        const url = (basePath === '/' ? '' : basePath) + 'get-cart-sidebar.php?t=' + Date.now() + '&r=' + Math.random();
                        
                        console.log('📡 Fetching cart from:', url);
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
                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }
                            return response.text();
                        })
                        .then(html => {
                            if (html && html.trim()) {
                                cartContent.innerHTML = html;
                                console.log('✅ Cart sidebar content loaded successfully');
                            } else {
                                cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
                            }
                        })
                        .catch(err => {
                            console.error('❌ Error loading cart:', err);
                            cartContent.innerHTML = '<div style="text-align:center;padding:20px;color:#dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading cart</p></div>';
                        });
                    }
                    
                    return true;
                }
                
                // Small delay to ensure cart is updated in session, then open sidebar
                setTimeout(function() {
                    // Try to use openCartSidebar if available
                    if (typeof window.openCartSidebar === 'function') {
                        console.log('🔄 Calling window.openCartSidebar()...');
                        try {
                            window.openCartSidebar();
                        } catch(err) {
                            console.error('❌ Error calling openCartSidebar:', err);
                            // Fallback to direct method
                            openSidebarNow();
                        }
                    } else {
                        // Fallback: open sidebar directly
                        console.log('⚠️ openCartSidebar not found, using direct method...');
                        openSidebarNow();
                    }
                }, 100); // Small delay to ensure cart is updated
                
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
}

// Define addToWishlist function - MUST be defined immediately
if (typeof window.addToWishlist === 'undefined') {
    window.addToWishlist = function(productId) {
        console.log('addToWishlist called with productId:', productId);
        
        // Check if user is logged in first
        console.log('Checking login status before wishlist action...');
        fetch('check-login.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(loginData => {
                console.log('Login check result:', loginData);
                if (loginData.status !== 'logged_in') {
                    console.log('User not logged in, showing login popup');
                // User is not logged in - show attractive login message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: false,
                        title: '🔒 Login Required',
                        html: '<div style="font-size: 80px; margin: 20px 0; animation: lockShake 0.5s ease-in-out; line-height: 1; overflow: hidden;">🔒</div><p style="font-size: 18px; color: #666; margin: 15px 0; padding: 0; word-wrap: break-word; overflow: hidden; white-space: normal;">✨ Please login to add products to your wishlist ✨</p><p style="font-size: 14px; color: #999; margin-top: 10px; padding: 0; word-wrap: break-word; overflow: hidden; white-space: normal;">Join us to save your favorite products! 💖</p>',
                        showConfirmButton: true,
                        confirmButtonText: '🔑 Login Now',
                        confirmButtonColor: '#e91e63',
                        showCloseButton: true,
                        closeButtonHtml: '<span class="login-close-icon">✕</span>',
                        allowEnterKey: true,
                        background: 'linear-gradient(135deg, #ffffff 0%, #fff5f5 100%)',
                        customClass: {
                            popup: 'login-required-popup',
                            title: 'login-required-title',
                            htmlContainer: 'login-required-content',
                            confirmButton: 'login-required-btn',
                            closeButton: 'login-close-btn'
                        },
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                        didOpen: () => {
                            // Ensure close button works properly - multiple attempts with direct DOM manipulation
                            const setupCloseButton = () => {
                                const closeBtn = document.querySelector('.login-close-btn, .swal2-close');
                                if (closeBtn) {
                                    // Force enable all properties
                                    closeBtn.style.pointerEvents = 'auto';
                                    closeBtn.style.cursor = 'pointer';
                                    closeBtn.style.zIndex = '10001';
                                    closeBtn.style.opacity = '1';
                                    closeBtn.disabled = false;
                                    closeBtn.removeAttribute('disabled');
                                    closeBtn.setAttribute('aria-label', 'Close');
                                    
                                    // Remove all event listeners by cloning
                                    const newCloseBtn = closeBtn.cloneNode(true);
                                    if (closeBtn.parentNode) {
                                        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                                    }
                                    
                                    // Add direct click handler - highest priority
                                    newCloseBtn.onclick = function(e) {
                                        if (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                        }
                                        try {
                                            Swal.close();
                                        } catch(err) {
                                            // Fallback: remove container directly
                                            const container = document.querySelector('.swal2-container');
                                            if (container) {
                                                container.remove();
                                            }
                                        }
                                        return false;
                                    };
                                    
                                    // Add event listener with capture phase
                                    newCloseBtn.addEventListener('click', function(e) {
                                        if (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                        }
                                        try {
                                            Swal.close();
                                        } catch(err) {
                                            const container = document.querySelector('.swal2-container');
                                            if (container) container.remove();
                                        }
                                    }, true); // Capture phase
                                    
                                    newCloseBtn.addEventListener('mousedown', function(e) {
                                        if (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                        }
                                        try {
                                            Swal.close();
                                        } catch(err) {
                                            const container = document.querySelector('.swal2-container');
                                            if (container) container.remove();
                                        }
                                    }, true);
                                    
                                    // Also handle icon clicks
                                    const icon = newCloseBtn.querySelector('.login-close-icon');
                                    if (icon) {
                                        icon.style.pointerEvents = 'auto';
                                        icon.style.cursor = 'pointer';
                                        icon.onclick = function(e) {
                                            if (e) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                            }
                                            try {
                                                Swal.close();
                                            } catch(err) {
                                                const container = document.querySelector('.swal2-container');
                                                if (container) container.remove();
                                            }
                                        };
                                    }
                                    
                                    newCloseBtn.style.cursor = 'pointer';
                                    newCloseBtn.style.pointerEvents = 'auto';
                                    newCloseBtn.style.zIndex = '10001';
                                }
                            };
                            
                            // Setup close button - reduced to prevent freezing
                            setTimeout(setupCloseButton, 100);
                            setTimeout(setupCloseButton, 300);
                            
                            // Ensure login button is always clickable - setup once, no aggressive monitoring
                            const setupLoginButton = () => {
                                const loginBtn = document.querySelector('.login-required-btn, .swal2-confirm');
                                if (loginBtn) {
                                    // Force enable
                                    loginBtn.disabled = false;
                                    loginBtn.removeAttribute('disabled');
                                    loginBtn.style.pointerEvents = 'auto';
                                    loginBtn.style.cursor = 'pointer';
                                    loginBtn.style.opacity = '1';
                                    loginBtn.classList.remove('swal2-loading', 'swal2-disabled');
                                }
                            };
                            
                            // Setup immediately - no aggressive interval monitoring
                            setupLoginButton();
                            setTimeout(setupLoginButton, 100);
                            
                            // No interval to store - removed aggressive monitoring
                            
                            // Listen for custom login success event (faster than polling)
                            const loginSuccessHandler = (event) => {
                                console.log('userLoggedIn event received, closing popup...');
                                
                                // CRITICAL: Clear all intervals immediately
                                if (checkLoginInterval) {
                                    clearInterval(checkLoginInterval);
                                    checkLoginInterval = null;
                                }
                                
                                // CRITICAL: Remove focus from ALL hidden inputs and modals to prevent freezing
                                const allHiddenInputs = document.querySelectorAll('.auth-modal-overlay input, .swal2-container input, #loginModal input, #registerModal input');
                                allHiddenInputs.forEach(input => {
                                    if (input === document.activeElement) {
                                        input.blur();
                                    }
                                });
                                
                                // Remove aria-hidden from login modal to prevent focus trap
                                const loginModal = document.getElementById('loginModal');
                                if (loginModal) {
                                    loginModal.removeAttribute('aria-hidden');
                                    const modalInputs = loginModal.querySelectorAll('input');
                                    modalInputs.forEach(input => {
                                        input.removeAttribute('aria-hidden');
                                        if (input === document.activeElement) {
                                            input.blur();
                                        }
                                    });
                                }
                                
                                // User logged in, close the popup immediately
                                try {
                                    Swal.close();
                                    // Also remove container directly as fallback
                                    setTimeout(() => {
                                        const container = document.querySelector('.swal2-container');
                                        if (container) {
                                            container.remove();
                                        }
                                        
                                        // CRITICAL: Force remove focus from any remaining hidden elements
                                        const stillFocused = document.activeElement;
                                        if (stillFocused && (stillFocused.closest('.auth-modal-overlay') || stillFocused.closest('.swal2-container'))) {
                                            stillFocused.blur();
                                            document.body.focus();
                                        }
                                        
                                        // Ensure login modal is fully closed and not retaining focus
                                        if (loginModal && loginModal.classList.contains('active')) {
                                            loginModal.classList.remove('active');
                                        }
                                        
                                        // Force body to have focus
                                        if (!document.activeElement || document.activeElement === document.body) {
                                            document.body.focus();
                                        }
                                    }, 150);
                                } catch(err) {
                                    console.error('Error closing popup:', err);
                                    // Fallback: remove container directly
                                    const container = document.querySelector('.swal2-container');
                                    if (container) {
                                        container.remove();
                                    }
                                }
                            };
                            
                            // Add event listener with capture phase for better reliability
                            document.addEventListener('userLoggedIn', loginSuccessHandler, true);
                            
                            // Check login status periodically (less aggressive to prevent freezing)
                            let checkLoginInterval = null;
                            let checkCount = 0;
                            const maxChecks = 10; // Stop after 20 seconds (10 checks * 2000ms)
                            
                            checkLoginInterval = setInterval(() => {
                                checkCount++;
                                
                                // Stop if popup is gone or max checks reached
                                const popupExists = document.querySelector('.login-required-popup, .swal2-container');
                                if (!popupExists || checkCount >= maxChecks) {
                                    if (checkLoginInterval) {
                                        clearInterval(checkLoginInterval);
                                        checkLoginInterval = null;
                                    }
                                    return;
                                }
                                
                                fetch('check-login.php')
                                    .then(response => response.json())
                                    .then(loginData => {
                                        if (loginData.status === 'logged_in') {
                                            console.log('Login detected via interval check, closing popup...');
                                            
                                            // CRITICAL: Clear interval immediately
                                            if (checkLoginInterval) {
                                                clearInterval(checkLoginInterval);
                                                checkLoginInterval = null;
                                            }
                                            
                                            // Remove focus from hidden inputs
                                            const hiddenInputs = document.querySelectorAll('.auth-modal-overlay input, .swal2-container input');
                                            hiddenInputs.forEach(input => {
                                                if (input === document.activeElement) {
                                                    input.blur();
                                                }
                                            });
                                            
                                            try {
                                                Swal.close();
                                                // Remove container as fallback
                                                setTimeout(() => {
                                                    const container = document.querySelector('.swal2-container');
                                                    if (container) {
                                                        container.remove();
                                                    }
                                                    // Remove focus from any remaining hidden elements
                                                    if (document.activeElement && document.activeElement.closest('.auth-modal-overlay')) {
                                                        document.activeElement.blur();
                                                        document.body.focus();
                                                    }
                                                }, 100);
                                            } catch(err) {
                                                console.error('Error closing popup:', err);
                                                const container = document.querySelector('.swal2-container');
                                                if (container) {
                                                    container.remove();
                                                }
                                            }
                                            // Remove event listener
                                            document.removeEventListener('userLoggedIn', loginSuccessHandler, true);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error checking login:', error);
                                    });
                            }, 2000); // Check every 2 seconds (much less aggressive)
                            
                            // Store references for cleanup - store interval ID on popup element
                            const loginPopupForInterval = document.querySelector('.login-required-popup');
                            if (loginPopupForInterval && checkLoginInterval) {
                                loginPopupForInterval.setAttribute('data-check-interval-id', checkLoginInterval);
                                loginPopupForInterval._checkLoginInterval = checkLoginInterval;
                                loginPopupForInterval._loginHandler = loginSuccessHandler;
                            }
                        },
                        willClose: () => {
                            // Clean up intervals and event listener when popup closes
                            const loginPopupForCleanup = document.querySelector('.login-required-popup');
                            if (loginPopupForCleanup) {
                                // Clear the checkLoginInterval using stored reference
                                if (loginPopupForCleanup._checkLoginInterval) {
                                    clearInterval(loginPopupForCleanup._checkLoginInterval);
                                    loginPopupForCleanup._checkLoginInterval = null;
                                }
                                
                                // Also try to clear using attribute
                                const intervalId = loginPopupForCleanup.getAttribute('data-check-interval-id');
                                if (intervalId) {
                                    clearInterval(parseInt(intervalId));
                                }
                                
                                // Remove event listener
                                if (loginPopupForCleanup._loginHandler) {
                                    document.removeEventListener('userLoggedIn', loginPopupForCleanup._loginHandler, true);
                                }
                            }
                            
                            // Remove focus from any hidden inputs to prevent freezing
                            const hiddenInputs = document.querySelectorAll('.auth-modal-overlay input, .swal2-container input');
                            hiddenInputs.forEach(input => {
                                if (input === document.activeElement) {
                                    input.blur();
                                }
                            });
                            
                            // Ensure body has focus - CRITICAL to prevent freezing
                            if (document.activeElement && document.activeElement.closest('.auth-modal-overlay, .swal2-container')) {
                                document.activeElement.blur();
                                document.body.focus();
                            }
                            
                            // Also remove any remaining intervals by checking all possible references
                            const allPopups = document.querySelectorAll('.login-required-popup, .swal2-container');
                            allPopups.forEach(popup => {
                                if (popup._checkLoginInterval) {
                                    clearInterval(popup._checkLoginInterval);
                                    popup._checkLoginInterval = null;
                                }
                            });
                        }
                    }).then((result) => {
                        // Reset button state immediately
                        setTimeout(() => {
                            const loginBtn = document.querySelector('.login-required-btn, .swal2-confirm');
                            if (loginBtn) {
                                loginBtn.disabled = false;
                                loginBtn.removeAttribute('disabled');
                                loginBtn.style.pointerEvents = 'auto';
                                loginBtn.style.cursor = 'pointer';
                                loginBtn.style.opacity = '1';
                                loginBtn.classList.remove('swal2-loading', 'swal2-disabled');
                            }
                        }, 10);
                        
                        if (result.isConfirmed) {
                            // Close SweetAlert first
                            Swal.close();
                            // Open login modal if available, otherwise redirect to login page
                            setTimeout(() => {
                                if (typeof openLogin === 'function') {
                                    openLogin();
                                    
                                    // Monitor login modal for closure and check login status (less aggressive)
                                    let checkLoginAfterModalClose = null;
                                    let modalCheckCount = 0;
                                    const maxModalChecks = 10; // Stop after 10 seconds (10 checks * 1000ms)
                                    
                                    checkLoginAfterModalClose = setInterval(() => {
                                        modalCheckCount++;
                                        
                                        // Stop if max checks reached
                                        if (modalCheckCount >= maxModalChecks) {
                                            if (checkLoginAfterModalClose) {
                                                clearInterval(checkLoginAfterModalClose);
                                                checkLoginAfterModalClose = null;
                                            }
                                            return;
                                        }
                                        
                                        const loginModal = document.getElementById('loginModal');
                                        if (loginModal && !loginModal.classList.contains('active')) {
                                            // Modal closed, check if user logged in
                                            fetch('check-login.php')
                                                .then(response => response.json())
                                                .then(loginData => {
                                                    if (loginData.status === 'logged_in') {
                                                        if (checkLoginAfterModalClose) {
                                                            clearInterval(checkLoginAfterModalClose);
                                                            checkLoginAfterModalClose = null;
                                                        }
                                                        
                                                        // Remove focus from hidden inputs
                                                        const hiddenInputs = document.querySelectorAll('.auth-modal-overlay input, .swal2-container input');
                                                        hiddenInputs.forEach(input => {
                                                            if (input === document.activeElement) {
                                                                input.blur();
                                                            }
                                                        });
                                                        
                                                        // Close the login required popup if still open
                                                        const loginPopup = document.querySelector('.swal2-container');
                                                        if (loginPopup) {
                                                            Swal.close();
                                                            setTimeout(() => {
                                                                if (loginPopup.parentNode) {
                                                                    loginPopup.remove();
                                                                }
                                                                // Ensure focus is removed from hidden elements
                                                                if (document.activeElement && document.activeElement.closest('.auth-modal-overlay')) {
                                                                    document.activeElement.blur();
                                                                    document.body.focus();
                                                                }
                                                            }, 100);
                                                        }
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error checking login after modal close:', error);
                                                });
                                        }
                                    }, 1000); // Check every 1 second (much less aggressive)
                                } else if (typeof window.openLogin === 'function') {
                                    window.openLogin();
                                } else {
                                    window.location.href = 'account.php';
                                }
                            }, 300);
                        }
                    });
                } else {
                    alert('Please login to add products to your wishlist.');
                }
                return;
            }
            
            // User is logged in, proceed with wishlist action
            console.log('User is logged in, proceeding with wishlist action for productId:', productId);
            if (!productId) {
                console.error('Product ID is required');
                alert('Error: Product ID is missing');
                return;
            }
            
            // Get button from event or find it - ensure we get the correct button
            let btn = null;
            if (typeof event !== 'undefined' && event && event.target) {
                btn = event.target.closest('.btn-secondary') || event.target.closest('button');
            }
            if (!btn) {
                // Find button by onclick attribute or by class
                btn = document.querySelector('.btn-secondary[onclick*="addToWishlist"]') || 
                      document.querySelector('button.btn-secondary') ||
                      document.querySelector('.btn-secondary');
            }
            
            if (!btn) {
                console.error('Wishlist button not found');
                return;
            }
            
            console.log('Wishlist button found:', btn);
            
            // Check if button already has wishlist-added class OR check server status
            const isAlreadyAdded = btn.classList.contains('wishlist-added');
            const action = isAlreadyAdded ? 'remove' : 'add';
            
            console.log('Wishlist action:', action, 'isAlreadyAdded (from class):', isAlreadyAdded);
            
            // If button doesn't have the class, check server to be sure
            if (!isAlreadyAdded && action === 'add') {
                // Quick check - if we're trying to add, verify it's not already there
                // This will be handled by the server response
            }
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = isAlreadyAdded ? '<i class="far fa-heart"></i> Removing...' : '<i class="far fa-heart"></i> Adding...';
            }
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', action);
            
            console.log('Sending wishlist request for productId:', productId, 'action:', action);
            
            fetch('add-to-wishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('Expected JSON but got:', text.substring(0, 200));
                        throw new Error('Invalid JSON response');
                    });
                }
            })
            .then(data => {
                console.log('Wishlist response received:', data);
                console.log('Response success:', data.success, 'Action:', action);
                
                if (data && data.success === true) {
                    console.log('Wishlist update successful, updating UI...');
                    
                    // Update wishlist count in header
                    if (typeof window.updateWishlistCount === 'function') {
                        window.updateWishlistCount();
                    } else {
                        const wishlistCount = document.getElementById('wishlistCount');
                        if (wishlistCount) {
                            if (data.wishlist_count > 0) {
                                wishlistCount.textContent = data.wishlist_count;
                                wishlistCount.setAttribute('data-count', data.wishlist_count);
                                wishlistCount.style.display = 'flex';
                            } else {
                                wishlistCount.textContent = '';
                                wishlistCount.setAttribute('data-count', '0');
                                wishlistCount.style.display = 'none';
                            }
                        }
                    }
                    
                    // CRITICAL: Update button based on action - this must happen
                    if (btn) {
                        console.log('Updating button, action:', action, 'btn element:', btn);
                        
                        if (action === 'add') {
                            // Add to wishlist - highlight button with attractive animation
                            console.log('HIGHLIGHTING BUTTON - Adding to wishlist');
                            
                            // Force apply all styles
                            btn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
                            btn.style.setProperty('color', '#e91e63', 'important');
                            btn.style.setProperty('background', 'linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%)', 'important');
                            btn.style.setProperty('border', '2px solid #e91e63', 'important');
                            btn.style.setProperty('font-weight', '700', 'important');
                            btn.style.setProperty('box-shadow', '0 6px 20px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1)', 'important');
                            btn.style.setProperty('animation', 'wishlistPulse 0.6s ease-out, wishlistGlow 2s ease-in-out infinite', 'important');
                            btn.style.setProperty('transform', 'scale(1.05)', 'important');
                            btn.style.setProperty('transition', 'all 0.3s ease', 'important');
                            
                            // Add the class
                            btn.classList.add('wishlist-added');
                            
                            // Add a subtle bounce effect
                            setTimeout(() => {
                                btn.style.setProperty('transform', 'scale(1)', 'important');
                            }, 600);
                            
                            console.log('Button highlighted - styles applied, class added:', btn.classList.contains('wishlist-added'));
                            console.log('Button computed styles:', {
                                color: window.getComputedStyle(btn).color,
                                background: window.getComputedStyle(btn).background,
                                border: window.getComputedStyle(btn).border
                            });
                        } else {
                            // Remove from wishlist - reset button to normal state
                            console.log('RESETTING BUTTON - Removing from wishlist');
                            btn.innerHTML = '<i class="far fa-heart"></i> Add to Wishlist';
                            
                            // Clear all inline styles to return to default CSS
                            btn.style.removeProperty('color');
                            btn.style.removeProperty('background');
                            btn.style.removeProperty('border');
                            btn.style.removeProperty('font-weight');
                            btn.style.removeProperty('box-shadow');
                            btn.style.removeProperty('animation');
                            btn.style.removeProperty('transform');
                            btn.style.setProperty('transition', 'all 0.3s ease', 'important');
                            
                            // Remove the wishlist-added class
                            btn.classList.remove('wishlist-added');
                            
                            console.log('Button reset to normal - class removed:', !btn.classList.contains('wishlist-added'));
                        }
                        
                        btn.disabled = false;
                        console.log('Wishlist button update completed');
                    } else {
                        console.error('Button element not found for update!');
                    }
                } else {
                    // Check if product is already in wishlist - if so, highlight button anyway
                    if (data && data.message && data.message.toLowerCase().includes('already in wishlist')) {
                        console.log('Product already in wishlist, highlighting button anyway');
                        if (btn) {
                            // Highlight button since product IS in wishlist
                            btn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
                            btn.style.setProperty('color', '#e91e63', 'important');
                            btn.style.setProperty('background', 'linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%)', 'important');
                            btn.style.setProperty('border', '2px solid #e91e63', 'important');
                            btn.style.setProperty('font-weight', '700', 'important');
                            btn.style.setProperty('box-shadow', '0 6px 20px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1)', 'important');
                            btn.style.setProperty('animation', 'wishlistGlow 2s ease-in-out infinite', 'important');
                            btn.style.setProperty('transition', 'all 0.3s ease', 'important');
                            btn.classList.add('wishlist-added');
                            btn.disabled = false;
                            console.log('Button highlighted - product already in wishlist');
                        }
                        // Don't show alert for "already in wishlist" - just highlight
                    } else {
                        console.error('Wishlist update failed:', data ? data.message : 'Unknown error');
                        if (btn) {
                            btn.innerHTML = isAlreadyAdded ? '<i class="fas fa-heart"></i> Added to Wishlist' : '<i class="far fa-heart"></i> Add to Wishlist';
                            btn.disabled = false;
                        }
                        alert(data ? (data.message || 'Error updating wishlist') : 'Error updating wishlist');
                    }
                }
            })
            .catch(error => {
                console.error('Wishlist error:', error);
                if (btn) {
                    btn.innerHTML = isAlreadyAdded ? '<i class="fas fa-heart"></i> Added to Wishlist' : '<i class="far fa-heart"></i> Add to Wishlist';
                    btn.disabled = false;
                }
                alert('Error updating wishlist. Please try again.');
            });
            })
            .catch(error => {
                console.error('Error checking login status:', error);
                // On error, assume not logged in and show login prompt
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: false,
                        title: '🔒 Login Required',
                        html: '<div style="font-size: 80px; margin: 20px 0; animation: lockShake 0.5s ease-in-out; line-height: 1; overflow: hidden;">🔒</div><p style="font-size: 18px; color: #666; margin: 15px 0; padding: 0; word-wrap: break-word; overflow: hidden; white-space: normal;">✨ Please login to add products to your wishlist ✨</p><p style="font-size: 14px; color: #999; margin-top: 10px; padding: 0; word-wrap: break-word; overflow: hidden; white-space: normal;">Join us to save your favorite products! 💖</p>',
                        showConfirmButton: true,
                        confirmButtonText: '🔑 Login Now',
                        confirmButtonColor: '#e91e63',
                        showCloseButton: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (typeof openLogin === 'function') {
                                openLogin();
                            } else if (typeof window.openLogin === 'function') {
                                window.openLogin();
                            } else {
                                window.location.href = 'account.php';
                            }
                        }
                    });
                } else {
                    alert('Please login to add products to your wishlist.');
                }
            });
    };
}

// Add direct event listeners - REPLACE onclick handlers entirely for reliability
document.addEventListener('DOMContentLoaded', function() {
    console.log('Setting up product button event listeners...');
    
    // Quantity buttons - use more specific selectors
    const quantitySelector = document.querySelector('.quantity-selector');
    if (quantitySelector) {
        const increaseBtn = quantitySelector.querySelector('button:last-of-type');
        const decreaseBtn = quantitySelector.querySelector('button:first-of-type');
        
        if (increaseBtn) {
            // Remove onclick and add event listener
            increaseBtn.removeAttribute('onclick');
            increaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Increase button clicked');
                if (typeof window.increaseQuantity === 'function') {
                    window.increaseQuantity();
                } else {
                    console.error('window.increaseQuantity is not a function');
                }
            }, true); // Use capture phase
        }
        
        if (decreaseBtn) {
            // Remove onclick and add event listener
            decreaseBtn.removeAttribute('onclick');
            decreaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Decrease button clicked');
                if (typeof window.decreaseQuantity === 'function') {
                    window.decreaseQuantity();
                } else {
                    console.error('window.decreaseQuantity is not a function');
                }
            }, true); // Use capture phase
        }
    }
    
    // Add to cart button
    const addToCartBtn = document.querySelector('.btn-add-cart');
    if (addToCartBtn) {
        const onclickAttr = addToCartBtn.getAttribute('onclick');
        const productIdMatch = onclickAttr ? onclickAttr.match(/addToCart\((\d+)\)/) : null;
        if (productIdMatch) {
            const productId = parseInt(productIdMatch[1]);
            // Remove onclick and add event listener
            addToCartBtn.removeAttribute('onclick');
            addToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Add to cart button clicked, productId:', productId);
                if (typeof window.addToCart === 'function') {
                    window.addToCart(productId);
                } else {
                    console.error('window.addToCart is not a function');
                }
            }, true); // Use capture phase
        }
    }
    
    // Add to wishlist button
    const addToWishlistBtn = document.querySelector('.btn-secondary');
    if (addToWishlistBtn && addToWishlistBtn.textContent.includes('Wishlist')) {
        const onclickAttr = addToWishlistBtn.getAttribute('onclick');
        const productIdMatch = onclickAttr ? onclickAttr.match(/addToWishlist\((\d+)\)/) : null;
        if (productIdMatch) {
            const productId = parseInt(productIdMatch[1]);
            // Remove onclick and add event listener
            addToWishlistBtn.removeAttribute('onclick');
            addToWishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Add to wishlist button clicked, productId:', productId);
                if (typeof window.addToWishlist === 'function') {
                    window.addToWishlist(productId);
                } else {
                    console.error('window.addToWishlist is not a function');
                }
            }, true); // Use capture phase
        }
    }
    
    // Accordion headers
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        const onclickAttr = header.getAttribute('onclick');
        if (onclickAttr && onclickAttr.includes('toggleAccordion')) {
            const sectionMatch = onclickAttr.match(/toggleAccordion\(['"]([^'"]+)['"]\)/);
            if (sectionMatch) {
                const section = sectionMatch[1];
                // Remove onclick and add event listener
                header.removeAttribute('onclick');
                header.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Accordion header clicked, section:', section);
                    if (typeof window.toggleAccordion === 'function') {
                        window.toggleAccordion(section);
                    } else {
                        console.error('window.toggleAccordion is not a function');
                    }
                }, true); // Use capture phase
            }
        }
    });
    
    console.log('Product button event listeners attached. Functions available:', {
        increaseQuantity: typeof window.increaseQuantity,
        decreaseQuantity: typeof window.decreaseQuantity,
        addToCart: typeof window.addToCart,
        addToWishlist: typeof window.addToWishlist,
        toggleAccordion: typeof window.toggleAccordion
    });
});

// Global function to force close wishlist modals
// Global flag to prevent multiple simultaneous close attempts
let isClosingWishlistModal = false;

window.forceCloseWishlistModal = function() {
    // Prevent multiple simultaneous calls
    if (isClosingWishlistModal) {
        return;
    }
    
    isClosingWishlistModal = true;
    
    try {
        // Use SweetAlert's built-in close method
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        
        // Reset flag after a short delay
        setTimeout(() => {
            isClosingWishlistModal = false;
        }, 500);
    } catch(e) {
        console.error('Error in forceCloseWishlistModal:', e);
        isClosingWishlistModal = false;
    }
};

// Removed global click listener - it was interfering with product buttons
// Close button is handled directly in didOpen callback

// Removed interval checker to prevent freezing - timers will handle modal closing

// Check if product is already in wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checking wishlist status on page load...');
    const wishlistBtn = document.querySelector('.btn-secondary[onclick*="addToWishlist"]') || 
                        document.querySelector('button.btn-secondary');
    if (wishlistBtn) {
        // Extract product ID from onclick attribute
        const onclickAttr = wishlistBtn.getAttribute('onclick');
        if (onclickAttr) {
            const productIdMatch = onclickAttr.match(/addToWishlist\((\d+)\)/);
            if (productIdMatch) {
                const productId = parseInt(productIdMatch[1]);
                console.log('Found product ID from button:', productId);
                // Check wishlist status immediately and also after a delay
                if (typeof window.checkWishlistStatus === 'function') {
                    window.checkWishlistStatus(productId);
                    // Also check again after a short delay to ensure session is ready
                    setTimeout(() => {
                        window.checkWishlistStatus(productId);
                    }, 500);
                }
            }
        }
    }
});

window.checkWishlistStatus = function(productId) {
    console.log('Checking wishlist status for productId:', productId);
    fetch('check-wishlist-status.php?product_id=' + productId)
        .then(response => response.json())
        .then(data => {
            console.log('Wishlist status check result:', data);
            const btn = document.querySelector('.btn-secondary[onclick*="addToWishlist"]') || 
                        document.querySelector('button.btn-secondary');
            if (btn) {
                if (data.in_wishlist) {
                    // Product is in wishlist - highlight button with same styles as add action
                    console.log('Product is in wishlist, highlighting button');
                    btn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
                    btn.style.setProperty('color', '#e91e63', 'important');
                    btn.style.setProperty('background', 'linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%)', 'important');
                    btn.style.setProperty('border', '2px solid #e91e63', 'important');
                    btn.style.setProperty('font-weight', '700', 'important');
                    btn.style.setProperty('box-shadow', '0 6px 20px rgba(233, 30, 99, 0.3), 0 0 0 4px rgba(233, 30, 99, 0.1)', 'important');
                    btn.style.setProperty('animation', 'wishlistGlow 2s ease-in-out infinite', 'important');
                    btn.style.setProperty('transition', 'all 0.3s ease', 'important');
                    btn.classList.add('wishlist-added');
                    console.log('Button highlighted on page load - product in wishlist');
                } else {
                    // Product is NOT in wishlist - ensure button is normal
                    console.log('Product is NOT in wishlist, ensuring button is normal');
                    btn.innerHTML = '<i class="far fa-heart"></i> Add to Wishlist';
                    btn.style.removeProperty('color');
                    btn.style.removeProperty('background');
                    btn.style.removeProperty('border');
                    btn.style.removeProperty('font-weight');
                    btn.style.removeProperty('box-shadow');
                    btn.style.removeProperty('animation');
                    btn.style.removeProperty('transform');
                    btn.style.setProperty('transition', 'all 0.3s ease', 'important');
                    btn.classList.remove('wishlist-added');
                    console.log('Button set to normal - product not in wishlist');
                }
            }
        })
        .catch(error => {
            console.error('Error checking wishlist status:', error);
        });
}

// Verify all functions are defined
console.log('Functions defined:', {
    increaseQuantity: typeof window.increaseQuantity,
    decreaseQuantity: typeof window.decreaseQuantity,
    toggleAccordion: typeof window.toggleAccordion,
    addToCart: typeof window.addToCart,
    addToWishlist: typeof window.addToWishlist,
    openCartSidebar: typeof window.openCartSidebar
});

// Ensure quantity functions are available - define them if missing
if (typeof window.increaseQuantity !== 'function') {
    console.warn('⚠️ increaseQuantity not found, defining fallback...');
    window.increaseQuantity = function() {
        const input = document.getElementById('quantity');
        if (input) {
            const max = parseInt(input.getAttribute('max')) || 999;
            const current = parseInt(input.value) || 1;
            if (current < max) {
                input.value = current + 1;
            }
        }
    };
}

if (typeof window.decreaseQuantity !== 'function') {
    console.warn('⚠️ decreaseQuantity not found, defining fallback...');
    window.decreaseQuantity = function() {
        const input = document.getElementById('quantity');
        if (input) {
            const current = parseInt(input.value) || 1;
            if (current > 1) {
                input.value = current - 1;
            }
        }
    };
}

// Ensure openCartSidebar is available - define fallback if missing
if (typeof window.openCartSidebar !== 'function') {
    console.warn('⚠️ openCartSidebar not found, defining fallback...');
    window.openCartSidebar = function() {
        const sidebar = document.getElementById('cartSidebar');
        const overlay = document.getElementById('cartSidebarOverlay');
        const cartContent = document.getElementById('cartSidebarContent');
        
        if (!sidebar || !overlay) {
            console.error('❌ Sidebar elements not found!');
            return false;
        }
        
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        if (cartContent) {
            cartContent.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading cart...</div>';
            const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
            const url = (basePath === '/' ? '' : basePath) + 'get-cart-sidebar.php?t=' + Date.now() + '&r=' + Math.random();
            
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
            .then(response => response.text())
            .then(html => {
                if (cartContent && html && html.trim()) {
                    cartContent.innerHTML = html;
                } else if (cartContent) {
                    cartContent.innerHTML = '<div class="empty-cart-sidebar"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
                }
            })
            .catch(err => {
                console.error('Error loading cart:', err);
                if (cartContent) {
                    cartContent.innerHTML = '<div style="text-align:center;padding:20px;color:#dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading cart</p></div>';
                }
            });
        }
        
        return true;
    };
}

// Verify all functions are defined after fallbacks
console.log('=== PRODUCT PAGE FUNCTIONS STATUS ===');
console.log('increaseQuantity:', typeof window.increaseQuantity, window.increaseQuantity ? '✓' : '✗');
console.log('decreaseQuantity:', typeof window.decreaseQuantity, window.decreaseQuantity ? '✓' : '✗');
console.log('addToCart:', typeof window.addToCart, window.addToCart ? '✓' : '✗');
console.log('addToWishlist:', typeof window.addToWishlist, window.addToWishlist ? '✓' : '✗');
console.log('toggleAccordion:', typeof window.toggleAccordion, window.toggleAccordion ? '✓' : '✗');
console.log('=====================================');

// Critical error checks (after fallbacks)
if (typeof window.increaseQuantity !== 'function') {
    console.error('CRITICAL: window.increaseQuantity is not a function!');
} else {
    console.log('✅ increaseQuantity is available');
}
if (typeof window.decreaseQuantity !== 'function') {
    console.error('CRITICAL: window.decreaseQuantity is not a function!');
} else {
    console.log('✅ decreaseQuantity is available');
}
if (typeof window.addToCart !== 'function') {
    console.error('CRITICAL: window.addToCart is not a function!');
} else {
    console.log('✅ addToCart is available');
}
if (typeof window.addToWishlist !== 'function') {
    console.error('CRITICAL: window.addToWishlist is not a function!');
} else {
    console.log('✅ addToWishlist is available');
}
if (typeof window.toggleAccordion !== 'function') {
    console.error('CRITICAL: window.toggleAccordion is not a function!');
} else {
    console.log('✅ toggleAccordion is available');
}
if (typeof window.openCartSidebar !== 'function') {
    console.error('CRITICAL: window.openCartSidebar is not a function!');
} else {
    console.log('✅ openCartSidebar is available');
}

</script>

<?php include "includes/footer.php"; ?>
