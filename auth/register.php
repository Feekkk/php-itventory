<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($staff_id)) {
        $error = 'Staff ID is required.';
    } elseif (empty($full_name)) {
        $error = 'Full name is required.';
    } elseif (strlen($full_name) < 3) {
        $error = 'Full name must be at least 3 characters long.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if staff ID or email already exists
            $conn = getDBConnection();
            
            // Check for duplicates
            $check_stmt = $conn->prepare("SELECT id FROM technician WHERE staff_id = ? OR email = ?");
            if (!$check_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $check_stmt->bind_param("ss", $staff_id, $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Staff ID or Email already exists.';
                $check_stmt->close();
                $conn->close();
            } else {
                $check_stmt->close();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new technician
                $insert_stmt = $conn->prepare("INSERT INTO technician (staff_id, full_name, email, password) VALUES (?, ?, ?, ?)");
                if (!$insert_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $insert_stmt->bind_param("ssss", $staff_id, $full_name, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $conn->close();
                    setSessionMessage('success', 'Account created successfully! Please sign in.');
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Registration failed: ' . $insert_stmt->error;
                    $insert_stmt->close();
                    $conn->close();
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
            if (isset($conn)) {
                $conn->close();
            }
        }
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
    <title>Sign Up - RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../js/register.js" defer></script>
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
                    <h1>Join RCMP-itventory</h1>
                    <p>Create your account to start managing IT equipment and reservations</p>
                </div>
                <div class="login-features">
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Access equipment inventory</span>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Reserve devices instantly</span>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 13l4 4L19 7" stroke="#2dc48d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span>Track your reservations</span>
                    </div>
                </div>
            </div>
            <div class="login-right">
                <div class="login-card">
                    <h2>Create Account</h2>
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
                    <form id="registerForm" action="" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="staff_id">Staff ID</label>
                            <input type="text" id="staff_id" name="staff_id" placeholder="Enter your staff ID" value="<?php echo htmlspecialchars($_POST['staff_id'] ?? ''); ?>" required autocomplete="username">
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"></circle>
                                    </svg>
                                </button>
                            </div>
                            <small class="password-hint">Password must be at least 8 characters long</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="login-button">
                            <span>Create Account</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </form>
                    <div class="login-divider">
                        <span>or</span>
                    </div>
                    <div class="login-help">
                        <p>Already have an account? <a href="login.php">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

