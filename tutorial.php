<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch active tutorials from database
$query = "SELECT * FROM tutorials WHERE status = 'active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/tutorial/tutorial.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/embroidery.jpg" alt="Tutorial Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title"> TUTORIALS </h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Tutorials</span>
        </nav>
    </div>
</section>

<!-- TUTORIALS SECTION -->
<section class="embroidery-section">
    <div class="section-container">
        <h2 class="section-title">📚 Learn & Create ✨</h2>
        <p style="text-align: center; color: #666; font-size: 16px; margin-top: -20px; margin-bottom: 20px;">
            Watch our step-by-step tutorials and master new crafting techniques! 🎨
        </p>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="tutorials-grid">
                <?php while($tutorial = mysqli_fetch_assoc($result)): 
                    $video_path = !empty($tutorial['video']) ? 'uploads/tutorials/' . htmlspecialchars($tutorial['video']) : '';
                    $date = date('F j, Y', strtotime($tutorial['created_at']));
                ?>
                    <div class="tutorial-item">
                        <div class="tutorial-video-wrapper">
                            <?php if (!empty($video_path)): ?>
                                <video controls class="tutorial-video-player" preload="metadata" playsinline webkit-playsinline controlsList="nodownload">
                                    <source src="<?php echo $video_path; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                               <!-- <div class="video-play-overlay">
                                    <div class="play-button-circle">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                                <div class="pause-button-overlay"></div>-->
                            <?php else: ?>
                                <div class="tutorial-placeholder">
                                    <i class="fas fa-video"></i>
                                    <p>No video available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tutorial-content">
                            <h3 class="tutorial-title"><?php echo htmlspecialchars($tutorial['title']); ?></h3>
                            <div class="tutorial-meta">
                                <span class="tutorial-date">
                                    <i class="far fa-calendar"></i>
                                    <span>on <?php echo $date; ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="tutorial-empty">
                <div class="empty-icon">🎥</div>
                <h3>No Tutorials Available</h3>
                <p>Check back soon for new tutorial videos!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Hide pause button when videos are playing
document.addEventListener('DOMContentLoaded', function() {
    const videos = document.querySelectorAll('.tutorial-video-player');
    
    videos.forEach(function(video) {
        const overlay = video.parentElement.querySelector('.pause-button-overlay');
        let originalControls = video.hasAttribute('controls');
        let controlsRemoved = false;
        
        function handlePlayState() {
            if (!video.paused) {
                // Video is playing - remove controls to hide pause button
                if (!controlsRemoved) {
                    video.removeAttribute('controls');
                    controlsRemoved = true;
                    video.classList.add('video-playing');
                    if (overlay) {
                        overlay.classList.add('active');
                    }
                }
            } else {
                // Video is paused - restore controls so user can play again
                if (controlsRemoved) {
                    if (originalControls) {
                        video.setAttribute('controls', 'controls');
                    }
                    controlsRemoved = false;
                    video.classList.remove('video-playing');
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                }
            }
        }
        
        // Handle play/pause events
        video.addEventListener('play', function() {
            setTimeout(handlePlayState, 50);
        });
        
        video.addEventListener('pause', function() {
            setTimeout(handlePlayState, 50);
        });
        
        video.addEventListener('playing', handlePlayState);
        
        // Allow clicking video to play/pause when controls are removed
        video.addEventListener('click', function(e) {
            if (controlsRemoved && !video.paused) {
                // If playing and controls removed, allow pause on click
                e.preventDefault();
                video.pause();
            } else if (controlsRemoved && video.paused) {
                // If paused and controls removed, play
                e.preventDefault();
                video.play();
            }
        });
        
        // Block clicks on overlay (pause button area)
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.stopPropagation();
                // Allow pause by clicking the overlay area
                if (!video.paused) {
                    video.pause();
                }
            });
        }
        
        // Initial state check
        setTimeout(handlePlayState, 100);
        
        // Monitor play state continuously
        setInterval(handlePlayState, 200);
    });
});
</script>

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

/* TUTORIALS GRID */
.tutorials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.tutorial-item {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    position: relative;
    animation: fadeInUp 0.6s ease-out both;
}

