-- Data Warehouse (Star Schema) for Food Ordering System
-- Creates a simple star schema in database `food_dw`
-- Run: mysql -u user -p < dw_schema.sql

CREATE DATABASE IF NOT EXISTS food_dw;
USE food_dw;

-- Date dimension
CREATE TABLE IF NOT EXISTS dim_date (
    date_id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    year_small INT,
    month_small INT,
    day_small INT,
    quarter_small INT,
    weekday_name VARCHAR(16)
);

-- User/customer dimension (keeps natural key from source)
CREATE TABLE IF NOT EXISTS dim_user (
    user_sk INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    username VARCHAR(100),
    email VARCHAR(150),
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50)
);

-- Category dimension
CREATE TABLE IF NOT EXISTS dim_category (
    category_sk INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL UNIQUE,
    name VARCHAR(150),
    description TEXT
);

-- Product / Food item dimension
CREATE TABLE IF NOT EXISTS dim_product (
    product_sk INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL UNIQUE,
    name VARCHAR(200),
    category_sk INT,
    price DECIMAL(10,2),
    is_vegetarian BOOLEAN,
    is_available BOOLEAN,
    FOREIGN KEY (category_sk) REFERENCES dim_category(category_sk)
);

-- Fact table: order line items (grain = one order item)
CREATE TABLE IF NOT EXISTS fact_order_items (
    fact_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    order_number VARCHAR(50),
    order_date_id INT,
    user_sk INT,
    product_sk INT,
    quantity INT,
    price DECIMAL(10,2),
    subtotal DECIMAL(12,2),
    payment_status VARCHAR(32),
    order_status VARCHAR(32),
    created_at DATETIME,
    INDEX idx_order_date_id (order_date_id),
    INDEX idx_user_sk (user_sk),
    INDEX idx_product_sk (product_sk)
);

-- Basic notes: this DW is intentionally simple and co-located on the same MySQL server.
-- The ETL script (etl_load.sql) populates dims using INSERT IGNORE / INSERT ... ON DUPLICATE KEY UPDATE
-- and then loads new fact rows by skipping already-loaded order_id/product combinations.
