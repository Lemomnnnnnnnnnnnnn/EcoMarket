<?php
session_start();
include("config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

/* Update Profile */
if (isset($_POST['update'])) {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE users
            SET fullname='$fullname',
                email='$email',
                phone='$phone',
                address='$address'
            WHERE id='$user_id'";

    if ($conn->query($sql)) {

        $_SESSION['fullname'] = $fullname;
        $message = "Profile updated successfully!";

    } else {

        $message = "Update failed.";

    }
}

/* Get User Information */

$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body class="dashboard-body">

<header class="customer-topbar">

<div class="customer-logo">

🌿 Eco Market

</div>

<nav class="customer-nav">

<a href="<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>

<a href="logout.php">Logout</a>

</nav>

<div class="customer-user">

<span><?php echo $_SESSION['fullname']; ?></span>

</div>

</header>

<div class="profile-container">

<h1>My Profile</h1>

<?php
if($message!=""){
?>
<div class="message">

<?php echo $message; ?>

</div>

<?php
}
?>

<form method="POST" class="profile-form">

<div>

<label>Full Name</label>

<input
type="text"
name="fullname"
value="<?php echo $user['fullname'];?>"
required>

</div>

<div>

<label>Username</label>

<input
type="text"
value="<?php echo $user['username'];?>"
readonly>

</div>

<div>

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo $user['email'];?>"
required>

</div>

<div>

<label>Phone Number</label>

<input
type="text"
name="phone"
value="<?php echo $user['phone'];?>">

</div>

<div class="full">

<label>Address</label>

<textarea
name="address"><?php echo $user['address'];?></textarea>

</div>

<button
type="submit"
name="update">

Update Profile

</button>

</form>

</div>

<footer>

<p>© 2026 Eco Market Sdn. Bhd.</p>

<p>This website is fictitious and developed solely for academic purposes as part of a university course.</p>

</footer>

</body>

</html>