-- Database Schema & Initial Data for SIA Mahasiswa & SaaS Multi-Tenancy (MySQL Version)
-- Universitas Sanata Dharma (USD) & Shella Ardana Andryansah
-- Compatible with MySQL 5.7+ & MariaDB 10+ (Laragon Default)

CREATE DATABASE IF NOT EXISTS `sia_mahasiswa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sia_mahasiswa`;

-- 0. Tabel Tenants (SaaS Multi-Institusi)
DROP TABLE IF EXISTS `keuangan_mahasiswa`;
DROP TABLE IF EXISTS `nilai_akademik`;
DROP TABLE IF EXISTS `jadwal_kuliah`;
DROP TABLE IF EXISTS `pengumuman`;
DROP TABLE IF EXISTS `mahasiswa`;
DROP TABLE IF EXISTS `tenants`;

CREATE TABLE `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kampus` VARCHAR(255) NOT NULL,
  `nama_singkat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT 'usd_logo.png',
  `warna_primer` VARCHAR(20) DEFAULT '#800000',
  `warna_aksen` VARCHAR(20) DEFAULT '#eab308',
  `paket` ENUM('Starter', 'Pro', 'Enterprise') DEFAULT 'Enterprise',
  `status_langganan` ENUM('Aktif', 'Masa Percobaan', 'Kedaluwarsa') DEFAULT 'Aktif',
  `tanggal_kadaluarsa` DATE DEFAULT NULL,
  `biaya_bulanan` DECIMAL(12,2) DEFAULT 7500000,
  `kuota_mahasiswa` INT DEFAULT 15000,
  `email_kontak` VARCHAR(100) DEFAULT NULL,
  `no_telp` VARCHAR(50) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. Tabel Mahasiswa
CREATE TABLE `mahasiswa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT 1,
  `nim` VARCHAR(20) NOT NULL UNIQUE,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'mahasiswa',
  `nama` VARCHAR(150) NOT NULL,
  `nik` VARCHAR(30) DEFAULT NULL,
  `tempat_lahir` VARCHAR(100) DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `jenis_kelamin` VARCHAR(20) DEFAULT NULL,
  `agama` VARCHAR(50) DEFAULT NULL,
  `golongan_darah` VARCHAR(5) DEFAULT NULL,
  `fakultas` VARCHAR(100) DEFAULT NULL,
  `prodi` VARCHAR(100) DEFAULT NULL,
  `jenjang` VARCHAR(10) DEFAULT 'S1',
  `angkatan` INT DEFAULT 2023,
  `semester_aktif` INT DEFAULT 7,
  `status_akademik` VARCHAR(50) DEFAULT 'Aktif',
  `dosen_pa` VARCHAR(150) DEFAULT NULL,
  `nip_dosen_pa` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `no_hp` VARCHAR(30) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `nama_ayah` VARCHAR(100) DEFAULT NULL,
  `nama_ibu` VARCHAR(100) DEFAULT NULL,
  `no_hp_ortu` VARCHAR(30) DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT 'shella.svg',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Jadwal Kuliah
CREATE TABLE `jadwal_kuliah` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT 1,
  `mahasiswa_id` INT NOT NULL,
  `kode_mk` VARCHAR(20) NOT NULL,
  `nama_mk` VARCHAR(150) NOT NULL,
  `sks` INT NOT NULL,
  `hari` VARCHAR(20) NOT NULL,
  `jam_mulai` VARCHAR(10) NOT NULL,
  `jam_selesai` VARCHAR(10) NOT NULL,
  `ruangan` VARCHAR(50) NOT NULL,
  `dosen` VARCHAR(150) NOT NULL,
  `semester` INT DEFAULT 7,
  `kelas` VARCHAR(10) DEFAULT 'A',
  `link_kelas` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Nilai Akademik
CREATE TABLE `nilai_akademik` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT 1,
  `mahasiswa_id` INT NOT NULL,
  `semester` INT NOT NULL,
  `kode_mk` VARCHAR(20) NOT NULL,
  `nama_mk` VARCHAR(150) NOT NULL,
  `sks` INT NOT NULL,
  `nilai_huruf` VARCHAR(5) NOT NULL,
  `nilai_angka` DECIMAL(4,2) NOT NULL,
  `bobot` DECIMAL(4,2) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Lulus',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Keuangan Mahasiswa
