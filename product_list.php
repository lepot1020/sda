<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get all products
$result = $mysqli->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <style>
        /* GREEN THEME TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

table th {
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    color: white;
    padding: 14px;
    font-size: 15px;
    text-align: left;
    letter-spacing: 0.5px;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #d6f5df;
    background: #fafffb;
    font-size: 14px;
}

table tr:hover td {
    background: #e9fff2;
}

        body { margin: 0; font-family: Arial; background: #f4f4f4; }

        .sidebar {
            width: 220px; height: 100vh;
            background: #333; position: fixed; top: 0; left: 0; padding-top: 20px;
        }
        .sidebar h2 { color: #fff; text-align: center; margin-bottom: 30px; }
        .sidebar a {
            display: block; padding: 15px;
            color: white; text-decoration: none; border-bottom: 1px solid #444;
        }
        .sidebar a:hover { background: #444; }

        .content { margin-left: 220px; padding: 20px; }

        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
            background: white; border-radius: 5px; overflow: hidden;
        }
        table th, table td {
            padding: 12px; border-bottom: 1px solid #ddd; text-align: left;
        }
        table th {
            background: #333; color: white;
        }
        tr:hover { background: #f1f1f1; }

        .btn {
            display: inline-block; padding: 10px 15px;
            background: #333; color: white; text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover { background: #555; }
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
/* ADD NEW PRODUCT BUTTON - GREEN */
.add-btn {
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}

.add-btn:hover {
    background: linear-gradient(90deg, #1e874b, #27ae60);
    transform: scale(1.04);
}

/* TABLE HEADER GREEN */
table th {
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    color: white;
    padding: 14px;
    font-size: 15px;
    text-align: left;
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

</style>
</head>
<body>

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

<div class="content">

    <h1>Product List</h1>

    <a href="product.php" class="btn">➕ Add New Product</a>

    <table>
<thead>
    <tr>
        <th style="text-align:center;">ID</th>
        <th style="text-align:center;">Product Name</th>
        <th style="text-align:center;">Category</th>
        <th style="text-align:center;">Quantity</th>
        <th style="text-align:center;">Date Added</th>
        <th style="text-align:center;">Action</th>
    </tr>
</thead>
<tbody>
<?php $i = 1; while ($p = $result->fetch_assoc()): ?>
<tr>
    <td style="text-align:center;"><?= $i; ?></td> <!-- Auto-increment number -->
    <td style="text-align:center;"><?= htmlspecialchars(strtoupper($p['product_name'])); ?></td>
    <td style="text-align:center;"><?= htmlspecialchars(strtoupper($p['category'])); ?></td>
    <td style="text-align:center;"><?= htmlspecialchars(strtoupper($p['quantity'])); ?></td>
    <td style="text-align:center;"><?= htmlspecialchars(strtoupper($p['created_at'])); ?></td>
    <td style="text-align:center;">
        <a href="edit_product.php?id=<?= $p['id']; ?>" 
           style="padding:6px 10px;background:green;color:white;text-decoration:none;border-radius:3px;">
            EDIT
        </a>
        <a href="delete_product.php?id=<?= $p['id']; ?>" 
           onclick="return confirm('Are you sure you want to delete this product?');"
           style="padding:6px 10px;background:red;color:white;text-decoration:none;border-radius:3px;">
            DELETE
        </a>
    </td>
</tr>
<?php $i++; endwhile; ?>
</tbody>
</table>


</div>

</body>
</html>
