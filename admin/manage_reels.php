<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Handle Reel Upload
if (isset($_POST['add_reel'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    if (!empty($_FILES['video']['name'])) {
        $upload_dir = "../uploads/reels/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_extensions = ['mp4', 'webm', 'ogg', 'mov'];
        $file_extension = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            $video_name = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['video']['name']);
            if (move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir . $video_name)) {
                $query = "INSERT INTO reels (title, video_path) VALUES ('$title', '$video_name')";
                if (mysqli_query($conn, $query)) {
                    $success = "Reel added successfully!";
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Failed to upload video.";
            }
        } else {
            $error = "Invalid video format.";
        }
    } else {
        $error = "Please select a video file.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $res = mysqli_query($conn, "SELECT video_path FROM reels WHERE id = '$id'");
    if ($row = mysqli_fetch_assoc($res)) {
        $file = "../uploads/reels/" . $row['video_path'];
        if (file_exists($file)) unlink($file);
        mysqli_query($conn, "DELETE FROM reels WHERE id = '$id'");
        $success = "Reel deleted successfully!";
    }
}

// Fetch all reels
$reels = mysqli_query($conn, "SELECT * FROM reels ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Royale Reels - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .reels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .reel-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(47, 199, 180, 0.1);
            transition: transform 0.3s ease;
        }
        .reel-card:hover {
            transform: translateY(-5px);
        }
        .reel-video-container {
            width: 100%;
            aspect-ratio: 9/16;
            background: #000;
            position: relative;
        }
        .reel-video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .reel-info {
            padding: 15px;
        }
        .reel-title {
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 5px;
            display: block;
        }
        .reel-date {
            font-size: 12px;
            color: #666;
        }
        .reel-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .btn-delete {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        .btn-delete:hover {
            background: #ff4757;
            color: #fff;
        }
        .upload-section {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 40px;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 20px;
            align-items: end;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #eee;
            border-radius: 8px;
        }
        .btn-add {
            background: #2fc7b4;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Royale Reels</h1>
                    <p>Manage your short vertical videos</p>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($success)): ?>
                    <div class="success-message" style="background:#d1fae5; color:#065f46; padding:15px; border-radius:8px; margin-bottom:20px; border-left:4px solid #10b981;">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="error-message" style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px; border-left:4px solid #ef4444;">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <section class="upload-section">
                    <h3>Add New Reel</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="input-group">
                                <label>Reel Title</label>
                                <input type="text" name="title" required placeholder="Ex: Crafting Magic...">
                            </div>
                            <div class="input-group">
                                <label>Video File (MP4/WebM/MOV)</label>
                                <input type="file" name="video" accept="video/*" required>
                            </div>
                            <button type="submit" name="add_reel" class="btn-add">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Reel
                            </button>
                        </div>
                    </form>
                </section>

                <h3>All Reels</h3>
                <div class="reels-grid">
                    <?php while($reel = mysqli_fetch_assoc($reels)): ?>
                        <div class="reel-card">
                            <div class="reel-video-container">
                                <video src="../uploads/reels/<?= $reel['video_path'] ?>" preload="metadata"></video>
                                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); color:#fff; font-size:40px; pointer-events:none; opacity:0.7;">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                            </div>
                            <div class="reel-info">
                                <span class="reel-title"><?= htmlspecialchars($reel['title']) ?></span>
                                <span class="reel-date"><i class="far fa-calendar-alt"></i> Uploaded: <?= date('d M, Y', strtotime($reel['created_at'])) ?></span>
                                <div class="reel-actions">
                                    <a href="?delete=<?= $reel['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this reel?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
