<?php
// auth/register_voter.php
include '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {
            $voter_id = "VOT" . strtoupper(uniqid());
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, voter_id) VALUES (?, ?, ?, ?, 'voter', ?)");
            $stmt->bind_param("sssss", $name, $email, $password, $phone, $voter_id);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Your Voter ID is: <strong>$voter_id</strong>. You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Registration - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="stat-icon" style="margin: 0 auto 20px; background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-users"></i></div>
                <h2>Voter Registration</h2>
                <p style="color: var(--gray);">Create your voter account</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" id="name" placeholder=" " required>
                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder=" " required>
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                </div>
                
                <div class="form-group">
                    <input type="tel" name="phone" id="phone" placeholder=" ">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number (Optional)</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder=" " required>
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                </div>
                
                <button type="submit" class="btn btn-success" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Register as Voter
                </button>
                
                <div style="text-align: center; margin-top: 24px;">
                    <p>Already have an account? <a href="login.php" style="color: var(--primary);">Login</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/animation.js"></script>
</body>
</html>