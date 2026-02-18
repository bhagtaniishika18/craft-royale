<?php
include "../../includes/db.php";
include "../../includes/header.php";

$subcategory_name = 'Gijai / Gimp / Stiff';
$subcategory_name_escaped = mysqli_real_escape_string($conn, $subcategory_name);

$subcategory_query = mysqli_query($conn, "SELECT s.*, c.category_name, c.id as category_id 
                                           FROM subcategories s 
                                           LEFT JOIN categories c ON s.category_id = c.id 
                                           WHERE s.subcategory_name = '$subcategory_name_escaped' 
                                           LIMIT 1");

if (!$subcategory_query || mysqli_num_rows($subcategory_query) == 0) {
    header("Location: ../../products.php");
    exit();
}

$subcategory_info = mysqli_fetch_assoc($subcategory_query);
$subcategory_id = (int)$subcategory_info['id']; // Ensure it's an integer
$category_id = (int)$subcategory_info['category_id'];
$category_name = $subcategory_info['category_name'];

// Validate subcategory_id is valid
if (!$subcategory_id || $subcategory_id <= 0) {
    header("Location: ../../products.php");
    exit();
}

$sort = $_GET['sort'] ?? 'newest';

$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Build products query - filter by subcategory (STRICT FILTERING)
// IMPORTANT: Only show products that belong to THIS specific subcategory
$where_conditions = [];
if (in_array('status', $products_columns)) {
    // Show active and out_of_stock products, but exclude inactive
    $where_conditions[] = "((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
}

// MANDATORY: Always filter by the specific subcategory_id
// This ensures products only show on their correct subcategory page
// STRICT FILTER: Only show products with THIS exact subcategory_id (not NULL, not 0, not other IDs)
if (in_array('subcategory_id', $products_columns)) {
    // Use JOIN with subcategories table to ensure we only get products from this exact subcategory
    $where_conditions[] = "p.subcategory_id = $subcategory_id";
    $where_conditions[] = "p.subcategory_id IS NOT NULL";
    $where_conditions[] = "p.subcategory_id > 0";
    $use_join = true;
} else {
    // If subcategory_id column doesn't exist, show no products
    $where_conditions[] = "1 = 0"; // This will return no results
    $use_join = false;
}

$order_by = "ORDER BY p.id DESC";
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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build count query with JOIN to ensure strict filtering
// CRITICAL: Use INNER JOIN to ensure ONLY products with THIS exact subcategory_id are counted
if (isset($use_join) && $use_join && in_array('subcategory_id', $products_columns)) {
    $subcategory_id = (int)$subcategory_id;
    $count_query = "SELECT COUNT(*) as total FROM products p 
                    INNER JOIN subcategories s ON p.subcategory_id = s.id 
                    WHERE s.id = $subcategory_id 
                    AND p.subcategory_id = $subcategory_id 
                    AND p.subcategory_id IS NOT NULL 
                    AND p.subcategory_id > 0";
    if (in_array('status', $products_columns)) {
        // Show active and out_of_stock products, but exclude inactive
        $count_query .= " AND ((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
    }
} else {
    $count_query = "SELECT COUNT(*) as total FROM products p";
    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
}
$count_result = mysqli_query($conn, $count_query);
$total_products = 0;
if ($count_result) {
    $count_data = mysqli_fetch_assoc($count_result);
    $total_products = $count_data ? $count_data['total'] : 0;
}
$total_pages = $total_products > 0 ? ceil($total_products / $per_page) : 0;

// Build products query with JOIN to ensure strict filtering
// CRITICAL: Use INNER JOIN to ensure ONLY products with THIS exact subcategory_id are returned
if (isset($use_join) && $use_join && in_array('subcategory_id', $products_columns)) {
    $subcategory_id = (int)$subcategory_id;
    $products_query = "SELECT p.* FROM products p 
                       INNER JOIN subcategories s ON p.subcategory_id = s.id 
                       WHERE s.id = $subcategory_id 
                       AND p.subcategory_id = $subcategory_id 
                       AND p.subcategory_id IS NOT NULL 
                       AND p.subcategory_id > 0";
    if (in_array('status', $products_columns)) {
        // Show active and out_of_stock products, but exclude inactive
        $products_query .= " AND ((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)";
    }
    $products_query .= " $order_by LIMIT $per_page OFFSET $offset";
} else {
    $products_query = "SELECT p.* FROM products p";
    if (!empty($where_conditions)) {
        $products_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $products_query .= " $order_by LIMIT $per_page OFFSET $offset";
}

$products_result = mysqli_query($conn, $products_query);
if (!$products_result) {
    error_log("Products query failed: " . mysqli_error($conn));
    $products_result = false;
}

$banner_image = '../../assets/images/embroidery/gijai.jpg';
include 'subcategory-template.php';
?>


