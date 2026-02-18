<?php
include 'includes/db.php';
include 'includes/header.php';

// Get active gift card designs
$designs_query = "SELECT * FROM gift_card_designs WHERE is_active = 1 ORDER BY display_order ASC";
$designs_result = mysqli_query($conn, $designs_query);
$designs = [];
while ($design = mysqli_fetch_assoc($designs_result)) {
    $designs[] = $design;
}

// If no designs, create defaults
if (empty($designs)) {
    $default_designs = [
        ['design_key' => 'merry_christmas', 'design_name' => 'Merry Christmas', 'title' => 'Merry Christmas', 'description' => 'To another very good year! \'Tis the season of joy!', 'background_color' => '#2d5016', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_birthday', 'design_name' => 'Happy Birthday', 'title' => 'Happy Birthday', 'description' => 'Celebrate yourself today (& always).', 'background_color' => '#e91e63', 'text_color' => '#ffffff'],
        ['design_key' => 'anniversary', 'design_name' => 'Anniversary', 'title' => 'Anniversary', 'description' => 'To another very good year! Wishing you love and joy!', 'background_color' => '#9c27b0', 'text_color' => '#ffffff'],
        ['design_key' => 'wedding', 'design_name' => 'Wedding', 'title' => 'Wedding', 'description' => 'Here\'s to forever! Congratulations!', 'background_color' => '#c2185b', 'text_color' => '#ffffff'],
        ['design_key' => 'bride_to_be', 'design_name' => 'Bride-To-Be', 'title' => 'Bride-To-Be', 'description' => 'Congratulations, bride-to-be!', 'background_color' => '#c2185b', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_diwali', 'design_name' => 'Happy Diwali', 'title' => 'Happy Diwali', 'description' => 'Gift Joy & Prosperity.', 'background_color' => '#ff9800', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_navratri', 'design_name' => 'Happy Navratri', 'title' => 'Happy Navratri', 'description' => 'Spreading Light & Cheer.', 'background_color' => '#ff9800', 'text_color' => '#ffffff'],
        ['design_key' => 'shubho_durga_puja', 'design_name' => 'Shubho Durga Puja', 'title' => 'Shubho Durga Puja', 'description' => 'Celebrating with love.', 'background_color' => '#8b0000', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_rakshabandhan', 'design_name' => 'Happy Rakshabandhan', 'title' => 'Happy Rakshabandhan', 'description' => 'Here\'s to strong, bold and beautiful women.', 'background_color' => '#9c27b0', 'text_color' => '#ffffff'],
        ['design_key' => 'friendship_day', 'design_name' => 'Happy Friendship\'s Day', 'title' => 'Happy Friendship\'s Day!', 'description' => 'Here\'s to the real ones.', 'background_color' => '#9c27b0', 'text_color' => '#ffffff'],
        ['design_key' => 'valentines_day', 'design_name' => 'Valentine\'s Day', 'title' => 'Valentine\'s Day', 'description' => 'Celebrate love & special moments!', 'background_color' => '#e91e63', 'text_color' => '#ffffff'],
        ['design_key' => 'womens_day', 'design_name' => 'Happy Women\'s Day', 'title' => 'Happy Women\'s Day', 'description' => 'Here\'s to strong, bold and beautiful women.', 'background_color' => '#9c27b0', 'text_color' => '#ffffff'],
    ];
    
    foreach ($default_designs as $design) {
        $design_key = mysqli_real_escape_string($conn, $design['design_key']);
        $design_name = mysqli_real_escape_string($conn, $design['design_name']);
        $title = mysqli_real_escape_string($conn, $design['title']);
        $description = mysqli_real_escape_string($conn, $design['description']);
        $background_color = mysqli_real_escape_string($conn, $design['background_color']);
        $text_color = mysqli_real_escape_string($conn, $design['text_color']);
        
        $insert_design = "INSERT INTO gift_card_designs (design_key, design_name, title, description, background_color, text_color, is_active, display_order) 
                         VALUES ('$design_key', '$design_name', '$title', '$description', '$background_color', '$text_color', 1, 0)";
        mysqli_query($conn, $insert_design);
    }
    
    // Reload designs
    $designs_result = mysqli_query($conn, $designs_query);
    $designs = [];
    while ($design = mysqli_fetch_assoc($designs_result)) {
        $designs[] = $design;
    }
}
?>
<style>
    .gift-card-hero {
        padding: 100px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 440px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    
    .gift-card-hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }
    
    .gift-card-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1;
    }
    
    .gift-card-hero-content {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .gift-card-hero h1 {
        font-size: 64px;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0 0 20px 0;
        text-shadow: 2px 2px 15px rgba(0, 0, 0, 0.5);
        line-height: 1.2;
    }
    
    .gift-card-hero p {
        font-size: 24px;
        font-weight: 500;
        color: #fff;
        margin: 0;
        text-shadow: 1px 1px 10px rgba(0, 0, 0, 0.5);
    }

    .gift-card-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .feature-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: center;
        border: 2px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(233, 30, 99, 0.2);
        border-color: #e91e63;
    }

    .feature-icon {
        font-size: 48px;
        margin-bottom: 20px;
        color: #e91e63;
    }

    .feature-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 15px 0;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
        margin: 0;
    }

    .designs-section {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .section-header h2 {
        font-size: 36px;
        font-weight: 800;
        color: #2b2b2b;
        margin: 0 0 10px 0;
    }

    .section-header p {
        font-size: 18px;
        color: #666;
        margin: 0;
    }

    .designs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .design-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .design-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(233, 30, 99, 0.3);
        border-color: #e91e63;
    }

    .design-preview {
        height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 24px;
        text-align: center;
        padding: 20px;
        position: relative;
    }

    .design-info {
        padding: 20px;
    }

    .design-title {
        font-size: 18px;
        font-weight: 700;
        color: #2b2b2b;
        margin: 0 0 10px 0;
    }

    .design-description {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
        margin: 0 0 15px 0;
    }

    .send-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #e91e63, #9c27b0);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .send-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.4);
    }
