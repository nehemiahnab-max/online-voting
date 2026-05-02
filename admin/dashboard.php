<?php
// admin/dashboard.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_voters = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'")->fetch_assoc()['count'];
$total_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'];
$pending_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE approved = 0")->fetch_assoc()['count'];
$total_votes = $conn->query("SELECT COUNT(*) as count FROM votes")->fetch_assoc()['count'];

// Get recent activities
$activities = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nehemiah Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-vote-yea"></i>
                <span>Admin Panel</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
                <a href="manage_candidates.php"><i class="fas fa-user-tie"></i> Candidates</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
        
        <!-- Stats Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <div class="stat-value"><?= $total_voters ?></div>
                <div class="stat-label">Total Voters</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="stat-value"><?= $total_candidates ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?= $pending_candidates ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= $total_votes ?></div>
                <div class="stat-label">Total Votes Cast</div>
            </div>
        </div>
        
        <!-- Pending Candidates -->
        <div class="card" style="margin-bottom: 30px;">
            <h3><i class="fas fa-user-clock"></i> Pending Candidate Approvals</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Party</th><th>Applied On</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $pending = $conn->query("
                            SELECT u.id, u.name, u.email, c.party_name, c.applied_at, c.candidateID 
                            FROM users u 
                            JOIN candidates c ON u.id = c.userID 
                            WHERE c.approved = 0
                        ");
                        if ($pending->num_rows > 0):
                            while ($row = $pending->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['party_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['applied_at'])) ?></td>
                            <td>
                                <a href="approve_candidate.php?id=<?= $row['candidateID'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this candidate?')">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align: center;">No pending candidates</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Recent Activity Log</h3>
            <div class="table-container">
                <table>
                    <thead><tr><th>User</th><th>Action</th><th>IP Address</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php while ($log = $activities->fetch_assoc()): 
                            $user = $conn->query("SELECT name FROM users WHERE id = {$log['user_id']}")->fetch_assoc();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name'] ?? 'System') ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/animation.js"></script>
</body>
</html>