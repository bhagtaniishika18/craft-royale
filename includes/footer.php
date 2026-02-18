<footer class="footer">
    <div class="footer-content">
        <!-- Company Information -->
        <div class="footer-column company-info">
            <div class="footer-logo">
                <img src="cr_logo.png" alt="Craft Royale Logo" class="logo-circle">
                <h3>Craft Royale</h3>
            </div>
            <p class="company-description">
                We offer a wide variety in Embroidery Laces, Metal Sequins, Pom Poms, Glass Crystals, Sew on Studs, Gota Patti, French Wire, Acrylics Beads, Natural Stone Beads, Cabochon Stones and other beading supplies.
            </p>
            <div class="social-icons">
                <a href="https://www.facebook.com" class="social-icon" aria-label="Facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com" class="social-icon" aria-label="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.linkedin.com" class="social-icon" aria-label="LinkedIn" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://www.youtube.com" class="social-icon" aria-label="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <!-- Useful Links -->
        <div class="footer-column">
            <h4>Useful Links</h4>
            <ul class="footer-links">
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="faqs.php">FAQs</a></li>
                <li><a href="privacy-policy.php">Privacy Policy</a></li>
                <li><a href="payment-options.php">Payment Option</a></li>
                <li><a href="returns-refund.php">Returns & Refund</a></li>
                <li><a href="track-order.php">Track Order</a></li>
                <li><a href="reviews.php">Reviews</a></li>
            </ul>
        </div>

        <!-- Policy Info -->
        <div class="footer-column">
            <h4>Policy Info</h4>
            <ul class="footer-links">
                <li><a href="profile.php">My Account</a></li>
                <li><a href="terms-of-services.php">Terms of Services</a></li>
                <li><a href="domestic-shipping.php">Domestic Shipping Policy</a></li>
            </ul>

            <div class="payment-methods">
                <h5>Payment Methods</h5>
                <div class="payment-icons" style="margin-top: 15px;">
                    <span class="payment-logo paypal">PayPal</span>
                    <span class="payment-logo visa">VISA</span>
                </div>
            </div>
        </div>

        <!-- Contact Us -->
        <div class="footer-column contact-info">
            <h4><a href="contact.php" style="color: inherit; text-decoration: none;">Contact Us</a></h4>
            <div class="contact-item">
                <a href="https://mail.google.com" target="_blank" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: inherit;">
                    <i class="fas fa-envelope"></i>
                    <span>care@craftroyale.com</span>
                </a>
            </div>
            <div class="contact-item">
                <a href="https://www.google.com/maps/search/?api=1&query=Aarvak+Garments+Private+Limited+Noida+Sector-6" target="_blank" style="text-decoration: none; color: inherit; display: flex; align-items: flex-start; gap: inherit;">
                    <i class="fas fa-map-marker-alt" style="margin-top: 4px;"></i>
                    <span>C/O:- Aarvak Garments Private Limited, E-71, 2nd Floor, Sector-6, Noida Uttar Pradesh, India Pincode: 201301</span>
                </a>
            </div>
            <div class="contact-item">
                <i class="fas fa-headset"></i>
                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                    <a href="tel:+918826002179" style="text-decoration: none; color: inherit;">+91-8826002179</a>,
                    <a href="tel:01204988495" style="text-decoration: none; color: inherit;">0120-4988495</a>
                </div>
            </div>

        </div>
    </div>

    <div class="copyright">
        <p>Copyright © 2025 Craft Royale by Ishika Bhagtani💕<br>All Rights Reserved.</p>
    </div>
</footer>
<!-- GLOBAL SUBSCRIBE POPUP -->
<div id="subscribeOverlay" class="subscribe-overlay">
  <div class="subscribe-popup">
    <span class="close-btn" onclick="closePopup()">×</span>

    <div class="popup-brand-craft">
         <img src="cr_logo.png" alt="Craft Royale" style="height: 40px; margin-bottom: 10px;">
    </div>
    
    <h2>BE THE FIRST TO KNOW</h2>
    <p>
      Subscribe to the <strong>Craft Royale</strong> newsletter to receive
      timely updates from your favorite products.
    </p>

    <form id="subscribeForm">
      <input type="email" name="email" placeholder="Your email address" required>
      <button type="submit">Subscribe</button>
    </form>

    <small>Your information will never be shared with any third party.</small>

    <div class="dont-show">
      <input type="checkbox" id="dontShow">
      <label for="dontShow">Don’t show this popup again</label>
    </div>
  </div>
