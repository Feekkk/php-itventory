<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access this page.');
    header('Location: ../auth/login.php');
    exit();
}

$user = getUserData();
$handover = null;
$error = '';

// Get handover ID from URL
$handover_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($handover_id)) {
    $error = 'Handover ID is required.';
} else {
    try {
        $conn = getDBConnection();
        
        // Check if handover table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'handover'");
        if ($table_check && $table_check->num_rows > 0) {
            $stmt = $conn->prepare("SELECT * FROM handover WHERE handoverID = ?");
            $stmt->bind_param("i", $handover_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $handover = $result->fetch_assoc();
            } else {
                $error = 'Handover record not found.';
            }
            
            $stmt->close();
        } else {
            $error = 'Handover table does not exist.';
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Set active page and title for header component
$activePage = 'pickup';
$pageTitle = 'Handover Details';
$additionalCSS = ['../css/ViewItem.css']; // Use ViewItem.css for back-link styling
$additionalJS = [];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <a href="Pickup.php" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Back to Pickup</span>
                </a>
                <h1>Handover Details</h1>
                <?php if ($handover): ?>
                    <p class="page-subtitle">View and manage handover details</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($handover): ?>
        <!-- Form content will be added here -->
        <p>Handover ID: <?php echo htmlspecialchars($handover['handoverID']); ?></p>
        <!-- Form will be designed here -->
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

