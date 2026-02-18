<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$subcategory_id = $_GET['id'] ?? null;
$subcategory = null;
$edit_mode = false;

if ($subcategory_id) {
    $edit_mode = true;
    $sub_query = mysqli_query($conn, "SELECT * FROM subcategories WHERE id='$subcategory_id'");
    $subcategory = mysqli_fetch_assoc($sub_query);
    if (!$subcategory) {
        header("Location: manage_categories.php");
        exit();
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

if (isset($_POST['submit'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $subcategory_name = mysqli_real_escape_string($conn, $_POST['subcategory_name']);
    $subcategory_slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $subcategory_name))));
    
    // Check if subcategory_slug column exists
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM subcategories LIKE 'subcategory_slug'");
    $has_slug_column = mysqli_num_rows($check_column) > 0;
    
    if ($edit_mode) {
        $update_query = "UPDATE subcategories SET 
                         category_id='$category_id',
                         subcategory_name='$subcategory_name'";
        
        if ($has_slug_column) {
            $update_query .= ", subcategory_slug='$subcategory_slug'";
        }
        
        $update_query .= " WHERE id='$subcategory_id'";
        
        $result = mysqli_query($conn, $update_query);
        if ($result) {
            header("Location: manage_categories.php?success=subcategory_updated");
        } else {
            header("Location: add_subcategory.php?id=$subcategory_id&error=" . urlencode(mysqli_error($conn)));
        }
    } else {
        // Check if subcategory with same name already exists in this category
        $check_duplicate = mysqli_query($conn, "SELECT id FROM subcategories WHERE category_id='$category_id' AND subcategory_name='$subcategory_name'");
        if (mysqli_num_rows($check_duplicate) > 0) {
            header("Location: add_subcategory.php?error=" . urlencode("A subcategory with this name already exists in the selected category."));
            exit();
        }
        
        if ($has_slug_column) {
            $insert_query = "INSERT INTO subcategories (category_id, subcategory_name, subcategory_slug) 
                             VALUES ('$category_id', '$subcategory_name', '$subcategory_slug')";
        } else {
            $insert_query = "INSERT INTO subcategories (category_id, subcategory_name) 
                             VALUES ('$category_id', '$subcategory_name')";
        }
        
        $result = mysqli_query($conn, $insert_query);
        if ($result) {
            header("Location: manage_categories.php?success=subcategory_added");
        } else {
            $error_msg = mysqli_error($conn);
            // Log the error for debugging
            error_log("Subcategory insert error: " . $error_msg);
            error_log("Query: " . $insert_query);
            header("Location: add_subcategory.php?error=" . urlencode("Database error: " . $error_msg));
        }
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subcategory - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?= $edit_mode ? 'Edit Subcategory' : 'Add New Subcategory' ?></h1>
                    <p class="welcome-text"><?= $edit_mode ? 'Update subcategory information' : 'Create a new subcategory under a category' ?></p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="manage_categories.php" class="back-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f0f0f0; color: #2b2b2b; text-decoration: none; border-radius: 8px; font-weight: 600; margin-bottom: 20px;">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>

                <?php if (isset($_GET['error'])): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
                        <br><small style="margin-top: 8px; display: block;">Please check the error message above and try again. If the problem persists, check your database connection and table structure.</small>
                    </div>
                <?php endif; ?>

                <div style="background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); max-width: 800px;">
                    <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid rgba(47, 199, 180, 0.1);">
                        <h2 style="font-size: 28px; font-weight: 800; background: linear-gradient(135deg, #2fc7b4, #2fa76b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0;">
                            <?= $edit_mode ? 'Edit Subcategory' : 'Add New Subcategory' ?>
                        </h2>
                    </div>

                    <form method="post">
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2b2b2b; font-size: 14px;">Category *</label>
                            <select name="category_id" required style="width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                                <option value="">Select Category</option>
                                <?php 
                                mysqli_data_seek($categories, 0);
                                while ($row = mysqli_fetch_assoc($categories)) { 
                                    $selected = ($edit_mode && $subcategory['category_id'] == $row['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row['id'] ?>" <?= $selected ?>><?= htmlspecialchars($row['category_name']) ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2b2b2b; font-size: 14px;">Subcategory Name *</label>
                            <input type="text" name="subcategory_name" required 
                                   value="<?= $edit_mode ? htmlspecialchars($subcategory['subcategory_name']) : '' ?>"
                                   placeholder="e.g., French Wire / Dabka" 
                                   style="width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                The URL slug will be auto-generated from the name
                            </small>
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" name="submit" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b); color: #fff; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);">
                                <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Subcategory' : 'Add Subcategory' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

