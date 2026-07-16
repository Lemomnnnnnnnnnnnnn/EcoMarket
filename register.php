<?php
include("config/database.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password != $confirm_password) {
        $message = "Password and Confirm Password do not match!";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $result = $conn->query($check);

        if ($result->num_rows > 0) {
            $message = "Username or Email already exists!";
        } else {

            $sql = "INSERT INTO users 
            (fullname, username, email, phone, address, password, role)
            VALUES 
            ('$fullname', '$username', '$email', '$phone', '$address', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                $message = "Registration successful! You can now login.";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}
$bodyClass = "login-page";
include("includes/header.php");

?>

<div class="auth-page">

<div class="form-container">

    <h2>Create Account</h2>

    <?php if ($message != "") { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" action="register.php">

        <label>Full Name</label>
        <input type="text" name="fullname" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Address</label>
        <textarea name="address" required></textarea>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="customer">Customer</option>
            <option value="vendor">Vendor</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>

        <button type="submit">Register</button>

        <p>Already have an account? <a href="login.php">Login here</a></p>

    </form>

</div>

