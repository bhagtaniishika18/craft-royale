<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Handle delete
if (isset($_GET['delete_category'])) {
    $id = (int)$_GET['delete_category'];
    mysqli_query($conn, "DELETE FROM categories WHERE id='$id'");
    header("Location: manage_categories.php?success=category_deleted");
    exit();
}

if (isset($_GET['delete_subcategory'])) {
    $id = (int)$_GET['delete_subcategory'];
    mysqli_query($conn, "DELETE FROM subcategories WHERE id='$id'");
    header("Location: manage_categories.php?success=subcategory_deleted");
    exit();
}

// Fetch all categories
$categories_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$categories = [];
while ($cat = mysqli_fetch_assoc($categories_query)) {
    $categories[$cat['id']] = $cat;
}

// Fetch all subcategories grouped by category
$subcategories_query = mysqli_query($conn, "SELECT s.*, c.category_name 
                                           FROM subcategories s 
                                           LEFT JOIN categories c ON s.category_id = c.id 
                                           ORDER BY c.category_name, s.subcategory_name");
$subcategories_by_category = [];
while ($sub = mysqli_fetch_assoc($subcategories_query)) {
    if (!isset($subcategories_by_category[$sub['category_id']])) {
        $subcategories_by_category[$sub['category_id']] = [];
    }
    $subcategories_by_category[$sub['category_id']][] = $sub;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(47, 199, 180, 0.4);
        }

        .categories-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .category-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 30px;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(47, 199, 180, 0.1);
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #2b2b2b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #2fc7b4;
        }

        .category-list, .subcategory-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-item, .subcategory-item {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .category-item:hover, .subcategory-item:hover {
            border-color: #2fc7b4;
            background: rgba(47, 199, 180, 0.05);
        }

        .category-header, .subcategory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-name, .subcategory-name {
            font-size: 16px;
            font-weight: 600;
            color: #2b2b2b;
        }

        .subcategory-name {
            font-size: 14px;
            font-weight: 500;
            padding-left: 20px;
            position: relative;
        }

        .subcategory-name::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #2fc7b4;
        }

        .item-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: rgba(47, 199, 180, 0.1);
            color: #2fc7b4;
            border: 1px solid rgba(47, 199, 180, 0.3);
        }

        .btn-edit:hover {
            background: #2fc7b4;
            color: #fff;
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            background: #dc3545;
            color: #fff;
        }

        .subcategory-count {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 968px) {
            .categories-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Manage Categories & Subcategories</h1>
                    <p class="welcome-text">Organize your products with categories and subcategories</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <?php
                        if ($_GET['success'] == 'category_deleted') {
                            echo '<i class="fas fa-check-circle"></i> Category deleted successfully!';
                        } elseif ($_GET['success'] == 'subcategory_deleted') {
                            echo '<i class="fas fa-check-circle"></i> Subcategory deleted successfully!';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
                    <a href="add_category.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add Category
                    </a>
                    <a href="add_subcategory.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add Subcategory
                    </a>
                </div>

                <div class="categories-container">
                    <!-- Categories Section -->
                    <div class="category-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-folder"></i>
                                Categories
                            </h3>
                            <span class="subcategory-count"><?= count($categories) ?> total</span>
                        </div>

                        <?php if (!empty($categories)): ?>
                            <ul class="category-list">
                                <?php foreach ($categories as $cat): 
                                    $sub_count = isset($subcategories_by_category[$cat['id']]) ? count($subcategories_by_category[$cat['id']]) : 0;
                                ?>
                                    <li class="category-item">
                                        <div class="category-header">
                                            <div>
                                                <div class="category-name"><?= htmlspecialchars($cat['category_name']) ?></div>
                                                <div class="subcategory-count"><?= $sub_count ?> subcategor<?= $sub_count != 1 ? 'ies' : 'y' ?></div>
                                            </div>
                                            <div class="item-actions">
                                                <a href="add_category.php?id=<?= $cat['id'] ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="manage_categories.php?delete_category=<?= $cat['id'] ?>" 
                                                   class="btn-delete" 
                                                   onclick="return confirm('Are you sure? This will also delete all subcategories under this category.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <h3>No categories found</h3>
                                <p>Start by adding your first category!</p>
                                <a href="add_category.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                                    <i class="fas fa-plus"></i> Add Category
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Subcategories Section -->
                    <div class="category-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-tags"></i>
                                Subcategories
                            </h3>
                            <span class="subcategory-count">
                                <?php
                                $total_subs = 0;
                                foreach ($subcategories_by_category as $subs) {
                                    $total_subs += count($subs);
                                }
                                echo $total_subs . ' total';
                                ?>
                            </span>
                        </div>

                        <?php if (!empty($subcategories_by_category)): ?>
                            <ul class="subcategory-list">
                                <?php foreach ($subcategories_by_category as $cat_id => $subs): 
                                    $cat_name = isset($categories[$cat_id]) ? $categories[$cat_id]['category_name'] : 'Unknown Category';
                                ?>
                                    <li class="category-item" style="margin-bottom: 20px;">
                                        <div class="category-name" style="margin-bottom: 10px; color: #2fc7b4;">
                                            <i class="fas fa-folder"></i> <?= htmlspecialchars($cat_name) ?>
                                        </div>
                                        <?php foreach ($subs as $sub): ?>
                                            <div class="subcategory-item" style="margin-left: 20px; margin-bottom: 8px;">
                                                <div class="subcategory-header">
                                                    <div class="subcategory-name"><?= htmlspecialchars($sub['subcategory_name']) ?></div>
                                                    <div class="item-actions">
                                                        <a href="add_subcategory.php?id=<?= $sub['id'] ?>" class="btn-edit">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a href="manage_categories.php?delete_subcategory=<?= $sub['id'] ?>" 
                                                           class="btn-delete" 
                                                           onclick="return confirm('Are you sure you want to delete this subcategory?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tag"></i>
                                <h3>No subcategories found</h3>
                                <p>Add subcategories to organize your products better!</p>
                                <a href="add_subcategory.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                                    <i class="fas fa-plus"></i> Add Subcategory
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

