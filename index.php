<?php
// index.php
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alelneh's Online Voting System | Secure & Transparent Elections</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="card" style="text-align: center; margin: 60px 0; background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <div class="float">
                <i class="fas fa-vote-yea" style="font-size: 80px; margin-bottom: 30px;"></i>
            </div>
            <h1 style="font-size: 48px; margin-bottom: 20px;">Welcome to Online Voting System</h1>
            <p style="font-size: 20px; margin-bottom: 40px; opacity: 0.95;">Secure, Transparent, and Easy-to-Use Platform for Democratic Elections</p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['role'] === 'candidate' ? 'candidate/dashboard.php' : 'voter/dashboard.php') ?>" class="btn btn-lg" style="background: white; color: #667eea;">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
            <?php else: ?>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="auth/login.php" class="btn btn-lg" style="background: white; color: #667eea;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="auth/register_voter.php" class="btn btn-lg btn-success">
                        <i class="fas fa-user-plus"></i> Register as Voter
                    </a>
                    <a href="auth/register_candidate.php" class="btn btn-lg btn-warning">
                        <i class="fas fa-user-tie"></i> Register as Candidate
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Features Section -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Secure Voting</h3>
                <p>Your vote is encrypted and protected. One person, one vote system.</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Real-time Results</h3>
                <p>View election results instantly with beautiful analytics and charts.</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-mobile-alt"></i></div>
                <h3>Mobile Friendly</h3>
                <p>Access from any device - desktop, tablet, or smartphone.</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <h3>24/7 Access</h3>
                <p>Vote anytime, anywhere with our cloud-based platform.</p>
            </div>
        </div>

        <!-- How It Works -->
        <div class="card" style="margin: 40px 0;">
            <h2 style="text-align: center; margin-bottom: 40px;">How It Works</h2>
            <div class="dashboard-grid">
                <div style="text-align: center;">
                    <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-user-plus"></i></div>
                    <h3>1. Register</h3>
                    <p>Create your account as a voter or candidate</p>
                </div>
                <div style="text-align: center;">
                    <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-check-circle"></i></div>
                    <h3>2. Verification</h3>
                    <p>Your identity is verified by the admin</p>
                </div>
                <div style="text-align: center;">
                    <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-vote-yea"></i></div>
                    <h3>3. Vote</h3>
                    <p>Cast your vote securely online</p>
                </div>
                <div style="text-align: center;">
                    <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-chart-bar"></i></div>
                    <h3>4. Results</h3>
                    <p>View real-time election results</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/animation.js"></script>
</body>
</html>