<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['applied_gift_card'])) {
    unset($_SESSION['applied_gift_card']);
    echo json_encode(['success' => true, 'message' => 'Gift card removed']);
} else {
    echo json_encode(['success' => false, 'message' => 'No gift card applied']);
}
?>
