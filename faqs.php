<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline preload="auto" webkit-playsinline>
        <source src="assets/images/faq1.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="FAQ Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">FAQ</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">FAQ</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="about-content-section">
    <div class="about-container">
        <div class="about-header">
            <h2 class="about-main-title">
                <span class="title-highlight">Frequently Asked Questions</span>
            </h2>
            <p class="about-tagline">Everything You Need to Know About Shopping with Craft Royale</p>
            <div class="title-underline"></div>
        </div>

        <div class="privacy-content-wrapper">
            <div class="privacy-text-content">
                <div class="privacy-intro-card">
                    <div class="intro-icon">❓</div>
                    <p class="privacy-intro">
                        Welcome to <strong>Craft Royale</strong>! We're here to help answer all your questions about shopping with us. Below you'll find answers to the most frequently asked questions from our Pan India customers.
                    </p>
                </div>

                <div class="faq-section-header">
                    <div class="section-header-icon">🇮🇳</div>
                    <h3 class="faq-section-title">Information for Customers Making Orders from India</h3>
                </div>

                <div class="about-paragraph card-style">
                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How can I make payment?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p><strong>1. Cash on Delivery:</strong> Available across Pan India.</p>
                            <p><strong>2. Online Payment:</strong> Payment by Credit/Debit Card, UPI ID, Wallet ID, Google Pay & Net banking are acceptable.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How can I check if cash on delivery service is available at my area pin code?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>You can check COD availability by entering your pin code during checkout. If COD is available for your area, it will be displayed as a payment option. Alternatively, you can contact us at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong> to verify COD availability for your location.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">Is there any extra charges for cash on delivery services?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>Yes, a COD charge of <strong>₹100</strong> will apply to all Cash on Delivery orders. This charge covers the additional processing and handling costs associated with COD service.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">What is the courier charge in India?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>Courier charges vary based on the weight of your order and delivery location. The exact shipping charges will be calculated and displayed at checkout before you complete your purchase. We offer competitive shipping rates across all states in India.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">Which courier company does you use for delivery?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>We work with multiple trusted courier partners including major logistics companies to ensure timely and safe delivery of your orders across Pan India. The specific courier partner for your order will be assigned based on your location and will be mentioned in your order confirmation email.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How do I track the status of my order?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>You can track your order status in multiple ways:</p>
                            <ul class="faq-list">
                                <li>Log into your account on our website and check "My Orders" section</li>
                                <li>Use the tracking link provided in your order confirmation email</li>
                                <li>Contact our customer support at <strong>care@craftroyale.com</strong> or <strong>+91-8826002179</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">Will I receive the courier Details?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>Yes, once your order is shipped, you will receive an email with all courier details including:</p>
                            <ul class="faq-list">
                                <li>Tracking number</li>
                                <li>Courier company name</li>
                                <li>Expected delivery date</li>
                                <li>Contact information for the courier</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How can I track my courier parcel?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>You can track your courier parcel using the tracking number provided in your shipping confirmation email. Simply:</p>
                            <ul class="faq-list">
                                <li>Visit the courier company's website</li>
                                <li>Enter your tracking number</li>
                                <li>Or use the tracking link directly from your order confirmation email</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">Can I cancel my order?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>Yes, you can cancel your order if it hasn't been shipped yet. To cancel:</p>
                            <ul class="faq-list">
                                <li>Log into your account and go to "My Orders"</li>
                                <li>Click on "Cancel Order" for the order you wish to cancel</li>
                                <li>Or contact us at <strong>care@craftroyale.com</strong> or <strong>+91-8826002179</strong> within 24 hours of placing the order</li>
                            </ul>
                            <p style="margin-top: 10px;">Once the order is shipped, you can return it as per our return policy.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">What should I do if the courier guy is giving me a damaged parcel?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>If you receive a damaged parcel, please:</p>
                            <ul class="faq-list">
                                <li><strong>Do not accept</strong> the parcel if it appears damaged from outside</li>
                                <li>Take photos of the damaged packaging</li>
                                <li>Contact us immediately at <strong>care@craftroyale.com</strong> or <strong>+91-8826002179</strong></li>
                                <li>We will arrange for a replacement or refund as per your preference</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">I have received a defective item or my order is incorrect?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>If you receive a defective item or incorrect order:</p>
                            <ul class="faq-list">
                                <li>Inform us within <strong>48 hours</strong> of receiving the order</li>
                                <li>Take clear photos of the defective/incorrect item</li>
                                <li>Contact us at <strong>care@craftroyale.com</strong> or <strong>+91-8826002179</strong></li>
                                <li>We will arrange for a replacement or refund immediately</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How I return some or all products from my order after receiving the courier?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>To return products from your order:</p>
                            <ul class="faq-list">
                                <li>Inform us within <strong>48 hours</strong> of receiving your order</li>
                                <li>Ensure products are in original condition with packaging intact</li>
                                <li>Send the product to our return address with the original invoice</li>
                                <li>Return address: <strong>www.craftroyale.com</strong>, C/O Aarvak Garments Pvt Ltd, E-71, 2nd Floor, Sector-06, Noida-201301, Uttar Pradesh, India</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">Who will pay the courier charges at the time of returning the product?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>The courier charges for returning the product will be paid by the <strong>customer</strong>. We do not provide pickup facility, so customers need to arrange and pay for the return shipment. These return shipping charges are non-refundable.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How I will receive the refund for the return products?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>The refunds will be issued to you either through <strong>BANK TRANSFER</strong> of the amount or in the form of <strong>discount coupons</strong>, which you can use in your next purchase. The refund method will be confirmed during the return process.</p>
                        </div>
                    </div>

                    <div class="faq-accordion-item">
                        <div class="faq-accordion-header">
                            <h4 class="faq-accordion-question">How long will it take to get the refunds?</h4>
                            <span class="faq-toggle-icon">+</span>
                        </div>
                        <div class="faq-accordion-content">
                            <p>Once the item is received in our warehouse, it undergoes a Product Inspection, which takes about <strong>1 day</strong>. Typically the refunds are initiated within <strong>3-5 working days</strong> after product inspection. You will receive a confirmation email once the refund is processed.</p>
                        </div>
                    </div>
                </div>

                <div class="about-cta">
                    <div class="cta-icon">💬</div>
                    <p class="cta-text">
                        Still have questions? Our customer support team is here to help! Contact us at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong> from Monday to Friday between <strong>10 a.m. to 5 p.m.</strong>
                    </p>
                    <div class="cta-sparkle">✨</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
