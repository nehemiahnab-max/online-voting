<?php
// admin/settings.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = '';
$error = '';

// Create settings table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Insert default settings if empty
$check = $conn->query("SELECT COUNT(*) as count FROM system_settings");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES 
        ('site_name', 'Online Voting System'),
        ('contact_email', 'admin@votingsystem.com'),
        ('voting_enabled', '1')
    ");
}

// Handle Election Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_election'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Check if election exists
    $check = $conn->query("SELECT id FROM elections LIMIT 1");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE elections SET title='$title', description='$description', start_date='$start_date', end_date='$end_date', status='$status'");
        $message = "Election settings updated successfully!";
    } else {
        $conn->query("INSERT INTO elections (title, description, start_date, end_date, status, created_by) VALUES ('$title', '$description', '$start_date', '$end_date', '$status', {$_SESSION['user_id']})");
        $message = "Election created successfully!";
    }
}

// Handle Admin Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = md5($_POST['current_password']);
    $new_password = md5($_POST['new_password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    $admin = $conn->query("SELECT password FROM users WHERE id = {$_SESSION['user_id']} AND role = 'admin'")->fetch_assoc();
    
    if ($admin['password'] != $current_password) {
        $error = "Current password is incorrect";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match";
    } else {
        $conn->query("UPDATE users SET password = '$new_password' WHERE id = {$_SESSION['user_id']}");
        $message = "Password changed successfully!";
    }
}

// Handle System Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_system'])) {
    $site_name = $conn->real_escape_string($_POST['site_name']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);
    $voting_enabled = isset($_POST['voting_enabled']) ? 1 : 0;
    
    // Update or insert settings
    $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES 
        ('site_name', '$site_name'),
        ('contact_email', '$contact_email'),
        ('voting_enabled', '$voting_enabled')
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    $message = "System settings updated successfully!";
}

// Get current election data
$election_result = $conn->query("SELECT * FROM elections ORDER BY id DESC LIMIT 1");
$election = $election_result->num_rows > 0 ? $election_result->fetch_assoc() : null;

// Get system settings
$settings_result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
while ($row_set = $settings_result->fetch_assoc()) {
    $settings[$row_set['setting_key']] = $row_set['setting_value'];
}

// Get statistics
$total_voters_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'");
$total_voters = $total_voters_result->num_rows > 0 ? $total_voters_result->fetch_assoc()['count'] : 0;

$total_votes_result = $conn->query("SELECT COUNT(*) as count FROM votes");
$total_votes = $total_votes_result->num_rows > 0 ? $total_votes_result->fetch_assoc()['count'] : 0;

$turnout = $total_voters > 0 ? round(($total_votes / $total_voters) * 100, 1) : 0;

// Get total users
$total_users_result = $conn->query("SELECT COUNT(*) as c FROM users");
$total_users = $total_users_result->num_rows > 0 ? $total_users_result->fetch_assoc()['c'] : 0;

