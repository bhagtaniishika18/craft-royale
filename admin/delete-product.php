<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view-products.php?error=" . urlencode("Invalid product ID"));
    exit();
}

$id = (int)$_GET['id'];

// Check which columns exist in products table
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Build SELECT query based on available columns
$select_fields = [];
if (in_array('image', $products_columns)) {
    $select_fields[] = 'image';
}
if (in_array('product_image', $products_columns)) {
    $select_fields[] = 'product_image';
}

// Get product info before deleting (to delete image file)
$product = null;
if (!empty($select_fields)) {
    $select_query = "SELECT " . implode(", ", $select_fields) . " FROM products WHERE id = $id LIMIT 1";
    $product_query = mysqli_query($conn, $select_query);
    if ($product_query && mysqli_num_rows($product_query) > 0) {
        $product = mysqli_fetch_assoc($product_query);
    }
}

// Delete product
$delete_query = "DELETE FROM products WHERE id = $id";
$result = mysqli_query($conn, $delete_query);

if ($result) {
    // Delete product image if exists
    if ($product) {
        // Get image from whichever column exists
        $image_to_delete = '';
        if (isset($product['image']) && !empty($product['image'])) {
            $image_to_delete = $product['image'];
        } elseif (isset($product['product_image']) && !empty($product['product_image'])) {
            $image_to_delete = $product['product_image'];
        }
        
        if (!empty($image_to_delete)) {
            // Try uploads/products folder
            $image_path = '../uploads/products/' . $image_to_delete;
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
            // Also check productsimg/embroidery folder
            $productsimg_path = '../productsimg/embroidery/' . $image_to_delete;
            if (file_exists($productsimg_path)) {
                @unlink($productsimg_path);
            }
        }
    }
    header("Location: view-products.php?success=" . urlencode("Product deleted successfully"));
} else {
    header("Location: view-products.php?error=" . urlencode("Failed to delete product: " . mysqli_error($conn)));
}
exit();
?>