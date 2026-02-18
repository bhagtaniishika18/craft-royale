<?php
session_start();
header('Content-Type: application/json');

$wishlist_count = 0;
if (isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    $wishlist_count = count($_SESSION['wishlist']);
}

echo json_encode(['count' => $wishlist_count]);
?>
