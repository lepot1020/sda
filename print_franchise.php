<?php
require 'db.php';

if (!isset($_GET['franchise_id']) || !isset($_GET['date'])) {
    die("Error: Missing required parameters (franchise_id, date).");
}

$franchise_id = intval($_GET['franchise_id']);
$date = $_GET['date'];

// Fetch franchise info
$stmt = $mysqli->prepare("SELECT * FROM franchisee WHERE id = ?");
$stmt->bind_param("i", $franchise_id);
$stmt->execute();
$franchise = $stmt->get_result()->fetch_assoc();

if (!$franchise) {
    die("Error: Franchise not found.");
}

// Fetch orders
$order_stmt = $mysqli->prepare("
    SELECT * FROM orders 
    WHERE franchise_id = ? AND DATE(created_at) = ?
");
$order_stmt->bind_param("is", $franchise_id, $date);
$order_stmt->execute();
$orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Print Report</title>
<style>
body { font-family: Arial; }
.print-container { width: 700px; margin: auto; }
h2 { text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
table, th, td { border: 1px solid black; }
th, td { padding: 8px; text-align: left; }
.print-btn { display: none; }
</style>
</head>
<body>

<div class="print-container">
    <h2>Franchise Report</h2>
    <p><strong>Franchise Name:</strong> <?= $franchise['name']; ?></p>
    <p><strong>Date:</strong> <?= $date; ?></p>

    <table>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Description</th>
            <th>Date</th>
        </tr>

        <?php if (empty($orders)): ?>
            <tr><td colspan="4" style="text-align:center;">No data</td></tr>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['product_name']; ?></td>
                <td><?= $o['quantity']; ?></td>
                <td><?= $o['notes']; ?></td>
                <td><?= $o['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<script>
window.print();
</script>

</body>
</html>
