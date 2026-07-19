<?php
include("../auth/admin.php");
include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $password = password_hash("staff123", PASSWORD_DEFAULT); // Default password for new staff
    $role = 'staff';

    // Insert into users
    $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullname, $username, $email, $phone, $password, $role);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Insert into staff table
        $stmt_staff = $conn->prepare("INSERT INTO staff (user_id, department, position, hire_date) VALUES (?, ?, ?, CURDATE())");
        $stmt_staff->bind_param("iss", $user_id, $department, $position);
        $stmt_staff->execute();
        
        header("Location: staff_list.php?msg=Staff added successfully (Default pass: staff123)");
        exit();
    } else {
        $error = "Error adding staff: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Staff - Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="admin-layout">
    <?php include("includes/admin_nav.php"); ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Add New Staff</h1>
            <a href="staff_list.php" class="btn btn-secondary">Back to List</a>
        </div>

        <div class="admin-card" style="max-width: 600px;">
            <?php if (isset($error)) echo "<div class='message' style='background:#ffebee; color:#c62828;'>$error</div>"; ?>
            
            <form action="" method="POST" class="profile-form" style="grid-template-columns: 1fr;">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div>
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div>
                    <label>Department</label>
                    <input type="text" name="department" required>
                </div>
                <div>
                    <label>Position</label>
                    <input type="text" name="position" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Staff</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>
