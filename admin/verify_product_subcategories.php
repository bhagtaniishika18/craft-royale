<?php
/**
 * Comprehensive tool to verify and fix product subcategory assignments
 * This will show all products and their subcategory assignments
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$messages = [];
$errors = [];

// Check which columns exist in products table FIRST
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Get all categories
$categories_query = mysqli_query($conn, "SELECT id, category_name FROM categories ORDER BY category_name");
$categories = [];
while ($cat = mysqli_fetch_assoc($categories_query)) {
    $categories[$cat['id']] = $cat['category_name'];
}

// Get all subcategories from all categories
$subcategories_query = mysqli_query($conn, "SELECT s.id, s.subcategory_name, s.category_id, c.category_name 
                                            FROM subcategories s 
                                            LEFT JOIN categories c ON s.category_id = c.id 
                                            ORDER BY c.category_name, s.subcategory_name");
$subcategories = [];
$subcategories_by_category = [];
while ($sub = mysqli_fetch_assoc($subcategories_query)) {
    $subcategories[$sub['id']] = [
        'name' => $sub['subcategory_name'],
        'category_id' => $sub['category_id'],
        'category_name' => $sub['category_name'] ?? 'Unknown'
    ];
    if (!isset($subcategories_by_category[$sub['category_id']])) {
        $subcategories_by_category[$sub['category_id']] = [];
    }
    $subcategories_by_category[$sub['category_id']][] = $sub['id'];
}

// Build SELECT fields based on what columns exist
$select_fields = ['p.id', 'p.name'];
if (in_array('category_id', $products_columns)) {
    $select_fields[] = 'p.category_id';
} else {
    $select_fields[] = 'NULL as category_id';
}
if (in_array('subcategory_id', $products_columns)) {
    $select_fields[] = 'p.subcategory_id';
} else {
    $select_fields[] = 'NULL as subcategory_id';
}

$select_fields[] = 's.subcategory_name';
$select_fields[] = 's.id as subcat_id';
if (in_array('category_id', $products_columns)) {
    $select_fields[] = 'c.category_name';
} else {
    $select_fields[] = 'NULL as category_name';
}

// Build WHERE clause based on available columns - get ALL products, not just embroidery
$where_clause = "";
if (in_array('category_id', $products_columns)) {
    // Get all products from all categories
    $where_clause = "WHERE 1=1";
} else {
    // If category_id doesn't exist, we can't filter by category
    $where_clause = "WHERE 1=1";
}

// Get all products with their subcategory info
$products_query = "SELECT " . implode(", ", $select_fields) . "
                   FROM products p
                   LEFT JOIN subcategories s ON " . (in_array('subcategory_id', $products_columns) ? "p.subcategory_id = s.id" : "1=0") . "
                   " . (in_array('category_id', $products_columns) ? "LEFT JOIN categories c ON p.category_id = c.id" : "") . "
                   $where_clause";
if (in_array('category_id', $products_columns) && in_array('subcategory_id', $products_columns)) {
    $products_query .= " ORDER BY c.category_name, p.subcategory_id, p.name";
} elseif (in_array('category_id', $products_columns)) {
    $products_query .= " ORDER BY c.category_name, p.name";
} elseif (in_array('subcategory_id', $products_columns)) {
    $products_query .= " ORDER BY p.subcategory_id, p.name";
} else {
    $products_query .= " ORDER BY p.name";
}
$products_result = mysqli_query($conn, $products_query);

if (!$products_result) {
    $errors[] = "Error fetching products: " . mysqli_error($conn);
}

// Handle form submission to fix a product
if (isset($_POST['fix_product'])) {
    $product_id = (int)$_POST['product_id'];
    $new_subcategory_id = (int)$_POST['subcategory_id'];
    
    if ($product_id > 0 && $new_subcategory_id > 0) {
        // Check if subcategory_id column exists before updating
        if (in_array('subcategory_id', $products_columns)) {
            $update_query = "UPDATE products SET subcategory_id = $new_subcategory_id WHERE id = $product_id";
            if (mysqli_query($conn, $update_query)) {
                $messages[] = "Product ID $product_id updated successfully!";
                // Refresh page
                header("Location: verify_product_subcategories.php");
                exit();
            } else {
                $errors[] = "Error updating product: " . mysqli_error($conn);
            }
        } else {
            $errors[] = "subcategory_id column does not exist in products table. Please run the database schema file first.";
        }
    } else {
        $errors[] = "Invalid product ID or subcategory ID";
    }
}

// Get statistics for all subcategories
$stats = [];
foreach ($subcategories as $sub_id => $sub_data) {
    if (in_array('subcategory_id', $products_columns)) {
        $count_query = "SELECT COUNT(*) as count FROM products WHERE subcategory_id = $sub_id";
    } else {
        $count_query = "SELECT COUNT(*) as count FROM products";
    }
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result) {
        $count_data = mysqli_fetch_assoc($count_result);
        $stats[$sub_id] = $count_data['count'];
    } else {
        $stats[$sub_id] = 0;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Product Subcategories - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #2fc7b4;
            font-size: 14px;
        }
        .stat-card .count {
            font-size: 32px;
            font-weight: 700;
            color: #2fa76b;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            font-weight: 600;
        }
        .status-ok { color: #2e7d32; }
        .status-warning { color: #ff9800; }
        .status-error { color: #f44336; }
        .fix-form {
            display: inline-flex;
            gap: 10px;
            align-items: center;
        }
        select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 6px 15px;
            background: #2fc7b4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #2fa76b;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Verify Product Subcategories</h1>
                    <p class="welcome-text">Check and fix product subcategory assignments</p>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="container">
                    <h2>Subcategory Statistics</h2>
                    <?php 
                    // Group subcategories by category for better display
                    $current_category = '';
                    foreach ($subcategories as $sub_id => $sub_data): 
                        if ($current_category != $sub_data['category_name']):
                            if ($current_category != ''): ?>
                                </div>
                            <?php endif; ?>
                            <h3 style="margin-top: 30px; margin-bottom: 15px; color: #2fa76b; font-size: 20px; border-bottom: 2px solid #2fc7b4; padding-bottom: 10px;">
                                <?= htmlspecialchars($sub_data['category_name']) ?>
                            </h3>
                            <div class="stats-grid">
                            <?php 
                            $current_category = $sub_data['category_name'];
                        endif;
                    ?>
                        <div class="stat-card">
                            <h3><?= htmlspecialchars($sub_data['name']) ?></h3>
                            <div class="count"><?= $stats[$sub_id] ?? 0 ?></div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if (!in_array('subcategory_id', $products_columns)): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Warning:</strong> The `subcategory_id` column does not exist in the products table. 
                            <br><br>
                            <strong>QUICK FIX - Click this link:</strong> 
                            <br><br>
                            <a href="SIMPLE_FIX.php" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 10px 0;">
                                <i class="fas fa-magic"></i> CLICK HERE TO ADD COLUMN AUTOMATICALLY
                            </a>
                            <br><br>
                            <strong>OR run this SQL in phpMyAdmin (copy the code below):</strong>
                            <br><br>
                            <div style="background: #2b2b2b; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; margin-top: 10px; font-size: 14px; text-align: left;">
                                ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL;<br>
                                ALTER TABLE `products` ADD INDEX `idx_subcategory_id` (`subcategory_id`);
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($messages as $msg): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($errors as $err): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?>
                        </div>
                    <?php endforeach; ?>

                    <h2 style="margin-top: 30px;">All  Products</h2>
                    <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Assigned Subcategory ID</th>
                                    <th>Assigned Subcategory Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($products_result)):
                                    $product_id = $product['id'];
                                    $product_name = htmlspecialchars($product['name']);
                                    $product_category = htmlspecialchars($product['category_name'] ?? 'N/A');
                                    
                                    // Handle subcategory_id - check if column exists
                                    if (in_array('subcategory_id', $products_columns)) {
                                        $assigned_subcategory_id = (int)($product['subcategory_id'] ?? 0);
                                    } else {
                                        $assigned_subcategory_id = 0;
                                    }
                                    
                                    $assigned_subcategory_name = htmlspecialchars($product['subcategory_name'] ?? 'N/A');

                                    $status_class = 'status-ok';
                                    $status_text = 'Correct';
                                    $needs_fix = false;

                                    if (!in_array('subcategory_id', $products_columns)) {
                                        $status_class = 'status-error';
                                        $status_text = 'subcategory_id column missing';
                                        $needs_fix = false; // Can't fix if column doesn't exist
                                    } elseif ($assigned_subcategory_id <= 0 || $assigned_subcategory_id === null) {
                                        $status_class = 'status-error';
                                        $status_text = 'Missing/Invalid Subcategory ID';
                                        $needs_fix = true;
                                    } elseif (!isset($subcategories[$assigned_subcategory_id])) {
                                        $status_class = 'status-error';
                                        $status_text = 'Subcategory ID does not exist';
                                        $needs_fix = true;
                                    } elseif (isset($product['subcat_id']) && $product['subcat_id'] != $assigned_subcategory_id) {
                                        $status_class = 'status-warning';
                                        $status_text = 'Subcategory mismatch';
                                        $needs_fix = true;
                                    }
                                ?>
                                    <tr>
                                        <td><?= $product_id ?></td>
                                        <td><?= $product_name ?></td>
                                        <td><?= $product_category ?></td>
                                        <td><?= $assigned_subcategory_id ?: 'NULL/0' ?></td>
                                        <td><?= $assigned_subcategory_name ?></td>
                                        <td class="<?= $status_class ?>">
                                            <i class="fas fa-<?= $needs_fix ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                                            <?= $status_text ?>
                                        </td>
                                        <td>
                                            <?php if (in_array('subcategory_id', $products_columns)): ?>
                                                <form method="post" class="fix-form" onsubmit="return confirm('Are you sure you want to change this product\'s subcategory?');">
                                                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                                    <select name="subcategory_id" required>
                                                        <option value="">Select Subcategory</option>
                                                        <?php 
                                                        // Group subcategories by category in dropdown
                                                        $current_cat = '';
                                                        foreach ($subcategories as $id => $sub_data): 
                                                            if ($current_cat != $sub_data['category_name']):
                                                                if ($current_cat != ''): ?>
                                                                    </optgroup>
                                                                <?php endif; ?>
                                                                <optgroup label="<?= htmlspecialchars($sub_data['category_name']) ?>">
                                                                <?php 
                                                                $current_cat = $sub_data['category_name'];
                                                            endif;
                                                            $selected = ($assigned_subcategory_id == $id) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $id ?>" <?= $selected ?>><?= htmlspecialchars($sub_data['name']) ?></option>
                                                        <?php endforeach; ?>
                                                        </optgroup>
                                                    </select>
                                                    <button type="submit" name="fix_product">Fix</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #999;">Column missing</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="message info">No products found or an error occurred.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

