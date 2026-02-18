<?php
session_start();
$_SESSION['user_id'] = 2; // bhagtaniishika9@gmail.com
header("Location: view_receipt.php?id=14");
exit;
?>
