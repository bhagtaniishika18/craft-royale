<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch all active blog posts from database
$query = "SELECT * FROM blog_posts WHERE status = 'active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* BLOG HERO BANNER */
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

/* BLOG POSTS GRID */
.blog-section {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.blog-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    animation: fadeInUp 0.6s ease-out both;
}

.blog-card:nth-child(1) { animation-delay: 0.1s; }
.blog-card:nth-child(2) { animation-delay: 0.2s; }
.blog-card:nth-child(3) { animation-delay: 0.3s; }
.blog-card:nth-child(4) { animation-delay: 0.4s; }
.blog-card:nth-child(5) { animation-delay: 0.5s; }
.blog-card:nth-child(6) { animation-delay: 0.6s; }

.blog-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(47, 199, 180, 0.2);
}

.blog-card-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card:hover .blog-card-image {
    transform: scale(1.05);
}

.blog-card-content {
    padding: 25px;
}

.blog-card-title {
    font-size: 20px;
    font-weight: 700;
    color: #2b2b2b;
    margin: 0 0 12px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-card-date {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.blog-card-date i {
    color: #2fc7b4;
}

.blog-card-link {
    color: #2fc7b4;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.blog-card-link:hover {
    color: #2fa76b;
    gap: 10px;
}

.blog-card-link i {
    transition: transform 0.3s ease;
}

.blog-card-link:hover i {
    transform: translateX(4px);
}

/* EMPTY STATE */
.blog-empty {
    text-align: center;
    padding: 80px 20px;
    color: #999;
}

.blog-empty i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
}

.blog-empty h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #666;
}

/* PAGINATION */
.blog-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 50px;
    padding: 20px 0;
}

.pagination-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-dot.active {
    background: #2fc7b4;
    transform: scale(1.2);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/blog.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/embroidery.jpg" alt="Blog Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">BLOG</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">Blog</span>
        </nav>
    </div>
</section>

<!-- BLOG POSTS SECTION -->
<section class="blog-section">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="blog-grid">
            <?php while($post = mysqli_fetch_assoc($result)): 
                $date = date('F j, Y', strtotime($post['created_at']));
                $image_path = !empty($post['image']) ? 'uploads/blogs/' . htmlspecialchars($post['image']) : 'assets/images/beads.jpg';
            ?>
                <div class="blog-card" onclick="openBlogModal(<?php echo $post['id']; ?>)">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?php echo $image_path; ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="blog-card-image"
                             onerror="this.src='assets/images/beads.jpg'">
                    <?php else: ?>
                        <img src="assets/images/beads.jpg" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="blog-card-image">
                    <?php endif; ?>
                    <div class="blog-card-content">
                        <h3 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="blog-card-date">
                            <i class="far fa-calendar"></i>
                            <span>on <?php echo $date; ?></span>
                        </div>
                        <a href="javascript:void(0);" class="blog-card-link" onclick="event.stopPropagation(); openBlogModal(<?php echo $post['id']; ?>);">
                            CONTINUE READING <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- PAGINATION (Simple dot indicator for now) -->
        <div class="blog-pagination">
            <div class="pagination-dot active"></div>
        </div>
    <?php else: ?>
        <div class="blog-empty">
            <i class="fas fa-blog"></i>
            <h3>No blog posts available</h3>
            <p>Check back soon for new content!</p>
        </div>
    <?php endif; ?>
</section>

<!-- BLOG MODAL POPUP -->
<div id="blogModal" class="blog-modal-overlay" onclick="closeBlogModal()">
    <div class="blog-modal-content" onclick="event.stopPropagation()">
        <span class="blog-modal-close" onclick="closeBlogModal()">&times;</span>
        <div id="blogModalBody">
            <div class="blog-modal-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* BLOG MODAL STYLES */
.blog-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(8px);
    z-index: 3000000;
    animation: fadeIn 0.3s ease-out;
    overflow-y: auto;
    padding: 15px;
}

.blog-modal-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-modal-content {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border-radius: 20px;
    max-width: 920px;
    width: 100%;
    max-height: 92vh;
    overflow-y: auto;
    position: relative;
    animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 30px 90px rgba(0, 0, 0, 0.5), 0 0 0 2px rgba(47, 199, 180, 0.15);
    border: 3px solid rgba(47, 199, 180, 0.25);
}

.blog-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
    cursor: pointer;
    z-index: 10001;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
}

.blog-modal-close:hover {
    background: linear-gradient(135deg, #ee5a6f, #dc3545);
    transform: rotate(90deg) scale(1.15);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
}

.blog-modal-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-family: 'Poppins', sans-serif;
}

.blog-modal-loading i {
    font-size: 48px;
    color: #2fc7b4;
    margin-bottom: 15px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.blog-modal-body {
    padding: 35px 30px;
    position: relative;
    font-family: 'Poppins', sans-serif;
}

.blog-modal-image {
    width: 100%;
    max-height: 380px;
    object-fit: cover;
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    border: 3px solid rgba(47, 199, 180, 0.25);
    transition: transform 0.3s ease;
}

.blog-modal-image:hover {
    transform: scale(1.01);
}

.blog-modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 34px;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 15px 0;
    line-height: 1.25;
    background: linear-gradient(135deg, #2fc7b4 0%, #2fa76b 50%, #1e8e7a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    padding-left: 0;
    letter-spacing: -0.5px;
}

.blog-modal-title::before {
    content: '📝';
    position: absolute;
    left: -35px;
    top: 5px;
    font-size: 28px;
    -webkit-text-fill-color: initial;
    opacity: 0.8;
}

.blog-modal-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 12px 18px;
    background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.08));
    border-radius: 12px;
    border-left: 3px solid #2fc7b4;
}

