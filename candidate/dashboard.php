<?php
// candidate/dashboard.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'candidate') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get candidate info
$candidate = $conn->query("
    SELECT c.*, u.name, u.email, u.phone 
    FROM candidates c 
    JOIN users u ON c.userID = u.id 
    WHERE c.userID = $user_id
")->fetch_assoc();

if (!$candidate) {
    header("Location: ../auth/login.php");
    exit();
}

// Get total votes
$my_votes = $candidate['total_votes'] ?? 0;

// Get all candidates for ranking
$all_candidates = $conn->query("
    SELECT u.name, c.total_votes, c.candidateID
    FROM users u JOIN candidates c ON u.id = c.userID 
    WHERE c.approved = 1 
    ORDER BY c.total_votes DESC
");

$rank = 1;
$my_rank = null;
$total_candidates = 0;
$total_votes_all = 0;

while ($row = $all_candidates->fetch_assoc()) {
    $total_candidates++;
    $total_votes_all += $row['total_votes'];
    if ($row['candidateID'] == $candidate['candidateID']) {
        $my_rank = $rank;
    }
    $rank++;
}

// Get recent votes for this candidate
$recent_votes = $conn->query("
    SELECT v.voted_at, v.ip_address 
    FROM votes v 
    WHERE v.candidateID = {$candidate['candidateID']} 
    ORDER BY v.voted_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Candidate Portal</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="campaign.php"><i class="fas fa-bullhorn"></i> Campaign</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Welcome Card -->
        <div class="card" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
            <p>Your journey to leadership starts here. Keep campaigning!</p>
        </div>

        <!-- Stats Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= $my_votes ?></div>
                <div class="stat-label">Your Total Votes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-value">#<?= $my_rank ?? 'N/A' ?></div>
                <div class="stat-label">Your Rank</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-percent"></i></div>
                <div class="stat-value"><?= $total_votes_all > 0 ? round(($my_votes / $total_votes_all) * 100, 1) : 0 ?>%</div>
                <div class="stat-label">Vote Share</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= $total_candidates ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
        </div>

        <!-- Campaign Progress -->
        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-bullhorn"></i> Your Campaign</h3>
                <div class="candidate-info">
                    <p><strong>Party:</strong> <?= htmlspecialchars($candidate['party_name'] ?? 'Not specified') ?></p>
                    <p><strong>Slogan:</strong> "<?= htmlspecialchars($candidate['slogan'] ?? 'No slogan yet') ?>"</p>
                    <p><strong>Manifesto:</strong> <?= nl2br(htmlspecialchars(substr($candidate['manifesto'] ?? '', 0, 200))) ?>...</p>
                    <a href="campaign.php" class="btn btn-sm">Update Campaign <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="card">
                <h3><i class="fas fa-history"></i> Recent Votes</h3>
                <?php if ($recent_votes->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Date & Time</th><th>IP Address</th></tr></thead>
                            <tbody>
                                <?php while ($vote = $recent_votes->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('M d, Y H:i:s', strtotime($vote['voted_at'])) ?></td>
                                    <td><?= htmlspecialchars($vote['ip_address']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: gray;">No votes yet. Start campaigning!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/animation.js"></script>
</body>
</html>