<?php
// Test script to verify session destruction
session_start();

echo "<!DOCTYPE html><html><head><title>Session Test</title><style>body{font-family:Arial;padding:20px;}h2{color:#2fc7b4;}p{margin:10px 0;}a{color:#2fa76b;text-decoration:none;padding:10px 20px;background:#f0f0f0;border-radius:5px;display:inline-block;margin:5px;}a:hover{background:#2fa76b;color:white;}</style></head><body>";

echo "<h2>Session Test</h2>";
echo "<p><strong>Current Session ID:</strong> " . (session_id() ?: 'None') . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green;'><strong>User ID in session:</strong> " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color:red;'><strong>No user_id in session</strong></p>";
}

if (isset($_SESSION['admin'])) {
    echo "<p style='color:green;'><strong>Admin in session:</strong> " . $_SESSION['admin'] . "</p>";
} else {
    echo "<p style='color:red;'><strong>No admin in session</strong></p>";
}

echo "<p><strong>All session data:</strong></p>";
echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
if (empty($_SESSION)) {
    echo "Session is EMPTY - No data found";
} else {
    print_r($_SESSION);
}
echo "</pre>";

echo "<p><strong>Session cookie:</strong> ";
if (isset($_COOKIE[session_name()])) {
    echo "<span style='color:green;'>" . $_COOKIE[session_name()] . "</span>";
} else {
    echo "<span style='color:red;'>Not set</span>";
}
echo "</p>";

echo "<p><strong>Session save path:</strong> " . (session_save_path() ?: 'Default') . "</p>";

echo "<hr>";
echo "<h3>Actions:</h3>";
echo "<p><a href='logout.php' onclick='return confirm(\"Are you sure you want to logout?\")'>Test Logout (Client)</a></p>";
echo "<p><a href='admin/logout.php' onclick='return confirm(\"Are you sure you want to logout?\")'>Test Logout (Admin)</a></p>";
echo "<p><a href='test_session_destroy.php'>Refresh Page</a></p>";

// Test the destroy function directly
if (isset($_GET['destroy'])) {
    echo "<hr><h3>Testing Direct Session Destruction:</h3>";
    require_once __DIR__ . '/includes/destroy_session.php';
    require_once __DIR__ . '/includes/force_destroy_session.php';
    
    $destroyed_id = destroy_session_completely();
    $force_destroyed_id = force_destroy_session();
    
    echo "<p style='color:green;'><strong>Method 1:</strong> Session destroyed! ID was: " . htmlspecialchars($destroyed_id) . "</p>";
    echo "<p style='color:green;'><strong>Method 2:</strong> Force destroyed! ID was: " . htmlspecialchars($force_destroyed_id) . "</p>";
    echo "<p><a href='test_session_destroy.php'>Check Again (Session should be empty now)</a></p>";
} else {
    echo "<p><a href='test_session_destroy.php?destroy=1' onclick='return confirm(\"This will destroy your session. Continue?\")'>Test Direct Session Destruction (Both Methods)</a></p>";
}

echo "</body></html>";
?>

