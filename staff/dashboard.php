<?php
include("../auth/staff.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body class="dashboard-body">

<!-- Navigation -->

<header class="customer-topbar">

    <div class="customer-logo">
        🌿 Eco Market
    </div>

    <nav class="customer-nav">

        <a href="dashboard.php">Dashboard</a>

        <a href="#">Assigned Tasks</a>

        <a href="../logout.php">Logout</a>

    </nav>

    <div class="customer-user">

        <?php echo $_SESSION['fullname']; ?>

    </div>

</header>

<!-- Main Content -->

<main class="staff-page">

    <div class="simple-dashboard">

        <h1>Staff Dashboard</h1>

        <h2>Welcome Back, <?php echo $_SESSION['fullname']; ?>!</h2>

        <p>
            You have successfully logged in as a <strong>Staff</strong>.
        </p>

        <p>
            This module will be completed by <strong>Member 5</strong>.
        </p>

        <div class="dashboard-buttons">

            <a href="#">Assigned Tasks</a>

            <a href="#">Performance</a>

        </div>

        <div class="notice">

            <h3>Upcoming Features</h3>

            <ul>

                <li>✔ Staff Dashboard </li>

                <li>✔ Product & Vendor Ratings </li>

                <li>✔ Customer Reviews </li>

                <li>✔ Notifications</li>

                <li>✔ Low Stock Alerts</li>

                <li>✔ Account Settings</li>

                <li>✔ Contact Page</li>

                <li> ✔About Page </li>

            </ul>

        </div>

    </div>

</main>

<?php include("../includes/footer.php"); ?>

</body>

</html>