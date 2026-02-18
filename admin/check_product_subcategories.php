<?php
/**
 * Diagnostic script to check product subcategory assignments
 * Run this to see which products have incorrect subcategory_id values
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

// Get all products
$products_query = mysqli_query($conn, "SELECT id, name, subcategory_id, category_id FROM products WHERE category_id = $embroidery_id ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Subcategory Checker</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #2fc7b4; color: white; }
        .error { background: #ffebee; }
        .warning { background: #fff3e0; }
        .success { background: #e8f5e9; }
    </style>
</head>
<body>
    <h1>Product Subcategory Assignment Checker</h1>
    <p>This page shows all embroidery products and their subcategory assignments.</p>
    
    <table>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Subcategory ID</th>
            <th>Subcategory Name</th>
            <th>Status</th>
        </tr>
        <?php while ($product = mysqli_fetch_assoc($products_query)): 
            $sub_id = (int)($product['subcategory_id'] ?? 0);
            $status_class = '';
            $status_text = '';
            
            if ($sub_id <= 0 || $sub_id === null) {
                $status_class = 'error';
                $status_text = 'NO SUBCATEGORY ASSIGNED';
            } elseif (!isset($subcategories[$sub_id])) {
                $status_class = 'error';
                $status_text = 'INVALID SUBCATEGORY ID';
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
        </tr>
        <?php endwhile; ?>
    </table>
    
    <h2>Subcategory Reference</h2>
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
</body>
</html>


