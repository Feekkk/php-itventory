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
$error = '';
$item = null;

// Get equipment ID from URL
$equipment_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($equipment_id)) {
    $error = 'Equipment ID is required.';
} else {
    try {
        $conn = getDBConnection();
        
        // Check if inventory table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'inventory'");
        if ($table_check && $table_check->num_rows > 0) {
            // Check if inventory table has category_id column
            $columns_check = $conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
            $has_category_id = $columns_check && $columns_check->num_rows > 0;
            
            if ($has_category_id) {
                // New structure: join with categories
                $stmt = $conn->prepare("SELECT i.*, c.category_name as category FROM inventory i LEFT JOIN categories c ON i.category_id = c.id WHERE i.equipment_id = ?");
            } else {
                // Old structure: use category column
                $stmt = $conn->prepare("SELECT * FROM inventory WHERE equipment_id = ?");
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
                        $status_class = strtolower(str_replace(' ', '-', $item['status'] ?? 'unknown'));
                        ?>
                        <span class="status-badge status-<?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($item['status'] ?? 'N/A'); ?>
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

                        <div class="detail-item">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <select class="status-dropdown status-<?php echo $status_class; ?>" data-equipment-id="<?php echo htmlspecialchars($item['equipment_id'] ?? ''); ?>">
                                    <option value="Available" <?php echo ($item['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="In Use" <?php echo ($item['status'] ?? '') === 'In Use' ? 'selected' : ''; ?>>In Use</option>
                                    <option value="Maintenance" <?php echo ($item['status'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Reserved" <?php echo ($item['status'] ?? '') === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($item['description'])): ?>
                        <div class="description-section">
                            <div class="detail-label">Description</div>
                            <div class="detail-value description-text"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
                        </div>
                    <?php endif; ?>
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
                                <?php echo $item['created_at'] ? date('d M Y, h:i A', strtotime($item['created_at'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                <?php echo $item['updated_at'] ? date('d M Y, h:i A', strtotime($item['updated_at'])) : 'N/A'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

