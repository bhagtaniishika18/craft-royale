// Gift Card Handler
class GiftCardManager {
    constructor() {
        this.appliedGiftCard = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        // Check session for applied gift card
        this.checkSession();
    }

    setupEventListeners() {
        const applyBtn = document.getElementById('apply-gift-card-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.applyGiftCard());
        }

        const removeBtn = document.getElementById('remove-gift-card-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => this.removeGiftCard());
        }

        // Support 16 alphanumeric characters with dash formatting
        const numberInput = document.getElementById('gift-card-number-input');
        if (numberInput) {
            numberInput.addEventListener('input', (e) => {
                // Remove non-alphanumeric, then uppercase
                let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

                // Add dashes every 4 characters
                let formattedValue = '';
                for (let i = 0; i < value.length && i < 16; i++) {
                    if (i > 0 && i % 4 === 0) formattedValue += '-';
                    formattedValue += value[i];
                }
                e.target.value = formattedValue;
            });
        }
    }

    async checkSession() {
        // Since we already have it in PHP, we just need to ensure our internal state matches
        // But if needed, we could fetch it here.
    }

    async applyGiftCard() {
        const numberInput = document.getElementById('gift-card-number-input');
        const pinInput = document.getElementById('gift-card-pin-input');
        const number = numberInput.value.trim();
        const pin = pinInput.value.trim();

        if (!number || !pin) {
            this.showMessage('Please enter both card number and PIN', 'error');
            return;
        }

        const applyBtn = document.getElementById('apply-gift-card-btn');
        const originalText = applyBtn.innerHTML;
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const formData = new FormData();
            formData.append('card_number', number);
            formData.append('pin', pin);

            const response = await fetch('apply-gift-card.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage(data.message, 'success');
                // Reload page to reflect changes in PHP summary (safest way to sync all logic)
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Failed to apply gift card. Please try again.', 'error');
        } finally {
            applyBtn.disabled = false;
            applyBtn.innerHTML = originalText;
        }
    }

    async removeGiftCard() {
        try {
            const response = await fetch('remove-gift-card.php', {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('Gift card removed', 'success');
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    showMessage(message, type) {
        // Remove existing messages
        const existingMessage = document.querySelector('.gift-card-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageEl = document.createElement('div');
        messageEl.className = `gift-card-message gift-card-message-${type}`;
        messageEl.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        const section = document.getElementById('gift-card-section');
        if (section) {
            section.appendChild(messageEl);
            setTimeout(() => {
                if (messageEl) messageEl.remove();
            }, 5000);
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.giftCardManager = new GiftCardManager();
});
