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

/* 📥 HANDLE RESTOCK SUBMISSION */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnRestock'])) {
    $productID    = $_POST['productID'];
    $supplierID   = $_POST['supplierID'];
    $quantity     = (int)$_POST['quantity'];
    $unitCost     = $_POST['unitCost'];
    $invoiceRef   = $_POST['invoiceRef'];
    $totalCost    = $quantity * $unitCost;

    // 1. Update Product Quantity
    $queryUpdate = "UPDATE Product 
                    SET quantityInStock = quantityInStock + :qty, 
                        lastDateRestock = SYSDATE 
                    WHERE productID = :pid";

    // 2. Insert into RestockDetail (Ensure sequence 'restock_detail_seq' exists)
    $queryLog = "INSERT INTO RestockDetail 
                 (restockDetailID, supplierID, productID, quantityRestock, dateTimeRestock, unitCost, totalCost, invoiceRef) 
                 VALUES (restock_detail_seq.NEXTVAL, :sid, :pid, :qty, SYSTIMESTAMP, :uCost, :tCost, :ref)";

    $stmtUpdate = oci_parse($conn, $queryUpdate);
    $stmtLog = oci_parse($conn, $queryLog);

    oci_bind_by_name($stmtUpdate, ':qty', $quantity);
    oci_bind_by_name($stmtUpdate, ':pid', $productID);

    oci_bind_by_name($stmtLog, ':sid', $supplierID);
    oci_bind_by_name($stmtLog, ':pid', $productID);
    oci_bind_by_name($stmtLog, ':qty', $quantity);
    oci_bind_by_name($stmtLog, ':uCost', $unitCost);
    oci_bind_by_name($stmtLog, ':tCost', $totalCost);
    oci_bind_by_name($stmtLog, ':ref', $invoiceRef);

    $res1 = oci_execute($stmtUpdate, OCI_NO_AUTO_COMMIT);
    $res2 = oci_execute($stmtLog, OCI_NO_AUTO_COMMIT);

    if ($res1 && $res2) {
        oci_commit($conn);
        $message = "<div style='color:#27ae60; background:#d4edda; padding:10px; border-radius:5px; margin-bottom:20px;'><b>Success:</b> Stock updated and restock logged!</div>";
    } else {
        oci_rollback($conn);
        $e = oci_error($stmtLog);
        $message = "<div style='color:#c0392b; background:#f8d7da; padding:10px; border-radius:5px; margin-bottom:20px;'><b>Error:</b> " . htmlentities($e['message']) . "</div>";
    }
}

/* 🔍 FETCH DROPDOWN DATA */
$sProducts = oci_parse($conn, "SELECT productID, productName, quantityInStock FROM Product ORDER BY productName ASC");
oci_execute($sProducts);

$sSuppliers = oci_parse($conn, "SELECT supplierID, companyName FROM Supplier ORDER BY companyName ASC");
oci_execute($sSuppliers);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restock Product - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* 🚀 SIDEBAR STYLING (Matches Manage Products) */
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
        .logout-link { margin-top: auto; padding: 20px 25px; background: #c0392b; color: white; text-decoration: none; text-align: center; font-weight: bold; transition: 0.3s; }
        .logout-link:hover { background: #e74c3c; }

        /* Main Content area */
        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        
        /* 📝 FORM BOX STYLING */
        .form-container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            max-width: 600px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #34495e; }
        select, input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 14px;
            box-sizing: border-box; 
        }
        .btn-submit { 
            background: #3498db; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
            width: 100%; 
            font-size: 16px;
        }
        .btn-submit:hover { background: #2980b9; }
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
        <a href="restock_product.php" class="active">🚚 Restock Product</a>
        <a href="add_supplier.php">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <h1>Restock Inventory</h1>
    
    <div class="form-container">
        <?= $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Select Product</label>
                <select name="productID" required>
                    <option value="">-- Choose Product --</option>
                    <?php while ($row = oci_fetch_assoc($sProducts)): ?>
                        <option value="<?= $row['PRODUCTID']; ?>">
                            <?= $row['PRODUCTNAME']; ?> (Current Stock: <?= $row['QUANTITYINSTOCK']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select name="supplierID" required>
                    <option value="">-- Choose Supplier --</option>
                    <?php while ($row = oci_fetch_assoc($sSuppliers)): ?>
                        <option value="<?= $row['SUPPLIERID']; ?>">
                            <?= $row['COMPANYNAME']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Quantity to Add</label>
                    <input type="number" name="quantity" min="1" required placeholder="0">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Unit Cost (RM)</label>
                    <input type="number" step="0.01" name="unitCost" required placeholder="0.00">
                </div>
            </div>

            <div class="form-group">
                <label>Invoice Reference</label>
                <input type="text" name="invoiceRef" placeholder="e.g. INV-2024-001">
            </div>

            <button type="submit" name="btnRestock" class="btn-submit">Update Inventory</button>
        </form>
    </div>
</div>

</body>
</html>