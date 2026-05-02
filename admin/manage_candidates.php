<?php
// admin/manage_candidates.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Get all candidates with details
$candidates = $conn->query("
    SELECT u.id, u.name, u.email, u.phone, c.candidateID, c.manifesto, c.party_name, c.slogan, 
           c.approved, c.approved_at, c.applied_at, c.total_votes
    FROM users u 
    JOIN candidates c ON u.id = c.userID 
    ORDER BY c.approved DESC, c.total_votes DESC
");

// Statistics
$total_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'];
$approved_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE approved = 1")->fetch_assoc()['count'];
$pending_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE approved = 0")->fetch_assoc()['count'];
$total_votes_all = $conn->query("SELECT SUM(total_votes) as total FROM candidates")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Nehemiah Manage Candidates - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Manage Candidates</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
                <a href="manage_candidates.php"><i class="fas fa-user-tie"></i> Candidates</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Stats Cards -->
        <div class="dashboard-grid">
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-user-tie"></i></div><div class="stat-value"><?= $total_candidates ?></div><div class="stat-label">Total Candidates</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-value"><?= $approved_candidates ?></div><div class="stat-label">Approved</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-value"><?= $pending_candidates ?></div><div class="stat-label">Pending</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-chart-line"></i></div><div class="stat-value"><?= $total_votes_all ?></div><div class="stat-label">Total Votes</div></div>
        </div>

        <!-- Candidates Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Party</th><th>Slogan</th><th>Status</th><th>Votes</th><th>Applied</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $candidates->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['candidateID'] ?></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong><br><small style="color: gray;"><?= htmlspecialchars($row['email']) ?></small></td>
                        <td><?= htmlspecialchars($row['party_name'] ?? 'N/A') ?></td>
                        <td><em>"<?= htmlspecialchars(substr($row['slogan'] ?? '', 0, 50)) ?>"</em></td>
                        <td>
                            <?php if ($row['approved']): ?>
                                <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Approved</span>
                            <?php else: ?>
                                <span style="color: #f59e0b;"><i class="fas fa-hourglass-half"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><span style="font-size: 20px; font-weight: bold;"><?= $row['total_votes'] ?></span></td>
                        <td><?= date('M d, Y', strtotime($row['applied_at'])) ?></td>
                        <td>
                            <?php if (!$row['approved']): ?>
                                <a href="approve_candidate.php?id=<?= $row['candidateID'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this candidate?')">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                            <?php endif; ?>
                            <a href="view_candidate.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="remove_user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this candidate?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../assets/js/animation.js"></script>
</body>
</html>






