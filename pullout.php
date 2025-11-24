<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

/* -------------------------
   HANDLE UPDATE
   ------------------------- */
if (isset($_POST['update_record'])) {

    $id = intval($_POST['id']);
    $franchisee = isset($_POST['franchisee_name']) ? $mysqli->real_escape_string($_POST['franchisee_name']) : "";
    $area = isset($_POST['area']) ? $mysqli->real_escape_string($_POST['area']) : "";
    $description = isset($_POST['product_description']) ? $mysqli->real_escape_string($_POST['product_description']) : "";
    $reason = isset($_POST['reason']) ? $mysqli->real_escape_string($_POST['reason']) : "";
    $date = isset($_POST['pullout_date']) ? $_POST['pullout_date'] : "";
    $status = isset($_POST['status']) ? $mysqli->real_escape_string($_POST['status']) : "Not Given";

    $mysqli->query("
        UPDATE pullout SET
            franchisee_name='$franchisee',
            area='$area',
            product_description='$description',
            reason='$reason',
            pullout_date='$date',
            status='$status'
        WHERE id=$id
    ");

    $message = "✅ Pull-Out Record Updated!";
}

/* -------------------------
   HANDLE DELETE
   ------------------------- */
if (isset($_POST['delete_record'])) {
    $id = intval($_POST['id']);
    $mysqli->query("DELETE FROM pullout WHERE id=$id");
    $message = "🗑️ Record Deleted Successfully!";
}

/* -------------------------
   HANDLE INSERT (SAVE)
   ------------------------- */
if (isset($_POST['save_pullout'])) {

    $franchisee = isset($_POST['franchisee_name']) ? $mysqli->real_escape_string($_POST['franchisee_name']) : "";
    $area = isset($_POST['area']) ? $mysqli->real_escape_string($_POST['area']) : "";
    $description = isset($_POST['product_description']) ? $mysqli->real_escape_string($_POST['product_description']) : "";
    $reason = isset($_POST['reason']) ? $mysqli->real_escape_string($_POST['reason']) : "";
    $date = isset($_POST['pullout_date']) ? $_POST['pullout_date'] : "";
    $status = isset($_POST['status']) ? "Given" : "Not Given";

    $uid = intval($_SESSION['user_id']);

    $mysqli->query("
        INSERT INTO pullout 
        (franchisee_name, area, product_description, reason, pullout_date, status, created_by)
        VALUES 
        ('$franchisee', '$area', '$description', '$reason', '$date', '$status', $uid)
    ");

    $message = "✅ Pull-Out Record Saved!";
}

/* -------------------------
   SEARCH & FILTER (GET)
   - search: keyword (franchisee, area, description, reason)
   - status: Given / Not Given / (empty = all)
   - from / to: date range (inclusive)
   ------------------------- */

$search = isset($_GET['search']) ? $mysqli->real_escape_string(trim($_GET['search'])) : "";
$filter_status = isset($_GET['status_filter']) ? $mysqli->real_escape_string($_GET['status_filter']) : "";

$where = "1=1";
if ($search !== "") {
    $kw = $mysqli->real_escape_string($search);
    $where .= " AND (franchisee_name LIKE '%$kw%' OR area LIKE '%$kw%' OR product_description LIKE '%$kw%' OR reason LIKE '%$kw%')";
}
if ($filter_status === "Given" || $filter_status === "Not Given") {
    $where .= " AND status = '$filter_status'";
}
/* -------------------------
   FETCH LOGS
   ------------------------- */
$logs = $mysqli->query("
    SELECT id, franchisee_name, area, product_description, reason, pullout_date, status, created_at
    FROM pullout
    WHERE $where
    ORDER BY created_at DESC
");

?>
<!DOCTYPE html>
<html>
<head>
<title>Pull Out</title>
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

/* small search/filter row */
.filter-row {
    display:flex;
    gap:10px;
    align-items:center;
    margin-bottom:12px;
}
.filter-row input[type="text"], .filter-row select, .filter-row input[type="date"] {
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}
.filter-row button {
    padding:8px 12px;
    border-radius:6px;
    border:none;
    background:#27ae60;
    color:#fff;
    cursor:pointer;
}

/* keep rest of your second style block (sidebar, etc) */
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

/* FORM CARD STYLING */
.card form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
}

.card input[type="text"],
.card input[type="date"],
.card textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    background: #fdfdfd;
    transition: 0.2s;
}

.card input:focus,
.card textarea:focus {
    border-color: #27ae60;
    box-shadow: 0 0 4px rgba(46, 204, 113, 0.6);
    outline: none;
}

.card button {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    padding: 12px;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    width: 200px;
}

.card button:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

/* SUCCESS MESSAGE */
.message {
    background: #2ecc71;
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: bold;
    width: 92%;
}

/* LAYOUT: FORM ON LEFT, HISTORY ON RIGHT */
.pullout-container {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 25px;
    margin-top: 20px;
}

/* HISTORY TABLE CARD */
.pullout-history {
    background: white;
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* TABLE DESIGN */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
    border-radius: 10px;
    overflow: hidden;
    background: white;
}

table th {
    background: #27ae60;
    color: white;
    padding: 12px;
    text-align: left;
    font-size: 15px;
}

table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    background: #fbfbfb;
}

