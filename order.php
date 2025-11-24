<?php
session_start();
date_default_timezone_set('Asia/Manila');
echo "PHP Time: " . date("h:i A");

require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/*
 SESSION STRUCTURE:
 $_SESSION['active_order'] = [
     'franchise_id' => 1,
     'franchise_name' => 'Sample Name',
     'area' => 'Manila',
     'cart' => [
         [ 'product_id'=>1, 'product_name'=>'Item A', 'category'=>'Drums', 'unit'=>'kg', 'qty'=>2 ],
         ...
     ]
 ];
*/

// initialize if empty
if (!isset($_SESSION['active_order'])) {
    $_SESSION['active_order'] = [
        'franchise_id' => null,
        'franchise_name' => null,
        'area' => null,
        'cart' => []
    ];
}

$message = "";

// ---------------------------------------
// Helper: fetch product including category + unit + quantity
// ---------------------------------------
function fetch_product($mysqli, $id) {
    $id = intval($id);
    $q = $mysqli->query("SELECT id, product_name, category, unit, quantity FROM products WHERE id = $id");
    return $q ? $q->fetch_assoc() : null;
}

// ---------------------------------------
// STEP 1: SELECT FRANCHISEE ONCE
// ---------------------------------------
if (isset($_POST['select_franchisee'])) {

    $fid = intval($_POST['franchise_id'] ?? 0);

    if ($fid <= 0) {
        $message = "Please choose a franchisee.";
    } else {
        $fres = $mysqli->query("SELECT * FROM franchisees WHERE id = $fid");
        $f = $fres ? $fres->fetch_assoc() : null;

        if ($f) {
            $_SESSION['active_order']['franchise_id']   = $f['id'];
            $_SESSION['active_order']['franchise_name'] = $f['franchisee_name'];
            $_SESSION['active_order']['area']           = $f['area'];
        } else {
            $message = "Invalid franchisee selected.";
        }
    }
}

// ---------------------------------------
// STEP 2: ADD ITEM INTO CART
// ---------------------------------------
if (isset($_POST['add_item'])) {

    if (!$_SESSION['active_order']['franchise_id']) {
        $message = "Please select a franchisee first.";
    } else {

        $product_id = intval($_POST['product_id'] ?? 0);
        $qty = intval($_POST['qty'] ?? 0);

        if ($product_id <= 0 || $qty <= 0) {
            $message = "Invalid product or quantity.";
        } else {
            $p = fetch_product($mysqli, $product_id);

            if (!$p) {
                $message = "Product not found.";
            } else {
                $available = intval($p['quantity']);

                // compute qty already in cart for this product
                $currentQty = 0;
                foreach ($_SESSION['active_order']['cart'] as $item) {
                    if ($item['product_id'] == $product_id) {
                        $currentQty += $item['qty'];
                    }
                }

                if ($qty + $currentQty > $available) {
                    $message = "Stock too low. Available: $available.";
                } else {
                    // merge if exists
                    $exists = false;
                    foreach ($_SESSION['active_order']['cart'] as &$it) {
                        if ($it['product_id'] == $product_id) {
                            $it['qty'] += $qty;
                            $exists = true;
                            break;
                        }
                    }
                    unset($it);

                    if (!$exists) {
                        $_SESSION['active_order']['cart'][] = [
                            'product_id' => $p['id'],
                            'product_name' => $p['product_name'],
                            'category' => $p['category'],
                            'unit' => $p['unit'],
                            'qty' => $qty
                        ];
                    }

                    $message = "Item added successfully.";
                }
            }
        }
    }
}

// ---------------------------------------
// REMOVE ONE ITEM
// ---------------------------------------
if (isset($_POST['remove_item'])) {
    $i = intval($_POST['remove_item']);
    if (isset($_SESSION['active_order']['cart'][$i])) {
        array_splice($_SESSION['active_order']['cart'], $i, 1);
        $message = "Item removed.";
    } else {
        $message = "Invalid item index.";
    }
}

