<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db.php');

if (isset($_REQUEST['id'])) {
    $id = mysqli_real_escape_string($conn, $_REQUEST['id']);
    
    // Get blog post to delete image
    $result = mysqli_query($conn, "SELECT image FROM blog_posts WHERE id = '$id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $blog = mysqli_fetch_assoc($result);
        
        // Delete image if exists
        if (!empty($blog['image']) && file_exists("../uploads/blogs/" . $blog['image'])) {
            unlink("../uploads/blogs/" . $blog['image']);
        }
    }
    
    // Delete blog post
    $delete_query = "DELETE FROM blog_posts WHERE id = '$id'";
    $result = mysqli_query($conn, $delete_query);
    
    if ($result) {
        header("Location: view_blogs.php?success=deleted");
    } else {
        header("Location: view_blogs.php?error=" . urlencode("Failed to delete blog post"));
    }
} else {
    header("Location: view_blogs.php?error=" . urlencode("Invalid request"));
}
exit();
?>







