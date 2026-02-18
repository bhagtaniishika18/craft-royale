<?php
include 'includes/db.php';

if (!isset($_POST['email'])) {
    echo "invalid";
    exit;
}

$email = trim($_POST['email']);

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid";
    exit;
}

// Check if already subscribed
$stmt = mysqli_prepare($conn, "SELECT id FROM subscribers WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    echo "exists";
    exit;
}

// Insert new subscriber
$insert = mysqli_prepare($conn, "INSERT INTO subscribers (email) VALUES (?)");
mysqli_stmt_bind_param($insert, "s", $email);
mysqli_stmt_execute($insert);

echo "success";
?>