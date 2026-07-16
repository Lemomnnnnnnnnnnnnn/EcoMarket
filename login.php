<?php
session_start();
include("config/database.php");
$bodyClass = "login-page";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' OR email='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == "admin") {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] == "staff") {
                header("Location: staff/dashboard.php");
            } elseif ($user['role'] == "vendor") {
                header("Location: vendor/dashboard.php");
            } else {
                header("Location: customer/dashboard.php");
            }
            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "User not found!";
    }
}

include("includes/header.php");

?>
<div class="form-container">

    <h2>Login</h2>

    <?php if ($message != "") { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="login.php">

        <label>Username or Email</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

        <p>Don't have an account? <a href="register.php">Register here</a></p>

    </form>

</div>

