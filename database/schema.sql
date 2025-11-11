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

-- Show table structure
DESCRIBE technician;

