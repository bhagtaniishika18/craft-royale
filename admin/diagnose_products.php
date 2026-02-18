<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

// Check products table structure
$columns_query = mysqli_query($conn, "SHOW COLUMNS FROM products");
$columns = [];
$column_names = [];
while ($col = mysqli_fetch_assoc($columns_query)) {
    $columns[] = $col;
    $column_names[] = $col['Field'];
}

// Check for required columns
$required_columns = ['id', 'category_id', 'subcategory_id', 'name', 'price', 'image', 'status', 'created_at'];
$missing_columns = [];
foreach ($required_columns as $req_col) {
    if (!in_array($req_col, $column_names)) {
        $missing_columns[] = $req_col;
    }
}

// Test queries
$test_queries = [];
$test_queries['Simple SELECT'] = "SELECT * FROM products LIMIT 1";
$test_queries['SELECT with alias'] = "SELECT * FROM products p LIMIT 1";
$test_queries['COUNT'] = "SELECT COUNT(*) as total FROM products";
$test_queries['COUNT with alias'] = "SELECT COUNT(*) as total FROM products p";

if (in_array('category_id', $column_names)) {
    $test_queries['Filter by category_id'] = "SELECT COUNT(*) as total FROM products p WHERE p.category_id = 1";
}

if (in_array('status', $column_names)) {
    $test_queries['Filter by status'] = "SELECT COUNT(*) as total FROM products p WHERE p.status = 'active'";
}

$query_results = [];
foreach ($test_queries as $name => $query) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        $query_results[$name] = ['success' => true, 'data' => $data, 'query' => $query];
    } else {
        $query_results[$name] = ['success' => false, 'error' => mysqli_error($conn), 'query' => $query];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnose Products Table - Craft Royale</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2fc7b4; }
        h2 { margin-top: 30px; color: #333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2fc7b4;
            color: white;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .missing { background: #fff3cd; }
        .query-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin: 5px 0;
            word-break: break-all;
        }
        .error-box {
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            color: #721c24;
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2fc7b4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Products Table Diagnosis</h1>
        
        <h2>Table Columns (<?= count($columns) ?> total)</h2>
        <table>
            <tr>
                <th>Column Name</th>
                <th>Type</th>
                <th>Null</th>
                <th>Default</th>
            </tr>
            <?php foreach ($columns as $col): 
                $is_missing = in_array($col['Field'], $missing_columns);
            ?>
                <tr class="<?= $is_missing ? 'missing' : '' ?>">
                    <td>
                        <?= htmlspecialchars($col['Field']) ?>
                        <?php if (in_array($col['Field'], $required_columns)): ?>
                            <strong style="color: #28a745;">*</strong>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($col['Type']) ?></td>
                    <td><?= $col['Null'] ?></td>
                    <td><?= htmlspecialchars($col['Default'] ?? 'NULL') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if (!empty($missing_columns)): ?>
            <div class="error-box">
                <strong>Missing Required Columns:</strong> <?= implode(', ', $missing_columns) ?>
                <br><br>
                <a href="fix_products_table.php" class="btn">Fix Products Table</a>
            </div>
        <?php else: ?>
            <div class="success" style="padding: 15px; background: #d4edda; border-radius: 4px; margin-top: 15px;">
                ✓ All required columns are present!
            </div>
        <?php endif; ?>

        <h2>Query Tests</h2>
        <?php foreach ($query_results as $name => $result): ?>
            <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                <h3 style="margin-top: 0;">
                    <?= htmlspecialchars($name) ?>
                    <?php if ($result['success']): ?>
                        <span class="success">✓ Success</span>
                    <?php else: ?>
                        <span class="error">✗ Failed</span>
                    <?php endif; ?>
                </h3>
                <div class="query-box"><?= htmlspecialchars($result['query']) ?></div>
                <?php if ($result['success']): ?>
                    <div style="margin-top: 10px;">
                        <strong>Result:</strong>
                        <pre><?= print_r($result['data'], true) ?></pre>
                    </div>
                <?php else: ?>
                    <div class="error-box">
                        <strong>Error:</strong> <?= htmlspecialchars($result['error']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div style="margin-top: 30px;">
            <a href="check_products_table.php" class="btn">View Detailed Table Info</a>
            <a href="fix_products_table.php" class="btn">Fix Products Table</a>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>










