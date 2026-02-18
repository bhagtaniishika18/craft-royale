<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$card_id = $_GET['id'] ?? 0;
$card_query = "SELECT * FROM gift_cards WHERE id = $card_id";
$card_result = mysqli_query($conn, $card_query);
$card = mysqli_fetch_assoc($card_result);

if (!$card) {
    header("Location: manage_gift_cards.php");
    exit();
}

// Get design
$design_query = "SELECT * FROM gift_card_designs WHERE design_key = '{$card['design']}'";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

// Get transactions
$transactions_query = "SELECT * FROM gift_card_transactions WHERE gift_card_id = $card_id ORDER BY created_at DESC";
$transactions_result = mysqli_query($conn, $transactions_query);
$transactions = [];
while ($txn = mysqli_fetch_assoc($transactions_result)) {
    $transactions[] = $txn;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Gift Card - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .card-view-container {
            max-width: 1000px;
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
            margin-bottom: 30px;
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

        .card-preview-section {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #f1f5f9;
        }

        .card-preview {
            width: 100%;
            max-width: 500px;
            aspect-ratio: 16/9;
            border-radius: 20px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 24px;
            text-align: center;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .card-details-section {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: 1px solid #f1f5f9;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .detail-item {
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            background: #fff;
            border-color: #8B5CF6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.1);
        }

        .detail-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 16px;
            color: #1e293b;
            font-weight: 700;
        }

        .transactions-section {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 25px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #8B5CF6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>View Gift Card</h1>
                    <p class="welcome-text">Gift Card Details</p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="manage_gift_cards.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Gift Cards
                </a>

                <div class="card-view-container">
                    <div class="card-preview-section">
                        <div class="card-preview" style="background: <?= !empty($design['background_image']) ? "url('../" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; background-size: cover; background-position: center; color: <?= $design['text_color'] ?>;">
                            <div style="font-size: 18px; margin-bottom: 10px;"><?= !empty($design['description']) ? htmlspecialchars($design['description']) : "'Tis the season of joy!" ?></div>
                            <div style="font-size: 32px; font-weight: 800; margin-bottom: 20px;"><?= htmlspecialchars($design['title']) ?></div>
                            <div style="font-size: 16px; opacity: 0.9;">CRAFT ROYALE</div>
                        </div>
                    </div>

                    <div class="card-details-section">
                        <h2 class="section-title">Card Details</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Card Number</div>
                                <div class="detail-value"><?= htmlspecialchars($card['card_number']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">PIN</div>
                                <div class="detail-value"><?= htmlspecialchars($card['pin']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value">₹<?= number_format($card['amount'], 2) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="status-badge status-<?= $card['status'] ?>">
                                        <?= ucfirst($card['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">To (Recipient)</div>
                                <div class="detail-value"><?= htmlspecialchars($card['to_name']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Recipient Contact</div>
                                <div class="detail-value"><?= htmlspecialchars($card['to_email'] ?? $card['to_phone'] ?? 'N/A') ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">From (Sender)</div>
                                <div class="detail-value"><?= htmlspecialchars($card['from_name']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Sender Phone</div>
                                <div class="detail-value"><?= htmlspecialchars($card['from_phone']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Valid Till</div>
                                <div class="detail-value"><?= date('d M Y', strtotime($card['valid_till'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created At</div>
                                <div class="detail-value"><?= date('d M Y, h:i A', strtotime($card['created_at'])) ?></div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <div class="detail-label">Message</div>
                            <div style="color: #2b2b2b; margin-top: 5px;"><?= nl2br(htmlspecialchars($card['message'])) ?></div>
                        </div>
                    </div>

                    <?php if (!empty($transactions)): ?>
                    <div class="transactions-section">
                        <h2 class="section-title">Transaction History</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount Used</th>
                                    <th>Remaining Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td><?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?></td>
                                        <td><?= ucfirst($txn['transaction_type']) ?></td>
                                        <td>₹<?= number_format($txn['amount_used'], 2) ?></td>
                                        <td>₹<?= number_format($txn['remaining_balance'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
