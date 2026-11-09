# Customer Management System - 3 Layer

## Menjalankan di XAMPP
1. Ekstrak folder `tugas-3layer` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, lalu import `database.sql`.
4. Buka `http://localhost/tugas-3layer/`.

Jangan membuka `index.html` langsung melalui `file://`. Jalankan melalui Apache agar JavaScript dapat mengakses `backend/api.php`.

## Struktur dan alur
- Presentation layer: `index.html`, `ManipulasiData.html`, `style.css`, `js/app.js`
- Business logic layer: `backend/services/PelangganService.php`
- Data access layer: `backend/data/PelangganRepository.php`
- API/controller: `backend/api.php`
- Database connection: `backend/config/Database.php`
- Database schema & sample data: `database.sql`

Frontend menggunakan Fetch API dan JSON ke endpoint relatif `backend/api.php`. Operasi yang tersedia: GET daftar/cari, POST tambah, PUT edit, DELETE hapus. Data wajib: nama, telepon, email, alamat.

## Catatan
Konfigurasi koneksi MySQL default di `backend/config/Database.php` menggunakan user `root` tanpa password, sesuai konfigurasi XAMPP standar. Sesuaikan jika konfigurasi MySQL lokal berbeda.
