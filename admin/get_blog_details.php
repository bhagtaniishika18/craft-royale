<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($blog_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid blog ID']);
    exit;
}

// Fetch blog details
$blog_query = "SELECT * FROM blog_posts WHERE id = $blog_id";
$blog_result = mysqli_query($conn, $blog_query);
$blog = mysqli_fetch_assoc($blog_result);

if (!$blog) {
    echo json_encode(['status' => 'error', 'message' => 'Blog not found']);
    exit;
}

// Fix image path
$image_path = !empty($blog['image']) ? '../uploads/blogs/' . $blog['image'] : '../assets/images/beads.jpg';
if (!empty($blog['image']) && !file_exists($image_path)) {
    $image_path = '../assets/images/beads.jpg';
}
$blog['image_path'] = $image_path;

// Format date
$blog['formatted_date'] = date('F j, Y', strtotime($blog['created_at']));

echo json_encode([
    'status' => 'success',
    'blog' => $blog
]);
?>
