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

// Statuses
$statuses = ['Available', 'In Use', 'Maintenance', 'Reserved'];

// Fetch categories from database
$categories = [];
try {
    $conn = getDBConnection();
    
    // Check if categories table exists, create if not
    $table_check = $conn->query("SHOW TABLES LIKE 'categories'");
    if (!$table_check || $table_check->num_rows === 0) {
        // Create categories table
        $create_categories_sql = "CREATE TABLE IF NOT EXISTS categories (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category_name (category_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$conn->query($create_categories_sql)) {
            throw new Exception("Error creating categories table: " . $conn->error);
        }
        
        // Insert default categories
        $default_categories = [
            ['Laptops', 'Portable computers and laptops'],
            ['Projectors', 'Projection equipment and displays'],
            ['Monitors', 'Computer monitors and displays'],
            ['Printers', 'Printing equipment'],
            ['Tablets', 'Tablet devices'],
            ['Accessories', 'Computer accessories and peripherals'],
            ['Cables & Adapters', 'Cables, adapters, and connectors'],
            ['Networking', 'Network equipment and devices'],
            ['Audio/Visual', 'Audio and visual equipment']
        ];
        
        $insert_stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        foreach ($default_categories as $cat) {
            $insert_stmt->bind_param("ss", $cat[0], $cat[1]);
            $insert_stmt->execute();
        }
        $insert_stmt->close();
    }
    
    // Fetch categories from database
    $categories_result = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
    if ($categories_result) {
        while ($row = $categories_result->fetch_assoc()) {
            $categories[$row['id']] = $row['category_name'];
        }
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = trim($_POST['equipment_id'] ?? '');
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $category_id = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $status = trim($_POST['status'] ?? 'Available');
    $location = trim($_POST['location'] ?? '');
    
    // Validation
    if (empty($equipment_id)) {
        $error = 'Equipment ID is required.';
    } elseif (empty($equipment_name)) {
        $error = 'Equipment name is required.';
    } elseif (empty($category_id)) {
        $error = 'Category is required.';
    } elseif (!isset($categories[$category_id])) {
        $error = 'Invalid category selected.';
    } elseif (empty($status)) {
        $error = 'Status is required.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Ensure categories table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'categories'");
            if (!$table_check || $table_check->num_rows === 0) {
                $create_categories_sql = "CREATE TABLE IF NOT EXISTS categories (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    category_name VARCHAR(100) NOT NULL UNIQUE,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_category_name (category_name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                $conn->query($create_categories_sql);
            }
            
            // Check if equipment table exists, create if not
            $equipment_table_check = $conn->query("SHOW TABLES LIKE 'equipment'");
            if (!$equipment_table_check || $equipment_table_check->num_rows === 0) {
                // Create equipment table
                $create_equipment_sql = "CREATE TABLE IF NOT EXISTS equipment (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    equipment_id VARCHAR(50) NOT NULL UNIQUE,
                    equipment_name VARCHAR(255) NOT NULL,
                    category_id INT(11) UNSIGNED NOT NULL,
                    brand VARCHAR(100),
                    model VARCHAR(100),
                    serial_number VARCHAR(100) NOT NULL,
                    processor VARCHAR(100),
                    operating_system VARCHAR(100),
                    year INT,
                    adapter VARCHAR(100),
                    warranty_end DATE,
                    remark TEXT,
                    total DECIMAL(10,2),
                    staff_id VARCHAR(50),
                    staff_name VARCHAR(255),
                    designation VARCHAR(100),
                    staff_email VARCHAR(255),
                    employment_type VARCHAR(100),
                    job_category VARCHAR(100),
                    dept_lvl3 VARCHAR(100),
                    dept_lvl4 VARCHAR(100),
                    source VARCHAR(50) DEFAULT 'manual',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_equipment_id (equipment_id),
                    INDEX idx_category_id (category_id),
                    INDEX idx_serial_number (serial_number),
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (!$conn->query($create_equipment_sql)) {
                    throw new Exception("Error creating equipment table: " . $conn->error);
                }
            }
            
            // Check if inventory table exists, create if not
            $inventory_table_check = $conn->query("SHOW TABLES LIKE 'inventory'");
            if (!$inventory_table_check || $inventory_table_check->num_rows === 0) {
                // Create inventory table with foreign key to equipment
                $create_inventory_sql = "CREATE TABLE IF NOT EXISTS inventory (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    equipment_id VARCHAR(50) NOT NULL UNIQUE,
                    status VARCHAR(50) DEFAULT 'Available',
                    location VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_equipment_id (equipment_id),
                    INDEX idx_status (status),
                    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE RESTRICT ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (!$conn->query($create_inventory_sql)) {
                    throw new Exception("Error creating inventory table: " . $conn->error);
                }
            }
            
            // Verify category exists
            $category_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
            $category_check->bind_param("i", $category_id);
            $category_check->execute();
            $category_result = $category_check->get_result();
            
            if ($category_result->num_rows === 0) {
                $error = 'Selected category does not exist.';
                $category_check->close();
                $conn->close();
            } else {
                $category_check->close();
                
                // Check if equipment_id already exists in equipment table
                $check_stmt = $conn->prepare("SELECT id FROM equipment WHERE equipment_id = ?");
                $check_stmt->bind_param("s", $equipment_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Equipment ID already exists. Please use a different ID.';
                    $check_stmt->close();
                } else {
                    $check_stmt->close();
                    
                    // Start transaction
                    $conn->autocommit(FALSE);
                    
                    try {
                        // Insert into equipment table first
                        $equipment_insert = $conn->prepare("INSERT INTO equipment (equipment_id, equipment_name, category_id, brand, model, serial_number, source) VALUES (?, ?, ?, ?, ?, ?, 'manual')");
                        $equipment_insert->bind_param("ssisss", $equipment_id, $equipment_name, $category_id, $brand, $model, $serial_number);
                        
                        if (!$equipment_insert->execute()) {
                            throw new Exception("Failed to insert equipment: " . $equipment_insert->error);
                        }
                        $equipment_insert->close();
                        
                        // Insert into inventory table (reference to equipment)
                        $inventory_insert = $conn->prepare("INSERT INTO inventory (equipment_id, status, location) VALUES (?, ?, ?)");
                        $inventory_insert->bind_param("sss", $equipment_id, $status, $location);
                        
                        if (!$inventory_insert->execute()) {
                            throw new Exception("Failed to insert inventory: " . $inventory_insert->error);
                        }
                        $inventory_insert->close();
                        
                        // Commit transaction
                        $conn->commit();
                        $conn->autocommit(TRUE);
                        
                        setSessionMessage('success', 'Inventory item added successfully!');
                        header('Location: ListInventory.php');
                        exit();
                    } catch (Exception $e) {
                        $conn->rollback();
                        $conn->autocommit(TRUE);
                        $error = 'Failed to add inventory item: ' . $e->getMessage();
                    }
                }
            }
            
            $conn->close();
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
$activePage = 'inventory';
$pageTitle = 'Add Inventory Item';
$additionalCSS = ['../css/AddInventoryItem.css'];
$additionalJS = ['../js/CRUD/AddInventoryItem.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Add New Inventory Item</h1>
                <p class="page-subtitle">Register a new IT equipment item to the inventory system</p>
            </div>
            <a href="ListInventory.php" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span>Back to Inventory</span>
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
        <form id="addInventoryForm" method="POST" class="inventory-form">
            <div class="form-section">
                <h2 class="section-title">Basic Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="equipment_id">Equipment ID <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="equipment_id" 
                            name="equipment_id" 
                            value="<?php echo htmlspecialchars($_POST['equipment_id'] ?? ''); ?>"
                            placeholder="e.g., EQ001"
                            required
                        >
                        <small>Unique identifier for this equipment</small>
                    </div>

                    <div class="form-group">
                        <label for="equipment_name">Equipment Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="equipment_name" 
                            name="equipment_name" 
                            value="<?php echo htmlspecialchars($_POST['equipment_name'] ?? ''); ?>"
                            placeholder="e.g., Dell Latitude 5520 Laptop"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat_id => $cat_name): ?>
                                <option value="<?php echo htmlspecialchars($cat_id); ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $cat_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <?php foreach ($statuses as $stat): ?>
                                <option value="<?php echo htmlspecialchars($stat); ?>" <?php echo (isset($_POST['status']) && $_POST['status'] === $stat) ? 'selected' : ($stat === 'Available' ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($stat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Equipment Details</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input 
                            type="text" 
                            id="brand" 
                            name="brand" 
                            value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>"
                            placeholder="e.g., Dell, HP, Apple"
                        >
                    </div>

                    <div class="form-group">
                        <label for="model">Model</label>
                        <input 
                            type="text" 
                            id="model" 
                            name="model" 
                            value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>"
                            placeholder="e.g., Latitude 5520, MacBook Pro"
                        >
                    </div>

                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input 
                            type="text" 
                            id="serial_number" 
                            name="serial_number" 
                            value="<?php echo htmlspecialchars($_POST['serial_number'] ?? ''); ?>"
                            placeholder="e.g., DL5520-2023-001"
                        >
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                            placeholder="e.g., IT Storage Room A"
                        >
                    </div>
                </div>
            </div>

            <div class="form-section">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Add Inventory Item</span>
                </button>
                <a href="ImportCSV.php" class="btn-import" style="background-color: #17a2b8; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-right: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Import CSV</span>
                </a>
                <a href="ListInventory.php" class="btn-cancel">Cancel</a>
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
                        <strong>Equipment ID</strong>
                        <p>Must be unique. Check existing inventory before assigning a new ID.</p>
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
                        <p>Equipment ID, Name, Category, and Status are mandatory fields.</p>
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
                        <strong>Accuracy Matters</strong>
                        <p>Double-check all information before submitting. Incorrect data may cause inventory discrepancies.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Serial Numbers</strong>
                        <p>Include serial numbers when available for better equipment tracking.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Location Details</strong>
                        <p>Specify the exact location where the equipment is stored or currently located.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

