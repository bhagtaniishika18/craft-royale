<?php
include 'includes/db.php';
include 'includes/header.php';

// Check which columns exist in products table
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
}

// Build query for new arrivals - show active and out_of_stock, exclude inactive
$newArrivalsQuery = "SELECT * FROM products";
if (in_array('status', $products_columns)) {
    $newArrivalsQuery .= " WHERE ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
}
$newArrivalsQuery .= " ORDER BY id DESC LIMIT 3";
$newArrivals = mysqli_query($conn, $newArrivalsQuery);

// Get last 4 items that have been ordered (from order_items)
// If no orders exist, fallback to regular products
$has_status = in_array('status', $products_columns);
// Build query for Best Seller - Show latest 4 unique ordered products
// If fewer than 4 ordered, fill with latest products
$bestSellerQuery = "
    SELECT p.*, MAX(oi.created_at) as last_order
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE " . ($has_status ? "((p.status = 'active' OR p.status = 'out_of_stock') AND p.status IS NOT NULL)" : "1=1") . "
    GROUP BY p.id
    ORDER BY last_order DESC, p.id DESC
    LIMIT 4
";
$bestSeller = mysqli_query($conn, $bestSellerQuery);

$latestBlogs = mysqli_query($conn, "SELECT * FROM blog_posts WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");

// Check for logout success message
$show_logout_message = isset($_GET['logout']) && $_GET['logout'] == '1';
?>

<?php if ($show_logout_message): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '👋 Logged Out',
            html: '<div style="font-size: 18px; color: #2fa76b; font-weight: 600;">You have been successfully logged out! ✨</div><div style="margin-top: 10px; font-size: 14px; color: #777;">Thank you for visiting Craft Royale. We hope to see you again soon! 💖</div>',
            icon: 'success',
            confirmButtonText: 'Continue Shopping 🛒',
            confirmButtonColor: '#2fa76b',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'swal2-title-custom',
                htmlContainer: 'swal2-html-container-custom',
                confirmButton: 'swal2-confirm-custom'
            }
        }).then(() => {
            // Remove logout parameter from URL
            if (window.history.replaceState) {
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    }
    
    // Update UI to show logged out state
    if (typeof updateUserUI === 'function') {
        updateUserUI(null);
    }
});
</script>
<?php endif; ?>

<!-- HERO BANNER -->
<section class="hero-slider-wrapper" style="width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important;">
    <div class="hero-slider" style="width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important;">
        <div class="hero-bg active" style="background-image:url('assets/images/slider/slide2.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
        <div class="hero-bg" style="background-image:url('assets/images/slider/2.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
        <div class="hero-bg" style="background-image:url('assets/images/slider/3.jpg'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>   
        <div class="hero-bg" style="background-image:url('assets/images/slider/slide1.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
        <div class="hero-bg" style="background-image:url('assets/images/slider/1.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
        <div class="hero-bg" style="background-image:url('assets/images/slider/slide3.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
        <div class="hero-bg" style="background-image:url('assets/images/slider/slide4.png'); background-size: cover !important; background-position: center center !important; background-repeat: no-repeat !important; width: 100% !important; height: 100% !important;"></div>
    </div>
</section>

<!-- INTRODUCTION AUDIO LOGIC -->
<audio id="introWelcomeAudio" preload="auto" style="display:none;">
    <source src="assets/images/introduction.mp3" type="audio/mpeg">
</audio>