table tr:hover td {
    background: #eefaf1;
}

/* FORM TITLE */
h1 {
    font-size: 32px;
    font-weight: bold;
    color: #2ecc71;
    margin-bottom: 15px;
    text-transform: uppercase;
}

/* MOBILE RESPONSIVE */
@media (max-width: 1000px) {
    .pullout-container {
        grid-template-columns: 1fr;
    }
}
.status-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-text {
    font-size: 15px;
    color: #333;
    cursor: pointer;
}
input[type="date"] {
    width: 180px;   /* adjust size here */
    padding: 10px;
}
/* Make ONLY the date input smaller */
.form-date {
    width: 160px;     /* adjust size */
    padding: 10px;
}
/* FORCE date input to stay small even inside a grid */
input[type="date'].small-date {
    width: 160px !important;
    max-width: 160px !important;
    display: inline-block !important;
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

<h1>Pull Out Form</h1>

<?php if ($message != "") echo "<div class='message'>$message</div>"; ?>

<div class="card">
    <form method="post">
        <input type="hidden" name="save_pullout" value="1">

        <label><b>Franchisee Name</b></label>
        <input type="text" name="franchisee_name" required>

        <label><b>Area</b></label>
        <input type="text" name="area" required>

        <label><b>Product Description</b></label>
        <textarea name="product_description" rows="3" required></textarea>

        <label><b>Reason</b></label>
        <textarea name="reason" rows="3" required></textarea>

        <label><b>Date</b></label>
<input type="date" name="pullout_date" class="small-date" required>



        <label><b>Status</b></label>
<div class="status-row">
    <input type="checkbox" name="status" id="status">
    <label for="status" class="status-text">Mark Check if Given</label>
</div>


        <br><br>
        <button type="submit">Save Pull Out</button>
    </form>
</div>
<div class="pullout-container">
    
    <div class="pullout-form">
        <!-- your pullout form here -->
    </div>

   
</div>

<div class="card">
    <h3>Pull-Out History</h3>

    <!-- SEARCH & FILTER (keeps look minimal, placed inside card above table) -->
    <div class="filter-row">
    <form method="get" style="display:flex; gap:8px; align-items:center;">
        <input type="text" name="search" placeholder="Search (franchisee, area, product...)" 
            value="<?= isset($_GET['search']) ? htmlentities($_GET['search']) : '' ?>">

        <select name="status_filter">
            <option value="">All Status</option>
            <option value="Given" <?= (isset($_GET['status_filter']) && $_GET['status_filter']=='Given') ? 'selected' : '' ?>>Given</option>
            <option value="Not Given" <?= (isset($_GET['status_filter']) && $_GET['status_filter']=='Not Given') ? 'selected' : '' ?>>Not Given</option>
        </select>

        <button type="submit">Search</button>

        <a href="pullout.php" 
           style="text-decoration:none; padding:8px 10px; border-radius:6px; background:#c0392b; color:#fff;">
           Reset
        </a>
    </form>
</div>


   <table>
    <tr>
        <th>Franchisee</th>
        <th>Area</th>
        <th>Product Description</th>
        <th>Reason</th>
        <th>Date</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php while($l = $logs->fetch_assoc()): ?>
        <tr>
            <td><?= $l['franchisee_name'] ?></td>
            <td><?= $l['area'] ?></td>
            <td><?= $l['product_description'] ?></td>
            <td><?= $l['reason'] ?></td>
            <td><?= $l['pullout_date'] ?></td>
            <td><?= $l['status'] ?></td>
            <td><?= $l['created_at'] ?></td>

            <td>
                <button 
                    class="btn edit-btn" 
                    onclick="openEditModal(
                        <?= $l['id'] ?>,
                        '<?= addslashes($l['franchisee_name']) ?>',
                        '<?= addslashes($l['area']) ?>',
                        '<?= addslashes($l['product_description']) ?>',
                        '<?= addslashes($l['reason']) ?>',
                        '<?= $l['pullout_date'] ?>',
                        '<?= $l['status'] ?>'
                    )"
                >
                    Edit
                </button>

                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_record" value="1">
                    <input type="hidden" name="id" value="<?= $l['id'] ?>">
                    <button 
                        class="btn delete-btn"
                        onclick="return confirm('Delete this record?')"
                    >Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</div>

