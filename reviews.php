<?php
include 'includes/db.php';
include 'includes/header.php';

// Fetch approved reviews
$approved_reviews = mysqli_query($conn, "SELECT * FROM reviews WHERE status='approved' ORDER BY created_at DESC LIMIT 20");

// Calculate average rating
$rating_query = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE status='approved'");
$rating_data = mysqli_fetch_assoc($rating_query);
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];
?>

<style>
/* Reviews Page Styles */
.reviews-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f8f0 100%);
    padding: 40px 20px;
    animation: fadeIn 0.8s ease-out;
}

.reviews-page-container {
    max-width: 1200px;
    margin: 0 auto;
}

.reviews-page-header {
    text-align: center;
    margin-bottom: 50px;
    animation: fadeInUp 0.8s ease-out;
}

.reviews-page-header h1 {
    font-size: 48px;
    font-weight: 800;
    color: #2b2b2b;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.reviews-page-header p {
    font-size: 18px;
    color: #666;
    margin: 0;
}

.go-home-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(47, 167, 107, 0.3);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.go-home-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.go-home-btn:hover::before {
    width: 300px;
    height: 300px;
}

.go-home-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.4);
    color: #ffffff;
}

.go-home-btn:active {
    transform: translateY(-1px);
}

.go-home-btn i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

.go-home-btn:hover i {
    transform: translateX(-3px);
}

.reviews-content {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 40px;
    margin-bottom: 60px;
}

/* Review Form */
.review-form-container {
    background: #ffffff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    animation: fadeInLeft 0.8s ease-out;
}

.review-form-container h2 {
    font-size: 28px;
    font-weight: 700;
    color: #2b2b2b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.review-form-container p {
    color: #666;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #2b2b2b;
    margin-bottom: 8px;
}

