<?php
// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');

session_start();

if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'logged_in',
        'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '',
        'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''
    ]);
} else {
    echo json_encode(['status' => 'not_logged_in']);
}
?>


