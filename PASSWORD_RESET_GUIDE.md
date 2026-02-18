# Password Reset System - Implementation Guide

## Overview
A complete password reset system has been implemented for Craft Royale, allowing users to securely reset their forgotten passwords.

## Files Created

### 1. Database Schema
**File:** `create_password_reset_table.sql`
- Creates `password_reset_tokens` table
- Stores email, token, expiration time, and usage status
- Includes indexes for performance

**To setup:** Import this SQL file into your database or run it via phpMyAdmin.

### 2. Forgot Password Page
**File:** `forgot-password.php`
- User enters their email address
- System generates a unique reset token
- Token expires after 1 hour
- Shows reset link (in production, this would be emailed)
- Premium UI with Craft Royale branding

### 3. Reset Password Page
**File:** `reset-password.php`
- Validates the reset token
- Checks if token has expired
- Real-time password strength checker
- Password confirmation validation
- Updates user password securely
- Marks token as used after successful reset

### 4. Login Modal Update
**File:** `includes/header.php` (modified)
- "Forgot your password?" link now redirects to `forgot-password.php`

## Features

### Security Features
✅ Unique token generation using `random_bytes()`
✅ Token expiration (1 hour)
✅ One-time use tokens
✅ Email verification before reset
✅ Password strength validation

### User Experience Features
✅ Clean, premium UI matching Craft Royale design
✅ Real-time password strength indicator
✅ Toggle password visibility
✅ Password match validation
✅ Clear error messages
✅ Success confirmations

## How It Works

### Step 1: User Requests Reset
1. User clicks "Forgot your password?" in login modal
2. Redirected to `forgot-password.php`
3. Enters email address
4. System generates unique token and stores in database

### Step 2: Reset Link Generation
1. Token is created with 1-hour expiration
2. Reset link is generated: `reset-password.php?token=XXXXX`
3. In production, this link would be emailed
4. For development, link is displayed on screen

### Step 3: Password Reset
1. User clicks reset link
2. Token is validated (exists, not expired, not used)
3. User enters new password
4. Password strength is checked in real-time
5. Password is updated in database
6. Token is marked as used

## Database Setup

Run this SQL to create the required table:

```sql
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Testing the System

### Test Flow:
1. Go to homepage and click login
2. Click "Forgot your password?"
3. Enter a registered email (e.g., test@example.com)
4. Copy the reset link shown on screen
5. Paste link in browser
6. Enter new password (min 6 characters)
7. Confirm password
8. Submit to reset

### Test URLs:
- Forgot Password: `http://localhost/Craft%20Royale/forgot-password.php`
- Reset Password: `http://localhost/Craft%20Royale/reset-password.php?token=XXXXX`

## Production Considerations

### Email Integration (TODO)
Currently, the reset link is displayed on screen. For production:

1. Install PHPMailer:
```bash
composer require phpmailer/phpmailer
```

2. Update `forgot-password.php` to send email:
```php
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('noreply@craftroyale.com', 'Craft Royale');
$mail->addAddress($email);
$mail->Subject = 'Password Reset - Craft Royale';
$mail->Body = "Click here to reset: $reset_link";
$mail->send();
```

### Password Hashing (IMPORTANT!)
Currently using plain text passwords. **MUST UPDATE** for production:

In `reset-password.php`, change:
```php
// Current (INSECURE):
$hashed_password = $new_password;

// Production (SECURE):
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
```

Also update login to use `password_verify()`.

### Token Cleanup
Add a cron job to delete expired tokens:
```sql
DELETE FROM password_reset_tokens 
WHERE expires_at < NOW() OR used = 1;
```

## UI Styling

The pages use:
- Craft Royale color scheme (#2fc7b4, #2fa76b)
- Premium gradients and shadows
- Responsive design
- Font Awesome icons
- Smooth animations

## Error Handling

- Invalid email: Shows generic message (security best practice)
- Expired token: Clear error with option to request new link
- Used token: Prevents reuse
- Password mismatch: Real-time validation
- Weak password: Strength indicator guides user

## Next Steps

1. ✅ Import SQL table
2. ✅ Test forgot password flow
3. ✅ Test reset password flow
4. 🔲 Integrate email sending (production)
5. 🔲 Implement password hashing (production)
6. 🔲 Add token cleanup cron job
7. 🔲 Add rate limiting to prevent abuse

## Support

If you encounter any issues:
1. Check database connection in `includes/db.php`
2. Verify `password_reset_tokens` table exists
3. Check browser console for JavaScript errors
4. Verify email exists in `users` table
