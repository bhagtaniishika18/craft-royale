<?php
include '../includes/db.php';
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM return_refund_requests LIKE 'request_type'");
if ($check_column && mysqli_num_rows($check_column) > 0) {
    echo "COLUMN EXISTS";
} else {
    echo "COLUMN MISSING";
}
?>
