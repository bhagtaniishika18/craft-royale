<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check products table structure
$columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
$columns = [];
while ($col = mysqli_fetch_assoc($columns_query)) {
    $columns[] = $col;
}

// Check for sample products
$products_check = mysqli_query($conn, "SELECT * FROM products LIMIT 1");
$has_products = mysqli_num_rows($products_check) > 0;
$sample_product = $has_products ? mysqli_fetch_assoc($products_check) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Products Table - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .info-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
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
            color: #fff;
        }
        .required { color: #dc3545; font-weight: bold; }
        .optional { color: #666; }
        .missing { background: #fff3cd; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo-icon">✦</div>
                    <h2>Craft <span>Royale</span></h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="fix_products_table.php" class="nav-item active">
                    <i class="fas fa-tools"></i>
                    <span>Fix Products Table</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Check Products Table Structure</h1>
                    <p class="welcome-text">View current table structure and sample data</p>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="info-box">
                    <h2>Table Columns (<?= count($columns) ?> total)</h2>
                    <table>
                        <tr>
                            <th>Column Name</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Default</th>
                            <th>Status</th>
                        </tr>
                        <?php 
                        $required_cols = ['id', 'category_id', 'name', 'price', 'image'];
                        foreach ($columns as $col): 
                            $is_required = in_array($col['Field'], $required_cols);
                            $is_missing_required = $is_required && !in_array($col['Field'], array_column($columns, 'Field'));
                        ?>
                            <tr class="<?= $is_missing_required ? 'missing' : '' ?>">
                                <td>
                                    <?= htmlspecialchars($col['Field']) ?>
                                    <?php if ($is_required): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($col['Type']) ?></td>
                                <td><?= $col['Null'] ?></td>
                                <td><?= htmlspecialchars($col['Default'] ?? 'NULL') ?></td>
                                <td>
                                    <?php if ($is_required): ?>
                                        <span style="color: #28a745;">✓ Required</span>
                                    <?php else: ?>
                                        <span class="optional">Optional</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <?php if ($sample_product): ?>
                    <div class="info-box">
                        <h2>Sample Product Data</h2>
                        <table>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                            <?php foreach ($sample_product as $key => $value): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                    <td>
                                        <?php 
                                        if ($key == 'image' || $key == 'images'): 
                                            if (!empty($value)) {
                                                echo htmlspecialchars($value);
                                                if ($key == 'image') {
                                                    $img_path = '../uploads/products/' . $value;
                                                    echo '<br><small style="color: ' . (file_exists($img_path) ? '#28a745' : '#dc3545') . ';">';
                                                    echo file_exists($img_path) ? '✓ File exists' : '✗ File not found';
                                                    echo '</small>';
                                                }
                                            } else {
                                                echo '<em style="color: #999;">(empty)</em>';
                                            }
                                        else: 
                                            if ($value === null) {
                                                echo '<em style="color: #999;">NULL</em>';
                                            } elseif (is_array($value) || is_object($value)) {
                                                echo '<em style="color: #999;">' . htmlspecialchars(json_encode($value)) . '</em>';
                                            } else {
                                                echo htmlspecialchars((string)$value); 
                                            }
                                        endif; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="info-box">
                        <p style="color: #999; text-align: center; padding: 20px;">
                            <i class="fas fa-info-circle"></i> No products found in database. Add your first product!
                        </p>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h2>Quick Actions</h2>
                    <p>
                        <a href="fix_products_table.php" class="action-btn" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; margin-right: 10px;">
                            <i class="fas fa-tools"></i> Fix Products Table
                        </a>
                        <a href="add_product.php" class="action-btn" style="display: inline-block; padding: 12px 24px; background: #999; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