.form-group label .required {
    color: #e74c3c;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2fa76b;
    box-shadow: 0 0 0 4px rgba(47, 167, 107, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.rating-input {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.rating-stars {
    display: flex;
    gap: 5px;
    font-size: 28px;
    cursor: pointer;
}

.rating-stars .star {
    color: #ddd;
    transition: all 0.2s ease;
}

.rating-stars .star:hover,
.rating-stars .star.active {
    color: #ffc107;
    transform: scale(1.2);
}

.rating-stars .star.filled {
    color: #ffc107;
}

.image-upload-wrapper {
    position: relative;
}

.image-preview {
    margin-top: 15px;
    display: none;
}

.image-preview img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.file-input-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 18px;
    border: 2px dashed #2fa76b;
    border-radius: 12px;
    background: #f0fdf9;
    color: #2fa76b;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-input-label:hover {
    background: #e8f8f0;
    border-color: #2fc7b4;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #2fa76b 0%, #2fc7b4 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 6px 20px rgba(47, 167, 107, 0.3);
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.submit-btn:hover::before {
    width: 400px;
    height: 400px;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(47, 167, 107, 0.4);
}

.submit-btn:active {
    transform: translateY(0);
}

/* Reviews Display */
.reviews-display {
    background: #ffffff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    animation: fadeInRight 0.8s ease-out;
    max-height: 800px;
    overflow-y: auto;
}

.reviews-display h2 {
    font-size: 28px;
    font-weight: 700;
    color: #2b2b2b;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-item {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 12px;
    border-left: 4px solid #2fa76b;
    transition: all 0.3s ease;
}

.review-item:hover {
    background: #f0fdf9;
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(47, 167, 107, 0.1);
}

.review-item-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 12px;
}

.review-item-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2fa76b, #2fc7b4);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.review-item-info h4 {
    margin: 0;
    font-size: 16px;
    color: #2b2b2b;
    font-weight: 600;
}

.review-item-info p {
    margin: 2px 0 0 0;
    font-size: 12px;
    color: #999;
}

.review-item-rating {
    display: flex;
    gap: 3px;
    margin-bottom: 10px;
}

.review-item-rating .star {
    font-size: 16px;
    color: #ffc107;
}

.review-item-message {
    color: #555;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    border-left: 4px solid #28a745;
    animation: slideDown 0.5s ease-out;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    border-left: 4px solid #dc3545;
    animation: slideDown 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

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

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 968px) {
    .reviews-content {
        grid-template-columns: 1fr;
    }
    
    .reviews-page-header h1 {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .reviews-page {
        padding: 20px 15px;
    }
    
    .review-form-container,
    .reviews-display {
        padding: 25px 20px;
    }
    
    .reviews-page-header h1 {
        font-size: 28px;
    }
}
</style>

<div class="reviews-page">
    <div class="reviews-page-container">
        <div class="reviews-page-header">
            <a href="index.php" class="go-home-btn">
                <i class="fas fa-arrow-left"></i>
                Go Back to Home
            </a>
            <h1>✨ Share Your Experience ✨</h1>
            <p>Your feedback helps us serve you better! 🌟</p>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="success-message">
                🎉 Thank you for your review! It will be published after admin approval.
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                ❌ Something went wrong. Please try again.
            </div>
        <?php endif; ?>

        <div class="reviews-content">
            <!-- Review Form -->
            <div class="review-form-container">
                <h2>💬 Write a Review</h2>
                <p>Tell us about your experience with Craft Royale</p>
                
                <form action="submit_review.php" method="POST" enctype="multipart/form-data" id="reviewForm">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="Enter your name">
                    </div>

                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" required placeholder="your.email@example.com">
                    </div>

                    <div class="form-group">
                        <label>Rating <span class="required">*</span></label>
                        <div class="rating-input">
                            <div class="rating-stars" id="ratingStars">
                                <span class="star" data-rating="1">★</span>
                                <span class="star" data-rating="2">★</span>
                                <span class="star" data-rating="3">★</span>
                                <span class="star" data-rating="4">★</span>
                                <span class="star" data-rating="5">★</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Product Category</label>
                        <select name="product_category">
                            <option value="">Select Category</option>
                            <option value="Embroidery Materials">Embroidery Materials</option>
                            <option value="Threads">Threads</option>
                            <option value="Beads">Beads</option>
                            <option value="Crystals & Stones">Crystals & Stones</option>
                            <option value="Tools & Frames">Tools & Frames</option>
                            <option value="Jewelry Making">Jewelry Making</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Overall Experience</label>
                        <select name="experience">
                            <option value="">Select Experience</option>
                            <option value="Excellent">Excellent ⭐⭐⭐⭐⭐</option>
                            <option value="Very Good">Very Good ⭐⭐⭐⭐</option>
                            <option value="Good">Good ⭐⭐⭐</option>
                            <option value="Fair">Fair ⭐⭐</option>
                            <option value="Poor">Poor ⭐</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Feedback <span class="required">*</span></label>
                        <textarea name="feedback" required placeholder="Share your thoughts about our products and service..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Additional Message</label>
                        <textarea name="message" placeholder="Any additional comments or suggestions..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Image (Optional)</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="review_image" id="reviewImage" accept="image/*">
                            <label for="reviewImage" class="file-input-label">
                                📷 Choose Image (Optional)
                            </label>
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">🚀 Submit Review</button>
                </form>
            </div>

            <!-- Reviews Display -->
            <div class="reviews-display">
                <h2>💖 Customer Reviews</h2>
                <div class="reviews-list">
                    <?php if(mysqli_num_rows($approved_reviews) > 0): ?>
                        <?php while($review = mysqli_fetch_assoc($approved_reviews)): 
                            $initial = strtoupper(substr($review['name'], 0, 1));
                            $time_ago = timeAgo($review['created_at']);
                        ?>
                            <div class="review-item">
                                <div class="review-item-header">
                                    <div class="review-item-avatar"><?= $initial ?></div>
                                    <div class="review-item-info">
                                        <h4><?= htmlspecialchars($review['name']) ?></h4>
                                        <p><?= $time_ago ?></p>
                                    </div>
                                </div>
                                <div class="review-item-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <?php if($review['product_category']): ?>
                                    <p style="font-size: 12px; color: #2fa76b; margin-bottom: 8px;">
                                        📦 <?= htmlspecialchars($review['product_category']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="review-item-message"><?= nl2br(htmlspecialchars($review['feedback'])) ?></p>
                                <?php if($review['image']): ?>
                                    <img src="uploads/reviews/<?= htmlspecialchars($review['image']) ?>" 
                                         alt="Review Image" 
                                         style="max-width: 100%; border-radius: 8px; margin-top: 10px;">
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 40px;">
                            No reviews yet. Be the first to review! 🌟
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Rating Stars Interaction
const stars = document.querySelectorAll('.rating-stars .star');
const ratingInput = document.getElementById('ratingValue');

stars.forEach((star, index) => {
    star.addEventListener('click', () => {
        const rating = index + 1;
        ratingInput.value = rating;
        
        stars.forEach((s, i) => {
            if (i < rating) {
                s.classList.add('filled', 'active');
            } else {
                s.classList.remove('filled', 'active');
            }
        });
    });
    
    star.addEventListener('mouseenter', () => {
        const rating = index + 1;
        stars.forEach((s, i) => {
            if (i < rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});

document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
    const currentRating = parseInt(ratingInput.value);
    stars.forEach((s, i) => {
        if (i < currentRating) {
            s.classList.add('active');
        } else {
            s.classList.remove('active');
        }
    });
});

// Image Preview
const imageInput = document.getElementById('reviewImage');
const imagePreview = document.getElementById('imagePreview');
const previewImg = document.getElementById('previewImg');

imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.style.display = 'none';
    }
});
</script>

<?php
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' mins ago';
    if ($diff < 86400) return floor($diff/3600) . ' hrs ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    if ($diff < 2592000) return floor($diff/604800) . ' weeks ago';
    if ($diff < 31536000) return floor($diff/2592000) . ' months ago';
    return floor($diff/31536000) . ' years ago';
}
?>

<?php include 'includes/footer.php'; ?>

