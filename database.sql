CREATE DATABASE IF NOT EXISTS db_customer_management;
USE db_customer_management;

CREATE TABLE IF NOT EXISTS pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data dummy awal untuk pengetesan
INSERT INTO pelanggan (nama, telepon, email, alamat) VALUES
('Andi Saputra', '081234567890', 'andi@email.com', 'Jl. Setiabudi No. 229, Bandung'),
('Budi Santoso', '089876543210', 'budi@email.com', 'Jl. Kopo No. 12, Bandung');
