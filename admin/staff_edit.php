<?php
include("../auth/admin.php");
include("../config/database.php");

if (!isset($_GET['id'])) {
    header("Location: staff_list.php");
    exit();
}

$id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $position = $_POST['position'];

    $stmt = $conn->prepare("UPDATE users SET fullname=?, phone=? WHERE id=? AND role='staff'");
    $stmt->bind_param("ssi", $fullname, $phone, $id);
    
    if ($stmt->execute()) {
        $stmt_staff = $conn->prepare("UPDATE staff SET department=?, position=? WHERE user_id=?");
        $stmt_staff->bind_param("ssi", $department, $position, $id);
        $stmt_staff->execute();
        
        header("Location: staff_list.php?msg=Staff updated successfully");
        exit();
    } else {
        $error = "Error updating staff: " . $conn->error;
    }
}

$query = "SELECT u.fullname, u.username, u.email, u.phone, s.department, s.position 
          FROM users u 
          LEFT JOIN staff s ON u.id = s.user_id 
          WHERE u.id = ? AND u.role = 'staff'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: staff_list.php?msg=Staff not found");
    exit();
}

$staff = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Staff - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Edit Staff (<?php echo htmlspecialchars($staff['username']); ?>)</h1>
            <a href="staff_list.php" class="btn btn-secondary">Back to List</a>
        </div>

        <div class="admin-card" style="max-width: 600px;">
            <?php if (isset($error)) echo "<div class='message' style='background:#ffebee; color:#c62828;'>$error</div>"; ?>
            
            <form action="" method="POST" class="profile-form" style="grid-template-columns: 1fr;">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($staff['fullname']); ?>" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($staff['phone']); ?>">
                </div>
                <div>
                    <label>Department</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($staff['department']); ?>" required>
                </div>
                <div>
                    <label>Position</label>
                    <input type="text" name="position" value="<?php echo htmlspecialchars($staff['position']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Staff</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>
