<?php
include 'includes/db.php';
header('Content-Type: application/json');

$query = "DESCRIBE gift_cards";
$result = mysqli_query($conn, $query);
$structure = [];
while ($row = mysqli_fetch_assoc($result)) {
    $structure[] = $row;
}

$query = "SELECT id, card_number, pin, status, amount FROM gift_cards LIMIT 5";
$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode(['structure' => $structure, 'sample_data' => $data]);
?>
