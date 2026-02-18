// Free Shipping Manager
console.log('🚚 Free Shipping Manager Initialized');

function updateFreeShippingBanner() {
    // Current threshold is ₹750
    const threshold = 750;
    const subtotalEl = document.getElementById('cartSubtotal');
    if (!subtotalEl) return;

    const subtotalText = subtotalEl.textContent.replace('₹', '').replace(',', '');
    const subtotal = parseFloat(subtotalText) || 0;

    const bannerText = document.querySelector('.banner-text');
    if (!bannerText) return;

    if (subtotal >= threshold) {
        bannerText.innerHTML = '🎉 Congratulations! You have unlocked <strong>Free Shipping</strong>! 📦';
    } else {
        const remaining = threshold - subtotal;
        bannerText.innerHTML = `🚚 Add <strong>₹${remaining.toFixed(2)}</strong> more to get <strong>Free Shipping</strong>!`;
    }
}

// Update on load
document.addEventListener('DOMContentLoaded', updateFreeShippingBanner);

// Listen for cart updates
document.addEventListener('cartUpdated', updateFreeShippingBanner);
