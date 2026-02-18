<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/payment.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="Payment Options Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">PAYMENT OPTION</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Payment Option</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="about-content-section">
    <div class="about-container">
        <div class="about-header">
            <h2 class="about-main-title">
                <span class="title-highlight">Payment Option</span>
            </h2>
            <p class="about-tagline">Secure & Convenient Payment Methods for Pan India</p>
            <div class="title-underline"></div>
        </div>

        <div class="privacy-content-wrapper">
            <div class="privacy-text-content">
                <div class="privacy-intro-card">
                    <div class="intro-icon">💳</div>
                    <p class="privacy-intro">
                        At <strong>Craft Royale</strong>, we offer convenient and secure payment options for all our Pan India customers. We ensure a smooth and hassle-free shopping experience with multiple payment methods to choose from.
                    </p>
                </div>

                <div class="payment-methods-section">
                    <div class="about-paragraph card-style">
                        <div class="paragraph-header">
                            <div class="paragraph-icon">💳</div>
                            <h3 class="about-subtitle">Credit/Debit Cards</h3>
                        </div>
                        <p>
                            We accept all major credit and debit cards for your convenience:
                        </p>
                        <ul class="product-list">
                            <li><span class="list-icon">✨</span>Visa</li>
                            <li><span class="list-icon">✨</span>MasterCard</li>
                            <li><span class="list-icon">✨</span>Maestro</li>
                            <li><span class="list-icon">✨</span>Rupay</li>
                        </ul>
                        <p style="margin-top: 15px;">
                            All card transactions are processed through secure, encrypted payment gateways to ensure the safety of your financial information.
                        </p>
                    </div>

                    <div class="about-paragraph card-style">
                        <div class="paragraph-header">
                            <div class="paragraph-icon">🏦</div>
                            <h3 class="about-subtitle">Net Banking</h3>
                        </div>
                        <p>
                            Pay directly from your bank account using Net Banking. We support payments from major banks across India:
                        </p>
                        <ul class="product-list">
                            <li><span class="list-icon">✨</span>State Bank of India (SBI)</li>
                            <li><span class="list-icon">✨</span>ICICI Bank</li>
                            <li><span class="list-icon">✨</span>HDFC Bank</li>
                            <li><span class="list-icon">✨</span>Axis Bank</li>
                            <li><span class="list-icon">✨</span>Punjab National Bank (PNB)</li>
                            <li><span class="list-icon">✨</span>And many other major banks</li>
                        </ul>
                        <p style="margin-top: 15px;">
                            Net Banking provides a quick and secure way to complete your purchase directly from your bank account.
                        </p>
                    </div>

                    <div class="about-paragraph card-style">
                        <div class="paragraph-header">
                            <div class="paragraph-icon">📱</div>
                            <h3 class="about-subtitle">UPI Payments</h3>
                        </div>
                        <p>
                            Experience fast and hassle-free transactions with UPI (Unified Payments Interface). We accept payments from all UPI-enabled apps:
                        </p>
                        <ul class="product-list">
                            <li><span class="list-icon">✨</span>Google Pay</li>
                            <li><span class="list-icon">✨</span>PhonePe</li>
                            <li><span class="list-icon">✨</span>Paytm</li>
                            <li><span class="list-icon">✨</span>BHIM UPI</li>
                            <li><span class="list-icon">✨</span>Amazon Pay</li>
                            <li><span class="list-icon">✨</span>Other UPI-enabled apps</li>
                        </ul>
                        <p style="margin-top: 15px;">
                            UPI payments are instant, secure, and require no additional charges. Simply scan the QR code or enter your UPI ID to complete the payment.
                        </p>
                    </div>

                    <div class="about-paragraph card-style">
                        <div class="paragraph-header">
                            <div class="paragraph-icon">👛</div>
                            <h3 class="about-subtitle">Wallet Payments</h3>
                        </div>
                        <p>
                            Use your digital wallet for quick and easy payments. We accept payments from:
                        </p>
                        <ul class="product-list">
                            <li><span class="list-icon">✨</span>Paytm Wallet</li>
                            <li><span class="list-icon">✨</span>Mobikwik</li>
                            <li><span class="list-icon">✨</span>Freecharge</li>
                            <li><span class="list-icon">✨</span>Amazon Pay Wallet</li>
                        </ul>
                        <p style="margin-top: 15px;">
                            Wallet payments are perfect for quick checkouts. Simply select your preferred wallet and complete the payment in seconds.
                        </p>
                    </div>

                    <div class="about-paragraph card-style">
                        <div class="paragraph-header">
                            <div class="paragraph-icon">💰</div>
                            <h3 class="about-subtitle">Cash on Delivery (COD)</h3>
                        </div>
                        <p>
                            Prefer to pay when you receive your order? We offer Cash on Delivery (COD) service across Pan India.
                        </p>
                        <ul class="product-list">
                            <li><span class="list-icon">✨</span>Available across all states in India</li>
                            <li><span class="list-icon">✨</span>Pay in cash when your order arrives</li>
                            <li><span class="list-icon">✨</span>No need for online payment</li>
                        </ul>
                        <p style="margin-top: 15px;">
                            <strong>Note:</strong> A COD charge of ₹100 will apply to all Cash on Delivery orders. This charge covers the additional processing and handling costs associated with COD service.
                        </p>
                    </div>
                </div>

                <div class="payment-security-card">
                    <div class="paragraph-header">
                        <div class="paragraph-icon">🔒</div>
                        <h3 class="about-subtitle">Payment Security</h3>
                    </div>
                    <p>
                        Your payment security is our top priority. All transactions are processed through secure, encrypted payment gateways that comply with PCI DSS standards. We never store your complete card details or banking information on our servers.
                    </p>
                    <ul class="product-list" style="margin-top: 20px;">
                        <li><span class="list-icon">✨</span>SSL encrypted transactions</li>
                        <li><span class="list-icon">✨</span>PCI DSS compliant payment gateways</li>
                        <li><span class="list-icon">✨</span>Secure authentication protocols</li>
                        <li><span class="list-icon">✨</span>24/7 fraud monitoring</li>
                    </ul>
                </div>

                <div class="about-cta">
                    <div class="cta-icon">💬</div>
                    <p class="cta-text">
                        Need help with payment? Contact us at <strong>care@craftroyale.com</strong> or call <strong>+91-8826002179</strong>
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

/* PAYMENT CONTENT SECTION */
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

.payment-methods-section {
    margin-bottom: 30px;
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

.payment-security-card {
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.15), rgba(47, 167, 107, 0.15));
    border-radius: 20px;
    padding: 35px;
    margin-top: 40px;
    margin-bottom: 30px;
    border: 2px solid rgba(47, 199, 180, 0.3);
    box-shadow: 0 10px 30px rgba(47, 167, 107, 0.15);
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
}
</style>

