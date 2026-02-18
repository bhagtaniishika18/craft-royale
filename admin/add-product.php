<?php
include("../includes/db.php");

// fetch categories (YOUR table)
$categories = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['submit'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $image = $_FILES['image']['name'];

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        "../uploads/products/" . $image
    );

    mysqli_query($conn, "
        INSERT INTO products (category_id, product_name, price, image, description)
        VALUES ('$category_id', '$name', '$price', '$image', '$desc')
    ");
}
?>

<h2>Add Product</h2>

<form method="post" enctype="multipart/form-data">
    <label>Category</label>
    <select name="category_id" required>
        <?php while ($row = mysqli_fetch_assoc($categories)) { ?>
            <option value="<?= $row['id'] ?>">
                <?= $row['category_name'] ?>
            </option>
        <?php } ?>
    </select>

    <input type="text" name="product_name" placeholder="Product Name" required>
    <input type="number" name="price" placeholder="Price" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="file" name="image" required>

    <button type="submit" name="submit">Add Product</button>
</form>
