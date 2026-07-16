<?php
include("../auth/customer.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="simple-dashboard">

    <h1>🌿 Eco Market</h1>

    <h2>Customer Dashboard</h2>

    <p>Welcome,
        <strong><?php echo $_SESSION['fullname']; ?></strong>
    </p>

    <p>You have successfully logged in as a <strong>Customer</strong>.</p>

    <div class="dashboard-buttons">

        <a href="../profile.php">My Profile</a>

        <a href="../logout.php">Logout</a>

    </div>

    <div class="notice">

        <h3>Notice</h3>

        <p>
            This is a temporary dashboard for the Authentication module.
            The complete Customer Shopping System will be developed by Member 3.
        </p>

    </div>

</div>

</body>

</html>