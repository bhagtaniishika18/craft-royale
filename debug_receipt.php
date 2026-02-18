<?php
include "includes/db.php";
$order_id = 14;
echo "<h1>Debugging Order #$order_id</h1>";

$res = mysqli_query($conn, "SELECT * FROM orders WHERE id=$order_id");
$order = mysqli_fetch_assoc($res);
if (!$order) {
    echo "Order not found!";
} else {
    echo "Order Number: " . $order['order_number'] . "<br>";
    echo "Total: " . $order['total_amount'] . "<br>";
    
    $res2 = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id=$order_id");
    echo "Item count: " . mysqli_num_rows($res2) . "<br>";
    while ($row = mysqli_fetch_assoc($res2)) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
}
?>
