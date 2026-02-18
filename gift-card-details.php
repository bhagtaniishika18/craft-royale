<?php
session_start();
include 'includes/db.php';

$design_key = $_GET['design'] ?? 'happy_birthday';

// Get design details
$design_query = "SELECT * FROM gift_card_designs WHERE design_key = '$design_key' AND is_active = 1";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

if (!$design) {
    header("Location: gift-cards.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if (isset($_POST['proceed_to_pay'])) {
    $_SESSION['gift_card_data'] = [
        'design' => $design_key,
        'to_name' => $_POST['to_name'],
        'to_email' => $_POST['to_email'],
        'from_name' => $_POST['from_name'],
        'from_phone' => $_POST['from_phone'],
        'message' => $_POST['message'],
        'amount' => $_POST['amount'],
        'pin' => $_POST['pin']
    ];
    header("Location: gift-card-payment.php");
    exit();
}

include 'includes/header.php';
?>
<style>
    .gift-card-page {
        min-height: 80vh;
        background: #f9f9f9;
        padding: 40px 0;
    }

    .gift-card-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
    }

    .progress-steps {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
        padding: 20px;
        background: #fff;
        border-radius: 12px;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
    }

    .step.active .step-number {
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        color: #fff;
    }

    .step.completed .step-number {
        background: #28a745;
        color: #fff;
    }

    .step.inactive .step-number {
        background: #e0e0e0;
        color: #999;
    }

    .step-label {
        font-weight: 600;
        color: #2b2b2b;
    }

    .step.inactive .step-label {
        color: #999;
    }

    .form-section {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
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

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: inherit;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #e91e63;
        box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .char-count {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    .amount-presets {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .amount-preset {
        width: 80px;
        height: 80px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }

    .amount-preset:hover {
        border-color: #e91e63;
        background: rgba(233, 30, 99, 0.05);
    }

    .amount-preset.selected {
        border-color: #e91e63;
        background: #e91e63;
        color: #fff;
    }

    .note-section {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-top: 30px;
    }

    .note-section h4 {
        font-size: 14px;
        font-weight: 700;
        color: #856404;
        margin: 0 0 10px 0;
    }

    .note-section ul {
        margin: 0;
        padding-left: 20px;
        color: #856404;
        font-size: 13px;
    }

    .note-section li {
        margin-bottom: 5px;
    }

    .proceed-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 30px;
    }

    .proceed-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(233, 30, 99, 0.4);
    }

    .preview-section {
        position: sticky;
        top: 20px;
        height: fit-content;
    }

    .gift-card-preview {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .preview-card {
        width: 100%;
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
    }

    .preview-details {
        font-size: 13px;
        color: #666;
    }

    .preview-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .preview-row:last-child {
        border-bottom: none;
    }

    .preview-label {
        font-weight: 600;
        color: #2b2b2b;
    }

    .preview-value {
        color: #666;
    }

    @media (max-width: 1024px) {
        .gift-card-container {
            grid-template-columns: 1fr;
        }

        .preview-section {
            position: relative;
            top: 0;
        }
    }
</style>

<div class="gift-card-page">
    <div class="gift-card-container">
        <div class="form-section">
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-number">✓</div>
                    <span class="step-label">Choose design</span>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span class="step-label">Enter Details</span>
                </div>
                <div class="step inactive">
                    <div class="step-number">3</div>
                    <span class="step-label">Payment</span>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="post" id="giftCardForm">
                <div class="section-title">Who is this for?</div>
                <div class="form-group">
                    <label>To</label>
                    <input type="text" name="to_name" id="to_name" required placeholder="Recipient's name">
                </div>
                <div class="form-group">
                    <label>Recipient's Phone Or Email</label>
                    <input type="text" name="to_email" id="to_email" placeholder="Email or Phone number">
                </div>

                <div class="section-title" style="margin-top: 30px;">Message</div>
                <div class="form-group">
                    <textarea name="message" id="message" maxlength="500" placeholder="Here's a little something to make your day!">Here's a little something to make your day!</textarea>
                    <div class="char-count"><span id="charCount">457</span> characters left</div>
                </div>

                <div class="section-title" style="margin-top: 30px;">How much would you like to gift?</div>
                <div class="form-group">
                    <label>Enter Amount (Max. ₹10,000)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="100" max="10000" required placeholder="Enter amount">
                    <div class="amount-presets">
                        <div class="amount-preset" onclick="setAmount(250)">₹250</div>
                        <div class="amount-preset" onclick="setAmount(500)">₹500</div>
                        <div class="amount-preset" onclick="setAmount(1000)">₹1000</div>
                        <div class="amount-preset" onclick="setAmount(2000)">₹2000</div>
                    </div>
                </div>

                <div class="section-title" style="margin-top: 30px;">Sender Information</div>
                <div class="form-group">
                    <label>From</label>
                    <input type="text" name="from_name" id="from_name" required placeholder="Your name">
                </div>
                <div class="form-group">
                    <label>Your Phone</label>
                    <input type="tel" name="from_phone" id="from_phone" required placeholder="Your phone number">
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">We will be sending you the confirmation over here</div>
                </div>

                <div class="form-group">
                    <label>PIN (4-10 digits) *</label>
                    <input type="text" name="pin" id="pin" pattern="[0-9]{4,10}" required placeholder="Enter 4-10 digit PIN" maxlength="10">
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">You will need this PIN to use the gift card</div>
                </div>

                <div class="note-section">
                    <h4>Please Note</h4>
                    <ul>
                        <li>Gift Card can't be canceled once after issued.</li>
                        <li>Recipient Email can't be modified as the GC is immediately sent after the payment.</li>
                    </ul>
                </div>

                <button type="submit" name="proceed_to_pay" class="proceed-btn">PROCEED TO PAY ></button>
            </form>
        </div>

        <div class="preview-section">
            <div class="gift-card-preview">
                <div class="preview-card" id="previewCard" style="background: <?= !empty($design['background_image']) ? "url('" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; background-size: cover; background-position: center; color: <?= $design['text_color'] ?>;">
                    <div style="font-size: 18px; margin-bottom: 10px;"><?= !empty($design['description']) ? htmlspecialchars($design['description']) : "'Tis the season of joy!" ?></div>
                    <div style="font-size: 32px; font-weight: 800; margin-bottom: 20px;"><?= htmlspecialchars($design['title']) ?></div>
                    <div style="font-size: 16px; opacity: 0.9;">CRAFT ROYALE</div>
                </div>

                <div class="preview-details">
                    <div style="margin-bottom: 15px;">
                        <div style="font-weight: 600; color: #2b2b2b; margin-bottom: 5px;">Hi,</div>
                        <div id="previewMessage" style="color: #666;">Here's a little something to make your day!</div>
                    </div>

                    <div class="preview-row">
                        <span class="preview-label">Amount</span>
                        <span class="preview-value" id="previewAmount">₹0.00</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Valid till</span>
                        <span class="preview-value" id="previewValidTill"><?= date('d/m/Y', strtotime('+1 year')) ?></span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">GIFT CARD NUMBER</span>
                        <span class="preview-value">XXXX XXXX XXXX XXXX</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">PIN</span>
                        <span class="preview-value">XXXX</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character count for message
const messageInput = document.getElementById('message');
const charCount = document.getElementById('charCount');

messageInput.addEventListener('input', function() {
    const remaining = 500 - this.value.length;
    charCount.textContent = remaining;
    document.getElementById('previewMessage').textContent = this.value || 'Here\'s a little something to make your day!';
});

// Amount presets
function setAmount(amount) {
    document.getElementById('amount').value = amount;
    document.getElementById('previewAmount').textContent = '₹' + amount.toFixed(2);
    
    // Update preset buttons
    document.querySelectorAll('.amount-preset').forEach(btn => {
        btn.classList.remove('selected');
        if (btn.textContent === '₹' + amount) {
            btn.classList.add('selected');
        }
    });
}

// Real-time preview updates
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    document.getElementById('previewAmount').textContent = '₹' + amount.toFixed(2);
    
    // Update preset selection
    document.querySelectorAll('.amount-preset').forEach(btn => {
        btn.classList.remove('selected');
        if (parseFloat(btn.textContent.replace('₹', '')) === amount) {
            btn.classList.add('selected');
        }
    });
});

document.getElementById('to_name').addEventListener('input', function() {
    // Update preview if needed
});

document.getElementById('from_name').addEventListener('input', function() {
    // Update preview if needed
});
</script>

<?php include 'includes/footer.php'; ?>
