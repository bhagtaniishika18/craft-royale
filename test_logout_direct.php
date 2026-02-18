<?php
/**
 * Direct logout test - This will destroy session and show results
 */
session_start();

echo "<!DOCTYPE html><html><head><title>Direct Logout Test</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo "h2{color:#2fc7b4;border-bottom:2px solid #2fc7b4;padding-bottom:10px;}";
echo ".info{background:#e8f5e9;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#ffebee;padding:15px;border-radius:5px;margin:10px 0;color:#c62828;}";
echo ".success{background:#e8f5e9;padding:15px;border-radius:5px;margin:10px 0;color:#2e7d32;}";
echo "a{color:#2fa76b;text-decoration:none;padding:10px 20px;background:#f0f0f0;border-radius:5px;display:inline-block;margin:5px;}";
echo "a:hover{background:#2fa76b;color:white;}</style></head><body>";

echo "<div class='container'>";
echo "<h2>Direct Logout Test</h2>";

// Show session before
echo "<div class='info'>";
echo "<h3>BEFORE Logout:</h3>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'None') . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green;'><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
}
if (isset($_SESSION['admin'])) {
    echo "<p style='color:green;'><strong>Admin:</strong> " . $_SESSION['admin'] . "</p>";
}

echo "<p><strong>All Session Data:</strong></p>";
echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
if (empty($_SESSION)) {
    echo "Session is EMPTY";
} else {
    print_r($_SESSION);
}
echo "</pre>";

echo "<p><strong>Session Cookie:</strong> ";
if (isset($_COOKIE[session_name()])) {
    echo "<span style='color:green;'>" . $_COOKIE[session_name()] . "</span>";
} else {
    echo "<span style='color:red;'>Not set</span>";
}
echo "</p>";
echo "</div>";

// Perform logout if requested
if (isset($_GET['logout'])) {
    echo "<div class='info'>";
    echo "<h3>Performing Logout...</h3>";
    
    require_once __DIR__ . '/includes/destroy_session.php';
    $destroyed_id = destroy_session_completely();
    
    echo "<p style='color:green;'><strong>Session destroyed! ID was: " . htmlspecialchars($destroyed_id) . "</strong></p>";
    echo "</div>";
    
    // Start a NEW session to check if old data is gone
    session_start();
    
    echo "<div class='success'>";
    echo "<h3>AFTER Logout (New Session):</h3>";
    echo "<p><strong>New Session ID:</strong> " . (session_id() ?: 'None') . "</p>";
    echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";
    
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color:red;'><strong>ERROR: User ID still exists: " . $_SESSION['user_id'] . "</strong></p>";
    } else {
        echo "<p style='color:green;'><strong>✓ User ID cleared</strong></p>";
    }
    
    if (isset($_SESSION['admin'])) {
        echo "<p style='color:red;'><strong>ERROR: Admin still exists: " . $_SESSION['admin'] . "</strong></p>";
    } else {
        echo "<p style='color:green;'><strong>✓ Admin cleared</strong></p>";
    }
    
    echo "<p><strong>All Session Data:</strong></p>";
    echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
    if (empty($_SESSION)) {
        echo "Session is EMPTY - SUCCESS!";
    } else {
        print_r($_SESSION);
    }
    echo "</pre>";
    
    echo "<p><strong>Session Cookie:</strong> ";
    if (isset($_COOKIE[session_name()])) {
        echo "<span style='color:orange;'>" . $_COOKIE[session_name()] . " (New session cookie)</span>";
    } else {
        echo "<span style='color:green;'>Not set - SUCCESS!</span>";
    }
    echo "</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p><a href='test_logout_direct.php'>Test Again</a></p>";
    echo "<p><a href='test_session_destroy.php'>Back to Session Test</a></p>";
    
} else {
    echo "<hr>";
    echo "<h3>Actions:</h3>";
    echo "<p><a href='test_logout_direct.php?logout=1' onclick='return confirm(\"This will destroy your session. Continue?\")'>Perform Logout Now</a></p>";
    echo "<p><a href='test_session_destroy.php'>Back to Session Test</a></p>";
}

echo "</div></body></html>";
?>