</div>

<!-- ROYALE REELS MODAL -->
<div id="reelsModal" class="reels-modal">
    <div class="reels-overlay" onclick="closeReels()"></div>
    <div class="reels-container">
        <!-- Top Navigation Icons -->
        <div class="reels-top-nav">
            <button onclick="filterReels('all')" id="filter-all" class="filter-btn active" title="All Reels"><i class="fas fa-th"></i></button>
            <?php if (isset($_SESSION['user_id'])): ?>
            <button onclick="filterReels('liked')" id="filter-liked" class="filter-btn" title="Liked Reels">
                <i class="fas fa-heart"></i>
                <span class="nav-count" id="count-liked">0</span>
            </button>
            <button onclick="filterReels('saved')" id="filter-saved" class="filter-btn" title="Saved Reels">
                <i class="fas fa-bookmark"></i>
                <span class="nav-count" id="count-saved">0</span>
            </button>
            <?php endif; ?>
        </div>

        <button class="reels-close" onclick="closeReels()"><i class="fas fa-times"></i></button>
        
        <div id="reelsWrapper" class="reels-wrapper">
            <!-- Reels will be injected here via JS -->
        </div>

        <!-- Navigation Arrows -->
        <button class="reel-nav prev" onclick="prevReel()"><i class="fas fa-chevron-up"></i></button>
        <button class="reel-nav next" onclick="nextReel()"><i class="fas fa-chevron-down"></i></button>
    </div>
</div>

<script>
let currentReelIndex = 0;
let allReels = [];
let filteredReels = [];
let activeFilter = 'all';

// Get current user's email for user-specific storage
function getCurrentUserEmail() {
    <?php if (isset($_SESSION['user_email'])): ?>
        return '<?php echo $_SESSION['user_email']; ?>';
    <?php else: ?>
        return null;
    <?php endif; ?>
}

// Get user-specific localStorage key
function getUserStorageKey(baseKey) {
    const userEmail = getCurrentUserEmail();
    if (!userEmail) {
        return baseKey; // Fallback for non-logged-in users
    }
    return `${baseKey}_${userEmail}`;
}

function checkReelAuth() {
    const loggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const loginIcon = document.getElementById('loginIcon');
    const isActuallyLoggedIn = loggedIn || (loginIcon && loginIcon.classList.contains('user-logged-in'));
    
    if (!isActuallyLoggedIn) {
        if (typeof openLogin === 'function') {
            openLogin();
            if (typeof showToast === 'function') {
                showToast('Login Required', 'Please login to Like, Comment or Save Reels! ✨', 'info');
            }
        } else {
            alert('Please login to interact with Reels!');
        }
        return false;
    }
    return true;
}

async function openReels() {
    const modal = document.getElementById('reelsModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    if (allReels.length === 0) {
        await fetchReels();
    }
    
    filterReels(activeFilter);
}

