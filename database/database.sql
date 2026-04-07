-- WMSU ARL Hub: Database Schema
-- Updated: 2026-04-06

CREATE DATABASE IF NOT EXISTS arl_db;
USE arl_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') DEFAULT 'student',
    profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
    is_banned BOOLEAN DEFAULT FALSE,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Materials Table
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('Reviewer', 'Textbook', 'Lecture Notes', 'Theses', 'Official Material') DEFAULT 'Reviewer',
    file_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    contributor_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_official BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    downloads_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contributor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Downloads Table
CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    material_id INT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    material_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

-- Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('upload_approved','upload_rejected','new_review','new_report','system') DEFAULT 'system',
    link VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reports (Flagged Materials) Table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('Plagiarism','Inappropriate Content','Copyright Violation','Inaccurate Information','Other') DEFAULT 'Other',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Performance Indexes
CREATE INDEX idx_materials_status    ON materials(status);
CREATE INDEX idx_materials_category  ON materials(category);
CREATE INDEX idx_downloads_user      ON downloads(user_id);
CREATE INDEX idx_notifications_user  ON notifications(user_id, is_read);
CREATE INDEX idx_reports_material    ON reports(material_id);

-- ===  SEED DATA  ===
-- Default Admin (Password: password123)
INSERT IGNORE INTO users (full_name, email, password, role)
VALUES ('WMSU ARL Admin', 'admin@wmsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Faculty (Password: password123)
INSERT IGNORE INTO users (full_name, email, password, role)
VALUES ('Dr. Maria Santos', 'faculty@wmsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty');

-- Sample Student (Password: password123)
INSERT IGNORE INTO users (full_name, email, password, role)
VALUES ('Juan dela Cruz', 'student@wmsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Sample Materials
INSERT IGNORE INTO materials (title, description, category, file_path, contributor_id, status, is_official, downloads_count, views_count)
VALUES 
('BSIT 2A Finals Reviewer', 'Complete reviewer for IT Elective 2 finals. Covers Lectures 1-12.', 'Reviewer', 'uploads/reviewer_1.pdf', 1, 'approved', false, 142, 389),
('Logic Circuits Textbook', 'Official textbook for Computer Engineering students.', 'Textbook', 'uploads/logic_circuits.pdf', 1, 'approved', true, 87, 215),
('Research Methods Lecture Notes', 'Compiled notes on research methodology and thesis writing.', 'Lecture Notes', 'uploads/research_notes.pdf', 2, 'approved', true, 63, 178),
('Network Security Reviewer', 'Key concepts and practice questions for the finals.', 'Reviewer', 'uploads/netsec_review.pdf', 3, 'pending', false, 0, 0);

-- Sample Reviews
INSERT IGNORE INTO reviews (user_id, material_id, rating, comment)
VALUES 
(3, 1, 5, 'Extremely helpful reviewer, covered everything in the finals!'),
(3, 2, 4, 'Great textbook, very comprehensive.'),
(1, 3, 5, 'Excellent compilation of research notes.');

-- Sample Audit Logs
INSERT IGNORE INTO audit_logs (user_id, action, details)
VALUES 
(1, 'Login', 'Admin logged in.'),
(1, 'approve', 'Approved material: BSIT 2A Finals Reviewer'),
(1, 'approve', 'Approved material: Logic Circuits Textbook');
