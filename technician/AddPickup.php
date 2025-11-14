<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access this page.');
    header('Location: ../auth/login.php');
    exit();
}

$error = '';
$success = '';
$user = getUserData();

// Fetch categories from database
$categories = [];
try {
    $conn = getDBConnection();
    
    // Check if categories table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($table_check && $table_check->num_rows > 0) {
        // Fetch categories from database
        $categories_result = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
        if ($categories_result) {
            while ($row = $categories_result->fetch_assoc()) {
                $categories[$row['id']] = $row['category_name'];
            }
        }
    } else {
        // Default categories if table doesn't exist
        $categories = [
            1 => 'Laptops',
            2 => 'Projectors',
            3 => 'Monitors',
            4 => 'Printers',
            5 => 'Tablets',
            6 => 'Accessories',
            7 => 'Cables & Adapters',
            8 => 'Networking',
            9 => 'Audio/Visual'
        ];
    }
    $conn->close();
} catch (Exception $e) {
    // If database error, use default categories
    $categories = [
        1 => 'Laptops',
        2 => 'Projectors',
        3 => 'Monitors',
        4 => 'Printers',
        5 => 'Tablets',
        6 => 'Accessories',
        7 => 'Cables & Adapters',
        8 => 'Networking',
        9 => 'Audio/Visual'
    ];
}

