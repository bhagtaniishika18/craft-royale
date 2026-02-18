<?php include("includes/db.php"); ?>

<h2>Categories</h2>

<div class="grid">
<?php
$res = mysqli_query($conn, "SELECT * FROM categories");
while ($row = mysqli_fetch_assoc($res)) {
?>
    <a href="products.php?cat=<?= $row['id'] ?>">
        <div class="cat-box">
            <?= $row['category_name'] ?>
        </div>
    </a>
<?php } ?>
</div>
