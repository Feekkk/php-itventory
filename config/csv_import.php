<?php
/**
 * CSV Import Configuration
 * 
 * This file defines the mapping between CSV columns and database columns
 * for importing equipment data from CSV files.
 */

/**
 * CSV Column to Database Column Mapping
 * 
 * Format: 'CSV Column Name' => [
 *     'db_column' => 'database_column_name',
 *     'required' => true/false,
 *     'transform' => 'function_name' (optional),
 *     'default' => 'default_value' (optional)
 * ]
 * 
 * @return array Column mapping configuration
 */
function getCSVColumnMapping() {
    return [
    // Equipment Information (Primary fields) - Maps to equipment table
    'Asset ID' => [
        'db_column' => 'equipment_id',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'AssetID' => [
        'db_column' => 'equipment_id',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Serial Number' => [
        'db_column' => 'serial_number',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'SerialNumber' => [
        'db_column' => 'serial_number',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'SeriaNumber' => [
        'db_column' => 'serial_number',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Brand' => [
        'db_column' => 'brand',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Model' => [
        'db_column' => 'model',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Processor' => [
        'db_column' => 'processor',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Operating System' => [
        'db_column' => 'operating_system',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'OS' => [
        'db_column' => 'operating_system',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Category' => [
        'db_column' => 'category_id',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'mapCategoryToId'
    ],
    'Department' => [
        'db_column' => 'location',
        'required' => false,
        'table' => 'inventory',
        'transform' => 'trim'
    ],
    'Status' => [
        'db_column' => 'status',
        'required' => false,
        'table' => 'inventory',
        'default' => 'Available',
        'transform' => 'normalizeStatus'
    ],
    'TAHUN' => [
        'db_column' => 'year',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'parseYear'
    ],
    'Year' => [
        'db_column' => 'year',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'parseYear'
    ],
    'Adapter' => [
        'db_column' => 'adapter',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Warranty End' => [
        'db_column' => 'warranty_end',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'parseDate'
    ],
    'WarrantyEnd' => [
        'db_column' => 'warranty_end',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'parseDate'
    ],
    'Remark' => [
        'db_column' => 'remark',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Total' => [
        'db_column' => 'total',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'parseNumeric'
    ],
    
    // Staff/Employee Information (assigned to equipment) - Maps to equipment table
    'ID' => [
        'db_column' => 'staff_id',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Employee Name' => [
        'db_column' => 'staff_name',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'StaffName' => [
        'db_column' => 'staff_name',
        'required' => true,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Designation' => [
        'db_column' => 'designation',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Email' => [
        'db_column' => 'staff_email',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'validateEmail'
    ],
    'Employment Type' => [
        'db_column' => 'employment_type',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Employement Type' => [
        'db_column' => 'employment_type',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'EmploymentType' => [
        'db_column' => 'employment_type',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'Job Category' => [
        'db_column' => 'job_category',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'JobCategory' => [
        'db_column' => 'job_category',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'DEPTLVL3' => [
        'db_column' => 'dept_lvl3',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ],
    'DEPTLVL4' => [
        'db_column' => 'dept_lvl4',
        'required' => false,
        'table' => 'equipment',
        'transform' => 'trim'
    ]
    ];
}

/**
 * Required fields for inventory import
 * 
 * @return array Required fields by table
 */
function getCSVRequiredFields() {
    return [
        'equipment' => ['equipment_id', 'serial_number', 'staff_name']
    ];
}

/**
 * Default values for inventory fields
 * 
 * @return array Default values by table
 */
function getCSVDefaultValues() {
    return [
        'equipment' => [
            'equipment_name' => '', // Will be generated from Brand + Model if empty
            'source' => 'csv'
        ],
        'inventory' => [
            'status' => 'Available'
        ]
    ];
}

/**
 * CSV Import Settings
 */
define('CSV_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('CSV_ALLOWED_EXTENSIONS', ['csv']);
define('CSV_DELIMITER', ','); // CSV delimiter
define('CSV_ENCLOSURE', '"'); // CSV enclosure character
define('CSV_ESCAPE', '\\'); // CSV escape character

/**
 * Get category ID from category name
 * 
 * @param string $categoryName Category name from CSV
 * @param mysqli $conn Database connection
 * @return int|null Category ID or null if not found
 */
function mapCategoryToId($categoryName, $conn = null) {
    if (empty($categoryName)) {
        return null; // Will be set to "Other" by import logic
    }
    
    if ($conn === null) {
        require_once __DIR__ . '/database.php';
        $conn = getDBConnection();
    }
    
    $categoryName = trim($categoryName);
    
    // Try to find exact match first
    $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['id'];
    }
    
    // Try case-insensitive match
    $stmt = $conn->prepare("SELECT id FROM categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['id'];
    }
    
    $stmt->close();
    
    // If category doesn't exist, return null (will be set to "Other" by import logic)
    return null;
}

/**
 * Normalize status value
 * 
 * @param string $status Status from CSV
 * @return string Normalized status
 */
function normalizeStatus($status) {
    if (empty($status)) {
        return 'Available';
    }
    
    $status = trim($status);
    $statusMap = [
        'available' => 'Available',
        'in use' => 'In Use',
        'maintenance' => 'Maintenance',
        'reserved' => 'Reserved',
        'hand over' => 'Hand Over',
        'handover' => 'Hand Over',
        'hand_over' => 'Hand Over'
    ];
    
    $lowerStatus = strtolower($status);
    return $statusMap[$lowerStatus] ?? ucfirst($status);
}

/**
 * Parse year value
 * 
 * @param mixed $year Year from CSV
 * @return int|null Year as integer or null
 */
function parseYear($year) {
    if (empty($year)) {
        return null;
    }
    
    $year = trim($year);
    $yearInt = (int)$year;
    
    // Validate year range (reasonable range: 1990-2100)
    if ($yearInt >= 1990 && $yearInt <= 2100) {
        return $yearInt;
    }
    
    return null;
}

/**
 * Parse date value
 * 
 * @param mixed $date Date from CSV
 * @return string|null Date in Y-m-d format or null
 */
function parseDate($date) {
    if (empty($date)) {
        return null;
    }
    
    $date = trim($date);
    
    // Try to parse various date formats
    $timestamp = strtotime($date);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    return null;
}

/**
 * Parse numeric value
 * 
 * @param mixed $value Numeric value from CSV
 * @return float|null Numeric value or null
 */
function parseNumeric($value) {
    if (empty($value) && $value !== '0' && $value !== 0) {
        return null;
    }
    
    $value = trim($value);
    $value = str_replace(',', '', $value); // Remove thousand separators
    
    if (is_numeric($value)) {
        return (float)$value;
    }
    
    return null;
}

/**
 * Validate email address
 * 
 * @param string $email Email from CSV
 * @return string|null Valid email or null
 */
function validateEmail($email) {
    if (empty($email)) {
        return null;
    }
    
    $email = trim($email);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    
    return null;
}

/**
 * Generate equipment name from brand and model
 * 
 * @param array $row CSV row data
 * @return string Generated equipment name
 */
function generateEquipmentName($row) {
    $brand = trim($row['Brand'] ?? $row['brand'] ?? '');
    $model = trim($row['Model'] ?? $row['model'] ?? '');
    
    if (!empty($brand) && !empty($model)) {
        return $brand . ' ' . $model;
    } elseif (!empty($brand)) {
        return $brand;
    } elseif (!empty($model)) {
        return $model;
    } else {
        return 'Equipment ' . ($row['Asset ID'] ?? $row['AssetID'] ?? 'Unknown');
    }
}

/**
 * Get required fields for a specific table
 * 
 * @param string $table Table name ('equipment' or 'inventory')
 * @return array Required field names
 */
function getCSVRequiredFieldsForTable($table) {
    $requiredFields = getCSVRequiredFields();
    return $requiredFields[$table] ?? [];
}

/**
 * Get default values for a specific table
 * 
 * @param string $table Table name ('equipment' or 'inventory')
 * @return array Default values
 */
function getCSVDefaultValuesForTable($table) {
    $defaultValues = getCSVDefaultValues();
    return $defaultValues[$table] ?? [];
}

