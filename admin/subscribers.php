<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM subscribers WHERE id=$id");
    header("Location: subscribers.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM subscribers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribers - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .subscribers-table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .subscribers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .subscribers-table thead {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }

        .subscribers-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subscribers-table td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .subscribers-table tbody tr {
            transition: all 0.3s ease;
        }

        .subscribers-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }

        .subscribers-table tbody tr:last-child td {
            border-bottom: none;
        }

        .subscriber-email {
            font-weight: 600;
            color: #2b2b2b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .subscriber-email i {
            color: #2fc7b4;
        }

        .subscriber-date {
            color: #666;
            font-size: 14px;
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

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
            border-radius: 20px;
            color: #2fa76b;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-delete {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333, #c0392b);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }

        .btn-delete i {
            font-size: 14px;
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
            border: 3px solid rgba(220, 53, 69, 0.2);
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
            background: linear-gradient(90deg, #dc3545, #e74c3c, #dc3545);
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
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(231, 76, 60, 0.05));
            border-bottom: 2px solid rgba(220, 53, 69, 0.1);
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
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
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
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: #fff;
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }

        .modal-btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
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
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Subscribers</h1>
                    <p class="welcome-text">Manage your email subscribers</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="stats-badge">
                    <i class="fas fa-users"></i>
                    <span>Total Subscribers: <?php echo mysqli_num_rows($result); ?></span>
                </div>

                <div class="subscribers-table-container">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <table class="subscribers-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email Address</th>
                                    <th>Subscribed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td>
                                            <div class="subscriber-email">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="subscriber-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('F j, Y', strtotime($row['subscribed_on'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" 
                                               class="btn-delete" 
                                               onclick="showConfirmModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>'); return false;"
                                               title="Remove Subscriber">
                                                <i class="fas fa-trash-alt"></i>
                                                Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No subscribers yet</h3>
                            <p>Subscribers will appear here once they sign up.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Beautiful Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-popup">
            <div class="modal-header">
                <div class="modal-icon">🗑</div>
                <h2 class="modal-title">Delete Subscriber?</h2>
                <p class="modal-message" id="modalMessage"></p>
            </div>
            <div class="modal-body">
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                    <button class="modal-btn modal-btn-confirm" id="modalConfirmBtn" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentId = 0;
        let currentEmail = '';
        
        function showConfirmModal(id, email) {
            currentId = id;
            currentEmail = email;
            
            const modal = document.getElementById('confirmModal');
            const modalMessage = document.getElementById('modalMessage');
            
            modalMessage.textContent = `Are you sure you want to remove "${email}" from the subscribers list? This action cannot be undone.`;
            
            modal.classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('show');
        }
        
        function confirmDelete() {
            window.location.href = `?action=delete&id=${currentId}`;
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
    </script>
</body>
</html>
