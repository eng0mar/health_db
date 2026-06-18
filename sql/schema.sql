-- ============================================
-- National Health Database System
-- Database Schema - national_health_db
-- ============================================

-- Use el database
CREATE DATABASE IF NOT EXISTS health_db;
USE health_db;

-- ============================================
-- Users Table
-- Stores all users: admin, doctor, patient
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor','patient') NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Medical Records Table
-- Kol record marbout b patient w doctor
-- ============================================
CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    notes TEXT DEFAULT NULL,
    visit_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Prescriptions Table
-- Kol prescription marbout b medical record
-- ============================================
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    medication_name VARCHAR(200) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    instructions TEXT DEFAULT NULL,
    prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES medical_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert default admin user
-- Password: admin123 (hashed)
-- ============================================
INSERT INTO users (name, email, password, role, phone) VALUES
('System Admin', 'admin@health.com', '$2y$10$mt0ZM3lZeOlKF2OYXnEiT.jUTi/Uvhhl5dh8MB3vX4KuRA0c6loF2', 'admin', '01000000000');
-- Password: admin123
