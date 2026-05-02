<?php
// admin/approve_candidate.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Debug: Show any errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Candidate ID not provided";
    header("Location: manage_candidates.php");
    exit();
}

$candidate_id = intval($_GET['id']);
echo "Candidate ID: " . $candidate_id; // For debugging

// Update candidate approval status
$sql = "UPDATE candidates SET approved = 1, approved_at = NOW() WHERE candidateID = $candidate_id";
echo "<br>SQL: " . $sql; // For debugging

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        $_SESSION['success'] = "Candidate approved successfully!";
        echo "<br>Success! Redirecting...";
    } else {
        $_SESSION['error'] = "Candidate already approved or not found";
        echo "<br>No rows affected";
    }
} else {
    $_SESSION['error'] = "Error approving candidate: " . $conn->error;
    echo "<br>Error: " . $conn->error;
}

// Redirect after 2 seconds so we can see debug info
header("refresh:2; url=manage_candidates.php");
?>