/* HERO BANNER - Reusing existing styles */
.embroidery-hero-banner {
    padding: 80px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.embroidery-hero-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
    min-width: 100%;
    min-height: 100%;
}

.embroidery-hero-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.embroidery-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
}

.embroidery-hero-title {
    font-size: 72px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 4px;
    margin: 0 0 20px 0;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
}

.breadcrumb {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 16px;
    color: #fff;
}

.breadcrumb a {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #2fc7b4;
}

.breadcrumb-separator {
    color: #fff;
    margin: 0 4px;
}

.breadcrumb-current {
    color: #2fc7b4;
    font-weight: 600;
}

/* FAQ CONTENT SECTION */
.about-content-section {
    padding: 100px 20px;
    background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
    position: relative;
    overflow: hidden;
}

.about-content-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    clip-path: polygon(0 0, 100% 0, 100% 50%, 0 100%);
    opacity: 0.05;
}

.about-container {
    max-width: 1000px;
    width: 100%;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.about-header {
    text-align: center;
    margin-bottom: 80px;
    position: relative;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.about-main-title {
    font-size: 52px;
    font-weight: 900;
    margin: 0 0 15px 0;
    position: relative;
    display: inline-block;
}

.title-highlight {
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    display: inline-block;
}

.title-highlight::after {
    display: none;
}

.about-tagline {
    font-size: 18px;
    color: #666;
    font-style: italic;
    font-weight: bold;
    margin: 10px 0 25px 0;
}

.title-underline {
    width: 150px;
    height: 5px;
    background: linear-gradient(90deg, transparent, #2fa76b, #2fc7b4, transparent);
    margin: 0 auto;
    border-radius: 3px;
    position: relative;
}

.title-underline::before,
.title-underline::after {
    content: '✦';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: #2fa76b;
    font-size: 20px;
}

.title-underline::before {
    left: -30px;
}

.title-underline::after {
    right: -30px;
}

.privacy-content-wrapper {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

.privacy-text-content {
    line-height: 1.8;
}

.privacy-intro-card {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 40px;
    border: 2px solid rgba(47, 199, 180, 0.2);
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(47, 167, 107, 0.1);
}

.privacy-intro-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(47, 199, 180, 0.1) 1px, transparent 1px);
    background-size: 30px 30px;
    animation: rotate 20s infinite linear;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.intro-icon {
    font-size: 40px;
    margin-bottom: 15px;
    display: inline-block;
    animation: rotate-icon 3s infinite;
}

@keyframes rotate-icon {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-10deg); }
    75% { transform: rotate(10deg); }
}

.privacy-intro {
    font-size: 20px;
    color: #2b2b2b;
    font-weight: 600;
    line-height: 1.9;
    position: relative;
    z-index: 1;
    margin: 0;
}

.privacy-intro strong {
    color: #2fa76b;
    font-weight: 800;
}

.faq-section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.15), rgba(47, 167, 107, 0.15));
    border-radius: 15px;
    border-left: 5px solid #2fa76b;
}

.section-header-icon {
    font-size: 40px;
}

.faq-section-title {
    font-size: 24px;
    font-weight: 800;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.about-paragraph.card-style {
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(47, 199, 180, 0.1);
    position: relative;
    overflow: hidden;
}

.about-paragraph.card-style::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    transform: scaleY(0);
    transition: transform 0.4s ease;
}

.about-paragraph.card-style:hover::before {
    transform: scaleY(1);
}

.about-paragraph.card-style:hover {
    transform: translateX(10px);
    box-shadow: 0 10px 30px rgba(47, 167, 107, 0.15);
}

/* FAQ ACCORDION */
.faq-accordion-item {
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(47, 199, 180, 0.1);
    padding-bottom: 15px;
}

