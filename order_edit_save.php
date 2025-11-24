<?php
require 'db.php';
session_start();

$order_id = intval($_POST['order_id']);

// Update existing items
if (!empty($_POST['qty'])) {
    foreach ($_POST['qty'] as $item_id => $new_qty) {

        // get old qty + product id
        $old = $mysqli->query("SELECT product_id, quantity FROM order_items WHERE id = $item_id")->fetch_assoc();

        $pid = $old['product_id'];
        $old_qty = $old['quantity'];

        // adjust inventory
        $difference = $new_qty - $old_qty;
        $mysqli->query("UPDATE products SET stock = stock - $difference WHERE id = $pid");

        // update order item
        $stmt = $mysqli->prepare("UPDATE order_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $item_id);
        $stmt->execute();
    }
}

// Delete items
if (!empty($_POST['delete'])) {
    foreach ($_POST['delete'] as $item_id) {

        $old = $mysqli->query("SELECT product_id, quantity FROM order_items WHERE id = $item_id")->fetch_assoc();
        $pid = $old['product_id'];
        $old_qty = $old['quantity'];

        // reverse quantity back to stock
        $mysqli->query("UPDATE products SET stock = stock + $old_qty WHERE id = $pid");

        // delete item
        $mysqli->query("DELETE FROM order_items WHERE id = $item_id");
    }
}

// Add new product
if (!empty($_POST['new_product_id']) && $_POST['new_quantity'] > 0) {
    $pid = intval($_POST['new_product_id']);
    $qty = intval($_POST['new_quantity']);

    $mysqli->query("INSERT INTO order_items (order_id, product_id, quantity) VALUES ($order_id, $pid, $qty)");
    $mysqli->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
}

header("Location: order_view.php?id=" . $mysqli->query("SELECT franchise_id FROM orders WHERE id = $order_id")->fetch_assoc()['franchise_id']);
exit;
