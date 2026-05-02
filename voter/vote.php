<?php
// voter/vote.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if already voted
if ($conn->query("SELECT * FROM votes WHERE voterID = $user_id")->num_rows > 0) {
    header("Location: dashboard.php?error=already_voted");
    exit();
}

// Get active election
$election = $conn->query("SELECT * FROM elections WHERE status = 'active' LIMIT 1")->fetch_assoc();
if (!$election) {
    header("Location: dashboard.php?error=no_election");
    exit();
}

// Get approved candidates
$candidates = $conn->query("
    SELECT u.name, c.candidateID, c.manifesto, c.party_name, c.slogan
    FROM users u
    JOIN candidates c ON u.id = c.userID
    WHERE c.approved = 1
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Cast Your Vote</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </nav>
        
        <h2 style="text-align: center; margin: 30px 0; color: white;">Select Your Preferred Candidate</h2>
        
        <div class="dashboard-grid">
            <?php while ($row = $candidates->fetch_assoc()): ?>
            <div class="candidate-card" data-id="<?= $row['candidateID'] ?>" onclick="selectCandidate(<?= $row['candidateID'] ?>, '<?= htmlspecialchars($row['name']) ?>')">
                <div class="candidate-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="candidate-info">
                    <div class="candidate-name"><?= htmlspecialchars($row['name']) ?></div>
                    <div class="candidate-party"><i class="fas fa-flag"></i> <?= htmlspecialchars($row['party_name']) ?></div>
                    <?php if ($row['slogan']): ?>
                        <div class="candidate-party" style="font-style: italic;">"<?= htmlspecialchars($row['slogan']) ?>"</div>
                    <?php endif; ?>
                    <button class="btn btn-sm" style="margin-top: 15px;">Select Candidate</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="voteModal" class="modal">
        <div class="modal-content">
            <i class="fas fa-check-circle" style="font-size: 64px; color: #10b981; margin-bottom: 20px;"></i>
            <h3>Confirm Your Vote</h3>
            <p>Are you sure you want to vote for <strong id="selectedCandidateName"></strong>?</p>
            <p style="color: #ef4444; margin: 20px 0;">⚠️ This action cannot be undone!</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button class="btn btn-secondary" onclick="closeModal('voteModal')">Cancel</button>
                <a href="" id="confirmVoteBtn" class="btn btn-success">Confirm Vote</a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/animation.js"></script>
    <script>
        let selectedCandidateId = null;
        
        function selectCandidate(id, name) {
            selectedCandidateId = id;
            document.getElementById('selectedCandidateName').textContent = name;
            document.getElementById('confirmVoteBtn').href = 'cast_vote.php?id=' + id;
            showModal('voteModal');
        }
    </script>
</body>
</html>