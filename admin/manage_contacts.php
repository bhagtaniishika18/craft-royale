<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle adding/updating admin notes
if (isset($_POST['update_note'])) {
    $id = (int)$_POST['message_id'];
    $note = mysqli_real_escape_string($conn, $_POST['admin_note']);
    
    $update_query = "UPDATE contact_messages SET admin_note = '$note' WHERE id = $id";
    if (mysqli_query($conn, $update_query)) {
        $success_msg = "Note updated successfully!";
    } else {
        $error_msg = "Error updating note.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM contact_messages WHERE id = $id")) {
        header("Location: manage_contacts.php?deleted=1");
        exit();
    }
}

// Filter Logic
$filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$where_clause = "";
if ($filter === 'quick') {
    $where_clause = "WHERE subject = 'Quick Email Query Popup'";
} elseif ($filter === 'general') {
    $where_clause = "WHERE subject != 'Quick Email Query Popup'";
}

// Fetch all messages with filter
$query = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Counts for the toggle badges
$total_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM contact_messages"));
$quick_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM contact_messages WHERE subject = 'Quick Email Query Popup'"));
$general_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM contact_messages WHERE subject != 'Quick Email Query Popup'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Messages - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        :root {
            --primary: #2fc7b4;
            --secondary: #0f172a;
            --accent: #7c4dff;
            --glass: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.4);
            --shadow-luxe: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.03);
            --radius-xl: 30px;
            --radius-lg: 20px;
        }

        body {
            background: #f0f4f8;
            background-image: radial-gradient(at 0% 0%, rgba(47, 199, 180, 0.05) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(124, 77, 255, 0.05) 0px, transparent 50%);
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
            margin: 0;
            min-height: 100vh;
        }

        .dashboard-content {
            padding: 25px 40px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Analytics HUD - Majestic Cards */
        .analytics-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card-luxe {
            background: var(--glass);
            backdrop-filter: blur(15px);
            padding: 25px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-luxe);
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card-luxe:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .stat-card-luxe::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: var(--primary);
            opacity: 0.03;
            border-radius: 50%;
            transform: translate(30%, 30%);
        }

        .stat-val {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1;
            letter-spacing: -1.5px;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Static Portal Header - Fluid Flow */
        .portal-header-luxe {
            background: var(--secondary);
            padding: 20px 30px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
            color: white;
        }

        .portal-title h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-switch {
            display: flex;
            background: rgba(255,255,255,0.05);
            padding: 6px;
            border-radius: 100px;
            gap: 5px;
        }

        .filter-link-luxe {
            padding: 10px 25px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            transition: all 0.3s ease;
        }

        .filter-link-luxe.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(47, 199, 180, 0.3);
        }

        .filter-link-luxe:not(.active):hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        /* Inquiry Feed - Floating HD Cards */
        .inquiry-feed {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .inquiry-card-highdef {
            background: white;
            border-radius: var(--radius-lg);
            padding: 25px 30px;
            display: grid;
            grid-template-columns: 280px 1fr 340px 60px;
            gap: 30px;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: var(--shadow-luxe);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .inquiry-card-highdef:hover {
            box-shadow: 0 30px 60px rgba(0,0,0,0.08);
            transform: scale(1.005);
        }

        .user-profile-luxe {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .avatar-ring {
            width: 55px;
            height: 55px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2fc7b4, #0f172a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(47, 199, 180, 0.2);
        }

        .user-details h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--secondary);
        }

        .user-details p {
            margin: 5px 0 0;
            font-size: 0.9rem;
            color: var(--primary);
            font-weight: 700;
        }

        .message-content-luxe {
            background: #f8fafc;
            padding: 20px 25px;
            border-radius: var(--radius-lg);
            border-left: 5px solid var(--primary);
        }

        .msg-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 1.5px;
            color: #94a3b8;
            margin-bottom: 12px;
            display: block;
        }

        .msg-text {
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .response-zone {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .response-input-wrapper {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 10px;
            display: flex;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .response-input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(47, 199, 180, 0.1);
        }

        .resp-field {
            flex: 1;
            border: none;
            padding: 12px;
            font-weight: 600;
            outline: none;
            color: var(--secondary);
            font-family: inherit;
        }

        .resp-send-btn {
            width: 42px;
            height: 42px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .resp-send-btn:hover {
            transform: scale(1.1) rotate(-10deg);
            box-shadow: 0 10px 20px rgba(47, 199, 180, 0.3);
        }

        .del-circle-btn {
            width: 45px;
            height: 45px;
            background: #fff1f2;
            color: #f43f5e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            border: 1px solid #ffe4e6;
        }

        .del-circle-btn:hover {
            background: #f43f5e;
            color: white;
            transform: scale(1.1);
        }

        .status-tag {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 20px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            z-index: 10;
        }

        .tag-new { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .tag-replied { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        @media (max-width: 1500px) {
            .inquiry-card-highdef { grid-template-columns: 1fr 1fr; }
            .status-tag { top: -15px; }
        }
        @media (max-width: 800px) {
            .inquiry-card-highdef { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Contact Messages</h1>
                    <p class="welcome-text">Review and respond to customer queries seamlessly.</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if (isset($success_msg)): ?>
                    <div class="alert" style="background: #d1fae5; color: #065f46; padding: 18px 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #10b981; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <strong>Success!</strong> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert" style="background: #fee2e2; color: #991b1b; padding: 18px 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #ef4444; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-trash-alt" style="font-size: 1.2rem;"></i> <strong>Deleted!</strong> Message has been removed from records.
                    </div>
                <?php endif; ?>

                <!-- Dashboard HUD -->
                <div class="analytics-strip">
                    <div class="stat-card-luxe" style="--primary: #2fc7b4;">
                        <span class="stat-val"><?php echo $total_count; ?></span>
                        <span class="stat-label">Total Inquiries</span>
                    </div>
                    <div class="stat-card-luxe" style="--primary: #7c4dff;">
                        <span class="stat-val"><?php echo $quick_count; ?></span>
                        <span class="stat-label">Quick Popups</span>
                    </div>
                    <div class="stat-card-luxe" style="--primary: #f59e0b;">
                        <span class="stat-val"><?php echo $general_count; ?></span>
                        <span class="stat-label">General Contact</span>
                    </div>
                </div>

                <!-- Majestic Portal Header -->
                <div class="portal-header-luxe">
                    <div class="portal-title">
                        <h3><i class="fas fa-inbox" style="color:var(--primary);"></i> Inbound Queries</h3>
                    </div>
                    <div class="filter-switch">
                        <a href="?type=all" class="filter-link-luxe <?php echo $filter === 'all' ? 'active' : ''; ?>">All Messages</a>
                        <a href="?type=quick" class="filter-link-luxe <?php echo $filter === 'quick' ? 'active' : ''; ?>">Quick Popups</a>
                        <a href="?type=general" class="filter-link-luxe <?php echo $filter === 'general' ? 'active' : ''; ?>">General</a>
                    </div>
                </div>

                <div class="inquiry-feed">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="inquiry-card-highdef">
                                <?php if ($row['admin_note']): ?>
                                    <span class="status-tag tag-replied"><i class="fas fa-check-circle"></i> Replied</span>
                                <?php else: ?>
                                    <span class="status-tag tag-new"><i class="fas fa-star"></i> New Inquiry</span>
                                <?php endif; ?>

                                <!-- User Info -->
                                <div class="user-profile-luxe">
                                    <div class="avatar-ring">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <div class="user-details">
                                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($row['email']); ?></p>
                                        <div style="font-size:0.75rem; color:#94a3b8; font-weight:700; margin-top:8px;">
                                            <i class="far fa-clock"></i> <?php echo date('d M, h:i A', strtotime($row['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inquiry Text -->
                                <div class="message-content-luxe">
                                    <span class="msg-heading"><?php echo htmlspecialchars($row['subject']); ?></span>
                                    <div class="msg-text">“<?php echo htmlspecialchars($row['message']); ?>”</div>
                                </div>

                                <!-- Response Zone -->
                                <div class="response-zone">
                                    <?php if ($row['admin_note']): ?>
                                        <div style="font-size:0.7rem; color:#64748b; margin-bottom:5px;">
                                            <strong>Last Response:</strong> <?php echo htmlspecialchars($row['admin_note']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <form action="manage_contacts.php" method="POST" class="response-input-wrapper">
                                        <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="admin_note" class="resp-field" placeholder="Reply to customer..." value="<?php echo htmlspecialchars($row['admin_note']); ?>">
                                        <button type="submit" name="update_note" class="resp-send-btn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Actions -->
                                <div style="display:flex; justify-content:center;">
                                    <a href="?delete=<?php echo $row['id']; ?>" class="del-circle-btn" onclick="return confirm('Delete this record?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 100px 0; background:white; border-radius:24px; border:2px dashed #e2e8f0;">
                            <i class="fas fa-comment-slash fa-4x" style="color:#cbd5e1; margin-bottom:20px;"></i>
                            <h2 style="color:#64748b; font-weight:800;">No Messages Found</h2>
                            <p style="color:#94a3b8;">Your inbox is as clean as crystal.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </main>
    </div>
</body>
</html>
