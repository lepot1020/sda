<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

/* SAVE PRODUCT TO DATABASE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];

    $stmt = $mysqli->prepare("INSERT INTO products (product_name, category, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $product_name, $category, $quantity);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $message = "Error saving product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Input</title>
    <style>
   /* GLOBAL */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #f6f7f9;
}

/* SIDEBAR (UNCHANGED) */
.sidebar {
    width: 230px;
    height: 100vh;
    background: #2c3e50;
    position: fixed;
    top: 0; left: 0;
    padding-top: 25px;
    box-shadow: 3px 0 8px rgba(0,0,0,0.2);
}
.sidebar h2 {
    color: #f39c12;
    text-align: center;
    margin-bottom: 25px;
    font-size: 22px;
}
.sidebar a {
    display: block;
    padding: 14px 20px;
    color: #ecf0f1;
    text-decoration: none;
    font-size: 16px;
    border-left: 5px solid transparent;
    transition: 0.3s;
}
.sidebar a:hover {
    background: #34495e;
    border-left: 5px solid #2ecc71;
}
.sidebar a.active {
    background: linear-gradient(90deg, #2ecc71, #27ae60);
    color: white;
    font-weight: bold;
    border-left: 6px solid #f39c12;
}

/* PAGE CONTENT WRAPPER */
.page-content {
    margin-left: 230px;
    padding: 40px;
    display: flex;
    justify-content: center;
}

/* FORM CARD */
.form-card {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    animation: fadeIn 0.3s ease-in-out;
}

/* Title */
.form-card h2 {
    margin-top: 0;
    text-align: center;
    color: #27ae60;
    font-size: 26px;
    margin-bottom: 20px;
}

/* Inputs */
.form-card input,
.form-card select {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    transition: 0.2s;
}
.form-card input:focus,
.form-card select:focus {
    border-color: #27ae60;
    box-shadow: 0 0 6px rgba(39,174,96,0.4);
}

/* Submit Button */
.form-card button {
    width: 100%;
    padding: 14px;
    font-size: 17px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    transition: 0.3s;
    font-weight: bold;
}
.form-card button:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 12px rgba(0,0,0,0.18);
}

/* Message Box */
.message {
    padding: 12px;
    background: #e8f8f0;
    border-left: 6px solid #27ae60;
    color: #27ae60;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}

/* Smooth Fade In Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}


</style>
</head>
<body>
<?php
    // Detect current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <!-- LOGO -->
    <div style="text-align:center; margin-bottom: 10px;">
        <img src="logo.png" 
             style="width: 90px; height: auto; border-radius: 10px;">
    </div>

    <h2>SABON DE AMOR</h2>

    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>

    <a href="franchise.php" class="<?= $current_page == 'franchise.php' ? 'active' : '' ?>">Add Franchise</a>
    <a href="franchise_list.php" class="<?= $current_page == 'franchise_list.php' ? 'active' : '' ?>">Franchise List</a>

    <a href="product_list.php" class="<?= $current_page == 'product_list.php' ? 'active' : '' ?>">Product List</a>

    <a href="order.php" class="<?= $current_page == 'order.php' ? 'active' : '' ?>">Add Order</a>
    <a href="order_list.php" class="<?= $current_page == 'order_list.php' ? 'active' : '' ?>">Order Records</a>

    <a href="logout.php" style="background:#c0392b;">Logout</a>
</div>
<div class="page-content">
    <div class="form-card">

        <h2>Add New Product</h2>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">

            <label>Product Name:</label>
            <input type="text" name="product_name" required>

            <label>Category:</label>
<select name="category" required>
    <option value="">-- Select Category --</option>

    <option>Drums</option>
    <option>Carbouy</option>
    <option>Liters</option>
    <option>Spray</option>
    <option>500ml spray</option>
    <option>250ml spray</option>
    <option>100ml Spray</option>
    <option>Bottles</option>
    <option>Sacks</option>
    <option>Box</option>
    <option>Gallon</option>
    <option>Packs</option>
    <option>Bundles</option>
    <option>Perfume</option>
    <option>Misc</option>
    <option>Others</option>
</select>



            <label>Quantity:</label>
            <input type="number" name="quantity" min="1" required>

            <button type="submit">Save Product</button>

        </form>

    </div>
</div>


</body>
</html>
