<?php
// Suppress warnings to prevent breaking JavaScript
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";          // KEEP EMPTY
$db   = "craft_royale";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for handling special characters
mysqli_set_charset($conn, "utf8mb4");
?>
