<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');

if (isset($_GET['id'])) {
    $tutorial_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get tutorial info to delete video file
    $result = mysqli_query($conn, "SELECT video FROM tutorials WHERE id = '$tutorial_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $tutorial = mysqli_fetch_assoc($result);
        
        // Delete video file if exists
        if (!empty($tutorial['video']) && file_exists("../uploads/tutorials/" . $tutorial['video'])) {
            unlink("../uploads/tutorials/" . $tutorial['video']);
        }
        
        // Delete from database
        $delete_query = "DELETE FROM tutorials WHERE id = '$tutorial_id'";
        if (mysqli_query($conn, $delete_query)) {
            header("Location: manage_tutorials.php?success=deleted");
        } else {
            header("Location: manage_tutorials.php?error=" . urlencode("Failed to delete tutorial"));
        }
    } else {
        header("Location: manage_tutorials.php?error=" . urlencode("Tutorial not found"));
    }
} else {
    header("Location: manage_tutorials.php");
}
exit();
?>






