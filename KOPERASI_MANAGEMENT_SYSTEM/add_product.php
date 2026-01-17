<?php
session_start();
require_once __DIR__ . '/dbconn.php';
$conn = $dbconn;

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['save_product'])) {
    $cat = $_POST['category'];
    $seqName = "";

    switch ($cat) {
        case "Food": $seqName = "food_seq.NEXTVAL"; break;
        case "SchoolClothing": $seqName = "clothing_seq.NEXTVAL"; break;
        case "SchoolMerchandise": $seqName = "merch_seq.NEXTVAL"; break;
        case "Seasonal": $seqName = "seasonal_seq.NEXTVAL"; break;
    }

    if ($seqName != "") {
        $q = oci_parse($conn, "SELECT $seqName FROM dual");
        oci_execute($q);
        $row = oci_fetch_array($q);
        $productID = $row[0];

        $sqlProduct = "INSERT INTO Product (
            productID, supplierID, productName, sellingPrice, 
            unitSize, description, quantityInStock, lastDateRestock, 
            reorderLevel, category
        ) VALUES (
            :pid, :sid, :pname, :price, :usize, :descrip, :qty, SYSDATE, :reorder, :cat
        )";

        $p = oci_parse($conn, $sqlProduct);
        oci_bind_by_name($p, ":pid", $productID);
        oci_bind_by_name($p, ":sid", $_POST['supplierID']);
        oci_bind_by_name($p, ":pname", $_POST['productName']);
        oci_bind_by_name($p, ":price", $_POST['sellingPrice']);
        oci_bind_by_name($p, ":usize", $_POST['unitSize']);
        oci_bind_by_name($p, ":descrip", $_POST['description']);
        oci_bind_by_name($p, ":qty", $_POST['quantityInStock']);
        oci_bind_by_name($p, ":reorder", $_POST['reorderLevel']);
        oci_bind_by_name($p, ":cat", $cat);
        
        $executeParent = oci_execute($p, OCI_NO_AUTO_COMMIT);

        if ($executeParent) {
            $successChild = false;
            if ($cat === "Food") {
                $sql = "INSERT INTO Food (productID, brand, flavour, expiryDate) VALUES (:pid, :brand, :flavour, TO_DATE(:expiry, 'YYYY-MM-DD'))";
                $s = oci_parse($conn, $sql);
                oci_bind_by_name($s, ":pid", $productID);
                oci_bind_by_name($s, ":brand", $_POST['brand']);
                oci_bind_by_name($s, ":flavour", $_POST['flavour']);
                oci_bind_by_name($s, ":expiry", $_POST['expiryDate']);
                $successChild = oci_execute($s, OCI_NO_AUTO_COMMIT);
            } elseif ($cat === "SchoolClothing") {
                $sql = "INSERT INTO SchoolClothing (productID, sizeCloth, colour, sportsHouse, sleeveType) VALUES (:pid, :sz, :col, :house, :sleeve)";
                $s = oci_parse($conn, $sql);
                oci_bind_by_name($s, ":pid", $productID);
                oci_bind_by_name($s, ":sz", $_POST['sizeCloth']);
                oci_bind_by_name($s, ":col", $_POST['colour']);
                oci_bind_by_name($s, ":house", $_POST['sportsHouse']);
                oci_bind_by_name($s, ":sleeve", $_POST['sleeveType']);
                $successChild = oci_execute($s, OCI_NO_AUTO_COMMIT);
            } elseif ($cat === "SchoolMerchandise") {
                $sql = "INSERT INTO SchoolMerchandise (productID, itemType, sizeMerch) VALUES (:pid, :item, :sz)";
                $s = oci_parse($conn, $sql);
                oci_bind_by_name($s, ":pid", $productID);
                oci_bind_by_name($s, ":item", $_POST['itemType']);
                oci_bind_by_name($s, ":sz", $_POST['sizeMerch']);
                $successChild = oci_execute($s, OCI_NO_AUTO_COMMIT);
            } elseif ($cat === "Seasonal") {
                $sql = "INSERT INTO Seasonal (productID, eventName, startDateEvent, endDateEvent, limitedEdition) VALUES (:pid, :event, TO_DATE(:sd, 'YYYY-MM-DD'), TO_DATE(:ed, 'YYYY-MM-DD'), :lim)";
                $s = oci_parse($conn, $sql);
                oci_bind_by_name($s, ":pid", $productID);
                oci_bind_by_name($s, ":event", $_POST['eventName']);
                oci_bind_by_name($s, ":sd", $_POST['startDateEvent']);
                oci_bind_by_name($s, ":ed", $_POST['endDateEvent']);
                oci_bind_by_name($s, ":lim", $_POST['limitedEdition']);
                $successChild = oci_execute($s, OCI_NO_AUTO_COMMIT);
            }

            if ($successChild) {
                oci_commit($conn);
                echo "<script>alert('Success! Product ID $productID created.'); window.location='manage_product.php';</script>";
            } else {
                oci_rollback($conn);
                echo "<script>alert('Failed to save category details.');</script>";
            }
        }
    }
}

