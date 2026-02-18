<?php
include "includes/db.php"; // This now sets utf8mb4

echo "<h1>Correcting encoding...</h1>";

$res = mysqli_query($conn, "SELECT id, product_name, product_image FROM order_items");
while ($row = mysqli_fetch_assoc($res)) {
    // Replace the specific bytes that represent '?' in broken encoding
    // These are often E2 80 93 for en-dash etc.
    $new_name = str_replace(['?', '–', '—', '’', '“', '”'], '-', $row['product_name']);
    $new_img = str_replace(['?', '–', '—', '’', '“', '”'], '-', $row['product_image']);
    
    if ($new_name !== $row['product_name'] || $new_img !== $row['product_image']) {
        $stmt = mysqli_prepare($conn, "UPDATE order_items SET product_name = ?, product_image = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $new_name, $new_img, $row['id']);
        mysqli_stmt_execute($stmt);
        echo "Updating ID: " . $row['id'] . "<br>";
    }
}
echo "Finished correctly.";
?>
