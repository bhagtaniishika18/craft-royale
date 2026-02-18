<?php
// Prevent any output before JSON
ob_start();
session_start();
include "includes/db.php";

// Clear any output that might have been generated
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Clear wishlist
$_SESSION['wishlist'] = [];

// Calculate wishlist count
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;

echo json_encode([
    'success' => true,
    'message' => 'Wishlist cleared successfully',
    'wishlist_count' => $wishlist_count
]);
exit;
?>
