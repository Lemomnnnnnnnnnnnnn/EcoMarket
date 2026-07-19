<?php
include("../auth/admin.php");
include("../config/database.php");

// Fetch performance stats
$query = "
    SELECT 
        u.id, 
        u.fullname,
        COUNT(t.id) as total_tasks,
        SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'staff'
    GROUP BY u.id, u.fullname
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Performance Tracking - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Staff Performance Tracking</h1>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Total Assigned Tasks</th>
                        <th>Pending</th>
                        <th>In Progress</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $total = $row['total_tasks'];
                                $completed = $row['completed_tasks'];
                                $rate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                                <td><?php echo $total; ?></td>
                                <td><?php echo $row['pending_tasks']; ?></td>
                                <td><?php echo $row['in_progress_tasks']; ?></td>
                                <td><?php echo $completed; ?></td>
                                <td>
                                    <div style="background:#e0e0e0; border-radius:10px; width:100%; height:10px; margin-top:5px; overflow:hidden;">
                                        <div style="background:<?php echo $rate == 100 ? '#4caf50' : ($rate > 0 ? '#2196f3' : '#e0e0e0'); ?>; width:<?php echo $rate; ?>%; height:100%;"></div>
                                    </div>
                                    <small><?php echo $rate; ?>%</small>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No staff data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
