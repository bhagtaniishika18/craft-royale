<?php
// Set cache prevention headers FIRST
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// Include session destruction utility
require_once __DIR__ . '/../includes/destroy_session.php';

// Destroy the session completely
destroy_session_completely();

// Prevent any new session from being created
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Redirect to login with cache prevention
header("Location: login.php");
exit();
?>