<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to update your profile']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = $_POST;

// Validate required fields (email is not required since it's disabled)
if(empty($data['first_name']) || empty($data['last_name']) || empty($data['mobile_no']) || 
   empty($data['city']) || empty($data['state']) || empty($data['zipcode'])) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Validate mobile number - must be exactly 10 digits
if(!preg_match('/^[0-9]{10}$/', $data['mobile_no'])) {
    echo json_encode(['status' => 'error', 'message' => 'Mobile number must be exactly 10 digits']);
    exit;
}

// Get current user email from database (email cannot be changed)
$get_user = "SELECT email FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $get_user);
if($user_result && mysqli_num_rows($user_result) > 0) {
    $current_user = mysqli_fetch_assoc($user_result);
    $email = $current_user['email']; // Use existing email, don't allow changes
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// Sanitize inputs
$first_name = mysqli_real_escape_string($conn, $data['first_name']);
$last_name = mysqli_real_escape_string($conn, $data['last_name']);
$mobile_no = mysqli_real_escape_string($conn, $data['mobile_no']);
$city = mysqli_real_escape_string($conn, $data['city']);
$state = mysqli_real_escape_string($conn, $data['state']);
$zipcode = mysqli_real_escape_string($conn, $data['zipcode']);

// Update user profile (email is NOT updated - it remains unchanged)
// Note: Column name is 'zip' not 'zipcode' based on register.php
$update_query = "UPDATE users SET 
    first_name = '$first_name',
    last_name = '$last_name',
    mobile_no = '$mobile_no',
    city = '$city',
    state = '$state',
    zip = '$zipcode'
    WHERE id = '$user_id'";

$result = mysqli_query($conn, $update_query);

if ($result) {
    // Update session data
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['user_email'] = $email;
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Profile updated successfully',
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email']
    ]);
} else {
    $error_message = mysqli_error($conn);
    error_log("Profile update error: " . $error_message);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to update profile. Please check if all fields are valid. Error: ' . $error_message
    ]);
}

mysqli_close($conn);
?>

