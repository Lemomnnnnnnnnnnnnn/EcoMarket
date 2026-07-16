<?php
include("session.php");

if ($_SESSION['role'] != "customer") {
    header("Location: ../login.php");
    exit();
}
?>