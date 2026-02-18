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

// Check which columns exist
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Handle success/error messages
$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';

// Get all products
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// Check if query was successful
$has_products = false;
$products_count = 0;
if ($result) {
    $products_count = mysqli_num_rows($result);
    $has_products = $products_count > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Craft Royale</title>
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

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(47, 199, 180, 0.1);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: rgba(47, 199, 180, 0.3);
        }

        .product-image-container {
            width: 100%;
            height: 240px;
            overflow: hidden;
            background: #ffffff;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .product-image-container img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image-container img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: #2fa76b;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .product-card-content {
            padding: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            margin: 0 0 12px 0;
            color: #1a1a2e;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.6em;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .product-price {
            margin: 0;
            color: #2fc7b4;
            font-size: 24px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .product-price small {
            font-size: 14px;
            color: #999;
            font-weight: 400;
        }

        .product-actions {
            display: flex;
            gap: 12px;
            margin-top: auto;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 12px;
            text-decoration: none;
            border-radius: 12px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            color: #2fa76b;
            border: 1px solid rgba(47, 199, 180, 0.2);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
            border-color: transparent;
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.05);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.1);
        }

        .btn-delete:hover {
            background: #e74c3c;
            color: white;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
            border-color: transparent;
        }

        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-state-icon {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #999;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 30px;
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

        .products-count {
            margin-bottom: 25px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(47, 199, 180, 0.1);
            color: #666;
            font-size: 14px;
        }

        .products-count i {
            color: #2fc7b4;
            font-size: 18px;
        }

        .products-count strong {
            color: #1a1a2e;
            font-size: 16px;
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
                    <h1>All Products</h1>
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
                <div class="page-header">
                    <h2 class="page-title">Products List</h2>
                    <a href="add_product.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        Add New Product
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
                            <p style="margin: 5px 0 0 0; font-size: 14px;">Unable to fetch products. Please check your database connection.</p>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 12px;"><?= mysqli_error($conn) ?></p>
                        </div>
            </div>
                <?php elseif ($has_products): ?>
                    <div class="products-count">
                        <i class="fas fa-box"></i> Total Products: <strong><?= $products_count ?></strong>
    </div>
                    <div class="products-grid">
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $product_name = $row['name'] ?? $row['product_name'] ?? 'Unnamed Product';
                            $product_price = $row['price'] ?? 0;
                            $product_image = $row['image'] ?? $row['product_image'] ?? '';
                            $product_id = $row['id'];
                            
                            // Handle image path
                            $image_path = '../uploads/products/' . $product_image;
                            if (!empty($product_image) && file_exists($image_path)) {
                                $display_image = $image_path;
                            } else {
                                $display_image = '../assets/images/beads.jpg';
                            }
                        ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <div class="product-badge">Active</div>
                                    <img src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($product_name) ?>" onerror="this.src='../assets/images/beads.jpg'">
                                </div>
                                <div class="product-card-content">
                                    <h4 class="product-name"><?= htmlspecialchars($product_name) ?></h4>
                                    <div class="product-meta">
                                        <p class="product-price">₹<?= number_format($product_price, 0) ?><small>.<?= substr(number_format($product_price, 2), -2) ?></small></p>
                                    </div>
                                    <div class="product-actions">
                                        <a href="add_product.php?id=<?= $product_id ?>" class="btn-edit" title="Edit Product">
                                            <i class="fas fa-pen-fancy"></i> Edit
                                        </a>
                                        <a href="delete-product.php?id=<?= $product_id ?>" class="btn-delete" title="Delete Product" onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open empty-state-icon"></i>
                        <h3>No Products Found</h3>
                        <p>You haven't added any products yet. Start by adding your first product!</p>
                        <a href="add_product.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                            <i class="fas fa-plus"></i>
                            Add Your First Product
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
