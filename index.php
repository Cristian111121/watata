<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/index.php");
    }
    exit();
} else {
    header("Location: user/login.php");
    exit();
}
?>
