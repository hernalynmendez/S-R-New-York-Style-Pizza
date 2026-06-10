-- Food Ordering System Database Schema
-- Created: 2026

CREATE DATABASE IF NOT EXISTS food_ordering_system;
USE food_ordering_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    country VARCHAR(50),
    profile_image VARCHAR(255),
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_search (first_name, last_name, email)
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Food Items Table
CREATE TABLE IF NOT EXISTS food_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    is_vegetarian BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    preparation_time INT DEFAULT 30,
    quantity_in_stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FULLTEXT INDEX ft_search (name, description),
    INDEX idx_category (category_id),
    INDEX idx_available (is_available)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    delivery_address TEXT,
    delivery_city VARCHAR(50),
    delivery_state VARCHAR(50),
    delivery_postal_code VARCHAR(10),
    payment_method VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'pending',
    order_status VARCHAR(50) DEFAULT 'pending',
    previous_status VARCHAR(50) DEFAULT NULL,
    special_instructions TEXT,
    estimated_delivery_time DATETIME,
    delivered_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (order_status),
    INDEX idx_created (created_at)
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id),
    INDEX idx_order (order_id)
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL UNIQUE,
    transaction_id VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_date DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
);

-- Ratings and Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_food (food_id),
    INDEX idx_user (user_id)
);

-- Delivery Partners Table (Optional for future use)
CREATE TABLE IF NOT EXISTS delivery_partners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    vehicle_type VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    current_orders INT DEFAULT 0,
    total_deliveries INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 5.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin Activity Log Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id),
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Burgers', 'Delicious burgers with various toppings'),
('Pizzas', 'Freshly baked pizzas with premium ingredients'),
('Salads', 'Healthy salads with fresh vegetables'),
('Desserts', 'Sweet treats and delicious desserts'),
('Beverages', 'Refreshing drinks and beverages'),
('Appetizers', 'Starters and appetizers');

-- admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, is_admin, is_active) VALUES
('admin', 'admin@foodsystem.com', '$2y$10$YourHashedPasswordHere', 'Admin', 'User', TRUE, TRUE);

-- Insert sample food items
INSERT INTO food_items (category_id, name, description, price, image, is_vegetarian, is_available, preparation_time, quantity_in_stock) VALUES
(1, 'Classic Burger', 'Juicy beef patty with lettuce, tomato, and cheese', 49.99, 'classicburger.jpg', FALSE, TRUE, 15, 50),
(1, 'Veggie Burger', 'Plant-based patty with fresh vegetables', 79.99, 'veggie burger.jpg', TRUE, TRUE, 15, 30),
(2, 'Margherita Pizza', 'Fresh mozzarella, basil, and tomato sauce', 249.99, 'magherita pizza.jpg', TRUE, TRUE, 20, 25),
(2, 'Pepperoni Pizza', 'Classic pepperoni with cheese and sauce', 199.99, 'Pepperoni Pizza.jpg', FALSE, TRUE, 20, 20),
(3, 'Caesar Salad', 'Crisp romaine lettuce with caesar dressing', 169.99, 'caesar salad.jpg', TRUE, TRUE, 5, 40),
(4, 'Chocolate Cake', 'Decadent chocolate cake with frosting', 139.99, 'chocolate cake.jpg', FALSE, TRUE, 10, 15),
(5, 'Soft Drink', 'Refreshing soft beverages', 49.49, 'softdrinks.jpg', FALSE, TRUE, 1, 100),
(6, 'Chicken Wings', 'Crispy fried wings with sauce', 299.99, 'chicken wings.jpg', FALSE, TRUE, 10, 35);
