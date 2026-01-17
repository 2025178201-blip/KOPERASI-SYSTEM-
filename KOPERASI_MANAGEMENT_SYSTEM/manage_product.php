<?php
session_start();
require_once __DIR__ . '/dbconn.php';
$conn = $dbconn;

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* 🗑️ HANDLE DELETE REQUEST */
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $childTables = ['Food', 'SchoolClothing', 'SchoolMerchandise', 'Seasonal', 'SalesDetail', 'RestockDetail', 'ProductTransaction'];
    foreach ($childTables as $table) {
        $q = oci_parse($conn, "DELETE FROM $table WHERE productID = :id");
        oci_bind_by_name($q, ':id', $id);
        oci_execute($q);
    }
    $qDelete = oci_parse($conn, "DELETE FROM Product WHERE productID = :id");
    oci_bind_by_name($qDelete, ':id', $id);
    if (oci_execute($qDelete)) {
        oci_commit($conn);
        echo "<script>alert('Product deleted!'); window.location='manage_product.php';</script>";
    }
}

/* 🔍 HANDLING FILTERS & SEARCH */
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'p.productID';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$nextOrder = ($order == 'ASC') ? 'DESC' : 'ASC';

$query = "SELECT p.*, s.companyName 
          FROM Product p 
          JOIN Supplier s ON p.supplierID = s.supplierID 
          WHERE (UPPER(p.productNAME) LIKE UPPER(:search) 
             OR UPPER(p.productID) LIKE UPPER(:search) 
             OR UPPER(p.CATEGORY) LIKE UPPER(:search))";

if ($cat_filter != '') {
    $query .= " AND p.CATEGORY = :cat ";
}

$query .= " ORDER BY $sort $order";

$stmt = oci_parse($conn, $query);
$searchParam = "%$search%";
oci_bind_by_name($stmt, ':search', $searchParam);
if ($cat_filter != '') {
    oci_bind_by_name($stmt, ':cat', $cat_filter);
}
oci_execute($stmt);

$catQuery = oci_parse($conn, "SELECT DISTINCT CATEGORY FROM Product ORDER BY CATEGORY");
oci_execute($catQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* 🚀 SIDEBAR FLEXBOX UPDATE */
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
        
        .nav-links { flex: 1; } /* This allows the nav area to take up available space */
        
        .nav-links a { display: block; color: #bdc3c7; padding: 15px 25px; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        
        /* 🚪 LOGOUT AT BOTTOM */
        .logout-link { 
            margin-top: auto; /* Pushes the button to the bottom */
            padding: 20px 25px; 
            background: #c0392b; 
            color: white; 
            text-decoration: none; 
            text-align: center; 
            font-weight: bold;
            transition: 0.3s;
        }
        .logout-link:hover { background: #e74c3c; }

        /* Main Content */
        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        .filter-container { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .filter-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .filter-form input, .filter-form select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-filter { background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        th { background: #34495e; color: white; text-transform: uppercase; padding: 12px 15px; font-size: 13px; }
        th a { color: white; text-decoration: none; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .badge-low { background: #fff3cd; color: #856404; padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
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
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1>Product Inventory</h1>
        <a href="add_product.php" style="background:#27ae60; color:white; padding:10px 20px; border-radius:5px; text-decoration:none; font-weight:bold;">+ Add Product</a>
    </div>

    <div class="filter-container">
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>">
            <select name="category_filter">
                <option value="">-- All Categories --</option>
                <?php while ($c = oci_fetch_assoc($catQuery)): ?>
                    <option value="<?= $c['CATEGORY'] ?>" <?= ($cat_filter == $c['CATEGORY']) ? 'selected' : '' ?>>
                        <?= $c['CATEGORY'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-filter">Apply Filters</button>
            <?php if($search || $cat_filter): ?>
                <a href="manage_product.php" style="color:#e74c3c; font-size:13px; text-decoration:none;">Clear All</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th><a href="?sort=p.productID&order=<?= $nextOrder ?>&search=<?= $search ?>&category_filter=<?= $cat_filter ?>">ID ↕</a></th>
                <th><a href="?sort=p.productNAME&order=<?= $nextOrder ?>&search=<?= $search ?>&category_filter=<?= $cat_filter ?>">Product Name ↕</a></th>
                <th><a href="?sort=p.CATEGORY&order=<?= $nextOrder ?>&search=<?= $search ?>&category_filter=<?= $cat_filter ?>">Category ↕</a></th>
                <th>Supplier</th>
                <th><a href="?sort=p.SELLINGPRICE&order=<?= $nextOrder ?>&search=<?= $search ?>&category_filter=<?= $cat_filter ?>">Price ↕</a></th>
                <th><a href="?sort=p.QUANTITYINSTOCK&order=<?= $nextOrder ?>&search=<?= $search ?>&category_filter=<?= $cat_filter ?>">Stock ↕</a></th>
                <th>Last Restock</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
                <tr>
                    <td><strong><?= $row['PRODUCTID']; ?></strong></td>
                    <td><?= $row['PRODUCTNAME']; ?></td>
                    <td><?= $row['CATEGORY']; ?></td>
                    <td><?= $row['COMPANYNAME']; ?></td>
                    <td>RM <?= number_format($row['SELLINGPRICE'], 2); ?></td>
                    <td>
                        <?= $row['QUANTITYINSTOCK']; ?>
                        <?php if($row['QUANTITYINSTOCK'] <= $row['REORDERLEVEL']): ?>
                            <span class="badge-low">LOW STOCK</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['LASTDATERESTOCK'] ? date('d M Y', strtotime($row['LASTDATERESTOCK'])) : 'N/A'; ?></td>
                    <td style="text-align:right;">
                        <a href="edit_product.php?id=<?= $row['PRODUCTID']; ?>" style="color:#3498db; text-decoration:none;">Edit</a> | 
                        <a href="manage_product.php?delete_id=<?= $row['PRODUCTID']; ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('Confirm delete?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>