function closeReels() {
    const modal = document.getElementById('reelsModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Stop all videos
    const videos = document.querySelectorAll('.reel-video');
    videos.forEach(v => v.pause());
}

async function fetchReels() {
    try {
        const response = await fetch('get-reels.php');
        allReels = await response.json();
        filteredReels = [...allReels];
    } catch (err) {
        console.error('Error fetching reels:', err);
    }
}

function filterReels(filter) {
    // Check if user is logged in for liked/saved filters
    if ((filter === 'liked' || filter === 'saved') && !checkReelAuth()) {
        return; // Don't proceed if not logged in
    }
    
    activeFilter = filter;
    
    // Update UI
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    const filterBtn = document.getElementById(`filter-${filter}`);
    if (filterBtn) filterBtn.classList.add('active');
    
    const liked = JSON.parse(localStorage.getItem(getUserStorageKey('reelLikes')) || '{}');
    const saved = JSON.parse(localStorage.getItem(getUserStorageKey('reelSaves')) || '{}');
    
    // Update Counts at the top
    updateNavCounts();

    if (filter === 'liked') {
        filteredReels = allReels.filter(r => liked[r.id]);
    } else if (filter === 'saved') {
        filteredReels = allReels.filter(r => saved[r.id]);
    } else {
        filteredReels = [...allReels];
    }
    
    const wrapper = document.getElementById('reelsWrapper');
    if (filter === 'all') {
        wrapper.classList.remove('grid-mode');
        renderReels();
        if (filteredReels.length > 0) {
            currentReelIndex = 0;
            playReel(0);
        }
    } else {
        wrapper.classList.add('grid-mode');
        renderGridView();
    }
}

function renderGridView() {
    const wrapper = document.getElementById('reelsWrapper');
    if (filteredReels.length === 0) {
        let msg = activeFilter === 'liked' ? "No liked reels yet. Heart some! ❤️" : "No saved reels yet. Bookmark some! 🔖";
        wrapper.innerHTML = `<div style="color:#fff; text-align:center; padding:100px 20px;">${msg}</div>`;
        return;
    }

    wrapper.innerHTML = `
        <div class="reel-grid-container">
            ${filteredReels.map((reel, index) => `
                <div class="grid-reel-item" onclick="selectFromGrid(${index})">
                    <video src="${reel.video}#t=0.5"></video>
                    <div class="grid-item-overlay">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function selectFromGrid(index) {
    const wrapper = document.getElementById('reelsWrapper');
    wrapper.classList.remove('grid-mode');
    renderReels();
    currentReelIndex = index;
    playReel(index);
}

function renderReels() {
    const wrapper = document.getElementById('reelsWrapper');
    if (filteredReels.length === 0) {
        wrapper.innerHTML = `<div style="color:#fff; text-align:center; padding:100px 20px;">No reels available. ✨</div>`;
        return;
    }

    const likedObj = JSON.parse(localStorage.getItem(getUserStorageKey('reelLikes')) || '{}');
    const savedObj = JSON.parse(localStorage.getItem(getUserStorageKey('reelSaves')) || '{}');

    wrapper.innerHTML = filteredReels.map((reel, index) => {
        const isLiked = likedObj[reel.id] ? 'active' : '';
        const isSaved = savedObj[reel.id] ? 'active' : '';
        const comments = JSON.parse(localStorage.getItem(getUserStorageKey(`reelComments_${reel.id}`)) || '[]');
        
        return `
            <div class="reel-item ${index === 0 ? 'active' : ''}" data-index="${index}" id="reel-${reel.id}">
                <div class="reel-video-box">
                    <video class="reel-video" loop playsinline src="${reel.video}" onclick="handleVideoClick(this)"></video>
                    <div class="reel-mute-overlay"><i class="fas fa-volume-mute"></i></div>
                </div>
                
                <div class="reel-sidebar">
                    <div class="reel-action ${isLiked}" onclick="toggleLike(this, ${reel.id})" id="like-btn-${reel.id}">
                        <i class="fas fa-heart" ${likedObj[reel.id] ? 'style="color:#ff4757"' : ''}></i>
                        <span>Like</span>
                    </div>
                    <div class="reel-action" onclick="openComments(${reel.id})">
                        <i class="fas fa-comment"></i>
                        <span>Comment</span>
                    </div>
                    <div class="reel-action ${isSaved}" onclick="toggleSave(this, ${reel.id})" id="save-btn-${reel.id}">
                        <i class="fas fa-bookmark" ${savedObj[reel.id] ? 'style="color:#ffd700"' : ''}></i>
                        <span>Save</span>
                    </div>
                </div>

                <!-- Client-only Comment Section -->
                <div class="reel-comments-overlay" id="comments-${reel.id}">
                    <div class="comments-header">
                        <span>Comments</span>
                        <i class="fas fa-times" onclick="closeComments(${reel.id})"></i>
                    </div>
                    <div class="comments-list" id="comment-list-${reel.id}">
                        <div class="comment-item">
                            <div class="comment-user">✦ Admin</div>
                            <div class="comment-text">Welcome to the first look at this creation! ✨</div>
                        </div>
                        ${comments.map(c => `
                            <div class="comment-item">
                                <div class="comment-user">You</div>
                                <div class="comment-text">${c}</div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="comment-input-area">
                        <input type="text" placeholder="Add a comment..." id="input-${reel.id}" onkeypress="handleCommentKey(event, ${reel.id})">
                        <button onclick="addComment(${reel.id})"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>

                <div class="reel-bottom-info">
                    <div class="reel-author">
                        <img src="cr_logo.png" alt="Logo">
                        <span>Craft Royale</span>
                    </div>
                    <h3 class="reel-title">${reel.title}</h3>
                    <p class="reel-music"><i class="fas fa-music"></i> Original Audio - Craft Royale</p>
                </div>
            </div>
        `;
    }).join('');
}

function openComments(reelId) {
    if (!checkReelAuth()) return;
    const comments = document.getElementById(`comments-${reelId}`);
    if (comments) comments.classList.add('active');
}

function closeComments(reelId) {
    const comments = document.getElementById(`comments-${reelId}`);
    if (comments) comments.classList.remove('active');
}

function handleCommentKey(e, reelId) {
    if (e.key === 'Enter') {
        addComment(reelId);
    }
}

function addComment(reelId) {
    const input = document.getElementById(`input-${reelId}`);
    const list = document.getElementById(`comment-list-${reelId}`);
    const text = input.value.trim();
    
    if (text) {
        const existing = JSON.parse(localStorage.getItem(getUserStorageKey(`reelComments_${reelId}`)) || '[]');
        existing.push(text);
        localStorage.setItem(getUserStorageKey(`reelComments_${reelId}`), JSON.stringify(existing));

        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment-item';
        commentDiv.innerHTML = `
            <div class="comment-user">You</div>
            <div class="comment-text">${text}</div>
        `;
        list.appendChild(commentDiv);
        list.scrollTop = list.scrollHeight;
        input.value = '';
    }
}

function toggleLike(btn, reelId) {
    if (!checkReelAuth()) return;
    const likes = JSON.parse(localStorage.getItem(getUserStorageKey('reelLikes')) || '{}');
    const icon = btn.querySelector('i');
    
    if (likes[reelId]) {
        delete likes[reelId];
        btn.classList.remove('active');
        icon.style.color = '#fff';
    } else {
        likes[reelId] = true;
        btn.classList.add('active');
        icon.style.color = '#ff4757';
        icon.classList.add('fa-bounce');
        setTimeout(() => icon.classList.remove('fa-bounce'), 1000);
    }
    
    localStorage.setItem(getUserStorageKey('reelLikes'), JSON.stringify(likes));
    updateNavCounts();
}

function toggleSave(btn, reelId) {
    if (!checkReelAuth()) return;
    const saves = JSON.parse(localStorage.getItem(getUserStorageKey('reelSaves')) || '{}');
    const icon = btn.querySelector('i');
    
    if (saves[reelId]) {
        delete saves[reelId];
        btn.classList.remove('active');
        icon.style.color = '#fff';
    } else {
        saves[reelId] = true;
        btn.classList.add('active');
        icon.style.color = '#ffd700';
    }
    
    localStorage.setItem(getUserStorageKey('reelSaves'), JSON.stringify(saves));
    updateNavCounts();
}

function updateNavCounts() {
    const liked = JSON.parse(localStorage.getItem(getUserStorageKey('reelLikes')) || '{}');
    const saved = JSON.parse(localStorage.getItem(getUserStorageKey('reelSaves')) || '{}');
    const lCount = Object.keys(liked).length;
    const sCount = Object.keys(saved).length;
    
    const lBadge = document.getElementById('count-liked');
    const sBadge = document.getElementById('count-saved');
    
    // Only update if elements exist (user is logged in)
    if (lBadge) {
        lBadge.textContent = lCount;
        lBadge.style.display = lCount > 0 ? 'flex' : 'none';
    }
    
    if (sBadge) {
        sBadge.textContent = sCount;
        sBadge.style.display = sCount > 0 ? 'flex' : 'none';
    }
}

function playReel(index) {
    const items = document.querySelectorAll('.reel-item');
    const videos = document.querySelectorAll('.reel-video');
    
    videos.forEach(v => v.pause());
    items.forEach(item => item.classList.remove('active'));
    
    if (items[index]) {
        items[index].classList.add('active');
        const video = items[index].querySelector('.reel-video');
        video.currentTime = 0;
        video.play().catch(e => console.log("Playback interrupted"));
        
        items[index].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function nextReel() {
    if (currentReelIndex < filteredReels.length - 1) {
        currentReelIndex++;
        playReel(currentReelIndex);
    }
}

function prevReel() {
    if (currentReelIndex > 0) {
        currentReelIndex--;
        playReel(currentReelIndex);
    }
}

function handleVideoClick(video) {
    const overlay = video.parentElement.querySelector('.reel-mute-overlay');
    const icon = overlay.querySelector('i');
    
    video.muted = !video.muted;
    
    if (video.muted) {
        icon.className = 'fas fa-volume-mute';
    } else {
        icon.className = 'fas fa-volume-up';
    }
    
    overlay.classList.add('visible');
    setTimeout(() => {
        overlay.classList.remove('visible');
    }, 1000);
}

document.getElementById('reelsWrapper').addEventListener('wheel', function(e) {
    e.preventDefault();
    if (e.deltaY > 0) nextReel();
    else prevReel();
}, { passive: false });
</script>

<!-- ✅ Live Search - Handled inline in header.php -->

<!-- ✅ Hero Image Slider -->
<script src="assets/js/slider.js"></script>
<script src="assets/js/hero-text.js"></script>
<!-- Libraries for SweetAlert2 and Confetti (already in some pages, added here for global support) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="assets/js/popup.js?v=<?= time() ?>"></script>

<!-- ✅ Scroll Animations -->
<script src="assets/js/scroll-animations.js"></script>

<!-- Prevent Back Button After Logout -->
<script src="assets/js/prevent-back.js"></script>

<!-- Dynamic Tab Title Based on Visibility -->
<script src="assets/js/tab-title.js"></script>

<!-- PDF Libraries for Receipt Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Global Receipt Download Handler -->
<script>
window.downloadReceipt = function(orderId) {
    if (window.isReceiptGenerating) return;
    window.isReceiptGenerating = true;

    if (typeof Swal === 'undefined') {
        window.open('download_receipt.php?id=' + orderId, '_blank');
        window.isReceiptGenerating = false;
        return;
    }

    // Modern, Ultra-Premium Minimalist Popup (Apple Style)
    Swal.fire({
        html: `
            <div class="luxury-billing-wrapper">
                <div class="luxury-loader" id="luxury-loader">
                    <div class="luxury-spinner-new">
                        <div class="spinner-ring"></div>
                        <div class="spinner-dot"></div>
                    </div>
                    <div class="luxury-content">
                        <h3 class="luxury-heading">Preparing Invoice</h3>
                        <p class="luxury-paragraph">Handcrafting your professional document...</p>
                    </div>
                </div>
            </div>
        `,
        width: '420px',
        padding: '0',
        background: '#ffffff',
        showConfirmButton: false,
        showCancelButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'luxury-billing-popup'
        },
        didOpen: () => {
            // Ensure the loader stays for a natural duration (Min 2s) for a more professional feel
            const minWait = new Promise(resolve => setTimeout(resolve, 2000));
            const fetchBill = fetch('download_receipt.php?id=' + orderId + '&html_only=1').then(r => r.text());

            Promise.all([fetchBill, minWait]).then(([html]) => {
                const container = document.createElement('div');
                container.id = 'temp-receipt-dl-container';
                container.style.position = 'fixed';
                container.style.left = '-10000px';
                container.style.top = '0';
                container.style.width = '850px';
                container.innerHTML = html;
                document.body.appendChild(container);

                const images = container.getElementsByTagName('img');
                let imagesLoaded = 0;
                
                const startCapture = () => {
                    html2canvas(container, {
                        scale: 1.5,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    }).then(canvas => {
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'a4');
                        const imgData = canvas.toDataURL('image/png', 0.98);
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const ratio = (pdfWidth - 20) / canvas.width;
                        
                        pdf.addImage(imgData, 'PNG', 10, 10, canvas.width * ratio, canvas.height * ratio);
                        pdf.save('Craft-Royale-Invoice-' + orderId + '.pdf');
                        
                        // Show success state with a guaranteed auto-close timer
                        Swal.fire({
                            html: `
                                <div class="luxury-success-state">
                                    <div class="luxury-icon-box">
                                        <div class="luxury-check-ring"></div>
                                        <i class="fas fa-check luxury-check-mark"></i>
                                    </div>
                                    <div class="luxury-content">
                                        <h3 class="luxury-heading" style="color: #27ae60;">Invoice Ready</h3>
                                        <p class="luxury-paragraph">Successfully saved to your downloads</p>
                                    </div>
                                </div>
                            `,
                            width: '420px',
                            padding: '0',
                            background: '#ffffff',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'luxury-billing-popup luxury-popup-success'
                            },
                            didClose: () => {
                                if (container.parentNode) document.body.removeChild(container);
                                window.isReceiptGenerating = false;
                            }
                        });

                    }).catch(err => {
                        console.error('PDF Error:', err);
                        if (container.parentNode) document.body.removeChild(container);
                        Swal.close();
                        window.isReceiptGenerating = false;
                        window.open('download_receipt.php?id=' + orderId, '_blank');
                    });
                };

                if (images.length === 0) startCapture();
                else {
                    Array.from(images).forEach(img => {
                        const done = () => { imagesLoaded++; if (imagesLoaded >= images.length) startCapture(); };
                        if (img.complete) done(); else img.onload = img.onerror = done;
                    });
                    setTimeout(() => { if (imagesLoaded < images.length) startCapture(); }, 3000);
                }
            });
        }
    });
};
</script>

<style>
/* Apple-Style Ultra-Premium Billing Popup */
.luxury-billing-popup {
    border-radius: 30px !important;
    overflow: hidden !important;
    box-shadow: 0 40px 100px rgba(0,0,0,0.12) !important;
    border: 1px solid rgba(0,0,0,0.03) !important;
    padding: 60px 40px !important;
}

.luxury-billing-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.luxury-spinner-new {
    position: relative;
    width: 60px;
    height: 60px;
    margin-bottom: 35px;
}

.spinner-ring {
    position: absolute;
    inset: 0;
    border: 2px solid #f2f2f2;
    border-top: 2px solid #e91e63;
    border-radius: 50%;
    animation: luxury-spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.spinner-dot {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 4px;
    height: 4px;
    background: #00c2cb;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 10px rgba(0, 194, 203, 0.5);
}

.luxury-content {
    animation: luxury-fade-up 0.5s ease-out both;
}

.luxury-heading {
    font-family: 'Cinzel', serif !important;
    font-size: 24px !important;
    font-weight: 700 !important;
    color: #1a1a1a !important;
    margin: 0 0 10px 0 !important;
    letter-spacing: 1px;
}

.luxury-paragraph {
    font-family: 'Inter', sans-serif !important;
    font-size: 14px !important;
    color: #888 !important;
    font-weight: 500 !important;
    margin: 0 !important;
}

/* Success State Refinements */
.luxury-icon-box {
    position: relative;
    width: 80px;
    height: 80px;
    margin-bottom: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.luxury-check-ring {
    position: absolute;
    inset: 0;
    border: 2px solid #e6f9f1;
    border-radius: 50%;
    animation: luxury-scale-up 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
}

.luxury-check-mark {
    font-size: 32px;
    color: #27ae60;
    z-index: 2;
    animation: luxury-check-pop 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
}

@keyframes luxury-spin {
    to { transform: rotate(360deg); }
}

@keyframes luxury-fade-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes luxury-scale-up {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes luxury-check-pop {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}

.luxury-popup-success {
    background: #ffffff !important;
    transition: background 0.5s ease;
}
</style>

</body>
</html>
