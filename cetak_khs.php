<?php
require_once __DIR__ . '/config/database.php';

// Auth Guard
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login.php');
    exit;
}

$mhsId = $currentUser['id'];
$semester = $_GET['semester'] ?? '1';

if ($semester === 'transkrip') {
    $title = "TRANSKRIP AKADEMIK SEMENTARA";
    $stmt = $pdo->prepare("SELECT * FROM nilai_akademik WHERE mahasiswa_id = ? ORDER BY semester ASC, kode_mk ASC");
    $stmt->execute([$mhsId]);
} else {
    $title = "KARTU HASIL STUDI (KHS) SEMESTER " . htmlspecialchars($semester);
    $stmt = $pdo->prepare("SELECT * FROM nilai_akademik WHERE mahasiswa_id = ? AND semester = ? ORDER BY kode_mk ASC");
    $stmt->execute([$mhsId, (int)$semester]);
}
$nilaiList = $stmt->fetchAll();

// Hitung Statistik
$totalSks = 0;
$totalMutu = 0;
foreach ($nilaiList as $n) {
    $totalSks += $n['sks'];
    $totalMutu += ($n['sks'] * $n['bobot']);
}
$ips = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0.00;

// Hitung IPK Keseluruhan
$stmtAll = $pdo->prepare("SELECT sks, bobot FROM nilai_akademik WHERE mahasiswa_id = ?");
$stmtAll->execute([$mhsId]);
$allN = $stmtAll->fetchAll();
$allSks = 0;
$allMutu = 0;
foreach ($allN as $a) {
    $allSks += $a['sks'];
    $allMutu += ($a['sks'] * $a['bobot']);
}
$ipk = $allSks > 0 ? round($allMutu / $allSks, 2) : 0.00;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title; ?> - <?= htmlspecialchars($currentUser['nim']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; }
    </style>
