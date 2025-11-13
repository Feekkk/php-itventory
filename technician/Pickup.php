<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access the pickup page.');
    header('Location: ../auth/login.php');
    exit();
}

$user = getUserData();

// Get filter parameters
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch pickups from database
$pickups = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'picked_up' => 0,
    'returned' => 0,
];

try {
    $conn = getDBConnection();
    $table_check = $conn->query("SHOW TABLES LIKE 'pickups'");
    
    if ($table_check && $table_check->num_rows > 0) {
        // Build WHERE clause
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $where[] = "(equipment_id LIKE ? OR equipment_name LIKE ? OR lecturer_name LIKE ? OR lecturer_email LIKE ?)";
            $search_param = "%{$search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'ssss';
        }
        
        if (!empty($status_filter)) {
            $where[] = "status = ?";
            $params[] = $status_filter;
            $types .= 's';
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get statistics
        $stats_sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Picked Up' THEN 1 ELSE 0 END) as picked_up,
            SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned
            FROM pickups";
        
        $stats_result = $conn->query($stats_sql);
        if ($stats_result) {
            $stats_row = $stats_result->fetch_assoc();
            $stats['total'] = $stats_row['total'] ?? 0;
            $stats['pending'] = $stats_row['pending'] ?? 0;
            $stats['picked_up'] = $stats_row['picked_up'] ?? 0;
            $stats['returned'] = $stats_row['returned'] ?? 0;
        }
        
        // Get pickups
        $sql = "SELECT * FROM pickups $where_clause ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $pickups = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    $conn->close();
} catch (Exception $e) {
    // Handle error silently
}

// Get session messages
$message = getSessionMessage();
$error = '';
$success = '';
if ($message) {
    if ($message['type'] === 'success') {
        $success = $message['content'];
    } else {
        $error = $message['content'];
    }
}

// Set active page and title for header component
$activePage = 'pickup';
$pageTitle = 'Equipment Pickup';
$additionalCSS = ['../css/Pickup.css'];
$additionalJS = ['../js/Pickup.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Equipment Pickup Management</h1>
                <p class="page-subtitle">View and manage all equipment pickup records</p>
            </div>
            <a href="AddPickup.php" class="add-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Pickup</span>
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 7h16v11H4z"></path>
                    <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Pickups</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon pending">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon picked-up">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['picked_up']; ?></div>
                <div class="stat-label">Picked Up</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon returned">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['returned']; ?></div>
                <div class="stat-label">Returned</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by equipment ID, name, lecturer name, or email..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="search-input"
                >
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Picked Up" <?php echo $status_filter === 'Picked Up' ? 'selected' : ''; ?>>Picked Up</option>
                    <option value="Returned" <?php echo $status_filter === 'Returned' ? 'selected' : ''; ?>>Returned</option>
                </select>

                <button type="submit" class="filter-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Apply Filters
                </button>

                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="Pickup.php" class="clear-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pickups Display - Two Vertical Boxes -->
    <div class="pickups-layout">
        <!-- Pending Box -->
        <div class="pickup-box pending-box">
            <div class="box-header">
                <div class="box-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h2>Pending</h2>
                    <span class="box-count"><?php 
                        $pending_count = count(array_filter($pickups, function($p) { 
                            return ($p['status'] ?? '') === 'Pending'; 
                        })); 
                        echo $pending_count; 
                    ?></span>
                </div>
            </div>
            <div class="box-content">
                <?php 
                $pending_pickups = array_filter($pickups, function($p) { 
                    return ($p['status'] ?? '') === 'Pending'; 
                }); 
                ?>
                <?php if (empty($pending_pickups)): ?>
                    <div class="empty-box-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <p>No pending pickups</p>
                    </div>
                <?php else: ?>
                    <div class="pickup-cards">
                        <?php foreach ($pending_pickups as $pickup): ?>
                            <div class="pickup-card">
                                <div class="card-header">
                                    <div class="equipment-info">
                                        <strong class="equipment-id"><?php echo htmlspecialchars($pickup['equipment_id']); ?></strong>
                                        <span class="equipment-name"><?php echo htmlspecialchars($pickup['equipment_name']); ?></span>
                                    </div>
                                    <span class="status-badge status-pending">Pending</span>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="info-label">Lecturer:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_name']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_email']); ?></span>
                                    </div>
                                    <?php if (!empty($pickup['lecturer_phone'])): ?>
                                        <div class="info-row">
                                            <span class="info-label">Phone:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_phone']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="info-row">
                                        <span class="info-label">Pickup Date:</span>
                                        <span class="info-value"><?php echo date('d M Y', strtotime($pickup['pickup_date'])); ?></span>
                                    </div>
                                    <?php if ($pickup['expected_return_date']): ?>
                                        <div class="info-row">
                                            <span class="info-label">Return Date:</span>
                                            <span class="info-value"><?php echo date('d M Y', strtotime($pickup['expected_return_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn view-btn" title="View Details">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    <button class="action-btn handover-btn" title="Hand Over">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hand Over Box -->
        <div class="pickup-box handover-box">
            <div class="box-header">
                <div class="box-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <h2>Hand Over</h2>
                    <span class="box-count"><?php 
                        $handover_count = count(array_filter($pickups, function($p) { 
                            return in_array($p['status'] ?? '', ['Picked Up', 'Returned']); 
                        })); 
                        echo $handover_count; 
                    ?></span>
                </div>
            </div>
            <div class="box-content">
                <?php 
                $handover_pickups = array_filter($pickups, function($p) { 
                    return in_array($p['status'] ?? '', ['Picked Up', 'Returned']); 
                }); 
                ?>
                <?php if (empty($handover_pickups)): ?>
                    <div class="empty-box-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <p>No handover records</p>
                    </div>
                <?php else: ?>
                    <div class="pickup-cards">
                        <?php foreach ($handover_pickups as $pickup): ?>
                            <?php
                            $status_class = strtolower(str_replace(' ', '-', $pickup['status'] ?? 'pending'));
                            ?>
                            <div class="pickup-card">
                                <div class="card-header">
                                    <div class="equipment-info">
                                        <strong class="equipment-id"><?php echo htmlspecialchars($pickup['equipment_id']); ?></strong>
                                        <span class="equipment-name"><?php echo htmlspecialchars($pickup['equipment_name']); ?></span>
                                    </div>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($pickup['status'] ?? 'Pending'); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <span class="info-label">Lecturer:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_name']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_email']); ?></span>
                                    </div>
                                    <?php if (!empty($pickup['lecturer_phone'])): ?>
                                        <div class="info-row">
                                            <span class="info-label">Phone:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($pickup['lecturer_phone']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="info-row">
                                        <span class="info-label">Pickup Date:</span>
                                        <span class="info-value"><?php echo date('d M Y', strtotime($pickup['pickup_date'])); ?></span>
                                    </div>
                                    <?php if ($pickup['expected_return_date']): ?>
                                        <div class="info-row">
                                            <span class="info-label">Return Date:</span>
                                            <span class="info-value"><?php echo date('d M Y', strtotime($pickup['expected_return_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($pickup['actual_return_date']): ?>
                                        <div class="info-row">
                                            <span class="info-label">Returned:</span>
                                            <span class="info-value"><?php echo date('d M Y', strtotime($pickup['actual_return_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button class="action-btn view-btn" title="View Details">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    <?php if ($pickup['status'] === 'Picked Up'): ?>
                                        <button class="action-btn return-btn" title="Mark as Returned">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 18l6-6-6-6"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

