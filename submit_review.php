<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $rating = intval($_POST['rating']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $product_category = isset($_POST['product_category']) ? mysqli_real_escape_string($conn, $_POST['product_category']) : '';
    $experience = isset($_POST['experience']) ? mysqli_real_escape_string($conn, $_POST['experience']) : '';
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
        $upload_dir = 'uploads/reviews/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $image_name = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['review_image']['tmp_name'], $upload_path)) {
                // Image uploaded successfully
            } else {
                $image_name = '';
            }
        }
    }
    
    // Insert review
    $query = "INSERT INTO reviews (name, email, rating, feedback, product_category, experience, image, message, status) 
              VALUES ('$name', '$email', $rating, '$feedback', '$product_category', '$experience', '$image_name', '$message', 'pending')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: reviews.php?success=1");
        exit;
    } else {
        header("Location: reviews.php?error=1");
        exit;
    }
} else {
    header("Location: reviews.php");
    exit;
}
?>


