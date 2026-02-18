<?php
include "includes/db.php";

echo "<h1>Attempting to fix order_items table...</h1>";

// Try to drop it first if it's corrupted
$drop = mysqli_query($conn, "DROP TABLE IF EXISTS `order_items`") or die(mysqli_error($conn));
echo "<p>Table dropped (if existed).</p>";

// Create it fresh
$create = "CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `mrp` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $create)) {
    echo "<p style='color: green;'>Table recreated successfully!</p>";
} else {
    echo "<p style='color: red;'>Creation failed: " . mysqli_error($conn) . "</p>";
}

echo "<a href='view_receipt.php?id=" . (isset($_GET['id']) ? $_GET['id'] : '13') . "'>Return to Receipt</a>";
?>
