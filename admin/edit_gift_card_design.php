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

// Get design ID
$design_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$design_id) {
    header("Location: manage_gift_card_designs.php");
    exit();
}

// Get design details
$design_query = "SELECT * FROM gift_card_designs WHERE id = $design_id";
$design_result = mysqli_query($conn, $design_query);
$design = mysqli_fetch_assoc($design_result);

if (!$design) {
    header("Location: manage_gift_card_designs.php");
    exit();
}

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
    $background_image = $design['background_image']; // Keep existing by default
    
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
                // Delete old image if exists
                if (!empty($design['background_image']) && file_exists('../' . $design['background_image'])) {
                    unlink('../' . $design['background_image']);
                }
                $background_image = 'uploads/gift-card-designs/' . $file_name;
            }
        }
    }

    // Check if design_key already exists (excluding current design)
    $check_query = "SELECT id FROM gift_card_designs WHERE design_key = '$design_key' AND id != $design_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Design key already exists. Please use a different key.";
    } elseif (empty($design_name) || empty($design_key) || empty($title)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $update_query = "UPDATE gift_card_designs SET 
                        design_name = '$design_name',
                        design_key = '$design_key',
                        title = '$title',
                        description = " . ($description ? "'$description'" : "NULL") . ",
                        background_color = '$background_color',
                        background_image = " . ($background_image ? "'$background_image'" : "NULL") . ",
                        text_color = '$text_color',
                        is_active = $is_active,
                        display_order = $display_order
                        WHERE id = $design_id";

        if (mysqli_query($conn, $update_query)) {
            $success_message = "Design updated successfully!";
            // Refresh design data
            $design_query = "SELECT * FROM gift_card_designs WHERE id = $design_id";
            $design_result = mysqli_query($conn, $design_query);
            $design = mysqli_fetch_assoc($design_result);
        } else {
            $error_message = "Error updating design: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gift Card Design - Craft Royale</title>
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

        .form-group {
            position: relative;
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
            transform: translateY(-2px);
        }

        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .color-picker-wrapper input[type="color"] {
            width: 80px;
            height: 50px;
            border: 3px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .color-picker-wrapper input[type="color"]:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .color-picker-wrapper input[type="text"] {
            flex: 1;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .preview-box {
            margin-top: 15px;
            padding: 50px 30px;
            border-radius: 20px;
            text-align: center;
            font-weight: 800;
            font-size: 28px;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #8B5CF6;
            position: relative;
            background-size: cover;
            background-position: center;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .preview-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .preview-box:hover::before {
            opacity: 1;
        }

        .preview-box:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
        }

        .preview-box span {
            position: relative;
            z-index: 1;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from {
                text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
            }
            to {
                text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4), 0 0 20px rgba(255, 255, 255, 0.3);
            }
        }

        .file-upload-wrapper {
            position: relative;
            margin-top: 10px;
        }

        .file-upload-wrapper input[type="file"] {
            padding: 15px;
            border: 3px dashed #8B5CF6;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .file-upload-wrapper input[type="file"]:hover {
            border-color: #7C3AED;
            background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.2);
        }

        .image-preview {
            margin-top: 15px;
            max-width: 350px;
            border-radius: 15px;
            border: 4px solid #8B5CF6;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
            display: block;
        }

        .image-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
            position: relative;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #8B5CF6 0%, #7C3AED 100%);
            border-radius: 3px;
        }

        .section-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #2b2b2b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            font-size: 28px;
            animation: iconBounce 2s ease-in-out infinite;
        }

        @keyframes iconBounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .admin-form {
            background: linear-gradient(135deg, #ffffff 0%, #fafbff 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
        }

        .form-section {
            margin-bottom: 40px;
            padding: 35px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 18px;
            border: 2px solid #f0f0f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-color: #e0d5ff;
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.1);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkbox-wrapper:hover {
            border-color: #8B5CF6;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            accent-color: #8B5CF6;
        }

        .alert {
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            animation: slideDown 0.5s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 2px solid #dc3545;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #white;
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
            color: #fff;
        }

        .submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .submit-btn:active {
            transform: translateY(-1px);
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

        small {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 12px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #8B5CF6;
        }

        .current-image-label {
            display: inline-block;
            padding: 8px 15px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: #2e7d32;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 15px;
            border: 2px solid #4caf50;
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
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>✏️ Edit Gift Card Design</h1>
                    <p class="welcome-text">Update design details and background</p>
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
                        <span style="font-size: 28px;">✅</span>
                        <div>
                            <strong>Success!</strong><br>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 28px;">❌</span>
                        <div>
                            <strong>Error!</strong><br>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="admin-form">
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">📝</span>
                            <h3>Basic Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>🏷️ Design Name *</label>
                                <input type="text" name="design_name" value="<?= htmlspecialchars($design['design_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>🔑 Design Key *</label>
                                <input type="text" name="design_key" value="<?= htmlspecialchars($design['design_key']) ?>" required>
                                <small style="color: #666; margin-top: 5px; display: block;">Unique identifier (lowercase, underscores only)</small>
                            </div>

                            <div class="form-group">
                                <label>📌 Title *</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($design['title']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>📊 Display Order</label>
                                <input type="number" name="display_order" value="<?= $design['display_order'] ?>" min="0">
                            </div>

                            <div class="form-group full-width">
                                <label>📝 Description</label>
                                <textarea name="description" rows="3"><?= htmlspecialchars($design['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">🎨</span>
                            <h3>Colors & Background</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>🎨 Background Color *</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="background_color" id="bgColor" value="<?= htmlspecialchars($design['background_color']) ?>" onchange="updatePreview()">
                                    <input type="text" name="background_color_text" id="bgColorText" value="<?= htmlspecialchars($design['background_color']) ?>" onchange="updatePreview()">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>📝 Text Color *</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="text_color" id="textColor" value="<?= htmlspecialchars($design['text_color']) ?>" onchange="updatePreview()">
                                    <input type="text" name="text_color_text" id="textColorText" value="<?= htmlspecialchars($design['text_color']) ?>" onchange="updatePreview()">
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>🖼️ Background Image (Optional)</label>
                                <?php if (!empty($design['background_image'])): ?>
                                    <div style="margin-bottom: 15px;">
                                        <span class="current-image-label">📷 Current Background Image</span>
                                        <img src="../<?= htmlspecialchars($design['background_image']) ?>" class="image-preview" style="display: block;">
                                    </div>
                                <?php endif; ?>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="background_image" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <img src="" class="image-preview" id="imagePreview" style="display: none;">
                                <small>💡 Upload a new image to replace the current one</small>
                            </div>

                            <div class="form-group full-width">
                                <label>👁️ Preview</label>
                                <div class="preview-box" id="previewBox" style="background: <?= !empty($design['background_image']) ? "url('../" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>; color: <?= $design['text_color'] ?>; background-size: cover; background-position: center;">
                                    <span id="previewText"><?= htmlspecialchars($design['title']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">⚙️</span>
                            <h3>Settings</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" name="is_active" <?= $design['is_active'] ? 'checked' : '' ?>>
                                    <span>✅ Active (Design will be visible to customers)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 20px; margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-save"></i> Update Design
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
                previewBox.style.backgroundImage = 'none';
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
    </script>
</body>
</html>
