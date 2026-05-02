<?php
// admin/manage_users.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

$sql = "SELECT * FROM users WHERE 1=1";
if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR voter_id LIKE '%$search%')";
}
if ($role_filter) {
    $sql .= " AND role = '$role_filter'";
}
$sql .= " ORDER BY created_at DESC";

$users = $conn->query($sql);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
$total_voters = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'")->fetch_assoc()['count'];
$total_candidates = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Nehemiah Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>Manage Users</span></div>
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
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-value"><?= $total_users ?></div><div class="stat-label">Total Users</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-user-shield"></i></div><div class="stat-value"><?= $total_admins ?></div><div class="stat-label">Admins</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-user-check"></i></div><div class="stat-value"><?= $total_voters ?></div><div class="stat-label">Voters</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-user-tie"></i></div><div class="stat-value"><?= $total_candidates ?></div><div class="stat-label">Candidates</div></div>
        </div>

        <!-- Search & Filter -->
        <div class="card" style="margin-bottom: 20px;">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <input type="text" name="search" placeholder=" " value="<?= htmlspecialchars($search) ?>">
                    <label><i class="fas fa-search"></i> Search by name, email or voter ID</label>
                </div>
                <div class="form-group">
                    <select name="role">
                        <option value="">All Roles</option>
                        <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="voter" <?= $role_filter == 'voter' ? 'selected' : '' ?>>Voter</option>
                        <option value="candidate" <?= $role_filter == 'candidate' ? 'selected' : '' ?>>Candidate</option>
                    </select>
                </div>
                <button type="submit" class="btn"><i class="fas fa-filter"></i> Filter</button>
                <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Voter ID</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['voter_id'] ?? 'N/A') ?></td>
                            <td>
                                <span style="padding: 4px 12px; border-radius: 20px; background: <?= 
                                    $row['role'] == 'admin' ? '#ef4444' : ($row['role'] == 'candidate' ? '#f59e0b' : '#10b981') 
                                ?>; color: white; font-size: 12px;">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                    <span style="color: #ef4444;"><i class="fas fa-ban"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 5px 12px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="remove_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" style="padding: 5px 12px;" onclick="return confirm('Delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($row['role'] == 'candidate'): ?>
                                    <a href="view_candidate.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" style="padding: 5px 12px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center;">No users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../assets/js/animation.js"></script>
</body>
</html>