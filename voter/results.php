<?php
// voter/results.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
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
$election_active = $election && $election['status'] == 'active';

// Get voter's vote status
$has_voted = $conn->query("SELECT * FROM votes WHERE voterID = {$_SESSION['user_id']}")->num_rows > 0;

// Get vote count per party
$party_votes = $conn->query("
    SELECT party_name, SUM(total_votes) as votes 
    FROM candidates 
    WHERE party_name IS NOT NULL AND party_name != ''
    GROUP BY party_name 
    ORDER BY votes DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Voter Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Election Results</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                <a href="vote.php"><i class="fas fa-vote-yea"></i> Vote</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Election Status Banner -->
        <div class="alert alert-<?= $election_active ? 'info' : ($election && $election['status'] == 'closed' ? 'success' : 'warning') ?>">
            <i class="fas fa-info-circle"></i>
            <?php if ($election_active): ?>
                Election is currently <strong>ACTIVE</strong>. Results are preliminary and may change.
            <?php elseif ($election && $election['status'] == 'closed'): ?>
                Election has <strong>CLOSED</strong>. These are the final official results.
            <?php else: ?>
                Election has not started yet. Results will appear here when voting begins.
            <?php endif; ?>
        </div>

        <!-- Winner Announcement -->
        <?php if ($winner && $winner['total_votes'] > 0 && (!$election_active || ($election && $election['status'] == 'closed'))): ?>
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; animation: pulse 2s infinite;">
            <i class="fas fa-trophy" style="font-size: 64px; margin-bottom: 20px;"></i>
            <h1 style="font-size: 48px;">🏆 WINNER 🏆</h1>
            <h2 style="font-size: 36px; margin: 10px 0;"><?= htmlspecialchars($winner['name']) ?></h2>
            <p style="font-size: 20px;"><?= htmlspecialchars($winner['party_name'] ?? 'Independent') ?></p>
            <p style="font-size: 18px; margin-top: 20px;">Total Votes: <strong><?= $winner['total_votes'] ?></strong> (<?= $total_votes > 0 ? round(($winner['total_votes'] / $total_votes) * 100, 1) : 0 ?>%)</p>
        </div>
        <?php endif; ?>

        <!-- Results Summary -->
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
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?= $has_voted ? 'Yes' : 'No' ?></div>
                <div class="stat-label">You Voted</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-value"><?= $election ? date('M d', strtotime($election['start_date'])) . ' - ' . date('M d', strtotime($election['end_date'])) : 'TBA' ?></div>
                <div class="stat-label">Election Period</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-chart-bar"></i> Candidate Vote Distribution</h3>
                <canvas id="votesChart" style="max-height: 350px;"></canvas>
            </div>
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Party-wise Results</h3>
                <canvas id="partyChart" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <!-- Detailed Results Table -->
        <div class="card">
            <h3><i class="fas fa-list-ol"></i> Complete Results</h3>
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate</th>
                            <th>Party</th>
                            <th>Slogan</th>
                            <th>Votes</th>
                            <th>Percentage</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($candidates_data as $candidate): 
                            $percentage = $total_votes > 0 ? round(($candidate['total_votes'] / $total_votes) * 100, 1) : 0;
                            $isWinner = $rank == 1 && (!$election_active || ($election && $election['status'] == 'closed'));
                        ?>
                        <tr class="<?= $isWinner ? 'winner-row' : '' ?>" style="<?= $isWinner ? 'background: #fef3c7;' : '' ?>">
                            <td>
                                <?php if ($rank == 1): ?>
                                    <i class="fas fa-crown" style="color: #f59e0b;"></i>
                                <?php elseif ($rank == 2): ?>
                                    <i class="fas fa-medal" style="color: #94a3b8;"></i>
                                <?php elseif ($rank == 3): ?>
                                    <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                <?php else: ?>
                                    #<?= $rank ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($candidate['name']) ?></strong></td>
                            <td><?= htmlspecialchars($candidate['party_name'] ?? 'Independent') ?></td>
                            <td><em>"<?= htmlspecialchars(substr($candidate['slogan'] ?? '', 0, 50)) ?>"</em></td>
                            <td><strong><?= $candidate['total_votes'] ?></strong></td>
                            <td><?= $percentage ?>%</td>
                            <td style="width: 200px;">
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?= $percentage ?>%; background: <?= $rank == 1 ? '#f59e0b' : '#6366f1' ?>;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php $rank++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Voter Turnout Info -->
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Voter Participation</h3>
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
                <div>
                    <p><strong>Total Registered Voters:</strong> <?= $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'voter'")->fetch_assoc()['c'] ?></p>
                    <p><strong>Total Votes Cast:</strong> <?= $total_votes ?></p>
                    <p><strong>Voter Turnout:</strong> <?= $total_votes > 0 ? round(($total_votes / $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'voter'")->fetch_assoc()['c']) * 100, 1) : 0 ?>%</p>
                </div>
                <div>
                    <canvas id="turnoutChart" style="max-height: 150px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Share Results -->
        <div class="card" style="text-align: center;">
            <h3><i class="fas fa-share-alt"></i> Share Results</h3>
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="shareResults('facebook')"><i class="fab fa-facebook"></i> Facebook</button>
                <button class="btn btn-secondary" onclick="shareResults('twitter')"><i class="fab fa-twitter"></i> Twitter</button>
                <button class="btn btn-secondary" onclick="shareResults('whatsapp')"><i class="fab fa-whatsapp"></i> WhatsApp</button>
                <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </div>

    <style>
        .winner-row {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        .results-table tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    </style>

    <script src="../assets/js/animation.js"></script>
    <script>
        // Candidate Votes Chart
        const ctx1 = document.getElementById('votesChart').getContext('2d');
        new Chart(ctx1, {
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
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: function(context) { return context.raw + ' votes'; } } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Votes' } } }
            }
        });

        // Party Chart
        <?php
        $party_labels = [];
        $party_data = [];
        while ($party = $party_votes->fetch_assoc()) {
            $party_labels[] = $party['party_name'];
            $party_data[] = $party['votes'];
        }
        ?>
        const ctx2 = document.getElementById('partyChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?= json_encode($party_labels) ?>,
                datasets: [{
                    data: <?= json_encode($party_data) ?>,
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'right' }, tooltip: { callbacks: { label: function(context) { return context.label + ': ' + context.raw + ' votes'; } } } }
            }
        });

        // Turnout Chart
        const totalVoters = <?= $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'voter'")->fetch_assoc()['c'] ?>;
        const totalVotes = <?= $total_votes ?>;
        const ctx3 = document.getElementById('turnoutChart').getContext('2d');
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: ['Voted', 'Not Voted'],
                datasets: [{
                    data: [totalVotes, totalVoters - totalVotes],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        function shareResults(platform) {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent("Check out the latest election results! " + window.location.href);
            
            let shareUrl = '';
            if (platform === 'facebook') shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            if (platform === 'twitter') shareUrl = `https://twitter.com/intent/tweet?text=${text}`;
            if (platform === 'whatsapp') shareUrl = `https://wa.me/?text=${text}`;
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    </script>
</body>
</html>