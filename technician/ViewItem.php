<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access this page.');
    header('Location: ../auth/login.php');
    exit();
}

// Handle AJAX status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    $equipment_id = isset($_POST['equipment_id']) ? trim($_POST['equipment_id']) : '';
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    if (empty($equipment_id) || empty($new_status)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $valid_statuses = ['Available', 'Maintenance', 'Reserved'];
    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    try {
        $conn = getDBConnection();
        
        // Check current status before allowing update
        $check_stmt = $conn->prepare("SELECT status FROM inventory WHERE equipment_id = ?");
        $check_stmt->bind_param("s", $equipment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $check_stmt->close();
            $conn->close();
            echo json_encode(['success' => false, 'message' => 'Equipment not found']);
            exit();
        }
        
        $current_status = $check_result->fetch_assoc()['status'];
        $check_stmt->close();
        
        // Prevent status change if current status is "hand over"
        if ($current_status === 'hand over') {
            $conn->close();
            echo json_encode(['success' => false, 'message' => 'Cannot change status. Equipment is currently in handover and must be returned first.']);
            exit();
        }
        
        $update_stmt = $conn->prepare("UPDATE inventory SET status = ? WHERE equipment_id = ?");
        $update_stmt->bind_param("ss", $new_status, $equipment_id);
        
        if ($update_stmt->execute()) {
            $update_stmt->close();
            $conn->close();
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            $update_stmt->close();
            $conn->close();
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->close();
        }
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

$user = getUserData();
$error = '';
$item = null;

// Get equipment ID from URL
$equipment_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($equipment_id)) {
    $error = 'Equipment ID is required.';
} else {
    try {
        $conn = getDBConnection();
        
        // Check if equipment table exists (new structure)
        $equipment_table_check = $conn->query("SHOW TABLES LIKE 'equipment'");
        $has_equipment_table = $equipment_table_check && $equipment_table_check->num_rows > 0;
        
        // Check if inventory table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'inventory'");
        if ($table_check && $table_check->num_rows > 0) {
            if ($has_equipment_table) {
                // New structure: Join equipment with inventory and categories
                $stmt = $conn->prepare("SELECT 
                    e.*, 
                    i.status, 
                    i.location, 
                    i.created_at as inventory_created_at,
                    i.updated_at as inventory_updated_at,
                    c.category_name as category 
                    FROM equipment e 
                    INNER JOIN inventory i ON e.equipment_id = i.equipment_id 
                    LEFT JOIN categories c ON e.category_id = c.id 
                    WHERE e.equipment_id = ?");
            } else {
                // Old structure: Check if inventory table has category_id column
                $columns_check = $conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
                $has_category_id = $columns_check && $columns_check->num_rows > 0;
                
                if ($has_category_id) {
                    // New structure without equipment table: join with categories
                    $stmt = $conn->prepare("SELECT i.*, c.category_name as category FROM inventory i LEFT JOIN categories c ON i.category_id = c.id WHERE i.equipment_id = ?");
                } else {
                    // Old structure: use category column
                    $stmt = $conn->prepare("SELECT * FROM inventory WHERE equipment_id = ?");
                }
            }
            
            $stmt->bind_param("s", $equipment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $item = $result->fetch_assoc();
            } else {
                $error = 'Equipment not found.';
            }
            
            $stmt->close();
        } else {
            $error = 'Inventory table does not exist.';
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Set active page and title for header component
$activePage = 'inventory';
$pageTitle = 'Equipment Details';
$additionalCSS = ['../css/ViewItem.css'];
$additionalJS = ['../js/ViewItem.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <a href="ListInventory.php" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Back to Inventory</span>
                </a>
                <h1>Equipment Details</h1>
                <?php if ($item): ?>
                    <p class="page-subtitle">View complete information for <?php echo htmlspecialchars($item['equipment_id'] ?? 'N/A'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($item): ?>
        <div class="item-details-wrapper">
            <!-- Main Details Card -->
            <div class="details-card main-card">
                <div class="card-header">
                    <div class="equipment-header">
                        <div>
                            <h2><?php echo htmlspecialchars($item['equipment_name'] ?? 'N/A'); ?></h2>
                            <p class="equipment-id">ID: <?php echo htmlspecialchars($item['equipment_id'] ?? 'N/A'); ?></p>
                        </div>
                        <?php
                        $current_status = $item['status'] ?? 'unknown';
                        $status_class = strtolower(str_replace(' ', '-', $current_status));
                        // Handle "hand over" status display
                        $display_status = ($current_status === 'hand over') ? 'Hand Over' : $current_status;
                        ?>
                        <span class="status-badge status-<?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($display_status); ?>
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Category</div>
                            <div class="detail-value">
                                <?php
                                $category_name = $item['category'] ?? 'N/A';
                                $category_class = strtolower(str_replace([' ', '&', '/'], ['-', '', '-'], $category_name));
                                ?>
                                <span class="category-badge category-<?php echo htmlspecialchars($category_class); ?>">
                                    <?php echo htmlspecialchars($category_name); ?>
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Brand</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Model</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['model'] ?? 'N/A'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Serial Number</div>
                            <div class="detail-value serial-number"><?php echo htmlspecialchars($item['serial_number'] ?? 'N/A'); ?></div>
                        </div>

                        <?php if (!empty($item['processor'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Processor</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['processor']); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($item['operating_system'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Operating System</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['operating_system']); ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <?php 
                                $detail_status = $item['status'] ?? '';
                                $is_handover = ($detail_status === 'hand over');
                                $detail_status_class = strtolower(str_replace(' ', '-', $detail_status));
                                ?>
                                <?php if ($is_handover): ?>
                                    <div class="status-readonly">
                                        <span class="status-badge status-hand-over">Hand Over</span>
                                        <small class="status-note">
                                            Status cannot be changed. Equipment must be returned first through the handover system.
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <select class="status-dropdown status-<?php echo $detail_status_class; ?>" data-equipment-id="<?php echo htmlspecialchars($item['equipment_id'] ?? ''); ?>">
                                        <option value="Available" <?php echo $detail_status === 'Available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="Maintenance" <?php echo $detail_status === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="Reserved" <?php echo $detail_status === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="details-card info-card">
                <div class="card-header">
                    <h3>Additional Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Created</div>
                            <div class="info-value">
                                <?php 
                                $created_at = $item['created_at'] ?? $item['inventory_created_at'] ?? null;
                                echo $created_at ? date('d M Y, h:i A', strtotime($created_at)) : 'N/A'; 
                                ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                <?php 
                                $updated_at = $item['updated_at'] ?? $item['inventory_updated_at'] ?? null;
                                echo $updated_at ? date('d M Y, h:i A', strtotime($updated_at)) : 'N/A'; 
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($item['staff_name'])): ?>
                        <div class="info-item">
                            <div class="info-label">Assigned To</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($item['staff_name']); ?>
                                <?php if (!empty($item['staff_id'])): ?>
                                    <small>(<?php echo htmlspecialchars($item['staff_id']); ?>)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($item['handover_status'])): ?>
                        <div class="info-item">
                            <div class="info-label">Handover Status</div>
                            <div class="info-value">
                                <?php 
                                $handover_status = $item['handover_status'];
                                $status_map = [
                                    'pending' => 'Pending',
                                    'picked_up' => 'Picked Up',
                                    'returned' => 'Returned'
                                ];
                                echo htmlspecialchars($status_map[$handover_status] ?? ucfirst($handover_status)); 
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Note Section -->
        <div class="note-section">
            <div class="note-content">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <p><strong>Note:</strong> Technicians can only change the status of equipment items. All other information is read-only. Equipment with "Hand Over" status cannot have its status changed until it is returned.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

