<?php
include("../auth/admin.php");
include("../config/database.php");

// Handle Add Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $due_date = $_POST['due_date'];
    $assigned_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $title, $description, $assigned_to, $assigned_by, $due_date);
    if ($stmt->execute()) {
        $msg = "Task assigned successfully.";
    } else {
        $error = "Failed to assign task: " . $conn->error;
    }
}

// Handle Delete Task
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: tasks.php?msg=Task deleted");
    exit();
}

// Fetch all staff for dropdown
$staff_result = $conn->query("SELECT id, fullname FROM users WHERE role = 'staff'");

// Fetch all tasks
$tasks_result = $conn->query("
    SELECT t.*, u.fullname as assignee_name 
    FROM tasks t 
    LEFT JOIN users u ON t.assigned_to = u.id 
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Tasks - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Tasks Management</h1>
        </div>

        <?php if (isset($_GET['msg'])) echo "<div class='message'>".htmlspecialchars($_GET['msg'])."</div>"; ?>
        <?php if (isset($msg)) echo "<div class='message'>$msg</div>"; ?>
        <?php if (isset($error)) echo "<div class='message' style='background:#ffebee; color:#c62828;'>$error</div>"; ?>

        <div class="admin-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">
            <!-- Assign Task Form -->
            <div class="admin-card">
                <h2>Assign New Task</h2>
                <br>
                <form action="" method="POST" class="profile-form" style="grid-template-columns: 1fr;">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label>Task Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div>
                        <label>Description</label>
                        <textarea name="description" style="height:80px;"></textarea>
                    </div>
                    <div>
                        <label>Assign To</label>
                        <select name="assigned_to" required>
                            <option value="">Select Staff...</option>
                            <?php while($s = $staff_result->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['fullname']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label>Due Date</label>
                        <input type="date" name="due_date">
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Task</button>
                </form>
            </div>

            <!-- Task List -->
            <div class="admin-card">
                <h2>All Tasks</h2>
                <br>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($tasks_result->num_rows > 0): ?>
                            <?php while($t = $tasks_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($t['title']); ?></strong><br>
                                        <small style="color:#666;"><?php echo htmlspecialchars(substr($t['description'], 0, 50)); ?>...</small>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['assignee_name']); ?></td>
                                    <td>
                                        <?php 
                                        $color = $t['status'] == 'Completed' ? 'green' : ($t['status'] == 'In Progress' ? 'orange' : 'gray');
                                        echo "<span style='color:$color; font-weight:bold;'>{$t['status']}</span>";
                                        ?>
                                    </td>
                                    <td><?php echo $t['due_date'] ? $t['due_date'] : '-'; ?></td>
                                    <td>
                                        <a href="tasks.php?delete=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No tasks found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
