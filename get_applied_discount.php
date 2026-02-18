<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'discount' => null];

if (isset($_SESSION['applied_discount'])) {
    $response['success'] = true;
    $response['discount'] = $_SESSION['applied_discount'];
}

echo json_encode($response);
?>
