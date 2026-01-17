<?php
session_start();
require_once __DIR__ . '/dbconn.php';

$conn = $dbconn; 

/* 🔒 PROTECT DASHBOARD */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* 👤 TOTAL USERS */
$qUsers = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM UserAccount");
oci_execute($qUsers);
$users = oci_fetch_assoc($qUsers);

/* 📦 TOTAL PRODUCTS */
$qProducts = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM Product");
oci_execute($qProducts);
$products = oci_fetch_assoc($qProducts);

/* 💰 TOTAL SALES */
$qSales = oci_parse($conn, "SELECT NVL(SUM(totalAmount), 0) AS TOTAL FROM Sales");
oci_execute($qSales);
$sales = oci_fetch_assoc($qSales);

/* ⚠️ LOW STOCK */
$qLowStock = oci_parse($conn, "
    SELECT productName, quantityInStock, reorderLevel 
    FROM Product 
    WHERE quantityInStock <= reorderLevel
");
oci_execute($qLowStock);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Koperasi Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            display: flex; /* Flexbox for sidebar layout */
            min-height: 100vh;
        }

        /* --- SIDEBAR STYLES --- */
        .sidebar {
            width: 240px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            background: #1a252f;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .nav-links {
            flex: 1; /* Pushes the logout button to the bottom */
            padding-top: 20px;
        }

        .nav-links a {
            display: block;
            color: #bdc3c7;
            padding: 15px 25px;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-links a:hover {
            background: #34495e;
            color: white;
            border-left: 4px solid #3498db;
        }

        .nav-links a.active {
            background: #34495e;
            color: white;
            border-left: 4px solid #3498db;
        }

        .logout-link {
            padding: 20px 25px;
            background: #c0392b;
            color: white;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
        }

        .logout-link:hover {
            background: #e74c3c;
        }

        /* --- MAIN CONTENT STYLES --- */
        .main-content {
            margin-left: 240px; /* Same as sidebar width */
            padding: 30px;
            width: 100%;
        }

        header {
            margin-bottom: 30px;
        }

        .cards {
            display: flex;
            gap: 20px;
        }

        .card {
            background: white;
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .card h2 {
            font-size: 32px;
            margin: 0;
            color: #2c3e50;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #34495e;
            color: white;
            font-weight: 500;
        }

        .welcome-text {
            color: #7f8c8d;
            font-size: 1rem;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        🛒 KOPERASI SYSTEM
    </div>
    <nav class="nav-links">
        <a href="cashier.php">💳 Cashier</a>
        <a href="index.php" class="active">🏠 Home</a>
        <a href="manage_product.php">📦 Manage Product</a>
        <a href="analytics.php">📈 View Analytics</a>
        <a href="restock_product.php">🚚 Restock Product</a>
        <a href="add_supplier.php">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <header>
        <h1>Dashboard Overview</h1>
        <span class="welcome-text">Logged in as: <strong><?= $_SESSION['user_name']; ?></strong></span>
    </header>

    <div class="cards">
        <div class="card">
            <h2><?= $users['TOTAL']; ?></h2>
            <p>Total Users</p>
        </div>

        <div class="card">
            <h2><?= $products['TOTAL']; ?></h2>
            <p>Total Products</p>
        </div>

        <div class="card">
            <h2>RM <?= number_format($sales['TOTAL'], 2); ?></h2>
            <p>Total Sales</p>
        </div>
    </div>

    <h3 style="margin-top:40px;">⚠️ Low Stock Products</h3>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Stock Remaining</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($qLowStock)) { ?>
            <tr>
                <td><?= $row['PRODUCTNAME']; ?></td>
                <td style="color: #c0392b; font-weight: bold;"><?= $row['QUANTITYINSTOCK']; ?></td>
                <td><?= $row['REORDERLEVEL']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>