</head>
<body class="bg-white p-6 max-w-4xl mx-auto text-sm" onload="window.print()">

    <!-- Print Control Buttons (Hidden on print) -->
    <div class="no-print mb-6 p-4 bg-slate-100 rounded-xl flex items-center justify-between border border-slate-200">
        <span class="text-xs text-slate-600 font-sans">Pratinjau Cetak KHS Resmi Universitas Sanata Dharma.</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-lg text-xs font-bold font-sans transition-all">
                Cetak Dokumen
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold font-sans transition-all">
                Tutup
            </button>
        </div>
    </div>

    <!-- Official University Letterhead (Kop Surat) -->
    <div class="border-b-4 border-double border-black pb-3 mb-4 flex items-center gap-4">
        <div class="w-20 h-20 shrink-0">
            <img src="assets/images/<?= htmlspecialchars($currentTenant['logo']); ?>" alt="Logo" class="w-full h-full object-contain">
        </div>
        <div class="text-center flex-1">
            <h1 class="text-lg font-bold tracking-wider uppercase leading-tight">YAYASAN SANATA DHARMA</h1>
            <h2 class="text-xl font-extrabold tracking-wide uppercase leading-tight"><?= strtoupper(htmlspecialchars($currentTenant['nama_kampus'])); ?></h2>
            <h3 class="text-base font-bold uppercase leading-tight"><?= strtoupper(htmlspecialchars($currentUser['fakultas'])); ?></h3>
            <p class="text-xs mt-1">Kampus Paingan, Maguwoharjo, Depok, Sleman, Yogyakarta 55282 &bull; Telp: (0274) 513301 &bull; Web: www.usd.ac.id</p>
        </div>
    </div>

    <!-- Document Title -->
    <div class="text-center my-4">
        <h3 class="text-base font-extrabold uppercase underline tracking-wide"><?= $title; ?></h3>
        <p class="text-xs">Tahun Akademik: 2026/2027 Ganjil</p>
    </div>

    <!-- Student Bio Grid -->
    <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs mb-4">
        <div class="flex">
            <span class="w-32 font-bold">NIM</span>
            <span>: <?= htmlspecialchars($currentUser['nim']); ?></span>
        </div>
        <div class="flex">
            <span class="w-32 font-bold">Fakultas</span>
            <span>: <?= htmlspecialchars($currentUser['fakultas']); ?></span>
        </div>
        <div class="flex">
            <span class="w-32 font-bold">Nama Mahasiswa</span>
            <span>: <?= htmlspecialchars($currentUser['nama']); ?></span>
        </div>
        <div class="flex">
            <span class="w-32 font-bold">Program Studi / Angk.</span>
            <span>: <?= htmlspecialchars($currentUser['prodi']); ?> (Angkatan <?= htmlspecialchars($currentUser['angkatan'] ?? '2023'); ?>)</span>
        </div>
        <div class="flex">
            <span class="w-32 font-bold">Dosen Pembimbing</span>
            <span>: <?= htmlspecialchars($currentUser['dosen_pa'] ?: 'Ir. Rosalia Arum Kumalasanti M.T.'); ?></span>
        </div>
        <div class="flex">
            <span class="w-32 font-bold">Status Akademik</span>
            <span>: <?= htmlspecialchars($currentUser['status_akademik']); ?> (Semester <?= htmlspecialchars($currentUser['semester_aktif']); ?>)</span>
        </div>
    </div>

    <!-- Table Nilai -->
    <table class="w-full border-collapse border border-black text-xs mb-4">
        <thead>
            <tr class="bg-gray-100 font-bold text-center">
                <th class="border border-black p-2">No</th>
                <?php if ($semester === 'transkrip'): ?>
                    <th class="border border-black p-2">Smt</th>
                <?php endif; ?>
                <th class="border border-black p-2">Kode MK</th>
                <th class="border border-black p-2 text-left">Nama Mata Kuliah</th>
                <th class="border border-black p-2">SKS (K)</th>
                <th class="border border-black p-2">Nilai Huruf (HM)</th>
                <th class="border border-black p-2">Bobot (AM)</th>
                <th class="border border-black p-2">Mutu (M)</th>
                <th class="border border-black p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($nilaiList as $n): ?>
                <?php $mutu = $n['sks'] * $n['bobot']; ?>
                <tr>
                    <td class="border border-black p-1.5 text-center"><?= $no++; ?></td>
                    <?php if ($semester === 'transkrip'): ?>
                        <td class="border border-black p-1.5 text-center"><?= $n['semester']; ?></td>
                    <?php endif; ?>
                    <td class="border border-black p-1.5 text-center font-mono"><?= htmlspecialchars($n['kode_mk']); ?></td>
                    <td class="border border-black p-1.5 font-semibold"><?= htmlspecialchars($n['nama_mk']); ?></td>
                    <td class="border border-black p-1.5 text-center"><?= $n['sks']; ?></td>
                    <td class="border border-black p-1.5 text-center font-bold"><?= htmlspecialchars($n['nilai_huruf']); ?></td>
                    <td class="border border-black p-1.5 text-center"><?= number_format($n['bobot'], 2); ?></td>
                    <td class="border border-black p-1.5 text-center font-bold"><?= number_format($mutu, 2); ?></td>
                    <td class="border border-black p-1.5 text-center"><?= htmlspecialchars($n['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="font-bold bg-gray-50">
                <td colspan="<?= $semester === 'transkrip' ? 4 : 3; ?>" class="border border-black p-1.5 text-right">JUMLAH:</td>
                <td class="border border-black p-1.5 text-center"><?= $totalSks; ?> SKS</td>
                <td colspan="2" class="border border-black p-1.5 text-right">TOTAL MUTU:</td>
                <td class="border border-black p-1.5 text-center"><?= number_format($totalMutu, 2); ?></td>
                <td class="border border-black p-1.5"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Academic Summary Card -->
    <div class="border border-black p-3 text-xs mb-8 flex justify-between">
        <div>
            <p><strong>Total SKS Semester Ini:</strong> <?= $totalSks; ?> SKS</p>
            <p><strong>Indeks Prestasi Semester (IPS):</strong> <?= number_format($ips, 2); ?></p>
        </div>
        <div>
            <p><strong>Total Kumulatif SKS Lulus:</strong> <?= $allSks; ?> SKS</p>
            <p><strong>Indeks Prestasi Kumulatif (IPK):</strong> <?= number_format($ipk, 2); ?></p>
        </div>
        <div>
            <p><strong>Beban Maksimum Semester Depan:</strong> 24 SKS</p>
            <p><strong>Predikat:</strong> <?= $ipk >= 3.51 ? 'DENGAN PUJIAN (CUMLAUDE)' : 'SANGAT MEMUASKAN'; ?></p>
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="grid grid-cols-2 gap-8 text-xs text-center mt-6">
        <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Dosen Pembimbing Akademik,</p>
            <div class="h-16"></div>
            <p class="font-bold underline"><?= htmlspecialchars($currentUser['dosen_pa'] ?: 'Ir. Rosalia Arum Kumalasanti M.T.'); ?></p>
            <p>NIP. <?= htmlspecialchars($currentUser['nip_dosen_pa'] ?: '196803201994032001'); ?></p>
        </div>

        <div>
            <p>Yogyakarta, <?= date('d F Y'); ?></p>
            <p class="font-bold">Ketua Program Studi <?= htmlspecialchars($currentUser['prodi']); ?>,</p>
            <div class="h-16"></div>
            <p class="font-bold underline">Dr. Ir. I Gusti Bagus Budi Dharma, M.T.</p>
            <p>NIP. 197108151998031001</p>
        </div>
    </div>

</body>
</html>
