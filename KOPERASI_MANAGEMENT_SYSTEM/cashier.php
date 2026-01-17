<?php
// Include your existing connection file
include('dbconn.php');
session_start();

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// --- PROCESS THE SALE TRANSACTION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalize_sale'])) {
    $userID = $_POST['userID'];
    $totalAmount = $_POST['totalAmount'];
    
    $productIDs = isset($_POST['prodID']) ? $_POST['prodID'] : [];
    $quantities = isset($_POST['qty']) ? $_POST['qty'] : [];
    $prices = isset($_POST['price']) ? $_POST['price'] : [];

    $saleID = time(); 
    
    $sqlSales = "INSERT INTO Sales (saleID, userID, saleDate, totalAmount) 
                 VALUES (:b_saleid, :b_userid, SYSDATE, :b_total)";
    
    $stmtSales = oci_parse($dbconn, $sqlSales);
    oci_bind_by_name($stmtSales, ':b_saleid', $saleID);
    oci_bind_by_name($stmtSales, ':b_userid', $userID);
    oci_bind_by_name($stmtSales, ':b_total', $totalAmount);
    
    $result = oci_execute($stmtSales, OCI_NO_AUTO_COMMIT);

    if ($result) {
        $error_occurred = false;

        for ($i = 0; $i < count($productIDs); $i++) {
            $curr_pid   = $productIDs[$i];
            $curr_qty   = $quantities[$i];
            $curr_uPrice = $prices[$i];
            $curr_tPrice = $curr_qty * $curr_uPrice;
            $curr_sdID   = $saleID . $i;

            $sqlDetail = "INSERT INTO SalesDetail (salesDetailID, saleID, productID, quantitySold, unitPrice, totalPrice) 
                          VALUES (:b_sdid, :b_sid, :b_pid, :b_qty, :b_uprice, :b_tprice)";
            
            $stmtDetail = oci_parse($dbconn, $sqlDetail);
            oci_bind_by_name($stmtDetail, ':b_sdid', $curr_sdID);
            oci_bind_by_name($stmtDetail, ':b_sid', $saleID);
            oci_bind_by_name($stmtDetail, ':b_pid', $curr_pid);
            oci_bind_by_name($stmtDetail, ':b_qty', $curr_qty);
            oci_bind_by_name($stmtDetail, ':b_uprice', $curr_uPrice);
            oci_bind_by_name($stmtDetail, ':b_tprice', $curr_tPrice);
            
            if (!oci_execute($stmtDetail, OCI_NO_AUTO_COMMIT)) { $error_occurred = true; break; }

            $sqlStock = "UPDATE Product SET quantityInStock = quantityInStock - :b_sqty WHERE productID = :b_spid";
            $stmtStock = oci_parse($dbconn, $sqlStock);
            oci_bind_by_name($stmtStock, ':b_sqty', $curr_qty);
            oci_bind_by_name($stmtStock, ':b_spid', $curr_pid);
            
            if (!oci_execute($stmtStock, OCI_NO_AUTO_COMMIT)) { $error_occurred = true; break; }
        }

        if (!$error_occurred) {
            oci_commit($dbconn);
            $message = "<div style='padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;'>Sale Completed Successfully! 🧾 #$saleID</div>";
        } else {
            oci_rollback($dbconn);
            $e = oci_error($dbconn);
            $message = "<div style='padding:15px; background:#f8d7da; color:#721c24; border-radius:5px; margin-bottom:20px;'>Error: " . $e['message'] . "</div>";
        }
    }
}

