-- MySQL / MariaDB şeması: restoran web sitesi
-- Bu dosya phpMyAdmin veya `mysql` istemcisi ile içe aktarılabilir.

CREATE DATABASE IF NOT EXISTS restoran_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE restoran_db;

-- Kullanıcılar
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255),
  role VARCHAR(50) DEFAULT 'staff',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menü kategorileri
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menü öğeleri
CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(8,2) NOT NULL,
  category_id INT DEFAULT NULL,
  available TINYINT(1) DEFAULT 1,
  CONSTRAINT fk_menu_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rezervasyonlar
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  email VARCHAR(255),
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  people INT DEFAULT 1,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- İletişim mesajları
CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  message TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek veri (varsa, hatasız ekleme için INSERT IGNORE kullanıyoruz)
INSERT IGNORE INTO categories (id, name) VALUES (1, 'Başlangıçlar'), (2, 'Ana Yemekler'), (3, 'Tatlılar');

INSERT IGNORE INTO menu_items (id, name, description, price, category_id, available) VALUES
(1, 'Mercimek Çorbası', 'Ev yapımı mercimek çorbası', 45.00, 1, 1),
(2, 'Izgara Tavuk', 'Özel baharatlı ızgara tavuk, salata ile', 120.00, 2, 1),
(3, 'Baklava', 'Fıstıklı baklava', 60.00, 3, 1);
