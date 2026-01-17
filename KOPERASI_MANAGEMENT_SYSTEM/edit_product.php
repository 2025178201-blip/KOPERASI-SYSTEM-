<?php
session_start();
require_once __DIR__ . '/dbconn.php';
$conn = $dbconn;

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_product.php");
    exit();
}

$productID = $_GET['id'];

/* 1. FETCH CURRENT PRODUCT DATA */
$qFetch = oci_parse($conn, "SELECT * FROM Product WHERE productID = :id");
oci_bind_by_name($qFetch, ':id', $productID);
oci_execute($qFetch);
$product = oci_fetch_assoc($qFetch);

if (!$product) {
    echo "<script>alert('Product not found!'); window.location='manage_product.php';</script>";
    exit();
}

/* 2. HANDLE UPDATE LOGIC */
if (isset($_POST['update_product'])) {
    $name = $_POST['productName'];
    $price = $_POST['sellingPrice'];
    $stock = $_POST['quantityInStock'];
    $reorder = $_POST['reorderLevel'];

    $qUpdate = oci_parse($conn, "
        UPDATE Product 
        SET productName = :name, 
            sellingPrice = :price, 
            quantityInStock = :stock, 
            reorderLevel = :reorder 
        WHERE productID = :id
    ");

    oci_bind_by_name($qUpdate, ':name', $name);
    oci_bind_by_name($qUpdate, ':price', $price);
    oci_bind_by_name($qUpdate, ':stock', $stock);
    oci_bind_by_name($qUpdate, ':reorder', $reorder);
    oci_bind_by_name($qUpdate, ':id', $productID);

    if (oci_execute($qUpdate)) {
        oci_commit($conn);
        echo "<script>alert('Product Updated!'); window.location='manage_product.php';</script>";
    } else {
        $e = oci_error($qUpdate);
        echo "<script>alert('Error: " . htmlentities($e['message']) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* Sidebar (Matching Manage Product/Cashier) */
        .sidebar { width: 240px; background: #2c3e50; color: white; position: fixed; height: 100%; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; }
        .nav-links { flex: 1; padding-top: 20px; }
        .nav-links a { display: block; color: #bdc3c7; padding: 15px 25px; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .logout-link { padding: 20px 25px; background: #c0392b; color: white; text-decoration: none; text-align: center; }

        /* Main Content */
        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        
        /* Breadcrumbs (Matching Style) */
        .breadcrumbs { margin-bottom: 20px; font-size: 14px; color: #7f8c8d; }
        .breadcrumbs a { color: #3498db; text-decoration: none; }
        .breadcrumbs a:hover { text-decoration: underline; }

        .form-container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            max-width: 600px;
        }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50; font-size: 14px; }
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
            font-size: 1rem;
        }

        .btn-save { 
            background: #3498db; 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .btn-save:hover { background: #2980b9; }
        
        .btn-cancel { 
            color: #7f8c8d; 
            text-decoration: none; 
            margin-left: 15px; 
            font-size: 14px;
        }
        .btn-cancel:hover { text-decoration: underline; }

        h2 { color: #2c3e50; margin-top: 0; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">🛒 KOPERASI SYSTEM</div>
    <nav class="nav-links">
        <a href="cashier.php">💳 Cashier</a>
        <a href="index.php">🏠 Home</a>
        <a href="manage_product.php" class="active">📦 Manage Product</a>
        <a href="analytics.php">📈 View Analytics</a>
        <a href="restock_product.php">🚚 Restock Product</a>
        <a href="add_supplier.php">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="breadcrumbs">
        🏠 <a href="index.php">Home</a> &nbsp; / &nbsp; 
        <a href="manage_product.php">Manage Product</a> &nbsp; / &nbsp; 
        <span>Edit Product</span>
    </div>

    <div class="form-container">
        <h2>Edit Product: <?= htmlentities($product['PRODUCTNAME']); ?></h2>
        <form method="POST">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="productName" value="<?= htmlentities($product['PRODUCTNAME']); ?>" required>
            </div>

            <div class="form-group">
                <label>Selling Price (RM)</label>
                <input type="number" step="0.01" name="sellingPrice" value="<?= $product['SELLINGPRICE']; ?>" required>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Quantity in Stock</label>
                    <input type="number" name="quantityInStock" value="<?= $product['QUANTITYINSTOCK']; ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Reorder Level</label>
                    <input type="number" name="reorderLevel" value="<?= $product['REORDERLEVEL']; ?>" required>
                </div>
            </div>

            <button type="submit" name="update_product" class="btn-save">Update Changes</button>
            <a href="manage_product.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>