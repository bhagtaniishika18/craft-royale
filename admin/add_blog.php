<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$edit_mode = false;
$blog = null;

// Check if editing
if (isset($_GET['id'])) {
    $edit_mode = true;
    $blog_id = mysqli_real_escape_string($conn, $_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM blog_posts WHERE id = '$blog_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $blog = mysqli_fetch_assoc($result);
    } else {
        header("Location: view_blogs.php?error=" . urlencode("Blog post not found"));
        exit();
    }
}

// Handle form submission
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle image upload
    $image = $blog['image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../uploads/blogs/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        
        // Delete old image if editing
        if ($edit_mode && !empty($blog['image']) && file_exists("../uploads/blogs/" . $blog['image'])) {
            unlink("../uploads/blogs/" . $blog['image']);
        }
    }

    if ($edit_mode) {
        // Update existing blog post
        $update_query = "UPDATE blog_posts SET 
                        title = '$title',
                        content = '$content',
                        status = '$status'";
        
        if (!empty($image)) {
            $update_query .= ", image = '$image'";
        }
        
        $update_query .= " WHERE id = " . $blog['id'];
        
        $result = mysqli_query($conn, $update_query);
        if ($result) {
            header("Location: view_blogs.php?success=updated");
        } else {
            header("Location: add_blog.php?id=" . $blog['id'] . "&error=" . urlencode(mysqli_error($conn)));
        }
    } else {
        // Insert new blog post
        $insert_query = "INSERT INTO blog_posts (title, content, image, status) 
                        VALUES ('$title', '$content', '$image', '$status')";
        
        $result = mysqli_query($conn, $insert_query);
        if ($result) {
            header("Location: view_blogs.php?success=added");
        } else {
            header("Location: add_blog.php?error=" . urlencode(mysqli_error($conn)));
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
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Blog Post - Craft Royale</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .form-group textarea {
            min-height: 200px;
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

        .image-preview {
            margin-top: 15px;
            display: block;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
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
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><?= $edit_mode ? 'Refine' : 'Compose' ?> Article</h1>
                    <p class="welcome-text"><?= $edit_mode ? 'Elevating your brand voice with premium content' : 'Start your next masterpiece for the Craft Royale blog' ?></p>
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
                        <h2><?= $edit_mode ? 'Edit Blog Post' : 'Add New Blog Post' ?></h2>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?= $edit_mode ? htmlspecialchars($blog['title']) : '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" required><?= $edit_mode ? htmlspecialchars($blog['content']) : '' ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Image</label>
                            <div class="file-upload">
                                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <label for="image" class="file-upload-label">
                                    <i class="fas fa-image"></i>
                                    <span>Click to upload feature image or drag and drop</span>
                                </label>
                            </div>
                            <?php if ($edit_mode && !empty($blog['image'])): ?>
                                <div class="image-preview">
                                    <img src="../uploads/blogs/<?= htmlspecialchars($blog['image']) ?>" alt="Current image" id="currentImage">
                                </div>
                            <?php endif; ?>
                            <div class="image-preview" id="preview" style="display: none;">
                                <img id="previewImage" src="" alt="Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?= ($edit_mode && $blog['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($edit_mode && $blog['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <button type="submit" name="submit" class="submit-btn">
                            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Publish' ?> Article
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
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewImage = document.getElementById('previewImage');
            const currentImage = document.getElementById('currentImage');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                if (currentImage) {
                    currentImage.style.display = 'block';
                }
            }
        }
    </script>
</body>
</html>







