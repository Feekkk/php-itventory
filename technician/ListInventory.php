<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access the inventory.');
    header('Location: ../auth/login.php');
    exit();
}

// Get user data
$user = getUserData();

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$conn = getDBConnection();
$where = [];
$params = [];
$types = '';

// Check if inventory table exists, if not, use sample data
$table_check = $conn->query("SHOW TABLES LIKE 'inventory'");
$has_table = $table_check && $table_check->num_rows > 0;

if ($has_table) {
    // Check if inventory table has category_id column (new structure) or category column (old structure)
    $columns_check = $conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
    $has_category_id = $columns_check && $columns_check->num_rows > 0;
    
    // Build WHERE clause
    if (!empty($search)) {
        $where[] = "(equipment_name LIKE ? OR equipment_id LIKE ? OR description LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'sss';
    }
    
    if (!empty($category)) {
        if ($has_category_id) {
            // New structure: use category_id
            $where[] = "category_id = ?";
            $params[] = (int)$category;
            $types .= 'i';
        } else {
            // Old structure: use category name
            $where[] = "category = ?";
            $params[] = $category;
            $types .= 's';
        }
    }
    
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM inventory $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_items = $total_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get inventory items with category name
    if ($has_category_id) {
        $sql = "SELECT i.*, c.category_name as category 
                FROM inventory i 
                LEFT JOIN categories c ON i.category_id = c.id 
                $where_clause 
                ORDER BY equipment_name ASC";
    } else {
        $sql = "SELECT * FROM inventory $where_clause ORDER BY equipment_name ASC";
    }
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $inventory_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
} else {
    // Sample data if table doesn't exist
    $all_sample_items = [
        [
            'equipment_id' => 'EQ001',
            'equipment_name' => 'Dell Latitude 5520 Laptop',
            'category' => 'Laptops',
            'brand' => 'Dell',
            'model' => 'Latitude 5520',
            'serial_number' => 'DL5520-2023-001',
            'status' => 'Available',
            'location' => 'IT Storage Room A',
            'description' => '15.6" FHD Display, Intel Core i7, 16GB RAM, 512GB SSD'
        ],
        [
            'equipment_id' => 'EQ002',
            'equipment_name' => 'Epson Projector EX9200',
            'category' => 'Projectors',
            'brand' => 'Epson',
            'model' => 'EX9200',
            'serial_number' => 'EP9200-2023-045',
            'status' => 'Available',
            'location' => 'IT Storage Room B',
            'description' => '3LCD Projector, 3600 Lumens, Full HD'
        ],
        [
            'equipment_id' => 'EQ003',
            'equipment_name' => 'Logitech Webcam C920',
            'category' => 'Accessories',
            'brand' => 'Logitech',
            'model' => 'C920',
            'serial_number' => 'LG920-2023-128',
            'status' => 'In Use',
            'location' => 'Conference Room 1',
            'description' => 'HD 1080p Webcam with autofocus'
        ],
        [
            'equipment_id' => 'EQ004',
            'equipment_name' => 'HP LaserJet Pro M404dn',
            'category' => 'Printers',
            'brand' => 'HP',
            'model' => 'LaserJet Pro M404dn',
            'serial_number' => 'HPM404-2023-067',
            'status' => 'Available',
            'location' => 'IT Storage Room A',
            'description' => 'Monochrome Laser Printer, Network Ready'
        ],
        [
            'equipment_id' => 'EQ005',
            'equipment_name' => 'Samsung Monitor 27"',
            'category' => 'Monitors',
            'brand' => 'Samsung',
            'model' => 'F27T450FQR',
            'serial_number' => 'SM27-2023-089',
            'status' => 'Available',
            'location' => 'IT Storage Room B',
            'description' => '27" FHD Monitor, IPS Panel'
        ],
        [
            'equipment_id' => 'EQ006',
            'equipment_name' => 'HDMI Cable 3m',
            'category' => 'Cables & Adapters',
            'brand' => 'Generic',
            'model' => 'HDMI-3M',
            'serial_number' => 'CBL-HDMI-003',
            'status' => 'Available',
            'location' => 'IT Storage Room C',
            'description' => 'High Speed HDMI Cable, 3 meters'
        ],
        [
            'equipment_id' => 'EQ007',
            'equipment_name' => 'iPad Pro 12.9"',
            'category' => 'Tablets',
            'brand' => 'Apple',
            'model' => 'iPad Pro 12.9"',
            'serial_number' => 'APIP12-2023-012',
            'status' => 'In Use',
            'location' => 'Faculty Office 3',
            'description' => '12.9" iPad Pro, 256GB, Wi-Fi'
        ],
        [
            'equipment_id' => 'EQ008',
            'equipment_name' => 'Cisco Switch 24-Port',
            'category' => 'Networking',
            'brand' => 'Cisco',
            'model' => 'SG250-24',
            'serial_number' => 'CS24-2023-034',
            'status' => 'Available',
            'location' => 'IT Storage Room A',
            'description' => '24-Port Gigabit Managed Switch'
        ]
    ];
    
    $inventory_items = $all_sample_items;
    
    // Apply filters to sample data
    if (!empty($search)) {
        $inventory_items = array_filter($inventory_items, function($item) use ($search) {
            return stripos($item['equipment_name'], $search) !== false ||
                   stripos($item['equipment_id'], $search) !== false ||
                   stripos($item['description'], $search) !== false;
        });
    }
    
    if (!empty($category)) {
        $inventory_items = array_filter($inventory_items, function($item) use ($category) {
            return $item['category'] === $category;
        });
    }
    
    if (!empty($status)) {
        $inventory_items = array_filter($inventory_items, function($item) use ($status) {
            return $item['status'] === $status;
        });
    }
    
    $total_items = count($inventory_items);
    $inventory_items = array_values($inventory_items); // Re-index array
}

