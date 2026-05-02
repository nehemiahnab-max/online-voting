<?php
// voter/dashboard.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if already voted
$has_voted = $conn->query("SELECT * FROM votes WHERE voterID = $user_id")->num_rows > 0;

// Get active election
$election = $conn->query("SELECT * FROM elections WHERE status = 'active' LIMIT 1")->fetch_assoc();
$election_active = $election ? true : false;

if ($election_active) {
    $end_time = strtotime($election['end_date']) * 1000;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-vote-yea"></i>
                <span>Voter Portal</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                <a href="vote.php"><i class="fas fa-vote-yea"></i> Vote</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        
        <!-- Welcome Card -->
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
            <p>Your voice matters. Cast your vote today!</p>
        </div>
        
        <?php if ($election_active): ?>
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div id="countdown"></div>
                    <div class="stat-label">Time Remaining</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?= $has_voted ? 'Voted' : 'Not Voted' ?></div>
                    <div class="stat-label">Your Voting Status</div>
                </div>
            </div>
            
            <?php if (!$has_voted): ?>
                <div style="text-align: center; margin: 40px 0;">
                    <a href="vote.php" class="btn btn-lg pulse" style="font-size: 24px; padding: 20px 60px;">
                        <i class="fas fa-vote-yea"></i> CAST YOUR VOTE NOW
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-success" style="text-align: center;">
                    <i class="fas fa-check-circle"></i> Thank you for participating in the election!
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info" style="text-align: center;">
                <i class="fas fa-info-circle"></i> No active election at the moment. Please check back later.
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/animation.js"></script>
    <?php if ($election_active && !$has_voted): ?>
    <script>
        startCountdown('countdown', <?= $end_time ?>);
    </script>
    <?php endif; ?>
</body>
</html>