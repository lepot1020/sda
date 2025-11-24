<?php
$mysqli = new mysqli("localhost", "root", "", "management_system");
$mysqli->query("SET time_zone = '+08:00'");

if ($mysqli->connect_error) {
    die("Database Connection Failed: " . $mysqli->connect_error);
}
?>
