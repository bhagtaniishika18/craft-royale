<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/tools/tools.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/tools.jpg" alt="DIY/Tools Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">Tools/Catalog</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Tools/Catalog</span>
        </nav>
    </div>
</section>

<!-- CATALOG SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">Catalog</h2>
        <div class="products-grid">
            <div class="product-item">
                <a href="products.php?cat=catalog">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/catalog.jpg" alt="Catalog" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Catalog</h3>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- EMBROIDERY FRAME/ADDA SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">Embroidery Frame/Adda</h2>
        <div class="products-grid">
            <div class="product-item">
                <a href="products.php?cat=embroidery-frame-adda">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/embroideryframe.jpg" alt="Embroidery Frame/Adda" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Embroidery Frame/Adda</h3>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- NEEDLES SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">Needles</h2>
        <div class="products-grid">
            <div class="product-item">
                <a href="products.php?cat=needles">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/needles1.jpg" alt="Needles" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Needles</h3>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- TOOLS SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">Tools</h2>
        <div class="products-grid">
            <div class="product-item">
                <a href="products.php?cat=tools">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/tools1.jpg" alt="Tools" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Tools</h3>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- DIY KITS SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">DIY Kits</h2>
        <div class="products-grid">
            <div class="product-item">
                <a href="products.php?cat=diy-kits&sub=embroidery-kits">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/embroiderykits.jpg" alt="Embroidery Kits" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Embroidery Kits</h3>
                </a>
            </div>
            <div class="product-item">
                <a href="products.php?cat=diy-kits&sub=painting-kits">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/embroiderypainingkits.jpg" alt="Painting Kits" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Painting Kits</h3>
                </a>
            </div>
            <div class="product-item">
                <a href="products.php?cat=diy-kits&sub=crochet-kit">
                    <div class="product-image-wrapper">
                        <img src="assets/images/tools/crochetkit.jpg" alt="Crochet Kit" onerror="this.src='assets/images/tools.jpg'">
                    </div>
                    <h3 class="product-name">Crochet Kit</h3>
                </a>
            </div>
          
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
/* EMBROIDERY HERO BANNER */
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
    background: rgba(0, 0, 0, 0.4);
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

/* EMBROIDERY SECTIONS */
.embroidery-section {
    padding: 60px 20px;
    background: #fff;
}

.embroidery-section:nth-child(even) {
    background: #f9f9f9;
}

.section-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: #2b2b2b;
    margin: 0 0 40px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: center;
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #2fa76b, transparent);
    border-radius: 2px;
}

/* PRODUCTS GRID */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

/* Single item grid - consistent sizing for Catalog, Embroidery Frame/Adda, Needles, Tools */
.products-grid .product-item:only-child {
    max-width: 300px;
    width: 100%;
}

/* Center single-item grids */
.products-grid:has(.product-item:only-child) {
    grid-template-columns: 1fr;
    justify-items: center;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
}

/* Fallback for browsers that don't support :has() */
@supports not selector(:has(*)) {
    .products-grid .product-item:only-child {
        max-width: 300px;
        margin: 0 auto;
    }
}

.product-item {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-item a {
    text-decoration: none;
    color: inherit;
    display: block;
}

.product-image-wrapper {
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f5f5f5;
    position: relative;
}

.product-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-item:hover .product-image-wrapper img {
    transform: scale(1.1);
}

.product-name {
    font-size: 18px;
    font-weight: 600;
    color: #2b2b2b;
    margin: 0;
    padding: 20px;
    text-align: center;
    transition: color 0.3s ease;
}

.product-item:hover .product-name {
    color: #2fa76b;
}

/* Responsive */
@media (max-width: 768px) {
    .embroidery-hero-title {
        font-size: 48px;
    }
    
    .section-title {
        font-size: 24px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .product-image-wrapper {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .embroidery-hero-title {
        font-size: 36px;
        letter-spacing: 2px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

