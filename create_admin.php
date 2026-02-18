<?php
include 'includes/db.php';

$name = "Admin";
$email = "admin@craftroyale.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password)
        VALUES ('$name', '$email', '$password')";

if (mysqli_query($conn, $sql)) {
    echo "✅ Admin created successfully<br>";
    echo "Email: admin@craftroyale.com<br>";
    echo "Password: admin123";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
