<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$edit_mode = false;
$tutorial = null;

// Check if editing
if (isset($_GET['id'])) {
    $edit_mode = true;
    $tutorial_id = mysqli_real_escape_string($conn, $_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM tutorials WHERE id = '$tutorial_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $tutorial = mysqli_fetch_assoc($result);
    } else {
        header("Location: view_tutorials.php?error=" . urlencode("Tutorial not found"));
        exit();
    }
}

// Handle form submission
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle video upload
    $video = $tutorial['video'] ?? '';
    if (!empty($_FILES['video']['name'])) {
        $upload_dir = "../uploads/tutorials/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_extensions = ['mp4', 'webm', 'ogg', 'mov'];
        $file_extension = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            header("Location: add_tutorial.php?error=" . urlencode("Invalid video format. Allowed: mp4, webm, ogg, mov"));
            exit();
        }
        
        $video = time() . '_' . $_FILES['video']['name'];
        move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir . $video);
        
        // Delete old video if editing
        if ($edit_mode && !empty($tutorial['video']) && file_exists("../uploads/tutorials/" . $tutorial['video'])) {
            unlink("../uploads/tutorials/" . $tutorial['video']);
        }
    }

    if ($edit_mode) {
        // Update existing tutorial
        $update_query = "UPDATE tutorials SET 
                        title = '$title',
                        status = '$status'";
        
        if (!empty($video)) {
            $update_query .= ", video = '$video'";
        }
        
        $update_query .= " WHERE id = " . $tutorial['id'];
        
        $result = mysqli_query($conn, $update_query);
        if ($result) {
            header("Location: view_tutorials.php?success=updated");
        } else {
            header("Location: add_tutorial.php?id=" . $tutorial['id'] . "&error=" . urlencode(mysqli_error($conn)));
        }
    } else {
        // Insert new tutorial
        if (empty($video)) {
            header("Location: add_tutorial.php?error=" . urlencode("Please upload a video"));
            exit();
        }
        
        $insert_query = "INSERT INTO tutorials (title, video, status) 
                        VALUES ('$title', '$video', '$status')";
        
        $result = mysqli_query($conn, $insert_query);
        if ($result) {
            header("Location: view_tutorials.php?success=added");
        } else {
            header("Location: add_tutorial.php?error=" . urlencode(mysqli_error($conn)));
        }
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Tutorial - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .form-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 40px;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(47, 199, 180, 0.1);
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .description-box {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 30px;
            border: 2px dashed #e2e8f0;
            border-radius: 20px;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #f1f5f9;
            border-color: #8B5CF6;
        }

        .file-upload-label i {
            font-size: 40px;
            color: #8B5CF6;
            margin-bottom: 15px;
        }

        .video-preview {
            margin-top: 15px;
            display: block;
        }

        .video-preview video {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #fff;
            padding: 16px 36px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 30px;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            margin-left: 15px;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }

        .cancel-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .file-info {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?= $edit_mode ? 'Refine' : 'Create' ?> Tutorial</h1>
                    <p class="welcome-text"><?= $edit_mode ? 'Polishing your premium educational content' : 'Start sharing your expertise with a new video' ?></p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-header">
                        <h2><?= $edit_mode ? 'Edit Tutorial' : 'Add New Tutorial' ?></h2>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?= $edit_mode ? htmlspecialchars($tutorial['title']) : '' ?>"
                                   placeholder="Enter tutorial title">
                        </div>

                        <div class="form-group">
                            <label for="video">Video *</label>
                            <div class="file-upload">
                                <input type="file" id="video" name="video" accept="video/*" onchange="previewVideo(this)">
                                <label for="video" class="file-upload-label">
                                    <i class="fas fa-video"></i>
                                    <span>Click to upload video or drag and drop</span>
                                </label>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-info-circle"></i> Supported formats: MP4, WebM, OGG, MOV (Max size: 100MB)
                            </div>
                            <?php if ($edit_mode && !empty($tutorial['video'])): ?>
                                <div class="video-preview">
                                    <video controls id="currentVideo">
                                        <source src="../uploads/tutorials/<?= htmlspecialchars($tutorial['video']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <p style="margin-top: 10px; font-size: 12px; color: #666;">
                                        Current video: <?= htmlspecialchars($tutorial['video']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <div class="video-preview" id="preview" style="display: none;">
                                <video controls id="previewVideo"></video>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?= ($edit_mode && $tutorial['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($edit_mode && $tutorial['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <button type="submit" name="submit" class="submit-btn">
                            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Publish' ?> Tutorial
                        </button>
                        <a href="dashboard.php" class="cancel-btn">
                            <i class="fas fa-th-large"></i> Back to Dashboard
                        </a>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewVideo(input) {
            const preview = document.getElementById('preview');
            const previewVideo = document.getElementById('previewVideo');
            const currentVideo = document.getElementById('currentVideo');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 100 * 1024 * 1024; // 100MB
                
                if (file.size > maxSize) {
                    alert('Video file is too large. Maximum size is 100MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewVideo.src = e.target.result;
                    preview.style.display = 'block';
                    if (currentVideo) {
                        currentVideo.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                if (currentVideo) {
                    currentVideo.style.display = 'block';
                }
            }
        }
    </script>
</body>
</html>






