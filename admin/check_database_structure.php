<?php
/**
 * Diagnostic script to check database structure and product assignments
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check products table structure
$columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_query)) {
    $products_columns[] = $col['Field'];
}

// Get a sample product to check
$sample_product = mysqli_query($conn, "SELECT * FROM products LIMIT 1");
$sample_data = null;
if ($sample_product && mysqli_num_rows($sample_product) > 0) {
    $sample_data = mysqli_fetch_assoc($sample_product);
}

// Get all products with their subcategory info
$products_list = [];
if (in_array('subcategory_id', $products_columns)) {
    $products_query = "SELECT p.id, p.name, p.subcategory_id, s.subcategory_name 
                       FROM products p 
                       LEFT JOIN subcategories s ON p.subcategory_id = s.id 
                       LIMIT 10";
    $products_result = mysqli_query($conn, $products_query);
    if ($products_result) {
        while ($p = mysqli_fetch_assoc($products_result)) {
            $products_list[] = $p;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Check - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .section h3 {
            margin-top: 0;
            color: #2fc7b4;
        }
        .column-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .column-item {
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #2fc7b4;
        }
        .column-item.missing {
            border-left-color: #f44336;
            background: #ffebee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2fc7b4;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .code-block {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Database Structure Check</h1>
                    <p class="welcome-text">Diagnostic information about your database</p>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="container">
                    <div class="section">
                        <h3>Products Table Columns</h3>
                        <p>Total columns: <strong><?= count($products_columns) ?></strong></p>
                        <div class="column-list">
                            <?php 
                            $required_columns = ['id', 'name', 'category_id', 'subcategory_id', 'price', 'image', 'status'];
                            foreach ($required_columns as $req_col): 
                                $exists = in_array($req_col, $products_columns);
                            ?>
                                <div class="column-item <?= $exists ? '' : 'missing' ?>">
                                    <i class="fas fa-<?= $exists ? 'check-circle' : 'times-circle' ?>" 
                                       style="color: <?= $exists ? '#4caf50' : '#f44336' ?>;"></i>
                                    <strong><?= $req_col ?></strong>
                                    <?= $exists ? '<span style="color: #4caf50;">✓</span>' : '<span style="color: #f44336;">✗ Missing</span>' ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <h4 style="margin-top: 20px;">All Columns:</h4>
                        <div class="code-block">
                            <?= implode(', ', $products_columns) ?>
                        </div>
                    </div>

                    <?php if ($sample_data): ?>
                    <div class="section">
                        <h3>Sample Product Data</h3>
                        <div class="code-block">
                            <?php print_r($sample_data); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($products_list)): ?>
                    <div class="section">
                        <h3>Sample Products with Subcategory Info</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Subcategory ID</th>
                                    <th>Subcategory Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products_list as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= $p['subcategory_id'] ?? 'NULL' ?></td>
                                    <td><?= htmlspecialchars($p['subcategory_name'] ?? 'N/A') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <div class="section">
                        <h3>Quick Actions</h3>
                        <?php if (!in_array('subcategory_id', $products_columns)): ?>
                            <a href="add_subcategory_column.php" class="btn">
                                <i class="fas fa-plus-circle"></i> Add subcategory_id Column
                            </a>
                        <?php else: ?>
                            <a href="verify_product_subcategories.php" class="btn">
                                <i class="fas fa-check-circle"></i> Verify Product Subcategories
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

