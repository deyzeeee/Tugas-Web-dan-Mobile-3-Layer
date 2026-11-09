-- Database untuk aplikasi Non-Layered (PHP Native + MySQL)
CREATE DATABASE IF NOT EXISTS db_pelanggan
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_pelanggan;

CREATE TABLE IF NOT EXISTS pelanggan (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  telepon VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES
('Faiz Muhammad Ghazali', 'paesGhazali@index.php', '081234567890', 'Jl. Gunung Lawu, Jakarta'),
('Abdillah Ridho Alriza', 'abdul666@yahoo.id', '082345678901', 'Jl. H. Saodah, Bandung'),
('Hafiz Rifai', 'hafizrifai@mahasiswa.itb.ac.id', '083456789012', 'Jl. Sudirman, Sukabumi');
