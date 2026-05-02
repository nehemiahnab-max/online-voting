<?php
//config/db.php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'online_voting';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Function to get user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Base URL - change this to match your setup
define('BASE_URL', '/online_voting/');
?>



