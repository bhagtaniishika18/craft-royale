<?php
session_start();

// Strong cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle success/error messages
$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';

// Get all subcategories with their category names
$result = mysqli_query($conn, "SELECT s.*, c.category_name 
                                FROM subcategories s 
                                LEFT JOIN categories c ON s.category_id = c.id 
                                ORDER BY c.category_name, s.subcategory_name");

// Check if query was successful
$has_subcategories = false;
$subcategories_count = 0;
if ($result) {
    $subcategories_count = mysqli_num_rows($result);
    $has_subcategories = $subcategories_count > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>All Subcategories - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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

        .subcategories-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
        }

        .subcategories-table {
            width: 100%;
            border-collapse: collapse;
        }

        .subcategories-table thead {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }

        .subcategories-table th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subcategories-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }

        .subcategories-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .subcategories-table tbody tr:last-child {
            border-bottom: none;
        }

        .subcategories-table td {
            padding: 15px 20px;
            font-size: 14px;
            color: #2b2b2b;
        }

        .subcategory-name-cell {
            font-weight: 600;
            color: #2b2b2b;
        }

        .category-name-cell {
            color: #666;
        }

        .subcategory-actions-cell {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }

        .subcategories-count {
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>All Subcategories</h1>
                    <p class="welcome-text">View and manage all your product subcategories</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="page-header">
                    <h2 class="page-title">Subcategories List</h2>
                    <a href="add_subcategory.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        Add New Subcategory
                    </a>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!$result): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Database Error</strong>
                            <p style="margin: 5px 0 0 0; font-size: 14px;">Unable to fetch subcategories. Please check your database connection.</p>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 12px;"><?= mysqli_error($conn) ?></p>
                        </div>
                    </div>
                <?php elseif ($has_subcategories): ?>
                    <div class="subcategories-count">
                        <i class="fas fa-tags"></i> Total Subcategories: <strong><?= $subcategories_count ?></strong>
                    </div>
                    <div class="subcategories-table-container">
                        <table class="subcategories-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subcategory Name</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($result, 0); // Reset result pointer
                                while($row = mysqli_fetch_assoc($result)): 
                                    $subcategory_name = $row['subcategory_name'] ?? 'Unnamed Subcategory';
                                    $category_name = $row['category_name'] ?? 'Unknown Category';
                                    $subcategory_id = $row['id'];
                                ?>
                                    <tr>
                                        <td><?= $subcategory_id ?></td>
                                        <td class="subcategory-name-cell"><?= htmlspecialchars($subcategory_name) ?></td>
                                        <td class="category-name-cell">
                                            <i class="fas fa-folder"></i> <?= htmlspecialchars($category_name) ?>
                                        </td>
                                        <td class="subcategory-actions-cell">
                                            <a href="add_subcategory.php?id=<?= $subcategory_id ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="manage_categories.php?delete_subcategory=<?= $subcategory_id ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this subcategory?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tag"></i>
                        <h3>No subcategories found</h3>
                        <p>Start by adding your first subcategory!</p>
                        <a href="add_subcategory.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                            <i class="fas fa-plus"></i>
                            Add Subcategory
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
