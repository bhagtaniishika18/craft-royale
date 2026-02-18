<?php
include 'includes/db.php';

echo "<h2>Status Column Fix & Verification</h2>";

// Check if status column exists
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
$products_columns = [];
$has_status = false;
$status_column_type = null;

while ($col = mysqli_fetch_assoc($columns_check)) {
    $products_columns[] = $col['Field'];
    if ($col['Field'] == 'status') {
        $has_status = true;
        $status_column_type = $col['Type'];
    }
}

echo "<h3>Step 1: Check Status Column</h3>";
if ($has_status) {
    echo "✅ Status column EXISTS<br>";
    echo "Column type: $status_column_type<br>";
    
    // Check if it has the right values
    if (stripos($status_column_type, 'enum') !== false) {
        echo "✅ Column is ENUM type (correct)<br>";
    } else {
        echo "⚠️ Column exists but may not be ENUM type<br>";
    }
} else {
    echo "❌ Status column DOES NOT EXIST<br>";
    echo "<h3>Step 2: Adding Status Column</h3>";
    
    // Add the status column
    $alter_query = "ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active' AFTER stock";
    if (mysqli_query($conn, $alter_query)) {
        echo "✅ Status column added successfully!<br>";
        $has_status = true;
    } else {
        echo "❌ Error adding status column: " . mysqli_error($conn) . "<br>";
        exit;
    }
}

if ($has_status) {
    echo "<h3>Step 3: Update NULL Status Values</h3>";
    
    // Check for NULL status
    $null_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE status IS NULL");
    $null_data = mysqli_fetch_assoc($null_check);
    $null_count = $null_data['count'];
    
    if ($null_count > 0) {
        echo "Found $null_count products with NULL status. Updating to 'active'...<br>";
        $update_query = "UPDATE products SET status = 'active' WHERE status IS NULL";
        if (mysqli_query($conn, $update_query)) {
            echo "✅ Updated $null_count products to 'active' status<br>";
        } else {
            echo "❌ Error updating: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "✅ No NULL status values found<br>";
    }
    
    echo "<h3>Step 4: Product Status Summary</h3>";
    $summary_query = "SELECT status, COUNT(*) as count FROM products GROUP BY status";
    $summary_result = mysqli_query($conn, $summary_query);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Status</th><th>Count</th><th>Will Show on Client?</th></tr>";
    while ($row = mysqli_fetch_assoc($summary_result)) {
        $status = $row['status'] ?? 'NULL';
        $count = $row['count'];
        $will_show = ($status == 'active' || $status == 'out_of_stock') ? '✅ YES' : '❌ NO';
        echo "<tr><td>" . htmlspecialchars($status) . "</td><td>$count</td><td>$will_show</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>Step 5: Test Query</h3>";
    $test_query = "SELECT COUNT(*) as total FROM products WHERE ((status = 'active' OR status = 'out_of_stock') AND status IS NOT NULL)";
    $test_result = mysqli_query($conn, $test_query);
    $test_data = mysqli_fetch_assoc($test_result);
    echo "Products that SHOULD show on client side: <strong>" . $test_data['total'] . "</strong><br>";
    
    $all_query = "SELECT COUNT(*) as total FROM products";
    $all_result = mysqli_query($conn, $all_query);
    $all_data = mysqli_fetch_assoc($all_result);
    echo "Total products in database: " . $all_data['total'] . "<br>";
    
    $inactive_query = "SELECT COUNT(*) as total FROM products WHERE status = 'inactive'";
    $inactive_result = mysqli_query($conn, $inactive_query);
    $inactive_data = mysqli_fetch_assoc($inactive_result);
    echo "Inactive products (should be hidden): " . $inactive_data['total'] . "<br>";
    
    echo "<h3>Step 6: Sample Inactive Products</h3>";
    $sample_query = "SELECT id, name, status FROM products WHERE status = 'inactive' LIMIT 5";
    $sample_result = mysqli_query($conn, $sample_query);
    
    if (mysqli_num_rows($sample_result) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
        while ($row = mysqli_fetch_assoc($sample_result)) {
            echo "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['name'] ?? 'N/A') . "</td><td>{$row['status']}</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>These products should NOT appear on the client side.</strong></p>";
    } else {
        echo "<p>No inactive products found. All products are active or out of stock.</p>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Clear your browser cache (Ctrl + Shift + Delete)</li>";
echo "<li>Hard refresh the page (Ctrl + F5)</li>";
echo "<li>Go to admin panel and set a product status to 'Inactive'</li>";
echo "<li>Visit the client-side products page - that product should NOT appear</li>";
echo "</ol>";

?>
