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

-- Create inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique equipment identifier',
    equipment_name VARCHAR(255) NOT NULL COMMENT 'Equipment name',
    category_id INT(11) UNSIGNED NOT NULL COMMENT 'Foreign key to categories table',
    brand VARCHAR(100) COMMENT 'Equipment brand',
    model VARCHAR(100) COMMENT 'Equipment model',
    serial_number VARCHAR(100) COMMENT 'Serial number',
    status VARCHAR(50) DEFAULT 'Available' COMMENT 'Equipment status',
    location VARCHAR(255) COMMENT 'Equipment location',
    description TEXT COMMENT 'Equipment description',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
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
('Audio/Visual', 'Audio and visual equipment');

-- Show table structures
DESCRIBE technician;
DESCRIBE categories;
DESCRIBE inventory;

