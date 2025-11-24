<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['franchisee_name'];
    $area = $_POST['area'];
    $type = trim($_POST['type']);

    $stmt = $mysqli->prepare("INSERT INTO franchisees (franchisee_name, area, type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $area, $type);

    if ($stmt->execute()) {
        $message = "Franchisee added successfully!";
    } else {
        $message = "Error adding franchisee.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Franchise</title>

    <style>
    /* ACTIVE SIDEBAR MENU */
.sidebar a.active {
    background: linear-gradient(90deg, #2ecc71, #27ae60);
    color: white;
    font-weight: bold;
    border-left: 6px solid #f39c12; /* orange accent */
}

    body {
        margin: 0;
        font-family: "Segoe UI", Arial;
        background: #f6f7f9;
    }

    /* SIDEBAR */
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
        letter-spacing: 1px;
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

    /* ACTIVE SIDEBAR MENU */
.sidebar a.active {
    background: linear-gradient(90deg, #2ecc71, #27ae60);
    color: white;
    font-weight: bold;
    border-left: 6px solid #f39c12;
}

/* GLOBAL */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #eef1f4;
}

/* SIDEBAR */
.sidebar {
    width: 230px;
    height: 100vh;
    background: #2c3e50;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 25px;
    box-shadow: 3px 0 10px rgba(0,0,0,0.25);
}

.sidebar h2 {
    color: #f39c12;
    text-align: center;
    font-size: 22px;
    margin-bottom: 25px;
}

.sidebar a {
    display: block;
    padding: 14px 20px;
    color: #ecf0f1;
    font-size: 16px;
    text-decoration: none;
    border-left: 5px solid transparent;
    transition: 0.3s ease;
}

.sidebar a:hover {
    background: #34495e;
    border-left: 5px solid #2ecc71;
}

/
/* CONTENT WRAPPER — FORM AT THE TOP AND CENTERED PROPERLY */
.main {
    margin-left: 230px;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;     
    padding: 40px;            /* mas luwag */
}




/* FORM CARD */
.form-container {
    max-width: 650px;        /* from 450px → 650px (LARGER) */
    width: 100%;
    background: #ffffff;
    padding: 35px 40px;      /* mas maluwag */
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.18);
    animation: fadeIn 0.4s ease;
}


@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-container h2 {
    color: #27ae60;
    margin-bottom: 18px;
}

/* SUCCESS MESSAGE */
.success {
    background: #d4efdf;
    color: #1e8449;
    padding: 12px;
    border-left: 5px solid #1e8449;
    border-radius: 6px;
    font-weight: bold;
    margin-bottom: 15px;
}

/* FORM INPUTS */
.form-container label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
    color: #555;
}

.form-container input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #c8c8c8;
    font-size: 14px;
    transition: 0.3s;
}

.form-container input:focus {
    border-color: #27ae60;
    box-shadow: 0 0 3px #27ae6044;
    outline: none;
}

/* BUTTON */
.form-container button {
    width: 100%;
    margin-top: 18px;
    padding: 12px;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    font-weight: bold;
    letter-spacing: 1px;
    transition: 0.3s;
}

.form-container button:hover {
    background: linear-gradient(135deg, #1e8f4e, #29d778);
    transform: translateY(-2px);
}

/* VIEW LIST LINK */
.view-link {
    display: block;
    margin-top: 15px;
    text-align: center;
    text-decoration: none;
    font-weight: bold;
    color: #2c3e50;
    padding: 10px;
    border-radius: 6px;
    background: #f0f3f5;
    transition: 0.3s;
}

.view-link:hover {
    background: #dcdfe2;
    color: #27ae60;
}
/* CONTENT WRAPPER — CENTER FORM */
.main {
    margin-left: 230px;          /* keep space for sidebar */
    height: 100vh;               /* full height */
    display: flex;               /* enable centering */
    justify-content: center;     /* center horizontally */
    align-items: center;         /* center vertically */
    padding: 20px;
}
.form-container select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #c8c8c8;
    font-size: 14px;
    background: white;
    transition: 0.3s;
}

.form-container select:focus {
    border-color: #27ae60;
    box-shadow: 0 0 3px #27ae6044;
    outline: none;
}

</style>
</head>
<body>

<!-- SIDEBAR -->
<?php
    // Detect current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">

    <!-- LOGO (ADD THIS) -->
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
<a href="pullout.php" class="<?= $current_page == 'pullout.php' ? 'active' : '' ?>">Pull Out</a>

    <a href="logout.php" style="background:#c0392b;">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="form-container">

        <h2>Add Franchisee</h2>

        <?php if ($message): ?>
            <p class="success"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST">

            <label>Franchisee Name:</label>
            <input type="text" name="franchisee_name" required>

            <label>Area:</label>
            <input type="text" name="area" required>

            <label>Type:</label>
<select name="type" required>
    <option value="" selected disabled>Select Type</option>
    <option value="Franchisee">Franchisee</option>
    <option value="Dealer">Dealer</option>
</select>


            <button type="submit">Save Franchisee</button>

        </form>

        <a href="franchise_list.php" class="view-link">➡ View Franchise List</a>

    </div>
</div>

</body>
</html>
