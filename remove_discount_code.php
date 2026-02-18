<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (isset($_SESSION['applied_discount'])) {
    unset($_SESSION['applied_discount']);
    $response['success'] = true;
    $response['message'] = 'Discount code removed successfully';
} else {
    $response['message'] = 'No discount code applied';
}

echo json_encode($response);
?>
