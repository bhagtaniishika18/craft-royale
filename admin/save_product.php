$name = $_POST['product_name'];
$price = $_POST['price'];
$cat = $_POST['category_id'];
$sub = $_POST['subcategory_id'];

$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];
move_uploaded_file($tmp,"../uploads/$image");

mysqli_query($conn,"
INSERT INTO products
(category_id, subcategory_id, product_name, price, image)
VALUES ('$cat','$sub','$name','$price','$image')
");
