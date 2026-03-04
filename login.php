<?php
session_start();
include 'db.php'; // ✅ DATABASE CONNECTION RAKHA

// ===== LOGIN PROTECTION =====
// If user already logged in, redirect to index
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$msg = '';
$msg_type = '';

// Handle form submission
if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Prepared statement (SECURE against SQL injection)
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();

        // Verify password
        if(password_verify($password, $user['password'])){
            // ✅ Set all required sessions
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');

            // ✅ Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            $msg = "❌ Invalid password! Please try again.";
            $msg_type = 'error';
        }
    } else {
        $msg = "❌ Email not registered. Please signup first.";
        $msg_type = 'error';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="lognsign.css">
</head>
<body>

    <div class="login-container">
        <!-- ===== LEFT SIDE - BRANDING ===== -->
        <div class="login-left">
            <div class="inventory-icon">📦</div>
            <h1>Inventory Management</h1>
            <p>Secure access to your inventory system. Manage products, track stock levels, and optimize your business operations.</p>
        </div>

        <!-- ===== RIGHT SIDE - LOGIN FORM ===== -->
        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back!</h2>
                <p>Login to access your inventory</p>
            </div>

            <?php if($msg): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input type="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                  </div>

                <button type="submit" name="login" class="btn-login">
                    Login to Inventory
                </button>

                <div class="signup-link">
                    New user? <a href="signup.php">Signup here</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>