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

// Generate random card number
function generateCardNumber() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4) . '-' . 
                     substr(md5(uniqid(rand(), true)), 0, 4));
}

// Get available designs
$designs_query = "SELECT * FROM gift_card_designs WHERE is_active = 1 ORDER BY display_order ASC";
$designs_result = mysqli_query($conn, $designs_query);
$designs = [];
while ($design = mysqli_fetch_assoc($designs_result)) {
    $designs[] = $design;
}

// If no designs exist, create default ones
if (empty($designs)) {
    $default_designs = [
        ['design_key' => 'merry_christmas', 'design_name' => 'Merry Christmas', 'title' => 'Merry Christmas', 'description' => 'To another very good year! \'Tis the season of joy!', 'background_color' => '#2d5016', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_birthday', 'design_name' => 'Happy Birthday', 'title' => 'Happy Birthday', 'description' => 'Celebrate yourself today (& always).', 'background_color' => '#e91e63', 'text_color' => '#ffffff'],
        ['design_key' => 'anniversary', 'design_name' => 'Anniversary', 'title' => 'Anniversary', 'description' => 'To another very good year! Wishing you love and joy!', 'background_color' => '#9c27b0', 'text_color' => '#ffffff'],
        ['design_key' => 'wedding', 'design_name' => 'Wedding', 'title' => 'Wedding', 'description' => 'Here\'s to forever! Congratulations!', 'background_color' => '#c2185b', 'text_color' => '#ffffff'],
        ['design_key' => 'happy_diwali', 'design_name' => 'Happy Diwali', 'title' => 'Happy Diwali', 'description' => 'Gift Joy & Prosperity.', 'background_color' => '#ff9800', 'text_color' => '#ffffff'],
        ['design_key' => 'valentines_day', 'design_name' => 'Valentine\'s Day', 'title' => 'Valentine\'s Day', 'description' => 'Celebrate love & special moments!', 'background_color' => '#e91e63', 'text_color' => '#ffffff'],
    ];
    
    foreach ($default_designs as $design) {
        $design_key = mysqli_real_escape_string($conn, $design['design_key']);
        $design_name = mysqli_real_escape_string($conn, $design['design_name']);
        $title = mysqli_real_escape_string($conn, $design['title']);
        $description = mysqli_real_escape_string($conn, $design['description']);
        $background_color = mysqli_real_escape_string($conn, $design['background_color']);
        $text_color = mysqli_real_escape_string($conn, $design['text_color']);
        
        $insert_design = "INSERT INTO gift_card_designs (design_key, design_name, title, description, background_color, text_color, is_active, display_order) 
                         VALUES ('$design_key', '$design_name', '$title', '$description', '$background_color', '$text_color', 1, 0)";
        mysqli_query($conn, $insert_design);
    }
    
    // Reload designs
    $designs_result = mysqli_query($conn, $designs_query);
    $designs = [];
    while ($design = mysqli_fetch_assoc($designs_result)) {
        $designs[] = $design;
    }
}

if (isset($_POST['create_gift_card'])) {
    $card_number = generateCardNumber();
    $pin = mysqli_real_escape_string($conn, $_POST['pin']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $design = mysqli_real_escape_string($conn, $_POST['design']);
    $to_name = mysqli_real_escape_string($conn, $_POST['to_name']);
    $to_email = !empty($_POST['to_email']) ? mysqli_real_escape_string($conn, $_POST['to_email']) : null;
    $to_phone = !empty($_POST['to_phone']) ? mysqli_real_escape_string($conn, $_POST['to_phone']) : null;
    $from_name = mysqli_real_escape_string($conn, $_POST['from_name']);
    $from_phone = mysqli_real_escape_string($conn, $_POST['from_phone']);
    $message = !empty($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : 'Here\'s a little something to make your day!';
    $valid_till = mysqli_real_escape_string($conn, $_POST['valid_till']);
    
    // Validate PIN (4-10 digits)
    if (!preg_match('/^[0-9]{4,10}$/', $pin)) {
        $error_message = "PIN must be 4-10 digits";
    } elseif ($amount < 100 || $amount > 10000) {
        $error_message = "Amount must be between ₹100 and ₹10,000";
    } else {
        $insert_query = "INSERT INTO gift_cards (card_number, pin, amount, design, to_name, to_email, to_phone, from_name, from_phone, message, status, valid_till) 
                        VALUES ('$card_number', '$pin', '$amount', '$design', '$to_name', " . 
                        ($to_email ? "'$to_email'" : "NULL") . ", " . 
                        ($to_phone ? "'$to_phone'" : "NULL") . ", 
                        '$from_name', '$from_phone', '$message', 'active', '$valid_till')";
        
        if (mysqli_query($conn, $insert_query)) {
            $success_message = "Gift card created successfully! Card Number: $card_number";
            // Clear form
            $_POST = [];
        } else {
            $error_message = "Error creating gift card: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Gift Card - Craft Royale</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: #fff;
            color: #7C3AED;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border-radius: 12px;
            border: 2px solid #f3f0ff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.08);
            margin-bottom: 25px;
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

        .design-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .design-option {
            background: #fff;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .design-option:hover {
            border-color: #8B5CF6;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
        }

        .design-option.selected {
            border-color: #8B5CF6;
            background: #fdfbff;
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.2);
        }

        .design-option.selected::after {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 10px;
            color: #8B5CF6;
            font-size: 20px;
            background: #fff;
            border-radius: 50%;
            line-height: 1;
        }

        .design-preview {
            width: 100%;
            height: 120px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 18px;
            text-align: center;
            padding: 15px;
            background-size: cover !important;
            background-position: center !important;
            box-shadow: inset 0 0 40px rgba(0,0,0,0.1);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .design-name {
            font-weight: 700;
            color: #2b2b2b;
            margin-top: 12px;
            font-size: 14px;
            text-align: center;
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #fff;
            padding: 14px 35px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.45);
            filter: brightness(1.1);
        }

        .add-new-design-card {
            background: #fff;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            min-height: 165px;
        }

        .add-new-design-card:hover {
            border-color: #8B5CF6;
            background: #f8fafc;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .add-new-design-card .plus-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 24px;
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .add-new-design-card:hover .plus-icon {
            background: #8B5CF6;
            color: #fff;
            transform: rotate(90deg);
        }

        .add-new-design-card span {
            font-weight: 700;
            color: #64748b;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .add-new-design-card:hover span {
            color: #8B5CF6;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Create Gift Card</h1>
                    <p class="welcome-text">Create a new gift card for your customers</p>
                </div>
            </header>

            <div class="dashboard-content">
                <a href="manage_gift_cards.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Gift Cards
                </a>

                <div class="form-container">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="giftCardForm">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>Choose Design *</label>
                                <div class="design-grid">
                                    <?php foreach ($designs as $design): ?>
                                        <div class="design-option" onclick="selectDesign('<?= $design['design_key'] ?>', this)">
                                            <div class="design-preview" style="background: <?= !empty($design['background_image']) ? "url('../" . htmlspecialchars($design['background_image']) . "')" : $design['background_color'] ?>;">
                                                <?= htmlspecialchars($design['title']) ?>
                                            </div>
                                            <div class="design-name"><?= htmlspecialchars($design['design_name']) ?></div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Add New Design Card -->
                                    <a href="add_gift_card_design.php" class="add-new-design-card">
                                        <div class="plus-icon">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <span>Add New Design</span>
                                    </a>
                                </div>
                                <input type="hidden" name="design" id="selectedDesign" value="<?= $designs[0]['design_key'] ?? '' ?>" required>
                            </div>

                            <div class="form-group">
                                <label>To (Recipient Name) *</label>
                                <input type="text" name="to_name" value="<?= htmlspecialchars($_POST['to_name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Recipient's Phone or Email</label>
                                <input type="text" name="to_email" value="<?= htmlspecialchars($_POST['to_email'] ?? '') ?>" placeholder="Email or Phone">
                            </div>

                            <div class="form-group full-width">
                                <label>Message</label>
                                <textarea name="message" placeholder="Here's a little something to make your day!"><?= htmlspecialchars($_POST['message'] ?? 'Here\'s a little something to make your day!') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Amount (₹) *</label>
                                <input type="number" name="amount" step="0.01" min="100" max="10000" value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" required placeholder="Min: ₹100, Max: ₹10,000">
                            </div>

                            <div class="form-group">
                                <label>PIN (4-10 digits) *</label>
                                <input type="text" name="pin" pattern="[0-9]{4,10}" value="<?= htmlspecialchars($_POST['pin'] ?? '') ?>" required placeholder="Enter 4-10 digit PIN" maxlength="10">
                            </div>

                            <div class="form-group">
                                <label>From (Sender Name) *</label>
                                <input type="text" name="from_name" value="<?= htmlspecialchars($_POST['from_name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Your Phone *</label>
                                <input type="tel" name="from_phone" value="<?= htmlspecialchars($_POST['from_phone'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Valid Till *</label>
                                <input type="date" name="valid_till" value="<?= htmlspecialchars($_POST['valid_till'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" required>
                            </div>
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" name="create_gift_card" class="submit-btn">
                                <i class="fas fa-save"></i> Create Gift Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function selectDesign(designKey, element) {
            // Remove selected class from all
            document.querySelectorAll('.design-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked element
            element.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('selectedDesign').value = designKey;
        }

        // Select first design by default
        document.addEventListener('DOMContentLoaded', function() {
            const firstDesign = document.querySelector('.design-option');
            if (firstDesign) {
                firstDesign.click();
            }
        });
    </script>
</body>
</html>
