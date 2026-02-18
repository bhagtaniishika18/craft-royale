<?php
include 'includes/db.php';

// 1. Create table if not exists
$create_sql = "CREATE TABLE IF NOT EXISTS callback_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('Pending', 'Processing', 'Done') DEFAULT 'Pending',
    admin_note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $create_sql)) {
    echo "Table check/creation completed. ";
} else {
    echo "Error: " . mysqli_error($conn);
}

// 2. Add user_id column if table existed without it
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM callback_requests LIKE 'user_id'");
if (mysqli_num_rows($check_col) == 0) {
    $alter_sql = "ALTER TABLE callback_requests ADD COLUMN user_id INT DEFAULT NULL AFTER id, ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
    if (mysqli_query($conn, $alter_sql)) {
        echo "Column 'user_id' added successfully!";
    } else {
        echo "Error adding column: " . mysqli_error($conn);
    }
} else {
    echo "Database is already up to date!";
}
?>