CREATE TABLE `keuangan_mahasiswa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT 1,
  `mahasiswa_id` INT NOT NULL,
  `kode_transaksi` VARCHAR(50) NOT NULL UNIQUE,
  `jenis_pembayaran` VARCHAR(100) NOT NULL,
  `semester` INT NOT NULL,
  `nominal` DECIMAL(12,2) NOT NULL,
  `tanggal_jatuh_tempo` DATE DEFAULT NULL,
  `tanggal_bayar` DATETIME DEFAULT NULL,
  `metode_pembayaran` VARCHAR(50) DEFAULT NULL,
  `nomor_va` VARCHAR(50) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Lunas',
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pengumuman
CREATE TABLE `pengumuman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT DEFAULT 1,
  `judul` VARCHAR(255) NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `tanggal` DATE NOT NULL,
  `isi` TEXT NOT NULL,
  `badge` VARCHAR(30) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA TENANTS
INSERT INTO `tenants` (`id`, `nama_kampus`, `nama_singkat`, `slug`, `logo`, `warna_primer`, `warna_aksen`, `paket`, `status_langganan`, `tanggal_kadaluarsa`, `biaya_bulanan`, `kuota_mahasiswa`, `email_kontak`, `no_telp`, `alamat`) VALUES
(1, 'Universitas Sanata Dharma', 'USD', 'usd', 'usd_logo.png', '#800000', '#eab308', 'Enterprise', 'Aktif', '2027-12-31', 7500000, 15000, 'akademik@usd.ac.id', '0274-513301', 'Jl. Afandi, Mrican, Tromol Pos 29, Yogyakarta 55002'),
(2, 'Institut Sains & Digital Indonesia', 'ISDI', 'isdi', 'logo.svg', '#059669', '#10b981', 'Pro', 'Aktif', '2027-08-15', 3500000, 2500, 'info@isdi.ac.id', '021-7890123', 'Jl. TB Simatupang No. 18, Jakarta Selatan');

-- SEED DATA MAHASISWA (Shella Ardana Andryansah - Password: 1122)
INSERT INTO `mahasiswa` (`id`, `tenant_id`, `nim`, `username`, `password`, `role`, `nama`, `nik`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `golongan_darah`, `fakultas`, `prodi`, `jenjang`, `angkatan`, `semester_aktif`, `status_akademik`, `dosen_pa`, `nip_dosen_pa`, `email`, `no_hp`, `alamat`, `nama_ayah`, `nama_ibu`, `no_hp_ortu`, `foto`)
VALUES 
(1, 1, '235314182', '235314182', '$2y$10$v7gO7XyW49vA11mZ.X1e9OFhQ4W174zK9k3f8pWpMekZ63e2Z7H1y', 'mahasiswa', 'Shella Ardana Andryansah', '3404075508020003', 'Yogyakarta', '2002-08-15', 'Perempuan', 'Katolik', 'B', 'Fakultas Sains dan Teknologi', 'Informatika', 'S1', 2023, 7, 'Aktif', 'Ir. Rosalia Arum Kumalasanti M.T.', '196803201994032001', 'shella.ardana@student.usd.ac.id', '082134567890', 'Jl. Paingan, Maguwoharjo, Depok, Sleman, D.I. Yogyakarta', 'Andryansah', 'Maria Lestari', '081223344556', 'shella.svg');

