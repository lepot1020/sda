<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$fid  = intval($_GET['fid']);
$date = $_GET['date'];

$mysqli->query("DELETE FROM orders WHERE franchise_id = $fid AND DATE(created_at) = '$date'");

header("Location: order_list.php?deleted=1");
exit;
?>
