<?php
/**
 * Technician Dashboard
 * Requires authentication
 */

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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: #f4f6f8;
            color: #1e1e1e;
        }

        .header {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e1e1e;
        }

        .header .logo img {
            width: 60px;
            height: auto;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-name {
            font-weight: 600;
            color: #1e1e1e;
        }

        .logout-btn {
            padding: 0.5rem 1.5rem;
            background: #2dc48d;
            color: #0b121b;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease;
        }

        .logout-btn:hover {
            background: #1fa372;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, #0b121b 0%, #1a2838 100%);
            color: #ffffff;
            padding: 3rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .welcome-card h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1e1e1e;
        }

        .card p {
            color: #6b7280;
            line-height: 1.6;
        }
    </style>
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