// ---------------------------------------
// CLEAR CART BUT KEEP FRANCHISEE
// ---------------------------------------
if (isset($_POST['clear_cart'])) {
    $_SESSION['active_order']['cart'] = [];
    $message = "Cart cleared.";
}

// ---------------------------------------
// RESET ALL (franchisee + cart) - optional link
// ---------------------------------------
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $_SESSION['active_order'] = [
        'franchise_id' => null,
        'franchise_name' => null,
        'area' => null,
        'cart' => []
    ];
    header("Location: order.php");
    exit;
}

// ---------------------------------------
// SAVE ORDER
// ---------------------------------------
if (isset($_POST['save_order'])) {

    if (!$_SESSION['active_order']['franchise_id']) {
        $message = "Select franchisee first.";
    } elseif (empty($_SESSION['active_order']['cart'])) {
        $message = "Cart is empty.";
    } else {

        $fid = $_SESSION['active_order']['franchise_id'];
$created_at = date("Y-m-d h:i:s A");  // 12-HOUR FORMAT WITH AM/PM


        $mysqli->begin_transaction();
        try {

            // Using the existing single-row-per-product "orders" design (no order_items table)
            $ins_stmt = $mysqli->prepare("INSERT INTO orders (franchise_id, product_id, quantity, status, created_at) VALUES (?, ?, ?, 'Pending', ?)");
            if (!$ins_stmt) throw new Exception("Prepare failed: " . $mysqli->error);

            foreach ($_SESSION['active_order']['cart'] as $it) {

                // lock stock (InnoDB)
                $pid = intval($it['product_id']);
                $r = $mysqli->query("SELECT quantity FROM products WHERE id = $pid FOR UPDATE");
                $row = $r ? $r->fetch_assoc() : null;
                if (!$row) throw new Exception("Product not found (ID $pid).");

                if (intval($row['quantity']) < intval($it['qty'])) {
                    throw new Exception("Not enough stock for " . $it['product_name']);
                }

                // insert order row
                $ins_stmt->bind_param("iiis", $fid, $pid, $it['qty'], $created_at);
                if (!$ins_stmt->execute()) {
                    throw new Exception("Insert failed: " . $ins_stmt->error);
                }

                // deduct stock
                $upd = $mysqli->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                if (!$upd) throw new Exception("Prepare update failed: " . $mysqli->error);
                $upd->bind_param("ii", $it['qty'], $pid);
                if (!$upd->execute()) throw new Exception("Update stock failed: " . $upd->error);
                $upd->close();
            }

            $ins_stmt->close();
            $mysqli->commit();

            // preserve time so summary page can find the saved order(s)
            $_SESSION['saved_order_time'] = $created_at;

            // clear only cart but keep franchisee? you requested redirect to summary showing franchise name + area and products
            // We'll clear the session active_order so next new order starts fresh (you can change if you prefer to keep franchisee)
            $franchise_name = $_SESSION['active_order']['franchise_name'];
            $franchise_area = $_SESSION['active_order']['area'];

            // clear active_order so user can start new one
            $_SESSION['active_order'] = [
                'franchise_id' => null,
                'franchise_name' => null,
                'area' => null,
                'cart' => []
            ];

            // redirect to summary page with timestamp
            header("Location: order_summary.php?time=" . urlencode($created_at) . "&f=" . urlencode($franchise_name) . "&a=" . urlencode($franchise_area));
            exit;

        } catch (Exception $e) {
            $mysqli->rollback();
            $message = "Save failed: " . $e->getMessage();
        }
    }
}

// fetch lists (fresh)
$franchisees = $mysqli->query("SELECT * FROM franchisees ORDER BY franchisee_name ASC");
$products = $mysqli->query("SELECT * FROM products ORDER BY product_name ASC");

