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

// GET FRANCHISEE INFO
$stmt = $mysqli->prepare("SELECT franchisee_name, area, type FROM franchisees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die("Franchisee not found.");
}

$stmt->bind_result($name, $area, $type);
$stmt->fetch();

// UPDATE WHEN FORM SUBMITTED
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['name'];
    $new_area = $_POST['area'];
    $new_type = $_POST['type'];

    $update = $mysqli->prepare("UPDATE franchisees SET franchisee_name=?, area=?, type=? WHERE id=?");
    $update->bind_param("sssi", $new_name, $new_area, $new_type, $id);
    $update->execute();

    header("Location: franchise_list.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Franchisee</title>

<style>
    body {
        font-family: Arial;
        background: #f5f6f7;
    }
    .container {
        width: 450px;
        margin: 50px auto;
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    h2 {
        color: #27ae60;
        margin-bottom: 20px;
    }
    input, select {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
    }
    button {
        width: 100%;
        padding: 12px;
        border: none;
        background: #27ae60;
        color: white;
        font-size: 17px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
    }
    button:hover {
        background: #2ecc71;
    }
</style>

</head>
<body>

<div class="container">
    <h2>Edit Franchisee</h2>

    <form method="POST">
        <label>Franchisee Name</label>
        <input type="text" name="name" value="<?= $name ?>" required>

        <label>Area</label>
        <input type="text" name="area" value="<?= $area ?>" required>

        <label>Type</label>
        <select name="type" required>
            <option value="Kiosk" <?= $type == "Kiosk" ? "selected" : "" ?>>type</option>
            <option value="franchisee" <?= $type == "franchisee" ? "selected" : "" ?>>franchisee</option>
            <option value="dealer" <?= $type == "Store" ? "dealer" : "" ?>>dealer</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
