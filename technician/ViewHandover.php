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

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Fetch handover records from database
$handovers = [];

try {
    $conn = getDBConnection();
    $table_check = $conn->query("SHOW TABLES LIKE 'handover'");
    
    if ($table_check && $table_check->num_rows > 0) {
        // Build WHERE clause
        $where = ["handoverStat IN ('picked_up', 'returned')"];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $where[] = "(equipment_id LIKE ? OR equipment_name LIKE ? OR lecturer_id LIKE ? OR lecturer_name LIKE ? OR lecturer_email LIKE ?)";
            $search_param = "%{$search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'sssss';
        }
        
        if (!empty($status_filter)) {
            // Map filter values to database values
            $status_map = [
                'Picked Up' => 'picked_up',
                'Returned' => 'returned'
            ];
            $db_status = $status_map[$status_filter] ?? $status_filter;
            $where[] = "handoverStat = ?";
            $params[] = $db_status;
            $types .= 's';
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        // Get handovers
        $sql = "SELECT * FROM handover $where_clause ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $handovers = $result->fetch_all(MYSQLI_ASSOC);
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
$pageTitle = 'Handover Records';
$additionalCSS = ['../css/Pickup.css'];
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
                <h1>Handover Records</h1>
                <p class="page-subtitle">View all handover records (picked up and returned)</p>
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
                    placeholder="Search by equipment ID, name, lecturer ID, lecturer name, or email..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="search-input"
                >
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
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
                    <a href="ViewHandover.php" class="clear-btn">
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
        <p class="results-count">
            <?php echo count($handovers); ?> handover record<?php echo count($handovers) !== 1 ? 's' : ''; ?> found
        </p>
    </div>

    <!-- Handovers Table -->
    <div class="handovers-list">
        <?php if (empty($handovers)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h3>No Handover Records</h3>
                <p>There are no handover records at this time.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="handover-table">
                    <thead>
                        <tr>
                            <th>Equipment ID</th>
                            <th>Equipment Name</th>
                            <th>Lecturer ID</th>
                            <th>Lecturer Name</th>
                            <th>Lecturer Email</th>
                            <th>Pickup Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Handover Staff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($handovers as $handover): ?>
                            <?php
                            // Map database status to display status
                            $status_map = [
                                'pending' => 'Pending',
                                'picked_up' => 'Picked Up',
                                'returned' => 'Returned'
                            ];
                            $display_status = $status_map[$handover['handoverStat']] ?? ucfirst($handover['handoverStat']);
                            $status_class = strtolower(str_replace(' ', '-', $display_status));
                            
                            // Fetch staff name if handoverStaff exists
                            $handoverStaffName = null;
                            if (!empty($handover['handoverStaff'])) {
                                try {
                                    $staff_conn = getDBConnection();
                                    $staff_stmt = $staff_conn->prepare("SELECT full_name FROM technician WHERE staff_id = ?");
                                    $staff_stmt->bind_param("s", $handover['handoverStaff']);
                                    $staff_stmt->execute();
                                    $staff_result = $staff_stmt->get_result();
                                    if ($staff_result->num_rows === 1) {
                                        $staff_row = $staff_result->fetch_assoc();
                                        $handoverStaffName = $staff_row['full_name'];
                                    }
                                    $staff_stmt->close();
                                    $staff_conn->close();
                                } catch (Exception $e) {
                                    // Handle error silently
                                }
                            }
                            ?>
                            <tr>
                                <td>
                                    <span class="equipment-id"><?php echo htmlspecialchars($handover['equipment_id']); ?></span>
                                </td>
                                <td>
                                    <span class="equipment-name"><?php echo htmlspecialchars($handover['equipment_name']); ?></span>
                                </td>
                                <td>
                                    <span><?php echo htmlspecialchars($handover['lecturer_id'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span><?php echo htmlspecialchars($handover['lecturer_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span><?php echo htmlspecialchars($handover['lecturer_email'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span><?php echo $handover['pickup_date'] ? date('d M Y', strtotime($handover['pickup_date'])) : 'N/A'; ?></span>
                                </td>
                                <td>
                                    <span><?php echo !empty($handover['return_date']) ? date('d M Y', strtotime($handover['return_date'])) : 'N/A'; ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($display_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <span>
                                        <?php 
                                        if (!empty($handoverStaffName)) {
                                            echo htmlspecialchars($handoverStaffName);
                                        } elseif (!empty($handover['handoverStaff'])) {
                                            echo htmlspecialchars($handover['handoverStaff']);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="PickupForm.php?id=<?php echo htmlspecialchars($handover['handoverID']); ?>" class="action-btn view-btn" title="View Details">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

