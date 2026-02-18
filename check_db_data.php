<?php
include 'includes/db.php';
$res = mysqli_query($conn, "SELECT * FROM callback_requests");
echo "<h3>Current Callback Requests in DB:</h3>";
echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Name</th><th>Phone</th><th>Status</th></tr>";
while($row = mysqli_fetch_assoc($res)) {
    echo "<tr><td>{$row['id']}</td><td>" . ($row['user_id'] ?? 'NULL') . "</td><td>{$row['name']}</td><td>{$row['phone']}</td><td>{$row['status']}</td></tr>";
}
echo "</table>";
?>