<script>
(function() {
    // Check if user is logged in (either via PHP or via JS state)
    const isUserLoggedIn = () => {
        return <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?> || 
               (typeof window.isUserLoggedInJS === 'function' && window.isUserLoggedInJS());
    };

    let hasStartedOnThisPage = false;
    const hero = document.querySelector('.hero-slider-wrapper');
    const audio = document.getElementById('introWelcomeAudio');

    function startAudio() {
        if (!audio || hasStartedOnThisPage) return;
        
        // Final check for login status
        const loggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        if (!loggedIn && !document.body.classList.contains('user-is-logged-in')) return;

        audio.play().then(() => {
            console.log("🔊 Intro: Playback started.");
            hasStartedOnThisPage = true;
            cleanup();
            if (window.location.search.includes('login=success')) {
                if (window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        }).catch(() => { /* Autoplay blocked */ });
    }

    function cleanup() {
        ['click', 'scroll', 'touchstart', 'mousemove'].forEach(ev => 
            document.removeEventListener(ev, startAudio)
        );
    }

    // Attempt immediately if already logged in (PHP)
    if (<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        startAudio();
    }

    // Listen for login event (for modal AJAX login)
    document.addEventListener('userLoggedIn', function() {
        console.log("🔊 Intro: Login event detected, preparing audio...");
        document.body.classList.add('user-is-logged-in');
        // Reset flag to allow playing after a fresh login if it didn't play before
        hasStartedOnThisPage = false; 
        startAudio();
        // Also add interaction triggers in case it was blocked
        ['click', 'scroll', 'touchstart', 'mousemove'].forEach(ev => 
            document.addEventListener(ev, startAudio, { once: true })
        );
    });

    // Global function to stop intro audio (called when user moves to intensive tasks like Reels)
    window.stopIntroAudio_CR = function() {
        if (audio && !audio.paused) {
            audio.pause();
            console.log("🔊 Intro: Interrupted by Reels/Interaction - Stopping.");
        }
    };

    // Attach to any reel-trigger in the header
    document.addEventListener('click', function(e) {
        if (e.target.closest('.reels-trigger')) {
            window.stopIntroAudio_CR();
        }
    });

    // Interaction triggers
    ['click', 'scroll', 'touchstart', 'mousemove'].forEach(ev => 
        document.addEventListener(ev, startAudio, { once: true })
    );

    // Stop logic
    window.addEventListener('scroll', () => {
        if (audio && !audio.paused && hero) {
            const rect = hero.getBoundingClientRect();
            if (rect.bottom < 50) audio.pause();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && audio) audio.pause();
    });
})();
</script>

<!-- NEW ARRIVALS -->
<section class="section">
    <h2>New Arrivals</h2>
    <div class="card-grid">
        <?php while($p = mysqli_fetch_assoc($newArrivals)) { 
            // Handle image path
            $product_image = $p['image'] ?? $p['product_image'] ?? '';
            if (!empty($product_image)) {
                $imagePath = 'uploads/products/' . $product_image;
                // Check if file exists, if not use fallback
                if (!file_exists($imagePath)) {
                    $imagePath = 'assets/images/beads.jpg';
                }
            } else {
                $imagePath = 'assets/images/beads.jpg';
            }
            
            $product_name = htmlspecialchars($p['name'] ?? $p['product_name'] ?? 'Product');
            $product_price = number_format($p['price'] ?? 0, 2);
            $product_mrp = isset($p['mrp']) && $p['mrp'] > $p['price'] ? number_format($p['mrp'], 2) : null;
            $discount = isset($p['discount_percent']) ? $p['discount_percent'] : 0;
        ?>
            <div class="card">
                <div class="card-image-wrapper">
                    <img src="<?= $imagePath ?>" alt="<?= $product_name ?>" onerror="this.src='assets/images/beads.jpg'">
                    <?php if ($discount > 0): ?>
                        <div class="product-badge">-<?= $discount ?>%</div>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <h4><?= $product_name ?></h4>
                    <div class="card-pricing">
                        <?php if ($product_mrp): ?>
                            <span class="card-mrp">₹<?= $product_mrp ?></span>
                        <?php endif; ?>
                        <span class="card-price">₹<?= $product_price ?></span>
                    </div>
                    <a href="product.php?id=<?= $p['id']; ?>" class="login-btn">View</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<!-- EMBROIDERY -->
<section class="category-banner embroidery-banner">
    <h2>Embroidery</h2>
</section>

<section class="image-section embroidery">
    <div class="image-grid">
        <div class="image-card">
            <img src="assets/images/embroidery/threads.jpg">
            <span>Threads</span>
        </div>
        <div class="image-card">
            <img src="assets/images/embroidery/zari.jpg">
            <span>Zari</span>
        </div>
        <div class="image-card">
            <img src="assets/images/embroidery/needles.jpg">
            <span>Needles</span>
        </div>
        <div class="image-card">
            <img src="assets/images/embroidery/floss.jpg">
             <span>Floss</span>
        </div>
    </div>
</section>

<!-- BEADS -->
<div class="category-banner beads-banner">
    <h2>Beads</h2>
</div>

<section class="image-section beads">
    <div class="image-grid">
        <div class="image-card">
            <img src="assets/images/beads/seed-beads.jpg">
            <span>Seed Beads</span>
        </div>
        <div class="image-card">
            <img src="assets/images/beads/glass-beads.jpg">
            <span>Glass Beads</span>
        </div>
        <div class="image-card">
            <img src="assets/images/beads/crystal-beads.jpg">
            <span>Crystal Beads</span>
        </div>
        <div class="image-card">
            <img src="assets/images/beads/pearls.jpg">
            <span>Pearls</span>
        </div>
    </div>
</section>
<!-- EMBELLISHMENT BANNER -->
<div class="category-banner embellishment-banner">
    <h2>Embellishment</h2>
</div>

<!-- EMBELLISHMENT -->
<section class="image-section embellishment">
    <div class="image-grid category-images">
        <div class="category-item">
            <img src="assets/images/embellishment/stones.jpg" alt="Stones">
            <p>Stones</p>
        </div>
        <div class="category-item">
            <img src="assets/images/embellishment/sequins.jpg" alt="Sequins">
            <p>Sequins</p>
        </div>
        <div class="category-item">
            <img src="assets/images/embellishment/buttons.jpg" alt="Buttons">
            <p>Buttons</p>
        </div>
        <div class="category-item">
            <img src="assets/images/embellishment/charms.jpg" alt="Charms">
            <p>Charms</p>
        </div>
    </div>
</section>

<!-- JEWELRY MAKING BANNER -->
<div class="category-banner jewelry-banner">
    <h2>Jewelry Making</h2>
</div>
<section class="image-section jewelry">
    <div class="image-grid">
        <div class="image-card">
            <img src="assets/images/jewelry/findings.jpg" alt="Findings">
            <span>Findings</span>
        </div>
        <div class="image-card">
            <img src="assets/images/jewelry/chains.jpg" alt="Chains">
            <span>Chains</span>
        </div>
        <div class="image-card">
            <img src="assets/images/jewelry/clasps.jpg" alt="Clasps">
            <span>Clasps</span>
        </div>
        <div class="image-card">
            <img src="assets/images/jewelry/hooks.jpg" alt="Hooks">
            <span>Hooks</span>
        </div>
    </div>
</section>

<!-- CRAFT SUPPLIES BANNER -->
<div class="category-banner craft-banner">
    <h2>Craft Supplies</h2>
</div>
<section class="image-section craft">
    <div class="image-grid">
        <div class="image-card">
            <img src="assets/images/craft/pompoms.jpg" alt="Pompoms">
            <span>Pompoms</span>
        </div>
        <div class="image-card">
            <img src="assets/images/craft/assorted.jpg" alt="Assorted">
            <span>Assorted</span>
        </div>
        <div class="image-card">
            <img src="assets/images/craft/ribbons.jpg" alt="Ribbons">
            <span>Ribbons</span>
        </div>
        <div class="image-card">
            <img src="assets/images/craft/foam.jpg" alt="Foam">
            <span>Foam</span>
        </div>
    </div>
</section>

<!-- TOOLS / CATALOG BANNER -->
<div class="category-banner tools-banner">
    <h2>Tools / Catalog</h2>
</div>
<section class="image-section tools">
    <div class="image-grid">
        <div class="image-card">
            <img src="assets/images/tools/cutting.jpg" alt="Cutting Tools">
            <span>Cutting</span>
        </div>
        <div class="image-card">
            <img src="assets/images/tools/measuring.jpg" alt="Measuring Tools">
            <span>Measuring</span>
        </div>
        <div class="image-card">
            <img src="assets/images/tools/needles.jpg" alt="Needles">
            <span>Needles</span>
        </div>
        <div class="image-card">
            <img src="assets/images/tools/tools.jpg" alt="Tools">
            <span>Tools</span>
        </div>
    </div>
</section>
<!-- BEST SELLER -->
<section class="section">
    <h2>Best Seller</h2>
    <div class="card-grid">
        <?php while($p = mysqli_fetch_assoc($bestSeller)) { 
            // Handle image path
            $product_image = $p['image'] ?? $p['product_image'] ?? '';
            if (!empty($product_image)) {
                $imagePath = 'uploads/products/' . $product_image;
                // Check if file exists, if not use fallback
                if (!file_exists($imagePath)) {
                    $imagePath = 'assets/images/beads.jpg';
                }
            } else {
                $imagePath = 'assets/images/beads.jpg';
            }
            
            $product_name = htmlspecialchars($p['name'] ?? $p['product_name'] ?? 'Product');
            $product_price = number_format($p['price'] ?? 0, 2);
            $product_mrp = isset($p['mrp']) && $p['mrp'] > $p['price'] ? number_format($p['mrp'], 2) : null;
            $discount = isset($p['discount_percent']) ? $p['discount_percent'] : 0;
        ?>
            <div class="card">
                <div class="card-image-wrapper">
                    <img src="<?= $imagePath ?>" alt="<?= $product_name ?>" onerror="this.src='assets/images/beads.jpg'">
                    <?php if ($discount > 0): ?>
                        <div class="product-badge">-<?= $discount ?>%</div>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <h4><?= $product_name ?></h4>
                    <div class="card-pricing">
                        <?php if ($product_mrp): ?>
                            <span class="card-mrp">₹<?= $product_mrp ?></span>
                        <?php endif; ?>
                        <span class="card-price">₹<?= $product_price ?></span>
                    </div>
                    <a href="product.php?id=<?= $p['id']; ?>" class="login-btn">View</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<!-- BLOG -->
<section class="section">
    <h2>📝 Latest From Blog ✨</h2>
    <div class="card-grid blog-card-grid">
        <?php if (mysqli_num_rows($latestBlogs) > 0): ?>
            <?php while($post = mysqli_fetch_assoc($latestBlogs)): 
                $date = date('F j, Y', strtotime($post['created_at']));
                $image_path = !empty($post['image']) ? 'uploads/blogs/' . htmlspecialchars($post['image']) : 'assets/images/beads.jpg';
            ?>
                <div class="card blog-card">
                    <div class="blog-card-image-wrapper">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 class="blog-card-img"
                                 onerror="this.src='assets/images/beads.jpg'">
                        <?php else: ?>
                            <img src="assets/images/beads.jpg" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 class="blog-card-img">
                        <?php endif; ?>
                        <div class="blog-card-overlay">
                            <span class="blog-emoji">📖</span>
                        </div>
                    </div>
                    <div class="blog-card-body">
                        <h4 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h4>
                        <div class="blog-card-date">
                            <span class="date-icon">📅</span>
                            <span><?php echo $date; ?></span>
                        </div>
                        <a href="blog.php" class="blog-read-more-btn">
                            <span>Read More</span>
                            <span class="arrow-icon">→</span>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card blog-card">
                <div class="blog-card-body">
                    <h4>🎨 Craft Trends</h4>
                    <p>Discover the latest trends in crafting!</p>
                </div>
            </div>
            <div class="card blog-card">
                <div class="blog-card-body">
                    <h4>💡 DIY Ideas</h4>
                    <p>Get inspired with creative DIY projects!</p>
                </div>
            </div>
            <div class="card blog-card">
                <div class="blog-card-body">
                    <h4>💎 Jewelry Tips</h4>
                    <p>Learn expert tips for jewelry making!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* BLOG CARD STYLES */
.blog-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.blog-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    padding: 0 !important;
    position: relative;
    border: 2px solid transparent;
}

.blog-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #2fa76b, #2fc7b4, #2fa76b);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
    z-index: 1;
}

.blog-card:hover::before {
    transform: scaleX(1);
}

.blog-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 40px rgba(47, 167, 107, 0.25);
    border-color: rgba(47, 167, 107, 0.2);
}

.blog-card-image-wrapper {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: linear-gradient(135deg, #e8f8f0, #f0fdf4);
}

.blog-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.blog-card:hover .blog-card-img {
    transform: scale(1.1) rotate(2deg);
}

.blog-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(47, 167, 107, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-card:hover .blog-card-overlay {
    opacity: 1;
}

.blog-emoji {
    font-size: 48px;
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.blog-card:hover .blog-emoji {
    transform: scale(1) rotate(360deg);
}

.blog-card-body {
    padding: 25px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.blog-card-title {
    font-size: 20px;
    font-weight: 700;
    color: #2b2b2b;
    margin: 0 0 15px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
    min-height: 56px;
}

.blog-card:hover .blog-card-title {
    color: #2fa76b;
}

.blog-card-date {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
    padding: 8px 12px;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.08), rgba(47, 167, 107, 0.08));
    border-radius: 8px;
    border-left: 3px solid #2fc7b4;
}

.date-icon {
    font-size: 16px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.blog-read-more-btn {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(47, 167, 107, 0.3);
    border: 2px solid transparent;
}

.blog-read-more-btn:hover {
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
    border-color: rgba(255, 255, 255, 0.3);
}

.blog-read-more-btn .arrow-icon {
    font-size: 18px;
    transition: transform 0.3s ease;
}

.blog-read-more-btn:hover .arrow-icon {
    transform: translateX(5px);
}

.blog-card:nth-child(1) { animation-delay: 0.1s; }
.blog-card:nth-child(2) { animation-delay: 0.2s; }
.blog-card:nth-child(3) { animation-delay: 0.3s; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .blog-card-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .blog-card-image-wrapper {
        height: 200px;
    }
}
</style>

<!-- WHOLESALE EMBROIDERY THREADS & BEADING SUPPLIES -->
<section class="wholesale-section">
    <div class="wholesale-banner"></div>
    <div class="wholesale-container">
        <h2 class="wholesale-heading">Wholesale Embroidery Threads & Beading Supplies</h2>
        <p class="wholesale-description">
            At Craft Royale, you can discover a whole new world of high-quality discounted embroidery materials along with accessing exclusive customer services. We offer a wide variety in Embroidery Laces, Metal Sequins, Pom Poms, Glass Crystals, Sew on Studs, Gota Patti, French Wire, Acrylics Beads, Natural Stone Beads, Cabochon Stones and other beading supplies. It's our aim to provide you with world-class embroidery tools that meet your needs and budget.
        </p>
        
        <div class="expanded-content" id="expandedContent">
            <div class="content-divider"></div>
            <div class="expanded-content-inner">
                <p class="expanded-paragraph">
                    We offer a comprehensive range of embroidery materials including Embroidery Laces, Metal Sequins, Pom Poms, Glass Crystals, Sew on Studs, Gota Patti, French Wire, Acrylics Beads, Natural Stone Beads, and Cabochon Stones. Our extensive collection of threads includes Knitting Yarn, Art Silk, Nylon, Crochet Cotton, Anchor Cotton Threads, and Metallic Threads (Badla, Zari). For your wire needs, we provide French Metallic Wire, Bullion Wires, and Gimp Wires including Dabka, Nakshi, Kora, Mukaish, and Gijai varieties.
                </p>
                <p class="expanded-paragraph">
                    Our embellishments section features Gota Flowers, Shisha Mirrors, Sew on Sequins, Metal Beads, Metal Chains, Metal Charms, Tassels, and Kundan Stones. We stock a wide variety of beads including Delica Beads (Cutdana), Seed Beads (Moti), Bugle Beads, Jasper Beads, Spacer Beads, and Superduo Beads from premium brands like Miyuki, M.G.B, Toho, and Preciosa.
                </p>
                <p class="expanded-paragraph">
                    For crystals and stones, we offer Hot Fix Swarovski Crystals & Pearls, Sew on Acrylic Stones, Resin Stones, MOP Buttons, and Crystal Glass Stones. Our faceted fire polished crystal bead strings come in various shapes including Cone, Rectangle, Bicone, Briolette, and Rondelle from Czech Republic. We also provide Centre Hole, Double Hole, and Drilled Hole Rhinestones with Brass Catchers.
                </p>
                <p class="expanded-paragraph">
                    Our design elements collection includes Ribbons, Gota Laces, Neckline Patches, Embroidered Lace, Trims, and Hand Embroidery Applique. For tools and frames, we offer various types of Needles, Hooks, and Frames including Tambour, Punch, and Zardosi frames. We stock Handmade Aari Needles, Tulip Japanese Beading Needles, and Wide Eye Beading Needles. Our Wooden Embroidery Hoops and Adda Frames are available in Round, Oval, Square, Triangle, and Rectangle shapes, including premium Pony Hoops brand.
                </p>
                <p class="expanded-paragraph">
                    For jewelry making enthusiasts, we provide Beading Wire, Crystal Pendants, Ball Chains, Rondelle Chains, and complete Jewelry Findings and Components including Clasps and Jump Rings. We also offer Embroidery Kits and Jewelry Making Kits suitable for both beginners and experts, along with various combo offers and special discount packages to meet all your crafting needs.
                </p>
            </div>
            <div class="content-divider"></div>
        </div>
        
        <div class="read-more-wrapper">
            <a href="#" class="read-more-link" id="readMoreLink" onclick="toggleReadMore(event)">Read more</a>
        </div>
        
        <div class="wholesale-features">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4"/>
                        <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"/>
                        <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"/>
                        <path d="M12 21c0-1-1-3-3-3s-3 2-3 3 1 3 3 3 3-2 3-3z"/>
                        <path d="M12 3c0 1-1 3-3 3S6 4 6 3 7 0 9 0s3 2 3 3z"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <div class="feature-title">Trusted by 1,00,000+</div>
                    <div class="feature-subtitle">Happy Customers Worldwide</div>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <div class="feature-title">4.8-Star</div>
                    <div class="feature-subtitle">Customer Rated</div>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <div class="feature-title">One-Stop</div>
                    <div class="feature-subtitle">Shop for Embroidery Materials</div>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <div class="feature-title">10+</div>
                    <div class="feature-subtitle">Years of Expertise</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REVIEWS SECTION -->
<section class="reviews-section">
    <div class="reviews-container">
        <h2 class="reviews-heading"><span class="emoji-heart">💖</span> Real Stories, Real Smiles <span class="emoji-heart">💖</span></h2>
        
        <?php
        // Fetch approved reviews and calculate average rating
        $approved_reviews_query = mysqli_query($conn, "SELECT * FROM reviews WHERE status='approved' ORDER BY created_at DESC LIMIT 4");
        $rating_query = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE status='approved'");
        $rating_data = mysqli_fetch_assoc($rating_query);
        $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 4.7;
        $total_reviews = $rating_data['total_reviews'] ?: 0;
        
        function timeAgo($datetime) {
            if (empty($datetime) || $datetime == '0000-00-00 00:00:00' || $datetime == null) {
                return 'Recently';
            }
            
            // Try to parse the datetime
            $timestamp = strtotime($datetime);
            
            // If strtotime fails, try alternative formats
            if ($timestamp === false) {
                // Try MySQL datetime format
                $timestamp = strtotime(str_replace('/', '-', $datetime));
            }
            
            if ($timestamp === false || $timestamp <= 0) {
                // If still fails, return formatted date
                $formatted = date('M d, Y', strtotime($datetime));
                return $formatted ?: 'Recently';
            }
            
            $current_time = time();
            $diff = $current_time - $timestamp;
            
            // Handle future dates (shouldn't happen, but just in case)
            if ($diff < 0) {
                return 'Just now';
            }
            
            // Less than 1 minute
            if ($diff < 60) {
                return 'Just now';
            }
            
            // Less than 1 hour
            if ($diff < 3600) {
                $mins = floor($diff / 60);
                return $mins . ($mins == 1 ? ' min ago' : ' mins ago');
            }
            
            // Less than 24 hours
            if ($diff < 86400) {
                $hours = floor($diff / 3600);
                return $hours . ($hours == 1 ? ' hr ago' : ' hrs ago');
            }
            
            // Less than 7 days
            if ($diff < 604800) {
                $days = floor($diff / 86400);
                if ($days == 1) return 'Yesterday';
                return $days . ' days ago';
            }
            
            // Less than 30 days
            if ($diff < 2592000) {
                $weeks = floor($diff / 604800);
                return $weeks . ($weeks == 1 ? ' week ago' : ' weeks ago');
            }
            
            // Less than 1 year
            if ($diff < 31536000) {
                $months = floor($diff / 2592000);
                return $months . ($months == 1 ? ' month ago' : ' months ago');
            }
            
            // More than 1 year
            $years = floor($diff / 31536000);
            return $years . ($years == 1 ? ' year ago' : ' years ago');
        }
        ?>
        
        <div class="google-reviews-header">
            <div class="google-logo-box">
                <div class="google-logo">
                    <span class="google-g">G</span>
                </div>
                <span class="google-text">⭐ Google Reviews ⭐</span>
            </div>
            <div class="rating-display">
                <span class="rating-number"><?= $avg_rating ?></span>
                <div class="stars-display">
                    <?php 
                    $full_stars = floor($avg_rating);
                    $has_half = ($avg_rating - $full_stars) >= 0.5;
                    for($i = 1; $i <= 5; $i++): 
                        if($i <= $full_stars): ?>
                            <span class="star filled">★</span>
                        <?php elseif($i == $full_stars + 1 && $has_half): ?>
                            <span class="star half">★</span>
                        <?php else: ?>
                            <span class="star empty">★</span>
                        <?php endif;
                    endfor; ?>
                </div>
                <span class="review-count">✨ (<?= number_format($total_reviews) ?> Verified Customer Stories) ✨</span>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: -20px; margin-bottom: 30px;">
            <a href="reviews.php" class="review-button" id="reviewBtn" onclick="window.location.href='reviews.php'; return false;" style="pointer-events: auto !important; cursor: pointer !important; z-index: 10000 !important; position: relative !important; display: inline-block !important;">🚀 REVIEW US ON GOOGLE</a>
        </div>
        
        <div class="reviews-grid">
            <?php if(mysqli_num_rows($approved_reviews_query) > 0): ?>
                <?php 
                while($review = mysqli_fetch_assoc($approved_reviews_query)): 
                    $initial = strtoupper(substr($review['name'], 0, 1));
                ?>
                    <div class="review-card">
                        <div class="review-avatar"><?= $initial ?></div>
                        <div class="review-name">
                            <?= htmlspecialchars($review['name']) ?>
                            <span class="verified-badge">✓</span>
                        </div>
                        <div class="verified-label">✨ VERIFIED CUSTOMER ✨</div>
                        <div class="review-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $review['rating'] ? 'filled' : ($i == ceil($review['rating']) && $review['rating'] != floor($review['rating']) ? 'half' : 'empty') ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="review-card" style="grid-column: 1 / -1; text-align: center; padding: 60px 40px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">🌟</div>
                    <p style="color: #999; margin: 0; font-size: 18px; font-weight: 600;">No reviews yet. Be the first to review! 🎉</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>










<?php include 'includes/footer.php'; ?>



<script>
// Toggle Read More/Less functionality
function toggleReadMore(event) {
    event.preventDefault();
    const expandedContent = document.getElementById('expandedContent');
    const readMoreLink = document.getElementById('readMoreLink');
    
    if (expandedContent.classList.contains('expanded')) {
        // Collapse
        expandedContent.classList.remove('expanded');
        readMoreLink.textContent = 'Read more';
        readMoreLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        // Expand
        expandedContent.classList.add('expanded');
        readMoreLink.textContent = 'Read less';
        // Smooth scroll to the expanded content
        setTimeout(() => {
            expandedContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

// Ensure review button is always clickable
document.addEventListener('DOMContentLoaded', function() {
    const reviewBtn = document.getElementById('reviewBtn') || document.querySelector('.review-button');
    if (reviewBtn) {
        // Force styles
        reviewBtn.style.pointerEvents = 'auto';
        reviewBtn.style.cursor = 'pointer';
        reviewBtn.style.zIndex = '10000';
        reviewBtn.style.position = 'relative';
        
        // Multiple click handlers to ensure it works
        function navigateToReviews() {
            window.location.href = 'reviews.php';
        }
        
        // Click handler
        reviewBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            navigateToReviews();
        }, true);
        
        // Touch handler for mobile
        reviewBtn.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            navigateToReviews();
        }, true);
        
        // Also handle mouseup as backup
        reviewBtn.addEventListener('mouseup', function(e) {
            e.stopPropagation();
            navigateToReviews();
        });
    }
});

</script>


