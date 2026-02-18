<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline preload="auto" webkit-playsinline>
        <source src="assets/images/shipping.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="Domestic Shipping Policy Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">DOMESTIC SHIPPING POLICY</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Domestic Shipping Policy</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="about-content-section">
    <div class="about-container">
        <div class="about-header">
            <h2 class="about-main-title">
                <span class="title-highlight">Domestic Shipping Policy</span>
            </h2>
            <p class="about-tagline">Fast & Reliable Shipping Across Pan India</p>
            <div class="title-underline"></div>
        </div>

        <div class="privacy-content-wrapper">
            <div class="privacy-text-content">
                <div class="privacy-intro-card">
                    <div class="intro-icon">🚚</div>
                    <p class="privacy-intro">
                        At <strong>Craft Royale</strong>, we are committed to delivering your orders safely and on time across Pan India. This policy outlines our shipping procedures, delivery times, and how we handle various shipping scenarios to ensure a smooth shopping experience.
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">🚛</div>
                        <h3 class="about-subtitle">Courier Partners</h3>
                    </div>
                    <p>
                        We partner with trusted courier companies to ensure reliable delivery across Pan India. Our courier partners include:
                    </p>
                    <ul class="product-list">
                        <li><span class="list-icon">✨</span>Bluedart</li>
                        <li><span class="list-icon">✨</span>DTDC</li>
                        <li><span class="list-icon">✨</span>Delhivery</li>
                        <li><span class="list-icon">✨</span>And other trusted logistics partners</li>
                    </ul>
                    <p style="margin-top: 15px;">
                        We select the most appropriate courier partner based on your location to ensure the fastest and most reliable delivery service.
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">💰</div>
                        <h3 class="about-subtitle">Courier Rates</h3>
                    </div>
                    <p>
                        We offer competitive shipping rates across Pan India:
                    </p>
                    <ul class="product-list">
                        <li><span class="list-icon">✨</span><strong>Free Shipping:</strong> For orders ₹750 or above (product value excluding shipping charges)</li>
                        <li><span class="list-icon">✨</span><strong>Shipping Charge:</strong> ₹100 for orders below ₹750 (product value excluding shipping)</li>
                    </ul>
                    <p style="margin-top: 15px;">
                        Shipping charges are calculated at checkout and will be clearly displayed before you complete your purchase.
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">💳</div>
                        <h3 class="about-subtitle">Payment Option</h3>
                    </div>
                    <p><strong>Cash on Delivery (COD):</strong></p>
                    <p>
                        A charge of <strong>₹100</strong> will apply to all Cash on Delivery orders. This charge covers the additional handling and processing costs associated with COD service. This fee is <strong>non-refundable</strong>, even if the order is returned. We encourage customers to use online payment methods to avoid this charge.
                    </p>
                    <p style="margin-top: 15px;"><strong>Online Payment:</strong></p>
                    <p>
                        We accept payments via Credit/Debit Card, Google Pay, UPI ID, Wallet ID, and Net Banking. Online payments help you avoid COD charges and ensure faster order processing.
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">⏱️</div>
                        <h3 class="about-subtitle">Delivery Times</h3>
                    </div>
                    <p><strong>Estimated Delivery Time:</strong></p>
                    <p>
                        We aim to deliver your order within <strong>3-5 working days</strong> (excluding weekends and public holidays) from the date of dispatch.
                    </p>
                    <p style="margin-top: 15px;"><strong>Working Days:</strong></p>
                    <p>
                        Working days are Monday through Friday, excluding public holidays. Orders placed on weekends or holidays will be processed on the next working day.
                    </p>
                    <p style="margin-top: 15px;"><strong>Standard Timeframe:</strong></p>
                    <p>
                        The 3-5 working days timeframe is our standard delivery estimate. Most orders are delivered within this period, but delivery times may vary based on your location and courier partner.
                    </p>
                    <p style="margin-top: 15px;"><strong>Potential Delays:</strong></p>
                    <p>
                        Delivery may be delayed due to unforeseen circumstances such as adverse weather conditions, natural disasters, public holidays, or courier service disruptions. We will keep you informed of any significant delays.
                    </p>
                    <p style="margin-top: 15px;"><strong>Tracking:</strong></p>
                    <p>
                        Once your order is dispatched, you will receive a tracking number via email. You can use this tracking number to monitor your order's progress until delivery.
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">❓</div>
                        <h3 class="about-subtitle">Further Information</h3>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">What should I do if I cannot track my order?</h4>
                        <p class="info-answer">
                            If you are unable to track your order using the tracking number provided in the dispatch email, please contact our customer service team at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong>. We will assist you in tracking your order and provide updated information.
                        </p>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">What happens if I am not available to receive my parcel?</h4>
                        <p class="info-answer">
                            If you are not available when the courier attempts delivery:
                        </p>
                        <ul class="faq-list">
                            <li>The courier will attempt to contact you via phone call or SMS</li>
                            <li>A second delivery attempt will be made within 24 hours</li>
                            <li>After two unsuccessful attempts, the parcel will be held at the courier facility for 3 days</li>
                            <li>If not collected within 3 days, the parcel will be returned to us</li>
                            <li>Resending costs will be borne by the customer</li>
                            <li>Repeated COD refusal may lead to discontinuation of COD option for your account</li>
                        </ul>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">What should I do if I receive a damaged parcel?</h4>
                        <p class="info-answer">
                            If the parcel appears damaged from outside, please <strong>refuse to accept it</strong>. All parcels are dispatched in perfect condition with visible security tape. If you notice any damage, please inform the courier and contact us immediately at <strong>care@craftroyale.com</strong> with photos of the damaged packaging.
                        </p>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">What should I do if a damaged parcel has been left for me?</h4>
                        <p class="info-answer">
                            If a damaged parcel has been accepted by a neighbor or left in your post box, it is considered "Accepted." Please report any damage or missing items within <strong>24 hours</strong> of receipt via email to <strong>care@craftroyale.com</strong> with clear photos. We will investigate and resolve the issue promptly.
                        </p>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">I have only received part of my order</h4>
                        <p class="info-answer">
                            If you receive only part of your order:
                        </p>
                        <ul class="faq-list">
                            <li>Check your order confirmation emails for split shipments</li>
                            <li>Track all shipments using the tracking numbers provided</li>
                            <li>Contact our customer service with your order details</li>
                            <li>Check for any company notifications regarding backorders or changes</li>
                            <li>We will ensure all items are delivered or provide appropriate solutions</li>
                        </ul>
                    </div>

                    <div class="info-item">
                        <h4 class="info-question">I have received a defective item or my order is incorrect</h4>
                        <p class="info-answer">
                            If you receive a defective item or incorrect order, please contact us within <strong>24 hours</strong> of receipt at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong>. Please provide photos and order details. We will arrange for a replacement or refund as per your preference.
                        </p>
                    </div>
                </div>

                <div class="about-cta">
                    <div class="cta-icon">💬</div>
                    <p class="cta-text">
                        If there is still any query regarding shipping & delivery, please contact us at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong>. Our customer support team is here to help!
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

