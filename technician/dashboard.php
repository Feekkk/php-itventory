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