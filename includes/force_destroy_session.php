<?php
/**
 * Force Session Destruction - More Aggressive Approach
 * This file uses a different approach to ensure session is completely destroyed
 */

function force_destroy_session() {
    // Get session info before starting
    $session_name = session_name();
    
    // Start session if needed
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get all session info
    $session_id = session_id();
    $params = session_get_cookie_params();
    
    // Clear all session data
    $_SESSION = array();
    
    // Destroy session
    session_destroy();
    
    // Close session
    if (function_exists('session_write_close')) {
        @session_write_close();
    }
    
    // Delete session file - find it first
    if (!empty($session_id)) {
        // Get session save path
        $save_path = session_save_path();
        if (empty($save_path)) {
            $save_path = ini_get('session.save_path');
        }
        if (empty($save_path)) {
            $save_path = sys_get_temp_dir();
        }
        
        // Try to find and delete session file
        $session_file = rtrim($save_path, '/\\') . '/sess_' . $session_id;
        if (file_exists($session_file)) {
            @unlink($session_file);
        }
        
        // Also try XAMPP specific paths
        $xampp_paths = array(
            'C:/xampp/tmp/sess_' . $session_id,
            'C:/xampp/php/tmp/sess_' . $session_id,
            sys_get_temp_dir() . '/sess_' . $session_id
        );
        
        foreach ($xampp_paths as $file_path) {
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
    }
    
    // Delete cookies - use output buffering to ensure headers can be sent
    $expire = time() - 3600;
    
    // Delete session cookie multiple ways
    if (!empty($session_name)) {
        // Clear from superglobal first
        unset($_COOKIE[$session_name]);
        
        // Delete cookie with various parameters
        setcookie($session_name, '', $expire, '/');
        setcookie($session_name, '', $expire, $params["path"]);
        setcookie($session_name, '', $expire, '/', $params["domain"]);
        setcookie($session_name, '', $expire);
        
        // Use header to set cookie (more direct)
        if (!headers_sent()) {
            header('Set-Cookie: ' . $session_name . '=; expires=' . gmdate('D, d M Y H:i:s', $expire) . ' GMT; path=/; domain=' . $params["domain"]);
            header('Set-Cookie: ' . $session_name . '=; expires=' . gmdate('D, d M Y H:i:s', $expire) . ' GMT; path=/');
        }
    }
    
    // Clear all PHPSESSID cookies
    foreach ($_COOKIE as $key => $value) {
        if (stripos($key, 'PHPSESSID') !== false || $key === $session_name) {
            setcookie($key, '', $expire, '/');
            setcookie($key, '', $expire);
            unset($_COOKIE[$key]);
        }
    }
    
    return $session_id;
}

?>



