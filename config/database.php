<?php
/**
 * Konfigurasi Database SIA Mahasiswa & SaaS Multi-Tenancy Engine
 * Mendukung SQLite (Default otomatis siap pakai) dan MySQL (Laragon)
 * Dilengkapi Sistem Autentikasi Pengguna & Data Resmi Universitas Sanata Dharma
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
define('DB_DRIVER', 'sqlite'); // 'sqlite' atau 'mysql'

// Konfigurasi jika menggunakan MySQL
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sia_mahasiswa');
define('DB_USER', 'root');
define('DB_PASS', '');

// File SQLite (otomatis gunakan /tmp jika berjalan di Vercel Serverless)
if (getenv('VERCEL') || isset($_ENV['VERCEL'])) {
    $tmpDb = '/tmp/sia_database.sqlite';
    $srcDb = __DIR__ . '/../database/sia_database.sqlite';
    if (!file_exists($tmpDb) && file_exists($srcDb)) {
        copy($srcDb, $tmpDb);
    }
    define('DB_SQLITE_FILE', $tmpDb);
} else {
    define('DB_SQLITE_FILE', __DIR__ . '/../database/sia_database.sqlite');
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            if (DB_DRIVER === 'sqlite') {
                $dbDir = dirname(DB_SQLITE_FILE);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }
                
                $isNewDb = !file_exists(DB_SQLITE_FILE) || filesize(DB_SQLITE_FILE) === 0;
                
                $this->pdo = new PDO('sqlite:' . DB_SQLITE_FILE);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                if ($isNewDb) {
                    $this->initSqliteTables();
                } else {
                    $this->initSaasMultiTenancy();
                }
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                $this->initSaasMultiTenancy();
            }
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }

    public function initSaasMultiTenancy() {
        // Cek tabel tenants
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_kampus TEXT NOT NULL,
            nama_singkat TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            logo TEXT DEFAULT 'usd_logo.png',
            warna_primer TEXT DEFAULT '#800000',
            warna_aksen TEXT DEFAULT '#eab308',
            paket TEXT DEFAULT 'Enterprise',
            status_langganan TEXT DEFAULT 'Aktif',
            tanggal_kadaluarsa TEXT,
            biaya_bulanan REAL DEFAULT 7500000,
            kuota_mahasiswa INTEGER DEFAULT 15000,
            email_kontak TEXT,
            no_telp TEXT,
            alamat TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Cek kolom di tabel mahasiswa
        try { $this->pdo->exec("ALTER TABLE mahasiswa ADD COLUMN username TEXT"); } catch (Exception $e) {}
        try { $this->pdo->exec("ALTER TABLE mahasiswa ADD COLUMN password TEXT"); } catch (Exception $e) {}
        try { $this->pdo->exec("ALTER TABLE mahasiswa ADD COLUMN role TEXT DEFAULT 'mahasiswa'"); } catch (Exception $e) {}
        try { $this->pdo->exec("ALTER TABLE mahasiswa ADD COLUMN tenant_id INTEGER DEFAULT 1"); } catch (Exception $e) {}

        $countTenants = $this->pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
        if ($countTenants == 0) {
            $this->seedSaasTenants();
        }
    }

    private function initSqliteTables() {
        $queries = [
            // Tabel Tenants (SaaS Institusi)
            "CREATE TABLE IF NOT EXISTS tenants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_kampus TEXT NOT NULL,
                nama_singkat TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                logo TEXT DEFAULT 'usd_logo.png',
                warna_primer TEXT DEFAULT '#800000',
                warna_aksen TEXT DEFAULT '#eab308',
                paket TEXT DEFAULT 'Enterprise',
                status_langganan TEXT DEFAULT 'Aktif',
                tanggal_kadaluarsa TEXT,
                biaya_bulanan REAL DEFAULT 7500000,
                kuota_mahasiswa INTEGER DEFAULT 15000,
                email_kontak TEXT,
                no_telp TEXT,
                alamat TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );",

            // Tabel Mahasiswa (Dengan Akun Login)
            "CREATE TABLE IF NOT EXISTS mahasiswa (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                nim TEXT UNIQUE NOT NULL,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'mahasiswa',
                nama TEXT NOT NULL,
                nik TEXT,
                tempat_lahir TEXT,
                tanggal_lahir TEXT,
                jenis_kelamin TEXT,
                agama TEXT,
                golongan_darah TEXT,
                fakultas TEXT,
                prodi TEXT,
                jenjang TEXT DEFAULT 'S1',
                angkatan INTEGER,
                semester_aktif INTEGER,
                status_akademik TEXT DEFAULT 'Aktif',
                dosen_pa TEXT,
                nip_dosen_pa TEXT,
                email TEXT,
                no_hp TEXT,
                alamat TEXT,
                nama_ayah TEXT,
                nama_ibu TEXT,
                no_hp_ortu TEXT,
                foto TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            );",

            // Tabel Jadwal Kuliah
            "CREATE TABLE IF NOT EXISTS jadwal_kuliah (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                mahasiswa_id INTEGER NOT NULL,
                kode_mk TEXT NOT NULL,
                nama_mk TEXT NOT NULL,
                sks INTEGER NOT NULL,
                hari TEXT NOT NULL,
                jam_mulai TEXT NOT NULL,
                jam_selesai TEXT NOT NULL,
                ruangan TEXT NOT NULL,
                dosen TEXT NOT NULL,
                semester INTEGER DEFAULT 7,
                kelas TEXT DEFAULT 'A',
                link_kelas TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
            );",

            // Tabel Nilai Akademik
            "CREATE TABLE IF NOT EXISTS nilai_akademik (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                mahasiswa_id INTEGER NOT NULL,
                semester INTEGER NOT NULL,
                kode_mk TEXT NOT NULL,
                nama_mk TEXT NOT NULL,
                sks INTEGER NOT NULL,
                nilai_huruf TEXT NOT NULL,
                nilai_angka REAL NOT NULL,
                bobot REAL NOT NULL,
                status TEXT DEFAULT 'Lulus',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
            );",

            // Tabel Keuangan Mahasiswa
            "CREATE TABLE IF NOT EXISTS keuangan_mahasiswa (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                mahasiswa_id INTEGER NOT NULL,
                kode_transaksi TEXT UNIQUE NOT NULL,
                jenis_pembayaran TEXT NOT NULL,
                semester INTEGER NOT NULL,
                nominal REAL NOT NULL,
                tanggal_jatuh_tempo TEXT,
                tanggal_bayar TEXT,
                metode_pembayaran TEXT,
                nomor_va TEXT,
                status TEXT DEFAULT 'Lunas',
                keterangan TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
            );",

            // Tabel Pengumuman
            "CREATE TABLE IF NOT EXISTS pengumuman (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                judul TEXT NOT NULL,
                kategori TEXT NOT NULL,
                tanggal TEXT NOT NULL,
                isi TEXT NOT NULL,
                badge TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );"
        ];

        foreach ($queries as $sql) {
            $this->pdo->exec($sql);
        }

        $this->seedSaasTenants();
        $this->seedInitialData();
    }

    public function seedSaasTenants() {
        $tenants = [
            [
                1, 'Universitas Sanata Dharma', 'USD', 'usd', 'usd_logo.png',
                '#800000', '#eab308', 'Enterprise', 'Aktif', '2027-12-31', 7500000, 15000,
                'akademik@usd.ac.id', '0274-513301', 'Jl. Afandi, Mrican, Tromol Pos 29, Yogyakarta 55002'
            ],
            [
                2, 'Institut Sains & Digital Indonesia', 'ISDI', 'isdi', 'logo.svg',
                '#059669', '#10b981', 'Pro', 'Aktif', '2027-08-15', 3500000, 2500,
                'info@isdi.ac.id', '021-7890123', 'Jl. TB Simatupang No. 18, Jakarta Selatan'
            ],
            [
                3, 'Politeknik Bisnis & Kreatif Nusantara', 'POLMET', 'polmet', 'logo.svg',
                '#9333ea', '#a855f7', 'Starter', 'Masa Percobaan', '2026-11-30', 1500000, 500,
                'contact@polmet.ac.id', '022-6543210', 'Jl. Dago No. 102, Bandung'
            ]
        ];

        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO tenants (
            id, nama_kampus, nama_singkat, slug, logo, warna_primer, warna_aksen, paket, status_langganan, tanggal_kadaluarsa, biaya_bulanan, kuota_mahasiswa, email_kontak, no_telp, alamat
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($tenants as $t) {
            $stmt->execute($t);
        }
    }

    private function seedInitialData() {
        // Hash kata sandi '1122'
        $passwordHash = password_hash('1122', PASSWORD_DEFAULT);

        // Seed Mahasiswa Utama: SHELLA ARDANA ANDRYANSAH (Universitas Sanata Dharma)
        $stmtMhs = $this->pdo->prepare("INSERT INTO mahasiswa (
            id, tenant_id, nim, username, password, role, nama, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, golongan_darah,
            fakultas, prodi, jenjang, angkatan, semester_aktif, status_akademik,
            dosen_pa, nip_dosen_pa, email, no_hp, alamat, nama_ayah, nama_ibu, no_hp_ortu, foto
        ) VALUES (
            1, 1, '235314182', '235314182', ?, 'mahasiswa', 'Shella Ardana Andryansah', '3404075508020003', 'Yogyakarta', '2002-08-15', 'Perempuan', 'Katolik', 'B',
            'Fakultas Sains dan Teknologi', 'Informatika', 'S1', 2023, 7, 'Aktif',
            'Ir. Rosalia Arum Kumalasanti M.T.', '196803201994032001', 'shella.ardana@student.usd.ac.id', '082134567890',
            'Jl. Paingan, Maguwoharjo, Depok, Sleman, D.I. Yogyakarta', 'Andryansah', 'Maria Lestari', '081223344556', 'shella.svg'
        )");
        $stmtMhs->execute([$passwordHash]);
        $mhsId = 1;

        // Seed Mata Kuliah Semester 7 Shella (Total 18 SKS)
        $jadwal = [
            [$mhsId, 1, 'SDEV207', 'Desain UI/UX', 3, 'Selasa', '13:00', '15:50', 'Lab Multimedia & UI/UX', 'Ir. Rosalia Arum Kumalasanti M.T.', 7, 'A', 'https://meet.google.com/usd-uiux'],
            [$mhsId, 1, 'INTS205', 'Komputer Pelihat', 3, 'Jumat', '13:00', '15:50', 'Lab Visi Komputer & AI', 'Iswanjono, S.T., M.T.', 7, 'B', 'https://meet.google.com/usd-vision'],
            [$mhsId, 1, 'PENL101', 'Metodologi Penelitian', 4, 'Rabu', '13:00', '16:50', 'Ruang Teori K.302', 'Dr. Ir. I Gusti Bagus Budi Dharma, M.T.', 7, 'F', 'https://meet.google.com/usd-metopen'],
            [$mhsId, 1, 'GRLE51P', 'Pembelajaran Mesin Berbasis Graph', 3, 'Rabu', '10:00', '12:50', 'Lab Sains Data & AI', 'Robertus Bellarminus Joko, M.Sc.', 7, 'A', 'https://meet.google.com/usd-graphml'],
            [$mhsId, 1, 'SDEV202', 'Pemrograman Perangkat Bergerak', 3, 'Senin', '10:00', '12:50', 'Lab Rekayasa Perangkat Lunak', 'Anastasia Rita Widiarti, M.Kom.', 7, 'B', 'https://meet.google.com/usd-mobile'],
            [$mhsId, 1, 'MATS21W', 'Probabilitas Lanjut', 2, 'Rabu', '07:00', '08:50', 'Ruang Teori K.201', 'Drs. T. Sarkim, M.Ed., Ph.D.', 7, 'A', 'https://meet.google.com/usd-prob']
        ];
        $stmtJadwal = $this->pdo->prepare("INSERT INTO jadwal_kuliah (mahasiswa_id, tenant_id, kode_mk, nama_mk, sks, hari, jam_mulai, jam_selesai, ruangan, dosen, semester, kelas, link_kelas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($jadwal as $j) {
            $stmtJadwal->execute($j);
        }

        // Seed Nilai Akademik Semester 1 - 6 (Total 125 SKS, Mutu 416.0 => IPK = 3.33)
        $nilai = [
            // Semester 1 (19 SKS, Mutu 60.0 -> IPS 3.16)
            [$mhsId, 1, 1, 'INF111', 'Algoritma & Pemrograman 1', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 1, 'INF112', 'Matematika Diskrit', 3, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 1, 'INF113', 'Logika Informatika', 2, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 1, 'MAT111', 'Kalkulus 1', 3, 'B', 72.0, 3.0, 'Lulus'],
            [$mhsId, 1, 1, 'INF114', 'Pengantar Teknologi Informasi', 3, 'C+', 65.0, 2.5, 'Lulus'],
            [$mhsId, 1, 1, 'MKU111', 'Bahasa Inggris Akademik', 2, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 1, 'MKU112', 'Pendidikan Agama & Moralitas', 2, 'C+', 66.0, 2.5, 'Lulus'],

            // Semester 2 (22 SKS, Mutu 72.5 -> IPS 3.30)
            [$mhsId, 1, 2, 'INF121', 'Algoritma & Struktur Data', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 2, 'INF122', 'Sistem Operasi', 3, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 2, 'INF123', 'Basis Data 1', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 2, 'MAT121', 'Kalkulus 2', 3, 'B+', 76.0, 3.5, 'Lulus'],
            [$mhsId, 1, 2, 'INF124', 'Arsitektur Komputer', 3, 'A', 88.0, 4.0, 'Lulus'],
            [$mhsId, 1, 2, 'MKU121', 'Pendidikan Pancasila', 2, 'C+', 65.0, 2.5, 'Lulus'],
            [$mhsId, 1, 2, 'MKU122', 'Bahasa Indonesia Ilmiah', 2, 'C+', 66.0, 2.5, 'Lulus'],
            [$mhsId, 1, 2, 'INF125', 'Praktikum Algoritma Lanjut', 2, 'C+', 65.0, 2.5, 'Lulus'],

            // Semester 3 (22 SKS, Mutu 75.5 -> IPS 3.43)
            [$mhsId, 1, 3, 'INF211', 'Pemrograman Berorientasi Objek', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 3, 'INF212', 'Jaringan Komputer', 3, 'B+', 79.0, 3.5, 'Lulus'],
            [$mhsId, 1, 3, 'INF213', 'Basis Data Lanjut', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 3, 'MAT211', 'Aljabar Linear Numerik', 3, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 3, 'INF214', 'Rekayasa Kebutuhan Perangkat Lunak', 3, 'A', 88.0, 4.0, 'Lulus'],
            [$mhsId, 1, 3, 'MKU211', 'Kewirausahaan Teknologi', 2, 'B+', 76.0, 3.5, 'Lulus'],
            [$mhsId, 1, 3, 'MKU212', 'Etika Profesi Informatika', 2, 'B', 72.0, 3.0, 'Lulus'],
            [$mhsId, 1, 3, 'INF215', 'Praktikum Jaringan Komputer', 2, 'C+', 65.0, 2.5, 'Lulus'],

            // Semester 4 (19 SKS, Mutu 65.0 -> IPS 3.42)
            [$mhsId, 1, 4, 'INF221', 'Pengembangan Aplikasi Web', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 4, 'INF222', 'Kecerdasan Buatan', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 4, 'INF223', 'Analisis & Desain Sistem', 3, 'B+', 79.0, 3.5, 'Lulus'],
            [$mhsId, 1, 4, 'INF224', 'Komputasi Paralel & Terdistribusi', 3, 'A', 89.0, 4.0, 'Lulus'],
            [$mhsId, 1, 4, 'MAT221', 'Statistika & Teori Peluang', 2, 'B+', 76.0, 3.5, 'Lulus'],
            [$mhsId, 1, 4, 'MKU221', 'Kepemimpinan & Kerja Tim', 2, 'B', 72.0, 3.0, 'Lulus'],
            [$mhsId, 1, 4, 'MKU222', 'Komunikasi Interpersonal', 2, 'C+', 65.0, 2.5, 'Lulus'],

            // Semester 5 (22 SKS, Mutu 72.0 -> IPS 3.27)
            [$mhsId, 1, 5, 'INF311', 'Rekayasa Perangkat Lunak Lanjut', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 5, 'INF312', 'Keamanan Informasi & Jaringan', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 5, 'INF313', 'Interaksi Manusia dan Komputer', 3, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 5, 'INF314', 'Manajemen Proyek TI', 3, 'B+', 76.0, 3.5, 'Lulus'],
            [$mhsId, 1, 5, 'INF315', 'Sistem Informasi Enterprise', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 5, 'MKU311', 'Filsafat Sains', 2, 'B', 72.0, 3.0, 'Lulus'],
            [$mhsId, 1, 5, 'MKU312', 'Studi Budaya & Lingkungan', 2, 'C+', 65.0, 2.5, 'Lulus'],
            [$mhsId, 1, 5, 'INF316', 'Praktikum Komputasi Awan', 2, 'C+', 66.0, 2.5, 'Lulus'],

            // Semester 6 (21 SKS, Mutu 71.0 -> IPS 3.38)
            [$mhsId, 1, 6, 'INF321', 'Pengembangan Aplikasi Bergerak', 4, 'B+', 78.0, 3.5, 'Lulus'],
            [$mhsId, 1, 6, 'INF322', 'Data Mining & Warehouse', 3, 'B+', 79.0, 3.5, 'Lulus'],
            [$mhsId, 1, 6, 'INF323', 'Pengujian Perangkat Lunak', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 6, 'INF324', 'Cloud Computing Architecture', 3, 'B+', 76.0, 3.5, 'Lulus'],
            [$mhsId, 1, 6, 'INF325', 'Tata Kelola TI (IT Governance)', 3, 'B+', 77.0, 3.5, 'Lulus'],
            [$mhsId, 1, 6, 'MKU321', 'Kuliah Kerja Nyata (KKN)', 3, 'B', 72.0, 3.0, 'Lulus'],
            [$mhsId, 1, 6, 'MKU322', 'Kapita Selekta Informatika', 2, 'B', 70.0, 3.0, 'Lulus']
            // Total: 60.0 + 72.5 + 75.5 + 65.0 + 72.0 + 71.0 = 416.0 Mutu / 125 SKS = 3.328 -> 3.33 IPK!
        ];
        $stmtNilai = $this->pdo->prepare("INSERT INTO nilai_akademik (mahasiswa_id, tenant_id, semester, kode_mk, nama_mk, sks, nilai_huruf, nilai_angka, bobot, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($nilai as $n) {
            $stmtNilai->execute($n);
        }

        // Seed Keuangan Shella (Total LUNAS Rp 80.671.000, 0 tunggakan!)
        $keuangan = [
            [$mhsId, 1, 'INV-USD-2021-1', 'Uang Kuliah Tunggal (UKT) Semester 1', 1, 11524500, '2021-08-20', '2021-08-15 10:20:00', 'Virtual Account BNI', '9882353141820001', 'Lunas', 'Registrasi Mahasiswa Baru Semester 1'],
            [$mhsId, 1, 'INV-USD-2021-2', 'Uang Kuliah Tunggal (UKT) Semester 2', 2, 11524500, '2022-01-20', '2022-01-14 11:15:00', 'Virtual Account BNI', '9882353141820002', 'Lunas', 'Registrasi Perkuliahan Semester 2'],
            [$mhsId, 1, 'INV-USD-2022-3', 'Uang Kuliah Tunggal (UKT) Semester 3', 3, 11524500, '2022-08-20', '2022-08-18 09:30:00', 'Virtual Account BNI', '9882353141820003', 'Lunas', 'Registrasi Perkuliahan Semester 3'],
            [$mhsId, 1, 'INV-USD-2022-4', 'Uang Kuliah Tunggal (UKT) Semester 4', 4, 11524500, '2023-01-20', '2023-01-16 14:00:00', 'Virtual Account BNI', '9882353141820004', 'Lunas', 'Registrasi Perkuliahan Semester 4'],
            [$mhsId, 1, 'INV-USD-2023-5', 'Uang Kuliah Tunggal (UKT) Semester 5', 5, 11524500, '2023-08-20', '2023-08-17 10:45:00', 'Virtual Account BNI', '9882353141820005', 'Lunas', 'Registrasi Perkuliahan Semester 5'],
            [$mhsId, 1, 'INV-USD-2023-6', 'Uang Kuliah Tunggal (UKT) Semester 6', 6, 11524500, '2024-01-20', '2024-01-15 15:10:00', 'Virtual Account BNI', '9882353141820006', 'Lunas', 'Registrasi Perkuliahan Semester 6'],
            [$mhsId, 1, 'INV-USD-2024-7', 'Uang Kuliah Tunggal (UKT) Semester 7', 7, 11524000, '2024-08-20', '2024-08-16 13:25:00', 'Virtual Account BNI', '9882353141820007', 'Lunas', 'Registrasi Perkuliahan Semester 7']
            // 11.524.500 * 6 + 11.524.000 = 80.671.000!
        ];
        $stmtKeuangan = $this->pdo->prepare("INSERT INTO keuangan_mahasiswa (mahasiswa_id, tenant_id, kode_transaksi, jenis_pembayaran, semester, nominal, tanggal_jatuh_tempo, tanggal_bayar, metode_pembayaran, nomor_va, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($keuangan as $k) {
            $stmtKeuangan->execute($k);
        }

        // Seed Pengumuman
        $pengumuman = [
            [1, 'Yudisium & Pengajuan Proposal Skripsi Semester 7', 'Akademik', '2026-10-18', 'Mahasiswa semester 7 yang mengambil Metodologi Penelitian dapat mulai berkonsultasi dengan Dosen PA terkait judul skripsi.', 'PENTING'],
            [1, 'Bebas Tanggungan Keuangan Kuliah Semester Ganjil', 'Keuangan', '2026-10-15', 'Seluruh pembayaran UKT hingga semester 7 telah tervalidasi lunas. Kartu Rencana Studi aktif dan bebas tunggakan.', 'INFO'],
            [1, 'Workshop UI/UX Design & Usability Testing Kampus Paingan', 'Seminar', '2026-10-10', 'Program Studi Informatika mengundang mahasiswa dalam workshop Figma & User Experience di Lab Multimedia.', 'EVENT']
        ];
        $stmtPengumuman = $this->pdo->prepare("INSERT INTO pengumuman (tenant_id, judul, kategori, tanggal, isi, badge) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($pengumuman as $p) {
            $stmtPengumuman->execute($p);
        }
    }
}

// Inisialisasi Database Connection
$pdo = Database::getConnection();

// ==========================================
// TENANT & CURRENT USER RESOLUTION
// ==========================================

// Handle Tenant ID
if (isset($_GET['tenant'])) {
    $requestedTenant = (int)$_GET['tenant'];
    $stmtT = $pdo->prepare("SELECT id FROM tenants WHERE id = ?");
    $stmtT->execute([$requestedTenant]);
    if ($stmtT->fetch()) {
        $_SESSION['active_tenant_id'] = $requestedTenant;
    }
}

if (!isset($_SESSION['active_tenant_id'])) {
    $stmtFirstTenant = $pdo->query("SELECT id FROM tenants ORDER BY id ASC LIMIT 1");
    $firstT = $stmtFirstTenant->fetch();
    $_SESSION['active_tenant_id'] = $firstT ? $firstT['id'] : 1;
}

$activeTenantId = (int)$_SESSION['active_tenant_id'];

// Ambil Data Tenant Aktif
$stmtCurrentTenant = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
$stmtCurrentTenant->execute([$activeTenantId]);
$currentTenant = $stmtCurrentTenant->fetch();

if (!$currentTenant) {
    $currentTenant = [
        'id' => 1,
        'nama_kampus' => 'Universitas Sanata Dharma',
        'nama_singkat' => 'USD',
        'slug' => 'usd',
        'logo' => 'usd_logo.png',
        'warna_primer' => '#800000',
        'warna_aksen' => '#eab308',
        'paket' => 'Enterprise',
        'status_langganan' => 'Aktif',
        'tanggal_kadaluarsa' => '2027-12-31',
        'biaya_bulanan' => 7500000,
        'kuota_mahasiswa' => 15000,
        'email_kontak' => 'akademik@usd.ac.id',
        'no_telp' => '0274-513301',
        'alamat' => 'Jl. Afandi, Mrican, Tromol Pos 29, Yogyakarta 55002'
    ];
}

// Pemulihan Sesi Otomatis untuk Lingkungan Serverless (Vercel)
if (!isset($_SESSION['logged_in_user']) && isset($_COOKIE['sia_auth_session'])) {
    $decodedCookie = base64_decode($_COOKIE['sia_auth_session']);
    if (strpos($decodedCookie, '||') !== false) {
        list($payloadJson, $cookieHash) = explode('||', $decodedCookie, 2);
        $expectedHash = hash_hmac('sha256', $payloadJson, 'sia_usd_secret_key_2026');
        if (hash_equals($expectedHash, $cookieHash)) {
            $cookieData = json_decode($payloadJson, true);
            if ($cookieData && isset($cookieData['id']) && ($cookieData['exp'] ?? 0) > time()) {
                $stmtAuthMhs = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ? LIMIT 1");
                $stmtAuthMhs->execute([$cookieData['id']]);
                $authMhs = $stmtAuthMhs->fetch();
                if ($authMhs) {
                    $_SESSION['logged_in_user'] = [
                        'id' => $authMhs['id'],
                        'nim' => $authMhs['nim'],
                        'nama' => $authMhs['nama'],
                        'tenant_id' => $authMhs['tenant_id'],
                        'foto' => $authMhs['foto'],
                        'role' => $authMhs['role'] ?? 'mahasiswa'
                    ];
                    $_SESSION['active_mahasiswa_id'] = $authMhs['id'];
                    $_SESSION['active_tenant_id'] = $authMhs['tenant_id'];
                }
            }
        }
    }
}

// Ambil User yang Sedang Login
$currentUser = null;
if (isset($_SESSION['logged_in_user']['id'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmtUser->execute([$_SESSION['logged_in_user']['id']]);
    $currentUser = $stmtUser->fetch();
}

if (!$currentUser && isset($_SESSION['active_mahasiswa_id'])) {
    $stmtUser = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmtUser->execute([$_SESSION['active_mahasiswa_id']]);
    $currentUser = $stmtUser->fetch();
}

if (!$currentUser) {
    // Default fallback to first student of active tenant
    $stmtMhsTenant = $pdo->prepare("SELECT * FROM mahasiswa WHERE tenant_id = ? ORDER BY id ASC LIMIT 1");
    $stmtMhsTenant->execute([$activeTenantId]);
    $currentUser = $stmtMhsTenant->fetch();
}

if (!$currentUser) {
    $currentUser = [
        'id' => 1,
        'tenant_id' => 1,
        'nim' => '235314182',
        'username' => '235314182',
        'nama' => 'Shella Ardana Andryansah',
        'prodi' => 'Informatika',
        'fakultas' => 'Fakultas Sains dan Teknologi',
        'semester_aktif' => 7,
        'dosen_pa' => 'Ir. Rosalia Arum Kumalasanti M.T.',
        'foto' => 'shella.svg'
    ];
}

// Helper Flash Message
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return '-';
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = $bulanIndo[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    return "$hari $bulan $tahun";
}
