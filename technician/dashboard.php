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

// Set active page and title for header component
$activePage = 'dashboard';
$pageTitle = 'Dashboard';

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!</h1>
                <p>Manage your IT equipment inventory and reservations</p>
            </div>
            <div class="welcome-info">
                <div class="info-item">
                    <span class="info-label">Staff ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['staff_id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="dashboard-grid">
            <a href="ListInventory.php" class="dashboard-card">
                <div class="card-icon inventory">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="11" rx="1.5"></rect>
                        <path d="M4 17h16l1 2H3l1-2z"></path>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Equipment Inventory</h3>
                    <p>Browse and manage all IT equipment available for reservation</p>
                </div>
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="Pickup.php" class="dashboard-card">
                <div class="card-icon pickup">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16v11H4z"></path>
                        <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                        <path d="M7 11h10"></path>
                        <path d="M10 14h4"></path>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Pickup Equipment</h3>
                    <p>Request and manage equipment pickup requests</p>
                </div>
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="Disposal.php" class="dashboard-card">
                <div class="card-icon disposal">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Equipment Disposal</h3>
                    <p>Submit and track equipment disposal requests</p>
                </div>
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="History.php" class="dashboard-card">
                <div class="card-icon history">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>History</h3>
                    <p>View your equipment reservation and transaction history</p>
                </div>
                <div class="card-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>
        </div>
    </div>
    <?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>