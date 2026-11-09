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
('Hafiz Rifai', '0812345678910', 'hafizganteng123@email.com', 'Jl. Setiabudi No. 229, Bandung'),
('Abdillah Ridho', '089876543210', 'ridhogemink@email.com', 'Jl. Jawa Barat No. 12, Bandung'),
('Faiz Muhammad Ghazali', '1285796341', 'faizemge@gmail.com', 'Jl. Cihampelas No. 45, Bandung'),
('Irsyad Afif', '4456789109', 'irsyad@yahoo.id', 'Jl. Geger Kalong No. 12, Bandung'),
('Muhammad Fadey Rafif', '1267458910', 'fadeypakef@email.com', 'Jl. Kanan Kiri No. 33, Bandung');
