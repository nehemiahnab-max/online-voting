<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SESSION['role'] == 'admin') {
    header("Location: admin/dashboard.php");
} elseif ($_SESSION['role'] == 'candidate') {
    header("Location: candidate/dashboard.php");
} elseif ($_SESSION['role'] == 'voter') {
    header("Location: voter/dashboard.php");
}
exit();
?>