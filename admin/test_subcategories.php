<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Subcategories - Craft Royale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #2fa76b; }
        .error { color: #dc3545; }
        .info { color: #2fc7b4; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2fc7b4;
            color: #fff;
        }
    </style>
</head>
<body>
    <h1>Subcategories Test & Debug</h1>
    
    <div class="test-box">
        <h2>1. Check if subcategories table exists</h2>
        <?php
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'subcategories'");
        if (mysqli_num_rows($table_check) > 0) {
            echo '<p class="success">✓ Subcategories table exists</p>';
        } else {
            echo '<p class="error">✗ Subcategories table does NOT exist. Please run the database schema file.</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>2. Check categories</h2>
        <?php
        $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
        if (mysqli_num_rows($cats) > 0) {
            echo '<p class="success">Found ' . mysqli_num_rows($cats) . ' categories:</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Category Name</th><th>Subcategories Count</th></tr>';
            while ($cat = mysqli_fetch_assoc($cats)) {
                $sub_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM subcategories WHERE category_id='{$cat['id']}'");
                $count = mysqli_fetch_assoc($sub_count)['count'];
                echo '<tr>';
                echo '<td>' . $cat['id'] . '</td>';
                echo '<td>' . htmlspecialchars($cat['category_name']) . '</td>';
                echo '<td>' . $count . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">No categories found in database.</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>3. Check subcategories</h2>
        <?php
        $subs = mysqli_query($conn, "SELECT s.*, c.category_name FROM subcategories s LEFT JOIN categories c ON s.category_id = c.id ORDER BY c.category_name, s.subcategory_name");
        if (mysqli_num_rows($subs) > 0) {
            echo '<p class="success">Found ' . mysqli_num_rows($subs) . ' subcategories:</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Category</th><th>Subcategory Name</th><th>Slug</th></tr>';
            while ($sub = mysqli_fetch_assoc($subs)) {
                echo '<tr>';
                echo '<td>' . $sub['id'] . '</td>';
                echo '<td>' . htmlspecialchars($sub['category_name']) . '</td>';
                echo '<td>' . htmlspecialchars($sub['subcategory_name']) . '</td>';
                echo '<td>' . htmlspecialchars($sub['subcategory_slug'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">No subcategories found in database.</p>';
            echo '<p class="info">💡 <a href="add_embroidery_subcategories.php">Click here to add Embroidery subcategories</a></p>';
            echo '<p class="info">💡 <a href="add_subcategory.php">Click here to add subcategories manually</a></p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>4. Test get_subcategories.php API</h2>
        <p>Select a category to test:</p>
        <select id="testCategory" onchange="testAPI()">
            <option value="">Select Category</option>
            <?php
            mysqli_data_seek($cats, 0);
            while ($cat = mysqli_fetch_assoc($cats)) {
                echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
            }
            ?>
        </select>
        <div id="apiResult" style="margin-top: 15px;"></div>
    </div>

    <div class="test-box">
        <h2>5. Quick Actions</h2>
        <p>
            <a href="add_embroidery_subcategories.php" style="display: inline-block; padding: 10px 20px; background: #2fc7b4; color: #fff; text-decoration: none; border-radius: 5px; margin-right: 10px;">
                Add All Embroidery Subcategories
            </a>
            <a href="add_subcategory.php" style="display: inline-block; padding: 10px 20px; background: #2fa76b; color: #fff; text-decoration: none; border-radius: 5px; margin-right: 10px;">
                Add Subcategory Manually
            </a>
            <a href="add_product.php" style="display: inline-block; padding: 10px 20px; background: #999; color: #fff; text-decoration: none; border-radius: 5px;">
                Back to Add Product
            </a>
        </p>
    </div>

    <script>
    function testAPI() {
        const categoryId = document.getElementById('testCategory').value;
        const resultDiv = document.getElementById('apiResult');
        
        if (!categoryId) {
            resultDiv.innerHTML = '';
            return;
        }
        
        resultDiv.innerHTML = '<p class="info">Loading...</p>';
        
        fetch(`get_subcategories.php?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    let html = '<p class="success">✓ API returned ' + data.length + ' subcategories:</p><ul>';
                    data.forEach(sub => {
                        html += '<li>' + sub.subcategory_name + ' (ID: ' + sub.id + ')</li>';
                    });
                    html += '</ul>';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ No subcategories returned. The API returned: ' + JSON.stringify(data) + '</p>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<p class="error">✗ Error: ' + error.message + '</p>';
            });
    }
    </script>
</body>
</html>










