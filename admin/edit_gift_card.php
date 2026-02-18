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

// Get gift card ID
$gift_card_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$gift_card_id) {
    header("Location: manage_gift_cards.php");
    exit();
}

// Get gift card details
$card_query = "SELECT * FROM gift_cards WHERE id = $gift_card_id";
$card_result = mysqli_query($conn, $card_query);
$gift_card = mysqli_fetch_assoc($card_result);

if (!$gift_card) {
    header("Location: manage_gift_cards.php");
    exit();
}

// Get available designs
$designs_query = "SELECT * FROM gift_card_designs WHERE is_active = 1 ORDER BY display_order ASC";
$designs_result = mysqli_query($conn, $designs_query);
$designs = [];
while ($design = mysqli_fetch_assoc($designs_result)) {
    $designs[] = $design;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to_name = mysqli_real_escape_string($conn, $_POST['to_name'] ?? '');
    $to_email = mysqli_real_escape_string($conn, $_POST['to_email'] ?? '');
    $to_phone = mysqli_real_escape_string($conn, $_POST['to_phone'] ?? '');
    $from_name = mysqli_real_escape_string($conn, $_POST['from_name'] ?? '');
    $from_phone = mysqli_real_escape_string($conn, $_POST['from_phone'] ?? '');
    $message = mysqli_real_escape_string($conn, $_POST['message'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $design = mysqli_real_escape_string($conn, $_POST['design'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
    $valid_till = mysqli_real_escape_string($conn, $_POST['valid_till'] ?? '');
    $pin = mysqli_real_escape_string($conn, $_POST['pin'] ?? '');

    // Handle background image upload
    $background_image = $gift_card['design']; // Keep existing design by default
    
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0) {
        $upload_dir = '../uploads/gift-cards/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['background_image']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($image_file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target_file)) {
                // Update design with background image
                $background_image = 'uploads/gift-cards/' . $file_name;
            }
        }
    }

    if (empty($to_name) || empty($from_name) || empty($amount) || empty($valid_till)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $update_query = "UPDATE gift_cards SET 
                        to_name = '$to_name',
                        to_email = " . ($to_email ? "'$to_email'" : "NULL") . ",
                        to_phone = " . ($to_phone ? "'$to_phone'" : "NULL") . ",
                        from_name = '$from_name',
                        from_phone = '$from_phone',
                        message = " . ($message ? "'$message'" : "NULL") . ",
                        amount = $amount,
                        design = '$design',
                        status = '$status',
                        valid_till = '$valid_till'";
        
        if (!empty($pin)) {
            $update_query .= ", pin = '$pin'";
        }
        
        $update_query .= " WHERE id = $gift_card_id";

        if (mysqli_query($conn, $update_query)) {
            $success_message = "Gift card updated successfully!";
            // Refresh gift card data
            $card_query = "SELECT * FROM gift_cards WHERE id = $gift_card_id";
            $card_result = mysqli_query($conn, $card_query);
            $gift_card = mysqli_fetch_assoc($card_result);
        } else {
            $error_message = "Error updating gift card: " . mysqli_error($conn);
        }
    }
}

// Get design details
$current_design_query = "SELECT * FROM gift_card_designs WHERE design_key = '{$gift_card['design']}' LIMIT 1";
$current_design_result = mysqli_query($conn, $current_design_query);
$current_design = mysqli_fetch_assoc($current_design_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gift Card - Craft Royale</title>
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

        .form-group input:disabled {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .design-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-top: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border-radius: 15px;
            border: 2px dashed #e0e0e0;
        }

        .design-option {
            cursor: pointer;
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: #fff;
            position: relative;
        }

        .design-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .design-option:hover {
            border-color: #8B5CF6;
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }

        .design-option:hover::before {
            opacity: 1;
        }

        .design-option.selected {
            border-color: #8B5CF6;
            border-width: 4px;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2), 0 8px 20px rgba(139, 92, 246, 0.3);
            transform: scale(1.05);
        }

        .design-option.selected::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            background: #8B5CF6;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.4);
            animation: checkmarkPop 0.3s ease;
        }

        @keyframes checkmarkPop {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .design-preview {
            padding: 40px 15px;
            text-align: center;
            font-weight: 800;
            font-size: 15px;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            background-size: cover;
            background-position: center;
        }

        .design-name {
            padding: 14px;
            text-align: center;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            border-top: 1px solid #f1f5f9;
            position: relative;
            z-index: 0;
        }

        .image-upload-preview {
            margin-top: 15px;
            max-width: 250px;
            border-radius: 15px;
            border: 3px solid #8B5CF6;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
            transition: all 0.3s ease;
        }

        .image-upload-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.3);
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
            transform: translateY(-2px);
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            font-size: 24px;
        }

        .admin-form {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #fafbff 0%, #ffffff 100%);
            border-radius: 15px;
            border: 1px solid #f0f0f0;
        }

        .form-section:last-child {
            margin-bottom: 0;
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

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #155724;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            animation: slideDown 0.5s ease;
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

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #721c24;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }

        .card-number-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 2px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            margin-bottom: 10px;
        }

        small {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Edit Gift Card</h1>
                    <p class="welcome-text">Update gift card details and design</p>
                </div>
                <div class="header-right">
                    <a href="manage_gift_cards.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Gift Cards
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <span style="font-size: 24px;">✅</span>
                        <div>
                            <strong>Success!</strong><br>
                            <?= $success_message ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <span style="font-size: 24px;">❌</span>
                        <div>
                            <strong>Error!</strong><br>
                            <?= $error_message ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="admin-form">
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">🎴</span>
                            <h3>Gift Card Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>🎫 Card Number</label>
                                <div class="card-number-display">
                                    <?= htmlspecialchars($gift_card['card_number']) ?>
                                </div>
                                <small>🔒 Card number cannot be changed</small>
                            </div>

                            <div class="form-group">
                                <label>🔐 PIN *</label>
                                <input type="text" name="pin" value="<?= htmlspecialchars($gift_card['pin']) ?>" maxlength="10" required>
                                <small>💡 Leave empty to keep current PIN</small>
                            </div>

                            <div class="form-group">
                                <label>💰 Amount (₹) *</label>
                                <input type="number" name="amount" step="0.01" value="<?= $gift_card['amount'] ?>" required>
                            </div>

                            <div class="form-group">
                                <label>📊 Status *</label>
                                <select name="status" required>
                                    <option value="pending" <?= $gift_card['status'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                    <option value="active" <?= $gift_card['status'] == 'active' ? 'selected' : '' ?>>✅ Active</option>
                                    <option value="used" <?= $gift_card['status'] == 'used' ? 'selected' : '' ?>>✔️ Used</option>
                                    <option value="expired" <?= $gift_card['status'] == 'expired' ? 'selected' : '' ?>>⏰ Expired</option>
                                    <option value="cancelled" <?= $gift_card['status'] == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>📅 Valid Till *</label>
                                <input type="date" name="valid_till" value="<?= $gift_card['valid_till'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">🎨</span>
                            <h3>Design & Background</h3>
                        </div>
                        <div class="form-grid">

                            <div class="form-group full-width">
                                <label>🎨 Choose Design *</label>
                                <div class="design-grid">
                                    <?php foreach ($designs as $design_opt): ?>
                                        <div class="design-option <?= $gift_card['design'] == $design_opt['design_key'] ? 'selected' : '' ?>" 
                                             onclick="selectDesign('<?= $design_opt['design_key'] ?>', this)">
                                            <div class="design-preview" style="background: <?= !empty($design_opt['background_image']) ? "url('../" . htmlspecialchars($design_opt['background_image']) . "')" : $design_opt['background_color'] ?>; color: <?= $design_opt['text_color'] ?>;">
                                                <?= htmlspecialchars($design_opt['title']) ?>
                                            </div>
                                            <div class="design-name"><?= htmlspecialchars($design_opt['design_name']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="design" id="selectedDesign" value="<?= $gift_card['design'] ?>" required>
                            </div>

                            <div class="form-group full-width">
                                <label>🖼️ Background Image (Optional)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="background_image" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <?php if ($current_design && !empty($current_design['background_image'])): ?>
                                    <img src="../<?= htmlspecialchars($current_design['background_image']) ?>" class="image-upload-preview" id="imagePreview">
                                <?php else: ?>
                                    <img src="" class="image-upload-preview" id="imagePreview" style="display: none;">
                                <?php endif; ?>
                                <small>📸 Upload a custom background image for this gift card design</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-icon">👥</span>
                            <h3>Recipient & Sender Details</h3>
                        </div>
                        <div class="form-grid">

                            <div class="form-group">
                                <label>👤 To (Recipient Name) *</label>
                                <input type="text" name="to_name" value="<?= htmlspecialchars($gift_card['to_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>📧 Recipient Email</label>
                                <input type="email" name="to_email" value="<?= htmlspecialchars($gift_card['to_email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>📱 Recipient Phone</label>
                                <input type="text" name="to_phone" value="<?= htmlspecialchars($gift_card['to_phone'] ?? '') ?>" maxlength="10" pattern="\d{10}" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                            </div>

                            <div class="form-group">
                                <label>👤 From (Sender Name) *</label>
                                <input type="text" name="from_name" value="<?= htmlspecialchars($gift_card['from_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>📱 Sender Phone *</label>
                                <input type="text" name="from_phone" value="<?= htmlspecialchars($gift_card['from_phone']) ?>" required maxlength="10" pattern="\d{10}" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                            </div>

                            <div class="form-group full-width">
                                <label>💌 Message</label>
                                <textarea name="message" rows="4" placeholder="Write a personalized message..."><?= htmlspecialchars($gift_card['message'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 20px; margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-magic"></i> Update Gift Card
                        </button>
                        <a href="manage_gift_cards.php" class="cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function selectDesign(designKey, element) {
            document.getElementById('selectedDesign').value = designKey;
            document.querySelectorAll('.design-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
