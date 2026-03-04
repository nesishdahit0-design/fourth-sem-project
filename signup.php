<?php
session_start();
include 'db.php';

// ===== SIGNUP PROTECTION =====
// If user already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$msg = '';
$msg_type = '';

// Handle form submission
if(isset($_POST['signup'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if(empty($name) || empty($email) || empty($password) || empty($confirm_password)){
        $msg = "❌ All fields are required!";
        $msg_type = 'error';
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg = "❌ Invalid email format!";
        $msg_type = 'error';
    }
    elseif(strlen($password) < 6){
        $msg = "❌ Password must be at least 6 characters!";
        $msg_type = 'error';
    }
    elseif($password !== $confirm_password){
        $msg = "❌ Passwords do not match!";
        $msg_type = 'error';
    }
    else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows > 0){
            $msg = "❌ Email already registered! Please login.";
            $msg_type = 'error';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if($stmt->execute()){
                $msg = "✅ Registration successful! Please login.";
                $msg_type = 'success';
                
                // Auto redirect after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $msg = "❌ Registration failed! Please try again.";
                $msg_type = 'error';
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Inventory Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="lognsign.css">
</head>
<body>

    <div class="signup-container">
        <!-- ===== LEFT SIDE - BRANDING ===== -->
        <div class="signup-left">
            <div class="inventory-icon">📦</div>
            <h1>Join Us Today!</h1>
            <p>Create your account and start managing your inventory efficiently. Track products, monitor stock, and grow your business.</p>
        </div>

        <!-- ===== RIGHT SIDE - SIGNUP FORM ===== -->
        <div class="signup-right">
            <div class="signup-header">
                <h2>Create Account</h2>
                <p>Fill in your details to get started</p>
            </div>

            <?php if($msg): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" name="name" placeholder="Enter your full name" >
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input type="email" name="email" placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" placeholder="Create a strong password">
                    </div>
                    <div class="password-strength">Minimum 6 characters required</div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔐</span>
                        <input type="password" name="confirm_password" placeholder="Re-enter your password" >
                    </div>
                </div>

                <button type="submit" name="signup" class="btn-signup">
                    Create My Account
                </button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login Here</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>