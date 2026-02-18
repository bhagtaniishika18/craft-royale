// Discount Code Handler
class DiscountCodeManager {
    constructor() {
        this.appliedDiscount = null;
        this.init();
    }

    init() {
        // Check if discount is stored in session
        this.loadFromSession();

        // Setup event listeners
        this.setupEventListeners();

        // Update display
        this.updateDisplay();
    }

    setupEventListeners() {
        // Apply discount button
        const applyBtn = document.getElementById('apply-discount-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.applyDiscount());
        }

        // Remove discount button
        const removeBtn = document.getElementById('remove-discount-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => this.removeDiscount());
        }

        // Enter key on input
        const input = document.getElementById('discount-code-input');
        if (input) {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyDiscount();
                }
            });
        }
    }

    async loadFromSession() {
        try {
            const response = await fetch('get_applied_discount.php');
            const data = await response.json();

            if (data.success && data.discount) {
                this.appliedDiscount = data.discount;
                this.updateDisplay();
            }
        } catch (error) {
            console.error('Error loading discount from session:', error);
        }
    }

    async applyDiscount() {
        const input = document.getElementById('discount-code-input');
        const code = input.value.trim().toUpperCase();

        if (!code) {
            this.showMessage('Please enter a discount code', 'error');
            return;
        }

        // Get cart subtotal
        const subtotal = this.getCartSubtotal();

        // Show loading
        const applyBtn = document.getElementById('apply-discount-btn');
        const originalText = applyBtn.innerHTML;
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';

        try {
            const formData = new FormData();
            formData.append('code', code);
            formData.append('cart_total', subtotal);

            const response = await fetch('apply_discount_code.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.appliedDiscount = data.code_data;
                this.updateDisplay();
                this.showMessage(data.message, 'success');
                input.value = '';
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            this.showMessage('Error applying discount code. Please try again.', 'error');
            console.error('Error:', error);
        } finally {
            applyBtn.disabled = false;
            applyBtn.innerHTML = originalText;
        }
    }

    async removeDiscount() {
        try {
            const response = await fetch('remove_discount_code.php', {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                this.appliedDiscount = null;
                this.updateDisplay();
                this.showMessage('Discount code removed', 'success');
            }
        } catch (error) {
            console.error('Error removing discount:', error);
        }
    }

    getCartSubtotal() {
        // Try to get from cart summary
        const subtotalElement = document.querySelector('[data-cart-subtotal]');
        if (subtotalElement) {
            const value = subtotalElement.getAttribute('data-cart-subtotal');
            return parseFloat(value) || 0;
        }

        // Fallback: calculate from cart items
        let subtotal = 0;
        document.querySelectorAll('[data-item-total]').forEach(item => {
            subtotal += parseFloat(item.getAttribute('data-item-total')) || 0;
        });

        return subtotal;
    }

    updateDisplay() {
        const subtotal = this.getCartSubtotal();
        let discount = 0;

        // Calculate discount
        if (this.appliedDiscount) {
            discount = this.appliedDiscount.discount_amount;
        }

        const finalTotalBeforeShipping = subtotal - discount;

        // Get shipping cost (if on checkout page)
        let shipping = 0;
        const shippingEl = document.querySelector('[data-shipping-amount]');
        if (shippingEl) {
            shipping = parseFloat(shippingEl.getAttribute('data-shipping-amount')) || 0;
        }

        // Get gift card amount (if on checkout page)
        let giftCard = 0;
        const giftCardEl = document.querySelector('[data-gift-card-amount]');
        if (giftCardEl) {
            giftCard = parseFloat(giftCardEl.getAttribute('data-gift-card-amount')) || 0;
        }

        const totalAmount = subtotal - discount + shipping - giftCard;
        const finalTotal = totalAmount;

        // Recalculate GST Breakdown (assuming 12% GST is included in item prices)
        // Formula: GST = Total - (Total / 1.12)
        // Note: GST is only on products (subtotal - discount), not on shipping
        const gstRate = 12;
        const taxableAmount = subtotal - discount;
        const gstAmount = taxableAmount - (taxableAmount / (1 + (gstRate / 100)));
        const exclSubtotal = taxableAmount - gstAmount;

        // Update Exclusive Subtotal display
        const exclElements = document.querySelectorAll('[data-display-subtotal-excl]');
        exclElements.forEach(el => {
            el.textContent = '₹' + exclSubtotal.toFixed(2);
        });

        // Update GST display
        const gstElements = document.querySelectorAll('[data-display-gst]');
        gstElements.forEach(el => {
            el.textContent = '₹' + gstAmount.toFixed(2);
        });

        // Update discount row
        const discountRow = document.getElementById('discount-row');
        if (discountRow) {
            if (this.appliedDiscount) {
                discountRow.style.display = 'flex';
                const discountCodeName = document.getElementById('discount-code-name');
                const discountAmount = document.getElementById('discount-amount');

                if (discountCodeName) {
                    discountCodeName.textContent = this.appliedDiscount.code;
                }
                if (discountAmount) {
                    discountAmount.textContent = '-₹' + discount.toFixed(2);
                }
            } else {
                discountRow.style.display = 'none';
            }
        }

        // Update total display
        const totalElements = document.querySelectorAll('[data-display-total]');
        totalElements.forEach(el => {
            el.textContent = '₹' + totalAmount.toFixed(2);
        });

        // Show/hide discount input and applied code
        const discountInput = document.getElementById('discount-input-section');
        const appliedCode = document.getElementById('applied-discount-section');

        if (this.appliedDiscount) {
            if (discountInput) discountInput.style.display = 'none';
            if (appliedCode) {
                appliedCode.style.display = 'block';
                const codeDisplay = document.getElementById('applied-code-display');
                const percentageDisplay = document.getElementById('applied-percentage-display');

                if (codeDisplay) {
                    codeDisplay.textContent = this.appliedDiscount.code;
                }
                if (percentageDisplay) {
                    percentageDisplay.textContent = this.appliedDiscount.percentage + '% OFF';
                }
            }
        } else {
            if (discountInput) discountInput.style.display = 'block';
            if (appliedCode) appliedCode.style.display = 'none';
        }
    }

    showMessage(message, type) {
        // Remove existing messages
        const existingMessage = document.querySelector('.discount-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `discount-message discount-message-${type}`;
        messageEl.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        // Insert after discount input
        const discountSection = document.getElementById('discount-section');
        if (discountSection) {
            discountSection.insertAdjacentElement('afterend', messageEl);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                messageEl.remove();
            }, 5000);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.discountManager = new DiscountCodeManager();
});

// Update display when cart changes
document.addEventListener('cartUpdated', () => {
    if (window.discountManager) {
        window.discountManager.updateDisplay();
    }
});
