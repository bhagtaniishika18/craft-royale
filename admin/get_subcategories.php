<?php
include("../includes/db.php");

header('Content-Type: application/json');

$category_id = $_GET['category_id'] ?? null;

if (!$category_id) {
    echo json_encode(['error' => 'No category ID provided', 'subcategories' => []]);
    exit();
}

$subcategories = [];

// Check if subcategories table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'subcategories'");
if (mysqli_num_rows($table_check) == 0) {
    // Table doesn't exist
    echo json_encode(['error' => 'Subcategories table does not exist', 'subcategories' => []]);
    exit();
}

// Sanitize category_id
$category_id = (int)$category_id;

// First, verify the category exists
$cat_check = mysqli_query($conn, "SELECT id, category_name FROM categories WHERE id='$category_id' LIMIT 1");
if (mysqli_num_rows($cat_check) == 0) {
    echo json_encode(['error' => 'Category not found', 'subcategories' => []]);
    exit();
}

$category_info = mysqli_fetch_assoc($cat_check);

// Get subcategories for this category
$query = mysqli_query($conn, "SELECT * FROM subcategories WHERE category_id='$category_id' ORDER BY subcategory_name");

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $subcategories[] = $row;
    }
} else {
    // Log error but return empty array
    $error_msg = mysqli_error($conn);
    error_log("Error fetching subcategories: " . $error_msg);
    echo json_encode(['error' => $error_msg, 'subcategories' => []]);
    exit();
}

// Return subcategories (or empty array if none found)
echo json_encode($subcategories);
?>

