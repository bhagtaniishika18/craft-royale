<?php
include 'includes/db.php';

header('Content-Type: application/json');

$data = $_POST;

// Validate required fields
if(empty($data['first_name']) || empty($data['last_name']) || empty($data['mobile_no']) || 
   empty($data['email']) || empty($data['password']) || empty($data['city']) || 
   empty($data['state']) || empty($data['zipcode'])) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Validate mobile number - must be exactly 10 digits
if(!preg_match('/^[0-9]{10}$/', $data['mobile_no'])) {
    echo json_encode(['status' => 'error', 'message' => 'Mobile number must be exactly 10 digits']);
    exit;
}

// Check if email already exists
$checkEmail = "SELECT id FROM users WHERE email = '{$data['email']}'";
$result = mysqli_query($conn, $checkEmail);
if(mysqli_num_rows($result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

// Hash password
$pass = password_hash($data['password'], PASSWORD_DEFAULT);

// Sanitize inputs
$first_name = mysqli_real_escape_string($conn, $data['first_name']);
$last_name = mysqli_real_escape_string($conn, $data['last_name']);
$mobile_no = mysqli_real_escape_string($conn, $data['mobile_no']);
$email = mysqli_real_escape_string($conn, $data['email']);
$city = mysqli_real_escape_string($conn, $data['city']);
$state = mysqli_real_escape_string($conn, $data['state']);
$zipcode = mysqli_real_escape_string($conn, $data['zipcode']);

$q = "INSERT INTO users 
(first_name, last_name, mobile_no, email, password, city, state, zip)
VALUES (
'$first_name',
'$last_name',
'$mobile_no',
'$email',
'$pass',
'$city',
'$state',
'$zipcode'
)";

if(mysqli_query($conn, $q)){
    echo json_encode(['status' => 'success', 'message' => 'Registered successfully']);
}else{
    echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . mysqli_error($conn)]);
}
