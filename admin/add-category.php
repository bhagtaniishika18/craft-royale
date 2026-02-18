<?php
include("../includes/db.php");

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $image = $_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/categories/".$image);

    mysqli_query($conn, "INSERT INTO categories (name, image) VALUES ('$name', '$image')");
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Category Name" required>
    <input type="file" name="image" required>
    <button name="submit">Add Category</button>
</form>
