<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$message = '';
$message_type = '';

// Check if subcategories table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'subcategories'");
if (mysqli_num_rows($table_check) == 0) {
    // Create table
    $create_table = "CREATE TABLE `subcategories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category_id` int(11) NOT NULL,
        `subcategory_name` varchar(255) NOT NULL,
        `subcategory_slug` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `category_id` (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $create_table)) {
        $message = "Subcategories table created successfully!";
        $message_type = "success";
    } else {
        $message = "Error creating table: " . mysqli_error($conn);
        $message_type = "error";
    }
} else {
    // Check if subcategory_slug column exists
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM subcategories LIKE 'subcategory_slug'");
    if (mysqli_num_rows($column_check) == 0) {
        // Add column
        $add_column = "ALTER TABLE `subcategories` ADD COLUMN `subcategory_slug` varchar(255) DEFAULT NULL AFTER `subcategory_name`";
        
        if (mysqli_query($conn, $add_column)) {
            $message = "subcategory_slug column added successfully!";
            $message_type = "success";
            
            // Update existing subcategories to have slugs
            $update_slugs = mysqli_query($conn, "UPDATE subcategories SET subcategory_slug = LOWER(REPLACE(REPLACE(REPLACE(subcategory_name, ' ', '-'), '/', '-'), '&', 'and')) WHERE subcategory_slug IS NULL OR subcategory_slug = ''");
            if ($update_slugs) {
                $message .= " Existing subcategories updated with slugs.";
            }
        } else {
            $message = "Error adding column: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = "subcategory_slug column already exists. Everything is ready!";
        $message_type = "info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Subcategories Table - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .result-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin: 20px 0;
            max-width: 800px;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #c3e6cb;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #f5c6cb;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #bee5eb;
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
                <a href="manage_categories.php" class="nav-item active">
                    <i class="fas fa-tags"></i>
                    <span>Manage Categories</span>
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
                    <h1>Fix Subcategories Table</h1>
                    <p class="welcome-text">Add missing column to subcategories table</p>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="result-box">
                    <h2>Result:</h2>
                    <div class="<?= $message_type ?>">
                        <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : ($message_type == 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <a href="manage_categories.php" class="action-btn">
                            <i class="fas fa-arrow-left"></i> Back to Manage Categories
                        </a>
                        <a href="add_subcategory.php" class="action-btn" style="margin-left: 10px;">
                            <i class="fas fa-plus"></i> Add Subcategory
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>










