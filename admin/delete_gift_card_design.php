<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$design_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if (!$design_id) {
    header("Location: manage_gift_card_designs.php");
    exit();
}

// Get design details
$design_query = "SELECT * FROM gift_card_designs WHERE id = $design_id";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

if (!$design) {
    header("Location: manage_gift_card_designs.php");
    exit();
}

// Check if design is being used by any gift cards
$usage_check = "SELECT COUNT(*) as count FROM gift_cards WHERE design = '{$design['design_key']}'";
$usage_result = mysqli_query($conn, $usage_check);
$usage_data = mysqli_fetch_assoc($usage_result);
$is_used = $usage_data['count'] > 0;

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    if ($is_used) {
        header("Location: manage_gift_card_designs.php?error=" . urlencode("Cannot delete design. It is being used by " . $usage_data['count'] . " gift card(s)."));
        exit();
    }
    
    // Delete background image if exists
    if (!empty($design['background_image']) && file_exists('../' . $design['background_image'])) {
        unlink('../' . $design['background_image']);
    }
    
    // Delete the design
    $delete_query = "DELETE FROM gift_card_designs WHERE id = $design_id";
    
    if (mysqli_query($conn, $delete_query)) {
        header("Location: manage_gift_card_designs.php?success=" . urlencode("Design deleted successfully!"));
        exit();
    } else {
        $error_message = "Error deleting design: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Gift Card Design - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .delete-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 22px;
            background: #fff;
            color: #7C3AED;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border-radius: 12px;
            border: 2px solid #f3f0ff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.08);
        }

        .back-btn:hover {
            background: #7C3AED;
            color: #fff;
            border-color: #7C3AED;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
            transform: translateX(-5px);
        }

        .back-btn i {
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-3px);
        }

        .warning-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            text-align: center;
        }

        .warning-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 25px;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .warning-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .warning-desc {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .design-preview-box {
            height: 140px;
            background-size: cover;
            background-position: center;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 22px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .design-info {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eef2f6;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
        }

        .info-value {
            color: #1e293b;
            font-weight: 700;
            font-size: 14px;
        }

        .action-btns {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .delete-btn {
            background: #ef4444;
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .cancel-link {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            color: #64748b;
            background: #f1f5f9;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
        }

        .cancel-link:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .error-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #fecaca;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #fff1f2;
            color: #f43f5e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 25px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Delete Design</h1>
                    <p class="welcome-text">Confirm permanent deletion</p>
                </div>
                <div class="header-right">
                    <a href="manage_gift_card_designs.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Designs
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="delete-container">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_used): ?>
                        <div class="error-card">
                            <div class="error-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                            <h2 class="warning-title">Action Blocked</h2>
                            <p class="warning-desc">This design is currently being used by <strong><?= $usage_data['count'] ?></strong> gift card(s). You must reassign those cards before you can delete this design.</p>
                            <a href="manage_gift_card_designs.php" class="cancel-link" style="background: #8B5CF6; color: #fff; border-color: #8B5CF6;">
                                <i class="fas fa-arrow-left" style="margin-right: 10px;"></i> Back to Designs
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="warning-card">
                            <div class="warning-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2 class="warning-title">Delete Design?</h2>
                            <p class="warning-desc">You are about to delete the following design. This action is permanent and cannot be undone.</p>
                            
                            <div class="design-preview-box" style="background: <?= !empty($design['background_image']) ? "url('../" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; color: <?= $design['text_color'] ?>;">
                                <?= htmlspecialchars($design['title']) ?>
                            </div>

                            <div class="design-info">
                                <div class="info-item">
                                    <span class="info-label">Design Name</span>
                                    <span class="info-value"><?= htmlspecialchars($design['design_name']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Design Key</span>
                                    <span class="info-value"><?= htmlspecialchars($design['design_key']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="info-value"><?= $design['is_active'] ? 'Active' : 'Inactive' ?></span>
                                </div>
                            </div>

                            <form method="post" class="action-btns">
                                <input type="hidden" name="confirm_delete" value="1">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash"></i> Yes, Delete Design
                                </button>
                                <a href="manage_gift_card_designs.php" class="cancel-link">Cancel</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
