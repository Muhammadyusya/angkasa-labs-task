-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS angkasa_labs;
USE angkasa_labs;

-- Buat tabel tugas (Merujuk ke F4: Manajemen Proyek & Tugas)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_klien VARCHAR(100) NOT NULL,
    pic VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    prioritas ENUM('Rendah', 'Sedang', 'Tinggi') DEFAULT 'Sedang',
    status ENUM('Pending', 'Dalam Proses', 'Selesai') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Masukkan 2 data dummy untuk ngetes UI
INSERT INTO tasks (nama_klien, pic, deskripsi, prioritas, status) VALUES
('Bimasakti Tech', 'Adrian Wirawan', 'Integrasi API Cloud untuk sistem logistik', 'Tinggi', 'Dalam Proses'),
('Nusantara Karya', 'Siska Amelia', 'Audit keamanan siber bulanan', 'Sedang', 'Pending');