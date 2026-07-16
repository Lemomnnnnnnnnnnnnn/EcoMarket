<?php
include("../auth/admin.php");
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

<header class="customer-topbar">

    <div class="customer-logo">
        🌿 Eco Market
    </div>

    <nav class="customer-nav">

        <a href="dashboard.php">Dashboard</a>

        <a href="#">Staff Management</a>

        <a href="#">Reports</a>

        <a href="#">Analytics</a>

        <a href="../logout.php">Logout</a>

    </nav>

    <div class="customer-user">

        <?php echo $_SESSION['fullname']; ?>

    </div>

</header>

<div class="simple-dashboard">

    <h1>Admin Dashboard</h1>

    <p>Welcome back, <?php echo $_SESSION['fullname']; ?>.</p>

    <p>This dashboard will be completed by Member 4.</p>

    <div class="dashboard-buttons">

        <a href="#">Manage Staff</a>

        <a href="#">Reports</a>

        <a href="#">Analytics</a>

    </div>

    <div class="notice">

        <h3>Member 4 Tasks</h3>

        <p>✔ Staff CRUD</p>

         <p>✔ Admin Dashboard</p>

        <p>✔ Assign Tasks</p>

        <p>✔ Performance Tracking</p>

        <p>✔ Most Popular Products</p>

        <p>✔ Analytics Dashboard</p>

        <p>✔ Most Searched Products</p>

        <p>✔Most Visited Pages</p>

        <p>✔Sales Reports</p>

    </div>

</div>

<?php include("../includes/footer.php"); ?>

</body>

</html>