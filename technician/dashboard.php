<?php

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access the dashboard.');
    header('Location: ../auth/login.php');
    exit();
}

// Get user data
$user = getUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="../js/dashboard.js" defer></script>
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
            <li><a href="dashboard.php" class="menu-item active">
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
            <li><a href="profile.php" class="menu-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1>Welcome to Your Dashboard</h1>
            <p>Staff ID: <?php echo htmlspecialchars($user['staff_id']); ?> | Email: <?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Equipment Inventory</h2>
                <p>Browse and manage IT equipment available for reservation.</p>
            </div>
            <div class="card">
                <h2>My Reservations</h2>
                <p>View and manage your equipment reservations.</p>
            </div>
            <div class="card">
                <h2>Request Equipment</h2>
                <p>Submit a new reservation request for IT equipment.</p>
            </div>
        </div>
    </div>
</body>
</html>

