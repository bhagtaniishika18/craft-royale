<?php
include 'includes/db.php';
include 'includes/header.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$u_res = mysqli_query($conn, "SELECT mobile_no FROM users WHERE id = $user_id");
$u_data = mysqli_fetch_assoc($u_res);
$user_phone = $u_data['mobile_no'];

// Fetch all callback requests from this user (using user_id or phone as identifier)
$query = "SELECT * FROM callback_requests WHERE user_id = '$user_id' OR phone = '$user_phone' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    // If table structure is old, this query might fail. Tell user to run setup.
    $db_error = true;
} else {
    $db_error = false;
}
?>

<style>
    .callbacks-container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 0 20px;
        min-height: 60vh;
    }
    .callbacks-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .callbacks-header h2 {
        font-size: 2rem;
        color: #333;
        font-weight: 800;
    }
    .back-btn {
        padding: 10px 20px;
        background: #f4f4f4;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .back-btn:hover {
        background: #eee;
    }
    .callback-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 6px solid #2fc7b4;
        position: relative;
    }
    .callback-status-tag {
        position: absolute;
        top: 25px;
        right: 25px;
        padding: 6px 15px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .status-pending { background: #fee2e2; color: #991b1b; }
    .status-processing { background: #fef3c7; color: #92400e; }
    .status-done { background: #dcfce7; color: #166534; }

    .callback-date {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 10px;
    }
    .callback-details {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #2b3674;
    }
    .callback-note {
        background: #f0fbf9;
        padding: 15px;
        border-radius: 10px;
        border: 1px dashed #2fc7b4;
    }
    .note-header {
        color: #2fc7b4;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .note-text {
        color: #333;
        font-style: italic;
        font-size: 0.95rem;
    }
    .empty-state {
        text-align: center;
        padding: 80px 0;
        color: #aaa;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
    }
</style>

<div class="callbacks-container">
    <div class="callbacks-header">
        <h2>My Callback Status</h2>
        <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    </div>

    <!-- Debug info for user to understand matching -->
    <div style="background: #f8fafc; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #e2e8f0; color: #64748b;">
        <i class="fas fa-info-circle"></i> Showing requests linked to your account or your registered number: <strong><?php echo htmlspecialchars($user_phone); ?></strong>. 
        <br><small>If you requested a call using a different number before logging in, it might not show here.</small>
    </div>

    <?php if ($db_error): ?>
        <div class="empty-state" style="color: #dc3545;">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Database Update Required</h3>
            <p>We've upgraded our tracking system. Please click the link below to update your database:</p>
            <a href="create_callback_table.php" class="back-btn" style="background: #dc3545; color: white; display: inline-flex; margin-top: 15px;">Update Database Now</a>
        </div>
    <?php elseif (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="callback-card">
                <span class="callback-status-tag status-<?php echo strtolower($row['status']); ?>">
                    <?php echo $row['status']; ?>
                </span>
                
                <div class="callback-date">
                    <i class="far fa-calendar-alt"></i> Requested on <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                </div>
                
                <div class="callback-details">
                    <i class="fas fa-phone-alt"></i> Callback for: <?php echo htmlspecialchars($row['phone']); ?>
                </div>

                <?php if ($row['admin_note']): ?>
                    <div class="callback-note">
                        <div class="note-header"><i class="fas fa-sticky-note"></i> Admin Note</div>
                        <div class="note-text"><?php echo nl2br(htmlspecialchars($row['admin_note'])); ?></div>
                    </div>
                <?php else: ?>
                    <div style="color: #999; font-size: 0.9rem;">
                        <i class="fas fa-clock"></i> We have received your request and will call you back shortly.
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-phone-slash"></i>
            <h3>You haven't requested any callbacks yet.</h3>
            <p>Need help? Visit our <a href="contact.php" style="color: #2fc7b4;">Contact Page</a> to request a call.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
