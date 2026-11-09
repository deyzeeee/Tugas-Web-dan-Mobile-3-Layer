-- Database untuk aplikasi Non-Layered (PHP Native + MySQL)
CREATE DATABASE IF NOT EXISTS db_pelanggan
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_pelanggan;

CREATE TABLE IF NOT EXISTS pelanggan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  telepon VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES
('Budi Santoso', 'budi@example.com', '081234567890', 'Jl. Merdeka No. 10, Jakarta'),
('Siti Aminah', 'siti@example.com', '082345678901', 'Jl. Sudirman No. 25, Bandung'),
('Andi Pratama', 'andi@example.com', '083456789012', 'Jl. Diponegoro No. 7, Surabaya');
