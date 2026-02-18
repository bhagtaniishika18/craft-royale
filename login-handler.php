<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

$data = $_POST;

// Validate required fields
if(empty($data['email']) || empty($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

// Sanitize email
$email = mysqli_real_escape_string($conn, $data['email']);

// Get user from database
$query = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    
    // Verify password
    if(password_verify($data['password'], $user['password'])) {
        // Regenerate session ID to prevent session fixation attacks
        // This ensures a new session ID is created on login
        session_regenerate_id(true);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful',
            'user_name' => $_SESSION['user_name'],
            'user_email' => $_SESSION['user_email']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
}
?>

