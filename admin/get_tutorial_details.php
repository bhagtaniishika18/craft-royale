<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$tutorial_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tutorial_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tutorial ID']);
    exit;
}

// Fetch tutorial details
$tutorial_query = "SELECT * FROM tutorials WHERE id = $tutorial_id";
$tutorial_result = mysqli_query($conn, $tutorial_query);
$tutorial = mysqli_fetch_assoc($tutorial_result);

if (!$tutorial) {
    echo json_encode(['status' => 'error', 'message' => 'Tutorial not found']);
    exit;
}

// Fix video path
$video_path = !empty($tutorial['video']) ? '../uploads/tutorials/' . $tutorial['video'] : '';
if (!empty($tutorial['video']) && !file_exists($video_path)) {
    $video_path = '../uploads/tutorials/' . $tutorial['video'];
}
$tutorial['video_path'] = $video_path;

// Format date
$tutorial['formatted_date'] = date('F j, Y', strtotime($tutorial['created_at']));

echo json_encode([
    'status' => 'success',
    'tutorial' => $tutorial
]);
?>
