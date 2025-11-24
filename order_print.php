<?php
require 'db.php';
session_start();

$order_id = intval($_GET['id']);

$order = $mysqli->query("
    SELECT o.*, f.name AS franchise_name, f.area
    FROM orders o
    LEFT JOIN franchise f ON f.id = o.franchise_id
    WHERE o.id = $order_id
")->fetch_assoc();

$items = $mysqli->query("
    SELECT oi.quantity, p.product_name, p.unit, p.category
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
?>

<style>
body { font-family: Arial; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
td, th { border: 1px solid black; padding: 8px; }
.print-btn { padding: 10px; background: black; color: white; }
</style>

<button onclick="window.print()" class="print-btn">PRINT</button>

<h2>Order Receipt</h2>

<p><b>Franchisee:</b> <?= $order['franchise_name'] ?></p>
<p><b>Area:</b> <?= $order['area'] ?></p>
<p><b>Date:</b> <?= $order['order_date'] ?></p>

<table>
<tr>
    <th>Product</th>
    <th>Category</th>
    <th>Unit</th>
    <th>Qty</th>
</tr>

<?php while($i = $items->fetch_assoc()): ?>
<tr>
    <td><?= $i['product_name'] ?></td>
    <td><?= $i['category'] ?></td>
    <td><?= $i['unit'] ?></td>
    <td><?= $i['quantity'] ?></td>
</tr>
<?php endwhile; ?>

</table>