</div>
<!-- EDIT MODAL -->
<!-- ============================
      EDIT MODAL
============================ -->
<style>
    /* Fade animation */
    @keyframes modalFade {
        from { opacity:0; transform:scale(0.92); }
        to   { opacity:1; transform:scale(1); }
    }

    /* Smooth inputs */
    #editModal input, 
    #editModal textarea, 
    #editModal select {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    /* Title styling */
    #editModal h3 {
        text-align: center;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 20px;
        font-weight: bold;
    }

    /* Modal container */
    #editModal .modal-box {
        background:white;
        padding:25px;
        width:420px;
        border-radius:12px;
        box-shadow:0 8px 20px rgba(0,0,0,0.35);
        animation: modalFade 0.25s ease;
    }
</style>

<div id="editModal" style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    backdrop-filter: blur(1px);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <div class="modal-box">

        <h3>Edit Pull-Out Record</h3>

        <form method="post">
            <input type="hidden" name="update_record" value="1">
            <input type="hidden" name="id" id="edit_id">

            <label>Franchisee Name</label>
            <input type="text" id="edit_franchisee" name="franchisee_name" required>

            <label>Area</label>
            <input type="text" id="edit_area" name="area" required>

            <label>Product Description</label>
            <textarea id="edit_description" name="product_description" required></textarea>

            <label>Reason</label>
            <textarea id="edit_reason" name="reason" required></textarea>

            <label>Date</label>
            <input type="date" id="edit_date" name="pullout_date" required>

            <label>Status</label>
            <select id="edit_status" name="status">
                <option value="Not Given">Not Given</option>
                <option value="Given">Given</option>
            </select>

            <br>

            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                <button class="btn edit-btn" type="submit">Save Changes</button>
                <button class="btn delete-btn" type="button" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>

    </div>
</div>

<script>
function openEditModal(id, f, a, d, r, date, status) {
    document.getElementById("editModal").style.display = "flex";

    document.getElementById("edit_id").value = id;
    document.getElementById("edit_franchisee").value = f;
    document.getElementById("edit_area").value = a;
    document.getElementById("edit_description").value = d;
    document.getElementById("edit_reason").value = r;
    document.getElementById("edit_date").value = date;
    document.getElementById("edit_status").value = status;
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}
</script>

<!-- ============================
      EDIT MODAL (NEW DESIGN)
============================ -->
<div id="editModal" style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    backdrop-filter: blur(2px);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <div style="
        background:#fff;
        width:420px;
        padding:25px;
        border-radius:12px;
        box-shadow:0 8px 20px rgba(0,0,0,0.25);
        animation: fadeIn 0.25s ease-out;
    ">
        
        <h3 style="margin-top:0; text-align:center;">Edit Pull-Out Record</h3>

        <form method="post">
            <input type="hidden" name="update_record" value="1">
            <input type="hidden" name="id" id="edit_id">

            <label>Franchisee Name</label>
            <input type="text" name="franchisee_name" id="edit_franchisee" required style="width:100%; margin-bottom:8px;">

            <label>Area</label>
            <input type="text" name="area" id="edit_area" required style="width:100%; margin-bottom:8px;">

            <label>Product Description</label>
            <textarea name="product_description" id="edit_description" required style="width:100%; height:80px; margin-bottom:8px;"></textarea>

            <label>Reason</label>
            <textarea name="reason" id="edit_reason" required style="width:100%; height:80px; margin-bottom:8px;"></textarea>

            <label>Pull-Out Date</label>
            <input type="date" name="pullout_date" id="edit_date" required style="width:100%; margin-bottom:8px;">

            <label>Status</label>
            <select name="status" id="edit_status" style="width:100%; margin-bottom:15px;">
                <option value="Given">Given</option>
                <option value="Not Given">Not Given</option>
            </select>

            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                <button type="submit" style="
                    padding:8px 16px;
                    background:#007bff;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    cursor:pointer;
                ">Save</button>

                <button type="button" onclick="closeEditModal()" style="
                    padding:8px 16px;
                    background:#777;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    cursor:pointer;
                ">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity:0; transform:scale(0.95); }
    to   { opacity:1; transform:scale(1); }
}
</style>

</body>
</html>
