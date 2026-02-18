<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$messages = [];
$errors = [];

// Check if products table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($table_check) == 0) {
    $errors[] = "Products table does not exist. Please run the database schema file first.";
} else {
    // Get current columns
    $columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($columns_query)) {
        $existing_columns[] = $col['Field'];
    }
    
    $messages[] = "Found " . count($existing_columns) . " columns in products table.";
    $messages[] = "Existing columns: " . implode(", ", $existing_columns);
    
    // Define required columns
    $required_columns = [
        'category_id' => "int(11) NOT NULL",
        'subcategory_id' => "int(11) DEFAULT NULL",
        'name' => "varchar(255) NOT NULL",
        'slug' => "varchar(255) NOT NULL DEFAULT ''",
        'sku' => "varchar(100) DEFAULT NULL",
        'mrp' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'price' => "decimal(10,2) NOT NULL",
        'discount_percent' => "int(11) DEFAULT 0",
        'description' => "text",
        'short_description' => "text",
        'image' => "varchar(255) NOT NULL",
        'images' => "text DEFAULT NULL",
        'stock' => "int(11) DEFAULT 0",
        'status' => "enum('active','inactive','out_of_stock') DEFAULT 'active'",
        'country_of_origin' => "varchar(100) DEFAULT NULL",
        'color' => "varchar(50) DEFAULT NULL",
        'size' => "varchar(50) DEFAULT NULL",
        'material' => "varchar(100) DEFAULT NULL",
        'weight' => "varchar(50) DEFAULT NULL",
        'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    // Check which columns are missing
    $missing_columns = [];
    foreach ($required_columns as $col_name => $col_def) {
        if (!in_array($col_name, $existing_columns)) {
            $missing_columns[$col_name] = $col_def;
        }
    }
    
    // Add missing columns
    if (!empty($missing_columns)) {
        $messages[] = "Found " . count($missing_columns) . " missing columns.";
        
        foreach ($missing_columns as $col_name => $col_def) {
            // Determine position
            $after = '';
            if ($col_name == 'subcategory_id') $after = 'AFTER category_id';
            elseif ($col_name == 'slug') $after = 'AFTER name';
            elseif ($col_name == 'sku') $after = 'AFTER slug';
            elseif ($col_name == 'mrp') $after = 'AFTER sku';
            elseif ($col_name == 'discount_percent') $after = 'AFTER price';
            elseif ($col_name == 'short_description') $after = 'AFTER description';
            elseif ($col_name == 'images') $after = 'AFTER image';
            elseif ($col_name == 'stock') $after = 'AFTER images';
            elseif ($col_name == 'status') $after = 'AFTER stock';
            elseif ($col_name == 'country_of_origin') $after = 'AFTER status';
            elseif ($col_name == 'color') $after = 'AFTER country_of_origin';
            elseif ($col_name == 'size') $after = 'AFTER color';
            elseif ($col_name == 'material') $after = 'AFTER size';
            elseif ($col_name == 'weight') $after = 'AFTER material';
            elseif ($col_name == 'updated_at') $after = 'AFTER created_at';
            
            $alter_query = "ALTER TABLE products ADD COLUMN `$col_name` $col_def $after";
            
            if (mysqli_query($conn, $alter_query)) {
                $messages[] = "✓ Added column: $col_name";
            } else {
                $errors[] = "✗ Failed to add column $col_name: " . mysqli_error($conn);
            }
        }
    } else {
        $messages[] = "✓ All required columns exist!";
    }
    
    // Check if 'name' column exists, if not check for 'product_name'
    if (!in_array('name', $existing_columns) && in_array('product_name', $existing_columns)) {
        $rename_query = "ALTER TABLE products CHANGE COLUMN `product_name` `name` varchar(255) NOT NULL";
        if (mysqli_query($conn, $rename_query)) {
            $messages[] = "✓ Renamed product_name to name";
        } else {
            $errors[] = "✗ Failed to rename product_name: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Products Table - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .result-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin: 20px 0;
            max-width: 900px;
        }
        .message {
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .message.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .message.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .columns-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 13px;
        }
        .action-btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            margin-right: 10px;
        }
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
                <a href="manage_products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="add_product.php" class="nav-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Product</span>
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
                    <h1>Fix Products Table</h1>
                    <p class="welcome-text">Add missing columns to products table</p>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="result-box">
                    <h2>Database Fix Results</h2>
                    
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= strpos($msg, '✓') !== false ? 'success' : 'info' ?>">
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php foreach ($errors as $error): ?>
                        <div class="message error">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($errors) && !empty($messages)): ?>
                        <div class="message success" style="margin-top: 20px;">
                            <i class="fas fa-check-circle"></i> <strong>Products table is ready!</strong> You can now add products.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($existing_columns)): ?>
                        <div class="columns-list">
                            <strong>Current Products Table Columns:</strong><br>
                            <?= htmlspecialchars(implode(", ", $existing_columns)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px;">
                        <a href="add_product.php" class="action-btn">
                            <i class="fas fa-plus"></i> Try Adding Product Now
                        </a>
                        <a href="manage_products.php" class="action-btn">
                            <i class="fas fa-box"></i> Manage Products
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>