-- SEED JADWAL KULIAH (Semester 7 - 18 SKS)
INSERT INTO `jadwal_kuliah` (`mahasiswa_id`, `tenant_id`, `kode_mk`, `nama_mk`, `sks`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`, `dosen`, `semester`, `kelas`, `link_kelas`) VALUES
(1, 1, 'SDEV207', 'Desain UI/UX', 3, 'Selasa', '13:00', '15:50', 'Lab Multimedia & UI/UX', 'Ir. Rosalia Arum Kumalasanti M.T.', 7, 'A', 'https://meet.google.com/usd-uiux'),
(1, 1, 'INTS205', 'Komputer Pelihat', 3, 'Jumat', '13:00', '15:50', 'Lab Visi Komputer & AI', 'Iswanjono, S.T., M.T.', 7, 'B', 'https://meet.google.com/usd-vision'),
(1, 1, 'PENL101', 'Metodologi Penelitian', 4, 'Rabu', '13:00', '16:50', 'Ruang Teori K.302', 'Dr. Ir. I Gusti Bagus Budi Dharma, M.T.', 7, 'F', 'https://meet.google.com/usd-metopen'),
(1, 1, 'GRLE51P', 'Pembelajaran Mesin Berbasis Graph', 3, 'Rabu', '10:00', '12:50', 'Lab Sains Data & AI', 'Robertus Bellarminus Joko, M.Sc.', 7, 'A', 'https://meet.google.com/usd-graphml'),
(1, 1, 'SDEV202', 'Pemrograman Perangkat Bergerak', 3, 'Senin', '10:00', '12:50', 'Lab Rekayasa Perangkat Lunak', 'Anastasia Rita Widiarti, M.Kom.', 7, 'B', 'https://meet.google.com/usd-mobile'),
(1, 1, 'MATS21W', 'Probabilitas Lanjut', 2, 'Rabu', '07:00', '08:50', 'Ruang Teori K.201', 'Drs. T. Sarkim, M.Ed., Ph.D.', 7, 'A', 'https://meet.google.com/usd-prob');

-- SEED NILAI AKADEMIK (Semester 1-6 Total 125 SKS, Mutu 416.0 => IPK = 3.33)
INSERT INTO `nilai_akademik` (`mahasiswa_id`, `tenant_id`, `semester`, `kode_mk`, `nama_mk`, `sks`, `nilai_huruf`, `nilai_angka`, `bobot`, `status`) VALUES
-- Semester 1 (19 SKS, Mutu 60.0 -> IPS 3.16)
(1, 1, 1, 'INF111', 'Algoritma & Pemrograman 1', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 1, 'INF112', 'Matematika Diskrit', 3, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 1, 'INF113', 'Logika Informatika', 2, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 1, 'MAT111', 'Kalkulus 1', 3, 'B', 72.0, 3.0, 'Lulus'),
(1, 1, 1, 'INF114', 'Pengantar Teknologi Informasi', 3, 'C+', 65.0, 2.5, 'Lulus'),
(1, 1, 1, 'MKU111', 'Bahasa Inggris Akademik', 2, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 1, 'MKU112', 'Pendidikan Agama & Moralitas', 2, 'C+', 66.0, 2.5, 'Lulus'),
-- Semester 2 (22 SKS, Mutu 72.5 -> IPS 3.30)
(1, 1, 2, 'INF121', 'Algoritma & Struktur Data', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 2, 'INF122', 'Sistem Operasi', 3, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 2, 'INF123', 'Basis Data 1', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 2, 'MAT121', 'Kalkulus 2', 3, 'B+', 76.0, 3.5, 'Lulus'),
(1, 1, 2, 'INF124', 'Arsitektur Komputer', 3, 'A', 88.0, 4.0, 'Lulus'),
(1, 1, 2, 'MKU121', 'Pendidikan Pancasila', 2, 'C+', 65.0, 2.5, 'Lulus'),
(1, 1, 2, 'MKU122', 'Bahasa Indonesia Ilmiah', 2, 'C+', 66.0, 2.5, 'Lulus'),
(1, 1, 2, 'INF125', 'Praktikum Algoritma Lanjut', 2, 'C+', 65.0, 2.5, 'Lulus'),
-- Semester 3 (22 SKS, Mutu 75.5 -> IPS 3.43)
(1, 1, 3, 'INF211', 'Pemrograman Berorientasi Objek', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 3, 'INF212', 'Jaringan Komputer', 3, 'B+', 79.0, 3.5, 'Lulus'),
(1, 1, 3, 'INF213', 'Basis Data Lanjut', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 3, 'MAT211', 'Aljabar Linear Numerik', 3, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 3, 'INF214', 'Rekayasa Kebutuhan Perangkat Lunak', 3, 'A', 88.0, 4.0, 'Lulus'),
(1, 1, 3, 'MKU211', 'Kewirausahaan Teknologi', 2, 'B+', 76.0, 3.5, 'Lulus'),
(1, 1, 3, 'MKU212', 'Etika Profesi Informatika', 2, 'B', 72.0, 3.0, 'Lulus'),
(1, 1, 3, 'INF215', 'Praktikum Jaringan Komputer', 2, 'C+', 65.0, 2.5, 'Lulus'),
-- Semester 4 (19 SKS, Mutu 65.0 -> IPS 3.42)
(1, 1, 4, 'INF221', 'Pengembangan Aplikasi Web', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 4, 'INF222', 'Kecerdasan Buatan', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 4, 'INF223', 'Analisis & Desain Sistem', 3, 'B+', 79.0, 3.5, 'Lulus'),
(1, 1, 4, 'INF224', 'Komputasi Paralel & Terdistribusi', 3, 'A', 89.0, 4.0, 'Lulus'),
(1, 1, 4, 'MAT221', 'Statistika & Teori Peluang', 2, 'B+', 76.0, 3.5, 'Lulus'),
(1, 1, 4, 'MKU221', 'Kepemimpinan & Kerja Tim', 2, 'B', 72.0, 3.0, 'Lulus'),
(1, 1, 4, 'MKU222', 'Komunikasi Interpersonal', 2, 'C+', 65.0, 2.5, 'Lulus'),
-- Semester 5 (22 SKS, Mutu 72.0 -> IPS 3.27)
(1, 1, 5, 'INF311', 'Rekayasa Perangkat Lunak Lanjut', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 5, 'INF312', 'Keamanan Informasi & Jaringan', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 5, 'INF313', 'Interaksi Manusia dan Komputer', 3, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 5, 'INF314', 'Manajemen Proyek TI', 3, 'B+', 76.0, 3.5, 'Lulus'),
(1, 1, 5, 'INF315', 'Sistem Informasi Enterprise', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 5, 'MKU311', 'Filsafat Sains', 2, 'B', 72.0, 3.0, 'Lulus'),
(1, 1, 5, 'MKU312', 'Studi Budaya & Lingkungan', 2, 'C+', 65.0, 2.5, 'Lulus'),
(1, 1, 5, 'INF316', 'Praktikum Komputasi Awan', 2, 'C+', 66.0, 2.5, 'Lulus'),
-- Semester 6 (21 SKS, Mutu 71.0 -> IPS 3.38)
(1, 1, 6, 'INF321', 'Pengembangan Aplikasi Bergerak', 4, 'B+', 78.0, 3.5, 'Lulus'),
(1, 1, 6, 'INF322', 'Data Mining & Warehouse', 3, 'B+', 79.0, 3.5, 'Lulus'),
(1, 1, 6, 'INF323', 'Pengujian Perangkat Lunak', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 6, 'INF324', 'Cloud Computing Architecture', 3, 'B+', 76.0, 3.5, 'Lulus'),
(1, 1, 6, 'INF325', 'Tata Kelola TI (IT Governance)', 3, 'B+', 77.0, 3.5, 'Lulus'),
(1, 1, 6, 'MKU321', 'Kuliah Kerja Nyata (KKN)', 3, 'B', 72.0, 3.0, 'Lulus'),
(1, 1, 6, 'MKU322', 'Kapita Selekta Informatika', 2, 'B', 70.0, 3.0, 'Lulus');

-- SEED KEUANGAN (Total Lunas Rp 80.671.000, 0 tunggakan)
INSERT INTO `keuangan_mahasiswa` (`mahasiswa_id`, `tenant_id`, `kode_transaksi`, `jenis_pembayaran`, `semester`, `nominal`, `tanggal_jatuh_tempo`, `tanggal_bayar`, `metode_pembayaran`, `nomor_va`, `status`, `keterangan`) VALUES
(1, 1, 'INV-USD-2021-1', 'Uang Kuliah Tunggal (UKT) Semester 1', 1, 11524500, '2021-08-20', '2021-08-15 10:20:00', 'Virtual Account BNI', '9882353141820001', 'Lunas', 'Registrasi Mahasiswa Baru Semester 1'),
(1, 1, 'INV-USD-2021-2', 'Uang Kuliah Tunggal (UKT) Semester 2', 2, 11524500, '2022-01-20', '2022-01-14 11:15:00', 'Virtual Account BNI', '9882353141820002', 'Lunas', 'Registrasi Perkuliahan Semester 2'),
(1, 1, 'INV-USD-2022-3', 'Uang Kuliah Tunggal (UKT) Semester 3', 3, 11524500, '2022-08-20', '2022-08-18 09:30:00', 'Virtual Account BNI', '9882353141820003', 'Lunas', 'Registrasi Perkuliahan Semester 3'),
(1, 1, 'INV-USD-2022-4', 'Uang Kuliah Tunggal (UKT) Semester 4', 4, 11524500, '2023-01-20', '2023-01-16 14:00:00', 'Virtual Account BNI', '9882353141820004', 'Lunas', 'Registrasi Perkuliahan Semester 4'),
(1, 1, 'INV-USD-2023-5', 'Uang Kuliah Tunggal (UKT) Semester 5', 5, 11524500, '2023-08-20', '2023-08-17 10:45:00', 'Virtual Account BNI', '9882353141820005', 'Lunas', 'Registrasi Perkuliahan Semester 5'),
(1, 1, 'INV-USD-2023-6', 'Uang Kuliah Tunggal (UKT) Semester 6', 6, 11524500, '2024-01-20', '2024-01-15 15:10:00', 'Virtual Account BNI', '9882353141820006', 'Lunas', 'Registrasi Perkuliahan Semester 6'),
(1, 1, 'INV-USD-2024-7', 'Uang Kuliah Tunggal (UKT) Semester 7', 7, 11524000, '2024-08-20', '2024-08-16 13:25:00', 'Virtual Account BNI', '9882353141820007', 'Lunas', 'Registrasi Perkuliahan Semester 7');
