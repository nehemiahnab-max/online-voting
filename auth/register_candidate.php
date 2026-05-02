<?php
// auth/register_candidate.php
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
    $manifesto = $conn->real_escape_string($_POST['manifesto']);
    $party_name = $conn->real_escape_string($_POST['party_name']);
    $slogan = $conn->real_escape_string($_POST['slogan']);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {
            $conn->begin_transaction();
            
            try {
                // Insert user - make sure number of ? matches number of variables
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'candidate')");
                $stmt->bind_param("ssss", $name, $email, $password, $phone);
                $stmt->execute();
                $userID = $conn->insert_id;
                
                // Insert candidate
                $stmt2 = $conn->prepare("INSERT INTO candidates (userID, manifesto, party_name, slogan) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $userID, $manifesto, $party_name, $slogan);
                $stmt2->execute();
                
                $conn->commit();
                $success = "Registration successful! Your account is pending admin approval.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed: " . $e->getMessage();
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
    <title>Candidate Registration - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 600px; margin: 50px auto;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="stat-icon" style="margin: 0 auto 20px; background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-user-tie"></i></div>
                <h2>Candidate Registration</h2>
                <p style="color: var(--gray);">Register to participate in the election</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
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
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                </div>
                
                <div class="form-group">
                    <input type="text" name="party_name" id="party_name" placeholder=" " required>
                    <label for="party_name"><i class="fas fa-flag"></i> Political Party</label>
                </div>
                
                <div class="form-group">
                    <input type="text" name="slogan" id="slogan" placeholder=" ">
                    <label for="slogan"><i class="fas fa-bullhorn"></i> Campaign Slogan</label>
                </div>
                
                <div class="form-group">
                    <textarea name="manifesto" id="manifesto" rows="4" placeholder=" " required></textarea>
                    <label for="manifesto"><i class="fas fa-file-alt"></i> Your Manifesto</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder=" " required>
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                </div>
                
                <button type="submit" name="register" class="btn btn-warning" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Register as Candidate
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