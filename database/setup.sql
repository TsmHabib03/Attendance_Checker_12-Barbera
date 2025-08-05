-- Attendance Checker Database Setup
-- This file creates the database and tables needed for the attendance system

CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Students table to store student information
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    class VARCHAR(50) NOT NULL,
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table to track attendance records
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('present', 'late') DEFAULT 'present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    UNIQUE KEY unique_attendance (student_id, date)
);

-- Admin users table (optional for admin authentication)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin user (username: admin, password: admin123)
-- Note: In production, use proper password hashing!
INSERT INTO admin_users (username, password) VALUES 
('admin', MD5('admin123'));

-- Sample students for testing
INSERT INTO students (student_id, first_name, last_name, email, class) VALUES
('STU001', 'John', 'Doe', 'john.doe@school.com', '12-A'),
('STU002', 'Jane', 'Smith', 'jane.smith@school.com', '12-A'),
('STU003', 'Mike', 'Johnson', 'mike.johnson@school.com', '12-B');
