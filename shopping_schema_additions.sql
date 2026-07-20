-- =====================================================
-- ECO MARKET SDN. BHD.
-- SHOPPING MODULE - DATABASE SCHEMA ADDITIONS
-- =====================================================
-- Run this AFTER importing ecomarket.sql.
-- ecomarket.sql already has `users`, `vendors`, `categories`,
-- and `products` (with products.category_id -> categories.id),
-- so this file only adds the shopping-flow tables:
-- cart, orders, order_items, payments.

-- ---------------------------------------------------
-- 1. Cart table
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ---------------------------------------------------
-- 2. Orders
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    shipping_name VARCHAR(150) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    payment_method ENUM('card','ewallet','cod') NOT NULL DEFAULT 'card',
    payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------
-- 3. Order Items (snapshot of product at time of order)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ---------------------------------------------------
-- 4. Payments (simulated payment gateway log)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_ref VARCHAR(40) NOT NULL UNIQUE,
    method ENUM('card','ewallet','cod') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('success','failed') NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ---------------------------------------------------
-- Optional: seed a couple of sample products so
-- Browse Products has something to show immediately.
-- Uncomment and adjust vendor_id / category_id as needed.
-- ---------------------------------------------------
-- INSERT INTO products (vendor_id, category_id, name, description, price, stock, image_url) VALUES
-- (1, 1, 'Fresh Cabbage (1kg)', 'Locally grown cabbage, harvested fresh.', 4.50, 50, NULL),
-- (1, 2, 'Ripe Bananas (1kg)', 'Sweet, ripe bananas from local farms.', 3.20, 80, NULL);
