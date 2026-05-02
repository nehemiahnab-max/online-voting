<?php
// candidate/profile.php
include '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'candidate') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current data
$profile = $conn->query("
    SELECT u.*, c.* 
    FROM users u 
    JOIN candidates c ON u.id = c.userID 
    WHERE u.id = $user_id
")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $party_name = $conn->real_escape_string($_POST['party_name']);
    $slogan = $conn->real_escape_string($_POST['slogan']);
    $manifesto = $conn->real_escape_string($_POST['manifesto']);
    
    $conn->begin_transaction();
    try {
        $conn->query("UPDATE users SET name = '$name', phone = '$phone' WHERE id = $user_id");
        $conn->query("UPDATE candidates SET party_name = '$party_name', slogan = '$slogan', manifesto = '$manifesto' WHERE userID = $user_id");
        $conn->commit();
        $message = "Profile updated successfully!";
        
        // Refresh data
        $profile = $conn->query("SELECT u.*, c.* FROM users u JOIN candidates c ON u.id = c.userID WHERE u.id = $user_id")->fetch_assoc();
        $_SESSION['name'] = $name;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Update failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Candidate</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo"><i class="fas fa-vote-yea"></i><span>My Profile</span></div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="campaign.php"><i class="fas fa-bullhorn"></i> Campaign</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <div class="dashboard-grid">
            <!-- Profile Info Card -->
            <div class="card">
                <div style="text-align: center;">
                    <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-user-tie fa-3x"></i></div>
                    <h2><?= htmlspecialchars($profile['name']) ?></h2>
                    <p style="color: var(--gray);"><?= htmlspecialchars($profile['email']) ?></p>
                    <p><span class="badge" style="background: #f59e0b; padding: 5px 15px; border-radius: 20px;">Candidate</span></p>
                </div>
                <hr style="margin: 20px 0;">
                <p><i class="fas fa-phone"></i> <strong>Phone:</strong> <?= htmlspecialchars($profile['phone'] ?? 'Not provided') ?></p>
                <p><i class="fas fa-flag"></i> <strong>Party:</strong> <?= htmlspecialchars($profile['party_name'] ?? 'Not specified') ?></p>
                <p><i class="fas fa-tag"></i> <strong>Slogan:</strong> "<?= htmlspecialchars($profile['slogan'] ?? 'No slogan') ?>"</p>
                <p><i class="fas fa-check-circle"></i> <strong>Status:</strong> <?= $profile['approved'] ? '<span style="color: #10b981;">Approved</span>' : '<span style="color: #f59e0b;">Pending Approval</span>' ?></p>
                <p><i class="fas fa-chart-line"></i> <strong>Total Votes:</strong> <?= $profile['total_votes'] ?></p>
            </div>

            <!-- Edit Profile Form -->
            <div class="card">
                <h3><i class="fas fa-edit"></i> Edit Profile</h3>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($profile['name']) ?>" required>
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    </div>
                    <div class="form-group">
                        <input type="text" name="party_name" id="party_name" value="<?= htmlspecialchars($profile['party_name'] ?? '') ?>">
                        <label for="party_name"><i class="fas fa-flag"></i> Political Party</label>
                    </div>
                    <div class="form-group">
                        <input type="text" name="slogan" id="slogan" value="<?= htmlspecialchars($profile['slogan'] ?? '') ?>">
                        <label for="slogan"><i class="fas fa-bullhorn"></i> Campaign Slogan</label>
                    </div>
                    <div class="form-group">
                        <textarea name="manifesto" id="manifesto" rows="5"><?= htmlspecialchars($profile['manifesto'] ?? '') ?></textarea>
                        <label for="manifesto"><i class="fas fa-file-alt"></i> Your Manifesto</label>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/animation.js"></script>
</body>
</html>