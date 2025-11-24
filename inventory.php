<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}

// Handle Form Submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);

    $stmt = $mysqli->prepare("INSERT INTO daily_stock (product_id, quantity, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $product_id, $qty);

    if ($stmt->execute()) {
        $msg = "✔ Daily stock added!";
    } else {
        $msg = "❌ Error saving stock.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory - Daily Stock</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:#f5f7fa;
    display:flex;
}
.sidebar{
    width:250px;
    background:#182848;
    height:100vh;
    padding:20px;
    position:fixed;
    color:white;
}
.sidebar h2{
    text-align:center;
    font-weight:600;
}
.sidebar a{
    display:block;
    padding:15px;
    margin-top:10px;
    text-decoration:none;
    color:#fff;
    border-radius:10px;
    transition:0.3s;
}
.sidebar a:hover{
    background:#4b6cb7;
}

.main{
    margin-left:250px;
    padding:30px;
    width:100%;
}

.form-box{
    background:white;
    padding:25px;
    border-radius:15px;
    width:450px;
    box-shadow:0px 5px 15px rgba(0,0,0,0.1);
}
.form-box h2{
    margin-top:0;
}
input, select{
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:10px;
    border:1px solid #ccc;
}
button{
    width:100%;
    padding:12px;
    border:none;
    background:#182848;
    color:#fff;
    font-size:16px;
    border-radius:10px;
    cursor:pointer;
    margin-top:15px;
}
button:hover{
    background:#4b6cb7;
}
.msg{
    margin-bottom:15px;
    padding:10px;
    border-radius:8px;
    background:#dff6dd;
    color:#2f6b2f;
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>My System</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="inventory.php" style="background:#4b6cb7;">Inventory (Daily Stock)</a>
    <a href="products.php">Products</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Daily Stock Input</h1>

    <div class="form-box">

        <?php if ($msg): ?>
            <div class="msg"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Product:</label>
            <select name="product_id" required>
                <option value="">Select Product</option>

                <?php
                $p = $mysqli->query("SELECT id, product_name FROM products ORDER BY product_name ASC");
                while ($row = $p->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= $row['product_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Quantity:</label>
            <input type="number" name="quantity" placeholder="Enter stock quantity" required>

            <button type="submit">Add Daily Stock</button>
        </form>
    </div>

</div>

</body>
</html>
