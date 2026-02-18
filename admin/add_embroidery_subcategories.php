<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Get Embroidery category ID
$embroidery_query = mysqli_query($conn, "SELECT id FROM categories WHERE category_name = 'Embroidery' LIMIT 1");
$embroidery = mysqli_fetch_assoc($embroidery_query);

if (!$embroidery) {
    die("Error: Embroidery category not found. Please create it first.");
}

$category_id = $embroidery['id'];

// All subcategories for Embroidery module
$subcategories = [
    // METALLIC WIRES
    ['name' => 'French Wire / Dabka', 'slug' => 'french-wire-dabka'],
    ['name' => 'Bullion Wire/Nakshi', 'slug' => 'bullion-wire-nakshi'],
    ['name' => 'Gijai / Gimp / Stiff', 'slug' => 'gijai-gimp-stiff'],
    ['name' => 'Mukaish Metal Strip', 'slug' => 'mukaish-metal-strip'],
    
    // METALLIC THREADS/CORD
    ['name' => 'Zari Threads', 'slug' => 'zari-threads'],
    ['name' => 'Badla Flat Metallic Threads', 'slug' => 'badla-flat-metallic-threads'],
    ['name' => 'Badla Dori/ Metallic Braided Cord', 'slug' => 'badla-dori-metallic-braided-cord'],
    
    // THREADS/CORD
    ['name' => 'Cotton Threads', 'slug' => 'cotton-threads'],
    ['name' => 'Crochet Cotton Threads', 'slug' => 'crochet-cotton-threads'],
    ['name' => 'Art Silk Threads', 'slug' => 'art-silk-threads'],
    ['name' => 'Nylon Threads', 'slug' => 'nylon-threads'],
    ['name' => 'Sewing Threads', 'slug' => 'sewing-threads'],
];

$added = 0;
$skipped = 0;
$errors = [];

foreach ($subcategories as $sub) {
    $name = mysqli_real_escape_string($conn, $sub['name']);
    $slug = mysqli_real_escape_string($conn, $sub['slug']);
    
    // Check if subcategory already exists
    $check = mysqli_query($conn, "SELECT id FROM subcategories WHERE subcategory_slug = '$slug' AND category_id = '$category_id'");
    
    if (mysqli_num_rows($check) > 0) {
        $skipped++;
    } else {
        $insert = mysqli_query($conn, "INSERT INTO subcategories (category_id, subcategory_name, subcategory_slug) VALUES ('$category_id', '$name', '$slug')");
        if ($insert) {
            $added++;
        } else {
            $errors[] = "Error adding {$sub['name']}: " . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Embroidery Subcategories - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .result-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin: 20px 0;
        }
        .success { color: #2fa76b; }
        .error { color: #dc3545; }
        .info { color: #2fc7b4; }
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
                <a href="add_subcategory.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Add Subcategory</span>
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
                    <h1>Add Embroidery Subcategories</h1>
                    <p class="welcome-text">Bulk add all embroidery subcategories</p>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="result-box">
                    <h2>Results:</h2>
                    <p class="success"><i class="fas fa-check-circle"></i> Added: <?= $added ?> subcategories</p>
                    <p class="info"><i class="fas fa-info-circle"></i> Skipped (already exist): <?= $skipped ?> subcategories</p>
                    <?php if (!empty($errors)): ?>
                        <div class="error">
                            <strong>Errors:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px;">
                        <a href="add_subcategory.php" class="btn-edit" style="display: inline-block; text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Back to Add Subcategory
                        </a>
                        <a href="add_product.php" class="btn-edit" style="display: inline-block; text-decoration: none; margin-left: 10px;">
                            <i class="fas fa-box"></i> Add Product
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>










