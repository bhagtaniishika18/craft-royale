<?php
include 'includes/db.php';

$gift_card_id = $_GET['id'] ?? 0;

$card_query = "SELECT * FROM gift_cards WHERE id = $gift_card_id";
$card_result = mysqli_query($conn, $card_query);
$card = mysqli_fetch_assoc($card_result);

if (!$card) {
    header("Location: gift-cards.php");
    exit();
}

// Get design
$design_query = "SELECT * FROM gift_card_designs WHERE design_key = '{$card['design']}'";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

include 'includes/header.php';
?>
<style>
    .success-page {
        min-height: 80vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }

    .success-page::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(233, 30, 99, 0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .success-container {
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .success-icon {
        font-size: 100px;
        margin-bottom: 20px;
        animation: bounce 1s ease-in-out;
        display: inline-block;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .success-title {
        font-size: 42px;
        font-weight: 800;
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0 0 15px 0;
        line-height: 1.2;
    }

    .success-message {
        font-size: 20px;
        color: #666;
        margin-bottom: 50px;
        font-weight: 500;
    }

    .gift-card-display {
        background: #fff;
        border-radius: 24px;
        padding: 50px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(233, 30, 99, 0.1);
    }

    .gift-card-display::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #e91e63, #9c27b0, #e91e63);
        background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .card-preview {
        width: 100%;
        max-width: 550px;
        aspect-ratio: 16/9;
        border-radius: 20px;
        margin: 0 auto 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 26px;
        text-align: center;
        padding: 40px;
        position: relative;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        overflow: hidden;
    }

    .card-preview::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        animation: rotate 10s linear infinite;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .card-details {
        text-align: left;
        max-width: 550px;
        margin: 0 auto;
        background: #f8f9fa;
        border-radius: 16px;
        padding: 30px;
    }

    .greeting-section {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        border-left: 4px solid #e91e63;
    }

    .greeting-name {
        font-weight: 700;
        color: #2b2b2b;
        font-size: 18px;
        margin-bottom: 8px;
    }

    .greeting-message {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-bottom: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .detail-row:hover {
        background: rgba(233, 30, 99, 0.05);
        margin: 0 -15px;
        padding-left: 15px;
        padding-right: 15px;
        border-radius: 8px;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 700;
        color: #2b2b2b;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        color: #e91e63;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 16px;
        background: #fff;
        padding: 8px 15px;
        border-radius: 8px;
        border: 2px solid rgba(233, 30, 99, 0.2);
    }

    .action-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 40px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 16px 36px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        color: #fff;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4);
    }

    .btn-secondary {
        background: #fff;
        color: #2b2b2b;
        border: 2px solid #e0e0e0;
    }

    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #e91e63;
        color: #e91e63;
        transform: translateY(-3px);
    }

    .btn i {
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .success-title {
            font-size: 32px;
        }

        .success-icon {
            font-size: 80px;
        }

        .gift-card-display {
            padding: 30px 20px;
        }

        .card-preview {
            padding: 30px 20px;
            font-size: 20px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="success-page">
    <div class="success-container">
        <div class="success-icon">🎉</div>
        <h1 class="success-title">Gift Card Created Successfully!</h1>
        <p class="success-message">Your gift card has been created and is ready to use.</p>

        <div class="gift-card-display">
            <div class="card-preview" style="background: <?= !empty($design['background_image']) ? "url('" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; background-size: cover; background-position: center; color: <?= $design['text_color'] ?>;">
                <div style="font-size: 18px; margin-bottom: 10px;"><?= !empty($design['description']) ? htmlspecialchars($design['description']) : "'Tis the season of joy!" ?></div>
                <div style="font-size: 32px; font-weight: 800; margin-bottom: 20px;"><?= htmlspecialchars($design['title']) ?></div>
                <div style="font-size: 16px; opacity: 0.9;">CRAFT ROYALE</div>
            </div>

            <div class="card-details">
                <div class="greeting-section">
                    <div class="greeting-name">Hi <?= htmlspecialchars($card['to_name']) ?>,</div>
                    <div class="greeting-message"><?= nl2br(htmlspecialchars($card['message'])) ?></div>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value">₹<?= number_format($card['amount'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Valid till</span>
                    <span class="detail-value"><?= date('d/m/Y', strtotime($card['valid_till'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">GIFT CARD NUMBER</span>
                    <span class="detail-value"><?= htmlspecialchars($card['card_number']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">PIN</span>
                    <span class="detail-value"><?= htmlspecialchars($card['pin']) ?></span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="gift-cards.php" class="btn btn-secondary">
                <i class="fas fa-gift"></i> Create Another
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
