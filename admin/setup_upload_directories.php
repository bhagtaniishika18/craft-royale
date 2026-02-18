<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$base_dir = dirname(__DIR__);
$upload_dirs = [
    'uploads/products',
    'uploads/categories',
    'uploads/reviews'
];

$results = [];
$all_success = true;

foreach ($upload_dirs as $dir) {
    $full_path = $base_dir . '/' . $dir;
    
    if (!file_exists($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            $results[] = ['dir' => $dir, 'status' => 'created', 'message' => 'Directory created successfully'];
        } else {
            $results[] = ['dir' => $dir, 'status' => 'error', 'message' => 'Failed to create directory'];
            $all_success = false;
        }
    } else {
        $results[] = ['dir' => $dir, 'status' => 'exists', 'message' => 'Directory already exists'];
    }
    
    // Check if directory is writable
    if (file_exists($full_path) && !is_writable($full_path)) {
        $results[] = ['dir' => $dir, 'status' => 'warning', 'message' => 'Directory exists but is not writable. Please set permissions to 755 or 777'];
        $all_success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Upload Directories - Craft Royale</title>
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
        .result-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .result-item.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .result-item.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .result-item.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .result-item.exists {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .directory-structure {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-family: monospace;
        }
        .directory-structure h3 {
            margin-top: 0;
            color: #2b2b2b;
        }
        .directory-structure ul {
            list-style: none;
            padding-left: 20px;
        }
        .directory-structure li {
            margin: 5px 0;
            color: #555;
        }
        .directory-structure li:before {
            content: "📁 ";
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
                <a href="manage_categories.php" class="nav-item">
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
                    <h1>Setup Upload Directories</h1>
                    <p class="welcome-text">Create directories for storing product and category images</p>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="result-box">
                    <h2>Directory Setup Results</h2>
                    
                    <?php foreach ($results as $result): ?>
                        <div class="result-item <?= $result['status'] ?>">
                            <strong><?= htmlspecialchars($result['dir']) ?></strong><br>
                            <?= htmlspecialchars($result['message']) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($all_success): ?>
                        <div class="result-item success" style="margin-top: 20px;">
                            <i class="fas fa-check-circle"></i> <strong>All directories are ready!</strong>
                        </div>
                    <?php else: ?>
                        <div class="result-item warning" style="margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Some issues detected. Please check the messages above.</strong>
                        </div>
                    <?php endif; ?>
                    
                    <div class="directory-structure">
                        <h3>Directory Structure:</h3>
                        <ul>
                            <li><strong>uploads/products/</strong> - Store product images here</li>
                            <li><strong>uploads/categories/</strong> - Store category images here</li>
                            <li><strong>uploads/reviews/</strong> - Store review images here</li>
                        </ul>
                        <p style="margin-top: 15px; color: #666;">
                            <strong>Base Path:</strong> <?= htmlspecialchars($base_dir) ?>
                        </p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <a href="manage_categories.php" class="action-btn" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-arrow-left"></i> Back to Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>










