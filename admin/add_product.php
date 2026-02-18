<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$product_id = $_GET['id'] ?? null;
$product = null;
$edit_mode = false;

if ($product_id) {
    $edit_mode = true;
    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'");
    if (!$product_query) {
        header("Location: manage_products.php?error=" . urlencode("Error fetching product: " . mysqli_error($conn)));
        exit();
    }
    $product = mysqli_fetch_assoc($product_query);
    if (!$product) {
        header("Location: manage_products.php?error=" . urlencode("Product not found"));
        exit();
    }
    // Debug: Log product data (remove in production)
    // error_log("Product data: " . print_r($product, true));
}

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

// Fetch subcategories based on selected category
$subcategories = [];
if ($edit_mode && isset($product['category_id']) && !empty($product['category_id'])) {
    $subcat_query = mysqli_query($conn, "SELECT * FROM subcategories WHERE category_id='{$product['category_id']}' ORDER BY subcategory_name");
    while ($sub = mysqli_fetch_assoc($subcat_query)) {
        $subcategories[] = $sub;
    }
}

if (isset($_POST['submit'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $subcategory_id = !empty($_POST['subcategory_id']) ? mysqli_real_escape_string($conn, $_POST['subcategory_id']) : null;
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', $name)));
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $mrp = mysqli_real_escape_string($conn, $_POST['mrp']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $discount_percent = !empty($_POST['discount_percent']) ? mysqli_real_escape_string($conn, $_POST['discount_percent']) : 0;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    // short_description removed
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $country_of_origin = mysqli_real_escape_string($conn, $_POST['country_of_origin']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);

    // Handle main image
    $image = $product['image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        // Create uploads/products directory if it doesn't exist
        $upload_dir = "../uploads/products/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        // Delete old image if editing
        if ($edit_mode && !empty($product['image']) && file_exists("../uploads/products/" . $product['image'])) {
            unlink("../uploads/products/" . $product['image']);
        }
    }

    // Handle additional images
    $images_array = [];
    if (!empty($_FILES['images']['name'][0])) {
        // Ensure uploads/products directory exists
        $upload_dir = "../uploads/products/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        foreach ($_FILES['images']['name'] as $key => $filename) {
            if (!empty($filename)) {
                $new_filename = time() . '_' . $key . '_' . $filename;
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_dir . $new_filename);
                $images_array[] = $new_filename;
            }
        }
    }
    
    // Merge with existing images if editing
    if ($edit_mode && !empty($product['images'])) {
        $existing_images = json_decode($product['images'], true);
        if (is_array($existing_images)) {
            $images_array = array_merge($existing_images, $images_array);
        }
    }
    $images_json = !empty($images_array) ? json_encode($images_array) : null;

    if ($edit_mode) {
        // Check which columns exist in products table
        $columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
        $existing_columns = [];
        while ($col = mysqli_fetch_assoc($columns_query)) {
            $existing_columns[] = $col['Field'];
        }
        
        // Build UPDATE query based on existing columns
        $update_fields = [];
        
        if (in_array('category_id', $existing_columns)) {
            $update_fields[] = "category_id='$category_id'";
        }
        
        if (in_array('subcategory_id', $existing_columns)) {
            $update_fields[] = "subcategory_id=" . ($subcategory_id ? "'$subcategory_id'" : "NULL");
        }
        
        // Check for 'name' or 'product_name'
        if (in_array('name', $existing_columns)) {
            $update_fields[] = "name='$name'";
        } elseif (in_array('product_name', $existing_columns)) {
            $update_fields[] = "product_name='$name'";
        }
        
        if (in_array('slug', $existing_columns)) {
            $update_fields[] = "slug='$slug'";
        }
        
        if (in_array('sku', $existing_columns)) {
            $update_fields[] = "sku=" . ($sku ? "'$sku'" : "NULL");
        }
        
        if (in_array('mrp', $existing_columns)) {
            $update_fields[] = "mrp='$mrp'";
        }
        
        if (in_array('price', $existing_columns)) {
            $update_fields[] = "price='$price'";
        }
        
        if (in_array('discount_percent', $existing_columns)) {
            $update_fields[] = "discount_percent='$discount_percent'";
        }
        
        if (in_array('description', $existing_columns)) {
            $update_fields[] = "description=" . ($description ? "'$description'" : "NULL");
        }
        
        // short_description removed
        
        if (in_array('stock', $existing_columns)) {
            $update_fields[] = "stock='$stock'";
        }
        
        if (in_array('status', $existing_columns)) {
            $update_fields[] = "status='$status'";
        }
        
        if (in_array('country_of_origin', $existing_columns)) {
            $update_fields[] = "country_of_origin=" . ($country_of_origin ? "'$country_of_origin'" : "NULL");
        }
        
        if (in_array('color', $existing_columns)) {
            $update_fields[] = "color=" . ($color ? "'$color'" : "NULL");
        }
        
        if (in_array('size', $existing_columns)) {
            $update_fields[] = "size=" . ($size ? "'$size'" : "NULL");
        }
        
        if (in_array('material', $existing_columns)) {
            $update_fields[] = "material=" . ($material ? "'$material'" : "NULL");
        }
        
        if (in_array('weight', $existing_columns)) {
            $update_fields[] = "weight=" . ($weight ? "'$weight'" : "NULL");
        }
        
        if (!empty($image) && in_array('image', $existing_columns)) {
            $update_fields[] = "image='$image'";
        }
        
        // Handle images - preserve existing if no new ones uploaded
        if (in_array('images', $existing_columns)) {
            if ($images_json !== null) {
                // New images uploaded, use them
                $update_fields[] = "images='" . mysqli_real_escape_string($conn, $images_json) . "'";
            } elseif ($edit_mode && !empty($product['images'])) {
                // No new images, preserve existing ones
                $update_fields[] = "images='" . mysqli_real_escape_string($conn, $product['images']) . "'";
            } else {
                // No images at all
                $update_fields[] = "images=NULL";
            }
        }
        
        if (empty($update_fields)) {
            header("Location: add_product.php?id=$product_id&error=" . urlencode("No valid columns found in products table. Please run fix_products_table.php first."));
            exit();
        }
        
        $update_query = "UPDATE products SET " . implode(", ", $update_fields) . " WHERE id='$product_id'";
        
        $result = mysqli_query($conn, $update_query);
        if (!$result) {
            // Log the error for debugging
            $error_msg = mysqli_error($conn);
            error_log("Update query failed: " . $error_msg);
            error_log("Update query: " . $update_query);
            header("Location: add_product.php?id=$product_id&error=" . urlencode("Failed to update product: " . $error_msg));
            exit();
        }
        
        // Success - redirect with success message
        header("Location: manage_products.php?success=updated");
        exit();
    } else {
        // Check which columns exist in products table
        $columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
        $existing_columns = [];
        while ($col = mysqli_fetch_assoc($columns_query)) {
            $existing_columns[] = $col['Field'];
        }
        
        // Build INSERT query based on existing columns
        $insert_fields = [];
        $insert_values = [];
        
        // Required fields (must exist)
        if (in_array('category_id', $existing_columns)) {
            $insert_fields[] = 'category_id';
            $insert_values[] = "'$category_id'";
        }
        
        if (in_array('subcategory_id', $existing_columns)) {
            $insert_fields[] = 'subcategory_id';
            $insert_values[] = $subcategory_id ? "'$subcategory_id'" : "NULL";
        }
        
        // Check for 'name' or 'product_name'
        if (in_array('name', $existing_columns)) {
            $insert_fields[] = 'name';
            $insert_values[] = "'$name'";
        } elseif (in_array('product_name', $existing_columns)) {
            $insert_fields[] = 'product_name';
            $insert_values[] = "'$name'";
        }
        
        // Optional fields
        if (in_array('slug', $existing_columns)) {
            $insert_fields[] = 'slug';
            $insert_values[] = "'$slug'";
        }
        
        if (in_array('sku', $existing_columns)) {
            $insert_fields[] = 'sku';
            $insert_values[] = $sku ? "'$sku'" : "NULL";
        }
        
        if (in_array('mrp', $existing_columns)) {
            $insert_fields[] = 'mrp';
            $insert_values[] = "'$mrp'";
        }
        
        if (in_array('price', $existing_columns)) {
            $insert_fields[] = 'price';
            $insert_values[] = "'$price'";
        }
        
        if (in_array('discount_percent', $existing_columns)) {
            $insert_fields[] = 'discount_percent';
            $insert_values[] = "'$discount_percent'";
        }
        
        if (in_array('description', $existing_columns)) {
            $insert_fields[] = 'description';
            $insert_values[] = $description ? "'$description'" : "NULL";
        }
        
        // short_description removed
        
        if (in_array('image', $existing_columns)) {
            $insert_fields[] = 'image';
            $insert_values[] = "'$image'";
        }
        
        if (in_array('images', $existing_columns)) {
            $insert_fields[] = 'images';
            $insert_values[] = $images_json ? "'" . mysqli_real_escape_string($conn, $images_json) . "'" : "NULL";
        }
        
        if (in_array('stock', $existing_columns)) {
            $insert_fields[] = 'stock';
            $insert_values[] = "'$stock'";
        }
        
        if (in_array('status', $existing_columns)) {
            $insert_fields[] = 'status';
            $insert_values[] = "'$status'";
        }
        
        if (in_array('country_of_origin', $existing_columns)) {
            $insert_fields[] = 'country_of_origin';
            $insert_values[] = $country_of_origin ? "'$country_of_origin'" : "NULL";
        }
        
        if (in_array('color', $existing_columns)) {
            $insert_fields[] = 'color';
            $insert_values[] = $color ? "'$color'" : "NULL";
        }
        
        if (in_array('size', $existing_columns)) {
            $insert_fields[] = 'size';
            $insert_values[] = $size ? "'$size'" : "NULL";
        }
        
        if (in_array('material', $existing_columns)) {
            $insert_fields[] = 'material';
            $insert_values[] = $material ? "'$material'" : "NULL";
        }
        
        if (in_array('weight', $existing_columns)) {
            $insert_fields[] = 'weight';
            $insert_values[] = $weight ? "'$weight'" : "NULL";
        }
        
        if (empty($insert_fields)) {
            header("Location: add_product.php?error=" . urlencode("No valid columns found in products table. Please run fix_products_table.php first."));
            exit();
        }
        
        $insert_query = "INSERT INTO products (" . implode(", ", $insert_fields) . ") VALUES (" . implode(", ", $insert_values) . ")";
        
        $result = mysqli_query($conn, $insert_query);
        if ($result) {
            header("Location: manage_products.php?success=added");
        } else {
            header("Location: add_product.php?error=" . urlencode(mysqli_error($conn)));
        }
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Product - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .form-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 40px;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(47, 199, 180, 0.1);
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2b2b2b;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2fc7b4;
            box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
        }

        .form-group input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
            color: #666;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group textarea.description {
            min-height: 150px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border: 2px dashed #2fc7b4;
            border-radius: 8px;
            background: rgba(47, 199, 180, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: rgba(47, 199, 180, 0.1);
            border-color: #2fa76b;
        }

        .file-upload-label i {
            font-size: 32px;
            color: #2fc7b4;
            margin-right: 10px;
        }

        .image-preview {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }

        .image-preview-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e0e0e0;
        }

        .image-preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .submit-btn {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(47, 199, 180, 0.4);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #2b2b2b;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?= $edit_mode ? 'Edit Product' : 'Add New Product' ?></h1>
                    <p class="welcome-text"><?= $edit_mode ? 'Update product information' : 'Add a new product to your store' ?></p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="manage_products.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>

                <?php if (isset($_GET['error'])): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
                        <br><br>
                        <a href="fix_products_table.php" style="color: #721c24; text-decoration: underline;">
                            <i class="fas fa-tools"></i> Click here to fix the products table
                        </a>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <div class="form-header">
                        <h2><?= $edit_mode ? 'Edit Product' : 'Add New Product' ?></h2>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category_id" id="category_id" required onchange="loadSubcategories()">
                                    <option value="">Select Category</option>
                                    <?php 
                                    mysqli_data_seek($categories, 0);
                                    while ($row = mysqli_fetch_assoc($categories)) { 
                                        $selected = '';
                                        if ($edit_mode && isset($product['category_id'])) {
                                            // Compare as strings to handle both string and int IDs
                                            $selected = (strval($product['category_id']) == strval($row['id'])) ? 'selected' : '';
                                        }
                                    ?>
                                        <option value="<?= $row['id'] ?>" <?= $selected ?>><?= htmlspecialchars($row['category_name']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id">
                                    <option value="">Select Subcategory</option>
                                    <?php foreach ($subcategories as $sub) { 
                                        $selected = '';
                                        if ($edit_mode && isset($product['subcategory_id']) && !empty($product['subcategory_id'])) {
                                            // Compare as strings to handle both string and int IDs
                                            $selected = (strval($product['subcategory_id']) == strval($sub['id'])) ? 'selected' : '';
                                        }
                                    ?>
                                        <option value="<?= $sub['id'] ?>" <?= $selected ?>><?= htmlspecialchars($sub['subcategory_name']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Product Name *</label>
                                <input type="text" name="name" value="<?= $edit_mode ? htmlspecialchars($product['name'] ?? '') : '' ?>" required>
                            </div>

                            <div class="form-group">
                                <label>SKU</label>
                                <input type="text" name="sku" value="<?= $edit_mode ? htmlspecialchars($product['sku'] ?? '') : '' ?>" placeholder="e.g., EMBFW5226">
                            </div>

                            <div class="form-group">
                                <label>Weight</label>
                                <input type="text" name="weight" value="<?= $edit_mode ? htmlspecialchars($product['weight'] ?? '') : '' ?>" placeholder="e.g., 100 Gram">
                            </div>

                            <div class="form-group">
                                <label>MRP (Original Price) *</label>
                                <input type="number" name="mrp" id="mrp" step="0.01" value="<?= $edit_mode ? ($product['mrp'] ?? '') : '' ?>" required oninput="calculateDiscount()">
                            </div>

                            <div class="form-group">
                                <label>Selling Price *</label>
                                <input type="number" name="price" id="price" step="0.01" value="<?= $edit_mode ? ($product['price'] ?? '') : '' ?>" required oninput="calculateDiscount()">
                            </div>

                            <div class="form-group">
                                <label>Discount Percent <small style="color: #666; font-weight: normal;">(Auto-calculated)</small></label>
                                <input type="number" name="discount_percent" id="discount_percent" step="1" value="<?= $edit_mode ? ($product['discount_percent'] ?? '') : '' ?>" placeholder="Auto-calculated from MRP and Selling Price" readonly>
                            </div>

                            <div class="form-group">
                                <label>Stock Quantity *</label>
                                <input type="number" name="stock" value="<?= $edit_mode ? ($product['stock'] ?? '0') : '0' ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" id="status" required>
                                    <option value="active" <?= ($edit_mode && isset($product['status']) && $product['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($edit_mode && isset($product['status']) && $product['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    <option value="out_of_stock" <?= ($edit_mode && isset($product['status']) && $product['status'] == 'out_of_stock') ? 'selected' : '' ?>>Out of Stock</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Country of Origin</label>
                                <input type="text" name="country_of_origin" value="<?= $edit_mode ? htmlspecialchars($product['country_of_origin'] ?? '') : '' ?>">
                            </div>

                            <div class="form-group">
                                <label>Color</label>
                                <input type="text" name="color" value="<?= $edit_mode ? htmlspecialchars($product['color'] ?? '') : '' ?>" placeholder="e.g., Pink">
                            </div>

                            <div class="form-group">
                                <label>Size</label>
                                <input type="text" name="size" value="<?= $edit_mode ? htmlspecialchars($product['size'] ?? '') : '' ?>" placeholder="e.g., 1MM">
                            </div>

                            <div class="form-group">
                                <label>Material</label>
                                <input type="text" name="material" value="<?= $edit_mode ? htmlspecialchars($product['material'] ?? '') : '' ?>">
                            </div>

                            <div class="form-group full-width">
                                <label>Full Description</label>
                                <textarea name="description" class="description" placeholder="Detailed product description"><?= $edit_mode ? htmlspecialchars($product['description'] ?? '') : '' ?></textarea>
                            </div>

                            <div class="form-group full-width">
                                <label>Main Product Image *</label>
                                <div class="file-upload">
                                    <input type="file" name="image" id="main_image" <?= !$edit_mode ? 'required' : '' ?> accept="image/*" onchange="previewMainImage(this)">
                                    <label for="main_image" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span><?= $edit_mode ? 'Change Main Image' : 'Upload Main Image' ?></span>
                                    </label>
                                </div>
                                <div id="main_image_preview" style="margin-top: 15px;">
                                    <?php if ($edit_mode && !empty($product['image'])) { ?>
                                        <img src="../uploads/products/<?= htmlspecialchars($product['image']) ?>" style="max-width: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>Additional Images</label>
                                <div class="file-upload">
                                    <input type="file" name="images[]" id="additional_images" multiple accept="image/*" onchange="previewAdditionalImages(this)">
                                    <label for="additional_images" class="file-upload-label">
                                        <i class="fas fa-images"></i>
                                        <span>Upload Additional Images</span>
                                    </label>
                                </div>
                                <div id="additional_images_preview" class="image-preview" style="margin-top: 15px;">
                                    <?php 
                                    if ($edit_mode && !empty($product['images'])) {
                                        $existing_images = json_decode($product['images'], true);
                                        if (is_array($existing_images)) {
                                            foreach ($existing_images as $img) {
                                                echo '<div class="image-preview-item">
                                                    <img src="../uploads/products/' . htmlspecialchars($img) . '" alt="Product Image">
                                                </div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" name="submit" class="submit-btn">
                                <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Product' : 'Add Product' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function loadSubcategories(preserveValue = null) {
            const categoryId = document.getElementById('category_id').value;
            const subcategorySelect = document.getElementById('subcategory_id');
            
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            
            if (categoryId) {
                // Show loading state
                subcategorySelect.innerHTML = '<option value="">Loading...</option>';
                subcategorySelect.disabled = true;
                
                return fetch(`get_subcategories.php?category_id=${categoryId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                        subcategorySelect.disabled = false;
                        
                        // Check if data is an array
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(sub => {
                                const option = document.createElement('option');
                                option.value = sub.id;
                                option.textContent = sub.subcategory_name;
                                subcategorySelect.appendChild(option);
                            });
                            console.log(`Loaded ${data.length} subcategories`);
                            
                            // Restore selected value if provided
                            if (preserveValue !== null) {
                                subcategorySelect.value = preserveValue;
                            }
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'No subcategories available for this category';
                            subcategorySelect.appendChild(option);
                            console.log('No subcategories found for category ID:', categoryId);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subcategories:', error);
                        subcategorySelect.innerHTML = '<option value="">Error: ' + error.message + '</option>';
                        subcategorySelect.disabled = false;
                    });
            } else {
                subcategorySelect.disabled = false;
                return Promise.resolve();
            }
        }
        
        // Load subcategories on page load if category is already selected (edit mode)
        <?php if ($edit_mode && isset($product['category_id']) && !empty($product['category_id'])): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const selectedSubcategoryId = <?= isset($product['subcategory_id']) && !empty($product['subcategory_id']) ? json_encode($product['subcategory_id']) : 'null' ?>;
            loadSubcategories(selectedSubcategoryId);
        });
        <?php endif; ?>
        
        // Also load subcategories when page loads if category is pre-selected (for new products with default category)
        window.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            if (categorySelect && categorySelect.value) {
                // Small delay to ensure DOM is ready
                setTimeout(function() {
                    loadSubcategories();
                }, 100);
            }
        });

        function previewMainImage(input) {
            const preview = document.getElementById('main_image_preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewAdditionalImages(input) {
            const preview = document.getElementById('additional_images_preview');
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'image-preview-item';
                        div.innerHTML = '<img src="' + e.target.result + '" alt="Product Image">';
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        function calculateDiscount() {
            const mrpInput = document.getElementById('mrp');
            const priceInput = document.getElementById('price');
            const discountInput = document.getElementById('discount_percent');
            
            if (!mrpInput || !priceInput || !discountInput) {
                return;
            }
            
            const mrp = parseFloat(mrpInput.value);
            const price = parseFloat(priceInput.value);
            
            // Only calculate if both values are valid numbers and MRP is greater than 0
            if (!isNaN(mrp) && !isNaN(price) && mrp > 0) {
                if (price <= mrp) {
                    // Calculate discount percentage: ((MRP - Price) / MRP) * 100
                    const discount = ((mrp - price) / mrp) * 100;
                    discountInput.value = Math.round(discount);
                } else {
                    // If selling price is greater than MRP, set discount to 0
                    discountInput.value = '0';
                }
            } else {
                // Clear discount if values are invalid
                discountInput.value = '';
            }
        }

        // Calculate discount on page load if values exist (for edit mode)
        window.addEventListener('DOMContentLoaded', function() {
            const mrpInput = document.getElementById('mrp');
            const priceInput = document.getElementById('price');
            if (mrpInput && priceInput && mrpInput.value && priceInput.value) {
                calculateDiscount();
            }
        });
    </script>
</body>
</html>
