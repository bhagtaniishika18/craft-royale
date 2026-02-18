<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $design_name = mysqli_real_escape_string($conn, $_POST['design_name'] ?? '');
    $design_key = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '_', $_POST['design_key'] ?? '')));
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $background_color = mysqli_real_escape_string($conn, $_POST['background_color'] ?? '#ffffff');
    $text_color = mysqli_real_escape_string($conn, $_POST['text_color'] ?? '#000000');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = intval($_POST['display_order'] ?? 0);

    // Handle background image upload
    $background_image = null;
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0) {
        $upload_dir = '../uploads/gift-card-designs/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['background_image']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($image_file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
                $background_image = 'uploads/gift-card-designs/' . $file_name;
            }
        }
    }

    // Check if design_key already exists
    $check_query = "SELECT id FROM gift_card_designs WHERE design_key = '$design_key'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Design key already exists. Please use a different key.";
    } elseif (empty($design_name) || empty($design_key) || empty($title)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $insert_query = "INSERT INTO gift_card_designs (design_name, design_key, title, description, background_color, background_image, text_color, is_active, display_order) 
                        VALUES ('$design_name', '$design_key', '$title', " . ($description ? "'$description'" : "NULL") . ", 
                                '$background_color', " . ($background_image ? "'$background_image'" : "NULL") . ", 
                                '$text_color', $is_active, $display_order)";

        if (mysqli_query($conn, $insert_query)) {
            header("Location: manage_gift_card_designs.php?success=" . urlencode("Design created successfully!"));
            exit();
        } else {
            $error_message = "Error creating design: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gift Card Design - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .color-picker-wrapper input[type="color"] {
            width: 80px;
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
        }

        .color-picker-wrapper input[type="text"] {
            flex: 1;
        }

        .preview-box {
            margin-top: 15px;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            font-weight: 800;
            font-size: 24px;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #e0e0e0;
            position: relative;
            background-size: cover;
            background-position: center;
        }

        .preview-box span {
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .file-upload-wrapper {
            position: relative;
            margin-top: 10px;
        }

        .file-upload-wrapper input[type="file"] {
            padding: 12px;
            border: 2px dashed #8B5CF6;
            border-radius: 10px;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-wrapper input[type="file"]:hover {
            border-color: #7C3AED;
            background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
        }

        .image-preview {
            margin-top: 15px;
            max-width: 300px;
            border-radius: 15px;
            border: 3px solid #8B5CF6;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f0f0f0;
        }

        .section-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #2b2b2b;
        }

        .admin-form {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 40px;
            padding: 35px;
            background: linear-gradient(135deg, #ffffff 0%, #f9faff 100%);
            border-radius: 20px;
            border: 1px solid #eef2ff;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.04);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.08);
            transform: translateY(-2px);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 25px;
            background: #fff;
            border-radius: 16px;
            border: 2px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .checkbox-wrapper:hover {
            border-color: #8B5CF6;
            background: #fdfbff;
        }

        .checkbox-wrapper .status-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .checkbox-wrapper .status-title {
            font-weight: 700;
            color: #1e293b;
            font-size: 15px;
        }

        .checkbox-wrapper .status-desc {
            font-size: 13px;
            color: #64748b;
        }

        /* Custom Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        input:checked + .slider {
            background-color: #8B5CF6;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #8B5CF6;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            background: #fff;
            color: #7C3AED;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border-radius: 12px;
            border: 2px solid #f3f0ff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.08);
        }

        .back-btn:hover {
            background: #7C3AED;
            color: #fff;
            border-color: #7C3AED;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
            transform: translateX(-5px);
        }

        .back-btn i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-3px);
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #fff;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .cancel-btn {
            background: #f1f5f9;
            color: #64748b;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 2px solid #e2e8f0;
        }

        .cancel-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.1);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>✨ Add Gift Card Design</h1>
                    <p class="welcome-text">Create a new gift card design with custom background</p>
                </div>
                <div class="header-right">
                    <a href="manage_gift_card_designs.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Designs
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <span style="font-size: 24px;">✅</span>
                        <div><?= htmlspecialchars($success_message) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 24px;">❌</span>
                        <div><?= htmlspecialchars($error_message) ?></div>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="admin-form">
                    <div class="form-section">
                        <div class="section-header">
                            <span style="font-size: 24px;">📝</span>
                            <h3>Basic Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>🏷️ Design Name *</label>
                                <input type="text" name="design_name" value="<?= htmlspecialchars($_POST['design_name'] ?? '') ?>" required placeholder="e.g., Happy Birthday">
                            </div>

                            <div class="form-group">
                                <label>🔑 Design Key *</label>
                                <input type="text" name="design_key" value="<?= htmlspecialchars($_POST['design_key'] ?? '') ?>" required placeholder="e.g., happy_birthday">
                                <small style="color: #666; margin-top: 5px; display: block;">Unique identifier (lowercase, underscores only)</small>
                            </div>

                            <div class="form-group">
                                <label>📌 Title *</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required placeholder="e.g., Happy Birthday">
                            </div>

                            <div class="form-group">
                                <label>📊 Display Order</label>
                                <input type="number" name="display_order" value="<?= htmlspecialchars($_POST['display_order'] ?? '0') ?>" min="0">
                            </div>

                            <div class="form-group full-width">
                                <label>📝 Description</label>
                                <textarea name="description" rows="3" placeholder="e.g., Celebrate yourself today (& always)."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span style="font-size: 24px;">🎨</span>
                            <h3>Colors & Background</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>🎨 Background Color *</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="background_color" id="bgColor" value="<?= htmlspecialchars($_POST['background_color'] ?? '#ffffff') ?>" onchange="updatePreview()">
                                    <input type="text" id="bgColorText" value="<?= htmlspecialchars($_POST['background_color'] ?? '#ffffff') ?>" onchange="updatePreview()" placeholder="#ffffff">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>📝 Text Color *</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="text_color" id="textColor" value="<?= htmlspecialchars($_POST['text_color'] ?? '#000000') ?>" onchange="updatePreview()">
                                    <input type="text" id="textColorText" value="<?= htmlspecialchars($_POST['text_color'] ?? '#000000') ?>" onchange="updatePreview()" placeholder="#000000">
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>🖼️ Background Image (Optional)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="background_image" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <img src="" class="image-preview" id="imagePreview" style="display: none;">
                                <small style="color: #666; margin-top: 5px; display: block;">Upload a custom background image (will override background color)</small>
                            </div>

                            <div class="form-group full-width">
                                <label>👁️ Preview</label>
                                <div class="preview-box" id="previewBox">
                                    <span id="previewText"><?= htmlspecialchars($_POST['title'] ?? 'Preview') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span style="font-size: 24px;">⚙️</span>
                            <h3>Settings</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="checkbox-wrapper">
                                    <div class="status-info">
                                        <span class="status-title">Active Status</span>
                                        <span class="status-desc">When enabled, this design will be visible to customers to choose.</span>
                                    </div>
                                    <div class="switch">
                                        <input type="checkbox" name="is_active" <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                                        <span class="slider"></span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 20px; margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-magic"></i> Create Design
                        </button>
                        <a href="manage_gift_card_designs.php" class="cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updatePreview() {
            const bgColor = document.getElementById('bgColor').value;
            const textColor = document.getElementById('textColor').value;
            const title = document.querySelector('input[name="title"]').value || 'Preview';
            
            document.getElementById('bgColorText').value = bgColor;
            document.getElementById('textColorText').value = textColor;
            
            const previewBox = document.getElementById('previewBox');
            const imagePreview = document.getElementById('imagePreview');
            
            if (imagePreview.style.display === 'none' || !imagePreview.src) {
                previewBox.style.background = bgColor;
                previewBox.style.color = textColor;
            }
            
            document.getElementById('previewText').textContent = title;
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewBox = document.getElementById('previewBox');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    previewBox.style.backgroundImage = 'url(' + e.target.result + ')';
                    previewBox.style.backgroundSize = 'cover';
                    previewBox.style.backgroundPosition = 'center';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Sync color inputs
        document.getElementById('bgColorText').addEventListener('input', function() {
            document.getElementById('bgColor').value = this.value;
            updatePreview();
        });

        document.getElementById('textColorText').addEventListener('input', function() {
            document.getElementById('textColor').value = this.value;
            updatePreview();
        });

        // Update preview when title changes
        document.querySelector('input[name="title"]').addEventListener('input', updatePreview);

        // Initial preview update
        updatePreview();
    </script>
</body>
</html>
