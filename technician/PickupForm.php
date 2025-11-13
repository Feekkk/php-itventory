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
$equipment = null;
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
                
                // Fetch equipment details from inventory
                if (!empty($handover['equipment_id'])) {
                    // Check if inventory table has category_id column
                    $columns_check = $conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
                    $has_category_id = $columns_check && $columns_check->num_rows > 0;
                    
                    if ($has_category_id) {
                        // New structure: join with categories
                        $equipment_stmt = $conn->prepare("SELECT i.*, c.category_name as category FROM inventory i LEFT JOIN categories c ON i.category_id = c.id WHERE i.equipment_id = ?");
                    } else {
                        // Old structure: use category column
                        $equipment_stmt = $conn->prepare("SELECT * FROM inventory WHERE equipment_id = ?");
                    }
                    
                    $equipment_stmt->bind_param("s", $handover['equipment_id']);
                    $equipment_stmt->execute();
                    $equipment_result = $equipment_stmt->get_result();
                    
                    if ($equipment_result->num_rows === 1) {
                        $equipment = $equipment_result->fetch_assoc();
                    }
                    
                    $equipment_stmt->close();
                }
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
$additionalCSS = ['../css/PickupForm.css'];
$additionalJS = ['../js/PickupForm.js'];

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
    <?php elseif ($handover && $equipment): ?>
        <div class="handover-content">
            <!-- Equipment Details Section -->
            <div class="details-section">
                <div class="details-card equipment-card">
                    <div class="card-header">
                        <h2>Equipment Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Equipment ID</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Equipment Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Category</div>
                                <div class="detail-value">
                                    <?php
                                    $category_name = $equipment['category'] ?? 'N/A';
                                    $category_class = strtolower(str_replace([' ', '&', '/'], ['-', '', '-'], $category_name));
                                    ?>
                                    <span class="category-badge category-<?php echo htmlspecialchars($category_class); ?>">
                                        <?php echo htmlspecialchars($category_name); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Brand</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['brand'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Model</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['model'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Serial Number</div>
                                <div class="detail-value serial-number"><?php echo htmlspecialchars($equipment['serial_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['location'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php
                                    $status = $equipment['status'] ?? 'N/A';
                                    $status_class = strtolower(str_replace(' ', '-', $status));
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($equipment['description'])): ?>
                            <div class="description-section">
                                <div class="detail-label">Description</div>
                                <div class="detail-value description-text"><?php echo nl2br(htmlspecialchars($equipment['description'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Handover Details Section -->
                <div class="details-card handover-card">
                    <div class="card-header">
                        <h2>Handover Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Handover ID</div>
                                <div class="detail-value handover-id"><?php echo htmlspecialchars($handover['handoverID']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Lecturer ID</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Lecturer Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_email'] ?? 'N/A'); ?></div>
                            </div>
                            <?php if (!empty($handover['lecturer_phone'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_phone']); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <div class="detail-label">Pickup Date</div>
                                <div class="detail-value"><?php echo $handover['pickup_date'] ? date('d M Y', strtotime($handover['pickup_date'])) : 'N/A'; ?></div>
                            </div>
                            <?php if (!empty($handover['return_date'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Return Date</div>
                                    <div class="detail-value"><?php echo date('d M Y', strtotime($handover['return_date'])); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php
                                    $status_map = [
                                        'pending' => 'Pending',
                                        'picked_up' => 'Picked Up',
                                        'returned' => 'Returned'
                                    ];
                                    $display_status = $status_map[$handover['handoverStat']] ?? ucfirst($handover['handoverStat']);
                                    $status_class = strtolower(str_replace(' ', '-', $display_status));
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($display_status); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Agreement Section -->
            <div class="agreement-section">
                <div class="agreement-card">
                    <div class="card-header">
                        <h2>User Agreement</h2>
                    </div>
                    <div class="card-body">
                        <div class="agreement-content">
                            <p class="agreement-intro">By proceeding with the handover, the lecturer agrees to the following terms and conditions:</p>
                            
                            <div class="agreement-terms">
                                <div class="term-item">
                                    <div class="term-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="term-text">
                                        <strong>Responsible Care:</strong> The lecturer is responsible for taking proper care of the equipment and ensuring it is used in accordance with its intended purpose.
                                    </div>
                                </div>

                                <div class="term-item">
                                    <div class="term-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="term-text">
                                        <strong>Security:</strong> The lecturer must ensure the equipment is kept secure and protected from theft, damage, or unauthorized use.
                                    </div>
                                </div>

                                <div class="term-item">
                                    <div class="term-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="term-text">
                                        <strong>Maintenance:</strong> The lecturer must report any issues, damages, or malfunctions immediately to the IT department.
                                    </div>
                                </div>

                                <div class="term-item">
                                    <div class="term-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="term-text">
                                        <strong>Return Policy:</strong> The equipment must be returned in the same condition as received, with all accessories and documentation included.
                                    </div>
                                </div>

                                <div class="term-item">
                                    <div class="term-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="term-text">
                                        <strong>Liability:</strong> The lecturer will be held financially responsible for any loss, damage, or theft of the equipment while in their possession.
                                    </div>
                                </div>
                            </div>

                            <div class="agreement-checkbox">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text">I have read and agree to the terms and conditions stated above</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button Section -->
            <div class="action-section">
                <button type="button" class="btn-handover" id="handoverBtn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Confirm Handover</span>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

