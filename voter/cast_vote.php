<?php
// voter/cast_vote.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$voter_id = $_SESSION['user_id'];
$candidate_id = intval($_GET['id']);

// Check if already voted
if ($conn->query("SELECT * FROM votes WHERE voterID = $voter_id")->num_rows > 0) {
    header("Location: dashboard.php?error=already_voted");
    exit();
}

// Get active election
$election = $conn->query("SELECT id FROM elections WHERE status = 'active' LIMIT 1")->fetch_assoc();
if (!$election) {
    header("Location: dashboard.php?error=no_election");
    exit();
}

// Cast vote
$stmt = $conn->prepare("INSERT INTO votes (candidateID, voterID, electionID, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$stmt->bind_param("iiiss", $candidate_id, $voter_id, $election['id'], $ip, $user_agent);

if ($stmt->execute()) {
    // Update candidate vote count
    $conn->query("UPDATE candidates SET total_votes = total_votes + 1 WHERE candidateID = $candidate_id");
    $_SESSION['success'] = "Your vote has been cast successfully!";
} else {
    $_SESSION['error'] = "Error casting vote";
}

header("Location: dashboard.php");
exit();
?>