// Get total candidates
$total_candidates_result = $conn->query("SELECT COUNT(*) as c FROM candidates");
$total_candidates = $total_candidates_result->num_rows > 0 ? $total_candidates_result->fetch_assoc()['c'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Nehemiah Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>System Settings</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
                <a href="manage_candidates.php"><i class="fas fa-user-tie"></i> Candidates</a>
                <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Election Settings -->
            <div class="card">
                <h3><i class="fas fa-calendar-alt"></i> Election Settings</h3>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($election['title'] ?? 'General Election 2025') ?>" required>
                        <label for="title"><i class="fas fa-tag"></i> Election Title</label>
                    </div>
                    <div class="form-group">
                        <textarea name="description" id="description" rows="3"><?= htmlspecialchars($election['description'] ?? 'Vote for your preferred candidate') ?></textarea>
                        <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" name="start_date" id="start_date" value="<?= isset($election['start_date']) ? date('Y-m-d\TH:i', strtotime($election['start_date'])) : '' ?>" required>
                        <label for="start_date"><i class="fas fa-play"></i> Start Date & Time</label>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" name="end_date" id="end_date" value="<?= isset($election['end_date']) ? date('Y-m-d\TH:i', strtotime($election['end_date'])) : '' ?>" required>
                        <label for="end_date"><i class="fas fa-stop"></i> End Date & Time</label>
                    </div>
                    <div class="form-group">
                        <select name="status" id="status">
                            <option value="upcoming" <?= (isset($election['status']) && $election['status'] == 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                            <option value="active" <?= (isset($election['status']) && $election['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="closed" <?= (isset($election['status']) && $election['status'] == 'closed') ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <label for="status"><i class="fas fa-info-circle"></i> Election Status</label>
                    </div>
                    <button type="submit" name="update_election" class="btn"><i class="fas fa-save"></i> Save Election Settings</button>
                </form>
            </div>

            <!-- Voter Turnout -->
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Voter Turnout</h3>
                <div style="text-align: center; padding: 20px;">
                    <div style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                        <canvas id="turnoutChart"></canvas>
                    </div>
                    <h2 style="margin-top: 20px;"><?= $turnout ?>%</h2>
                    <p><?= $total_votes ?> out of <?= $total_voters ?> voters participated</p>
                    <div class="progress-container" style="margin-top: 20px;">
                        <div class="progress-bar" style="width: <?= $turnout ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <h3><i class="fas fa-key"></i> Change Admin Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <input type="password" name="current_password" id="current_password" required>
                        <label for="current_password"><i class="fas fa-lock"></i> Current Password</label>
                    </div>
                    <div class="form-group">
                        <input type="password" name="new_password" id="new_password" required>
                        <label for="new_password"><i class="fas fa-key"></i> New Password</label>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Change Password</button>
                </form>
            </div>

            <!-- System Settings -->
            <div class="card">
                <h3><i class="fas fa-cogs"></i> System Configuration</h3>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="site_name" id="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'Online Voting System') ?>" required>
                        <label for="site_name"><i class="fas fa-globe"></i> Site Name</label>
                    </div>
                    <div class="form-group">
                        <input type="email" name="contact_email" id="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? 'admin@votingsystem.com') ?>" required>
                        <label for="contact_email"><i class="fas fa-envelope"></i> Contact Email</label>
                    </div>
                    <div class="form-group">
                        <label style="position: relative; left: 0; top: 0; transform: none; margin-bottom: 10px; display: block; cursor: pointer;">
                            <input type="checkbox" name="voting_enabled" value="1" <?= (isset($settings['voting_enabled']) && $settings['voting_enabled'] == '1') ? 'checked' : '' ?>>
                            <i class="fas fa-toggle-on"></i> Enable Voting System
                        </label>
                    </div>
                    <button type="submit" name="update_system" class="btn"><i class="fas fa-save"></i> Save System Settings</button>
                </form>
            </div>
        </div>

        <!-- System Information -->
        <div class="card">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div>
                    <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                    <p><strong>MySQL Version:</strong> <?= $conn->server_info ?></p>
                    <p><strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?></p>
                </div>
                <div>
                    <p><strong>Total Users:</strong> <?= $total_users ?></p>
                    <p><strong>Total Candidates:</strong> <?= $total_candidates ?></p>
                    <p><strong>Total Votes:</strong> <?= $total_votes ?></p>
                </div>
                <div>
                    <p><strong>Last Backup:</strong> <?= date('M d, Y H:i:s') ?></p>
                    <p><strong>System Status:</strong> <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Operational</span></p>
                    <button class="btn btn-sm btn-secondary" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/animation.js"></script>
    <script>
        // Voter Turnout Chart
        const ctx = document.getElementById('turnoutChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Voted', 'Not Voted'],
                datasets: [{
                    data: [<?= $total_votes ?>, <?= max(0, $total_voters - $total_votes) ?>],
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
    </script>
</body>
</html>