<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';

// 1. Switch Student Active
if ($action === 'switch_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    if ($studentId > 0) {
        $check = $pdo->prepare("SELECT id FROM mahasiswa WHERE id = ?");
        $check->execute([$studentId]);
        if ($check->fetch()) {
            $_SESSION['active_mahasiswa_id'] = $studentId;
            setFlash('success', 'Profil mahasiswa berhasil dialihkan!');
        }
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

// 2. Update Biodata Mahasiswa
if ($action === 'update_biodata' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? $currentUser['id']);
    $nama = trim($_POST['nama'] ?? '');
    $nik = trim($_POST['nik'] ?? '');
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? 'Laki-laki');
    $agama = trim($_POST['agama'] ?? '');
    $golongan_darah = trim($_POST['golongan_darah'] ?? '');
    $fakultas = trim($_POST['fakultas'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $angkatan = (int)($_POST['angkatan'] ?? 2023);
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nama_ayah = trim($_POST['nama_ayah'] ?? '');
    $nama_ibu = trim($_POST['nama_ibu'] ?? '');
    $no_hp_ortu = trim($_POST['no_hp_ortu'] ?? '');

    if (empty($nama) || empty($email) || empty($prodi)) {
        setFlash('error', 'Nama, email, dan program studi wajib diisi!');
        header('Location: ../biodata.php');
        exit;
    }

    // Handle Foto Upload
    $fotoFilename = $currentUser['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileSize = $_FILES['foto']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExt, $allowedExt) && $fileSize <= 2 * 1024 * 1024) {
            $newFileName = 'mhs_' . $id . '_' . time() . '.' . $fileExt;
            $uploadDir = __DIR__ . '/../uploads/photos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $fotoFilename = $newFileName;
            }
        } else {
            setFlash('warning', 'Format foto harus JPG/PNG maksimal 2MB. Foto tidak diubah.');
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET 
            nama = ?, nik = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, agama = ?, golongan_darah = ?,
            fakultas = ?, prodi = ?, angkatan = ?, email = ?, no_hp = ?, alamat = ?, nama_ayah = ?, nama_ibu = ?, no_hp_ortu = ?, foto = ?
            WHERE id = ?");
        $stmt->execute([
            $nama, $nik, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama, $golongan_darah,
            $fakultas, $prodi, $angkatan, $email, $no_hp, $alamat, $nama_ayah, $nama_ibu, $no_hp_ortu, $fotoFilename,
            $id
        ]);
        setFlash('success', 'Biodata mahasiswa berhasil diperbarui!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui biodata: ' . $e->getMessage());
    }

    header('Location: ../biodata.php');
    exit;
}

// 3. Tambah Mahasiswa Baru
if ($action === 'create_mahasiswa' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $prodi = trim($_POST['prodi'] ?? 'S1 Teknik Informatika');
    $fakultas = trim($_POST['fakultas'] ?? 'Fakultas Teknik & Ilmu Komputer');
    $semester = (int)($_POST['semester_aktif'] ?? 1);
    $email = trim($_POST['email'] ?? strtolower($nim) . '@student.ac.id');
    $no_hp = trim($_POST['no_hp'] ?? '081234567890');

    if (empty($nim) || empty($nama)) {
        setFlash('error', 'NIM dan Nama Mahasiswa wajib diisi!');
        header('Location: ../biodata.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, fakultas, semester_aktif, email, no_hp, status_akademik, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Aktif', 'default_student.svg')");
        $stmt->execute([$nim, $nama, $prodi, $fakultas, $semester, $email, $no_hp]);
        $newId = $pdo->lastInsertId();
        $_SESSION['active_mahasiswa_id'] = $newId;
        setFlash('success', 'Mahasiswa baru berhasil ditambahkan dan dijadikan profil aktif!');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal menambah mahasiswa: ' . $e->getMessage());
    }

    header('Location: ../biodata.php');
    exit;
}

header('Location: ../biodata.php');
exit;