// Get categories from database
$categories = [];
$category_counts = [];
try {
    $cat_conn = getDBConnection();
    
    // Check if categories table exists
    $cat_table_check = $cat_conn->query("SHOW TABLES LIKE 'categories'");
    if ($cat_table_check && $cat_table_check->num_rows > 0) {
        // Fetch categories from database
        $cat_result = $cat_conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
        if ($cat_result) {
            while ($row = $cat_result->fetch_assoc()) {
                $categories[$row['id']] = $row['category_name'];
                $category_counts[$row['category_name']] = 0;
            }
        }
        
        // Count items by category (use all items, not filtered)
        if ($has_table) {
            // Check if inventory table has category_id column
            $columns_check = $cat_conn->query("SHOW COLUMNS FROM inventory LIKE 'category_id'");
            $has_category_id = $columns_check && $columns_check->num_rows > 0;
            
            if ($has_category_id) {
                // New structure: count by category_id
                foreach ($categories as $cat_id => $cat_name) {
                    $count_sql = "SELECT COUNT(*) as count FROM inventory WHERE category_id = ?";
                    $count_stmt = $cat_conn->prepare($count_sql);
                    $count_stmt->bind_param("i", $cat_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $category_counts[$cat_name] = $count_result->fetch_assoc()['count'];
                    $count_stmt->close();
                }
            } else {
                // Old structure: count by category name
                foreach ($categories as $cat_id => $cat_name) {
                    $count_sql = "SELECT COUNT(*) as count FROM inventory WHERE category = ?";
                    $count_stmt = $cat_conn->prepare($count_sql);
                    $count_stmt->bind_param("s", $cat_name);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $category_counts[$cat_name] = $count_result->fetch_assoc()['count'];
                    $count_stmt->close();
                }
            }
        }
    } else {
        // If categories table doesn't exist, use default categories
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
        foreach ($categories as $cat_name) {
            $category_counts[$cat_name] = 0;
        }
    }
    
    $cat_conn->close();
} catch (Exception $e) {
    // Fallback to default categories
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
    foreach ($categories as $cat_name) {
        $category_counts[$cat_name] = 0;
    }
}

// Get statuses
$statuses = ['Available', 'In Use', 'Maintenance', 'Reserved'];

// Count from sample data if no database
if (!$has_table) {
    foreach ($all_sample_items as $item) {
        $cat = $item['category'] ?? '';
        if (isset($category_counts[$cat])) {
            $category_counts[$cat]++;
        }
    }
}

// Set active page and title for header component
$activePage = 'inventory';
$pageTitle = 'List Inventory';
$additionalCSS = ['../css/inventory.css'];
$additionalJS = ['../js/inventory.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Equipment Inventory</h1>
                <p class="page-subtitle">Browse and manage all IT equipment available in the department</p>
            </div>
            <div class="add-inventory-dropdown">
                <button type="button" class="add-inventory-btn" id="addInventoryBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Inventory</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-arrow">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="dropdown-menu" id="addInventoryDropdown">
                    <a href="AddInventoryItem.php" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="11" rx="1.5"></rect>
                            <path d="M4 17h16l1 2H3l1-2z"></path>
                        </svg>
                        <span>Add Item</span>
                    </a>
                    <a href="AddCategoriesItem.php" class="dropdown-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                        </svg>
                        <span>Add Category</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Count Cards -->
    <div class="category-cards">
        <?php foreach ($categories as $cat_id => $cat_name): ?>
            <a href="?category=<?php echo urlencode($cat_id); ?>" class="category-card">
                <div class="category-card-content">
                    <h3 class="category-name"><?php echo htmlspecialchars($cat_name); ?></h3>
                    <p class="category-count"><?php echo $category_counts[$cat_name] ?? 0; ?> items</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search and Filter Section -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form" id="filtersForm">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by equipment name, ID, or description..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="search-input"
                >
            </div>

            <div class="filter-group">
                <select name="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat_id => $cat_name): ?>
                        <option value="<?php echo htmlspecialchars($cat_id); ?>" <?php echo $category == $cat_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <?php foreach ($statuses as $stat): ?>
                        <option value="<?php echo htmlspecialchars($stat); ?>" <?php echo $status === $stat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="filter-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Apply Filters
                </button>

                <?php if (!empty($search) || !empty($category) || !empty($status)): ?>
                    <a href="ListInventory.php" class="clear-btn">
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
        <span class="results-count"><?php echo $total_items; ?> item<?php echo $total_items !== 1 ? 's' : ''; ?> found</span>
    </div>

    <!-- Inventory Table -->
    <div class="table-container">
        <?php if (empty($inventory_items)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="5" width="18" height="11" rx="1.5"></rect>
                    <path d="M4 17h16l1 2H3l1-2z"></path>
                </svg>
                <h3>No equipment found</h3>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
        <?php else: ?>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Equipment ID</th>
                        <th>Equipment Name</th>
                        <th>Category</th>
                        <th>Brand / Model</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory_items as $item): ?>
                        <tr>
                            <td>
                                <span class="equipment-id"><?php echo htmlspecialchars($item['equipment_id'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <div class="equipment-name">
                                    <strong><?php echo htmlspecialchars($item['equipment_name'] ?? 'N/A'); ?></strong>
                                    <?php if (!empty($item['description'])): ?>
                                        <span class="equipment-desc"><?php echo htmlspecialchars($item['description']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $category_name = $item['category'] ?? 'other';
                                $category_class = strtolower(str_replace([' ', '&', '/'], ['-', '', '-'], $category_name));
                                ?>
                                <span class="category-badge category-<?php echo htmlspecialchars($category_class); ?>">
                                    <?php echo htmlspecialchars($category_name); ?>
                                </span>
                            </td>
                            <td>
                                <div class="brand-model">
                                    <span class="brand"><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></span>
                                    <span class="model"><?php echo htmlspecialchars($item['model'] ?? ''); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="serial-number"><?php echo htmlspecialchars($item['serial_number'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <?php
                                $status_class = strtolower(str_replace(' ', '-', $item['status'] ?? 'unknown'));
                                ?>
                                <span class="status-badge status-<?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($item['status'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="location"><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <a href="ViewItem.php?id=<?php echo urlencode($item['equipment_id'] ?? ''); ?>" class="action-btn view-btn" title="View Details">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
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
