<?php
/**
 * SIMPLE DIRECT FIX - No complex logic, just add the column
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check if column exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM products WHERE Field = 'subcategory_id'");
$exists = ($check && mysqli_num_rows($check) > 0);

if (!$exists) {
    // SIMPLE: Just add the column without any AFTER clause
    $sql = "ALTER TABLE products ADD COLUMN subcategory_id int(11) DEFAULT NULL";
    
    if (mysqli_query($conn, $sql)) {
        // Add index
        @mysqli_query($conn, "ALTER TABLE products ADD INDEX idx_subcategory_id (subcategory_id)");
        $result = "SUCCESS";
        $message = "Column added successfully!";
    } else {
        $result = "ERROR";
        $message = mysqli_error($conn);
    }
} else {
    $result = "EXISTS";
    $message = "Column already exists!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Fix - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        body { font-family: Arial; }
        .box {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success { background: #e8f5e9; border: 2px solid #4caf50; color: #2e7d32; }
        .error { background: #ffebee; border: 2px solid #f44336; color: #c62828; }
        .exists { background: #e3f2fd; border: 2px solid #2196f3; color: #1565c0; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 10px;
        }
        .code {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <div class="dashboard-content">
                <div class="box <?= strtolower($result) ?>">
                    <?php if ($result == "SUCCESS"): ?>
                        <i class="fas fa-check-circle icon" style="color: #4caf50;"></i>
                        <h2>SUCCESS!</h2>
                        <p style="font-size: 18px;"><?= htmlspecialchars($message) ?></p>
                        <p>The 'subcategory_id' column has been added to your products table.</p>
                        <a href="verify_product_subcategories.php" class="btn">
                            <i class="fas fa-arrow-right"></i> Fix Product Assignments Now
                        </a>
                    <?php elseif ($result == "EXISTS"): ?>
                        <i class="fas fa-info-circle icon" style="color: #2196f3;"></i>
                        <h2>Column Already Exists</h2>
                        <p style="font-size: 18px;"><?= htmlspecialchars($message) ?></p>
                        <a href="verify_product_subcategories.php" class="btn">
                            <i class="fas fa-arrow-right"></i> Go to Verify Product Subcategories
                        </a>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle icon" style="color: #f44336;"></i>
                        <h2>Error</h2>
                        <p style="font-size: 18px;"><?= htmlspecialchars($message) ?></p>
                        <p><strong>Please run this SQL manually in phpMyAdmin:</strong></p>
                        <div class="code">
ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL;<br><br>
ALTER TABLE `products` ADD INDEX `idx_subcategory_id` (`subcategory_id`);
                        </div>
                        <a href="SIMPLE_FIX.php" class="btn">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

