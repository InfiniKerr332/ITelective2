-- WMSU ARL Hub: Database Migration
-- Run this on your existing arl_db to add missing tables/columns
-- Updated: 2026-04-08

USE arl_db;

-- ─── 1. Add is_verified column to users ───
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='arl_db' AND TABLE_NAME='users' AND COLUMN_NAME='is_verified');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 1 AFTER is_banned', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─── 2. Add first_name, last_name columns ───
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='arl_db' AND TABLE_NAME='users' AND COLUMN_NAME='first_name');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE users ADD COLUMN first_name VARCHAR(100) DEFAULT NULL AFTER full_name', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='arl_db' AND TABLE_NAME='users' AND COLUMN_NAME='last_name');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE users ADD COLUMN last_name VARCHAR(100) DEFAULT NULL AFTER first_name', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─── 3. Update materials category ENUM to support all types ───
ALTER TABLE materials MODIFY COLUMN category ENUM(
    'Reviewer','Textbook','Lecture Notes','Theses','Official Material',
    'Modules','Handouts','Past Exams','Research','Thesis'
) DEFAULT 'Modules';

-- ─── 4. Create material_files table for multi-file upload support ───
CREATE TABLE IF NOT EXISTS material_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(20),
    file_size BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

-- ─── 5. Add report description column if missing ───
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='arl_db' AND TABLE_NAME='reports' AND COLUMN_NAME='description');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE reports ADD COLUMN description TEXT AFTER reason', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─── 6. Safe index creation (wrapped in procedures to avoid duplicate errors) ───
DROP PROCEDURE IF EXISTS safe_create_index;
DELIMITER //
CREATE PROCEDURE safe_create_index()
BEGIN
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END; -- Duplicate key name
    CREATE INDEX idx_material_files_material ON material_files(material_id);
END //
DELIMITER ;
CALL safe_create_index();
DROP PROCEDURE IF EXISTS safe_create_index;
