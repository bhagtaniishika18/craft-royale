<?php
include 'includes/db.php';
include 'includes/header.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user_email']; // Assuming session has email, if not we fetch from user_id

// Fetch user's email if not in session
if (!$user_email) {
    $u_id = $_SESSION['user_id'];
    $u_res = mysqli_query($conn, "SELECT email FROM users WHERE id = $u_id");
    $u_data = mysqli_fetch_assoc($u_res);
    $user_email = $u_data['email'];
}

// Fetch all messages from this user (using email as common identifier)
$query = "SELECT * FROM contact_messages WHERE email = '$user_email' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<style>
    .queries-container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 0 20px;
    }
    .queries-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .queries-header h2 {
        font-size: 2rem;
        color: #333;
    }
    .back-btn {
        padding: 10px 20px;
        background: #f4f4f4;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .back-btn:hover {
        background: #eee;
    }
    .query-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 6px solid #2fc7b4;
    }
    .query-date {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 15px;
    }
    .query-subject {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #2b3674;
    }
    .query-message {
        color: #555;
        line-height: 1.6;
        margin-bottom: 20px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }
    .response-section {
        background: #f0fbf9;
        padding: 20px;
        border-radius: 10px;
        border: 1px dashed #2fc7b4;
    }
    .response-header {
        color: #2fc7b4;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .response-text {
        color: #333;
        font-style: italic;
        line-height: 1.5;
    }
    .no-response {
        color: #999;
        font-size: 0.9rem;
    }
    .empty-state {
        text-align: center;
        padding: 60px 0;
        color: #aaa;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
    }
</style>

<div class="queries-container">
    <div class="queries-header">
        <h2>My Queries & Responses</h2>
        <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="query-card">
                <div class="query-date">
                    <i class="far fa-calendar-alt"></i> Posted on <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                </div>
                <div class="query-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                <div class="query-message">
                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                </div>

                <div class="response-section">
                    <?php if ($row['admin_note']): ?>
                        <div class="response-header"><i class="fas fa-reply"></i> Official Response</div>
                        <div class="response-text"><?php echo nl2br(htmlspecialchars($row['admin_note'])); ?></div>
                    <?php else: ?>
                        <div class="no-response"><i class="fas fa-clock"></i> Our team is reviewing this. We will get back to you soon.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-comment-slash"></i>
            <h3>You haven't submitted any queries yet.</h3>
            <p>If you have any questions, visit our <a href="contact.php" style="color: #2fc7b4;">Contact Page</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