.faq-accordion-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.faq-accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 20px;
    background: #2fc7b4;
    border-radius: 12px;
    transition: all 0.3s ease;
    margin-bottom: 0;
}

.faq-accordion-header:hover {
    background: #2fa76b;
}

.faq-accordion-header.active {
    background: #2fc7b4;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

.faq-accordion-question {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    flex: 1;
    padding-right: 20px;
}

.faq-toggle-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #2fc7b4;
    border-radius: 50%;
    font-size: 24px;
    font-weight: 800;
    flex-shrink: 0;
    transition: all 0.3s ease;
    line-height: 1;
}

.faq-accordion-header.active .faq-toggle-icon {
    color: #2fc7b4;
}

.faq-accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.4s ease;
    padding: 0 20px;
    background: #fff;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

.faq-accordion-content.active {
    max-height: 1000px;
    padding: 20px;
    border-top: 1px solid rgba(47, 199, 180, 0.2);
}

.faq-accordion-content p {
    font-size: 16px;
    color: #555;
    line-height: 1.9;
    margin: 0 0 12px 0;
}

.faq-accordion-content p strong {
    color: #2fa76b;
    font-weight: 700;
}

.faq-list {
    list-style: none;
    padding: 0;
    margin: 15px 0;
}

.faq-list li {
    padding: 12px 0 12px 30px;
    position: relative;
    color: #555;
    font-size: 15px;
    line-height: 1.8;
    border-bottom: 1px solid rgba(47, 199, 180, 0.1);
}

.faq-list li:last-child {
    border-bottom: none;
}

.faq-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 12px;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
}

.about-cta {
    margin-top: 50px;
    padding: 40px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    border-radius: 25px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(47, 167, 107, 0.4);
    position: relative;
    overflow: hidden;
}

.about-cta::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 1px, transparent 1px);
    background-size: 40px 40px;
    animation: rotate 15s infinite linear;
}

.cta-icon {
    font-size: 50px;
    margin-bottom: 15px;
    display: inline-block;
    animation: bounce 2s infinite;
    position: relative;
    z-index: 1;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.cta-text {
    font-size: 20px;
    color: #fff;
    font-weight: 700;
    margin: 0;
    line-height: 1.9;
    position: relative;
    z-index: 1;
}

.cta-text strong {
    font-size: 22px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.cta-sparkle {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 30px;
    animation: sparkle 2s infinite;
}

@keyframes sparkle {
    0%, 100% { opacity: 0.5; transform: scale(1) rotate(0deg); }
    50% { opacity: 1; transform: scale(1.2) rotate(180deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .embroidery-hero-title {
        font-size: 48px;
    }

    .about-main-title {
        font-size: 38px;
    }

    .privacy-intro {
        font-size: 18px;
    }

    .privacy-intro-card {
        padding: 25px;
    }

    .faq-section-title {
        font-size: 20px;
    }

    .about-content-section {
        padding: 60px 20px;
    }

    .about-paragraph.card-style {
        padding: 25px;
    }

    .faq-accordion-question {
        font-size: 16px;
    }

    .faq-accordion-content {
        padding: 15px !important;
    }
}

@media (max-width: 480px) {
    .embroidery-hero-title {
        font-size: 36px;
        letter-spacing: 2px;
    }

    .about-main-title {
        font-size: 28px;
    }

    .privacy-intro {
        font-size: 16px;
    }

    .faq-accordion-question {
        font-size: 15px;
    }

    .faq-accordion-content p {
        font-size: 14px;
    }

    .about-cta {
        padding: 30px 20px;
    }

    .cta-text {
        font-size: 18px;
    }

    .faq-section-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordionHeaders = document.querySelectorAll('.faq-accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const item = this.parentElement;
            const content = item.querySelector('.faq-accordion-content');
            const icon = this.querySelector('.faq-toggle-icon');
            const isActive = this.classList.contains('active');
            
            // Close all other accordions
            accordionHeaders.forEach(otherHeader => {
                if (otherHeader !== this) {
                    otherHeader.classList.remove('active');
                    const otherContent = otherHeader.parentElement.querySelector('.faq-accordion-content');
                    const otherIcon = otherHeader.querySelector('.faq-toggle-icon');
                    otherContent.classList.remove('active');
                    otherIcon.textContent = '+';
                }
            });
            
            // Toggle current accordion
            if (isActive) {
                this.classList.remove('active');
                content.classList.remove('active');
                icon.textContent = '+';
            } else {
                this.classList.add('active');
                content.classList.add('active');
                icon.textContent = '−';
            }
        });
    });
});

// Video autoplay script
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('.embroidery-hero-video');
    if (video) {
        // Force video to play
        video.play().catch(function(error) {
            console.log('Video autoplay prevented:', error);
            // Try again after a short delay
            setTimeout(function() {
                video.play().catch(function(err) {
                    console.log('Video play failed:', err);
                });
            }, 100);
        });
        
        // Ensure video plays when it's ready
        video.addEventListener('loadeddata', function() {
            video.play().catch(function(error) {
                console.log('Video play on load failed:', error);
            });
        });
    }
});
</script>

