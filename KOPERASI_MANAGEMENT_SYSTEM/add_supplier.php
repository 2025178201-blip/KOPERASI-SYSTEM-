<?php
session_start();
require_once __DIR__ . '/dbconn.php';
$conn = $dbconn;

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

/* 📥 HANDLE ADD SUPPLIER SUBMISSION */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnAddSupplier'])) {
    $companyName    = $_POST['companyName'];
    $personInCharge = $_POST['personInCharge'];
    $contactNumber  = $_POST['contactNumber'];
    $companyAddress = $_POST['companyAddress'];

    // SQL for Oracle - Using a sequence or manual ID logic
    // Assuming you have a sequence named 'supplier_seq'
    $queryInsert = "INSERT INTO Supplier (supplierID, companyName, personInCharge, contactNumber, companyAddress) 
                    VALUES (supplier_seq.NEXTVAL, :cname, :pic, :contact, :addr)";

    $stmtInsert = oci_parse($conn, $queryInsert);

    oci_bind_by_name($stmtInsert, ':cname', $companyName);
    oci_bind_by_name($stmtInsert, ':pic', $personInCharge);
    oci_bind_by_name($stmtInsert, ':contact', $contactNumber);
    oci_bind_by_name($stmtInsert, ':addr', $companyAddress);

    if (oci_execute($stmtInsert)) {
        oci_commit($conn);
        $message = "<div style='color:#27ae60; background:#d4edda; padding:10px; border-radius:5px; margin-bottom:20px;'>✅ Success: New supplier added!</div>";
    } else {
        $e = oci_error($stmtInsert);
        $message = "<div style='color:#c0392b; background:#f8d7da; padding:10px; border-radius:5px; margin-bottom:20px;'>❌ Error: " . htmlentities($e['message']) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* 🚀 SIDEBAR (Consistent with your other pages) */
        .sidebar { 
            width: 240px; 
            background: #2c3e50; 
            color: white; 
            position: fixed; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; }
        .nav-links { flex: 1; }
        .nav-links a { display: block; color: #bdc3c7; padding: 15px 25px; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .logout-link { margin-top: auto; padding: 20px 25px; background: #c0392b; color: white; text-decoration: none; text-align: center; font-weight: bold; }

        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #34495e; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: inherit; }
        textarea { resize: vertical; height: 100px; }
        .btn-submit { background: #27ae60; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">🛒 KOPERASI SYSTEM</div>
    <nav class="nav-links">
        <a href="cashier.php">💳 Cashier</a>
        <a href="index.php">🏠 Home</a>
        <a href="manage_product.php">📦 Manage Product</a>
        <a href="analytics.php">📈 View Analytics</a>
        <a href="restock_product.php">🚚 Restock Product</a>
        <a href="add_supplier.php" class="active">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <h1>Add New Supplier</h1>
    
    <div class="form-container">
        <?= $message; ?>
        <form method="POST">
            <div class="form-group">
                <label>🏢 Company Name</label>
                <input type="text" name="companyName" required placeholder="Enter company name">
            </div>

            <div class="form-group">
                <label>👤 Person In Charge</label>
                <input type="text" name="personInCharge" required placeholder="Full name">
            </div>

            <div class="form-group">
                <label>📞 Contact Number</label>
                <input type="text" name="contactNumber" required placeholder="e.g. 012-3456789">
            </div>

            <div class="form-group">
                <label>📍 Company Address</label>
                <textarea name="companyAddress" required placeholder="Enter full address"></textarea>
            </div>

            <button type="submit" name="btnAddSupplier" class="btn-submit">➕ Register Supplier</button>
        </form>
    </div>
</div>

</body>
</html>