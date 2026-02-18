<?php
include 'includes/db.php';
$q = mysqli_query($conn, "SELECT card_number, pin FROM gift_cards WHERE status='active' LIMIT 1");
if ($r = mysqli_fetch_assoc($q)) {
    echo "Valid Card: " . $r['card_number'] . " PIN: " . $r['pin'];
} else {
    echo "No active gift cards found!";
}
?>
