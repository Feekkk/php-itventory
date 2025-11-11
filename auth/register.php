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
                    <form id="registerForm" action="#" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="staff_id">Staff ID</label>
                            <input type="text" id="staff_id" name="staff_id" placeholder="Enter your staff ID" required autocomplete="username">
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
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

