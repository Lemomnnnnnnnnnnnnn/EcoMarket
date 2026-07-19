<?php
include("../auth/admin.php");
include("../config/database.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'weekly';
$query = "";
$title = "";

switch ($filter) {
    case 'weekly':
        $title = "Weekly Sales Report";
        $query = "SELECT DATE(created_at) as date, COUNT(id) as total_orders, SUM(total_amount) as revenue FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date DESC";
        break;
    case 'monthly':
        $title = "Monthly Sales Report";
        $query = "SELECT DATE(created_at) as date, COUNT(id) as total_orders, SUM(total_amount) as revenue FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date DESC";
        break;
    case 'quarterly':
        $title = "Quarterly Sales Report";
        $query = "SELECT YEAR(created_at) as year, QUARTER(created_at) as quarter, COUNT(id) as total_orders, SUM(total_amount) as revenue FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) GROUP BY year, quarter ORDER BY year DESC, quarter DESC";
        break;
    case 'yearly':
        $title = "Yearly Sales Report";
        $query = "SELECT YEAR(created_at) as year, COUNT(id) as total_orders, SUM(total_amount) as revenue FROM orders GROUP BY YEAR(created_at) ORDER BY year DESC";
        break;
}

$result = $conn->query($query);
$total_revenue = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Sales Reports</h1>
        </div>

        <div class="admin-card" style="margin-bottom:20px;">
            <div class="dashboard-buttons" style="margin-top:0;">
                <a href="reports.php?filter=weekly" class="<?php echo $filter=='weekly'?'btn-primary':'btn-secondary'; ?>">Weekly</a>
                <a href="reports.php?filter=monthly" class="<?php echo $filter=='monthly'?'btn-primary':'btn-secondary'; ?>">Monthly</a>
                <a href="reports.php?filter=quarterly" class="<?php echo $filter=='quarterly'?'btn-primary':'btn-secondary'; ?>">Quarterly</a>
                <a href="reports.php?filter=yearly" class="<?php echo $filter=='yearly'?'btn-primary':'btn-secondary'; ?>">Yearly</a>
            </div>
        </div>

        <div class="admin-card">
            <h2><?php echo $title; ?></h2>
            <br>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Total Orders</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $period = "";
                                if(isset($row['date'])) $period = $row['date'];
                                else if(isset($row['quarter'])) $period = "Q" . $row['quarter'] . " " . $row['year'];
                                else if(isset($row['year'])) $period = $row['year'];

                                $total_revenue += $row['revenue'];
                            ?>
                            <tr>
                                <td><?php echo $period; ?></td>
                                <td><?php echo $row['total_orders']; ?></td>
                                <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr style="background:#e8f5e9; font-weight:bold;">
                            <td colspan="2" style="text-align:right;">Total Revenue:</td>
                            <td>$<?php echo number_format($total_revenue, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="3">No sales data found for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <button class="btn btn-primary" style="margin-top:20px;" onclick="window.print()">Print Report</button>
        </div>

    </main>
</div>

</body>
</html>