$qSuppliers = oci_parse($conn, "SELECT supplierID, companyName FROM Supplier ORDER BY companyName");
oci_execute($qSuppliers);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* Sidebar (Matching Manage Product) */
        .sidebar { width: 240px; background: #2c3e50; color: white; position: fixed; height: 100%; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; }
        .nav-links { flex: 1; padding-top: 20px; }
        .nav-links a { display: block; color: #bdc3c7; padding: 15px 25px; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .logout-link { padding: 20px 25px; background: #c0392b; color: white; text-decoration: none; text-align: center; }

        /* Main Content */
        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        
        /* Breadcrumbs */
        .breadcrumbs { margin-bottom: 20px; font-size: 14px; color: #7f8c8d; }
        .breadcrumbs a { color: #3498db; text-decoration: none; }
        .breadcrumbs a:hover { text-decoration: underline; }

        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 800px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .cat { display: none; border: 1px dashed #3498db; padding: 20px; margin-top: 20px; background: #f0f7fd; border-radius: 5px; }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50; font-size: 14px; }
        input, select, textarea { padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        h2, h3 { color: #2c3e50; margin-top: 0; }
        
        .btn-save { background: #27ae60; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; width: auto; margin-top: 20px; }
        .btn-save:hover { background: #2ecc71; }
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
        <span>Add New Product</span>
    </div>

    <div class="form-container">
        <h2>Add New Product</h2>
        <form method="POST">
            <label>Product Category</label>
            <select name="category" id="category" onchange="toggleCategory()" required>
                <option value="">-- Select Category --</option>
                <option value="Food">Food</option>
                <option value="SchoolClothing">School Clothing</option>
                <option value="SchoolMerchandise">School Merchandise</option>
                <option value="Seasonal">Seasonal</option>
            </select>

            <div id="parentProduct" style="display:none; margin-top: 25px;">
                <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
                <h3>General Information</h3>
                <div class="form-grid">
                    <div>
                        <label>Supplier</label>
                        <select name="supplierID" required>
                            <option value="">-- Select Supplier --</option>
                            <?php while ($rowS = oci_fetch_assoc($qSuppliers)) { ?>
                                <option value="<?= $rowS['SUPPLIERID'] ?>"><?= $rowS['COMPANYNAME'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label>Product Name</label>
                        <input name="productName" placeholder="Enter name" required>
                    </div>
                    <div>
                        <label>Selling Price (RM)</label>
                        <input name="sellingPrice" type="number" step="0.01" required>
                    </div>
                    <div>
                        <label>Unit Size</label>
                        <input name="unitSize" placeholder="e.g. 500g, XL, 1pc">
                    </div>
                    <div>
                        <label>Initial Stock</label>
                        <input name="quantityInStock" type="number" required>
                    </div>
                    <div>
                        <label>Reorder Level</label>
                        <input name="reorderLevel" type="number">
                    </div>
                    <div style="grid-column: span 2;">
                        <label>Description</label>
                        <input name="description" placeholder="Brief details about the product">
                    </div>
                </div>

                <div id="Food" class="cat">
                    <h3>🍔 Food Details</h3>
                    <div class="form-grid">
                        <input name="brand" placeholder="Brand">
                        <input name="flavour" placeholder="Flavour">
                        <div>
                            <label>Expiry Date</label>
                            <input type="date" name="expiryDate">
                        </div>
                    </div>
                </div>

                <div id="SchoolClothing" class="cat">
                    <h3>👕 Clothing Details</h3>
                    <div class="form-grid">
                        <input name="sizeCloth" placeholder="Size (e.g. S, M, L)">
                        <input name="colour" placeholder="Colour">
                        <input name="sportsHouse" placeholder="Sports House">
                        <input name="sleeveType" placeholder="Sleeve Type">
                    </div>
                </div>

                <div id="SchoolMerchandise" class="cat">
                    <h3>🎁 Merchandise Details</h3>
                    <div class="form-grid">
                        <input name="itemType" placeholder="Item Type">
                        <input name="sizeMerch" placeholder="Dimensions">
                    </div>
                </div>

                <div id="Seasonal" class="cat">
                    <h3>📅 Seasonal Details</h3>
                    <div class="form-grid">
                        <input name="eventName" placeholder="Event Name">
                        <div><label>Start</label><input type="date" name="startDateEvent"></div>
                        <div><label>End</label><input type="date" name="endDateEvent"></div>
                        <select name="limitedEdition">
                            <option value="NO">Limited Edition: NO</option>
                            <option value="YES">Limited Edition: YES</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="save_product" class="btn-save">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCategory() {
    const selected = document.getElementById('category').value;
    const parentDiv = document.getElementById('parentProduct');
    document.querySelectorAll('.cat').forEach(div => div.style.display = 'none');
    
    if (selected !== "") {
        parentDiv.style.display = 'block';
        document.getElementById(selected).style.display = 'block';
    } else {
        parentDiv.style.display = 'none';
    }
}
</script>

</body>
</html>