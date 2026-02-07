-- Test database schema for zaikokanri
-- Run: mysql -u root -p zaikokanri_test < tests/schema.sql

CREATE TABLE IF NOT EXISTS article_info (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    article_name VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    threshold INT NOT NULL DEFAULT 0,
    category_id INT DEFAULT NULL,
    contact_id INT DEFAULT NULL,
    display_order INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    type VARCHAR(10) NOT NULL,
    original_value INT NOT NULL,
    updated_value INT NOT NULL,
    from_barcode TINYINT NOT NULL DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS error_log (
    error_id INT AUTO_INCREMENT PRIMARY KEY,
    error_message TEXT,
    query TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS contact (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS barcode_list (
    barcode_id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(255) NOT NULL,
    article_id INT NOT NULL,
    destock_count INT NOT NULL DEFAULT 1,
    is_prompt TINYINT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS stock_log (
    stock_log_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    original_stock INT NOT NULL,
    updated_stock INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
