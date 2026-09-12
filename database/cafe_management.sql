CREATE DATABASE IF NOT EXISTS cafe_management
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_ci;

USE cafe_management;

CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	role ENUM('admin', 'manager', 'customer') NOT NULL DEFAULT 'customer',
	status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(120) NOT NULL,
	description VARCHAR(255),
	category VARCHAR(80) NOT NULL,
	image_url VARCHAR(500),
	price DECIMAL(10, 2) NOT NULL,
	stock_quantity INT NOT NULL DEFAULT 0,
	is_available BOOLEAN NOT NULL DEFAULT TRUE,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu_items (name, description, category, image_url, price, stock_quantity, is_available) VALUES
('Cappuccino', 'Espresso with steamed milk foam', 'Coffee', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80', 4.50, 25, TRUE),
('Masala Tea', 'Black tea with warm spices and milk', 'Tea', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=800&q=80', 3.50, 25, TRUE),
('Chocolate Cake', 'Moist chocolate cake with cream', 'Dessert', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80', 5.00, 12, TRUE),
('Classic Burger', 'Grilled beef patty with fresh toppings', 'Main Course', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 8.50, 15, TRUE),
('Creamy Pasta', 'Pasta tossed in a creamy herb sauce', 'Main Course', 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=800&q=80', 9.00, 15, TRUE),
('Garden Salad', 'Crisp seasonal vegetables and dressing', 'Healthy', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', 6.50, 15, TRUE);

CREATE TABLE IF NOT EXISTS orders (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	order_type ENUM('dine-in', 'takeaway') NOT NULL,
	status ENUM('pending', 'preparing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
	total DECIMAL(10, 2) NOT NULL DEFAULT 0,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	order_id INT UNSIGNED NOT NULL,
	menu_item_id INT UNSIGNED NOT NULL,
	quantity INT NOT NULL,
	price DECIMAL(10, 2) NOT NULL,
	CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
	CONSTRAINT fk_order_items_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

CREATE TABLE IF NOT EXISTS reviews (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	menu_item_id INT UNSIGNED NOT NULL,
	rating TINYINT UNSIGNED NOT NULL,
	comment VARCHAR(500),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
	CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id),
	CONSTRAINT fk_reviews_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);
