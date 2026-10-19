<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';
$mhsId = $currentUser['id'];

function calculateGrade($huruf, $angka = null) {
    $huruf = strtoupper(trim($huruf));
    $map = [
        'A'  => ['bobot' => 4.0, 'status' => 'Lulus', 'default_angka' => 90.0],
        'A-' => ['bobot' => 3.7, 'status' => 'Lulus', 'default_angka' => 82.5],
        'B+' => ['bobot' => 3.5, 'status' => 'Lulus', 'default_angka' => 77.5],
        'B'  => ['bobot' => 3.0, 'status' => 'Lulus', 'default_angka' => 72.0],
        'B-' => ['bobot' => 2.7, 'status' => 'Lulus', 'default_angka' => 67.0],
        'C+' => ['bobot' => 2.5, 'status' => 'Lulus', 'default_angka' => 62.0],
        'C'  => ['bobot' => 2.0, 'status' => 'Lulus', 'default_angka' => 57.0],
        'D'  => ['bobot' => 1.0, 'status' => 'Tidak Lulus', 'default_angka' => 45.0],
        'E'  => ['bobot' => 0.0, 'status' => 'Tidak Lulus', 'default_angka' => 30.0]
    ];

    if (!isset($map[$huruf])) {
        $huruf = 'A';
    }

    $bobot = $map[$huruf]['bobot'];
    $status = $map[$huruf]['status'];
    $finalAngka = ($angka !== null && $angka !== '' && is_numeric($angka)) ? (float)$angka : $map[$huruf]['default_angka'];

    return [$huruf, $finalAngka, $bobot, $status];
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = (int)($_POST['semester'] ?? 1);
    $kode_mk = trim($_POST['kode_mk'] ?? '');
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks = (int)($_POST['sks'] ?? 3);
    $nilai_huruf = trim($_POST['nilai_huruf'] ?? 'A');
    $nilai_angka = $_POST['nilai_angka'] ?? null;

    if (empty($kode_mk) || empty($nama_mk) || $sks <= 0) {
        setFlash('error', 'Semua bidang wajib diisi dengan benar!');
        header('Location: ../nilai.php?semester=' . $semester);
        exit;
    }

    list($huruf, $angka, $bobot, $status) = calculateGrade($nilai_huruf, $nilai_angka);

    try {
        $stmt = $pdo->prepare("INSERT INTO nilai_akademik (mahasiswa_id, semester, kode_mk, nama_mk, sks, nilai_huruf, nilai_angka, bobot, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$mhsId, $semester, $kode_mk, $nama_mk, $sks, $huruf, $angka, $bobot, $status]);
        setFlash('success', "Nilai mata kuliah $nama_mk berhasil ditambahkan!");
    } catch (PDOException $e) {
        setFlash('error', 'Gagal menambah nilai: ' . $e->getMessage());
    }

    header('Location: ../nilai.php?semester=' . $semester);
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $semester = (int)($_POST['semester'] ?? 1);
    $kode_mk = trim($_POST['kode_mk'] ?? '');
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks = (int)($_POST['sks'] ?? 3);
    $nilai_huruf = trim($_POST['nilai_huruf'] ?? 'A');
    $nilai_angka = $_POST['nilai_angka'] ?? null;

    if ($id <= 0 || empty($kode_mk) || empty($nama_mk) || $sks <= 0) {
        setFlash('error', 'Data tidak valid untuk diperbarui!');
        header('Location: ../nilai.php?semester=' . $semester);
        exit;
    }

    list($huruf, $angka, $bobot, $status) = calculateGrade($nilai_huruf, $nilai_angka);

    try {
        $stmt = $pdo->prepare("UPDATE nilai_akademik SET semester = ?, kode_mk = ?, nama_mk = ?, sks = ?, nilai_huruf = ?, nilai_angka = ?, bobot = ?, status = ? WHERE id = ? AND mahasiswa_id = ?");
        $stmt->execute([$semester, $kode_mk, $nama_mk, $sks, $huruf, $angka, $bobot, $status, $id, $mhsId]);
        setFlash('success', "Data nilai $nama_mk berhasil diperbarui!");
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui nilai: ' . $e->getMessage());
    }

    header('Location: ../nilai.php?semester=' . $semester);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $semester = (int)($_GET['semester'] ?? 1);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM nilai_akademik WHERE id = ? AND mahasiswa_id = ?");
            $stmt->execute([$id, $mhsId]);
            setFlash('success', 'Data nilai berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menghapus nilai: ' . $e->getMessage());
        }
    }
    header('Location: ../nilai.php?semester=' . $semester);
    exit;
}

header('Location: ../nilai.php');
exit;