</style>

<!-- HERO SECTION -->
<section class="gift-card-hero">
    <video class="gift-card-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/giftcards.mp4" type="video/mp4">
        <!-- Fallback message if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="Gift Card Background">
    </video>
    <div class="gift-card-hero-content">
        <h1>WELCOME TO CRAFT ROYALE GIFT CARD STORE</h1>
        <p>Give the gift of creativity and craft supplies</p>
    </div>
</section>

<!-- FEATURES -->
<div class="gift-card-features">
    <div class="feature-card">
        <div class="feature-icon">🎁</div>
        <h3>One card to rule them all</h3>
        <p>Use it on any of the Craft Royale products and enjoy shopping for your favorite craft supplies.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">💌</div>
        <h3>Show your love instantly</h3>
        <p>Send gift cards to your loved ones on WhatsApp, SMS or Email instantly.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🏢</div>
        <h3>Get Gift Cards for your employees</h3>
        <p>Give your employees a gift they would all love. Perfect for corporate gifting.</p>
    </div>
</div>

<!-- DESIGNS SECTION -->
<div class="designs-section">
    <div class="section-header">
        <h2>Choose a design</h2>
        <p>That captures the mood</p>
    </div>

    <div class="designs-grid">
        <?php foreach ($designs as $design): ?>
            <div class="design-card" onclick="selectDesign('<?= $design['design_key'] ?>')">
                <div class="design-preview" style="background: <?= !empty($design['background_image']) ? "url('" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; background-size: cover; background-position: center; color: <?= $design['text_color'] ?>;">
                    <?= htmlspecialchars($design['title']) ?>
                </div>
                <div class="design-info">
                    <h3 class="design-title"><?= htmlspecialchars($design['design_name']) ?></h3>
                    <p class="design-description"><?= htmlspecialchars($design['description']) ?></p>
                    <button class="send-btn">SEND ></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function selectDesign(designKey) {
    window.location.href = 'gift-card-details.php?design=' + designKey;
}
</script>

<?php include 'includes/footer.php'; ?>
