<?php
session_start();
include('dbconn.php');

/* 🔒 PROTECT PAGE */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- 1. FINANCIAL SUMMARY DATA (GAUGE) ---
$qRevenue = oci_parse($dbconn, "SELECT SUM(totalAmount) AS REVENUE FROM Sales");
oci_execute($qRevenue);
$revData = oci_fetch_assoc($qRevenue);

$totalRevenue = isset($revData['REVENUE']) ? (float)$revData['REVENUE'] : 0.0;
$targetRevenue = 5000.00; 
$performance = ($totalRevenue > 0) ? ($totalRevenue / $targetRevenue) * 100 : 0;
$remaining = max(0, $targetRevenue - $totalRevenue);

// --- 2. TOP 5 SELLING PRODUCTS ---
$qTopProd = oci_parse($dbconn, "
    SELECT p.productName, SUM(sd.quantitySold) as TOTAL_SOLD 
    FROM SalesDetail sd 
    JOIN Product p ON sd.productID = p.productID 
    GROUP BY p.productName 
    ORDER BY TOTAL_SOLD DESC 
    FETCH FIRST 5 ROWS ONLY");
oci_execute($qTopProd);
$topLabels = []; $topValues = [];
while ($row = oci_fetch_assoc($qTopProd)) {
    $topLabels[] = $row['PRODUCTNAME'];
    $topValues[] = (int)$row['TOTAL_SOLD'];
}

// --- 3. DAILY TRANSACTION VOLUME ---
$qDailyVol = oci_parse($dbconn, "
    SELECT TO_CHAR(saleDate, 'FMDay') as DAY_NAME, COUNT(saleID) as TOTAL_TRANS 
    FROM Sales 
    WHERE TO_CHAR(saleDate, 'MM-YYYY') = '01-2026'
    GROUP BY TO_CHAR(saleDate, 'FMDay'), TO_CHAR(saleDate, 'D')
    ORDER BY TO_CHAR(saleDate, 'D') ASC");
oci_execute($qDailyVol);
$dayLabels = []; $dayValues = [];
while ($row = oci_fetch_assoc($qDailyVol)) {
    $dayLabels[] = trim($row['DAY_NAME']);
    $dayValues[] = (int)$row['TOTAL_TRANS'];
}

// --- 4. WEEKLY TREND DATA ---
$qTrend = oci_parse($dbconn, "
    SELECT 
        CASE 
            WHEN TO_CHAR(saleDate, 'DD') <= 07 THEN 'Week 1'
            WHEN TO_CHAR(saleDate, 'DD') <= 14 THEN 'Week 2'
            WHEN TO_CHAR(saleDate, 'DD') <= 21 THEN 'Week 3'
            ELSE 'Week 4'
        END AS WEEK_LABEL,
        SUM(totalAmount) AS WEEKLY_REV
    FROM Sales
    WHERE TO_CHAR(saleDate, 'MM-YYYY') = '01-2026'
    GROUP BY 
        CASE 
            WHEN TO_CHAR(saleDate, 'DD') <= 07 THEN 'Week 1'
            WHEN TO_CHAR(saleDate, 'DD') <= 14 THEN 'Week 2'
            WHEN TO_CHAR(saleDate, 'DD') <= 21 THEN 'Week 3'
            ELSE 'Week 4'
        END
    ORDER BY WEEK_LABEL ASC");
oci_execute($qTrend);
$weekLabels = []; $weekValues = [];
while ($row = oci_fetch_assoc($qTrend)) {
    $weekLabels[] = $row['WEEK_LABEL'];
    $weekValues[] = (float)$row['WEEKLY_REV'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard - Koperasi</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; display: flex; }
        .sidebar { width: 240px; background: #2c3e50; color: white; position: fixed; height: 100vh; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; }
        .nav-links { flex: 1; padding-top: 20px; }
        .nav-links a { display: block; color: #bdc3c7; padding: 15px 25px; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .main-content { margin-left: 240px; padding: 30px; width: calc(100% - 240px); }
        .breadcrumbs { margin-bottom: 25px; font-size: 14px; color: #7f8c8d; background: white; padding: 12px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .breadcrumbs a { color: #3498db; text-decoration: none; }
        .logout-link { padding: 20px 25px; background: #c0392b; color: white; text-decoration: none; text-align: center; }
        
        .analytics-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .chart-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .chart-card h3 { margin: 0 0 5px 0; color: #2c3e50; font-size: 16px; }
        
        /* Explanation Text Style */
        .chart-desc { font-size: 12px; color: #7f8c8d; margin-bottom: 15px; border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; line-height: 1.4; }
        
        .chart-container { position: relative; height: 250px; width: 100%; }
        .gauge-wrapper { position: relative; height: 180px; width: 100%; }
        .gauge-center-text { position: absolute; top: 65%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">🛒 KOPERASI SYSTEM</div>
    <nav class="nav-links">
        <a href="cashier.php">💳 Cashier</a>
        <a href="index.php">🏠 Home</a>
        <a href="manage_product.php">📦 Manage Product</a>
        <a href="analytics.php" class="active">📈 View Analytics</a>
        <a href="restock_product.php">🚚 Restock Product</a>
        <a href="add_supplier.php">🏢 Add Supplier</a>
    </nav>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="breadcrumbs">
        🏠 <a href="index.php">Home</a> &nbsp; / &nbsp; 
        <a href="manage_product.php">Manage Product</a> &nbsp; / &nbsp; 
        <span>Analytics Dashboard</span> &nbsp; / &nbsp; 
    </div>

    <h2 style="color: #2c3e50; margin-bottom: 20px;">Business Insights 📊</h2>

    <div class="analytics-grid">
        <div class="chart-card">
            <h3>Monthly Revenue Goal 🏁</h3>
            <p class="chart-desc">Tracks total sales progress against the monthly target of RM 5,000.00.</p>
            <div class="gauge-wrapper">
                <canvas id="revenueGaugeChart"></canvas>
                <div class="gauge-center-text">
                    <span style="font-size: 24px; font-weight: 900; color: #27ae60;"><?= number_format($performance, 1) ?>%</span><br>
                    <span style="font-size: 11px; color: #95a5a6;">OF RM <?= number_format($targetRevenue, 0) ?></span>
                </div>
            </div>
            <p style="text-align: center; font-weight: bold; color: #2c3e50; margin-top: 5px;">
                Collected: RM <?= number_format($totalRevenue, 2) ?>
            </p>
        </div>

        <div class="chart-card">
            <h3>Most Popular Items 🏆</h3>
            <p class="chart-desc">Displays the top 5 products based on the total quantity sold this month.</p>
            <div class="chart-container">
                <canvas id="topSellingChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>Daily Sales Volume 👥</h3>
            <p class="chart-desc">Shows the number of successful transactions processed each day of the week.</p>
            <div class="chart-container">
                <canvas id="dailyVolumeChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>Weekly Sales Revenue 📈</h3>
            <p class="chart-desc">Visualizes revenue fluctuations over 4 weeks to identify growth patterns.</p>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// 1. REVENUE GAUGE
new Chart(document.getElementById('revenueGaugeChart'), {
    type: 'doughnut',
    data: {
        labels: ['Collected', 'Remaining'],
        datasets: [{
            data: [<?= $totalRevenue ?>, <?= $remaining ?>],
            backgroundColor: ['#27ae60', '#ecf0f1'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            cutout: '80%'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 2. TOP SELLING (Horizontal)
new Chart(document.getElementById('topSellingChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($topLabels) ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?= json_encode($topValues) ?>,
            backgroundColor: '#3498db',
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// 3. DAILY VOLUME
new Chart(document.getElementById('dailyVolumeChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dayLabels) ?>,
        datasets: [{
            label: 'Transactions',
            data: <?= json_encode($dayValues) ?>,
            backgroundColor: '#9b59b6',
            borderRadius: 4
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 4. TREND
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($weekLabels) ?>,
        datasets: [{
            label: 'Revenue (RM)',
            data: <?= json_encode($weekValues) ?>,
            borderColor: '#e67e22',
            backgroundColor: 'rgba(230, 126, 34, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>