.tutorial-item:nth-child(1) { animation-delay: 0.1s; }
.tutorial-item:nth-child(2) { animation-delay: 0.2s; }
.tutorial-item:nth-child(3) { animation-delay: 0.3s; }
.tutorial-item:nth-child(4) { animation-delay: 0.4s; }
.tutorial-item:nth-child(5) { animation-delay: 0.5s; }
.tutorial-item:nth-child(6) { animation-delay: 0.6s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tutorial-item::before {
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

.tutorial-item:hover::before {
    transform: scaleX(1);
}

.tutorial-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(47, 199, 180, 0.2);
    border-color: rgba(47, 167, 107, 0.2);
}

.tutorial-video-wrapper {
    position: relative;
    width: 100%;
    height: 450px;
    overflow: hidden;
    background: linear-gradient(135deg, #e8f8f0, #f0fdf4);
    border-radius: 12px 12px 0 0;
}

.tutorial-video-player {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    display: block;
}

/* Hide play/pause button in video controls completely */
.tutorial-video-player::-webkit-media-controls-play-button {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    left: -9999px !important;
}

.tutorial-video-player::-webkit-media-controls-start-playback-button {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    left: -9999px !important;
}

/* Hide when playing */
.tutorial-video-player:not([paused])::-webkit-media-controls-play-button,
.tutorial-video-player:not([paused])::-webkit-media-controls-start-playback-button,
.tutorial-video-player.video-playing::-webkit-media-controls-play-button,
.tutorial-video-player.video-playing::-webkit-media-controls-start-playback-button {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    left: -9999px !important;
}

/* For Firefox and other browsers */
.tutorial-video-player::-moz-media-controls-play-button {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Hide controls panel play button area */
.tutorial-video-player::-webkit-media-controls-panel {
    display: flex !important;
}

/* Target any button in controls */
video.tutorial-video-player::-webkit-media-controls-panel > button:first-child {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
}

/* Fullscreen video styling - covers entire screen and shows full video */
.tutorial-video-player:-webkit-full-screen {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    object-fit: contain;
    background: #000;
}

.tutorial-video-player:-moz-full-screen {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    object-fit: contain;
    background: #000;
}

.tutorial-video-player:-ms-fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    object-fit: contain;
    background: #000;
}

.tutorial-video-player:fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    object-fit: contain;
    background: #000;
}

.tutorial-item:hover .tutorial-video-player {
    transform: scale(1.05);
}

.video-play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.2) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 1;
}

.pause-button-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 50px;
    background: transparent;
    z-index: 10;
    pointer-events: auto;
    display: none;
}

.pause-button-overlay.active {
    display: block;
}

.tutorial-item:hover .video-play-overlay {
    opacity: 0.7;
}

.play-button-circle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
    transform: scale(0.9);
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

.tutorial-item:hover .play-button-circle {
    transform: scale(1.05);
    box-shadow: 0 8px 30px rgba(47, 167, 107, 0.5);
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
    }
    50% {
        box-shadow: 0 6px 28px rgba(47, 167, 107, 0.5);
    }
}

.tutorial-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    color: #2fc7b4;
}

.tutorial-placeholder i {
    font-size: 64px;
    margin-bottom: 10px;
}

.tutorial-placeholder p {
    font-size: 16px;
    font-weight: 600;
    color: #666;
}


.tutorial-content {
    padding: 25px;
    position: relative;
    display: flex;
    flex-direction: column;
}

.tutorial-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #2fa76b;
    margin-bottom: 10px;
    border: 1px solid rgba(47, 167, 107, 0.2);
    width: fit-content;
}

.tutorial-badge i {
    font-size: 12px;
}

.tutorial-title {
    font-size: 20px;
    font-weight: 700;
    color: #2b2b2b;
    margin: 0 0 12px 0;
    line-height: 1.4;
    transition: color 0.3s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tutorial-item:hover .tutorial-title {
    color: #2fa76b;
}

.tutorial-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: auto;
    padding-top: 10px;
}

.tutorial-date {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #666;
}

.tutorial-date i {
    color: #2fc7b4;
}

.tutorial-empty {
    text-align: center;
    padding: 80px 20px;
    color: #999;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.tutorial-empty h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #666;
}

.tutorial-empty p {
    font-size: 16px;
    color: #999;
}

/* Responsive */
@media (max-width: 768px) {
    .embroidery-hero-title {
        font-size: 48px;
    }
    
    .section-title {
        font-size: 24px;
    }
    
    .tutorials-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .tutorials-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .tutorial-video-wrapper {
        height: 350px;
    }
}

@media (max-width: 480px) {
    .embroidery-hero-title {
        font-size: 36px;
        letter-spacing: 2px;
    }
}
</style>

