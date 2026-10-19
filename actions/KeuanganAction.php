<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';
$mhsId = $currentUser['id'];

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_pembayaran = trim($_POST['jenis_pembayaran'] ?? '');
    $semester = (int)($_POST['semester'] ?? $currentUser['semester_aktif']);
    $nominal = (float)($_POST['nominal'] ?? 0);
    $tanggal_jatuh_tempo = trim($_POST['tanggal_jatuh_tempo'] ?? date('Y-m-d', strtotime('+30 days')));
    $keterangan = trim($_POST['keterangan'] ?? '');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? 'Virtual Account Mandiri');
    
    // Auto generate kode transaksi & VA
    $kode_transaksi = 'INV-' . date('Y') . '-' . $semester . '-' . rand(100, 999);
    $nomor_va = '88' . rand(1000, 9999) . $currentUser['nim'];

    if (empty($jenis_pembayaran) || $nominal <= 0) {
        setFlash('error', 'Jenis pembayaran dan nominal valid wajib diisi!');
        header('Location: ../keuangan.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO keuangan_mahasiswa (mahasiswa_id, kode_transaksi, jenis_pembayaran, semester, nominal, tanggal_jatuh_tempo, metode_pembayaran, nomor_va, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Belum Lunas', ?)");
        $stmt->execute([$mhsId, $kode_transaksi, $jenis_pembayaran, $semester, $nominal, $tanggal_jatuh_tempo, $metode_pembayaran, $nomor_va, $keterangan]);
        setFlash('success', 'Tagihan keuangan baru berhasil dibuat!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal membuat tagihan: ' . $e->getMessage());
    }

    header('Location: ../keuangan.php');
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $jenis_pembayaran = trim($_POST['jenis_pembayaran'] ?? '');
    $semester = (int)($_POST['semester'] ?? $currentUser['semester_aktif']);
    $nominal = (float)($_POST['nominal'] ?? 0);
    $tanggal_jatuh_tempo = trim($_POST['tanggal_jatuh_tempo'] ?? '');
    $status = trim($_POST['status'] ?? 'Belum Lunas');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($id <= 0 || empty($jenis_pembayaran) || $nominal <= 0) {
        setFlash('error', 'Data tidak valid untuk diperbarui!');
        header('Location: ../keuangan.php');
        exit;
    }

    $tanggal_bayar = ($status === 'Lunas') ? date('Y-m-d H:i:s') : null;

    try {
        $stmt = $pdo->prepare("UPDATE keuangan_mahasiswa SET jenis_pembayaran = ?, semester = ?, nominal = ?, tanggal_jatuh_tempo = ?, status = ?, metode_pembayaran = ?, keterangan = ?, tanggal_bayar = ? WHERE id = ? AND mahasiswa_id = ?");
        $stmt->execute([$jenis_pembayaran, $semester, $nominal, $tanggal_jatuh_tempo, $status, $metode_pembayaran, $keterangan, $tanggal_bayar, $id, $mhsId]);
        setFlash('success', 'Data tagihan berhasil diperbarui!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui tagihan: ' . $e->getMessage());
    }

    header('Location: ../keuangan.php');
    exit;
}

if ($action === 'pay' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? 'Virtual Account Mandiri');

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE keuangan_mahasiswa SET status = 'Lunas', tanggal_bayar = CURRENT_TIMESTAMP, metode_pembayaran = ? WHERE id = ? AND mahasiswa_id = ?");
            $stmt->execute([$metode_pembayaran, $id, $mhsId]);
            setFlash('success', 'Pembayaran tagihan berhasil dikonfirmasi dan lunas!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal melakukan pembayaran: ' . $e->getMessage());
        }
    }
    header('Location: ../keuangan.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM keuangan_mahasiswa WHERE id = ? AND mahasiswa_id = ?");
            $stmt->execute([$id, $mhsId]);
            setFlash('success', 'Data tagihan keuangan berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }
    header('Location: ../keuangan.php');
    exit;
}

header('Location: ../keuangan.php');
exit;
