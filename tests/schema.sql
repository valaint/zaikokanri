-- Test database schema for zaikokanri (SQLite)

CREATE TABLE IF NOT EXISTS article_info (
    article_id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_name TEXT NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    threshold INTEGER NOT NULL DEFAULT 0,
    category_id INTEGER DEFAULT NULL,
    contact_id INTEGER DEFAULT NULL,
    display_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS history (
    history_id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    original_value INTEGER NOT NULL,
    updated_value INTEGER NOT NULL,
    from_barcode INTEGER NOT NULL DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS error_log (
    error_id INTEGER PRIMARY KEY AUTOINCREMENT,
    error_message TEXT,
    query TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS category (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS contact (
    contact_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS barcode_list (
    barcode_id INTEGER PRIMARY KEY AUTOINCREMENT,
    barcode TEXT NOT NULL,
    article_id INTEGER NOT NULL,
    destock_count INTEGER NOT NULL DEFAULT 1,
    is_prompt INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS stock_log (
    stock_log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_id INTEGER NOT NULL,
    original_stock INTEGER NOT NULL,
    updated_stock INTEGER NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
