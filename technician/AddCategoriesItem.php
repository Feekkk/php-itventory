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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');
    
    // Validation
    if (empty($category_name)) {
        $error = 'Category name is required.';
    } elseif (strlen($category_name) < 2) {
        $error = 'Category name must be at least 2 characters long.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if categories table exists, create if not
            $table_check = $conn->query("SHOW TABLES LIKE 'categories'");
            if (!$table_check || $table_check->num_rows === 0) {
                // Create categories table
                $create_table_sql = "CREATE TABLE IF NOT EXISTS categories (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    category_name VARCHAR(100) NOT NULL UNIQUE,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_category_name (category_name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Error creating categories table: " . $conn->error);
                }
            }
            
            // Check if category_name already exists
            $check_stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
            $check_stmt->bind_param("s", $category_name);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Category name already exists. Please use a different name.';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Insert new category
                $insert_stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
                $insert_stmt->bind_param("ss", $category_name, $category_description);
                
                if ($insert_stmt->execute()) {
                    $insert_stmt->close();
                    $conn->close();
                    setSessionMessage('success', 'Category added successfully!');
                    header('Location: ListInventory.php');
                    exit();
                } else {
                    $error = 'Failed to add category: ' . $insert_stmt->error;
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
$pageTitle = 'Add Category';
$additionalCSS = ['../css/AddInventoryItem.css'];
$additionalJS = ['../js/CRUD/AddInventoryItem.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Add New Category</h1>
                <p class="page-subtitle">Create a new equipment category for the inventory system</p>
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
            <form id="addCategoryForm" method="POST" class="inventory-form">
                <div class="form-section">
                    <h2 class="section-title">Category Information</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="category_name">Category Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="category_name" 
                                name="category_name" 
                                value="<?php echo htmlspecialchars($_POST['category_name'] ?? ''); ?>"
                                placeholder="e.g., Servers, Workstations, Peripherals"
                                required
                            >
                            <small>Enter a unique name for this category</small>
                        </div>

                        <div class="form-group full-width">
                            <label for="category_description">Description</label>
                            <textarea 
                                id="category_description" 
                                name="category_description" 
                                rows="4"
                                placeholder="Enter a description for this category (optional)..."
                            ><?php echo htmlspecialchars($_POST['category_description'] ?? ''); ?></textarea>
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
                        <span>Add Category</span>
                    </button>
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
                        <strong>Category Name</strong>
                        <p>Must be unique. Check existing categories before creating a new one.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Naming Convention</strong>
                        <p>Use clear, descriptive names that help identify the type of equipment.</p>
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
                        <strong>Description</strong>
                        <p>Optional but recommended. Helps other users understand what equipment belongs in this category.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

