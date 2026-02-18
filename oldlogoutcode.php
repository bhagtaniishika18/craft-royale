<?php
// Set cache prevention headers FIRST
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session destruction utility
require_once __DIR__ . '/includes/destroy_session.php';

// Destroy the session completely
$destroyed_session_id = destroy_session_completely();

// Prevent any new session from being created
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    // Return JSON for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success', 
        'message' => 'Logged out successfully',
        'session_destroyed' => true,
        'session_id' => $destroyed_session_id
    ]);
} else {
    // Redirect for direct requests
    header('Location: index.php?logout=1', true, 302);
}
exit();
?>


