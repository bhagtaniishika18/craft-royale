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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    // Check if email exists
    $query = "SELECT id, first_name, last_name FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        $message = "Database error: " . mysqli_error($conn);
        $message_type = 'error';
    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $insert_query = "INSERT INTO password_reset_tokens (email, token, expires_at) 
                        VALUES ('$email', '$token', '$expires_at')";
        
        if (mysqli_query($conn, $insert_query)) {
            // Create reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Craft%20Royale/reset-password.php?token=" . $token;
            
            // In production, send email here
            // For now, we'll show the link (for development)
            $message = "Password reset link has been generated. Please click the link below to reset your password:<br><br>
                       <a href='$reset_link' style='color: #2fc7b4; font-weight: bold; text-decoration: underline;'>Click here to reset your password</a><br><br>
                       <small style='color: #999;'>This link will expire in 1 hour.</small>";
            $message_type = 'success';
            
            // TODO: Send email using PHPMailer or similar
            // mail($email, "Password Reset - Craft Royale", $email_body, $headers);
        } else {
            $message = "Error creating reset token: " . mysqli_error($conn);
            $message_type = 'error';
        }
    } else {
        // Don't reveal if email exists or not (security best practice)
        $message = "If an account with that email exists, you will receive a password reset link shortly.";
        $message_type = 'info';
    }
}

include 'includes/header.php';
?>

<style>
    .forgot-password-container {
        max-width: 500px;
        margin: 100px auto;
        padding: 40px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .forgot-password-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .forgot-password-header i {
        font-size: 60px;
        color: #2fc7b4;
        margin-bottom: 20px;
        display: block;
    }

    .forgot-password-header h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 10px;
    }

    .forgot-password-header p {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
    }

    .form-group {
        margin-bottom: 25px;
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
        padding: 15px;
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

    .message.info {
        background: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
    }

    .message i {
        margin-right: 8px;
    }
</style>

<div class="forgot-password-container">
    <div class="forgot-password-header">
        <i class="fas fa-key"></i>
        <h1>Forgot Password?</h1>
        <p>No worries! Enter your email address and we'll send you a link to reset your password.</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fas fa-paper-plane"></i> Send Reset Link
        </button>
    </form>

    <div class="back-to-login">
        <a href="javascript:void(0)" onclick="openLogin()">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
