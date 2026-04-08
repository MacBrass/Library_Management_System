-- ============================================================
-- Library Management System - Database Schema
-- Fr. CRCE, Bandra, Mumbai
-- ============================================================
-- Run this SQL in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- ============================================================
-- Table: users
-- Stores all system users (admin, professor, student)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'professor', 'student') NOT NULL DEFAULT 'student',
    department VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: books
-- Stores all book records in the library
-- ============================================================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(200) NOT NULL,
    isbn VARCHAR(20) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    publisher VARCHAR(200) DEFAULT NULL,
    year INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    available INT NOT NULL DEFAULT 1,
    cover_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: requests
-- Tracks book request lifecycle:
-- requested -> approved/rejected -> issued -> returned
-- ============================================================
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('requested', 'approved', 'rejected', 'issued', 'returned') NOT NULL DEFAULT 'requested',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date DATETIME DEFAULT NULL,
    issued_date DATETIME DEFAULT NULL,
    return_date DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: borrow_history
-- Permanent record of all borrowing transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS borrow_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATETIME NOT NULL,
    return_date DATETIME DEFAULT NULL,
    fine DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: fine_settings
-- Configurable fine rules (admin can update)
-- ============================================================
CREATE TABLE IF NOT EXISTS fine_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(100) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default fine settings
INSERT INTO fine_settings (setting_key, setting_value) VALUES
('fine_mode', 'per_day'),
('borrow_period', '14'),
('student_fine_per_day', '5'),
('professor_fine_per_day', '3'),
('student_fixed_fine', '50'),
('professor_fixed_fine', '30'),
('no_return_days', '60'),
('no_return_fine', '500'),
('currency', '₹');

-- ============================================================
-- Table: receipts
-- Digital receipts for book issues and returns
-- ============================================================
CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(30) NOT NULL UNIQUE,
    type ENUM('issue', 'return', 'fine') NOT NULL,
    request_id INT NOT NULL,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATETIME DEFAULT NULL,
    due_date DATETIME DEFAULT NULL,
    return_date DATETIME DEFAULT NULL,
    fine DECIMAL(10,2) DEFAULT 0.00,
    late_days INT DEFAULT 0,
    fine_mode VARCHAR(20) DEFAULT 'per_day',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Insert Sample Users
-- NOTE: These passwords are placeholder hashes.
-- After importing this SQL, visit http://localhost/library-system/setup.php
-- to generate correct bcrypt password hashes.
-- ============================================================

-- Admin: admin@frcrce.ac.in / Admin123
INSERT INTO users (name, email, password, role, department, status) VALUES
('Admin User', 'admin@frcrce.ac.in', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfc7T5UYX3h4k9xG0kCQ0ep5QgYfKyVu', 'admin', 'Administration', 'active');

-- Professor: prof@frcrce.ac.in / Prof1234
INSERT INTO users (name, email, password, role, department, status) VALUES
('Prof. Sharma', 'prof@frcrce.ac.in', '$2y$10$Nh8JzV7z8K9L2xQ3eUYVxeS5c3T5hGEqMzmVkLXoJrBp5KpVNfjdC', 'professor', 'Computer Science', 'active');

-- Student: student@frcrce.ac.in / Student123
INSERT INTO users (name, email, password, role, department, status) VALUES
('Rahul Verma', 'student@frcrce.ac.in', '$2y$10$GZ8KQOYqKJEf4v5h3t2CnO8V8d5yX7kLp2Qr9NmEAJfW3bGhRfS0y', 'student', 'Computer Science', 'active');

-- ============================================================
-- Insert Sample Books
-- ============================================================
INSERT INTO books (title, author, isbn, category, publisher, year, quantity, available) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Computer Science', 'MIT Press', 2009, 5, 5),
('Database System Concepts', 'Abraham Silberschatz', '9780078022159', 'Computer Science', 'McGraw Hill', 2019, 3, 3),
('Operating System Concepts', 'Abraham Silberschatz', '9781119800361', 'Computer Science', 'Wiley', 2021, 4, 4),
('Clean Code', 'Robert C. Martin', '9780132350884', 'Software Engineering', 'Pearson', 2008, 3, 3),
('The Pragmatic Programmer', 'Andrew Hunt', '9780135957059', 'Software Engineering', 'Addison-Wesley', 2019, 2, 2),
('Data Structures Using C', 'Reema Thareja', '9780198099307', 'Computer Science', 'Oxford', 2014, 6, 6),
('Computer Networks', 'Andrew Tanenbaum', '9780132126953', 'Networking', 'Pearson', 2010, 3, 3),
('Artificial Intelligence: A Modern Approach', 'Stuart Russell', '9780134610993', 'Artificial Intelligence', 'Pearson', 2020, 4, 4),
('Design Patterns', 'Erich Gamma', '9780201633610', 'Software Engineering', 'Addison-Wesley', 1994, 2, 2),
('Machine Learning', 'Tom Mitchell', '9780070428072', 'Artificial Intelligence', 'McGraw Hill', 1997, 3, 3);
