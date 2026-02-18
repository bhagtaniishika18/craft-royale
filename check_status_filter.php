<?php
include 'includes/db.php';

echo "<h2>Status Filter Diagnostic</h2>";

// Check if status column exists
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
$has_status = false;
while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
    if ($col['Field'] == 'status') {
        $has_status = true;
    }
}

echo "<h3>1. Status Column Check:</h3>";
if ($has_status) {
    echo "✅ Status column EXISTS<br>";
} else {
    echo "❌ Status column DOES NOT EXIST - This is the problem!<br>";
    echo "You need to add the status column to your products table.<br>";
}

// Check product statuses
if ($has_status) {
    echo "<h3>2. Product Status Distribution:</h3>";
    $status_query = "SELECT status, COUNT(*) as count FROM products GROUP BY status";
    $status_result = mysqli_query($conn, $status_query);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Status</th><th>Count</th></tr>";
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status = $row['status'] ?? 'NULL';
        $count = $row['count'];
        echo "<tr><td>" . htmlspecialchars($status) . "</td><td>$count</td></tr>";
    }
    echo "</table>";
    
    // Check for inactive products
    echo "<h3>3. Inactive Products (should NOT show on client side):</h3>";
    $inactive_query = "SELECT id, name, status FROM products WHERE status = 'inactive' LIMIT 10";
    $inactive_result = mysqli_query($conn, $inactive_query);
    
    if (mysqli_num_rows($inactive_result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
        while ($row = mysqli_fetch_assoc($inactive_result)) {
            echo "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['name']) . "</td><td>{$row['status']}</td></tr>";
        }
        echo "</table>";
        echo "<p>These products should NOT appear on client side.</p>";
    } else {
        echo "<p>No inactive products found.</p>";
    }
    
    // Test the actual query
    echo "<h3>4. Testing Client-Side Query (should exclude inactive):</h3>";
    $test_query = "SELECT COUNT(*) as total FROM products WHERE ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    $test_result = mysqli_query($conn, $test_query);
    $test_data = mysqli_fetch_assoc($test_result);
    echo "Products that SHOULD show: " . $test_data['total'] . "<br>";
    
    $all_query = "SELECT COUNT(*) as total FROM products";
    $all_result = mysqli_query($conn, $all_query);
    $all_data = mysqli_fetch_assoc($all_result);
    echo "Total products in database: " . $all_data['total'] . "<br>";
    
    $inactive_count_query = "SELECT COUNT(*) as total FROM products WHERE status = 'inactive'";
    $inactive_count_result = mysqli_query($conn, $inactive_count_query);
    $inactive_count_data = mysqli_fetch_assoc($inactive_count_result);
    echo "Inactive products: " . $inactive_count_data['total'] . "<br>";
    
} else {
    echo "<h3>2. Fix Required:</h3>";
    echo "<p>You need to add the status column. Run this SQL:</p>";
    echo "<pre>";
    echo "ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active' AFTER stock;";
    echo "</pre>";
}

echo "<h3>5. Files Updated:</h3>";
$files_to_check = [
    'products.php',
    'product.php',
    'index.php',
    'search.php',
    'ajax-search.php',
    'sale.php',
    'best-sellers.php',
    'new-arrivals.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, "status = 'active' OR status = 'out_of_stock'") !== false) {
            echo "✅ $file - Updated<br>";
        } else {
            echo "❌ $file - NOT Updated<br>";
        }
    } else {
        echo "⚠️ $file - File not found<br>";
    }
}

?>
