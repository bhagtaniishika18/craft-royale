<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check for success/error messages
$success_message = isset($_GET['success']) ? urldecode($_GET['success']) : '';
$error_message = isset($_GET['error']) ? urldecode($_GET['error']) : '';

// Get all gift card designs
$designs_query = "SELECT * FROM gift_card_designs ORDER BY display_order ASC, created_at DESC";
$designs_result = mysqli_query($conn, $designs_query);
$designs = [];
while ($design = mysqli_fetch_assoc($designs_result)) {
    $designs[] = $design;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gift Card Designs - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid #8B5CF6;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: #2b2b2b;
            text-align: left;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 13px;
            margin: 0 0 10px 0;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .designs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .design-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
        }

        .design-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #8B5CF6;
        }

        .design-preview-large {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            background-size: cover;
            background-position: center;
        }

        .design-preview-large::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
        }

        .design-preview-large span {
            position: relative;
            z-index: 1;
        }

        .design-info {
            padding: 20px;
        }

        .design-name {
            font-size: 18px;
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .design-key {
            font-size: 12px;
            color: #666;
            background: #f5f5f5;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .design-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .design-detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-small {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        .btn-edit:hover {
            background: #e2e8f0;
            color: #7C3AED;
            border-color: #8B5CF6;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fff;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* Custom Modern Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .custom-active-modal {
            background: #fff;
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.active .custom-active-modal {
            transform: scale(1);
        }

        .modal-icon {
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

        .modal-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .modal-desc {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .modal-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
        }

        .btn-confirm {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .btn-confirm:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .alert-error {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #fecaca;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #2b2b2b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>🎨 Manage Gift Card Designs</h1>
                    <p class="welcome-text">Create and manage gift card designs with custom backgrounds</p>
                </div>
                <div class="header-right">
                    <a href="add_gift_card_design.php" class="submit-btn">
                        <i class="fas fa-plus"></i> Add New Design
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($success_message): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Designs</h3>
                        <p class="stat-number"><?= count($designs) ?></p>
                    </div>
                    <div class="stat-card" style="border-left-color: #10b981;">
                        <h3>Active Designs</h3>
                        <p class="stat-number"><?= count(array_filter($designs, function($d) { return $d['is_active'] == 1; })) ?></p>
                    </div>
                    <div class="stat-card" style="border-left-color: #f59e0b;">
                        <h3>Inactive Designs</h3>
                        <p class="stat-number"><?= count(array_filter($designs, function($d) { return $d['is_active'] == 0; })) ?></p>
                    </div>
                </div>

                <?php if (empty($designs)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎨</div>
                        <h3>No Designs Found</h3>
                        <p>Create your first gift card design to get started!</p>
                        <a href="add_gift_card_design.php" class="submit-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-plus"></i> ✨ Create First Design
                        </a>
                    </div>
                <?php else: ?>
                    <div class="designs-grid">
                        <?php foreach ($designs as $design): ?>
                            <div class="design-card">
                                <div class="design-preview-large" 
                                     style="background: <?= !empty($design['background_image']) ? "url('../" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; color: <?= $design['text_color'] ?>;">
                                    <span><?= htmlspecialchars($design['title']) ?></span>
                                </div>
                                <div class="design-info">
                                    <div class="design-name">
                                        <?php if ($design['is_active']): ?>
                                            <span>✅</span>
                                        <?php else: ?>
                                            <span>⏸️</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($design['design_name']) ?>
                                    </div>
                                    <div class="design-key">🔑 <?= htmlspecialchars($design['design_key']) ?></div>
                                    <div class="design-details">
                                        <div class="design-detail-item">
                                            <span>🎨</span>
                                            <span style="width: 30px; height: 20px; background: <?= htmlspecialchars($design['background_color']) ?>; border: 1px solid #ddd; border-radius: 4px; display: inline-block;"></span>
                                        </div>
                                        <div class="design-detail-item">
                                            <span>📝</span>
                                            <span><?= htmlspecialchars($design['text_color']) ?></span>
                                        </div>
                                        <?php if (!empty($design['background_image'])): ?>
                                            <div class="design-detail-item">
                                                <span>🖼️</span>
                                                <span>Has Image</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($design['description'])): ?>
                                        <p style="font-size: 12px; color: #666; margin: 10px 0;"><?= htmlspecialchars($design['description']) ?></p>
                                    <?php endif; ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                        <span class="status-badge status-<?= $design['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $design['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                        <span style="font-size: 12px; color: #999;">Order: <?= $design['display_order'] ?></span>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="edit_gift_card_design.php?id=<?= $design['id'] ?>" class="btn-small btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="confirmDeleteDesign(<?= $design['id'] ?>)" class="btn-small btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <!-- Modern Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="custom-active-modal">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="modal-title">Confirm Deletion</h2>
            <p class="modal-desc">Are you sure you want to delete this gift card design? This action is permanent and cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" onclick="closeDeleteModal()">No, Keep it</button>
                <button onclick="submitDelete()" class="modal-btn btn-confirm" style="display: flex; align-items: center;">Yes, Delete it</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for secure deletion -->
    <form id="deleteForm" method="POST" action="delete_gift_card_design.php" style="display: none;">
        <input type="hidden" name="id" id="deleteId">
        <input type="hidden" name="confirm_delete" value="1">
    </form>

    <script>
        function confirmDeleteDesign(designId) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('deleteId').value = designId;
            modal.classList.add('active');
        }

        function submitDelete() {
            document.getElementById('deleteForm').submit();
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
        }

        // Close modal if clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>
