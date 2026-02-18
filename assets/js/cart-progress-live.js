// Cart Progress Live
console.log('📈 Cart Progress Live Initialized');

function updateCartProgress() {
    // Basic progress bar update logic if a progress bar exists
    const progressBar = document.querySelector('.cart-progress-bar');
    if (!progressBar) return;

    const threshold = 750;
    const subtotalEl = document.getElementById('cartSubtotal');
    if (!subtotalEl) return;

    const subtotalText = subtotalEl.textContent.replace('₹', '').replace(',', '');
    const subtotal = parseFloat(subtotalText) || 0;

    const percentage = Math.min((subtotal / threshold) * 100, 100);
    progressBar.style.width = percentage + '%';
}

document.addEventListener('DOMContentLoaded', updateCartProgress);
document.addEventListener('cartUpdated', updateCartProgress);
