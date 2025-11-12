<?php
/**
 * Header Component with Burger Menu
 * 
 * Usage: require_once __DIR__ . '/component/header.php';
 * 
 * @param string $activePage The active page identifier (e.g., 'dashboard', 'profile', 'inventory')
 * @param string $pageTitle The page title for the <title> tag
 */

// Ensure user is logged in
if (!isset($user) || !$user) {
    if (!function_exists('getUserData')) {
        require_once __DIR__ . '/../auth/session.php';
    }
    if (!isLoggedIn()) {
        setSessionMessage('error', 'Please log in to access this page.');
        header('Location: ../auth/login.php');
        exit();
    }
    $user = getUserData();
}

// Set default active page if not provided
$activePage = $activePage ?? 'dashboard';
$pageTitle = $pageTitle ?? 'RCMP-itventory';

// Allow pages to add additional CSS/JS files
$additionalCSS = $additionalCSS ?? [];
$additionalJS = $additionalJS ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <?php foreach ($additionalCSS as $css): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
    <?php endforeach; ?>
    <script src="../js/dashboard.js" defer></script>
    <?php foreach ($additionalJS as $js): ?>
        <script src="<?php echo htmlspecialchars($js); ?>" defer></script>
    <?php endforeach; ?>
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
            <li><a href="dashboard.php" class="menu-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Dashboard</span>
            </a></li>
            <li><a href="ListInventory.php" class="menu-item <?php echo $activePage === 'inventory' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="5" width="18" height="11" rx="1.5"></rect>
                    <path d="M4 17h16l1 2H3l1-2z"></path>
                </svg>
                <span>List Inventory</span>
            </a></li>
            <li><a href="Pickup.php" class="menu-item <?php echo $activePage === 'pickup' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 7h16v11H4z"></path>
                    <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                    <path d="M7 11h10"></path>
                    <path d="M10 14h4"></path>
                </svg>
                <span>Pickup</span>
            </a></li>
            <li><a href="Disposal.php" class="menu-item <?php echo $activePage === 'disposal' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <span>Disposal</span>
            </a></li>
            <li><a href="History.php" class="menu-item <?php echo $activePage === 'history' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>History</span>
            </a></li>
            <li><a href="profile.php" class="menu-item <?php echo $activePage === 'profile' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </a></li>
        </ul>
    </nav>

