// Slider functionality for product recommendations
let currentSlideIndex = 0;

function slideRecommendations(direction) {
    const track = document.querySelector('.recommendations-track');
    const items = document.querySelectorAll('.recommendation-item');
    const totalItems = items.length;

    if (direction === 'next') {
        currentSlideIndex = (currentSlideIndex + 1) % totalItems;
    } else {
        currentSlideIndex = (currentSlideIndex - 1 + totalItems) % totalItems;
    }

    slideToIndex(currentSlideIndex);
}

function slideToIndex(index) {
    const track = document.querySelector('.recommendations-track');
    const items = document.querySelectorAll('.recommendation-item');
    const dots = document.querySelectorAll('.slider-dot');

    if (!track || items.length === 0) return;

    currentSlideIndex = index;
    const slideWidth = items[0].offsetWidth + 15; // item width + gap
    track.style.transform = `translateX(-${index * slideWidth}px)`;

    // Update dots
    dots.forEach((dot, i) => {
        dot.style.background = i === index ? '#667eea' : '#ddd';
    });
}

// Estimate Shipping Modal Functions
function openEstimateShippingModal() {
    const modal = document.getElementById('estimateShippingModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeEstimateShippingModal() {
    const modal = document.getElementById('estimateShippingModal');
    const result = document.getElementById('shippingResult');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    if (result) {
        result.style.display = 'none';
    }
}

function calculateShipping(event) {
    event.preventDefault();

    const state = document.getElementById('shippingState').value;
    const pincode = document.getElementById('shippingPincode').value;

    if (!state || !pincode) {
        alert('Please fill in all fields');
        return;
    }

    // Validate pincode
    if (!/^[0-9]{6}$/.test(pincode)) {
        alert('Please enter a valid 6-digit pincode');
        return;
    }

    // Calculate delivery days based on state (you can customize this logic)
    let minDays = 4;
    let maxDays = 10;

    // Metro cities get faster delivery
    const metroCities = ['Delhi', 'Maharashtra', 'Karnataka', 'Tamil Nadu', 'Telangana', 'West Bengal'];
    if (metroCities.includes(state)) {
        minDays = 3;
        maxDays = 6;
    }

    // Remote areas take longer
    const remoteStates = ['Arunachal Pradesh', 'Mizoram', 'Nagaland', 'Manipur', 'Meghalaya', 'Tripura', 'Ladakh', 'Jammu and Kashmir'];
    if (remoteStates.includes(state)) {
        minDays = 7;
        maxDays = 14;
    }

    // Show result
    const result = document.getElementById('shippingResult');
    const deliveryDays = document.getElementById('deliveryDays');

    if (deliveryDays) {
        deliveryDays.textContent = `${minDays}-${maxDays} business days`;
    }

    if (result) {
        result.style.display = 'block';
        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Add recommended product to cart
function addRecommendedToCart(productId) {
    // Use existing add to cart functionality
    if (typeof window.addToCart === 'function') {
        window.addToCart(productId);
    } else {
        // Fallback: direct AJAX call
        fetch('add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload cart sidebar
                    if (typeof window.openCartSidebar === 'function') {
                        window.openCartSidebar();
                    }
                    // Show success message
                    alert('Product added to cart!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add product to cart');
            });
    }
}

// Close modal when clicking outside
document.addEventListener('click', function (event) {
    const modal = document.getElementById('estimateShippingModal');
    if (event.target === modal) {
        closeEstimateShippingModal();
    }
});

console.log('✅ Cart sidebar enhancements loaded');
