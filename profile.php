<?php
session_start();

// Prevent caching of authenticated pages
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

include 'includes/db.php';

// Check if user is logged in - redirect if not
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';

// Get user data
$user_id = $_SESSION['user_id'];
$user_data = null;
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    header("Location: index.php");
    exit();
}
?>

<!-- HERO BANNER -->
<section class="embroidery-hero-banner">
    <video class="embroidery-hero-video" autoplay muted loop playsinline>
        <source src="assets/images/login1.mp4" type="video/mp4">
        <!-- Fallback image if video doesn't load -->
        <img src="assets/images/beads.jpg" alt="My Profile Background">
    </video>
    <div class="embroidery-hero-content">
        <h1 class="embroidery-hero-title">MY PROFILE</h1>
        <nav class="breadcrumb">
            <a href="index.php">Home</a> <span class="breadcrumb-separator">></span> <span class="breadcrumb-current">My Profile</span>
        </nav>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="account-section">
    <div class="account-container">
        <div class="account-card">
            <div class="account-header">
                <h2><i class="fas fa-user-circle"></i> Profile Information</h2>
            </div>
            <div class="account-body">
                <!-- VIEW MODE -->
                <div id="profileViewMode" class="profile-view-mode">
                    <div class="profile-details">
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-user"></i> Full Name
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars(trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''))) ?>
                            </div>
                        </div>
                        
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars($user_data['email'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-phone"></i> Mobile Number
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars($user_data['mobile_no'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-map-marker-alt"></i> City
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars($user_data['city'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-map"></i> State
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars($user_data['state'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="profile-detail-row">
                            <div class="profile-detail-label">
                                <i class="fas fa-mail-bulk"></i> ZIP Code
                            </div>
                            <div class="profile-detail-value">
                                <?= htmlspecialchars($user_data['zip'] ?? 'N/A') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <button onclick="toggleEditMode()" class="account-btn" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b);">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                        <a href="account.php" class="account-btn" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b);">
                            <i class="fas fa-shopping-bag"></i> View Orders
                        </a>
                        <a href="my-queries.php" class="account-btn" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b);">
                            <i class="fas fa-comment-dots"></i> View Queries
                        </a>
                        <a href="my-callbacks.php" class="account-btn" style="background: linear-gradient(135deg, #2fc7b4, #2fa76b);">
                            <i class="fas fa-phone-alt"></i> View Call Status
                        </a>
                    </div>
                </div>
                
                <!-- EDIT MODE -->
                <div id="profileEditMode" class="profile-edit-mode" style="display: none;">
                    <form id="profileEditForm" onsubmit="updateProfile(event)">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user_data['first_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user_data['last_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled style="background: #f5f5f5; cursor: not-allowed;">
                            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Mobile Number *</label>
                            <input type="text" name="mobile_no" value="<?= htmlspecialchars($user_data['mobile_no'] ?? '') ?>" pattern="[0-9]{10}" maxlength="10" required>
                            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Must be exactly 10 digits</small>
                        </div>
                        
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($user_data['city'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>State *</label>
                            <input type="text" name="state" value="<?= htmlspecialchars($user_data['state'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>ZIP Code *</label>
                            <input type="text" name="zipcode" value="<?= htmlspecialchars($user_data['zip'] ?? '') ?>" required>
                        </div>
                        
                        <div class="profile-actions">
                            <button type="submit" class="account-btn" style="background: linear-gradient(135deg, #2fa76b, #2fc7b4);">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" onclick="toggleEditMode()" class="account-btn" style="background: linear-gradient(135deg, #6c757d, #5a6268);">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

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

/* ACCOUNT SECTION - Reuse from account.php */
.account-section {
    padding: 60px 20px;
    background: #f5f7fa;
    min-height: 60vh;
}

.account-container {
    max-width: 900px;
    margin: 0 auto;
}

.account-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.account-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.12);
}

.account-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, #2fc7b4, #2fa76b);
    color: #fff;
    text-align: center;
    position: relative;
}

.account-header::before,
.account-header::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 80px;
    height: 2px;
    background: #fff;
}

.account-header::before {
    left: 30px;
}

.account-header::after {
    right: 30px;
}

.account-header h2,
.account-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

    .account-body {
        padding: 40px;
    }
    
    /* FORM STYLES */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2b2b2b;
        font-size: 14px;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #2fc7b4;
        box-shadow: 0 0 0 3px rgba(47, 199, 180, 0.1);
    }
    
    .form-group input:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .form-group small {
        color: #666;
        font-size: 12px;
        display: block;
        margin-top: 5px;
    }

.account-btn {
    width: 100%;
    padding: 15px;
    background: #2b2b2b;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    box-sizing: border-box;
}

.account-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* PROFILE SPECIFIC STYLES */
.profile-details {
    padding: 0;
}

.profile-detail-row {
    display: flex;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.profile-detail-row:last-child {
    border-bottom: none;
}

.profile-detail-row:hover {
    background: rgba(47, 167, 107, 0.05);
    padding-left: 10px;
    border-radius: 8px;
}

.profile-detail-label {
    flex: 0 0 200px;
    font-weight: 600;
    color: #2b2b2b;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
}

.profile-detail-label i {
    color: #2fa76b;
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.profile-detail-value {
    flex: 1;
    color: #666;
    font-size: 15px;
    word-break: break-word;
}

.profile-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #e0e0e0;
}

.profile-actions .account-btn {
    flex: 1;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.profile-actions .account-btn i {
    font-size: 16px;
}

@media (max-width: 768px) {
    .profile-detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .profile-detail-label {
        flex: 1;
        width: 100%;
    }
    
    .profile-detail-value {
        flex: 1;
        width: 100%;
    }
    
    .profile-actions {
        flex-direction: column;
    }
    
    .account-body {
        padding: 30px 20px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Toggle between view and edit mode
function toggleEditMode() {
    const viewMode = document.getElementById('profileViewMode');
    const editMode = document.getElementById('profileEditMode');
    
    if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
    } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    }
}

// Update profile
function updateProfile(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('update-profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(data => {
        // Always re-enable button after response
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🎉 Success!',
                    html: '<div style="font-size: 18px; color: #2fa76b; font-weight: 600;">Profile updated successfully! ✨</div><div style="margin-top: 10px; font-size: 14px; color: #777;">Your changes have been saved.</div>',
                    icon: 'success',
                    confirmButtonText: 'Awesome! 🚀',
                    confirmButtonColor: '#2fa76b',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            } else {
                alert('Profile updated successfully!');
            }
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Show error message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '❌ Error!',
                    html: '<div style="font-size: 16px; color: #dc3545;">' + (data.message || 'Failed to update profile') + '</div>',
                    icon: 'error',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#dc3545'
                });
            } else {
                alert(data.message || 'Failed to update profile');
            }
        }
    })
    .catch(error => {
        console.error('Update profile error:', error);
        // Always re-enable button on error
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '❌ Error!',
                html: '<div style="font-size: 16px; color: #dc3545;">Something went wrong. Please try again.</div>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        } else {
            alert('Something went wrong. Please try again.');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
