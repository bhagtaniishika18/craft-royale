<?php
/**
 * Script to check and fix product subcategory assignments
 * This will show products that might have incorrect subcategory_id values
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Get all embroidery subcategories
$embroidery_cat = mysqli_query($conn, "SELECT id FROM categories WHERE category_name = 'Embroidery' LIMIT 1");
$embroidery_id = mysqli_fetch_assoc($embroidery_cat)['id'] ?? 0;

$subcategories_query = mysqli_query($conn, "SELECT id, subcategory_name FROM subcategories WHERE category_id = $embroidery_id ORDER BY subcategory_name");
$subcategories = [];
while ($sub = mysqli_fetch_assoc($subcategories_query)) {
    $subcategories[$sub['id']] = $sub['subcategory_name'];
}

// Get all embroidery products
$products_query = mysqli_query($conn, "SELECT id, name, subcategory_id, category_id FROM products WHERE category_id = $embroidery_id ORDER BY name");
$products = [];
while ($product = mysqli_fetch_assoc($products_query)) {
    $products[] = $product;
}

// Handle form submission to fix a product
if (isset($_POST['fix_product'])) {
    $product_id = (int)$_POST['product_id'];
    $new_subcategory_id = (int)$_POST['subcategory_id'];
    
    $update_query = "UPDATE products SET subcategory_id = $new_subcategory_id WHERE id = $product_id";
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Product updated successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
    
    // Refresh page to show updated data
    header("Location: fix_product_subcategories.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Product Subcategories</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2fc7b4; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #2fc7b4; color: white; font-weight: 600; }
        .error { background: #ffebee; }
        .warning { background: #fff3e0; }
        .success { background: #e8f5e9; }
        .fix-form { display: inline-block; }
        select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 6px 15px; background: #2fc7b4; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2fa76b; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #4caf50; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #f44336; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fix Product Subcategory Assignments</h1>
        <p class="subtitle">Check and fix products that have incorrect subcategory_id values</p>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <h2>All Embroidery Products</h2>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Current Subcategory ID</th>
                <th>Current Subcategory Name</th>
                <th>Status</th>
                <th>Fix</th>
            </tr>
            <?php foreach ($products as $product): 
                $sub_id = (int)($product['subcategory_id'] ?? 0);
                $status_class = '';
                $status_text = '';
                
                if ($sub_id <= 0 || $sub_id === null) {
                    $status_class = 'error';
                    $status_text = 'NO SUBCATEGORY';
                } elseif (!isset($subcategories[$sub_id])) {
                    $status_class = 'error';
                    $status_text = 'INVALID ID';
                } else {
                    $status_class = 'success';
                    $status_text = 'OK';
                }
            ?>
            <tr class="<?= $status_class ?>">
                <td><?= $product['id'] ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= $sub_id ?: 'NULL/0' ?></td>
                <td><?= isset($subcategories[$sub_id]) ? htmlspecialchars($subcategories[$sub_id]) : 'NOT FOUND' ?></td>
                <td><?= $status_text ?></td>
                <td>
                    <form method="post" class="fix-form" onsubmit="return confirm('Are you sure you want to change this product\'s subcategory?');">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <select name="subcategory_id" required>
                            <option value="">Select Subcategory</option>
                            <?php foreach ($subcategories as $id => $name): 
                                $selected = ($sub_id == $id) ? 'selected' : '';
                            ?>
                                <option value="<?= $id ?>" <?= $selected ?>><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="fix_product">Fix</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2 style="margin-top: 40px;">Subcategory Reference</h2>
        <table>
            <tr>
                <th>Subcategory ID</th>
                <th>Subcategory Name</th>
            </tr>
            <?php foreach ($subcategories as $id => $name): ?>
            <tr>
                <td><?= $id ?></td>
                <td><?= htmlspecialchars($name) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>

