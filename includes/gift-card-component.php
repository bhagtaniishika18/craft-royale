<?php
// Get applied gift card from session
$applied_gift_card = isset($_SESSION['applied_gift_card']) ? $_SESSION['applied_gift_card'] : null;
?>

<style>
    #gift-card-section {
        margin: 0;
        padding-top: 0;
    }
    
    .gift-card-input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .gift-card-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    
    .gift-card-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .gift-card-field label {
        font-size: 12px;
        font-weight: 700;
        color: #666;
        text-transform: uppercase;
    }
    
    #gift-card-number-input,
    #gift-card-pin-input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        box-sizing: border-box;
    }
    
    #gift-card-number-input:focus,
    #gift-card-pin-input:focus {
        outline: none;
        border-color: #764ba2;
    }
    
    #apply-gift-card-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #764ba2, #667eea);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    #apply-gift-card-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
    }
    
    #apply-gift-card-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .applied-gift-card-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 2px solid #2196f3;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .applied-gift-card-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .gift-card-icon {
        width: 40px;
        height: 40px;
        background: #2196f3;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .gift-card-details h4 {
        margin: 0;
        color: #0d47a1;
        font-size: 14px;
        font-weight: 700;
    }
    
    .gift-card-details p {
        margin: 4px 0 0 0;
        color: #0d47a1;
        font-size: 12px;
    }
    
    #remove-gift-card-btn {
        padding: 8px 12px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 12px;
    }
    
    #remove-gift-card-btn:hover {
        background: #d32f2f;
    }
    
    .gift-card-message {
        padding: 10px 15px;
        border-radius: 6px;
        margin-top: 10px;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideDown 0.3s ease-out;
    }
    
    .gift-card-message-success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }
    
    .gift-card-message-error {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }
</style>

<div id="gift-card-section">
    <!-- Gift Card Input (shown when no gift card applied) -->
    <div id="gift-card-input-section" style="display: <?php echo $applied_gift_card ? 'none' : 'block'; ?>">
        <div class="gift-card-input-wrapper">
            <div class="gift-card-inputs">
                <div class="gift-card-field">
                    <label for="gift-card-number-input">Card Number (16 characters)</label>
                    <input 
                        type="text" 
                        id="gift-card-number-input" 
                        placeholder="7B1F-B4B6-182E-E89A"
                        maxlength="19"
                        style="text-transform: uppercase;"
                    >
                </div>
                <div class="gift-card-field">
                    <label for="gift-card-pin-input">PIN</label>
                    <input 
                        type="password" 
                        id="gift-card-pin-input" 
                        placeholder="PIN"
                        maxlength="6"
                    >
                </div>
            </div>
            <button id="apply-gift-card-btn">
                <i class="fas fa-gift"></i> Apply Gift Card
            </button>
        </div>
    </div>
    
    <!-- Applied Gift Card (shown when gift card is applied) -->
    <div id="applied-gift-card-section" style="display: <?php echo $applied_gift_card ? 'block' : 'none'; ?>">
        <div class="applied-gift-card-box">
            <div class="applied-gift-card-info">
                <div class="gift-card-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="gift-card-details">
                    <h4 id="applied-card-display"><?php echo $applied_gift_card ? 'Card: ****' . substr($applied_gift_card['card_number'], -4) : ''; ?></h4>
                    <p id="applied-balance-display">Balance: ₹<?php echo $applied_gift_card ? number_format($applied_gift_card['balance'], 2) : '0.00'; ?></p>
                </div>
            </div>
            <button id="remove-gift-card-btn">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    </div>
</div>
