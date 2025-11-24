<?php
session_start();
$time = date("Y-m-d H:i:s"); // still 24hr but correct timezone


require 'db.php'; // Load DB first

// Apply timezone to MySQL
$mysqli->query("SET time_zone = '+08:00'");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Order List</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    padding: 20px;
}

.table-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
    margin-bottom: 25px;
}

h2 { margin-bottom: 10px; cursor: pointer; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
}

table th {
    background: #333;
    color: white;
}

.btn {
    padding: 7px 20px;
    background: #1fa83a;
    color: white;
    border: none;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    border-radius: 5px;
}

.search-box {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.search-box input {
    width: 250px;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #aaa;
}

.search-box button {
    padding: 10px 20px;
    background: #1fa83a;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.hidden {
    display: none;
}
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

    /* CONTENT AREA – FIX OVERLAP */
.content {
    margin-left: 260px !important;  /* push all content to the right */
    padding: 30px;
    max-width: calc(100% - 260px); /* prevent table from going under sidebar */
}

/* Ensure table does NOT slide under sidebar */
.table-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
    margin-bottom: 25px;
    width: 100%;
    max-width: 100%;
}

table {
    width: 100%;
    background: white;
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
/* GREEN TABLE THEME */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: #e8f8f5; /* soft green background */
}

table th {
    background: #27ae60;      /* strong green */
    color: white;
    padding: 12px;
    font-size: 15px;
    border: 1px solid #1e8449;
}

table td {
    padding: 10px;
    border: 1px solid #b2dfdb; /* light mint border */
    color: #145a32;
    background: #ecfdf5; /* very light green */
}

/* Hover effect row */
table tr:hover td {
    background: #d4efdf; /* light highlight */
}
.delete-btn {
    padding: 6px 14px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.delete-btn:hover {
    background: #e74c3c;
}

</style>
<script>
function toggleSection(id) {
    var div = document.getElementById(id);
    div.classList.toggle("hidden");
}
</script>

</head>

<body>
<div class="content">

<h1>Order Records</h1>
<a class="btn" href="dashboard.php">⬅ Back to Dashboard</a>
<br><br>

<!-- SEARCH BOX -->
<form class="search-box" method="GET">
    <input type="text" name="search" placeholder="Search Franchisee / Area"
           value="<?= $_GET['search'] ?? '' ?>">

    <button type="submit">Search</button>

    <button type="submit" name="today" value="1" style="background:#1fa83a;">
        Today Only
    </button>
</form>

<?php
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : "";
$todayOnly = isset($_GET['today']) ? "AND DATE(o.created_at) = CURDATE()" : "";

$where = "WHERE 1 ";
if ($search !== "") {
    $where .= "AND (f.franchisee_name LIKE '%$search%' 
               OR f.area LIKE '%$search%')";
}

// FIXED GROUPING — CORRECT
$q = "
SELECT 
    MIN(o.id) AS batch_id,
    o.franchise_id,
    DATE(o.created_at) AS order_date,
    f.franchisee_name,
    f.area
FROM orders o
LEFT JOIN franchisees f ON f.id = o.franchise_id
$where
$todayOnly
GROUP BY 
    o.franchise_id,
    DATE(o.created_at)
ORDER BY batch_id DESC
";

$groups = $mysqli->query($q);

if (!$groups || $groups->num_rows == 0) {
    echo "<p>No results found.</p>";
    exit;
}

$groupIndex = 1;

while ($g = $groups->fetch_assoc()):

    $fid  = $g['franchise_id'];
    $date = $g['order_date'];
    $sectionId = "section_" . $groupIndex;

    echo "<div class='table-box'>";

    echo "
<h2 onclick=\"toggleSection('$sectionId')\" style='display:flex; justify-content:space-between; align-items:center;'>
    <span>{$g['franchisee_name']} – {$g['area']} ( $date )</span>
    <button class='delete-btn' onclick=\"deleteBatch('$fid', '$date')\">🗑 Delete</button>
</h2>
";

    echo "<div id='$sectionId'>";

    $batchId = $g['batch_id'];

    // FIX TIME FORMAT — 12 HR WITH PM/AM CORRECT
 $itemsQ = "
    SELECT 
        p.product_name,
        p.category,
        oi.quantity,
        DATE(oi.created_at) AS order_date
    FROM orders oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE DATE(oi.created_at) = '{$g['order_date']}'
      AND oi.franchise_id = {$g['franchise_id']}
      AND oi.id >= $batchId
";


    $items = $mysqli->query($itemsQ);

    echo "<table>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>Date</th>
        </tr>";


    $i = 1;
    while ($it = $items->fetch_assoc()) {
      echo "
<tr>
    <td>{$i}</td>
    <td>{$it['product_name']}</td>
    <td>{$it['category']}</td>
    <td>pcs</td>
    <td>{$it['quantity']}</td>
    <td>{$it['order_date']}</td>
</tr>";

        $i++;
    }

    echo "</table>";
    echo "</div>";
    echo "</div>";

    $groupIndex++;

endwhile;
?>
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
</div>
<script>
function deleteBatch(fid, date) {
    if (confirm("Are you sure you want to delete all orders for this franchise on " + date + "?")) {
        window.location.href = "order_delete.php?fid=" + fid + "&date=" + date;
    }
}
</script>

</body>
</html>