.blog-modal-date {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #555;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
}

.blog-modal-date i {
    color: #2fc7b4;
    font-size: 16px;
}

.blog-modal-date::before {
    content: '📅';
    font-size: 16px;
    margin-right: 2px;
}

.blog-modal-content-text {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    line-height: 1.85;
    color: #333;
    white-space: pre-wrap;
    background: linear-gradient(to bottom, #ffffff, #fafbfc);
    padding: 25px 28px;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    border: 2px solid rgba(47, 199, 180, 0.12);
    position: relative;
    margin-top: 5px;
}

.blog-modal-content-text::before {
    content: '✨';
    position: absolute;
    top: -18px;
    left: 25px;
    font-size: 22px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(47, 199, 180, 0.35);
    border: 3px solid #fff;
}

.blog-modal-content-text p {
    margin-bottom: 18px;
    text-align: justify;
    color: #2d2d2d;
    font-weight: 400;
}

.blog-modal-content-text p:first-child {
    margin-top: 0;
}

.blog-modal-content-text p:first-child::first-letter {
    font-family: 'Playfair Display', serif;
    font-size: 64px;
    font-weight: 900;
    color: #2fc7b4;
    float: left;
    line-height: 0.85;
    margin-right: 8px;
    margin-top: 8px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* SCROLLBAR STYLING FOR MODAL */
.blog-modal-content::-webkit-scrollbar {
    width: 10px;
}

.blog-modal-content::-webkit-scrollbar-track {
    background: linear-gradient(135deg, #f1f1f1, #e9ecef);
    border-radius: 10px;
    margin: 10px;
}

.blog-modal-content::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    border-radius: 10px;
    border: 2px solid #f1f1f1;
}

.blog-modal-content::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    box-shadow: 0 0 10px rgba(47, 199, 180, 0.5);
}

/* DECORATIVE ELEMENTS */
.blog-modal-body::after {
    content: '✨';
    position: absolute;
    bottom: 20px;
    right: 20px;
    font-size: 32px;
    opacity: 0.3;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>

<script>
function openBlogModal(blogId) {
    const modal = document.getElementById('blogModal');
    const modalBody = document.getElementById('blogModalBody');
    
    // Show loading state
            modalBody.innerHTML = `
                <div class="blog-modal-loading">
                    <div style="font-size: 48px; margin-bottom: 15px;">📖</div>
                    <i class="fas fa-spinner fa-spin"></i>
                    <p style="margin-top: 15px; font-size: 16px; font-weight: 600;">Loading amazing content...</p>
                </div>
            `;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Fetch blog post content
    fetch(`get_blog_post.php?id=${blogId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const post = data.post;
                const date = new Date(post.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const imagePath = post.image ? `uploads/blogs/${post.image}` : 'assets/images/beads.jpg';
                
                // Escape HTML to prevent XSS, but preserve line breaks
                const escapeHtml = (text) => {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                const title = escapeHtml(post.title);
                // Convert line breaks to paragraphs for better formatting
                const contentLines = escapeHtml(post.content).split('\n').filter(line => line.trim() !== '');
                const formattedContent = contentLines.map(line => `<p>${line.trim()}</p>`).join('');
                
                modalBody.innerHTML = `
                    <div class="blog-modal-body">
                        ${post.image ? `<img src="${imagePath}" alt="${title}" class="blog-modal-image" onerror="this.src='assets/images/beads.jpg'">` : ''}
                        <h2 class="blog-modal-title">${title}</h2>
                        <div class="blog-modal-meta">
                            <div class="blog-modal-date">
                                <i class="far fa-calendar"></i>
                                <span>Published on ${date}</span>
                            </div>
                        </div>
                        <div class="blog-modal-content-text">
                            ${formattedContent}
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="blog-modal-loading">
                        <div style="font-size: 48px; margin-bottom: 15px;">😔</div>
                        <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                        <p style="margin-top: 15px; font-size: 16px; font-weight: 600; color: #dc3545;">Oops! Couldn't load the blog post</p>
                        <p style="margin-top: 10px; font-size: 14px; color: #999;">Please try again later</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `
                <div class="blog-modal-loading">
                    <div style="font-size: 48px; margin-bottom: 15px;">😔</div>
                    <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                    <p style="margin-top: 15px; font-size: 16px; font-weight: 600; color: #dc3545;">Oops! Something went wrong</p>
                    <p style="margin-top: 10px; font-size: 14px; color: #999;">Please try again later</p>
                </div>
            `;
        });
}

function closeBlogModal() {
    const modal = document.getElementById('blogModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}
</script>

<?php include 'includes/footer.php'; ?>
