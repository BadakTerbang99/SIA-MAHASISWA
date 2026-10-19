<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';

// 1. Switch Tenant Context
if ($action === 'switch_tenant') {
    $tenantId = (int)($_REQUEST['tenant_id'] ?? 1);
    if ($tenantId > 0) {
        $stmt = $pdo->prepare("SELECT id FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        if ($stmt->fetch()) {
            $_SESSION['active_tenant_id'] = $tenantId;
            unset($_SESSION['active_mahasiswa_id']); // Reset active student to pick tenant's student
            setFlash('success', 'Berhasil beralih ke lingkungan kampus mitra!');
        }
    }
    $redirect = $_REQUEST['redirect'] ?? '../index.php';
    header("Location: $redirect");
    exit;
}

// 2. Register / Create New Tenant (From SaaS Landing Page or Admin)
if (($action === 'register_tenant' || $action === 'create_tenant') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kampus = trim($_POST['nama_kampus'] ?? '');
    $nama_singkat = trim($_POST['nama_singkat'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $paket = trim($_POST['paket'] ?? 'Pro');
    $email_kontak = trim($_POST['email_kontak'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $warna_primer = trim($_POST['warna_primer'] ?? '#4f46e5');
    $warna_aksen = trim($_POST['warna_aksen'] ?? '#3b82f6');

    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $nama_singkat ?: $nama_kampus));
    }

    // Pricing & Quota mapping based on Plan
    $planMap = [
        'Starter' => ['biaya' => 1500000, 'kuota' => 500],
        'Pro' => ['biaya' => 3500000, 'kuota' => 2500],
        'Enterprise' => ['biaya' => 7500000, 'kuota' => 10000]
    ];
    $biaya = $planMap[$paket]['biaya'] ?? 3500000;
    $kuota = $planMap[$paket]['kuota'] ?? 2500;
    $status = ($action === 'register_tenant') ? 'Masa Percobaan' : 'Aktif';
    $kadaluarsa = date('Y-m-d', strtotime('+30 days'));

    if (empty($nama_kampus) || empty($email_kontak)) {
        setFlash('error', 'Nama perguruan tinggi dan email resmi wajib diisi!');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../saas.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tenants (
            nama_kampus, nama_singkat, slug, logo, warna_primer, warna_aksen, paket, status_langganan, tanggal_kadaluarsa, biaya_bulanan, kuota_mahasiswa, email_kontak, no_telp, alamat
        ) VALUES (?, ?, ?, 'logo.svg', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nama_kampus, $nama_singkat ?: $nama_kampus, $slug, $warna_primer, $warna_aksen,
            $paket, $status, $kadaluarsa, $biaya, $kuota, $email_kontak, $no_telp, $alamat
        ]);
        $newTenantId = $pdo->lastInsertId();

        // Seed 1 Mahasiswa default untuk kampus baru ini
        $stmtMhs = $pdo->prepare("INSERT INTO mahasiswa (
            tenant_id, nim, nama, nik, fakultas, prodi, jenjang, angkatan, semester_aktif, status_akademik,
            email, no_hp, alamat, dosen_pa, foto
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'S1', ?, 1, 'Aktif',
            ?, ?, ?, 'Dr. Ir. Rektor Pembimbing, M.Sc.', 'default_student.svg'
        )");
        $nimBaru = date('y') . '0100' . rand(1000, 9999);
        $stmtMhs->execute([
            $newTenantId, $nimBaru, 'Mahasiswa Perdana', '3201012301010001',
            'Fakultas Teknologi & Terapan', 'S1 Sistem Informasi', date('Y'),
            'mhs.' . $slug . '@student.ac.id', $no_telp, $alamat
        ]);
        $mhsBaruId = $pdo->lastInsertId();

        // Seed 1 jadwal & 1 tagihan
        $pdo->prepare("INSERT INTO jadwal_kuliah (mahasiswa_id, tenant_id, kode_mk, nama_mk, sks, hari, jam_mulai, jam_selesai, ruangan, dosen, semester, kelas)
            VALUES (?, ?, 'MK101', 'Pengantar Sistem Informasi Cloud', 3, 'Senin', '08:00', '10:30', 'Ruang Utama 101', 'Dr. Pembimbing, M.Sc.', 1, 'A')")
            ->execute([$mhsBaruId, $newTenantId]);

        $pdo->prepare("INSERT INTO keuangan_mahasiswa (mahasiswa_id, tenant_id, kode_transaksi, jenis_pembayaran, semester, nominal, tanggal_jatuh_tempo, status)
            VALUES (?, ?, ?, 'Uang Kuliah Tunggal (UKT) Semester 1', 1, 4500000, ?, 'Belum Lunas')")
            ->execute([$mhsBaruId, $newTenantId, 'INV-' . strtoupper($slug) . '-01', date('Y-m-d', strtotime('+14 days'))]);

        // Aktifkan tenant baru di sesi
        $_SESSION['active_tenant_id'] = $newTenantId;
        $_SESSION['active_mahasiswa_id'] = $mhsBaruId;

        setFlash('success', "Kampus $nama_kampus berhasil didaftarkan ke platform SaaS!");

        if ($action === 'create_tenant') {
            header('Location: ../saas_admin.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } catch (PDOException $e) {
        setFlash('error', 'Gagal mendaftarkan kampus: ' . $e->getMessage());
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../saas.php'));
        exit;
    }
}

// 3. Update Tenant
if ($action === 'update_tenant' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama_kampus = trim($_POST['nama_kampus'] ?? '');
    $nama_singkat = trim($_POST['nama_singkat'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $paket = trim($_POST['paket'] ?? 'Pro');
    $status_langganan = trim($_POST['status_langganan'] ?? 'Aktif');
    $tanggal_kadaluarsa = trim($_POST['tanggal_kadaluarsa'] ?? '');
    $kuota_mahasiswa = (int)($_POST['kuota_mahasiswa'] ?? 2500);
    $biaya_bulanan = (float)($_POST['biaya_bulanan'] ?? 3500000);
    $email_kontak = trim($_POST['email_kontak'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $warna_primer = trim($_POST['warna_primer'] ?? '#4f46e5');

    if ($id <= 0 || empty($nama_kampus)) {
        setFlash('error', 'Data kampus tidak valid!');
        header('Location: ../saas_admin.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tenants SET 
            nama_kampus = ?, nama_singkat = ?, slug = ?, paket = ?, status_langganan = ?,
            tanggal_kadaluarsa = ?, kuota_mahasiswa = ?, biaya_bulanan = ?, email_kontak = ?,
            no_telp = ?, warna_primer = ?
            WHERE id = ?");
        $stmt->execute([
            $nama_kampus, $nama_singkat, $slug, $paket, $status_langganan,
            $tanggal_kadaluarsa, $kuota_mahasiswa, $biaya_bulanan, $email_kontak,
            $no_telp, $warna_primer, $id
        ]);
        setFlash('success', "Data kampus $nama_kampus berhasil diperbarui!");
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui kampus: ' . $e->getMessage());
    }

    header('Location: ../saas_admin.php');
    exit;
}

// 4. Delete Tenant
if ($action === 'delete_tenant') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 1) { // Proteksi tenant utama ID 1
        try {
            $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ?");
            $stmt->execute([$id]);
            if ($_SESSION['active_tenant_id'] == $id) {
                $_SESSION['active_tenant_id'] = 1;
                unset($_SESSION['active_mahasiswa_id']);
            }
            setFlash('success', 'Kampus mitra berhasil dihapus dari sistem SaaS!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menghapus kampus: ' . $e->getMessage());
        }
    } else {
        setFlash('warning', 'Kampus default utama tidak boleh dihapus.');
    }

    header('Location: ../saas_admin.php');
    exit;
}

header('Location: ../saas.php');
exit;
