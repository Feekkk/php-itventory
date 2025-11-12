<?php
require_once __DIR__ . '/../database/config.php';
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

// Categories and statuses
$categories = ['Laptops', 'Projectors', 'Monitors', 'Printers', 'Tablets', 'Accessories', 'Cables & Adapters', 'Networking', 'Audio/Visual'];
$statuses = ['Available', 'In Use', 'Maintenance', 'Reserved'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = trim($_POST['equipment_id'] ?? '');
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $status = trim($_POST['status'] ?? 'Available');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($equipment_id)) {
        $error = 'Equipment ID is required.';
    } elseif (empty($equipment_name)) {
        $error = 'Equipment name is required.';
    } elseif (empty($category)) {
        $error = 'Category is required.';
    } elseif (empty($status)) {
        $error = 'Status is required.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if inventory table exists, create if not
            $table_check = $conn->query("SHOW TABLES LIKE 'inventory'");
            if (!$table_check || $table_check->num_rows === 0) {
                // Create inventory table
                $create_table_sql = "CREATE TABLE IF NOT EXISTS inventory (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    equipment_id VARCHAR(50) NOT NULL UNIQUE,
                    equipment_name VARCHAR(255) NOT NULL,
                    category VARCHAR(100) NOT NULL,
                    brand VARCHAR(100),
                    model VARCHAR(100),
                    serial_number VARCHAR(100),
                    status VARCHAR(50) DEFAULT 'Available',
                    location VARCHAR(255),
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_equipment_id (equipment_id),
                    INDEX idx_category (category),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Error creating inventory table: " . $conn->error);
                }
            }
            
            // Check if equipment_id already exists
            $check_stmt = $conn->prepare("SELECT id FROM inventory WHERE equipment_id = ?");
            $check_stmt->bind_param("s", $equipment_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Equipment ID already exists. Please use a different ID.';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Insert new inventory item
                $insert_stmt = $conn->prepare("INSERT INTO inventory (equipment_id, equipment_name, category, brand, model, serial_number, status, location, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("sssssssss", $equipment_id, $equipment_name, $category, $brand, $model, $serial_number, $status, $location, $description);
                
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $conn->close();
                    setSessionMessage('success', 'Inventory item added successfully!');
                    header('Location: ListInventory.php');
                    exit();
                } else {
                    $error = 'Failed to add inventory item: ' . $insert_stmt->error;
                    $insert_stmt->close();
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
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
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
                <h2 class="section-title">Additional Information</h2>
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        placeholder="Enter equipment specifications, features, or additional notes..."
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
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
                <a href="ListInventory.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

