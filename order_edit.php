<?php
require 'db.php';
session_start();

$order_id = intval($_GET['id']);

// Get order info
$order = $mysqli->query("
    SELECT o.*, f.name AS franchise_name, f.area
    FROM orders o
    LEFT JOIN franchise f ON f.id = o.franchise_id
    WHERE o.id = $order_id
")->fetch_assoc();

// Get all products
$products = $mysqli->query("SELECT * FROM products");

// Get order items
$items = $mysqli->query("
    SELECT oi.*, p.product_name, p.unit, p.category
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
?>

<h2>Edit Order #<?= $order['id'] ?> — <?= $order['franchise_name'] ?> (<?= $order['area'] ?>)</h2>

<form method="POST" action="order_edit_save.php">

<input type="hidden" name="order_id" value="<?= $order_id ?>">

<table border="1" cellpadding="8">
<tr>
    <th>Product</th>
    <th>Category</th>
    <th>Unit</th>
    <th>Quantity</th>
    <th>Delete</th>
</tr>

<?php while($row = $items->fetch_assoc()): ?>
<tr>
    <td><?= $row['product_name'] ?></td>
    <td><?= $row['category'] ?></td>
    <td><?= $row['unit'] ?></td>
    <td>
        <input type="number" name="qty[<?= $row['id'] ?>]" value="<?= $row['quantity'] ?>" min="1">
    </td>
    <td>
        <input type="checkbox" name="delete[]" value="<?= $row['id'] ?>">
    </td>
</tr>
<?php endwhile; ?>

</table>

<br><br>

<h3>Add New Product</h3>

<select name="new_product_id">
    <option value="">-- Select Product --</option>
    <?php while($p = $products->fetch_assoc()): ?>
        <option value="<?= $p['id'] ?>">
            <?= $p['product_name'] ?> (<?= $p['category'] ?> - <?= $p['unit'] ?>)
        </option>
    <?php endwhile; ?>
</select>

<input type="number" name="new_quantity" min="1" placeholder="Qty">

<br><br>
<button type="submit">Save Changes</button>

</form>
