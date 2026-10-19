<?php
require_once __DIR__ . '/config/database.php';

// Auth Guard
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login.php');
    exit;
}

$mhsId = $currentUser['id'];
$stmt = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE mahasiswa_id = ? ORDER BY 
    CASE hari 
        WHEN 'Senin' THEN 1 
        WHEN 'Selasa' THEN 2 
        WHEN 'Rabu' THEN 3 
        WHEN 'Kamis' THEN 4 
        WHEN 'Jumat' THEN 5 
        WHEN 'Sabtu' THEN 6 
        ELSE 7 
    END, jam_mulai ASC");
$stmt->execute([$mhsId]);
$jadwalList = $stmt->fetchAll();

$totalSks = 0;
foreach ($jadwalList as $j) {
    $totalSks += $j['sks'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah - <?= htmlspecialchars($currentUser['nim']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; }
    </style>
</head>
<body class="bg-white p-6 max-w-5xl mx-auto text-sm" onload="window.print()">

    <div class="no-print mb-6 p-4 bg-slate-100 rounded-xl flex items-center justify-between border border-slate-200">
        <span class="text-xs text-slate-600 font-sans">Pratinjau Cetak Jadwal Kuliah Resmi Universitas Sanata Dharma.</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-lg text-xs font-bold font-sans transition-all">
                Cetak Jadwal
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold font-sans transition-all">
                Tutup
            </button>
        </div>
    </div>

    <!-- Letterhead -->
    <div class="border-b-4 border-double border-black pb-3 mb-4 flex items-center gap-4">
        <div class="w-16 h-16 shrink-0">
            <img src="assets/images/<?= htmlspecialchars($currentTenant['logo']); ?>" alt="Logo" class="w-full h-full object-contain">
        </div>
        <div class="text-center flex-1">
            <h1 class="text-base font-bold uppercase leading-tight"><?= strtoupper(htmlspecialchars($currentTenant['nama_kampus'])); ?></h1>
            <h2 class="text-lg font-extrabold uppercase leading-tight"><?= strtoupper(htmlspecialchars($currentUser['fakultas'])); ?></h2>
            <p class="text-xs">JADWAL PERKULIAHAN MAHASISWA SEMESTER GANJIL T.A. 2026/2027</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs mb-4">
        <div class="flex"><span class="w-32 font-bold">NIM</span><span>: <?= htmlspecialchars($currentUser['nim']); ?></span></div>
        <div class="flex"><span class="w-32 font-bold">Program Studi</span><span>: <?= htmlspecialchars($currentUser['prodi']); ?> (Angkatan <?= htmlspecialchars($currentUser['angkatan'] ?? '2023'); ?>)</span></div>
        <div class="flex"><span class="w-32 font-bold">Nama Lengkap</span><span>: <?= htmlspecialchars($currentUser['nama']); ?></span></div>
        <div class="flex"><span class="w-32 font-bold">Total SKS Diambil</span><span>: <?= $totalSks; ?> SKS (Semester <?= htmlspecialchars($currentUser['semester_aktif']); ?>)</span></div>
    </div>

    <table class="w-full border-collapse border border-black text-xs mb-6">
        <thead>
            <tr class="bg-gray-100 font-bold text-center">
                <th class="border border-black p-2">Hari</th>
                <th class="border border-black p-2">Waktu</th>
                <th class="border border-black p-2">Kode MK</th>
                <th class="border border-black p-2 text-left">Nama Mata Kuliah</th>
                <th class="border border-black p-2">SKS</th>
                <th class="border border-black p-2">Kelas</th>
                <th class="border border-black p-2">Ruang / Lab</th>
                <th class="border border-black p-2 text-left">Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jadwalList as $j): ?>
                <tr>
                    <td class="border border-black p-2 text-center font-bold"><?= htmlspecialchars($j['hari']); ?></td>
                    <td class="border border-black p-2 text-center font-mono"><?= htmlspecialchars($j['jam_mulai']) . ' - ' . htmlspecialchars($j['jam_selesai']); ?></td>
                    <td class="border border-black p-2 text-center font-mono"><?= htmlspecialchars($j['kode_mk']); ?></td>
                    <td class="border border-black p-2 font-semibold"><?= htmlspecialchars($j['nama_mk']); ?></td>
                    <td class="border border-black p-2 text-center font-bold"><?= $j['sks']; ?></td>
                    <td class="border border-black p-2 text-center"><?= htmlspecialchars($j['kelas']); ?></td>
                    <td class="border border-black p-2 text-center font-semibold"><?= htmlspecialchars($j['ruangan']); ?></td>
                    <td class="border border-black p-2"><?= htmlspecialchars($j['dosen']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="flex justify-between items-end text-xs mt-10">
        <div>
            <p>Catatan:</p>
            <p>1. Hadir 10 menit sebelum perkuliahan dimulai.</p>
            <p>2. Kehadiran minimal 75% untuk dapat mengikuti UAS.</p>
        </div>
        <div class="text-center">
            <p>Yogyakarta, <?= date('d F Y'); ?></p>
            <p class="font-bold">Mahasiswa Yang Bersangkutan,</p>
            <div class="h-16"></div>
            <p class="font-bold underline"><?= htmlspecialchars($currentUser['nama']); ?></p>
            <p>NIM. <?= htmlspecialchars($currentUser['nim']); ?></p>
        </div>
    </div>

</body>
</html>
