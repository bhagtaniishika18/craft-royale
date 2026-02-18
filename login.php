<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<style>
    /* PAGE BACKGROUND & LAYOUT */
    .login-page-wrapper {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #f8fdfc 0%, #e6f7f2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }

    /* Cinematic Decorative Elements */
    .login-page-wrapper::before,
    .login-page-wrapper::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(47, 199, 180, 0.1), rgba(47, 167, 107, 0.1));
        z-index: 0;
    }

    .login-page-wrapper::before {
        top: -100px;
        right: -100px;
    }

    .login-page-wrapper::after {
        bottom: -100px;
        left: -100px;
    }

    /* LOGIN CARD */
    .login-container {
        width: 100%;
        max-width: 450px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        padding: 45px;
        position: relative;
        z-index: 10;
        border: 1px solid rgba(255, 255, 255, 0.5);
        animation: cardFadeUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    @keyframes cardFadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .login-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .login-header p {
        color: #666;
        font-size: 15px;
    }

    /* FORM STYLING */
    .login-form-group {
        margin-bottom: 22px;
        position: relative;
    }

    .login-form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #444;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .login-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .login-input-wrapper i {
        position: absolute;
        left: 16px;
        color: #999;
        font-size: 16px;
        transition: color 0.3s ease;
    }

    .login-input-wrapper input {
        width: 100%;
        padding: 14px 16px 14px 46px;
        background: #fdfdfd;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        font-size: 15px;
        color: #1a1a1a;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .login-input-wrapper input:focus {
        background: #fff;
        border-color: #2fc7b4;
        box-shadow: 0 0 0 4px rgba(47, 199, 180, 0.1);
    }

    .login-input-wrapper input:focus + i {
        color: #2fc7b4;
    }

    /* ACTIONS */
    .login-form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        font-size: 14px;
    }

    .login-forgot {
        color: #2fc7b4;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .login-forgot:hover {
        color: #2fa76b;
        text-decoration: underline;
    }

    .login-submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #2fc7b4 0%, #2fa76b 100%);
        color: #fff;
        border: none;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 8px 25px rgba(47, 199, 180, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    .login-submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(47, 199, 180, 0.4);
    }

    .login-submit-btn:active {
        transform: translateY(0);
    }

    .login-submit-btn:disabled {
        opacity: 0.8;
        cursor: not-allowed;
        transform: none;
    }

    /* FOOTER OF CARD */
    .login-card-footer {
        text-align: center;
        padding-top: 25px;
        border-top: 1px solid #eee;
        font-size: 14px;
        color: #666;
    }

    .login-card-footer a {
        color: #2fc7b4;
        text-decoration: none;
        font-weight: 700;
        margin-left: 5px;
    }

    .login-card-footer a:hover {
        text-decoration: underline;
    }

    /* Responsive Adjustments */
    @media (max-width: 480px) {
        .login-container {
            padding: 30px 25px;
        }
        .login-header h1 {
            font-size: 26px;
        }
    }
</style>

<div class="login-page-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please enter your details to sign in.</p>
        </div>

        <form id="standaloneLoginForm" onsubmit="handleStandaloneLogin(event); return false;">
            <div class="login-form-group">
                <label>Email Address</label>
                <div class="login-input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="name@example.com" required autocomplete="email">
                </div>
            </div>

            <div class="login-form-group">
                <label>Password</label>
                <div class="login-input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <div class="login-form-options">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" style="width:16px; height:16px; cursor:pointer;">
                    <span style="color: #666; font-weight: 500;">Remember me</span>
                </label>
                <a href="#" class="login-forgot" onclick="event.preventDefault(); showToast('Coming Soon', 'Password recovery will be available shortly! 🛠️', 'info');">Forgot password?</a>
            </div>

            <button type="submit" class="login-submit-btn">
                <span>Sign In</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="login-card-footer">
            Don't have an account? <a href="#" onclick="event.preventDefault(); if(typeof openRegister === 'function') openRegister(); else showToast('Coming Soon', 'Registration is available via the homepage! ✨', 'info');">Create account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Show beautiful toast notification (duplicated for standalone page reliability)
function showToast(title, message, type = 'success') {
    const existingToast = document.querySelector('.auth-toast');
    if (existingToast) {
        existingToast.classList.remove('show');
        setTimeout(() => { existingToast.remove(); }, 300);
    }
    
    const toast = document.createElement('div');
    toast.className = `auth-toast ${type === 'error' ? 'error' : (type === 'info' ? 'info' : '')}`;
    
    const icon = type === 'error' ? '✕' : (type === 'info' ? 'ℹ' : '✓');
    
    toast.innerHTML = `
        <div class="auth-toast-icon-wrapper">
            <div class="auth-toast-icon">${icon}</div>
        </div>
        <div class="auth-toast-content">
            <div class="auth-toast-title">${title}</div>
            <div class="auth-toast-message">${message}</div>
        </div>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('show'); }, 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.remove(); }, 400);
    }, 4000);
}

// Provide global access to register modal for the footer link
window.openRegister = function() {
    const regModal = document.getElementById('registerModal');
    if (regModal) {
        regModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        showToast('Registration', 'Please use the registration tab on the home page! ✨', 'info');
    }
};

// Handle standalone login form
function handleStandaloneLogin(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Verifying...</span> <i class="fas fa-circle-notch fa-spin"></i>';
    
    fetch('login-handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Login Successful', `Welcome back, ${data.user_name}! ✨`, 'success');
            setTimeout(() => {
                window.location.href = 'index.php?login=success';
            }, 1000);
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            showToast('Login Failed', data.message || 'Invalid email or password', 'error');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showToast('Error', 'Something went wrong. Please try again.', 'error');
    });
}
</script>

