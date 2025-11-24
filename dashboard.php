<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


// ORDER SUMMARY

$today = date("Y-m-d");
$month = date("Y-m");

// TODAY ORDERS (unique order tables)
$total_orders_today = $mysqli->query("
    SELECT COUNT(DISTINCT created_at) AS total
    FROM orders
    WHERE DATE(created_at) = '$today'
")->fetch_assoc()['total'] ?? 0;

// MONTH ORDERS (unique order tables)
$total_orders_month = $mysqli->query("
    SELECT COUNT(DISTINCT created_at) AS total
    FROM orders
    WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'
")->fetch_assoc()['total'] ?? 0;

// TOP PRODUCT
$top_product_query = $mysqli->query("
    SELECT p.product_name, SUM(o.quantity) AS total_sold
    FROM orders o LEFT JOIN products p ON p.id=o.product_id
    GROUP BY o.product_id ORDER BY total_sold DESC LIMIT 1
");

$top_product = ($top_product_query && $top_product_query->num_rows > 0)
    ? $top_product_query->fetch_assoc()
    : ['product_name'=>'None', 'total_sold'=>0];

// TOP FRANCHISE
$top_franchise_query = $mysqli->query("
    SELECT f.franchisee_name, COUNT(o.id) AS order_count
    FROM orders o LEFT JOIN franchisees f ON f.id=o.franchise_id
    GROUP BY o.franchise_id ORDER BY order_count DESC LIMIT 1
");

$top_franchise = $top_franchise_query->fetch_assoc() ?? ['franchisee_name'=>'None', 'order_count'=>0];

// STOCK DATA
$stockQuery = $mysqli->query("SELECT product_name, category, quantity FROM products ORDER BY product_name ASC");

$chart_products = [];
$chart_categories = [];
$chart_quantities = [];
while ($row = $stockQuery->fetch_assoc()) {
  $chart_products[] = strtoupper($row['product_name']);
$chart_categories[] = strtoupper($row['category']);
$chart_quantities[] = $row['quantity'];

}

// CATEGORY TOTALS FOR PIE CHART
$catQuery = $mysqli->query("
    SELECT category, SUM(quantity) AS total_qty
    FROM products
    GROUP BY category
");

$category_labels = [];
$category_totals = [];

while ($c = $catQuery->fetch_assoc()) {
  $category_labels[] = strtoupper($c['category']);

    $category_totals[] = $c['total_qty'];
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        background: #1fa83a;
        padding: 12px;
        display: inline-block;
        border-radius: 6px;
        margin-bottom: 20px;
    
    }

    /* TABLE */
.stock-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #eafaf1; /* light green background */
}

.stock-table th {
    background: #27ae60; /* GREEN HEADER */
    color: white;
    padding: 12px;
    text-align: center; /* center header text */
}

.stock-table td {
    padding: 10px;
    border: 1px solid #b2e0c5; /* soft green border */
    text-align: center; /* center table data */
    font-weight: 500;
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
<a href="pullout.php" class="<?= $current_page == 'pullout.php' ? 'active' : '' ?>">Pull Out</a>

    <a href="logout.php" style="background:#c0392b;">Logout</a>
</div>

<div class="content">

    <h1>Welcome, <?= strtoupper($_SESSION['fullname']); ?>!</h1>


    <!-- SUMMARY CARDS -->
    <div class="summary-box">
        <div class="summary-item">
            Orders Today
            <span><?= $total_orders_today ?></span>
        </div>
        <div class="summary-item">
            Orders This Month
            <span><?= $total_orders_month ?></span>
        </div>
        <div class="summary-item">
            Top Product
            <span><?= strtoupper($top_product['product_name']) ?></span>

        </div>
        <div class="summary-item">
            Top Franchisee
            <span><?= strtoupper($top_franchise['franchisee_name']) ?></span>

        </div>
    </div>

    <!-- INVENTORY CARD -->
    <div class="card">
        <h1 style="color:#1fa83a;">Daily Stock</h1>

       <div class="sort-box">
    <label><b>Filter by Category:</b></label>
    <select id="filterCategory" onchange="filterCategory()">
        <option value="all">All Categories</option>
        <?php foreach(array_unique($chart_categories) as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
        <?php endforeach; ?>
    </select>
</div>


        <canvas id="stockChart" height="120"></canvas>

        <h3 style="margin-top:25px;color:#27ae60;">Inventory Table</h3>
        <table class="stock-table" id="stockTable"></table>
        <h3 style="margin-top:35px;color:#1fa83a;">Category Stock Distribution</h3>
<div style="width: 300px; margin: auto;">
    <canvas id="categoryChart"></canvas>
</div>

    </div>

</div>
<script>
function filterCategory() {
    let selected = document.getElementById("filterCategory").value;

    // Get original data from PHP (hindi mawawala ang original values)
    let allProducts = <?= json_encode($chart_products); ?>;
    let allCategories = <?= json_encode($chart_categories); ?>;
    let allQuantities = <?= json_encode($chart_quantities); ?>;

    let filteredProducts = [];
    let filteredCategories = [];
    let filteredQuantities = [];

    for (let i = 0; i < allProducts.length; i++) {
        if (selected === "all" || allCategories[i] === selected) {
            filteredProducts.push(allProducts[i]);
            filteredCategories.push(allCategories[i]);
            filteredQuantities.push(allQuantities[i]);
        }
    }

    // Update main JS variables
    products = filteredProducts;
    categories = filteredCategories;
    quantities = filteredQuantities;

    // Update barchart
    stockChart.data.labels = filteredProducts;
    stockChart.data.datasets[0].data = filteredQuantities;
    stockChart.update();

    // Update inventory table
    buildTable();
}
</script>

<script>


let products = <?= json_encode($chart_products); ?>;
let categories = <?= json_encode($chart_categories); ?>;
let quantities = <?= json_encode($chart_quantities); ?>;

function buildTable() {
    let table = document.getElementById("stockTable");

    table.innerHTML = `
        <tr>
            <th>Product Name</th>
            <th>Category</th>
            <th>Stock</th>
        </tr>
    `;

    products.forEach((p, i) => {
        table.innerHTML += `
            <tr>
                <td>${p}</td>
                <td>${categories[i]}</td>
                <td>${quantities[i]}</td>
            </tr>
        `;
    });
}  


function updateSort() {
    let type = document.getElementById("sortSelect").value;

    let combined = products.map((p, i) => ({
        product: p,
        qty: quantities[i]
    }));

    if (type === "high") combined.sort((a,b)=> b.qty - a.qty);
    if (type === "low") combined.sort((a,b)=> a.qty - b.qty);

    products = combined.map(x=>x.product);
    quantities = combined.map(x=>x.qty);

    stockChart.data.labels = products;
    stockChart.data.datasets[0].data = quantities;
    stockChart.update();

    buildTable();
}

var ctx = document.getElementById('stockChart').getContext('2d');
var stockChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: products,
        datasets: [{
            label: 'Stock Quantity',
            data: quantities,
            backgroundColor: '#2ecc71',
            borderColor: '#27ae60',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true }}
    }
});

buildTable();

// PIE CHART FOR CATEGORY TOTALS
const catLabels = <?= json_encode($category_labels); ?>;
const catTotals = <?= json_encode($category_totals); ?>;

// AUTO-GENERATE UNIQUE COLORS
function generateColors(count) {
    let colors = [];
    for (let i = 0; i < count; i++) {
        colors.push("hsl(" + (i * (360 / count)) + ", 70%, 55%)");
    }
    return colors;
}

const categoryChart = new Chart(document.getElementById('categoryChart'), {
    type: 'pie',
    data: {
        labels: catLabels,
        datasets: [{
            data: catTotals,
            backgroundColor: generateColors(catLabels.length),
            borderWidth: 2,
            borderColor: "#fff"
        }]
    }
});

</script>

</body>
</html>
