-- RCMP-itventory Database Schema
-- Run this SQL script to create the database and tables manually

-- Create database
CREATE DATABASE IF NOT EXISTS rcmp_itventory 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE rcmp_itventory;

-- Create technician table
CREATE TABLE IF NOT EXISTS technician (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique staff identification number',
    full_name VARCHAR(255) NOT NULL COMMENT 'Full name of the technician',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email address (must be unique)',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX idx_staff_id (staff_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique category name',
    description TEXT COMMENT 'Category description',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Category creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX idx_category_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create equipment table (Master table for all equipment data from CSV or manual entry)
CREATE TABLE IF NOT EXISTS equipment (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique equipment identifier (Asset ID)',
    equipment_name VARCHAR(255) NOT NULL COMMENT 'Equipment name',
    category_id INT(11) UNSIGNED NOT NULL COMMENT 'Foreign key to categories table',
    brand VARCHAR(100) COMMENT 'Equipment brand',
    model VARCHAR(100) COMMENT 'Equipment model',
    serial_number VARCHAR(100) NOT NULL COMMENT 'Serial number (REQUIRED)',
    processor VARCHAR(100) COMMENT 'Processor specification',
    operating_system VARCHAR(100) COMMENT 'Operating System',
    year INT COMMENT 'Year (TAHUN)',
    adapter VARCHAR(100) COMMENT 'Adapter information',
    warranty_end DATE COMMENT 'Warranty End date',
    remark TEXT COMMENT 'Remarks/Notes',
    total DECIMAL(10,2) COMMENT 'Total value',
    -- Staff/Employee Information (who the equipment is assigned to)
    -- For CSV imports: Staff info from CSV
    -- For handovers: Staff info becomes the lecturer/recipient info
    -- If staff_name is NULL/empty, equipment is available for handover. If set, equipment is handed over.
    staff_id VARCHAR(50) COMMENT 'Staff/Lecturer ID (from CSV or handover recipient)',
    staff_name VARCHAR(255) COMMENT 'Staff/Lecturer Name (from CSV or handover recipient). If NULL/empty, equipment is available for handover.',
    designation VARCHAR(100) COMMENT 'Designation',
    staff_email VARCHAR(255) COMMENT 'Staff/Lecturer Email (from CSV or handover recipient)',
    employment_type VARCHAR(100) COMMENT 'Employment Type',
    job_category VARCHAR(100) COMMENT 'Job Category',
    dept_lvl3 VARCHAR(100) COMMENT 'Department Level 3 (DEPTLVL3)',
    dept_lvl4 VARCHAR(100) COMMENT 'Department Level 4 (DEPTLVL4)',
    source VARCHAR(50) DEFAULT 'manual' COMMENT 'Source: manual or csv',
    -- Handover Information (merged from handover table)
    pickup_date DATE COMMENT 'Pickup/Handover date',
    return_date DATE COMMENT 'Return date (when equipment was returned)',
    handover_status VARCHAR(50) DEFAULT NULL COMMENT 'Handover status: pending, picked_up, returned, or NULL if not handed over',
    handover_staff VARCHAR(50) COMMENT 'Staff ID who performed the handover',
    return_staff VARCHAR(50) COMMENT 'Staff ID who received the return',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_category_id (category_id),
    INDEX idx_serial_number (serial_number),
    INDEX idx_staff_id (staff_id),
    INDEX idx_staff_name (staff_name),
    INDEX idx_source (source),
    INDEX idx_handover_status (handover_status),
    INDEX idx_pickup_date (pickup_date),
    INDEX idx_handover_staff (handover_staff),
    INDEX idx_return_staff (return_staff),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create inventory table (References equipment table)
CREATE TABLE IF NOT EXISTS inventory (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'Foreign key to equipment table',
    status VARCHAR(50) DEFAULT 'Available' COMMENT 'Equipment status (inventory-specific)',
    location VARCHAR(255) COMMENT 'Equipment location (Department) - inventory-specific',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_status (status),
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories if they don't exist
INSERT IGNORE INTO categories (category_name, description) VALUES
('Laptops', 'Portable computers and laptops'),
('Projectors', 'Projection equipment and displays'),
('Monitors', 'Computer monitors and displays'),
('Printers', 'Printing equipment'),
('Tablets', 'Tablet devices'),
('Accessories', 'Computer accessories and peripherals'),
('Cables & Adapters', 'Cables, adapters, and connectors'),
('Networking', 'Network equipment and devices'),
('Audio/Visual', 'Audio and visual equipment'),
('Other', 'Other equipment and miscellaneous items');

-- Note: Handover table has been merged into equipment table
-- Equipment availability is determined by staff_name field:
-- - If staff_name is NULL/empty: Equipment is available for handover
-- - If staff_name is set: Equipment is already handed over to that staff/lecturer

-- Show table structures
DESCRIBE technician;
DESCRIBE categories;
DESCRIBE equipment;
DESCRIBE inventory;
