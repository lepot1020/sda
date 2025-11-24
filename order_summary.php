<?php
require 'db.php';
session_start();

// ---- VALIDATE URL PARAMETERS ----
$time = $_GET['time'] ?? null;
$franchisee = $_GET['f'] ?? null;
$area = $_GET['a'] ?? null;

if (!$time || !$franchisee || !$area) {
    http_response_code(400);
    echo "<h2>Invalid summary link. Missing parameters.</h2>";
    exit;
}

// ---- FETCH ORDERED ITEMS BY created_at ----
$sql = "
    SELECT 
        o.quantity,
        p.product_name,
        COALESCE(p.category, '') AS category,
        COALESCE(p.unit, '') AS unit
    FROM orders o
    LEFT JOIN products p ON p.id = o.product_id
    WHERE o.created_at = ?
    ORDER BY o.id ASC
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo "<h2>Query prepare failed.</h2><pre>" . htmlspecialchars($mysqli->error) . "</pre>";
    exit;
}

$stmt->bind_param("s", $time);

if (!$stmt->execute()) {
    echo "<h2>Query execute failed.</h2><pre>" . htmlspecialchars($stmt->error) . "</pre>";
    exit;
}

$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    echo "<h2>No orders found for this timestamp.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Order Summary</title>
<meta charset="utf-8">


<style>
body { font-family: Arial, sans-serif; padding:20px; }
.summary-box { border: 1px solid #ddd; padding:20px; border-radius:8px; max-width:980px; background:#fff; }
table { width:100%; border-collapse: collapse; margin-top: 15px; }
table th, table td { padding:10px; border:1px solid #ccc; text-align:left; }
table th { background:#f2f2f2; }
.btn {
    padding:10px 16px;
    background:#0b63c6;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    text-decoration:none;
}
.btn:hover { background:#084f9a; }
.header { display:flex; justify-content:space-between; align-items:center; gap:12px; }

@media print {
  .no-print { display:none; }
}
</style>

<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    padding: 20px;
    background: #f5f7fa;
    color: #333;
}

/* Header Buttons */
.header {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-bottom: 20px;
}

.btn {
    padding: 10px 18px;
    background: #0a7cff;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s ease-in-out;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.btn:hover {
    background: #005fcc;
    transform: translateY(-1px);
}

/* Summary Box Card */
.summary-box {
    background: #ffffff;
    padding: 25px;
    border-radius: 10px;
    max-width: 1000px;
    margin: auto;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.08);
    border-left: none;
}

.summary-box p {
    font-size: 15px;
    margin: 5px 0;
}

h1 {
    text-align: left;
    font-size: 28px;
    margin-top: 0;
}

/* ⭐ GREEN TABLE THEME ⭐ */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 18px;
    overflow: hidden;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}

table th {
    background: #2e8b57; /* Dark Green */
    color: white;
    padding: 12px;
    font-size: 15px;
    letter-spacing: 0.3px;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #d3e8d8; /* Light green border */
    background: #ffffff;
}

table tr:hover td {
    background: #e9f7ef; /* Very light green */
}

/* 🖨 PRINT MODE – KEEP TABLE GREEN */
@media print {

    body {
        background: white !important;
        padding: 0;
        margin: 0;
    }

    .no-print {
        display: none !important;
    }

    .summary-box {
        box-shadow: none !important;
        border-left: none !important;
    }

    table {
        box-shadow: none !important;
    }

    table th {
        background: #2e8b57 !important;
        color: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    table td {
        background: white !important;
        -webkit-print-color-adjust: exact;
    }
}
/* Green Buttons */
.btn-green {
    background: #28a745;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-weight: bold;
    cursor: pointer;
}
.btn-green:hover {
    background: #218838;
}

/* Order Summary Box */
.order-summary {
    background: #e8f5e9;
    border: 2px solid #28a745;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    width: 100%;
}

/* Green Table */
.green-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.green-table th {
    background: #2e7d32;
    color: white;
    padding: 10px;
}
.green-table td {
    border: 1px solid #c8e6c9;
    padding: 9px;
    background: #f1f8e9;
}
@page {
    size: auto;
    margin: 0;
}
body {
    margin: 20px;
}

.green-table th,
.green-table td {
    text-align: center;       /* Center all cells */
    text-transform: uppercase; /* Capslock */
}

</style>

</head>
<body>

<h1><center>Order Summary</center></h1>

<div class="summary-box">
   <p><strong>Franchisee:</strong> <?= htmlspecialchars(strtoupper($franchisee)) ?></p>
<p><strong>Area:</strong> <?= htmlspecialchars(strtoupper($area)) ?></p>
<p><strong>Order Time:</strong> <?= htmlspecialchars(strtoupper($time)) ?></p>

 <h3>Ordered Products</h3>

<table class="green-table">
    <thead>
        <tr>
            <th style="width:48px">#</th>
            <th>Product</th>
            <th style="width:160px">Category</th>
            <th style="width:120px">Quantity</th>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td style="text-align:center;"><?= $i ?></td>
        <td style="text-align:center;"><?= htmlspecialchars(strtoupper($row['product_name'])) ?></td>
        <td style="text-align:center;"><?= htmlspecialchars(strtoupper($row['category'])) ?></td>
        <td style="text-align:center;"><?= htmlspecialchars($row['quantity']) ?></td>
    </tr>
    <?php $i++; endwhile; ?>
</tbody>

</table>


    <!-- ✅ BUTTONS MOVED BELOW, CENTERED -->
    <div class="footer-buttons no-print" style="text-align:center; margin-top:25px;">
        <button onclick="window.print()" class="btn-green">Print</button>
<a href="dashboard.php" class="btn-green" style="text-decoration:none;">Go to Dashboard</a>

    </div>

</div>

</body>

</html>
