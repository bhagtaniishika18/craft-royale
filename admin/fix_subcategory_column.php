<?php
/**
 * Simple one-click script to add subcategory_id column
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$success = false;
$error = '';

// Check if column exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'subcategory_id'");
$column_exists = ($check && mysqli_num_rows($check) > 0);

if (!$column_exists) {
    // Try to add the column
    // First, find where to place it
    $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
    $has_category_id = false;
    while ($col = mysqli_fetch_assoc($columns_check)) {
        if ($col['Field'] == 'category_id') {
            $has_category_id = true;
            break;
        }
    }
    
    $after_clause = $has_category_id ? 'AFTER category_id' : 'AFTER id';
    
    // Add the column
    $sql = "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL $after_clause";
    if (mysqli_query($conn, $sql)) {
        $success = true;
        // Try to add index
        @mysqli_query($conn, "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id)");
    } else {
        $error = mysqli_error($conn);
    }
} else {
    $success = true; // Column already exists
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Subcategory Column - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-box {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #4caf50;
            margin: 20px 0;
        }
        .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #f44336;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }
        .icon-large {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .success-box .icon-large { color: #4caf50; }
        .error-box .icon-large { color: #f44336; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <div class="dashboard-content">
                <div class="container">
                    <?php if ($success): ?>
                        <div class="success-box">
                            <i class="fas fa-check-circle icon-large"></i>
                            <h2>Success!</h2>
                            <?php if ($column_exists): ?>
                                <p>The 'subcategory_id' column already exists in your products table.</p>
                            <?php else: ?>
                                <p>The 'subcategory_id' column has been successfully added to your products table!</p>
                            <?php endif; ?>
                            <p style="margin-top: 20px;">You can now assign products to subcategories.</p>
                        </div>
                        <a href="verify_product_subcategories.php" class="btn">
                            <i class="fas fa-arrow-right"></i> Go to Verify Product Subcategories
                        </a>
                    <?php else: ?>
                        <div class="error-box">
                            <i class="fas fa-exclamation-triangle icon-large"></i>
                            <h2>Error Adding Column</h2>
                            <p><?= htmlspecialchars($error) ?></p>
                            <p style="margin-top: 20px;">Please try running this SQL manually in phpMyAdmin:</p>
                            <div style="background: #2b2b2b; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; text-align: left; margin-top: 15px;">
                                ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL AFTER category_id;<br>
                                ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id);
                            </div>
                        </div>
                        <a href="fix_subcategory_column.php" class="btn">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px;">
                        <a href="check_database_structure.php" class="btn" style="background: #666;">
                            <i class="fas fa-info-circle"></i> Check Database Structure
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

