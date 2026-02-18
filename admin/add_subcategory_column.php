<?php
/**
 * Script to add subcategory_id column to products table if it doesn't exist
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$messages = [];
$errors = [];

// Check if subcategory_id column exists
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Handle form submission
if (isset($_POST['add_column'])) {
    if (!in_array('subcategory_id', $products_columns)) {
        // Determine position - after category_id if it exists, otherwise after id
        $after_column = in_array('category_id', $products_columns) ? 'AFTER category_id' : 'AFTER id';
        
        // Add subcategory_id column
        $alter_query = "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL $after_column";
        if (mysqli_query($conn, $alter_query)) {
            $messages[] = "Successfully added 'subcategory_id' column to products table!";
            
            // Also add index for better performance (ignore error if already exists)
            $index_query = "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id)";
            @mysqli_query($conn, $index_query);
            
            // Refresh to check again
            header("Location: add_subcategory_column.php?success=1");
            exit();
        } else {
            $errors[] = "Error adding column: " . mysqli_error($conn);
            $errors[] = "SQL Error: " . mysqli_error($conn);
        }
    } else {
        $messages[] = "The 'subcategory_id' column already exists in the products table.";
    }
}

// Handle adding column via GET parameter (for direct SQL execution)
if (isset($_GET['add_column_direct']) && $_GET['add_column_direct'] == '1') {
    if (!in_array('subcategory_id', $products_columns)) {
        $after_column = in_array('category_id', $products_columns) ? 'AFTER category_id' : 'AFTER id';
        $alter_query = "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL $after_column";
        if (mysqli_query($conn, $alter_query)) {
            @mysqli_query($conn, "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id)");
            header("Location: add_subcategory_column.php?success=1");
            exit();
        }
    }
}

if (!in_array('subcategory_id', $products_columns)) {
    // Column doesn't exist - show message
} else {
    $messages[] = "The 'subcategory_id' column already exists in the products table.";
}

// Check if category_id exists
if (!in_array('category_id', $products_columns)) {
    $errors[] = "Warning: 'category_id' column also doesn't exist. You may need to run the full database schema.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Subcategory Column - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }
        .columns-list {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .columns-list h3 {
            margin-top: 0;
        }
        .columns-list ul {
            list-style: none;
            padding: 0;
        }
        .columns-list li {
            padding: 5px 0;
        }
        .code-block {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Add Subcategory Column</h1>
                    <p class="welcome-text">Add missing subcategory_id column to products table</p>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="container">
                    <h2>Database Column Check</h2>
                    
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
                    
                    <div class="columns-list">
                        <h3>Current Columns in Products Table:</h3>
                        <ul>
                            <?php 
                            $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
                            $has_subcategory = false;
                            $has_category = false;
                            while ($col = mysqli_fetch_assoc($columns_check)): 
                                if ($col['Field'] == 'subcategory_id') $has_subcategory = true;
                                if ($col['Field'] == 'category_id') $has_category = true;
                            ?>
                                <li>
                                    <i class="fas fa-check-circle" style="color: #4caf50;"></i> 
                                    <strong><?= htmlspecialchars($col['Field']) ?></strong> 
                                    (<?= htmlspecialchars($col['Type']) ?>)
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    
                    <?php 
                    // Re-check columns after potential update
                    $columns_check2 = mysqli_query($conn, "SHOW COLUMNS FROM products");
                    $products_columns2 = [];
                    while ($col = mysqli_fetch_assoc($columns_check2)) {
                        $products_columns2[] = $col['Field'];
                    }
                    $has_subcategory_now = in_array('subcategory_id', $products_columns2);
                    ?>
                    
                    <?php if (!$has_subcategory_now): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Missing Column:</strong> The 'subcategory_id' column is missing from the products table.
                        </div>
                        <p><strong>Option 1:</strong> Use the form below to add the column</p>
                        <form method="post" action="" onsubmit="return confirm('This will add the subcategory_id column to your products table. Continue?');">
                            <button type="submit" name="add_column" class="btn">
                                <i class="fas fa-plus-circle"></i> Add subcategory_id Column (Form)
                            </button>
                        </form>
                        
                        <p style="margin-top: 20px;"><strong>Option 2:</strong> Or click this link to add it directly</p>
                        <a href="?add_column_direct=1" class="btn" onclick="return confirm('This will add the subcategory_id column. Continue?');">
                            <i class="fas fa-plus-circle"></i> Add subcategory_id Column (Direct)
                        </a>
                        
                        <p style="margin-top: 20px;"><strong>Option 3:</strong> Run this SQL manually in phpMyAdmin:</p>
                        <div class="code-block" style="background: #2b2b2b; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; margin-top: 10px;">
                            <?php 
                            $after_col = in_array('category_id', $products_columns) ? 'AFTER category_id' : 'AFTER id';
                            echo "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL $after_col;\n";
                            echo "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id);";
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i>
                            <strong>All Required Columns Exist!</strong> You can now use the verify_product_subcategories.php page to fix product assignments.
                        </div>
                        <a href="verify_product_subcategories.php" class="btn">
                            <i class="fas fa-arrow-right"></i> Go to Verify Product Subcategories
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!$has_category): ?>
                        <div class="message error" style="margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Missing Column:</strong> The 'category_id' column is also missing. You may need to run the full database schema file.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

