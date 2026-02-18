<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$category_id = $_GET['id'] ?? null;
$category = null;
$edit_mode = false;

if ($category_id) {
    $edit_mode = true;
    $cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE id='$category_id'");
    $category = mysqli_fetch_assoc($cat_query);
    if (!$category) {
        header("Location: manage_categories.php");
        exit();
    }
}

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if ($edit_mode) {
        $update_query = "UPDATE categories SET category_name='$name' WHERE id='$category_id'";
        mysqli_query($conn, $update_query);
        header("Location: manage_categories.php?success=category_updated");
    } else {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$name')");
        header("Location: manage_categories.php?success=category_added");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Category - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .form-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 40px;
            border: 1px solid rgba(47, 199, 180, 0.1);
            max-width: 800px;
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(47, 199, 180, 0.1);
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2b2b2b;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2fc7b4;
            box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
        }


        .submit-btn {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(47, 199, 180, 0.4);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #2b2b2b;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?= $edit_mode ? 'Edit Category' : 'Add New Category' ?></h1>
                    <p class="welcome-text"><?= $edit_mode ? 'Update category information' : 'Create a new product category' ?></p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="manage_categories.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>

                <div class="form-container">
                    <div class="form-header">
                        <h2><?= $edit_mode ? 'Edit Category' : 'Add New Category' ?></h2>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="category_name" value="<?= $edit_mode ? htmlspecialchars($category['category_name']) : '' ?>" required placeholder="e.g., Embroidery, Beads, Embellishments">
                        </div>


                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" name="submit" class="submit-btn">
                                <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Category' : 'Add Category' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>

