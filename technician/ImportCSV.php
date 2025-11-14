<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csv_import.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access this page.');
    header('Location: ../auth/login.php');
    exit();
}

$user = getUserData();
$error = '';
$success = '';
$import_results = null;

// Handle CSV file upload and import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error: ' . $file['error'];
    } elseif ($file['size'] > CSV_MAX_FILE_SIZE) {
        $error = 'File size exceeds maximum allowed size of ' . (CSV_MAX_FILE_SIZE / 1024 / 1024) . 'MB.';
    } elseif (!in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), CSV_ALLOWED_EXTENSIONS)) {
        $error = 'Invalid file type. Only CSV files are allowed.';
    } else {
        // Process CSV file
        $conn = getDBConnection();
        $column_mapping = getCSVColumnMapping();
        $required_fields_equipment = getCSVRequiredFieldsForTable('equipment');
        $default_values_equipment = getCSVDefaultValuesForTable('equipment');
        $default_values_inventory = getCSVDefaultValuesForTable('inventory');
        
        $import_results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Fix encoding: Convert CSV file to UTF-8
        $file_content = file_get_contents($file['tmp_name']);
        
        // Detect encoding and convert to UTF-8
        $detected_encoding = mb_detect_encoding($file_content, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ASCII'], true);
        if ($detected_encoding && $detected_encoding !== 'UTF-8') {
            $file_content = mb_convert_encoding($file_content, 'UTF-8', $detected_encoding);
            // Save converted content back to temp file
            file_put_contents($file['tmp_name'], $file_content);
        }
        
        // Open CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $error = 'Failed to open CSV file.';
        } else {
            // Read header row
            $headers = fgetcsv($handle, 1000, CSV_DELIMITER, CSV_ENCLOSURE, CSV_ESCAPE);
            if ($headers === false) {
                $error = 'Failed to read CSV headers.';
                fclose($handle);
            } else {
                // Normalize headers (trim whitespace)
                $headers = array_map('trim', $headers);
                
                // Map CSV columns to database columns
                $column_map = [];
                foreach ($headers as $index => $header) {
                    if (isset($column_mapping[$header])) {
                        $column_map[$index] = $column_mapping[$header];
                    }
                }
                
                if (empty($column_map)) {
                    $error = 'No recognized columns found in CSV. Please check your CSV format.';
                    fclose($handle);
                } else {
                    // Process each row
                    $row_number = 1; // Start from 1 (header is row 0)
                    
                    while (($row = fgetcsv($handle, 1000, CSV_DELIMITER, CSV_ENCLOSURE, CSV_ESCAPE)) !== false) {
                        $row_number++;
                        $import_results['total']++;
                        
                        // Skip empty rows
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        
                        // Separate data into equipment and inventory tables
                        $equipment_data = [];
                        $inventory_data = [];
                        $missing_required = [];
                        
                        // Initialize with default values
                        foreach ($default_values_equipment as $key => $value) {
                            $equipment_data[$key] = $value;
                        }
                        foreach ($default_values_inventory as $key => $value) {
                            $inventory_data[$key] = $value;
                        }
                        
                        // Process each column and separate by table
                        foreach ($column_map as $csv_index => $mapping) {
                            if (isset($row[$csv_index])) {
                                $csv_value = trim($row[$csv_index]);
                                
                                // Convert to UTF-8 if needed (handle special characters)
                                if (!mb_check_encoding($csv_value, 'UTF-8')) {
                                    $csv_value = mb_convert_encoding($csv_value, 'UTF-8', 'Windows-1252');
                                }
                                
                                $db_column = $mapping['db_column'];
                                $target_table = $mapping['table'] ?? 'equipment';
                                
                                // Apply transformation if specified
                                if (!empty($mapping['transform']) && function_exists($mapping['transform'])) {
                                    if ($mapping['transform'] === 'mapCategoryToId') {
                                        $csv_value = mapCategoryToId($csv_value, $conn);
                                    } else {
                                        $csv_value = call_user_func($mapping['transform'], $csv_value);
                                    }
                                }
                                
                                // Apply default if value is empty and default is set
                                if (empty($csv_value) && isset($mapping['default'])) {
                                    $csv_value = $mapping['default'];
                                }
                                
                                // Store in appropriate table data array
                                if ($target_table === 'equipment') {
                                    $equipment_data[$db_column] = $csv_value;
                                } elseif ($target_table === 'inventory') {
                                    $inventory_data[$db_column] = $csv_value;
                                }
                            }
                        }
                        
                        // Check required fields for equipment
                        foreach ($required_fields_equipment as $required_field) {
                            if (empty($equipment_data[$required_field])) {
                                $missing_required[] = $required_field;
                            }
                        }
                        
                        if (!empty($missing_required)) {
                            $import_results['failed']++;
                            $import_results['errors'][] = "Row $row_number: Missing required fields: " . implode(', ', $missing_required);
                            continue;
                        }
                        
                        // Generate equipment_name if empty
                        if (empty($equipment_data['equipment_name'])) {
                            // Create associative array from CSV row for generateEquipmentName
                            $row_data = [];
                            foreach ($column_map as $csv_index => $mapping) {
                                if (isset($row[$csv_index])) {
                                    $csv_header = $headers[$csv_index];
                                    $value = trim($row[$csv_index]);
                                    // Convert to UTF-8
                                    if (!mb_check_encoding($value, 'UTF-8')) {
                                        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                                    }
                                    $row_data[$csv_header] = $value;
                                }
                            }
                            $equipment_data['equipment_name'] = generateEquipmentName($row_data);
                        }
                        
                        // Ensure category_id exists (if not, use "Other" category)
                        if (empty($equipment_data['category_id'])) {
                            // Try to get "Other" category ID
                            $cat_stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = 'Other' LIMIT 1");
                            $cat_stmt->execute();
                            $cat_result = $cat_stmt->get_result();
                            if ($cat_result->num_rows > 0) {
                                $equipment_data['category_id'] = $cat_result->fetch_assoc()['id'];
                            } else {
                                // If "Other" category doesn't exist, create it
                                $create_other_stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES ('Other', 'Other equipment and miscellaneous items')");
                                if ($create_other_stmt->execute()) {
                                    $equipment_data['category_id'] = $conn->insert_id;
                                } else {
                                    // Fallback: get first available category
                                    $fallback_stmt = $conn->prepare("SELECT id FROM categories LIMIT 1");
                                    $fallback_stmt->execute();
                                    $fallback_result = $fallback_stmt->get_result();
                                    if ($fallback_result->num_rows > 0) {
                                        $equipment_data['category_id'] = $fallback_result->fetch_assoc()['id'];
                                    } else {
                                        $import_results['failed']++;
                                        $import_results['errors'][] = "Row $row_number: No category found and unable to create 'Other' category.";
                                        continue;
                                    }
                                    $fallback_stmt->close();
                                }
                                $create_other_stmt->close();
                            }
                            $cat_stmt->close();
                        }
                        
                        // Insert into database (equipment table first, then inventory)
                        try {
                            // Start transaction
                            $conn->autocommit(FALSE);
                            
                            // Check if equipment_id already exists in equipment table
                            if (!empty($equipment_data['equipment_id'])) {
                                $check_stmt = $conn->prepare("SELECT id FROM equipment WHERE equipment_id = ?");
                                $check_stmt->bind_param("s", $equipment_data['equipment_id']);
                                $check_stmt->execute();
                                $check_result = $check_stmt->get_result();
                                
                                if ($check_result->num_rows > 0) {
                                    $import_results['failed']++;
                                    $import_results['errors'][] = "Row $row_number: Equipment ID '{$equipment_data['equipment_id']}' already exists.";
                                    $check_stmt->close();
                                    $conn->rollback();
                                    $conn->autocommit(TRUE);
                                    continue;
                                }
                                $check_stmt->close();
                            }
                            
                            // Get existing columns for equipment table
                            $equipment_columns_check = $conn->query("SHOW COLUMNS FROM equipment");
                            $equipment_existing_columns = [];
                            $equipment_col_types = [];
                            if ($equipment_columns_check) {
                                while ($col = $equipment_columns_check->fetch_assoc()) {
                                    $equipment_existing_columns[] = $col['Field'];
                                    $equipment_col_types[$col['Field']] = strtolower($col['Type']);
                                }
                            }
                            
                            // Filter equipment_data to only include existing columns
                            $filtered_equipment = [];
                            foreach ($equipment_data as $col => $val) {
                                if (in_array($col, $equipment_existing_columns)) {
                                    $filtered_equipment[$col] = $val;
                                }
                            }
                            
                            // Build INSERT query for equipment table
                            $equipment_columns = array_keys($filtered_equipment);
                            $equipment_placeholders = array_fill(0, count($equipment_columns), '?');
                            $equipment_types = '';
                            
                            foreach ($equipment_columns as $col) {
                                if (isset($equipment_col_types[$col])) {
                                    $col_type = $equipment_col_types[$col];
                                    if (strpos($col_type, 'int') !== false) {
                                        $equipment_types .= 'i';
                                    } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false || strpos($col_type, 'double') !== false) {
                                        $equipment_types .= 'd';
                                    } else {
                                        $equipment_types .= 's';
                                    }
                                } else {
                                    $equipment_types .= 's';
                                }
                            }
                            
                            // Insert into equipment table
                            $equipment_column_list = implode(', ', $equipment_columns);
                            $equipment_placeholder_list = implode(', ', $equipment_placeholders);
                            $equipment_sql = "INSERT INTO equipment ($equipment_column_list) VALUES ($equipment_placeholder_list)";
                            $equipment_stmt = $conn->prepare($equipment_sql);
                            
                            if (!$equipment_stmt) {
                                throw new Exception("Prepare equipment failed: " . $conn->error);
                            }
                            
                            $equipment_values = array_values($filtered_equipment);
                            $equipment_stmt->bind_param($equipment_types, ...$equipment_values);
                            
                            if (!$equipment_stmt->execute()) {
                                throw new Exception("Equipment insert failed: " . $equipment_stmt->error);
                            }
                            $equipment_stmt->close();
                            
                            // Now insert into inventory table (reference to equipment)
                            $inventory_data['equipment_id'] = $equipment_data['equipment_id'];
                            
                            // Get existing columns for inventory table
                            $inventory_columns_check = $conn->query("SHOW COLUMNS FROM inventory");
                            $inventory_existing_columns = [];
                            $inventory_col_types = [];
                            if ($inventory_columns_check) {
                                while ($col = $inventory_columns_check->fetch_assoc()) {
                                    $inventory_existing_columns[] = $col['Field'];
                                    $inventory_col_types[$col['Field']] = strtolower($col['Type']);
                                }
                            }
                            
                            // Filter inventory_data to only include existing columns
                            $filtered_inventory = [];
                            foreach ($inventory_data as $col => $val) {
                                if (in_array($col, $inventory_existing_columns)) {
                                    $filtered_inventory[$col] = $val;
                                }
                            }
                            
                            // Build INSERT query for inventory table
                            $inventory_columns = array_keys($filtered_inventory);
                            $inventory_placeholders = array_fill(0, count($inventory_columns), '?');
                            $inventory_types = '';
                            
                            foreach ($inventory_columns as $col) {
                                if (isset($inventory_col_types[$col])) {
                                    $col_type = $inventory_col_types[$col];
                                    if (strpos($col_type, 'int') !== false) {
                                        $inventory_types .= 'i';
                                    } elseif (strpos($col_type, 'decimal') !== false || strpos($col_type, 'float') !== false || strpos($col_type, 'double') !== false) {
                                        $inventory_types .= 'd';
                                    } else {
                                        $inventory_types .= 's';
                                    }
                                } else {
                                    $inventory_types .= 's';
                                }
                            }
                            
                            // Insert into inventory table
                            $inventory_column_list = implode(', ', $inventory_columns);
                            $inventory_placeholder_list = implode(', ', $inventory_placeholders);
                            $inventory_sql = "INSERT INTO inventory ($inventory_column_list) VALUES ($inventory_placeholder_list)";
                            $inventory_stmt = $conn->prepare($inventory_sql);
                            
                            if (!$inventory_stmt) {
                                throw new Exception("Prepare inventory failed: " . $conn->error);
                            }
                            
                            $inventory_values = array_values($filtered_inventory);
                            $inventory_stmt->bind_param($inventory_types, ...$inventory_values);
                            
                            if (!$inventory_stmt->execute()) {
                                throw new Exception("Inventory insert failed: " . $inventory_stmt->error);
                            }
                            $inventory_stmt->close();
                            
                            // Commit transaction
                            $conn->commit();
                            $conn->autocommit(TRUE);
                            
                            $import_results['success']++;
                        } catch (Exception $e) {
                            $conn->rollback();
                            $conn->autocommit(TRUE);
                            $import_results['failed']++;
                            $import_results['errors'][] = "Row $row_number: " . $e->getMessage();
                        }
                    }
                    
                    fclose($handle);
                    
                    if ($import_results['success'] > 0) {
                        $success = "Successfully imported {$import_results['success']} item(s).";
                        if ($import_results['failed'] > 0) {
                            $success .= " {$import_results['failed']} item(s) failed to import.";
                        }
                    } else {
                        $error = "Import failed. No items were imported.";
                    }
                }
            }
        }
        
        $conn->close();
    }
}

