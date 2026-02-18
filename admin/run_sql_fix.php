<?php
/**
 * Direct SQL execution to add subcategory_id column
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$messages = [];
$errors = [];

// Read and execute the SQL file
$sql_file = __DIR__ . '/add_subcategory_column_direct.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($statements as $statement) {
        // Skip comments and empty statements
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        // Skip SET and PREPARE/EXECUTE statements - we'll do it manually
        if (stripos($statement, 'SET @') === 0 || 
            stripos($statement, 'PREPARE') === 0 || 
            stripos($statement, 'EXECUTE') === 0 || 
            stripos($statement, 'DEALLOCATE') === 0 ||
            stripos($statement, 'SELECT') === 0) {
            continue;
        }
        
        // Execute ALTER TABLE statements
        if (stripos($statement, 'ALTER TABLE') === 0) {
            if (mysqli_query($conn, $statement)) {
                $messages[] = "Executed: " . substr($statement, 0, 50) . "...";
            } else {
                $errors[] = "Error: " . mysqli_error($conn) . " - " . substr($statement, 0, 50);
            }
        }
    }
}

// Direct approach - just try to add the column
$check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'subcategory_id'");
if (!($check && mysqli_num_rows($check) > 0)) {
    // Column doesn't exist, add it
    // First, check what columns exist to determine placement
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM products");
    $column_list = [];
    $found_category = false;
    $found_id = false;
    
    while ($col = mysqli_fetch_assoc($cols)) {
        $column_list[] = $col['Field'];
        if ($col['Field'] == 'category_id') {
            $found_category = true;
        }
        if ($col['Field'] == 'id') {
            $found_id = true;
        }
    }
    
    // Build ALTER query - don't use AFTER if category_id doesn't exist
    $alter_query = "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL";
    
    // DON'T use AFTER clause - just add at the end
    // This avoids errors if the reference column doesn't exist
    
    if (mysqli_query($conn, $alter_query)) {
        $messages[] = "✓ Successfully added 'subcategory_id' column!";
        
        // Add index (ignore error if already exists)
        @mysqli_query($conn, "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id)");
        $messages[] = "✓ Added index on subcategory_id";
    } else {
        $errors[] = "✗ Error: " . mysqli_error($conn);
        $errors[] = "✗ Query was: " . $alter_query;
    }
} else {
    $messages[] = "✓ Column 'subcategory_id' already exists!";
}

// Verify
$verify = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'subcategory_id'");
$column_exists = ($verify && mysqli_num_rows($verify) > 0);
?>
<!DOCTYPE html>
<html>
<head>
    <title>SQL Fix - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .code {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 15px 0;
            overflow-x: auto;
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
                    <p class="welcome-text">Direct SQL execution to add missing column</p>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="container">
                    <h2>Execution Results</h2>
                    
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
                    
                    <?php if ($column_exists): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i> 
                            <strong>SUCCESS!</strong> The 'subcategory_id' column now exists in your products table.
                        </div>
                        <a href="verify_product_subcategories.php" class="btn">
                            <i class="fas fa-arrow-right"></i> Go to Verify Product Subcategories
                        </a>
                    <?php else: ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Column still missing.</strong> Please run this SQL manually in phpMyAdmin:
                        </div>
                        <div class="code">
-- Try this first (without AFTER clause):<br>
ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL;<br>
ALTER TABLE `products` ADD INDEX `idx_subcategory_id` (`subcategory_id`);<br><br>
-- Or if you know your table structure, use AFTER:<br>
-- ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL AFTER `id`;
                        </div>
                        <a href="run_sql_fix.php" class="btn">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px;">
                        <h3>Manual SQL (if above didn't work):</h3>
                        <p><strong>Since category_id doesn't exist, use this SQL (without AFTER clause):</strong></p>
                        <div class="code">
ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL;<br>
ALTER TABLE `products` ADD INDEX `idx_subcategory_id` (`subcategory_id`);
                        </div>
                        <p style="margin-top: 15px; color: #666;">
                            <strong>Note:</strong> The column will be added at the end of the table. You can move it later if needed.
                        </p>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #fff3e0; border-radius: 5px;">
                        <h4 style="margin-top: 0;">Current Products Table Columns:</h4>
                        <?php
                        $cols_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
                        if ($cols_check) {
                            echo '<div style="font-family: monospace; font-size: 12px;">';
                            while ($col = mysqli_fetch_assoc($cols_check)) {
                                echo htmlspecialchars($col['Field']) . ' (' . htmlspecialchars($col['Type']) . ')<br>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

