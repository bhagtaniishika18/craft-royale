<?php
include 'includes/db.php';

$sql = "SELECT * FROM reels ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$reels = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reels[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'video' => 'uploads/reels/' . $row['video_path'],
            'date' => date('d M, Y', strtotime($row['created_at']))
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($reels);
?>
