<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle approve/decline actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE reviews SET status='approved' WHERE id=$id");
    } elseif ($action == 'decline') {
        mysqli_query($conn, "UPDATE reviews SET status='declined' WHERE id=$id");
    } elseif ($action == 'delete') {
        // Get image path before deleting
        $review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM reviews WHERE id=$id"));
        if ($review && $review['image'] && file_exists('../uploads/reviews/' . $review['image'])) {
            unlink('../uploads/reviews/' . $review['image']);
        }
        mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    }
    header("Location: reviews.php");
    exit();
}

// Fetch all reviews
$reviews = mysqli_query($conn, "SELECT * FROM reviews ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .dashboard-content {
            padding: 20px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .reviews-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table-header {
            background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
            color: #fff;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .table-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-wrapper {
            overflow-x: auto;
            max-height: calc(100vh - 350px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(47, 167, 107, 0.3) transparent;
        }

        .table-wrapper::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(47, 167, 107, 0.3);
            border-radius: 10px;
        }
        
        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px; /* Ensure table has enough room to breathe */
        }
        
        .table-wrapper thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table-wrapper thead tr {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .table-wrapper th {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 3px solid #2fa76b;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
            white-space: nowrap;
            background: #f8fafc;
        }
        
        .table-wrapper tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .table-wrapper tbody tr:hover {
            background: #f8fdf9;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(47, 167, 107, 0.1);
        }
        
        .table-wrapper td {
            padding: 12px;
            font-size: 12px;
            color: #475569;
            vertical-align: middle;
            white-space: normal;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 2px solid #ffc107;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .status-declined {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #dc3545;
        }
        
        .review-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .review-image:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }
        
        .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: nowrap;
            justify-content: flex-start;
        }
        
        .btn-approve, .btn-decline, .btn-delete {
            padding: 5px 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            min-width: auto;
        }
        
        .btn-approve::before, .btn-decline::before, .btn-delete::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-approve:hover::before, .btn-decline:hover::before, .btn-delete:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-approve {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }
        
        .btn-approve:hover {
            background: linear-gradient(135deg, #218838, #1ea080);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        
        .btn-decline {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
        }
        
        .btn-decline:hover {
            background: linear-gradient(135deg, #c82333, #c0392b);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: #fff;
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 12px;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 2px;
        }
        
        .rating-stars span {
            display: inline-block;
        }
        
        .review-details {
            max-width: 100%;
            word-wrap: break-word;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-size: 10px;
        }
        
        /* Beautiful Modal Popup */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-overlay.show {
            display: flex;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-popup {
            background: linear-gradient(135deg, #ffffff 0%, #f8fdf9 100%);
            border-radius: 24px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: popupZoom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            border: 3px solid rgba(47, 167, 107, 0.2);
            position: relative;
        }
        
        @keyframes popupZoom {
            from {
                transform: scale(0.8) rotate(-2deg);
                opacity: 0;
            }
            to {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }
        
        .modal-popup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2fa76b, #2fc7b4, #2fa76b);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .modal-header {
            padding: 30px 30px 20px;
            text-align: center;
            background: linear-gradient(135deg, rgba(47, 167, 107, 0.05), rgba(47, 199, 180, 0.05));
            border-bottom: 2px solid rgba(47, 167, 107, 0.1);
        }
        
        .modal-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: iconPulse 2s ease-in-out infinite;
        }
        
        .modal-icon.approve {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        
        .modal-icon.decline {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }
        
        .modal-icon.delete {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: #fff;
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }
        
        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        .modal-title {
            font-size: 28px;
            font-weight: 700;
            color: #2b2b2b;
            margin: 0 0 10px 0;
        }
        
        .modal-message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .modal-btn {
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 120px;
            position: relative;
            overflow: hidden;
        }
        
        .modal-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .modal-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .modal-btn-confirm {
            background: linear-gradient(135deg, #2fa76b, #2fc7b4);
            color: #fff;
            box-shadow: 0 6px 20px rgba(47, 167, 107, 0.3);
        }
        
        .modal-btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(47, 167, 107, 0.4);
        }
        
        .modal-btn-cancel {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }
        
        .modal-btn-cancel:hover {
            background: #e9ecef;
            transform: translateY(-3px);
        }
        
        .modal-btn-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }
        
        .modal-btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
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
                    <h1>💬 Manage Reviews</h1>
                    <p class="welcome-text">Approve or decline customer reviews</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="reviews-table">
                    <div class="table-header">
                        <h2>🌟 All Reviews</h2>
                    </div>
                    
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Rating</th>
                                    <th>Category</th>
                                    <th>Experience</th>
                                    <th>Feedback</th>
                                    <th>Message</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($reviews) > 0): ?>
                                    <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                                        <tr>
                                            <td><strong>#<?= $review['id'] ?></strong></td>
                                            <td><strong><?= htmlspecialchars($review['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($review['email']) ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                                    <span class="rating-stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <span><?= $i <= $review['rating'] ? '★' : '☆' ?></span>
                                                        <?php endfor; ?>
                                                    </span>
                                                    <span style="font-weight: 600; color: #ffc107; font-size: 11px;">(<?= $review['rating'] ?>)</span>
                                                </div>
                                            </td>
                                            <td style="font-size: 10px;"><?= htmlspecialchars(substr($review['product_category'] ?: 'N/A', 0, 12)) ?><?= strlen($review['product_category']) > 12 ? '...' : '' ?></td>
                                            <td style="font-size: 10px;"><?= htmlspecialchars($review['experience'] ?: 'N/A') ?></td>
                                            <td>
                                                <div class="review-details" title="<?= htmlspecialchars($review['feedback']) ?>">
                                                    <?= htmlspecialchars(substr($review['feedback'], 0, 30)) ?><?= strlen($review['feedback']) > 30 ? '...' : '' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="review-details" title="<?= htmlspecialchars($review['message'] ?: 'N/A') ?>">
                                                    <?= htmlspecialchars(substr($review['message'] ?: 'N/A', 0, 30)) ?><?= strlen($review['message']) > 30 ? '...' : '' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($review['image']): ?>
                                                    <img src="../uploads/reviews/<?= htmlspecialchars($review['image']) ?>" 
                                                         alt="Review Image" 
                                                         class="review-image"
                                                         onclick="showImageModal(this.src)">
                                                <?php else: ?>
                                                    <span style="color: #999;">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $review['status'] ?>">
                                                    <?= ucfirst($review['status']) ?>
                                                </span>
                                            </td>
                                            <td style="font-size: 12px; color: #666;">
                                                <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if($review['status'] != 'approved'): ?>
                                                        <a href="#" 
                                                           class="btn-approve" 
                                                           onclick="showConfirmModal('approve', <?= $review['id'] ?>, 'Approve this review?', 'This review will be visible to all customers.'); return false;">
                                                            ✓ Approve
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if($review['status'] != 'declined'): ?>
                                                        <a href="#" 
                                                           class="btn-decline" 
                                                           onclick="showConfirmModal('decline', <?= $review['id'] ?>, 'Decline this review?', 'This review will be hidden from customers.'); return false;">
                                                            ✗ Decline
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="#" 
                                                       class="btn-delete" 
                                                       onclick="showConfirmModal('delete', <?= $review['id'] ?>, 'Delete this review?', 'This action cannot be undone. The review will be permanently removed.'); return false;">
                                                        🗑 Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" style="padding: 60px; text-align: center; color: #999;">
                                            <div style="font-size: 48px; margin-bottom: 20px;">📝</div>
                                            <div style="font-size: 18px; font-weight: 600;">No reviews found yet.</div>
                                            <div style="font-size: 14px; margin-top: 10px;">Reviews will appear here once customers submit them.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Beautiful Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-popup">
            <div class="modal-header">
                <div id="modalIcon" class="modal-icon"></div>
                <h2 class="modal-title" id="modalTitle"></h2>
                <p class="modal-message" id="modalMessage"></p>
            </div>
            <div class="modal-body">
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                    <button class="modal-btn" id="modalConfirmBtn" onclick="confirmAction()">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentAction = '';
        let currentId = 0;
        
        function showConfirmModal(action, id, title, message) {
            currentAction = action;
            currentId = id;
            
            const modal = document.getElementById('confirmModal');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            
            // Set icon and button style based on action
            if (action === 'approve') {
                modalIcon.className = 'modal-icon approve';
                modalIcon.innerHTML = '✓';
                confirmBtn.className = 'modal-btn modal-btn-confirm';
                confirmBtn.textContent = '✓ Approve';
            } else if (action === 'decline') {
                modalIcon.className = 'modal-icon decline';
                modalIcon.innerHTML = '✗';
                confirmBtn.className = 'modal-btn modal-btn-danger';
                confirmBtn.textContent = '✗ Decline';
            } else if (action === 'delete') {
                modalIcon.className = 'modal-icon delete';
                modalIcon.innerHTML = '🗑';
                confirmBtn.className = 'modal-btn modal-btn-danger';
                confirmBtn.textContent = '🗑 Delete';
            }
            
            modal.classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('show');
        }
        
        function confirmAction() {
            window.location.href = `?action=${currentAction}&id=${currentId}`;
        }
        
        // Close modal on overlay click
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Image modal (optional enhancement)
        function showImageModal(src) {
            const modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.9);z-index:10001;display:flex;align-items:center;justify-content:center;cursor:pointer;';
            modal.onclick = () => document.body.removeChild(modal);
            
            const img = document.createElement('img');
            img.src = src;
            img.style.cssText = 'max-width:90%;max-height:90%;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);';
            
            modal.appendChild(img);
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>

