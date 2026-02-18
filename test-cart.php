<?php
session_start();

echo "Session ID: " . session_id() . "<br>";
echo "Cart exists: " . (isset($_SESSION['cart']) ? 'Yes' : 'No') . "<br>";

if (isset($_SESSION['cart'])) {
    echo "Cart items: " . count($_SESSION['cart']) . "<br>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
} else {
    echo "Cart is empty";
}
?>