// Fetch available equipment from database with category information
$equipment_items = [];
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
            // Only show equipment WITHOUT staff_name (available for handover)
            $equipment_sql = "SELECT e.equipment_id, e.equipment_name, e.category_id, c.category_name 
                             FROM equipment e 
                             INNER JOIN inventory i ON e.equipment_id = i.equipment_id
                             LEFT JOIN categories c ON e.category_id = c.id 
                             WHERE (e.staff_name IS NULL OR e.staff_name = '')
                             ORDER BY c.category_name ASC, e.equipment_name ASC";
        } else {
            // Old structure: Check if inventory table has category_id column
            $columns_check = $conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
            $has_category_id = $columns_check && $columns_check->num_rows > 0;
            
            if ($has_category_id) {
                // New structure without equipment table: join with categories
                $equipment_sql = "SELECT i.equipment_id, i.equipment_name, i.category_id, c.category_name 
                                 FROM inventory i 
                                 LEFT JOIN categories c ON i.category_id = c.id 
                                 WHERE i.status = 'Available' 
                                 ORDER BY c.category_name ASC, i.equipment_name ASC";
            } else {
                // Old structure: try to join by category name, or use NULL category_id
                $equipment_sql = "SELECT i.equipment_id, i.equipment_name, c.id as category_id, i.category as category_name
                                 FROM inventory i
                                 LEFT JOIN categories c ON i.category = c.category_name
                                 WHERE i.status = 'Available'
                                 ORDER BY i.category ASC, i.equipment_name ASC";
            }
        }
        
        $equipment_result = $conn->query($equipment_sql);
        if ($equipment_result) {
            while ($row = $equipment_result->fetch_assoc()) {
                $equipment_items[] = $row;
            }
        }
    }
    $conn->close();
} catch (Exception $e) {
    // Handle error silently
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = trim($_POST['equipment_id'] ?? '');
    $lecturer_id = trim($_POST['lecturer_id'] ?? '');
    $lecturer_name = trim($_POST['lecturer_name'] ?? '');
    $lecturer_email = trim($_POST['lecturer_email'] ?? '');
    $pickup_date = trim($_POST['pickup_date'] ?? date('Y-m-d')); // Default to today if not provided
    $return_date = trim($_POST['return_date'] ?? '');
    
    // Validation
    if (empty($equipment_id)) {
        $error = 'Equipment selection is required.';
    } elseif (empty($lecturer_id)) {
        $error = 'Lecturer ID is required.';
    } elseif (empty($lecturer_name)) {
        $error = 'Lecturer name is required.';
    } elseif (empty($lecturer_email)) {
        $error = 'Lecturer email is required.';
    } elseif (!filter_var($lecturer_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if equipment table exists
            $equipment_table_check = $conn->query("SHOW TABLES LIKE 'equipment'");
            $has_equipment_table = $equipment_table_check && $equipment_table_check->num_rows > 0;
            
            if ($has_equipment_table) {
                // New structure: Check equipment table for staff_name
                $equipment_stmt = $conn->prepare("SELECT e.equipment_name, e.staff_name FROM equipment e WHERE e.equipment_id = ?");
                $equipment_stmt->bind_param("s", $equipment_id);
                $equipment_stmt->execute();
                $equipment_result = $equipment_stmt->get_result();
                
                if ($equipment_result->num_rows === 0) {
                    $error = 'Selected equipment not found.';
                    $equipment_stmt->close();
                    $conn->close();
                } else {
                    $equipment_row = $equipment_result->fetch_assoc();
                    $equipment_name = $equipment_row['equipment_name'];
                    $staff_name_existing = $equipment_row['staff_name'] ?? '';
                    $equipment_stmt->close();
                    
                    // Check if equipment is available (no staff_name means available)
                    if (!empty($staff_name_existing)) {
                        $error = 'Equipment is already assigned to: ' . htmlspecialchars($staff_name_existing) . '. Cannot create new handover.';
                        $conn->close();
                    } else {
            } else {
                // Old structure: Get equipment name and status from inventory
                $equipment_stmt = $conn->prepare("SELECT equipment_name, status FROM inventory WHERE equipment_id = ?");
                $equipment_stmt->bind_param("s", $equipment_id);
                $equipment_stmt->execute();
                $equipment_result = $equipment_stmt->get_result();
                
                if ($equipment_result->num_rows === 0) {
                    $error = 'Selected equipment not found.';
                    $equipment_stmt->close();
                    $conn->close();
                } else {
                    $equipment_row = $equipment_result->fetch_assoc();
                    $equipment_name = $equipment_row['equipment_name'];
                    $equipment_status = $equipment_row['status'];
                    $equipment_stmt->close();
                    
                    // Check if equipment is available
                    if ($equipment_status !== 'Available') {
                        $error = 'Equipment is not available for handover. Current status: ' . htmlspecialchars($equipment_status);
                        $conn->close();
                    } else {
            }
            
            if (empty($error)) {
                    // Check if handover table exists, create if not
                    $table_check = $conn->query("SHOW TABLES LIKE 'handover'");
                    if (!$table_check || $table_check->num_rows === 0) {
                        $create_table_sql = "CREATE TABLE IF NOT EXISTS handover (
                            handoverID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            equipment_id VARCHAR(50) NOT NULL,
                            equipment_name VARCHAR(255) NOT NULL,
                            lecturer_id VARCHAR(50) NOT NULL,
                            lecturer_name VARCHAR(255) NOT NULL,
                            lecturer_email VARCHAR(255) NOT NULL,
                            pickup_date DATE NOT NULL,
                            return_date DATE,
                            handoverStat VARCHAR(50) DEFAULT 'pending',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            INDEX idx_equipment_id (equipment_id),
                            INDEX idx_lecturer_id (lecturer_id),
                            INDEX idx_handoverStat (handoverStat),
                            INDEX idx_pickup_date (pickup_date),
                            FOREIGN KEY (equipment_id) REFERENCES inventory(equipment_id) ON DELETE RESTRICT ON UPDATE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                        
                        if (!$conn->query($create_table_sql)) {
                            throw new Exception("Error creating handover table: " . $conn->error);
                        }
                    } else {
                        // Table exists, check if lecturer_id column exists, add it if not
                        $column_check = $conn->query("SHOW COLUMNS FROM handover LIKE 'lecturer_id'");
                        if (!$column_check || $column_check->num_rows === 0) {
                            $alter_table_sql = "ALTER TABLE handover ADD COLUMN lecturer_id VARCHAR(50) NOT NULL DEFAULT '' AFTER equipment_name";
                            if ($conn->query($alter_table_sql)) {
                                // Add index if column was successfully added
                                $index_check = $conn->query("SHOW INDEX FROM handover WHERE Key_name = 'idx_lecturer_id'");
                                if (!$index_check || $index_check->num_rows === 0) {
                                    $index_sql = "ALTER TABLE handover ADD INDEX idx_lecturer_id (lecturer_id)";
                                    $conn->query($index_sql);
                                }
                            }
                        }
                    }
                    
                    // Start transaction to ensure all operations succeed or fail together
                    $conn->autocommit(FALSE);
                    
                    try {
                        if ($has_equipment_table) {
                            // New structure: Update equipment table with handover information (merged handover table)
                            $return_date_param = empty($return_date) ? NULL : $return_date;
                            
                            $update_equipment_stmt = $conn->prepare("UPDATE equipment SET 
                                staff_id = ?, 
                                staff_name = ?, 
                                staff_email = ?, 
                                pickup_date = ?, 
                                return_date = ?, 
                                handover_status = 'pending'
                                WHERE equipment_id = ?");
                            $update_equipment_stmt->bind_param("ssssss", 
                                $lecturer_id, 
                                $lecturer_name, 
                                $lecturer_email, 
                                $pickup_date, 
                                $return_date_param, 
                                $equipment_id);
                            
                            if (!$update_equipment_stmt->execute()) {
                                throw new Exception("Failed to update equipment handover information: " . $update_equipment_stmt->error);
                            }
                            $update_equipment_stmt->close();
                        } else {
                            // Old structure: Insert into handover table (backward compatibility)
                            if (empty($return_date)) {
                                $insert_stmt = $conn->prepare("INSERT INTO handover (equipment_id, equipment_name, lecturer_id, lecturer_name, lecturer_email, pickup_date, handoverStat) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                                $insert_stmt->bind_param("ssssss", $equipment_id, $equipment_name, $lecturer_id, $lecturer_name, $lecturer_email, $pickup_date);
                            } else {
                                $insert_stmt = $conn->prepare("INSERT INTO handover (equipment_id, equipment_name, lecturer_id, lecturer_name, lecturer_email, pickup_date, return_date, handoverStat) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                                $insert_stmt->bind_param("sssssss", $equipment_id, $equipment_name, $lecturer_id, $lecturer_name, $lecturer_email, $pickup_date, $return_date);
                            }
                            
                            if (!$insert_stmt->execute()) {
                                throw new Exception("Failed to insert handover record: " . $insert_stmt->error);
                            }
                            $insert_stmt->close();
                        }
                        
                        // Update inventory status to "hand over" (for backward compatibility)
                        $update_stmt = $conn->prepare("UPDATE inventory SET status = 'hand over' WHERE equipment_id = ?");
                        $update_stmt->bind_param("s", $equipment_id);
                        
                        if (!$update_stmt->execute()) {
                            throw new Exception("Failed to update inventory status: " . $update_stmt->error);
                        }
                        $update_stmt->close();
                        
                        // Commit transaction
                        $conn->commit();
                        $conn->autocommit(TRUE);
                        
                        setSessionMessage('success', 'Handover request added successfully! Equipment assigned to ' . htmlspecialchars($lecturer_name) . '.');
                        $conn->close();
                        header('Location: Pickup.php');
                        exit();
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $conn->rollback();
                        $conn->autocommit(TRUE);
                        $error = 'Failed to add handover request: ' . $e->getMessage();
                        if (isset($insert_stmt)) {
                            $insert_stmt->close();
                        }
                        if (isset($update_equipment_stmt)) {
                            $update_equipment_stmt->close();
                        }
                        if (isset($update_stmt)) {
                            $update_stmt->close();
                        }
                        $conn->close();
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
            if (isset($conn)) {
                $conn->close();
            }
        }
    }
}

// Get any session messages
$message = getSessionMessage();
if ($message) {
    if ($message['type'] === 'success') {
        $success = $message['content'];
    } else {
        $error = $message['content'];
    }
}

// Set active page and title for header component
$activePage = 'pickup';
$pageTitle = 'Add Pickup Request';
$additionalCSS = ['../css/AddPickup.css'];
$additionalJS = ['../js/AddPickup.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Add New Pickup Request</h1>
                <p class="page-subtitle">Register a new equipment pickup request for a lecturer</p>
            </div>
            <a href="Pickup.php" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span>Back to Pickups</span>
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

    <div class="content-layout">
        <div class="form-wrapper">
        <form id="addPickupForm" method="POST" class="pickup-form">
            <div class="form-section">
                <h2 class="section-title">Equipment Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="category_filter">Category Filter</label>
                        <select id="category_filter" name="category_filter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat_id => $cat_name): ?>
                                <option value="<?php echo htmlspecialchars($cat_id); ?>" <?php echo (isset($_POST['category_filter']) && $_POST['category_filter'] == $cat_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Filter equipment by category</small>
                    </div>

                    <div class="form-group">
                        <label for="equipment_id">Equipment <span class="required">*</span></label>
                        <select id="equipment_id" name="equipment_id" required>
                            <option value="">Select Equipment</option>
                            <?php foreach ($equipment_items as $item): ?>
                                <?php 
                                $category_id = $item['category_id'] ?? null;
                                $category_name = $item['category_name'] ?? 'Uncategorized';
                                $data_category = $category_id ? 'data-category="' . htmlspecialchars($category_id) . '"' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($item['equipment_id']); ?>" 
                                        <?php echo $data_category; ?>
                                        <?php echo (isset($_POST['equipment_id']) && $_POST['equipment_id'] == $item['equipment_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($item['equipment_name'] . ' (' . $item['equipment_id'] . ') - ' . $category_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Select the equipment to be picked up</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Lecturer Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="lecturer_id">Lecturer ID <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="lecturer_id" 
                            name="lecturer_id" 
                            value="<?php echo htmlspecialchars($_POST['lecturer_id'] ?? ''); ?>"
                            placeholder="e.g., LEC001"
                            required
                        >
                        <small>Unique identifier for the lecturer</small>
                    </div>

                    <div class="form-group">
                        <label for="lecturer_name">Lecturer Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="lecturer_name" 
                            name="lecturer_name" 
                            value="<?php echo htmlspecialchars($_POST['lecturer_name'] ?? ''); ?>"
                            placeholder="e.g., Dr. John Smith"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="lecturer_email">Lecturer Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="lecturer_email" 
                            name="lecturer_email" 
                            value="<?php echo htmlspecialchars($_POST['lecturer_email'] ?? ''); ?>"
                            placeholder="e.g., john.smith@university.edu"
                            required
                        >
                    </div>

                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Add Pickup Request</span>
                </button>
                <a href="Pickup.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
        </div>

        <div class="notice-panel">
            <div class="notice-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <h3>Important Notice</h3>
            </div>
            <div class="notice-content">
                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Equipment Selection</strong>
                        <p>Only equipment with "Available" status can be selected for pickup.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Category Filter</strong>
                        <p>Use the category filter to quickly find equipment when the list is large.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Required Fields</strong>
                        <p>Equipment, Lecturer ID, Lecturer Name, and Email are mandatory fields.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Status</strong>
                        <p>New pickup requests will be created with "Pending" status by default.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

