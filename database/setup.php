<?php
/**
 * Database Setup Script for RCMP-itventory
 * 
 * This script creates the database and necessary tables.
 * Run this file once to set up your database.
 * 
 * IMPORTANT: Update the database credentials below before running.
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rcmp_itventory');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL server (without database)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>RCMP-itventory Database Setup</h2>";
    echo "<p>Setting up database...</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Database '" . DB_NAME . "' created or already exists.</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create technician table
    $sql = "CREATE TABLE IF NOT EXISTS technician (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        staff_id VARCHAR(50) NOT NULL UNIQUE,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_staff_id (staff_id),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Table 'technician' created or already exists.</p>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Create database configuration file
    $configContent = "<?php
/**
 * Database Configuration
 * 
 * IMPORTANT: Keep this file secure and do not commit to public repositories.
 */

define('DB_HOST', '" . DB_HOST . "');
define('DB_USER', '" . DB_USER . "');
define('DB_PASS', '" . DB_PASS . "');
define('DB_NAME', '" . DB_NAME . "');

/**
 * Get database connection
 * 
 * @return mysqli Database connection object
 */
function getDBConnection() {
    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (\$conn->connect_error) {
        die('Connection failed: ' . \$conn->connect_error);
    }
    
    \$conn->set_charset('utf8mb4');
    
    return \$conn;
}
";
    
    $configFile = __DIR__ . '/config.php';
    if (file_put_contents($configFile, $configContent)) {
        echo "<p style='color: green;'>✓ Database configuration file created at: database/config.php</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not create config file. Please create database/config.php manually.</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>Setup completed successfully!</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Update database credentials in database/config.php if needed</li>";
    echo "<li>Include database/config.php in your PHP files that need database access</li>";
    echo "<li>Delete or secure this setup.php file after setup</li>";
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    exit(1);
}
?>

