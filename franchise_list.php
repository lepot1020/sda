<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$franchisees = $mysqli->query("SELECT * FROM franchisees ORDER BY id DESC");

// Detect current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Franchise List</title>

<style>
/* KEEP SIDEBAR EXACTLY THE SAME */
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #eef2f3;
}

.content {
    margin-left: 230px;
    padding: 30px;
}

/* PAGE TITLE */
.page-title {
    font-size: 28px;
    font-weight: bold;
    color: #2ecc71;
    margin-bottom: 20px;
    text-transform: uppercase;
}

/* CARD DESIGN */
.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 95%;
    margin: auto;
}

/* TABLE */
.table-custom {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    border-radius: 10px;
    overflow: hidden;
}

.table-custom th {
    background: #2ecc71;
    color: white;
    padding: 12px;
    font-size: 16px;
    text-align: left;
}

.table-custom td {
    padding: 12px;
    background: white;
    border-bottom: 1px solid #ddd;
    font-size: 15px;
}

/* BUTTONS */
.btn {
    padding: 7px 14px;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}

.edit-btn { background: #27ae60; }
.edit-btn:hover { background: #2ecc71; }

.delete-btn { background: #c0392b; }
.delete-btn:hover { background: #e74c3c; }

</style>
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

    /* CONTENT AREA */
    .content {
        margin-left: 230px;
        padding: 25px;
    }

    h1 {
        color: #27ae60;
        font-size: 28px;
        margin-bottom: 20px;
    }

    /* SUMMARY CARDS */
    .summary-box {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-item {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        padding: 20px;
        color: white;
        border-radius: 12px;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        transition: 0.3s;
    }
    .summary-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    .summary-item span {
        font-size: 32px;
        display: block;
        margin-top: 10px;
        color: #f9e79f;
    }

    /* CARD */
    .card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    /* SORT BOX */
    .sort-box {
        background: #fde3a7;
        padding: 12px;
        display: inline-block;
        border-radius: 6px;
        margin-bottom: 20px;
        border-left: 5px solid #e67e22;
    }

    /* TABLE */
    .stock-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: white;
    }
    .stock-table th {
        background: #e67e22;
        color: white;
        padding: 12px;
    }
    .stock-table td {
        padding: 10px;
        border: 1px solid #ccc;
    }

</style>
</head>

<body>

<div class="sidebar">

    <!-- LOGO (ADD THIS) -->
    <div style="text-align:center; margin-bottom: 10px;">
        <img src="logo.png" 
             style="width: 90px; height: auto; border-radius: 10px;">
    </div>

    <h2>SABON DE AMOR</h2>

    <a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">Dashboard</a>

    <a href="franchise.php" class="<?= $current_page=='franchise.php'?'active':'' ?>">Add Franchise</a>
    <a href="franchise_list.php" class="<?= $current_page=='franchise_list.php'?'active':'' ?>">Franchise List</a>

    <a href="product_list.php" class="<?= $current_page=='product_list.php'?'active':'' ?>">Product List</a>

    <a href="order.php" class="<?= $current_page=='order.php'?'active':'' ?>">Add Order</a>
    <a href="order_list.php" class="<?= $current_page=='order_list.php'?'active':'' ?>">Order Records</a>
<a href="pullout.php" class="<?= $current_page == 'pullout.php' ? 'active' : '' ?>">Pull Out</a>

    <a href="logout.php" style="background:#c0392b;">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    
    <div class="page-title">Franchisee List</div>

    <div class="card">
        <table class="table-custom">
            <tr>
                <th>ID</th>
                <th>Franchisee Name</th>
                <th>Area</th>
                <th>Type</th>
                <th>Action</th>
            </tr>

           <?php 
            $counter = 1; 
            while ($row = $franchisees->fetch_assoc()): 
            ?>

            <tr>
                <td><?= $counter++ ?></td>
                <td><?= strtoupper($row['franchisee_name']) ?></td>
                <td><?= strtoupper($row['area']) ?></td>
                <td><?= strtoupper($row['type']) ?></td>

                <td>
                    <a class="btn edit-btn" href="edit_franchise.php?id=<?= $row['id'] ?>">Edit</a>
                    <a class="btn delete-btn" href="delete_franchise.php?id=<?= $row['id'] ?>"
                        onclick="return confirm('Are you sure you want to delete this franchisee?');">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>

</div>

</body>
</html>
