-- Database schema for inventory system
CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('raw_material', 'semi_finished', 'finished_good', 'consumable') NOT NULL
);

-- Items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    current_stock INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT DEFAULT 0,
    unit VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Production plans table
CREATE TABLE production_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_date DATE NOT NULL,
    item_id INT,
    planned_quantity INT,
    status ENUM('planned', 'in_progress', 'completed') DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Production results table
CREATE TABLE production_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT,
    actual_quantity INT,
    result_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES production_plans(id)
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default categories
INSERT INTO categories (name, type) VALUES 
('Raw Materials', 'raw_material'),
('Semi Finished', 'semi_finished'),
('Finished Goods', 'finished_good'),
('Consumables', 'consumable');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_title', 'Inventory Control System'),
('language', 'id'),
('logo_path', 'assets/images/logo.png');

-- Insert sample items
INSERT INTO items (name, category_id, current_stock, min_stock, max_stock, unit) VALUES 
('Steel Plate', 1, 100, 20, 500, 'kg'),
('Plastic Pellets', 1, 250, 50, 1000, 'kg'),
('Semi Product A', 2, 50, 10, 200, 'pcs'),
('Final Product X', 3, 30, 5, 100, 'pcs'),
('Cleaning Supplies', 4, 15, 5, 50, 'pcs');
