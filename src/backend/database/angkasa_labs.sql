CREATE DATABASE angkasa_labs;
USE angkasa_labs;

-- Tabel Pengguna/User
CREATE TABLE pengguna (
    id_pengguna VARCHAR(255) PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'tim', 'klien', 'freelancer') NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    status_akun ENUM('aktif', 'nonaktif') NOT NULL
);

CREATE TABLE klien (
    id_klien VARCHAR(255) PRIMARY KEY,
    nama_klien VARCHAR(100) NOT NULL,
    nama_brand VARCHAR(100) NOT NULL,
    bidang_usaha VARCHAR(100) NOT NULL,
    kontak VARCHAR(50) NOT NULL,
    alamat TEXT NOT NULL,
    akun_ig VARCHAR(100),
    akun_tiktok VARCHAR(100),
    akun_facebook VARCHAR(100)
);

-- Tabel Manajemen Proyek
CREATE TABLE proyek (
    id_proyek VARCHAR(255) PRIMARY KEY,
    id_klien VARCHAR(255),
    nama_proyek VARCHAR(100) NOT NULL,
    tujuan TEXT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE,
    status_proyek ENUM('draft', 'berjalan', 'selesai', 'ditunda') NOT NULL,
    anggaran DECIMAL(15, 2),
    FOREIGN KEY (id_klien) REFERENCES klien(id_klien) ON DELETE CASCADE
);

CREATE TABLE tugas (
    id_tugas VARCHAR(255) PRIMARY KEY,
    id_proyek VARCHAR(255),
    id_penanggung_jawab VARCHAR(255),
    judul_tugas VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    deadline DATETIME,
    prioritas ENUM('rendah', 'sedang', 'tinggi') NOT NULL,
    status_tugas ENUM('belum mulai', 'sedang berjalan', 'selesai') NOT NULL,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) on DELETE CASCADE,
    FOREIGN KEY (id_penanggung_jawab) REFERENCES pengguna(id_pengguna) on DELETE CASCADE
);

CREATE TABLE konten (
    id_konten VARCHAR(255) PRIMARY KEY,
    id_proyek VARCHAR(255),
    judul_konten VARCHAR(100) NOT NULL,
    jenis_konten ENUM('feed', 'story', 'reels', 'tiktok', 'short') NOT NULL,
    caption TEXT,
    link_file VARCHAR(255),
    status_approval ENUM('draft', 'menunggu', 'revisi', 'disetujui') NOT NULL,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) on DELETE CASCADE
);

-- Tabel Pendukung
CREATE TABLE jadwal_publish (
    id_jadwal VARCHAR(255) PRIMARY KEY,
    id_konten VARCHAR(255),
    platform ENUM('instagram', 'tiktok', 'facebook', 'youtube') NOT NULL,
    waktu_publish DATETIME NOT NULL,
    status_publish ENUM('terjadwal', 'berhasil', 'gagal') NOT NULL,
    FOREIGN KEY (id_konten) REFERENCES konten(id_konten) on DELETE CASCADE
);

CREATE TABLE kpi_campaign (
    id_kpi VARCHAR(255) PRIMARY KEY,
    id_proyek VARCHAR(255),
    periode VARCHAR(50) NOT NULL,
    platform ENUM('instagram', 'tiktok', 'facebook', 'youtube') NOT NULL,
    impression INT,
    reach INT,
    clicks INT,
    ctr FLOAT,
    engagement_rate FLOAT,
    cpm FLOAT,
    roi FLOAT,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) on DELETE CASCADE
);

CREATE TABLE laporan (
    id_laporan VARCHAR(255) PRIMARY KEY,
    id_proyek VARCHAR(255) NOT NULL,
    periode VARCHAR(20),
    tgl_dibuat DATE,
    ringkasan TEXT,
    file_laporan VARCHAR(255),
    FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE
);


-- Data Dummy (buat testing)
-- pengguna
INSERT INTO pengguna (id_pengguna, nama, email, password, role, no_hp, status_akun) VALUES
("A0001", 'M. Yusya Yanwar M', 'yusya@angkasalabs.com', 'password123', 'admin', '08111222333', 'aktif'),
("A0002", 'Zelda Azahra Putri', 'zelda@angkasalabs.com', 'password123', 'tim', '08222333444', 'aktif'),
("A0003", 'Siti Julianti', 'siti@angkasalabs.com', 'password123', 'tim', '08333444555', 'aktif'),
("A0004", 'M. Ikbal Alwi F', 'ikbal@angkasalabs.com', 'password123', 'tim', '08444555666', 'aktif');

-- klien
INSERT INTO klien (id_klien, nama_klien, nama_brand, bidang_usaha, kontak, alamat, akun_ig, akun_tiktok, akun_facebook) VALUES
('K0001', 'Budi Santoso', 'PT Maju Jaya', 'Retail & Fashion', '08551234567', 'Jl. Merdeka No. 1, Yogyakarta', 'majujaya.id', 'majujaya_tok', 'Maju Jaya Official'),
('K0002', 'Andi Susanto', 'CV Sukses Mandiri', 'Food & Beverage', '08997654321', 'Jl. Sudirman No. 2, Yogyakarta', 'sukses.mandiri', 'suksesmandiri.id', 'Sukses Mandiri FB');

-- proyek
INSERT INTO proyek (id_proyek, id_klien, nama_proyek, tujuan, tanggal_mulai, tanggal_selesai, status_proyek, anggaran) VALUES
('P0001', 'K0001', 'Brand Campaign Q4', 'Meningkatkan brand awareness', '2026-04-01', '2026-06-30', 'berjalan', 50000000.00),
('P0002', 'K0002', 'Product Launch Social Media', 'Promosi peluncuran menu baru', '2026-04-15', '2026-05-15', 'berjalan', 30000000.00);

-- tugas
INSERT INTO tugas (id_tugas, id_proyek, id_penanggung_jawab, judul_tugas, deskripsi, deadline, prioritas, status_tugas) VALUES
('T0001', 'P0001', 'A0001', 'Membuat desain feed IG promo', 'Desain 3 carousel untuk promo akhir tahun.', '2026-04-25 10:00:00', 'tinggi', 'belum mulai'),
('T0002', 'P0001', 'A0002', 'Copywriting Ads Facebook', 'Bikin caption yang mengundang interaksi audiens.', '2026-04-25 15:00:00', 'sedang', 'sedang berjalan'),
('T0003', 'P0001', 'A0003', 'Edit video Reels', 'Durasi maksimal 30 detik untuk teaser.', '2026-04-23 12:00:00', 'tinggi', 'belum mulai'),
('T0004', 'P0002', 'A0001', 'Setup Meta Business Suite', 'Koneksikan halaman IG dan FB page klien.', '2026-04-21 09:00:00', 'rendah', 'selesai'),
('T0005', 'P0001', 'A0002', 'Riset Pasar', 'Lakukan riset pasar untuk memahami target audiens.', '2026-04-20 14:00:00', 'tinggi', 'belum mulai'),
('T0006', 'P0002', 'A0003', 'Buat Konten Feed', 'Buat konten feed yang menarik sesuai riset pasar.', '2026-04-22 11:00:00', 'sedang', 'sedang berjalan'),
('T0007', 'P0001', 'A0001', 'Meeting evaluasi mingguan', 'Sync up dengan tim dan klien terkait Ads.', '2026-04-20 19:00:00', 'tinggi', 'selesai');

SELECT * FROM tugas;
