<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle updating status
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['callback_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $note = mysqli_real_escape_string($conn, $_POST['admin_note']);
    
    $update_query = "UPDATE callback_requests SET status = '$status', admin_note = '$note' WHERE id = $id";
    if (mysqli_query($conn, $update_query)) {
        $success_msg = "Callback request updated successfully!";
    } else {
        $error_msg = "Error updating callback request.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM callback_requests WHERE id = $id")) {
        header("Location: manage_callbacks.php?deleted=1");
        exit();
    }
}

// Filter Logic
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "";
if ($filter !== 'all') {
    $where_clause = "WHERE status = '$filter'";
}

// Fetch all callbacks with filter
$query = "SELECT * FROM callback_requests $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Counts for the toggle badges
$total_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM callback_requests"));
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM callback_requests WHERE status = 'Pending'"));
$processing_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM callback_requests WHERE status = 'Processing'"));
$done_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM callback_requests WHERE status = 'Done'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Callback Requests - Craft Royale</title>
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

        .analytics-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            border-color: var(--primary);
        }

        .stat-val {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .portal-header-luxe {
            background: var(--secondary);
            padding: 20px 30px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            color: white;
        }

        .filter-switch {
            display: flex;
            background: rgba(255,255,255,0.05);
            padding: 6px;
            border-radius: 100px;
            gap: 5px;
        }

        .filter-link-luxe {
            padding: 10px 20px;
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
        }

        .callback-feed {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .callback-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 25px;
            display: grid;
            grid-template-columns: 250px 180px 1fr 200px 60px;
            gap: 25px;
            align-items: center;
            box-shadow: var(--shadow-luxe);
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-pending { background: #fee2e2; color: #991b1b; }
        .status-processing { background: #fef3c7; color: #92400e; }
        .status-done { background: #dcfce7; color: #166534; }

        .form-select-luxe {
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            font-family: inherit;
            font-weight: 600;
            outline: none;
            width: 100%;
        }

        .form-select-luxe:focus {
            border-color: var(--primary);
        }

        .note-input {
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            font-family: inherit;
            width: 100%;
            outline: none;
        }

        .note-input:focus {
            border-color: var(--primary);
        }

        .update-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }

        .del-btn {
            width: 45px;
            height: 45px;
            background: #fff1f2;
            color: #f43f5e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: 1px solid #ffe4e6;
        }

        .del-btn:hover {
            background: #f43f5e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Callback Requests</h1>
                    <p class="welcome-text">Manage customer callback requests efficiently.</p>
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
                    <div class="alert" style="background: #d1fae5; color: #065f46; padding: 18px 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #10b981;">
                        <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert" style="background: #fee2e2; color: #991b1b; padding: 18px 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #ef4444;">
                        <i class="fas fa-trash-alt"></i> Record deleted successfully.
                    </div>
                <?php endif; ?>

                <div class="analytics-strip">
                    <div class="stat-card-luxe" style="--primary: #2fc7b4;">
                        <span class="stat-val"><?php echo $total_count; ?></span>
                        <span class="stat-label">Total Requests</span>
                    </div>
                    <div class="stat-card-luxe" style="--primary: #f43f5e;">
                        <span class="stat-val"><?php echo $pending_count; ?></span>
                        <span class="stat-label">Pending</span>
                    </div>
                    <div class="stat-card-luxe" style="--primary: #f59e0b;">
                        <span class="stat-val"><?php echo $processing_count; ?></span>
                        <span class="stat-label">Processing</span>
                    </div>
                    <div class="stat-card-luxe" style="--primary: #10b981;">
                        <span class="stat-val"><?php echo $done_count; ?></span>
                        <span class="stat-label">Done</span>
                    </div>
                </div>

                <div class="portal-header-luxe">
                    <div class="portal-title">
                        <h3><i class="fas fa-phone-alt"></i> Callback Feed</h3>
                    </div>
                    <div class="filter-switch">
                        <a href="?status=all" class="filter-link-luxe <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?status=Pending" class="filter-link-luxe <?php echo $filter === 'Pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="?status=Processing" class="filter-link-luxe <?php echo $filter === 'Processing' ? 'active' : ''; ?>">Processing</a>
                        <a href="?status=Done" class="filter-link-luxe <?php echo $filter === 'Done' ? 'active' : ''; ?>">Done</a>
                    </div>
                </div>

                <div class="callback-feed">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="callback-card">
                                <div class="user-info">
                                    <h4 style="margin:0; font-size:1.1rem;"><?php echo htmlspecialchars($row['name']); ?></h4>
                                    <p style="margin:5px 0; color:var(--primary); font-weight:700;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></p>
                                    <small style="color:#94a3b8;"><i class="far fa-clock"></i> <?php echo date('d M, h:i A', strtotime($row['created_at'])); ?></small>
                                </div>

                                <div>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </div>

                                <form action="manage_callbacks.php" method="POST" style="display:contents;">
                                    <input type="hidden" name="callback_id" value="<?php echo $row['id']; ?>">
                                    <div>
                                        <input type="text" name="admin_note" class="note-input" placeholder="Admin note..." value="<?php echo htmlspecialchars($row['admin_note']); ?>">
                                    </div>
                                    <div>
                                        <select name="status" class="form-select-luxe" style="margin-bottom:10px;">
                                            <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $row['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Done" <?php echo $row['status'] == 'Done' ? 'selected' : ''; ?>>Done</option>
                                        </select>
                                        <button type="submit" name="update_status" class="update-btn" style="width:100%;">Update</button>
                                    </div>
                                </form>

                                <div style="display:flex; justify-content:center;">
                                    <a href="?delete=<?php echo $row['id']; ?>" class="del-btn" onclick="return confirm('Delete this request?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 50px; background:white; border-radius:24px; border:2px dashed #e2e8f0;">
                            <i class="fas fa-phone-slash fa-3x" style="color:#cbd5e1; margin-bottom:15px;"></i>
                            <h3 style="color:#64748b;">No callback requests found</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
