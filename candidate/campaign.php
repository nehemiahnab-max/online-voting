<?php
// candidate/campaign.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'candidate') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$candidate = $conn->query("SELECT candidateID, party_name, slogan, manifesto, total_votes FROM candidates WHERE userID = $user_id")->fetch_assoc();

// Get vote history
$vote_history = $conn->query("
    SELECT DATE(voted_at) as vote_date, COUNT(*) as daily_votes 
    FROM votes 
    WHERE candidateID = {$candidate['candidateID']} 
    GROUP BY DATE(voted_at) 
    ORDER BY vote_date DESC 
    LIMIT 7
");

$daily_data = [];
$daily_labels = [];
while ($row = $vote_history->fetch_assoc()) {
    $daily_labels[] = date('M d', strtotime($row['vote_date']));
    $daily_data[] = $row['daily_votes'];
}
$daily_labels = array_reverse($daily_labels);
$daily_data = array_reverse($daily_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Dashboard - Candidate</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Campaign Center</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="campaign.php"><i class="fas fa-bullhorn"></i> Campaign</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Campaign Banner -->
        <div class="card" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; text-align: center;">
            <i class="fas fa-bullhorn" style="font-size: 48px; margin-bottom: 15px;"></i>
            <h1>Your Campaign Headquarters</h1>
            <p>Track your performance and engage with voters</p>
        </div>

        <div class="dashboard-grid">
            <!-- Campaign Stats -->
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= $candidate['total_votes'] ?></div>
                <div class="stat-label">Total Votes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-value" id="todayVotes">0</div>
                <div class="stat-label">Today's Votes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value" id="avgVotes">0</div>
                <div class="stat-label">Avg Daily Votes</div>
            </div>
        </div>

        <!-- Vote Trends Chart -->
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Vote Trends (Last 7 Days)</h3>
            <canvas id="trendsChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Campaign Tips & Share -->
        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-lightbulb"></i> Campaign Tips</h3>
                <ul style="margin-left: 20px; line-height: 1.8;">
                    <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Share your manifesto on social media</li>
                    <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Engage with voters through community events</li>
                    <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Be transparent and honest about your plans</li>
                    <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Respond to voter concerns promptly</li>
                    <li><i class="fas fa-check-circle" style="color: #10b981;"></i> Use local media to reach more people</li>
                </ul>
            </div>
            <div class="card">
                <h3><i class="fas fa-share-alt"></i> Share Your Campaign</h3>
                <div style="display: flex; gap: 15px; justify-content: center; margin: 20px 0;">
                    <button class="btn btn-secondary" onclick="shareOn('facebook')"><i class="fab fa-facebook"></i> Facebook</button>
                    <button class="btn btn-secondary" onclick="shareOn('twitter')"><i class="fab fa-twitter"></i> Twitter</button>
                    <button class="btn btn-secondary" onclick="shareOn('whatsapp')"><i class="fab fa-whatsapp"></i> WhatsApp</button>
                </div>
                <div class="form-group">
                    <input type="text" id="shareLink" value="<?= BASE_URL ?>candidate/dashboard.php" readonly>
                    <button class="btn btn-sm" onclick="copyLink()"><i class="fas fa-copy"></i> Copy Link</button>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Share your candidate profile link to get more votes!
                </div>
            </div>
        </div>

        <!-- Your Manifesto -->
        <div class="card">
            <h3><i class="fas fa-file-alt"></i> Your Manifesto</h3>
            <div style="background: #f8fafc; padding: 20px; border-radius: 16px; margin-top: 15px;">
                <p style="white-space: pre-line;"><?= nl2br(htmlspecialchars($candidate['manifesto'] ?? 'No manifesto provided yet. Update your profile to add one!')) ?></p>
            </div>
        </div>
    </div>

    <script src="../assets/js/animation.js"></script>
    <script>
        // Vote trends chart
        const ctx = document.getElementById('trendsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($daily_labels) ?>,
                datasets: [{
                    label: 'Daily Votes',
                    data: <?= json_encode($daily_data) ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } }
            }
        });

        // Calculate today's votes and average
        const dailyData = <?= json_encode($daily_data) ?>;
        if (dailyData.length > 0) {
            document.getElementById('todayVotes').textContent = dailyData[dailyData.length - 1] || 0;
            const avg = dailyData.reduce((a, b) => a + b, 0) / dailyData.length;
            document.getElementById('avgVotes').textContent = Math.round(avg);
        }

        function shareOn(platform) {
            const url = encodeURIComponent(window.location.origin + '/online_voting/candidate/dashboard.php');
            const text = encodeURIComponent("Vote for me in the upcoming election! Let's build a better future together!");
            
            let shareUrl = '';
            if (platform === 'facebook') shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            if (platform === 'twitter') shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
            if (platform === 'whatsapp') shareUrl = `https://wa.me/?text=${text}%20${url}`;
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        function copyLink() {
            const input = document.getElementById('shareLink');
            input.select();
            document.execCommand('copy');
            showToast('Link copied to clipboard!', 'success');
        }
    </script>
</body>
</html>