<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$success_message = '';
$error_message = '';

// Get admin data
$admin_email = $_SESSION['admin'];
$admin_query = "SELECT * FROM admin WHERE email = '$admin_email'";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);

if (!$admin) {
    header("Location: login.php");
    exit();
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate current password
    if ($admin['password'] != $current_password) {
        $error_message = "Current password is incorrect";
    } elseif (empty($new_password)) {
        $error_message = "New password cannot be empty";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long";
    } elseif ($new_password != $confirm_password) {
        $error_message = "New password and confirm password do not match";
    } else {
        // Update password
        $update_query = "UPDATE admin SET password = '$new_password' WHERE email = '$admin_email'";
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Password changed successfully!";
            // Refresh admin data
            $admin_result = mysqli_query($conn, $admin_query);
            $admin = mysqli_fetch_assoc($admin_result);
        } else {
            $error_message = "Error updating password: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .profile-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(47, 199, 180, 0.1);
        }

        .profile-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
        }

        .profile-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #2b2b2b;
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

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #f9f9f9;
        }

        .form-group input:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            background: #fff;
        }

        .form-group input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
            color: #666;
        }

        .save-btn {
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
            color: #fff;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
        }

        .password-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid rgba(47, 199, 180, 0.1);
        }

        .password-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .password-section h3 i {
            color: #8B5CF6;
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
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                    <h1>Admin Profile</h1>
                    <p class="welcome-text">Manage your profile settings</p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>

                <div class="profile-container">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2>Admin Profile</h2>
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

                        <form method="post">
                            <?php if (isset($admin['name'])): ?>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" value="<?= htmlspecialchars($admin['name']) ?>" readonly>
                            </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="text" value="<?= htmlspecialchars($admin['password']) ?>" readonly>
                            </div>

                            <?php if (isset($admin['created_at'])): ?>
                            <div class="form-group">
                                <label>Created At</label>
                                <input type="text" value="<?= date('d M Y', strtotime($admin['created_at'])) ?>" readonly>
                            </div>
                            <?php endif; ?>

                            <div class="password-section">
                                <h3>
                                    <i class="fas fa-lock"></i>
                                    Change Password
                                </h3>

                                <div class="form-group">
                                    <label>Current Password *</label>
                                    <input type="password" name="current_password" required>
                                </div>

                                <div class="form-group">
                                    <label>New Password *</label>
                                    <input type="password" name="new_password" required minlength="6">
                                </div>

                                <div class="form-group">
                                    <label>Confirm New Password *</label>
                                    <input type="password" name="confirm_password" required minlength="6">
                                </div>

                                <button type="submit" name="change_password" class="save-btn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
