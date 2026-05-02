<?php
// auth/login.php
include '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') header("Location: " . BASE_URL . "admin/dashboard.php");
    elseif ($role === 'candidate') header("Location: " . BASE_URL . "candidate/dashboard.php");
    else header("Location: " . BASE_URL . "voter/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = md5($_POST['password']);
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND is_active = 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if candidate is approved
        if ($user['role'] === 'candidate') {
            $check = $conn->query("SELECT approved FROM candidates WHERE userID = {$user['id']}");
            $candidate = $check->fetch_assoc();
            if (!$candidate || $candidate['approved'] == 0) {
                $error = "Your account is pending admin approval";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                header("Location: " . BASE_URL . "candidate/dashboard.php");
                exit();
            }
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Log activity
            $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'login', ?)");
            $ip = $_SERVER['REMOTE_ADDR'];
            $log->bind_param("is", $user['id'], $ip);
            $log->execute();
            
            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "voter/dashboard.php");
            }
            exit();
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 450px; margin: 80px auto;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-vote-yea"></i></div>
                <h2>Welcome Back</h2>
                <p style="color: var(--gray);">Login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder=" " required>
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div style="text-align: center; margin-top: 24px;">
                    <p>Don't have an account? <a href="register_voter.php" style="color: var(--primary);">Register as Voter</a></p>
                    <p style="margin-top: 10px;">Want to be a candidate? <a href="register_candidate.php" style="color: var(--secondary);">Register as Candidate</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/animation.js"></script>
</body>
</html>










<?php
// auth/login.php
include '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') header("Location: " . BASE_URL . "admin/dashboard.php");
    elseif ($role === 'candidate') header("Location: " . BASE_URL . "candidate/dashboard.php");
    else header("Location: " . BASE_URL . "voter/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = md5($_POST['password']);
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND is_active = 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if candidate is approved
        if ($user['role'] === 'candidate') {
            $check = $conn->query("SELECT approved FROM candidates WHERE userID = {$user['id']}");
            if ($check->num_rows > 0) {
                $candidate = $check->fetch_assoc();
                if ($candidate['approved'] == 0) {
                    $error = "Your account is pending admin approval";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];
                    header("Location: " . BASE_URL . "candidate/dashboard.php");
                    exit();
                }
            } else {
                $error = "Candidate profile not found";
            }
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "voter/dashboard.php");
            }
            exit();
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 450px; margin: 80px auto;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="stat-icon" style="margin: 0 auto 20px;"><i class="fas fa-vote-yea"></i></div>
                <h2>Welcome Back</h2>
                <p style="color: var(--gray);">Login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder=" " required>
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div style="text-align: center; margin-top: 24px;">
                    <p>Don't have an account? <a href="register_voter.php" style="color: var(--primary);">Register as Voter</a></p>
                    <p style="margin-top: 10px;">Want to be a candidate? <a href="register_candidate.php" style="color: var(--secondary);">Register as Candidate</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/animation.js"></script>
</body>
</html>