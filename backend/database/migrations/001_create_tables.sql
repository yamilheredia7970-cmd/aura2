CREATE DATABASE IF NOT EXISTS aura_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aura_ecommerce;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  image_url VARCHAR(500) NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  short_description VARCHAR(255) NULL,
  price DECIMAL(10,2) NOT NULL,
  old_price DECIMAL(10,2) NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0,
  review_count INT UNSIGNED NOT NULL DEFAULT 0,
  image_url VARCHAR(500) NULL,
  is_new TINYINT(1) NOT NULL DEFAULT 0,
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_category (category_id),
  INDEX idx_products_price (price)
) ENGINE=InnoDB;

CREATE TABLE product_variants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  size VARCHAR(20) NOT NULL,
  color VARCHAR(20) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_variant (product_id, size, color)
) ENGINE=InnoDB;

CREATE TABLE carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  guest_token CHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_cart_user (user_id),
  UNIQUE KEY uniq_cart_guest (guest_token)
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_cart_item (cart_id, variant_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('pending', 'paid', 'shipped', 'cancelled') NOT NULL DEFAULT 'pending',
  total DECIMAL(10,2) NOT NULL,
  shipping_address VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  size VARCHAR(20) NOT NULL,
  color VARCHAR(20) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  gateway VARCHAR(40) NOT NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  external_reference VARCHAR(120) NULL,
  amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(180) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attempts_lookup (email, ip_address, attempted_at)
) ENGINE=InnoDB;