// Get session messages
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
$pageTitle = 'Import CSV';
$additionalCSS = ['../css/AddInventoryItem.css'];
$additionalJS = [];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Import CSV File</h1>
                <p class="page-subtitle">Import equipment data from a CSV file</p>
            </div>
            <a href="AddInventoryItem.php" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span>Back to Add Item</span>
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

    <?php if ($import_results): ?>
        <div class="import-results">
            <h3>Import Results</h3>
            <div class="results-summary">
                <div class="result-item">
                    <strong>Total Rows:</strong> <?php echo $import_results['total']; ?>
                </div>
                <div class="result-item success">
                    <strong>Successfully Imported:</strong> <?php echo $import_results['success']; ?>
                </div>
                <div class="result-item error">
                    <strong>Failed:</strong> <?php echo $import_results['failed']; ?>
                </div>
            </div>
            
            <?php if (!empty($import_results['errors'])): ?>
                <div class="errors-list">
                    <h4>Errors:</h4>
                    <ul>
                        <?php foreach (array_slice($import_results['errors'], 0, 20) as $error_msg): ?>
                            <li><?php echo htmlspecialchars($error_msg); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($import_results['errors']) > 20): ?>
                            <li><em>... and <?php echo count($import_results['errors']) - 20; ?> more errors</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="content-layout">
        <div class="form-wrapper">
            <form method="POST" enctype="multipart/form-data" class="inventory-form">
                <div class="form-section">
                    <h2 class="section-title">CSV File Upload</h2>
                    <div class="form-group full-width">
                        <label for="csv_file">Select CSV File <span class="required">*</span></label>
                        <input 
                            type="file" 
                            id="csv_file" 
                            name="csv_file" 
                            accept=".csv"
                            required
                        >
                        <small>Maximum file size: <?php echo (CSV_MAX_FILE_SIZE / 1024 / 1024); ?>MB</small>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">CSV Format Requirements</h2>
                    <div class="csv-info">
                        <p><strong>Required Columns:</strong></p>
                        <ul>
                            <li>Asset ID (or AssetID) - Equipment ID</li>
                            <li>Serial Number (or SerialNumber) - Serial Number</li>
                            <li>Employee Name (or StaffName) - Staff Name</li>
                        </ul>
                        <p><strong>Optional Columns:</strong></p>
                        <ul>
                            <li>Brand, Model, Processor, Operating System (or OS)</li>
                            <li>Category, Status, Department</li>
                            <li>Year (or TAHUN), Adapter, Warranty End, Remark, Total</li>
                            <li>ID, Designation, Email, Employment Type, Job Category, DEPTLVL3, DEPTLVL4</li>
                        </ul>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <span>Import CSV</span>
                    </button>
                    <a href="AddInventoryItem.php" class="btn-cancel">Cancel</a>
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
                <h3>CSV Import Guidelines</h3>
            </div>
            <div class="notice-content">
                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>File Format</strong>
                        <p>CSV file must have headers in the first row. Column names should match the expected format.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Required Fields</strong>
                        <p>Asset ID, Serial Number, and Employee Name are mandatory. Rows missing these will be skipped.</p>
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
                        <strong>Duplicate Prevention</strong>
                        <p>Equipment IDs must be unique. Duplicate IDs will be skipped during import.</p>
                    </div>
                </div>

                <div class="notice-item">
                    <div class="notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                        </svg>
                    </div>
                    <div class="notice-text">
                        <strong>Category Mapping</strong>
                        <p>Categories will be automatically matched. If not found, the first available category will be used.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.import-results {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.import-results h3 {
    margin-top: 0;
    color: #2c3e50;
}
.results-summary {
    display: flex;
    gap: 20px;
    margin: 15px 0;
}
.result-item {
    padding: 10px 15px;
    background: white;
    border-radius: 5px;
    border-left: 4px solid #6c757d;
}
.result-item.success {
    border-left-color: #28a745;
}
.result-item.error {
    border-left-color: #dc3545;
}
.errors-list {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 5px;
    max-height: 300px;
    overflow-y: auto;
}
.errors-list ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}
.errors-list li {
    margin: 5px 0;
    color: #dc3545;
}
.csv-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}
.csv-info ul {
    margin: 10px 0;
    padding-left: 20px;
}
.csv-info li {
    margin: 5px 0;
}
</style>

<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

