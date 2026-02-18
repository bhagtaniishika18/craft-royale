<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline preload="auto" webkit-playsinline>
        <source src="assets/images/refund.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="Returns & Refund Policy Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">RETURN & REFUND POLICY</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Returns & Refund</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="about-content-section">
    <div class="about-container">
        <div class="about-header">
            <h2 class="about-main-title">
                <span class="title-highlight">Return & Refund Policy</span>
            </h2>
            <p class="about-tagline">Hassle-Free Returns for Your Peace of Mind</p>
            <div class="title-underline"></div>
        </div>

        <div class="privacy-content-wrapper">
            <div class="privacy-text-content">
                <div class="privacy-intro-card">
                    <div class="intro-icon">🔄</div>
                    <p class="privacy-intro">
                        <strong>Craft Royale</strong> is dedicated to upholding the highest standards of customer satisfaction, which is why we offer a 'no nonsense return policy'. We believe in straightforward and transparent processes to ensure a hassle-free experience for our customers. Here's how it works:
                    </p>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">📋</div>
                        <h3 class="about-subtitle">Our Return Policy</h3>
                    </div>
                    
                    <div class="return-point">
                        <div class="return-number">1</div>
                        <div class="return-content">
                            <h4 class="return-point-title">Quick Notification</h4>
                            <p>If you wish to return a product, simply inform us within <strong>48 hours</strong> of receiving your order. Our customer support team is available to assist you with the return process.</p>
                        </div>
                    </div>

                    <div class="return-point">
                        <div class="return-number">2</div>
                        <div class="return-content">
                            <h4 class="return-point-title">Simple Process</h4>
                            <p>Once you notify us, we'll guide you through the easy steps for returning the product. Our team is always ready to assist you in making the return process as smooth as possible.</p>
                        </div>
                    </div>

                    <div class="return-point">
                        <div class="return-number">3</div>
                        <div class="return-content">
                            <h4 class="return-point-title">Condition for Returns</h4>
                            <p>Products must be returned in their <strong>original condition</strong>, with packaging intact, and unused. We do not accept returns for items that have been used, altered, or damaged by the customer.</p>
                        </div>
                    </div>

                    <div class="return-point">
                        <div class="return-number">4</div>
                        <div class="return-content">
                            <h4 class="return-point-title">Fast Refunds</h4>
                            <p>After the returned product is received and inspected, we will process your refund quickly. You can expect the refund to be issued within <strong>3-5 business days</strong>.</p>
                        </div>
                    </div>

                    <div class="return-point">
                        <div class="return-number">5</div>
                        <div class="return-content">
                            <h4 class="return-point-title">Return Fees</h4>
                            <p>Please note that the return charges need to be paid by the customer side as we do not have pick up facility. This shipping fees is <strong>non-adjustable or refundable</strong>.</p>
                        </div>
                    </div>
                </div>

                <div class="about-paragraph card-style">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">❓</div>
                        <h3 class="about-subtitle">Frequently Asked Questions</h3>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">Can I return a part or all items from my order?</h4>
                        <p class="faq-answer">Yes, you may return a part or all items from your Order, in case of multiple items in an order. Simply inform us which items you wish to return within 48 hours of delivery.</p>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">How will I receive the refunds?</h4>
                        <p class="faq-answer">The refunds will be issued to you either through <strong>BANK TRANSFER</strong> of the amount or in the form of <strong>discount coupons</strong>, which you can use in your next purchase. The refund method will be confirmed during the return process.</p>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">How long will it take to get the refunds?</h4>
                        <p class="faq-answer">Once the item is received in our warehouse, it undergoes a Product Inspection, which takes about <strong>1 day</strong>. Typically the refunds are initiated within <strong>3-5 working days</strong> after product inspection.</p>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">How do I return the product?</h4>
                        <p class="faq-answer">Please send the product at the below address with the original invoice:</p>
                        <div class="return-address">
                            <p><strong>www.craftroyale.com</strong></p>
                            <p>C/O Aarvak Garments Pvt Ltd,</p>
                            <p>E-71, 2nd Floor, Sector-06,</p>
                            <p>Noida-201301, Uttar Pradesh,</p>
                            <p>India.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">Shipping/COD handling charges of order</h4>
                        <p class="faq-answer">It would not be included in the refund value of your order as these are <strong>non-refundable charges</strong>. Only the product value will be refunded.</p>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">Who will pay the courier charges at the time of returning the product?</h4>
                        <p class="faq-answer">The courier charges for returning the product will be paid by the <strong>customer</strong>. We do not provide pickup facility, so customers need to arrange and pay for the return shipment.</p>
                    </div>

                    <div class="faq-item">
                        <h4 class="faq-question">I still haven't received my refunds?</h4>
                        <p class="faq-answer">If you haven't received your refund within the specified time, please mail us at <strong>care@craftroyale.com</strong> or call at <strong>+91-8826002179</strong> from Monday to Friday between <strong>10 a.m. to 5 p.m.</strong>. Our team will assist you immediately.</p>
                    </div>
                </div>

                <div class="about-cta">
                    <div class="cta-icon">💬</div>
                    <p class="cta-text">
                        If you have any concerns or questions about returns, feel free to reach out to us. Our priority is to make sure you have a seamless shopping experience, and we are here to help! Contact us at <strong>care@craftroyale.com</strong>
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

/* RETURNS CONTENT SECTION */
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

/* RETURN POINTS */
.return-point {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px;
    border-left: 4px solid #2fa76b;
    transition: all 0.3s ease;
}

.return-point:hover {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.15);
}

.return-number {
    width: 50px;
    height: 50px;
    min-width: 50px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.3);
}

.return-content {
    flex: 1;
}

.return-point-title {
    font-size: 20px;
    font-weight: 700;
    color: #2fa76b;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.return-content p {
    margin: 0;
    color: #555;
    line-height: 1.8;
}

/* FAQ ITEMS */
.faq-item {
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.faq-item:hover {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    border-left-color: #2fa76b;
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(47, 167, 107, 0.15);
}

.faq-question {
    font-size: 18px;
    font-weight: 700;
    color: #2fa76b;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.faq-question::before {
    content: '❓';
    font-size: 20px;
}

.faq-answer {
    font-size: 16px;
    color: #555;
    line-height: 1.8;
    margin: 0;
}

.faq-answer strong {
    color: #2fa76b;
    font-weight: 700;
}

.return-address {
    background: rgba(47, 199, 180, 0.1);
    padding: 20px;
    border-radius: 10px;
    margin-top: 15px;
    border-left: 4px solid #2fa76b;
}

.return-address p {
    margin: 5px 0;
    color: #2b2b2b;
    font-size: 15px;
    line-height: 1.8;
}

.return-address p:first-child {
    font-weight: 700;
    color: #2fa76b;
    font-size: 16px;
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

    .return-point {
        flex-direction: column;
        gap: 15px;
    }

    .return-number {
        width: 40px;
        height: 40px;
        min-width: 40px;
        font-size: 20px;
    }

    .faq-question {
        font-size: 16px;
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

    .return-point-title {
        font-size: 18px;
    }

    .faq-question {
        font-size: 15px;
    }

    .faq-answer {
        font-size: 14px;
    }
}
</style>

<script>
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