/* SHIPPING CONTENT SECTION */
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

.about-paragraph.card-style {
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
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

.paragraph-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.paragraph-icon {
    font-size: 32px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.3);
}

.about-subtitle {
    font-size: 26px;
    font-weight: 800;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.about-paragraph p {
    font-size: 16px;
    color: #555;
    line-height: 1.9;
    margin: 0 0 15px 0;
}

.about-paragraph p strong {
    color: #2fa76b;
    font-weight: 700;
}

.product-list {
    list-style: none;
    padding: 0;
    margin: 25px 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.product-list li {
    padding: 15px 20px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px;
    color: #2b2b2b;
    font-size: 15px;
    position: relative;
    padding-left: 50px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid transparent;
    font-weight: 500;
}

.list-icon {
    position: absolute;
    left: 15px;
    font-size: 20px;
    top: 50%;
    transform: translateY(-50%);
}

.product-list li:hover {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    transform: translateX(10px) scale(1.02);
    border-color: #2fa76b;
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.2);
}

/* INFO ITEMS */
.info-item {
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px;
    border-left: 4px solid #2fa76b;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.15);
}

.info-question {
    font-size: 18px;
    font-weight: 700;
    color: #2fa76b;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-question::before {
    content: '❓';
    font-size: 20px;
}

.info-answer {
    font-size: 16px;
    color: #555;
    line-height: 1.8;
    margin: 0 0 10px 0;
}

.info-answer strong {
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

    .about-subtitle {
        font-size: 22px;
    }

    .about-content-section {
        padding: 60px 20px;
    }

    .about-paragraph.card-style {
        padding: 25px;
    }

    .product-list {
        grid-template-columns: 1fr;
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

    .about-paragraph p {
        font-size: 15px;
    }

    .product-list li {
        font-size: 14px;
        padding: 12px 15px;
        padding-left: 45px;
    }

    .about-cta {
        padding: 30px 20px;
    }

    .cta-text {
        font-size: 18px;
    }

    .paragraph-icon {
        width: 40px;
        height: 40px;
        font-size: 24px;
    }

    .about-subtitle {
        font-size: 20px;
    }

    .info-question {
        font-size: 16px;
    }

    .info-answer {
        font-size: 14px;
    }
}
</style>

<script>
// Video autoplay
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('.embroidery-hero-video');
    if (video) {
        video.play().catch(function(error) {
            setTimeout(function() {
                video.play().catch(function(err) {
                    console.log('Video play failed:', err);
                });
            }, 100);
        });
    }
});
</script>