?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>Add Order</title>

    <style>
        body { font-family: Arial, sans-serif; padding:20px; background:#f4f4f4; }
        .card { background:white; padding:15px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08); margin-bottom:15px; max-width:900px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px; border-bottom:1px solid #eee; text-align:left; }
        .btn { padding:8px 12px; border:none; background:#333; color:white; border-radius:6px; cursor:pointer; }
        .btn.danger { background:#c0392b; }
        .muted { color:#666; font-size:13px; }
    </style>
</head>
<body>
<div class="page-container">
    <div class="page-content">

<div class="page-wrapper">

<div class="card">
    <h2>Add Franchisee orders</h2>

    <?php if ($message): ?>
        <div style="padding:10px; background:#fffbeb; border:1px solid #f5d7a1; margin-bottom:12px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Franchisee selection (only if none selected) -->
    <?php if (empty($_SESSION['active_order']['franchise_id'])): ?>
        <form method="POST" class="card" style="max-width:700px;">
            <label><strong>Select Franchisee</strong></label><br>
          <input type="text" id="franchiseSearch" placeholder="Type franchisee name..." autocomplete="off"
       style="padding:8px; width:100%; margin:8px 0;">

<input type="hidden" name="franchise_id" id="franchiseID">

<div id="suggestions" style="
    background:white; 
    border:1px solid #ccc; 
    border-radius:5px; 
    max-height:200px; 
    overflow-y:auto; 
    position:absolute; 
    width:90%; 
    display:none;
"></div>


            <button type="submit" name="select_franchisee" class="btn">Confirm Franchisee</button>
        </form>
    <?php else: ?>
        <div class="card" style="max-width:700px;">
            <strong>Franchisee:</strong> <?= htmlspecialchars($_SESSION['active_order']['franchise_name']) ?>
            <div class="muted">Area: <?= htmlspecialchars($_SESSION['active_order']['area']) ?></div>
            <div style="margin-top:8px;">
                <a href="order.php?reset=1" class="btn" style="background:#777; text-decoration:none;">Change Franchisee</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Add item (only if franchisee chosen) -->
    <div class="card" id="add-item-product" style="max-width:900px;">
        <h3>Add Item</h3>

        <?php if (empty($_SESSION['active_order']['franchise_id'])): ?>
            <p class="muted">Choose a franchisee first to start adding items.</p>
        <?php else: ?>
            <form method="POST" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px;">
                    <label>Product</label><br>
                    <select name="product_id" required style="width:100%; padding:8px;">
                        <option value="">Select product</option>
                        <?php
                        // products was fetched earlier; if loop consumed it, re-fetch
                        $ps = $mysqli->query("SELECT * FROM products ORDER BY product_name ASC");
                        while ($pp = $ps->fetch_assoc()):
                        ?>
                            <option value="<?= intval($pp['id']) ?>">
                                <?= htmlspecialchars($pp['product_name']) ?> (<?= htmlspecialchars($pp['category'] ?? '') ?> - <?= htmlspecialchars($pp['unit'] ?? '') ?>)
                                — Stock: <?= intval($pp['quantity'] ?? $pp['stock'] ?? 0) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="width:120px;">
                    <label>Qty</label><br>
                    <input type="number" name="qty" min="1" value="1" style="padding:8px; width:100%;">
                </div>

                <div style="min-width:120px;">
                    <label>&nbsp;</label><br>
                    <button type="submit" name="add_item" class="btn">Add to Cart</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Cart display -->
    <div class="card" style="max-width:1000px;">
        <h3>Cart Items</h3>

        <?php if (empty($_SESSION['active_order']['cart'])): ?>
            <p>No items in cart yet.</p>
        <?php else: ?>
            <table>
                <thead>
    <tr>
        <th style="width:40px">#</th>
        <th>Product</th>
        <th>Category</th>
        <th style="width:80px">Quantity</th>
        <th style="width:120px"></th>
    </tr>
</thead>

                <tbody>
                    <?php foreach ($_SESSION['active_order']['cart'] as $i => $it): ?>
                        <tr>
   <td><?= $i + 1 ?></td>
<td><?= htmlspecialchars($it['product_name']) ?></td>
<td><?= htmlspecialchars($it['category']) ?></td> <!-- Category -->
<td style="text-align:center;"><?= intval($it['qty']) ?></td> <!-- Quantity -->
<td>
    <form method="POST" style="display:inline;">
        <button type="submit" name="remove_item" value="<?= $i ?>" class="btn danger">Remove</button>
    </form>
</td>

</tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:12px; display:flex; gap:10px; align-items:center;">
                <form method="POST" style="display:inline;">
                    <button type="submit" name="clear_cart" class="btn" style="background:#777;">Clear Cart</button>
                </form>

                <!-- Save order -->
                <form method="POST" style="display:inline;">
                    <button type="submit" name="save_order" class="btn" onclick="return confirm('Save order and deduct stock?')">Save Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</div>
<style>
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


/* Active link */
.sidebar a.active {
    background: linear-gradient(90deg, #2ecc71, #27ae60);
    color: white;
    font-weight: bold;
    border-left: 6px solid #f39c12;
}

/* ===============================
   MAIN PAGE WRAPPER (CENTERED)
================================*/
.page-container {
    margin-left: 230px;   /* respect sidebar */
    padding: 30px;
    display: flex;
    justify-content: center;
}

.page-content {
    width: 100%;
    max-width: 900px;     /* keeps content beautifully centered */
}

/* ===============================
   CARDS
================================*/
.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.09);
    margin-bottom: 25px;
}

/* Headers */
.page-content h2,
.page-content h3 {
    color: #27ae60;
    margin-top: 0;
    margin-bottom: 15px;
}

/* ===============================
   TABLES
================================*/
.page-content table {
    width: 100%;
    border-collapse: collapse;
}

.page-content table th {
    background: #27ae60;
    color: white;
    padding: 12px;
}

.page-content table td {
    padding: 10px;
    border-bottom: 1px solid #eaeaea;
}

/* ===============================
   BUTTONS
================================*/
.btn {
    background: #27ae60;
    padding: 10px 15px;
    border-radius: 6px;
    border: none;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    background: #2ecc71;
}

/* Danger button */
.btn.danger {
    background: #e74c3c;
}

.btn.danger:hover {
    background: #c0392b;
}

/* ===============================
   ALERT BOX
================================*/
.alert-box {
    padding: 12px;
    background: #fff3cd;
    border-left: 6px solid #f39c12;
    margin-bottom: 15px;
    border-radius: 6px;
}


</style>
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
</div>
    </div>
</div>
<script>
const searchBox = document.getElementById("franchiseSearch");
const suggestionBox = document.getElementById("suggestions");
const hiddenID = document.getElementById("franchiseID");

searchBox.addEventListener("keyup", function () {
    let query = this.value.trim();

    if (query.length < 1) {
        suggestionBox.style.display = "none";
        return;
    }

    fetch("search_franchisee.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach(item => {
                html += `<div class="sugItem" 
                         data-id="${item.id}" 
                         style="padding:10px; cursor:pointer; border-bottom:1px solid #eee;">
                            <strong>${item.franchisee_name}</strong>
                            <div style="font-size:12px; color:#666;">${item.area}</div>
                         </div>`;
            });

            suggestionBox.innerHTML = html;
            suggestionBox.style.display = data.length > 0 ? "block" : "none";

            document.querySelectorAll(".sugItem").forEach(el => {
                el.addEventListener("click", function () {
                    searchBox.value = this.querySelector("strong").innerText;
                    hiddenID.value = this.dataset.id;
                    suggestionBox.style.display = "none";
                });
            });
        });
});
</script>

</body>
</html>
