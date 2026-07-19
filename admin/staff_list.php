<?php
include("../auth/admin.php");
include("../config/database.php");

$query = "SELECT u.id, u.fullname, u.username, u.email, u.phone, s.department, s.position, s.hire_date 
          FROM users u 
          LEFT JOIN staff s ON u.id = s.user_id 
          WHERE u.role = 'staff'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Management - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Staff Management</h1>
            <a href="staff_add.php" class="btn btn-primary">Add New Staff</a>
        </div>

        <div class="admin-card">
            <?php
            if (isset($_GET['msg'])) {
                echo "<div class='message'>" . htmlspecialchars($_GET['msg']) . "</div>";
            }
            ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="staff_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="staff_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this staff member?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No staff members found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
