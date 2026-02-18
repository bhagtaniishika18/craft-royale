<?php
include '../includes/db.php';

// Check if request_type column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM return_refund_requests LIKE 'request_type'");
$has_request_type = ($check_column && mysqli_num_rows($check_column) > 0);

if (!$has_request_type) {
    echo "Adding request_type column...<br>";
    $alter_query = "ALTER TABLE return_refund_requests ADD COLUMN request_type ENUM('return', 'refund') DEFAULT 'return' AFTER order_number";
    if (mysqli_query($conn, $alter_query)) {
        echo "Column added successfully!";
    } else {
        echo "Error adding column: " . mysqli_error($conn);
    }
} else {
    echo "request_type column already exists.";
}
?>
