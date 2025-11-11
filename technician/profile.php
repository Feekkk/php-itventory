<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access the profile page.');
    header('Location: ../auth/login.php');
    exit();
}

$error = '';
$success = '';
$user = getUserData();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name)) {
        $error = 'Full name is required.';
    } elseif (strlen($full_name) < 3) {
        $error = 'Full name must be at least 3 characters long.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if email is already taken by another user
            $check_stmt = $conn->prepare("SELECT id FROM technician WHERE email = ? AND id != ?");
            $check_stmt->bind_param("si", $email, $user['id']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email is already taken by another user.';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // If password change is requested
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Current password is required to change password.';
                    } else {
                        // Verify current password
                        $verify_stmt = $conn->prepare("SELECT password FROM technician WHERE id = ?");
                        $verify_stmt->bind_param("i", $user['id']);
                        $verify_stmt->execute();
                        $verify_result = $verify_stmt->get_result();
                        
                        if ($verify_result->num_rows === 1) {
                            $user_data = $verify_result->fetch_assoc();
                            
                            if (!password_verify($current_password, $user_data['password'])) {
                                $error = 'Current password is incorrect.';
                            } elseif (strlen($new_password) < 8) {
                                $error = 'New password must be at least 8 characters long.';
                            } elseif ($new_password !== $confirm_password) {
                                $error = 'New passwords do not match.';
                            } else {
                                // Update with new password
                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $update_stmt = $conn->prepare("UPDATE technician SET full_name = ?, email = ?, password = ? WHERE id = ?");
                                $update_stmt->bind_param("sssi", $full_name, $email, $hashed_password, $user['id']);
                                
                                if ($update_stmt->execute()) {
                                    // Update session
                                    $_SESSION['full_name'] = $full_name;
                                    $_SESSION['user_email'] = $email;
                                    $user['full_name'] = $full_name;
                                    $user['email'] = $email;
                                    $success = 'Profile updated successfully!';
                                } else {
                                    $error = 'Failed to update profile.';
                                }
                                $update_stmt->close();
                            }
                        }
                        $verify_stmt->close();
                    }
                } else {
                    // Update without password change
                    $update_stmt = $conn->prepare("UPDATE technician SET full_name = ?, email = ? WHERE id = ?");
                    $update_stmt->bind_param("ssi", $full_name, $email, $user['id']);
                    
                    if ($update_stmt->execute()) {
                        // Update session
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['user_email'] = $email;
                        $user['full_name'] = $full_name;
                        $user['email'] = $email;
                        $success = 'Profile updated successfully!';
                    } else {
                        $error = 'Failed to update profile.';
                    }
                    $update_stmt->close();
                }
            }
            
            $conn->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
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
    <title>Profile - RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/profile.css">
    <script src="../js/dashboard.js" defer></script>
    <script src="../js/profile.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button class="burger-menu" id="burgerMenu" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="logo">
                <img src="../public/unikl-rcmp.png" alt="RCMP Logo">
                <span>RCMP-itventory</span>
            </div>
        </div>
        <div class="user-info">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- Dropdown Menu -->
    <nav class="dropdown-menu" id="dropdownMenu">
        <ul class="menu-list">
            <li><a href="dashboard.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Dashboard</span>
            </a></li>
            <li><a href="equipment.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="5" width="18" height="11" rx="1.5"></rect>
                    <path d="M4 17h16l1 2H3l1-2z"></path>
                </svg>
                <span>Equipment Inventory</span>
            </a></li>
            <li><a href="reservations.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 7h16v11H4z"></path>
                    <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                    <path d="M7 11h10"></path>
                    <path d="M10 14h4"></path>
                </svg>
                <span>My Reservations</span>
            </a></li>
            <li><a href="request.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Request Equipment</span>
            </a></li>
            <li><a href="history.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>History</span>
            </a></li>
            <li><a href="profile.php" class="menu-item active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-staff-id">Staff ID: <?php echo htmlspecialchars($user['staff_id']); ?></p>
            </div>
        </div>

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

        <div class="profile-card">
            <div class="card-header">
                <h2>Edit Profile</h2>
                <p>Update your personal information</p>
            </div>
            <form id="profileForm" method="POST" class="profile-form">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="staff_id">Staff ID</label>
                        <input type="text" id="staff_id" value="<?php echo htmlspecialchars($user['staff_id']); ?>" disabled>
                        <small>Staff ID cannot be changed</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Change Password</h3>
                    <p class="section-description">Leave blank if you don't want to change your password</p>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <small>Password must be at least 8 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <span>Save Changes</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                    </button>
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

