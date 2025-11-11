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
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="../public/unikl-rcmp.png" alt="RCMP Logo">
            <span>RCMP-itventory</span>
        </div>
        <div class="user-info">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

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

