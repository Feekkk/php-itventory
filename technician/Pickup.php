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
    'overdue' => 0
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
            SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned,
            SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as overdue
            FROM pickups";
        
        $stats_result = $conn->query($stats_sql);
        if ($stats_result) {
            $stats_row = $stats_result->fetch_assoc();
            $stats['total'] = $stats_row['total'] ?? 0;
            $stats['pending'] = $stats_row['pending'] ?? 0;
            $stats['picked_up'] = $stats_row['picked_up'] ?? 0;
            $stats['returned'] = $stats_row['returned'] ?? 0;
            $stats['overdue'] = $stats_row['overdue'] ?? 0;
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

        <div class="stat-card">
            <div class="stat-icon overdue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Overdue</div>
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
                    <option value="Overdue" <?php echo $status_filter === 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
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

    <!-- Results Summary -->
    <div class="results-summary">
        <span class="results-count"><?php echo count($pickups); ?> record<?php echo count($pickups) !== 1 ? 's' : ''; ?> found</span>
    </div>

    <!-- Pickups Table -->
    <div class="table-container">
        <?php if (empty($pickups)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 7h16v11H4z"></path>
                    <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                </svg>
                <h3>No pickup records found</h3>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
        <?php else: ?>
            <table class="pickups-table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Lecturer</th>
                        <th>Pickup Date</th>
                        <th>Expected Return</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pickups as $pickup): ?>
                        <tr>
                            <td>
                                <div class="equipment-info">
                                    <strong class="equipment-id"><?php echo htmlspecialchars($pickup['equipment_id']); ?></strong>
                                    <span class="equipment-name"><?php echo htmlspecialchars($pickup['equipment_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="lecturer-info">
                                    <strong><?php echo htmlspecialchars($pickup['lecturer_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($pickup['lecturer_email']); ?></span>
                                    <?php if (!empty($pickup['lecturer_phone'])): ?>
                                        <span class="lecturer-phone"><?php echo htmlspecialchars($pickup['lecturer_phone']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="date-value"><?php echo date('d M Y', strtotime($pickup['pickup_date'])); ?></span>
                            </td>
                            <td>
                                <?php if ($pickup['expected_return_date']): ?>
                                    <span class="date-value"><?php echo date('d M Y', strtotime($pickup['expected_return_date'])); ?></span>
                                <?php else: ?>
                                    <span class="date-value text-muted">Not set</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_class = strtolower(str_replace(' ', '-', $pickup['status'] ?? 'pending'));
                                ?>
                                <span class="status-badge status-<?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($pickup['status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="date-value text-muted"><?php echo date('d M Y', strtotime($pickup['created_at'])); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view-btn" title="View Details">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    <?php if ($pickup['status'] === 'Picked Up' || $pickup['status'] === 'Pending'): ?>
                                        <button class="action-btn return-btn" title="Mark as Returned">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 18l6-6-6-6"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

