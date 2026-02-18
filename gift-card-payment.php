<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['gift_card_data'])) {
    header("Location: gift-cards.php");
    exit();
}

$gift_card_data = $_SESSION['gift_card_data'];

// Get design details
$design_query = "SELECT * FROM gift_card_designs WHERE design_key = '{$gift_card_data['design']}'";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

// Generate card number
function generateCardNumber() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4));
}

// Handle payment
if (isset($_POST['complete_payment'])) {
    $card_number = generateCardNumber();
    $pin = mysqli_real_escape_string($conn, $gift_card_data['pin']);
    $amount = mysqli_real_escape_string($conn, $gift_card_data['amount']);
    $design = mysqli_real_escape_string($conn, $gift_card_data['design']);
    $to_name = mysqli_real_escape_string($conn, $gift_card_data['to_name']);
    $to_email = !empty($gift_card_data['to_email']) ? mysqli_real_escape_string($conn, $gift_card_data['to_email']) : null;
    $to_phone = !empty($gift_card_data['to_email']) && !filter_var($gift_card_data['to_email'], FILTER_VALIDATE_EMAIL) ? mysqli_real_escape_string($conn, $gift_card_data['to_email']) : null;
    $from_name = mysqli_real_escape_string($conn, $gift_card_data['from_name']);
    $from_phone = mysqli_real_escape_string($conn, $gift_card_data['from_phone']);
    $message = mysqli_real_escape_string($conn, $gift_card_data['message']);
    $valid_till = date('Y-m-d', strtotime('+1 year'));
    
    $insert_query = "INSERT INTO gift_cards (card_number, pin, amount, design, to_name, to_email, to_phone, from_name, from_phone, message, status, valid_till) 
                    VALUES ('$card_number', '$pin', '$amount', '$design', '$to_name', " . 
                    ($to_email && filter_var($to_email, FILTER_VALIDATE_EMAIL) ? "'$to_email'" : "NULL") . ", " . 
                    ($to_phone ? "'$to_phone'" : "NULL") . ", 
                    '$from_name', '$from_phone', '$message', 'active', '$valid_till')";
    
    if (mysqli_query($conn, $insert_query)) {
        $gift_card_id = mysqli_insert_id($conn);
        
        // Record transaction
        $transaction_query = "INSERT INTO gift_card_transactions (gift_card_id, amount_used, remaining_balance, transaction_type) 
                            VALUES ($gift_card_id, 0, $amount, 'purchase')";
        mysqli_query($conn, $transaction_query);
        
        // Clear session data
        unset($_SESSION['gift_card_data']);
        
        // Redirect to success page
        header("Location: gift-card-success.php?id=$gift_card_id");
        exit();
    } else {
        $error_message = "Error creating gift card: " . mysqli_error($conn);
    }
}

include 'includes/header.php';
?>
<style>
    .payment-page {
        min-height: 80vh;
        background: #f9f9f9;
        padding: 40px 0;
    }

    .payment-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .payment-section {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .payment-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .summary-title {
        font-size: 24px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 20px 0;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .summary-item:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 18px;
    }

    .summary-label {
        color: #666;
    }

    .summary-value {
        color: #2b2b2b;
        font-weight: 600;
    }

    .preview-card {
        width: 100%;
        max-width: 450px;
        aspect-ratio: 16/9;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 24px;
        text-align: center;
        padding: 30px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .payment-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 30px;
    }

    .payment-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4);
    }
</style>

<div class="payment-page">
    <div class="payment-container">
        <div class="payment-section">
            <h2 class="summary-title">Payment Summary</h2>
            
            <div class="payment-summary">
                <div style="flex-shrink: 0;">
                    <div class="preview-card" style="background: <?= !empty($design['background_image']) ? "url('" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; background-size: cover; background-position: center; color: <?= $design['text_color'] ?>;">
                        <div style="font-size: 16px; margin-bottom: 8px;"><?= !empty($design['description']) ? htmlspecialchars($design['description']) : "'Tis the season of joy!" ?></div>
                        <div style="font-size: 28px; font-weight: 800; margin-bottom: 15px;"><?= htmlspecialchars($design['title']) ?></div>
                        <div style="font-size: 14px; opacity: 0.9;">CRAFT ROYALE</div>
                    </div>
                </div>
                <div>
                    <div class="summary-item">
                        <span class="summary-label">Gift Card Amount</span>
                        <span class="summary-value">₹<?= number_format($gift_card_data['amount'], 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">To</span>
                        <span class="summary-value"><?= htmlspecialchars($gift_card_data['to_name']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">From</span>
                        <span class="summary-value"><?= htmlspecialchars($gift_card_data['from_name']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Design</span>
                        <span class="summary-value"><?= htmlspecialchars($design['design_name']) ?></span>
                    </div>
                    <div class="summary-item" style="border-top: 2px solid #e0e0e0; margin-top: 10px; padding-top: 20px;">
                        <span class="summary-label" style="font-size: 18px; color: #2b2b2b;">Total Amount</span>
                        <span class="summary-value" style="font-size: 22px; color: #e91e63;">₹<?= number_format($gift_card_data['amount'], 2) ?></span>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <button type="submit" name="complete_payment" class="payment-btn">
                    Complete Payment - ₹<?= number_format($gift_card_data['amount'], 2) ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
