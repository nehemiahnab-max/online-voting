<?php
// admin/results.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Get election results
$results = $conn->query("
    SELECT u.name, c.candidateID, c.party_name, c.slogan, c.total_votes,
           (SELECT COUNT(*) FROM votes) as total_votes_cast
    FROM users u 
    JOIN candidates c ON u.id = c.userID 
    WHERE c.approved = 1
    ORDER BY c.total_votes DESC
");

$total_votes = 0;
$candidates_data = [];
while ($row = $results->fetch_assoc()) {
    $total_votes += $row['total_votes'];
    $candidates_data[] = $row;
}

// Get winner
$winner = !empty($candidates_data) ? $candidates_data[0] : null;

// Get election info
$election = $conn->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Nehemiah Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Election Results</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
                <a href="manage_candidates.php"><i class="fas fa-user-tie"></i> Candidates</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Winner Announcement -->
        <?php if ($winner && $winner['total_votes'] > 0): ?>
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white;">
            <i class="fas fa-trophy" style="font-size: 48px; margin-bottom: 15px;"></i>
            <h2>🏆 WINNER 🏆</h2>
            <h1 style="font-size: 48px; margin: 10px 0;"><?= htmlspecialchars($winner['name']) ?></h1>
            <p style="font-size: 20px;"><?= htmlspecialchars($winner['party_name']) ?></p>
            <p style="font-size: 18px;">Total Votes: <strong><?= $winner['total_votes'] ?></strong> (<?= $total_votes > 0 ? round(($winner['total_votes'] / $total_votes) * 100, 1) : 0 ?>%)</p>
        </div>
        <?php endif; ?>

        <!-- Results Grid -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= $total_votes ?></div>
                <div class="stat-label">Total Votes Cast</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="stat-value"><?= count($candidates_data) ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-value"><?= $election ? date('M d', strtotime($election['start_date'])) . ' - ' . date('M d', strtotime($election['end_date'])) : 'N/A' ?></div>
                <div class="stat-label">Election Period</div>
            </div>
        </div>

        <!-- Chart and Results Table -->
        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Vote Distribution</h3>
                <canvas id="votesChart" style="max-height: 300px;"></canvas>
            </div>
            <div class="card">
                <h3><i class="fas fa-list-ol"></i> Candidate Rankings</h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Rank</th><th>Candidate</th><th>Party</th><th>Votes</th><th>Percentage</th><th>Progress</th></tr></thead>
                        <tbody>
                            <?php $rank = 1; foreach ($candidates_data as $candidate): 
                                $percentage = $total_votes > 0 ? round(($candidate['total_votes'] / $total_votes) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?= $rank++ ?></td>
                                <td><strong><?= htmlspecialchars($candidate['name']) ?></strong></td>
                                <td><?= htmlspecialchars($candidate['party_name']) ?></td>
                                <td><strong><?= $candidate['total_votes'] ?></strong></td>
                                <td><?= $percentage ?>%</td>
                                <td style="width: 150px;">
                                    <div class="progress-container"><div class="progress-bar" style="width: <?= $percentage ?>%;"></div></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/animation.js"></script>
    <script>
        const ctx = document.getElementById('votesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($candidates_data, 'name')) ?>,
                datasets: [{
                    label: 'Votes Received',
                    data: <?= json_encode(array_column($candidates_data, 'total_votes')) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' }, title: { display: true, text: 'Election Results' } }
            }
        });
    </script>
</body>
</html>