<?php
session_start();

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'includes/db.php';

$message = '';
$message_type = '';
$token_valid = false;
$email = '';

// Check if token is provided
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Verify token
    $query = "SELECT email, expires_at, used FROM password_reset_tokens 
              WHERE token = '$token' AND used = 0";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $token_data = mysqli_fetch_assoc($result);
        $email = $token_data['email'];
        
        // Check if token has expired
        if (strtotime($token_data['expires_at']) > time()) {
            $token_valid = true;
        } else {
            $message = "This password reset link has expired. Please request a new one.";
            $message_type = 'error';
        }
    } else {
        $message = "Invalid or already used password reset link.";
        $message_type = 'error';
    }
} else {
    header("Location: forgot-password.php");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } else {
        // Hash password using PHP's password_hash() function
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        
        if (mysqli_query($conn, $update_query)) {
            // Mark token as used
            $mark_used = "UPDATE password_reset_tokens SET used = 1 WHERE token = '$token'";
            mysqli_query($conn, $mark_used);
            
            $message = "Your password has been reset successfully! You can now login with your new password.";
            $message_type = 'success';
            $token_valid = false; // Hide form
        } else {
            $message = "Something went wrong. Please try again.";
            $message_type = 'error';
        }
    }
}

include 'includes/header.php';
?>

<style>
    .reset-password-container {
        max-width: 500px;
        margin: 100px auto;
        padding: 40px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .reset-password-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .reset-password-header i {
        font-size: 60px;
        color: #2fc7b4;
        margin-bottom: 20px;
        display: block;
    }

    .reset-password-header h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 10px;
    }

    .reset-password-header p {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 15px 45px 15px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #2fc7b4;
        box-shadow: 0 0 0 4px rgba(47, 199, 180, 0.1);
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 43px;
        cursor: pointer;
        color: #999;
        transition: color 0.3s ease;
    }

    .toggle-password:hover {
        color: #2fc7b4;
    }

    .password-strength {
        margin-top: 8px;
        font-size: 12px;
    }

    .strength-bar {
        height: 4px;
        background: #e0e0e0;
        border-radius: 2px;
        margin-top: 5px;
        overflow: hidden;
    }

    .strength-bar-fill {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .strength-weak { background: #f44336; width: 33%; }
    .strength-medium { background: #ff9800; width: 66%; }
    .strength-strong { background: #4caf50; width: 100%; }

    .submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #2fc7b4, #2fa76b);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(47, 199, 180, 0.4);
    }

    .back-to-login {
        text-align: center;
        margin-top: 20px;
    }

    .back-to-login a {
        color: #2fc7b4;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .back-to-login a:hover {
        color: #2fa76b;
    }

    .message {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 14px;
        line-height: 1.6;
    }

    .message.success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .message.error {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    .message i {
        margin-right: 8px;
    }

    .password-requirements {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .password-requirements ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }

    .password-requirements li {
        margin: 5px 0;
        color: #666;
    }

    .password-requirements li.valid {
        color: #4caf50;
    }

    .password-requirements li i {
        margin-right: 5px;
    }
</style>

<div class="reset-password-container">
    <div class="reset-password-header">
        <i class="fas fa-lock-open"></i>
        <h1>Reset Password</h1>
        <p>Create a new password for your account.</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($token_valid): ?>
        <div class="password-requirements">
            <strong>Password Requirements:</strong>
            <ul id="requirements">
                <li id="req-length"><i class="fas fa-circle"></i> At least 6 characters</li>
                <li id="req-match"><i class="fas fa-circle"></i> Passwords match</li>
            </ul>
        </div>

        <form method="POST" action="" id="resetForm">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBar"></div>
                    </div>
                    <small id="strengthText"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-check"></i> Reset Password
            </button>
        </form>
    <?php elseif ($message_type == 'success'): ?>
        <div class="back-to-login">
            <a href="javascript:void(0)" onclick="openLogin()">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        </div>
    <?php else: ?>
        <div class="back-to-login">
            <a href="forgot-password.php">
                <i class="fas fa-arrow-left"></i> Request New Reset Link
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling;
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const reqLength = document.getElementById('req-length');
    
    let strength = 0;
    
    if (password.length >= 6) {
        strength++;
        reqLength.classList.add('valid');
        reqLength.querySelector('i').className = 'fas fa-check-circle';
    } else {
        reqLength.classList.remove('valid');
        reqLength.querySelector('i').className = 'fas fa-circle';
    }
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    strengthBar.className = 'strength-bar-fill';
    
    if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Weak password';
        strengthText.style.color = '#f44336';
    } else if (strength <= 3) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = 'Medium password';
        strengthText.style.color = '#ff9800';
    } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Strong password';
        strengthText.style.color = '#4caf50';
    }
    
    checkPasswordMatch();
});

// Check password match
document.getElementById('confirm_password')?.addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    const password = document.getElementById('password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    const reqMatch = document.getElementById('req-match');
    const submitBtn = document.getElementById('submitBtn');
    
    if (confirmPassword && password === confirmPassword) {
        reqMatch.classList.add('valid');
        reqMatch.querySelector('i').className = 'fas fa-check-circle';
        submitBtn.disabled = false;
    } else {
        reqMatch.classList.remove('valid');
        reqMatch.querySelector('i').className = 'fas fa-circle';
        if (confirmPassword) {
            submitBtn.disabled = true;
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
