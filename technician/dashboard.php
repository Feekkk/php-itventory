<?php
require_once __DIR__ . '/../config/database.php';
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
        <div class="dashboard-layout">
            <div class="dashboard-main">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!</h1>
                        <p>Manage your IT equipment inventory and reservations</p>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="quick-actions-section">
                    <h2 class="section-title">Quick Actions</h2>
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
            </div>

            <!-- Sticky User Info Card -->
            <div class="user-info-card">
                <div class="user-info-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h3>User Information</h3>
                </div>
                <div class="user-info-body">
                    <div class="user-info-item">
                        <div class="user-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="user-info-content">
                            <strong>Full Name</strong>
                            <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                        </div>
                    </div>
                    <div class="user-info-item">
                        <div class="user-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div class="user-info-content">
                            <strong>Staff ID</strong>
                            <p><?php echo htmlspecialchars($user['staff_id']); ?></p>
                        </div>
                    </div>
                    <div class="user-info-item">
                        <div class="user-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <div class="user-info-content">
                            <strong>Email</strong>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>