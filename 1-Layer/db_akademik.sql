-- Database untuk aplikasi Non-Layered (PHP Native + MySQL)
CREATE DATABASE IF NOT EXISTS db_akademik
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_akademik;

CREATE TABLE IF NOT EXISTS mahasiswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nim VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  jurusan VARCHAR(80) NOT NULL,
  angkatan YEAR NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO mahasiswa (nim, nama, email, jurusan) VALUES
('2504531', 'Faiz Muhammad Ghazali', 'paesGhazali9@index.php', 'Ilmu Komputer'),
('2504418', 'Abdillah Ridho Alriza', 'abdul666@yahoo.di', 'Ilmu Komputer'),
('2508874', 'Hafiz Rifai', 'hafizrifai@mahasiswa.itb.ac.id', 'STEI-K');
