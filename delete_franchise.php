<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Missing franchise ID.");
}

$id = intval($_GET['id']);

$delete = $mysqli->prepare("DELETE FROM franchisees WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();

header("Location: franchise_list.php?deleted=1");
exit;
?>
