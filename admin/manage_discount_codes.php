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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $code = strtoupper(trim($_POST['code']));
            $discount_percentage = floatval($_POST['discount_percentage']);
            $description = trim($_POST['description']);
            $valid_from = $_POST['valid_from'];
            $valid_until = $_POST['valid_until'];
            $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : NULL;
            $min_order_amount = !empty($_POST['min_order_amount']) ? floatval($_POST['min_order_amount']) : 0;
            $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : NULL;
            $status = $_POST['status'];
            
            $stmt = $conn->prepare("INSERT INTO discount_codes (code, discount_percentage, description, valid_from, valid_until, usage_limit, min_order_amount, max_discount_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsssidds", $code, $discount_percentage, $description, $valid_from, $valid_until, $usage_limit, $min_order_amount, $max_discount_amount, $status);
            
            if ($stmt->execute()) {
                $success_message = "Discount code added successfully!";
            } else {
                $error_message = "Error: Code might already exist.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            mysqli_query($conn, "DELETE FROM discount_codes WHERE id = $id");
            $success_message = "Discount code deleted successfully!";
        } elseif ($_POST['action'] === 'toggle_status') {
            $id = intval($_POST['id']);
            $new_status = $_POST['new_status'];
            mysqli_query($conn, "UPDATE discount_codes SET status = '$new_status' WHERE id = $id");
            $success_message = "Status updated successfully!";
        }
    }
}

// Check if discount_codes table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'discount_codes'");
$table_exists = mysqli_num_rows($table_check) > 0;

// Get all discount codes if table exists
if ($table_exists) {
    $discount_codes = mysqli_query($conn, "SELECT * FROM discount_codes ORDER BY created_at DESC");
} else {
    $discount_codes = false;
    $setup_required = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Discount Codes - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .discount-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2fc7b4;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
        }
        
        .codes-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .codes-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .codes-table th {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .codes-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .codes-table tr:hover {
            background: #f9f9f9;
        }
        
        .code-badge {
            background: linear-gradient(135deg, #ff6b9d, #ff8fab);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-expired {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            margin-right: 5px;
            transition: all 0.2s;
        }
        
        .btn-toggle {
            background: #ffc107;
            color: #333;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .usage-info {
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>🎟️ Manage Discount Codes</h1>
                    <p class="welcome-text">Create and manage discount codes for customers</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>
            
            <div class="dashboard-content">
                <?php if (isset($setup_required) && $setup_required): ?>
                    <div class="alert" style="background: #fff3cd; color: #856404; border: 2px solid #ffc107; padding: 25px;">
                        <h3 style="margin: 0 0 15px 0; color: #856404;">
                            <i class="fas fa-exclamation-triangle"></i> Setup Required
                        </h3>
                        <p style="margin-bottom: 15px; font-size: 15px;">
                            The <strong>discount_codes</strong> table doesn't exist in your database yet. 
                            Please follow these steps to set it up:
                        </p>
                        
                        <div style="background: white; padding: 20px; border-radius: 8px; margin: 15px 0;">
                            <h4 style="margin: 0 0 15px 0; color: #333;">
                                <i class="fas fa-database"></i> Step 1: Create Database Tables
                            </h4>
                            <ol style="margin: 0; padding-left: 20px; line-height: 1.8;">
                                <li>Open <strong>phpMyAdmin</strong></li>
                                <li>Select your database: <code style="background: #f0f0f0; padding: 3px 8px; border-radius: 4px;">craft_royale</code></li>
                                <li>Go to the <strong>SQL</strong> tab</li>
                                <li>Open this file: <code style="background: #f0f0f0; padding: 3px 8px; border-radius: 4px;">create_discount_codes_table.sql</code></li>
                                <li>Copy the SQL code and paste it in phpMyAdmin</li>
                                <li>Click <strong>Go</strong> to execute</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                        
                        <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #2196F3;">
                            <strong><i class="fas fa-info-circle"></i> File Location:</strong><br>
                            <code style="background: white; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 8px;">
                                c:\xampp\htdocs\Craft Royale\create_discount_codes_table.sql
                            </code>
                        </div>
                        
                        <p style="margin: 15px 0 0 0; font-size: 14px;">
                            <strong>Note:</strong> This will create the tables and add 3 sample discount codes (FIRSTSALE, WELCOME20, SAVE15).
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($table_exists): ?>
                <!-- Add Discount Code Form -->
                <div class="discount-form">
                    <h2 style="margin-bottom: 20px; color: #2fc7b4;">
                        <i class="fas fa-plus-circle"></i> Add New Discount Code
                    </h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Code *</label>
                                <input type="text" name="code" placeholder="e.g., FIRSTSALE" required style="text-transform: uppercase;">
                            </div>
                            
                            <div class="form-group">
                                <label>Discount Percentage *</label>
                                <input type="number" name="discount_percentage" placeholder="e.g., 10" min="0" max="100" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Valid From *</label>
                                <input type="date" name="valid_from" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Valid Until *</label>
                                <input type="date" name="valid_until" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Usage Limit</label>
                                <input type="number" name="usage_limit" placeholder="Leave empty for unlimited" min="1">
                            </div>
                            
                            <div class="form-group">
                                <label>Min Order Amount (₹)</label>
                                <input type="number" name="min_order_amount" placeholder="0" min="0" step="0.01" value="0">
                            </div>
                            
                            <div class="form-group">
                                <label>Max Discount Amount (₹)</label>
                                <input type="number" name="max_discount_amount" placeholder="Leave empty for no limit" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="2" placeholder="Optional description for internal use"></textarea>
                        </div>
                        
                        <button type="submit" class="btn-add">
                            <i class="fas fa-plus"></i> Add Discount Code
                        </button>
                    </form>
                </div>
                
                <!-- Discount Codes Table -->
                <div class="codes-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Valid Period</th>
                                <th>Usage</th>
                                <th>Min Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($code = mysqli_fetch_assoc($discount_codes)): ?>
                                <?php
                                $is_expired = strtotime($code['valid_until']) < time();
                                $status_class = $is_expired ? 'status-expired' : ($code['status'] === 'active' ? 'status-active' : 'status-inactive');
                                $status_text = $is_expired ? 'Expired' : ucfirst($code['status']);
                                ?>
                                <tr>
                                    <td>
                                        <span class="code-badge"><?php echo htmlspecialchars($code['code']); ?></span>
                                        <?php if ($code['description']): ?>
                                            <br><small style="color: #666;"><?php echo htmlspecialchars($code['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong style="color: #2fc7b4; font-size: 18px;"><?php echo $code['discount_percentage']; ?>%</strong>
                                        <?php if ($code['max_discount_amount']): ?>
                                            <br><small class="usage-info">Max: ₹<?php echo number_format($code['max_discount_amount'], 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?php echo date('d M Y', strtotime($code['valid_from'])); ?></div>
                                        <div style="color: #666;">to <?php echo date('d M Y', strtotime($code['valid_until'])); ?></div>
                                    </td>
                                    <td>
                                        <div><strong><?php echo $code['times_used']; ?></strong> times used</div>
                                        <?php if ($code['usage_limit']): ?>
                                            <small class="usage-info">Limit: <?php echo $code['usage_limit']; ?></small>
                                        <?php else: ?>
                                            <small class="usage-info">Unlimited</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?php echo number_format($code['min_order_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!$is_expired): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo $code['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $code['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" class="btn-action btn-toggle">
                                                    <i class="fas fa-toggle-<?php echo $code['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                                    <?php echo $code['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this discount code?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $code['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
