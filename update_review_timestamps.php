<?php
include 'includes/db.php';

// Update review timestamps to show different times
// This will make each review show a different time display

$updates = [
    ['id' => 1, 'interval' => '0 MINUTE', 'label' => 'Just now'],
    ['id' => 2, 'interval' => '38 MINUTE', 'label' => '38 mins ago'],
    ['id' => 3, 'interval' => '2 HOUR', 'label' => '2 hrs ago'],
    ['id' => 4, 'interval' => '1 DAY', 'label' => '1 day ago'],
    ['id' => 5, 'interval' => '2 DAY', 'label' => '2 days ago'],
    ['id' => 6, 'interval' => '1 WEEK', 'label' => '1 week ago'],
];

echo "<h2>Updating Review Timestamps...</h2>";
echo "<p>This will update the timestamps so reviews show different times.</p><br>";

$success_count = 0;
$error_count = 0;

foreach ($updates as $update) {
    $sql = "UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL {$update['interval']}) WHERE id = {$update['id']}";
    
    if (mysqli_query($conn, $sql)) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE id = {$update['id']}");
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] > 0) {
            echo "✅ Review ID {$update['id']} updated - will show: <strong>{$update['label']}</strong><br>";
            $success_count++;
        } else {
            echo "⚠️ Review ID {$update['id']} doesn't exist (skipped)<br>";
        }
    } else {
        echo "❌ Error updating review ID {$update['id']}: " . mysqli_error($conn) . "<br>";
        $error_count++;
    }
}

echo "<br><hr>";
echo "<h3>Summary:</h3>";
echo "✅ Successfully updated: <strong>{$success_count}</strong> reviews<br>";
echo "❌ Errors: <strong>{$error_count}</strong><br>";
echo "⚠️ Skipped (review doesn't exist): <strong>" . (count($updates) - $success_count - $error_count) . "</strong><br>";

echo "<br><p><strong>Done!</strong> Now refresh your index.php page to see the updated times.</p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";

mysqli_close($conn);
?>











