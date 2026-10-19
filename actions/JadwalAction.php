<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';
$mhsId = $currentUser['id'];

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mk = trim($_POST['kode_mk'] ?? '');
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks = (int)($_POST['sks'] ?? 3);
    $hari = trim($_POST['hari'] ?? 'Senin');
    $jam_mulai = trim($_POST['jam_mulai'] ?? '08:00');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '10:30');
    $ruangan = trim($_POST['ruangan'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester = (int)($_POST['semester'] ?? $currentUser['semester_aktif']);
    $kelas = trim($_POST['kelas'] ?? 'A');
    $link_kelas = trim($_POST['link_kelas'] ?? '');

    if (empty($kode_mk) || empty($nama_mk) || empty($ruangan) || empty($dosen)) {
        setFlash('error', 'Semua bidang wajib diisi lengkap!');
        header('Location: ../jadwal.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO jadwal_kuliah (mahasiswa_id, kode_mk, nama_mk, sks, hari, jam_mulai, jam_selesai, ruangan, dosen, semester, kelas, link_kelas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$mhsId, $kode_mk, $nama_mk, $sks, $hari, $jam_mulai, $jam_selesai, $ruangan, $dosen, $semester, $kelas, $link_kelas]);
        setFlash('success', 'Jadwal kuliah baru berhasil ditambahkan!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal menambah jadwal: ' . $e->getMessage());
    }

    header('Location: ../jadwal.php');
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $kode_mk = trim($_POST['kode_mk'] ?? '');
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks = (int)($_POST['sks'] ?? 3);
    $hari = trim($_POST['hari'] ?? 'Senin');
    $jam_mulai = trim($_POST['jam_mulai'] ?? '08:00');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '10:30');
    $ruangan = trim($_POST['ruangan'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester = (int)($_POST['semester'] ?? $currentUser['semester_aktif']);
    $kelas = trim($_POST['kelas'] ?? 'A');
    $link_kelas = trim($_POST['link_kelas'] ?? '');

    if (empty($id) || empty($kode_mk) || empty($nama_mk) || empty($ruangan) || empty($dosen)) {
        setFlash('error', 'Data tidak lengkap untuk diperbarui!');
        header('Location: ../jadwal.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE jadwal_kuliah SET kode_mk = ?, nama_mk = ?, sks = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, ruangan = ?, dosen = ?, semester = ?, kelas = ?, link_kelas = ? WHERE id = ? AND mahasiswa_id = ?");
        $stmt->execute([$kode_mk, $nama_mk, $sks, $hari, $jam_mulai, $jam_selesai, $ruangan, $dosen, $semester, $kelas, $link_kelas, $id, $mhsId]);
        setFlash('success', 'Jadwal kuliah berhasil diperbarui!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
    }

    header('Location: ../jadwal.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE id = ? AND mahasiswa_id = ?");
            $stmt->execute([$id, $mhsId]);
            setFlash('success', 'Jadwal kuliah berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
    header('Location: ../jadwal.php');
    exit;
}

header('Location: ../jadwal.php');
exit;
