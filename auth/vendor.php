<?php
include("session.php");

if ($_SESSION['role'] != "vendor") {
    header("Location: ../login.php");
    exit();
}
?>