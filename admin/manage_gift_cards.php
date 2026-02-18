<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check for success/error messages
$deleted_message = isset($_GET['deleted']) ? 'Gift card deleted successfully!' : '';

// Get all gift cards
$gift_cards_query = "SELECT * FROM gift_cards ORDER BY created_at DESC";
$gift_cards_result = mysqli_query($conn, $gift_cards_query);
$gift_cards = [];
while ($card = mysqli_fetch_assoc($gift_cards_result)) {
    $gift_cards[] = $card;
}

// Get statistics
$total_cards = count($gift_cards);
$active_cards = 0;
$used_cards = 0;
$total_value = 0;
$used_value = 0;

foreach ($gift_cards as $card) {
    $total_value += $card['amount'];
    if ($card['status'] == 'active') {
        $active_cards++;
    } elseif ($card['status'] == 'used') {
        $used_cards++;
        $used_value += $card['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gift Cards - Craft Royale</title>
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
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

        .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2b2b2b;
            border-bottom: 2px solid #e0e0e0;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-active { background: #d4edda; color: #155724; }
        .status-used { background: #d1ecf1; color: #0c5460; }
        .status-expired { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #8B5CF6;
            color: #fff;
        }

        .btn-view:hover {
            background: #7C3AED;
        }

        .btn-edit {
            background: #28a745;
            color: #fff;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Manage Gift Cards</h1>
                    <p class="welcome-text">View and manage all gift cards</p>
                </div>
                <div class="header-right">
                    <a href="add_gift_card.php" class="submit-btn">
                        <i class="fas fa-plus"></i> Create Gift Card
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($deleted_message): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?= $deleted_message ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Gift Cards</h3>
                        <p class="stat-number"><?= $total_cards ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Cards</h3>
                        <p class="stat-number"><?= $active_cards ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Used Cards</h3>
                        <p class="stat-number"><?= $used_cards ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Value</h3>
                        <p class="stat-number">₹<?= number_format($total_value, 2) ?></p>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Card Number</th>
                                <th>Amount</th>
                                <th>To</th>
                                <th>From</th>
                                <th>Status</th>
                                <th>Valid Till</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($gift_cards)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                        No gift cards found. <a href="add_gift_card.php">Create your first gift card</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($gift_cards as $card): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($card['card_number']) ?></strong></td>
                                        <td>₹<?= number_format($card['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($card['to_name']) ?></td>
                                        <td><?= htmlspecialchars($card['from_name']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $card['status'] ?>">
                                                <?= ucfirst($card['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($card['valid_till'])) ?></td>
                                        <td><?= date('d M Y', strtotime($card['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view_gift_card.php?id=<?= $card['id'] ?>" class="btn-small btn-view">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="edit_gift_card.php?id=<?= $card['id'] ?>" class="btn-small" style="background: #28a745; color: #fff;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button onclick="confirmDelete(<?= $card['id'] ?>)" class="btn-small" style="background: #dc3545; color: #fff;">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
            <p class="modal-desc">Are you sure you want to delete this gift card? This action is permanent and cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" onclick="closeDeleteModal()">No, Keep it</button>
                <button onclick="submitDelete()" class="modal-btn btn-confirm" style="display: flex; align-items: center;">Yes, Delete it</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for secure deletion -->
    <form id="deleteForm" method="POST" action="delete_gift_card.php" style="display: none;">
        <input type="hidden" name="id" id="deleteId">
        <input type="hidden" name="confirm_delete" value="1">
    </form>

    <script>
        function confirmDelete(cardId) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('deleteId').value = cardId;
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
