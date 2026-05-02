<?php
// admin/remove_user.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id == 0) {
    $_SESSION['error'] = "User ID not provided";
    header("Location: manage_users.php");
    exit();
}

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    header("Location: manage_users.php");
    exit();
}

// Get user info for logging
$user = $conn->query("SELECT name, email, role FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: manage_users.php");
    exit();
}

// Delete user (cascade will handle related records)
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User " . htmlspecialchars($user['name']) . " has been removed";
    
    // Log the activity
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'delete_user', ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $details = "Deleted user: " . $user['name'] . " (Email: " . $user['email'] . ", Role: " . $user['role'] . ")";
    $log->bind_param("iss", $_SESSION['user_id'], $details, $ip);
    $log->execute();
} else {
    $_SESSION['error'] = "Error deleting user";
}

header("Location: manage_users.php");
exit();
?>




