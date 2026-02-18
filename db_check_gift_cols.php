<?php
include "includes/db.php";
$res = mysqli_query($conn, "SHOW COLUMNS FROM gift_card_transactions");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
