<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Craft Royale</title>
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

        .products-table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table thead {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }

        .products-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .products-table tbody tr {
            transition: all 0.3s ease;
        }

        .products-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }

        .products-table tbody tr:last-child td {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(47, 199, 180, 0.2);
        }

        .product-name {
            font-weight: 600;
            color: #2b2b2b;
            font-size: 16px;
        }

        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #2fa76b;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }

        /* Beautiful Success Popup */
        .success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
            color: #fff;
            padding: 0;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(47, 167, 107, 0.4);
            z-index: 10000;
            min-width: 350px;
            max-width: 450px;
            overflow: hidden;
            animation: slideInRight 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: top right;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
            to {
                transform: translateX(400px) scale(0.8);
                opacity: 0;
            }
        }

        .success-popup.hiding {
            animation: slideOutRight 0.4s ease-in forwards;
        }

        .success-popup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(255,255,255,0.6), rgba(255,255,255,0.3));
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .success-popup-content {
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .success-icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            animation: iconBounce 0.6s ease-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        @keyframes iconBounce {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        .success-icon-wrapper i {
            font-size: 28px;
            color: #fff;
            animation: checkmarkDraw 0.6s ease-out;
        }

        @keyframes checkmarkDraw {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.2) rotate(0deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        .success-message-content {
            flex: 1;
        }

        .success-popup-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 5px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .success-popup-text {
            font-size: 14px;
            margin: 0;
            opacity: 0.95;
            line-height: 1.4;
        }

        .success-popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        .success-popup-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
            opacity: 1;
        }

        .success-popup-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.4);
            width: 100%;
            transform-origin: left;
            animation: progressBar 5s linear forwards;
        }

        @keyframes progressBar {
            from {
                transform: scaleX(1);
            }
            to {
                transform: scaleX(0);
            }
        }

        @media (max-width: 768px) {
            .success-popup {
                right: 10px;
                top: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }
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
                    <h1>Manage Products</h1>
                    <p class="welcome-text">View and manage all your products</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($_GET['success']) && ($_GET['success'] == 'added' || $_GET['success'] == 'updated')): ?>
                    <div id="successPopup" class="success-popup">
                        <div class="success-popup-content">
                            <div class="success-icon-wrapper">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="success-message-content">
                                <div class="success-popup-title">
                                    <?php if($_GET['success'] == 'added'): ?>
                                        Product Added Successfully!
                                    <?php else: ?>
                                        Product Updated Successfully!
                                    <?php endif; ?>
                                </div>
                                <div class="success-popup-text">
                                    <?php if($_GET['success'] == 'added'): ?>
                                        Your product has been added to the catalog.
                                    <?php else: ?>
                                        Your product has been updated successfully.
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button class="success-popup-close" onclick="closeSuccessPopup()">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="success-popup-progress"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <h2 class="page-title">Products List</h2>
                    <a href="add_product.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        Add New Product
                    </a>
                </div>

                <div class="products-table-container">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <img src="../uploads/products/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name'] ?? $row['product_name'] ?? 'Product'); ?>" class="product-image" onerror="this.src='../assets/images/beads.jpg'">
                                        </td>
                                        <td>
                                            <div class="product-name"><?php echo htmlspecialchars($row['name'] ?? $row['product_name'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td>
                                            <div class="product-price">₹<?php echo number_format($row['price'] ?? 0, 2); ?></div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="add_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="delete-product.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>No products found</h3>
                            <p>Start by adding your first product!</p>
                            <a href="add_product.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                                <i class="fas fa-plus"></i>
                                Add Product
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-close success popup after 5 seconds
        <?php if(isset($_GET['success']) && ($_GET['success'] == 'added' || $_GET['success'] == 'updated')): ?>
        function closeSuccessPopup() {
            const popup = document.getElementById('successPopup');
            if (popup) {
                popup.classList.add('hiding');
                setTimeout(() => {
                    popup.remove();
                    // Remove success parameter from URL without page reload
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    window.history.replaceState({}, '', url);
                }, 400);
            }
        }

        // Auto-close after 5 seconds
        setTimeout(() => {
            closeSuccessPopup();
        }, 5000);

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSuccessPopup();
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
