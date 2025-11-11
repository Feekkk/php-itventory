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
                    <form id="loginForm" action="#" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
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
                        <p>Need help? <a href="#">Contact IT Support</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

