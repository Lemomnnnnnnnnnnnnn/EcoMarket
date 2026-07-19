<?php
include("../auth/admin.php");
include("../config/database.php");

// Fetch high level stats
$staff_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'staff'";
$staff_count = $conn->query($staff_count_query)->fetch_assoc()['count'];

$tasks_query = "SELECT COUNT(*) as count FROM tasks WHERE status != 'Completed'";
$tasks_count = $conn->query($tasks_query)->fetch_assoc()['count'];

$sales_query = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'";
$sales_total = $conn->query($sales_query)->fetch_assoc()['total'];
if(!$sales_total) $sales_total = 0;

$visits_query = "SELECT SUM(visit_count) as total FROM page_visits";
$visits_total = $conn->query($visits_query)->fetch_assoc()['total'];
if(!$visits_total) $visits_total = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Welcome, <?php echo $_SESSION['fullname']; ?></h1>
            <div class="admin-user-info">
                <span>Role: Admin</span>
            </div>
        </div>

        <div class="admin-grid">
            <div class="stat-card">
                <h3>Total Staff</h3>
                <div class="value"><?php echo $staff_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Tasks</h3>
                <div class="value"><?php echo $tasks_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Sales</h3>
                <div class="value">$<?php echo number_format($sales_total, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Page Visits</h3>
                <div class="value"><?php echo number_format($visits_total); ?></div>
            </div>
        </div>

        <div class="admin-card" style="margin-top: 30px;">
            <h2>Quick Actions</h2>
            <br>
            <div class="dashboard-buttons" style="margin-top:0;">
                <a href="staff_add.php">Add New Staff</a>
                <a href="tasks.php">Assign Task</a>
                <a href="reports.php">Generate Report</a>
            </div>
        </div>

    </main>
</div>

</body>
</html>