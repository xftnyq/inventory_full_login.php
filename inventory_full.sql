-- 1. DROP and CREATE the Database
DROP DATABASE IF EXISTS inventory_full;
CREATE DATABASE inventory_full;
USE inventory_full;

-- 2. Create All Tables

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'User') DEFAULT 'Admin'
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active'
);

CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(20),
    address TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active'
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    address TEXT,
    mobile VARCHAR(20),
    balance DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    brand_id INT,
    supplier_id INT,
    name VARCHAR(150) NOT NULL,
    model VARCHAR(50),
    quantity INT DEFAULT 0,
    base_price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    supplier_id INT,
    quantity INT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    customer_id INT,
    total_item INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- 3. Insert Sample Data
INSERT INTO users (username, password, role) VALUES ('admin', MD5('admin'), 'Admin');
INSERT INTO categories (name) VALUES 
('Electronics'), ('Home Appliances'), ('Office Supplies'), ('Gadgets'), ('Clothing'), ('Books'), ('Food & Beverages');
INSERT INTO suppliers (name, mobile, address, status) VALUES 
('Tech Supply Inc.', '09123456789', 'Makati City', 'Active'), ('Appliance Central Corp.', '09229876543', 'Pasig City', 'Active'), 
('Office Dynamics Trading', '09331112222', 'Quezon City', 'Active'), ('Global Fabrics Ltd.', '09190001000', 'Mandaluyong City', 'Active'), 
('Bookworm Distributors', '09457778888', 'Taguig City', 'Active'), ('Fresh Grocers Supply', '09187654321', 'Cavite City', 'Active');
INSERT INTO brands (category_id, name, status) VALUES 
(1, 'Apple', 'Active'), (1, 'Samsung', 'Active'), (2, 'LG', 'Active'), (3, 'Pilot', 'Active'), (5, 'Levi\'s', 'Active'), 
(2, 'Sharp', 'Active'), (7, 'Healthy Harvest', 'Active'), (7, 'Fizzy Drinks Co.', 'Active'); 
INSERT INTO customers (name, address, mobile, balance) VALUES 
('Mark Cooper', 'Sample Address 1', '2147483647', 25000.00), ('Elena Rodriguez', 'Cebu Business Park', '09175551234', 500.00), 
('John Michael Lee', 'BGC Taguig', '09987654321', 12000.50), ('Sofia Dela Cruz', 'Alabang Muntinlupa', '09051234567', 1500.00), 
('Robert Tan', 'Binondo Manila', '09209876543', 0.00);
INSERT INTO products (category_id, brand_id, supplier_id, name, model, quantity, base_price, status) VALUES
(1, 1, 1, 'iPhone 15 Pro', 'A2848', 50, 999.00, 'Active'), (1, 2, 1, 'Galaxy S24 Ultra', 'SM-S928', 30, 850.00, 'Active'), 
(2, 3, 2, 'Inverter Refrigerator', 'GR-B200', 15, 650.00, 'Active'), (3, 4, 3, 'G2 Gel Pen (Black)', 'G2-7', 200, 1.50, 'Active'), 
(1, 1, 1, 'MacBook Air M3 13"', 'A3113', 20, 1099.00, 'Active'), (5, 5, 4, 'Men\'s Denim Jeans', '501-SLIM', 75, 50.00, 'Active'), 
(2, 6, 2, 'Smart LED TV 55-inch', '55LEDUHD', 10, 450.00, 'Active'), (6, NULL, 5, 'Intro to SQL', NULL, 40, 35.00, 'Active'), 
(7, 7, 6, 'Organic Trail Mix 500g', 'TM-500', 500, 8.50, 'Active'), (7, 8, 6, 'Sparkling Water (Case)', 'SW-24', 150, 15.00, 'Active'), 
(7, 7, 6, 'Protein Energy Bar', 'PEB-20', 1000, 2.00, 'Active'); 
INSERT INTO purchases (product_id, supplier_id, quantity, price, total_cost) 
VALUES 
(1, 1, 10, 900.00, 9000.00), (2, 1, 5, 800.00, 4000.00), (4, 3, 50, 1.00, 50.00), (6, 4, 25, 40.00, 1000.00), 
(7, 2, 5, 400.00, 2000.00), (9, 6, 100, 6.50, 650.00), (10, 6, 30, 12.00, 360.00), (11, 6, 200, 1.20, 240.00); 
INSERT INTO orders (product_id, customer_id, total_item) VALUES 
(1, 1, 2), (2, 2, 1), (3, 3, 1), (6, 4, 3), (9, 5, 5), (11, 1, 10);