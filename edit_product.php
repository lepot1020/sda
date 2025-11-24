<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

// Get existing product
$stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

$message = "";

// Save updated product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];

    $update = $mysqli->prepare("UPDATE products SET product_name=?, category=?, quantity=? WHERE id=?");
    $update->bind_param("ssii", $product_name, $category, $quantity, $id);

    if ($update->execute()) {
        header("Location: product_list.php?updated=1");
        exit;
    } else {
        $message = "Failed to update product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .container {
            background:#fff; width:400px; margin:40px auto; padding:20px;
            border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,.2);
        }
        input, select {
            width:100%; padding:12px; margin:8px 0; font-size:15px;
        }
        button {
            padding:12px; width:100%; background:#333; color:white; border:none;
            cursor:pointer; font-size:16px;
        }
        button:hover { background:#555; }
        a { text-decoration:none; }
        body { 
    font-family: Arial, sans-serif; 
    background:#eef1f5; 
    margin:0;
    padding:0;
}

.container {
    background:#fff;
    width:420px;
    margin:50px auto;
    padding:25px 30px;
    border-radius:12px;
    box-shadow:0px 8px 18px rgba(0,0,0,0.15);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}

h2 {
    margin-top:0;
    text-align:center;
    font-weight:bold;
    color:#333;
    padding-bottom:10px;
}

label {
    font-weight:bold;
    color:#333;
    font-size:14px;
}

input, select {
    width:100%;
    padding:12px;
    margin-top:5px;
    margin-bottom:15px;
    font-size:15px;
    border-radius:6px;
    border:1px solid #ccc;
    outline:none;
    transition:0.2s;
    background:#fafafa;
}

input:focus, select:focus {
    border-color:#007bff;
    background:white;
    box-shadow:0px 0px 5px rgba(0,123,255,0.3);
}

button {
    padding:12px;
    width:100%;
    background:#28a745; /* GREEN */
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
    font-weight:bold;
    transition:0.2s ease;
}

button:hover {
    background:#1f8a39; /* DARKER GREEN */
}


a {
    text-decoration:none;
    display:block;
    text-align:center;
    padding:12px;
    margin-top:10px;
    background:#28a745; /* GREEN */
    color:white;
    border-radius:6px;
    font-weight:bold;
    transition:0.2s ease;
}

a:hover {
    background:#1f8a39; /* DARKER GREEN */
}


    </style>
</head>
<body>

<div class="container">
    <h2>Edit Product</h2>

    <?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>

    <form method="POST">

        <label>Product Name:</label>
        <input type="text" name="product_name" value="<?= $product['product_name']; ?>" required>

        <label>Category:</label>
        <select name="category" required>
            <option <?= $product['category']=='Drums'?'selected':'' ?>>Drums</option>
            <option <?= $product['category']=='Carbouy'?'selected':'' ?>>Carbouy</option>
            <option <?= $product['category']=='Liters'?'selected':'' ?>>Liters</option>
            <option <?= $product['category']=='Spray'?'selected':'' ?>>Spray</option>
            <option <?= $product['category']=='500ml spray'?'selected':'' ?>>500ml spray</option>
            <option <?= $product['category']=='250ml spray'?'selected':'' ?>>250ml spray</option>
            <option <?= $product['category']=='100ml Spray'?'selected':'' ?>>100ml Spray</option>
            <option <?= $product['category']=='Bottles'?'selected':'' ?>>Bottles</option>
            <option <?= $product['category']=='Sacks'?'selected':'' ?>>Sacks</option>
            <option <?= $product['category']=='Box'?'selected':'' ?>>Box</option>
            <option <?= $product['category']=='Gallon'?'selected':'' ?>>Gallon</option>
            <option <?= $product['category']=='Packs'?'selected':'' ?>>Packs</option>
            <option <?= $product['category']=='Bundles'?'selected':'' ?>>Bundles</option>
            <option <?= $product['category']=='Perfume'?'selected':'' ?>>Perfume</option>
            <option <?= $product['category']=='Misc'?'selected':'' ?>>Misc</option>
            <option <?= $product['category']=='Others'?'selected':'' ?>>Others</option>


        </select>

        <label>Quantity:</label>
        <input type="number" name="quantity" min="1" value="<?= $product['quantity']; ?>" required>

        <button type="submit">Save Changes</button>

    </form>

    <br>
    <a href="product_list.php">⬅ Back to Product List</a>

</div>

</body>
</html>
