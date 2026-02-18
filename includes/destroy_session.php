<?php
/**
 * Complete Session Destruction Utility
 * Destroys session, clears cookies, and prevents back button access
 */

function destroy_session_completely() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get session information BEFORE destroying
    $session_id = session_id();
    $session_name = session_name();
    $params = session_get_cookie_params();
    $save_path = session_save_path();
    
    if (empty($save_path)) {
        $save_path = ini_get('session.save_path');
    }
    if (empty($save_path)) {
        $save_path = sys_get_temp_dir();
    }
    
    // Clear all session variables
    $_SESSION = array();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['admin']);
    
    // Write empty session and close
    session_write_close();
    
    // Start session again to destroy it properly
    session_start();
    
    // Destroy the session
    session_destroy();
    
    // Delete session file from all possible locations
    if (!empty($session_id)) {
        $paths_to_check = array(
            rtrim($save_path, '/\\') . '/sess_' . $session_id,
            'C:/xampp/tmp/sess_' . $session_id,
            'C:/xampp/php/tmp/sess_' . $session_id,
            sys_get_temp_dir() . '/sess_' . $session_id,
            ini_get('session.save_path') . '/sess_' . $session_id
        );
        
        foreach ($paths_to_check as $file_path) {
            if (file_exists($file_path) && is_file($file_path)) {
                @unlink($file_path);
            }
        }
    }
    
    // Clear cookies - use multiple methods
    $expire = time() - 86400;
    
    // Clear from $_COOKIE superglobal
    if (isset($_COOKIE[$session_name])) {
        unset($_COOKIE[$session_name]);
    }
    
    // Delete cookie with all possible parameter combinations
    if (!empty($session_name)) {
        setcookie($session_name, '', $expire, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        setcookie($session_name, '', $expire, '/', $params["domain"], $params["secure"], $params["httponly"]);
        setcookie($session_name, '', $expire, '/');
        setcookie($session_name, '', $expire);
        
        // Use header for more reliable cookie deletion
        if (!headers_sent()) {
            header('Set-Cookie: ' . $session_name . '=; expires=' . gmdate('D, d M Y H:i:s', $expire) . ' GMT; path=/; domain=' . $params["domain"]);
            header('Set-Cookie: ' . $session_name . '=; expires=' . gmdate('D, d M Y H:i:s', $expire) . ' GMT; path=/');
            header('Set-Cookie: ' . $session_name . '=; expires=' . gmdate('D, d M Y H:i:s', $expire) . ' GMT; path=' . $params["path"]);
        }
    }
    
    // Clear all session-related cookies
    $cookie_keys = array_keys($_COOKIE);
    foreach ($cookie_keys as $key) {
        if (stripos($key, 'PHPSESSID') !== false || $key === $session_name || stripos($key, 'session') !== false) {
            setcookie($key, '', $expire, '/');
            setcookie($key, '', $expire);
            unset($_COOKIE[$key]);
        }
    }
    
    return $session_id;
}

?>
