<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../technician/dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } else {
        // Authenticate user
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id, staff_id, full_name, email, password FROM technician WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['staff_id'] = $user['staff_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Set remember me cookie if checked
                if ($remember) {
                    $cookie_value = base64_encode($user['id'] . ':' . hash('sha256', $user['password']));
                    setcookie('remember_token', $cookie_value, time() + (86400 * 30), '/'); // 30 days
                }
                
                // Redirect to dashboard
                header('Location: ../technician/dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Get any session messages
$message = getSessionMessage();
if ($message) {
    if ($message['type'] === 'success') {
        $success = $message['content'];
    } else {
        $error = $message['content'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../js/login.js" defer></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="../index.php" class="logo">
                <img src="../public/unikl-rcmp.png" alt="Universiti Kuala Lumpur Royal College of Medicine Perak logo">
                <span>RCMP-itventory</span>
            </a>
            <div class="nav-links">
                <a href="../index.php">Back to Home</a>
            </div>
        </nav>
    </header>

    <main class="login-container">
        <div class="login-wrapper">
            <div class="login-left">
                <div class="login-brand">
                    <img src="../public/rcmp-white.png" alt="RCMP Logo" class="login-logo">
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your IT inventory management dashboard</p>
                </div>
                <div class="login-features">
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Track equipment in real-time</span>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Manage reservations seamlessly</span>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Generate reports instantly</span>
                    </div>
                </div>
            </div>
            <div class="login-right">
                <div class="login-card">
                    <h2>Sign In</h2>
                    <?php if ($error): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    <form id="loginForm" action="" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        <button type="submit" class="login-button">
                            <span>Sign In</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </form>
                    <div class="login-divider">
                        <span>or</span>
                    </div>
                    <div class="login-help">
                        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                        <p class="login-help-secondary">Need help? <a href="#">Contact IT Support</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

