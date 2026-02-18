<?php
session_start();
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$in_wishlist = false;
if ($product_id > 0 && isset($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    $in_wishlist = isset($_SESSION['wishlist'][$product_id]);
}

echo json_encode(['in_wishlist' => $in_wishlist]);
?>
