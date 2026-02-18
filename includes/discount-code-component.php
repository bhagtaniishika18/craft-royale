<?php
// Get applied discount from session
$applied_discount = isset($_SESSION['applied_discount']) ? $_SESSION['applied_discount'] : null;
?>

<style>
    #discount-section {
        margin: 20px 0;
    }
    
    .discount-input-wrapper {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    #discount-code-input {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    #discount-code-input:focus {
        outline: none;
        border-color: #2fc7b4;
    }
    
    #apply-discount-btn {
        padding: 12px 24px;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }
    
    #apply-discount-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(47, 199, 180, 0.3);
    }
    
    #apply-discount-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .applied-discount-box {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 2px solid #28a745;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .applied-discount-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .discount-icon {
        width: 40px;
        height: 40px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .discount-details h4 {
        margin: 0;
        color: #155724;
        font-size: 16px;
        font-weight: 700;
    }
    
    .discount-details p {
        margin: 4px 0 0 0;
        color: #155724;
        font-size: 13px;
    }
    
    #remove-discount-btn {
        padding: 8px 16px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 13px;
    }
    
    #remove-discount-btn:hover {
        background: #c82333;
    }
    
    .discount-message {
        padding: 12px 15px;
        border-radius: 8px;
        margin-top: 10px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .discount-message-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .discount-message-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .discount-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-top: 1px dashed #e0e0e0;
        color: #28a745;
        font-weight: 600;
    }
    
    .discount-row i {
        margin-right: 8px;
    }
</style>

<div id="discount-section">
    <!-- Discount Input (shown when no discount applied) -->
    <div id="discount-input-section" style="display: <?php echo $applied_discount ? 'none' : 'block'; ?>">
        <div class="discount-input-wrapper">
            <input 
                type="text" 
                id="discount-code-input" 
                placeholder="Enter discount code (e.g., FIRSTSALE)"
                maxlength="50"
            >
            <button id="apply-discount-btn">
                <i class="fas fa-tag"></i> Apply
            </button>
        </div>
    </div>
    
    <!-- Applied Discount (shown when discount is applied) -->
    <div id="applied-discount-section" style="display: <?php echo $applied_discount ? 'block' : 'none'; ?>">
        <div class="applied-discount-box">
            <div class="applied-discount-info">
                <div class="discount-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="discount-details">
                    <h4 id="applied-code-display"><?php echo $applied_discount ? htmlspecialchars($applied_discount['code']) : ''; ?></h4>
                    <p id="applied-percentage-display"><?php echo $applied_discount ? $applied_discount['percentage'] . '% OFF' : ''; ?> Applied</p>
                </div>
            </div>
            <button id="remove-discount-btn">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    </div>
</div>

<!-- Discount row in cart summary (to be included in summary section) -->
<div id="discount-row" class="discount-row" style="display: <?php echo $applied_discount ? 'flex' : 'none'; ?>">
    <span>
        <i class="fas fa-tag"></i>
        Discount (<span id="discount-code-name"><?php echo $applied_discount ? htmlspecialchars($applied_discount['code']) : ''; ?></span>):
    </span>
    <span id="discount-amount">
        -₹<?php echo $applied_discount ? number_format($applied_discount['discount_amount'], 2) : '0.00'; ?>
    </span>
</div>