$prodQuery = oci_parse($dbconn, "SELECT productID, productName, sellingPrice FROM Product WHERE quantityInStock > 0 ORDER BY productName ASC");
oci_execute($prodQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cashier Terminal - Koperasi</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f6f8; margin: 0; display: flex; }
        
        /* Sidebar Styles (Matched) */
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

        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50; font-size: 14px; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; width: 100%; }
        
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .cart-table th { background: #3498db; color: white; padding: 12px; text-align: left; }
        .cart-table td { padding: 12px; border-bottom: 1px solid #eee; }

        .btn-add { background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn-pay { background: #27ae60; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; font-size: 18px; cursor: pointer; margin-top: 20px; font-weight: bold; }
        .btn-pay:hover { background: #2ecc71; }
        
        .total-box { text-align: right; font-size: 28px; font-weight: bold; margin-top: 20px; color: #2c3e50; border-top: 2px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">🛒 KOPERASI SYSTEM</div>
    <nav class="nav-links">
        <a href="cashier.php" class="active">💳 Cashier</a>
        <a href="index.php">🏠 Home</a>
        <a href="manage_product.php">📦 Manage Product</a>
        <a href="analytics.php">📈 View Analytics</a>
        <a href="restock_product.php">🚚 Restock Product</a>
        <a href="add_supplier.php">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="breadcrumbs">
        🏠 <a href="index.php">Home</a> &nbsp; / &nbsp; 
        <span>Cashier Terminal</span>
    </div>

    <div class="form-container">
        <h2>Checkout Counter 🏪</h2>
        <?php echo $message; ?>

        <form method="POST">
            <div style="max-width: 400px; margin-bottom: 30px;">
                <label>Customer User ID (Scan IC)</label>
                <input type="text" name="userID" placeholder="Enter ID..." required autofocus>
            </div>

            <div style="background: #f0f7fd; padding: 20px; border-radius: 8px; border: 1px dashed #3498db;">
                <label>Select Product to Add</label>
                <select id="itemPicker">
                    <option value="">-- Search Product --</option>
                    <?php while ($row = oci_fetch_array($prodQuery, OCI_ASSOC)): ?>
                        <option value="<?php echo $row['PRODUCTID']; ?>" data-price="<?php echo $row['SELLINGPRICE']; ?>">
                            <?php echo $row['PRODUCTNAME']; ?> (RM <?php echo $row['SELLINGPRICE']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="button" class="btn-add" onclick="addItem()">Add to Cart +</button>
            </div>

            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Price (RM)</th>
                        <th>Quantity</th>
                        <th>Subtotal (RM)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="total-box">
                Total: RM <span id="grandTotal">0.00</span>
                <input type="hidden" name="totalAmount" id="totalInput" value="0">
            </div>

            <button type="submit" name="finalize_sale" class="btn-pay">Finalize Sale & Print Receipt 🧾</button>
        </form>
    </div>
</div>

<script>
    function addItem() {
        var picker = document.getElementById('itemPicker');
        var productID = picker.value;
        
        if (!productID) {
            alert("Please select a product!");
            return;
        }

        var productName = picker.options[picker.selectedIndex].text.split(' (')[0];
        var price = parseFloat(picker.options[picker.selectedIndex].getAttribute('data-price'));

        var table = document.getElementById('cartTable').getElementsByTagName('tbody')[0];
        var row = table.insertRow();

        row.innerHTML = `
            <td>${productID}<input type="hidden" name="prodID[]" value="${productID}"></td>
            <td>${productName}</td>
            <td>${price.toFixed(2)}<input type="hidden" name="price[]" value="${price}"></td>
            <td><input type="number" name="qty[]" value="1" min="1" style="width:60px;" onchange="updateSubtotal(this, ${price})"></td>
            <td class="subtotal">${price.toFixed(2)}</td>
            <td><button type="button" onclick="removeRow(this)" style="color:#c0392b; cursor:pointer; font-weight:bold; border:none; background:none;">Remove</button></td>
        `;

        // RESET FIELD TO "-- Search Product --"
        picker.value = ""; 

        updateGrandTotal();
    }

    function removeRow(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        updateGrandTotal();
    }

    function updateSubtotal(input, price) {
        var qty = input.value;
        if (qty < 1) input.value = 1;
        var subtotalCell = input.parentElement.parentElement.querySelector('.subtotal');
        subtotalCell.innerText = (qty * price).toFixed(2);
        updateGrandTotal();
    }

    function updateGrandTotal() {
        var total = 0;
        document.querySelectorAll('.subtotal').forEach(function(cell) {
            total += parseFloat(cell.innerText);
        });
        document.getElementById('grandTotal').innerText = total.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);
    }
</script>

</body>
</html>