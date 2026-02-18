<?php
// Simple test file to verify cart sidebar is accessible
session_start();

echo "<h2>Cart Sidebar Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Cart exists: " . (isset($_SESSION['cart']) ? 'Yes' : 'No') . "</p>";

if (isset($_SESSION['cart'])) {
    echo "<p>Cart items: " . count($_SESSION['cart']) . "</p>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
} else {
    echo "<p>Cart is empty or not set</p>";
}

echo "<hr>";
echo "<p>If you can see this, the file is accessible.</p>";
echo "<p>Cart is now loaded directly in header.php - no separate file needed.</p>";
