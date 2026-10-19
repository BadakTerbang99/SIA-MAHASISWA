<?php
require_once __DIR__ . '/config/database.php';

// Auth Guard
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login.php');
    exit;
}

$mhsId = $currentUser['id'];
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Cetak per Transaksi / Kuitansi Tunggal
    $stmt = $pdo->prepare("SELECT * FROM keuangan_mahasiswa WHERE id = ? AND mahasiswa_id = ?");
    $stmt->execute([$id, $mhsId]);
    $item = $stmt->fetch();
    $isSingle = true;
} else {
    // Cetak Rekap Semua Pembayaran
    $stmt = $pdo->prepare("SELECT * FROM keuangan_mahasiswa WHERE mahasiswa_id = ? ORDER BY semester ASC, id ASC");
    $stmt->execute([$mhsId]);
    $items = $stmt->fetchAll();
    $isSingle = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $isSingle ? 'Kuitansi Pembayaran' : 'Rekap Keuangan'; ?> - <?= htmlspecialchars($currentUser['nim']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; }
    </style>
</head>
<body class="bg-white p-6 max-w-4xl mx-auto text-sm" onload="window.print()">

    <div class="no-print mb-6 p-4 bg-slate-100 rounded-xl flex items-center justify-between border border-slate-200">
        <span class="text-xs text-slate-600 font-sans">Pratinjau Kuitansi Keuangan Mahasiswa Universitas Sanata Dharma.</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-lg text-xs font-bold font-sans transition-all">
                Cetak Dokumen
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
            <h2 class="text-lg font-extrabold uppercase leading-tight">BIRO KEUANGAN & ADMINISTRASI UMUM</h2>
            <p class="text-xs">BUKTI RESMI TRANSAKSI KEUANGAN MAHASISWA</p>
        </div>
    </div>

    <?php if ($isSingle && $item): ?>
        <!-- Kuitansi Tunggal -->
        <div class="text-center my-4">
            <h3 class="text-base font-extrabold uppercase underline tracking-wide">KUITANSI PEMBAYARAN KAMPUS</h3>
            <p class="text-xs font-mono font-bold">No. Registrasi: <?= htmlspecialchars($item['kode_transaksi']); ?></p>
        </div>

        <div class="border border-black p-4 mb-6 space-y-3 text-xs">
            <div class="flex">
                <span class="w-40 font-bold">Telah Diterima Dari</span>
                <span>: <strong><?= htmlspecialchars($currentUser['nama']); ?></strong> (NIM: <?= htmlspecialchars($currentUser['nim']); ?>)</span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Program Studi / Angk.</span>
                <span>: <?= htmlspecialchars($currentUser['prodi']); ?> (Angkatan <?= htmlspecialchars($currentUser['angkatan'] ?? '2023'); ?>) / <?= htmlspecialchars($currentUser['fakultas']); ?></span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Untuk Pembayaran</span>
                <span>: <?= htmlspecialchars($item['jenis_pembayaran']); ?> (Semester <?= $item['semester']; ?>)</span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Nominal Pembayaran</span>
                <span class="text-base font-bold">: <?= formatRupiah($item['nominal']); ?></span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Metode / Channel</span>
                <span>: <?= htmlspecialchars($item['metode_pembayaran']); ?> (VA: <?= htmlspecialchars($item['nomor_va']); ?>)</span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Tanggal Pembayaran</span>
                <span>: <?= $item['tanggal_bayar'] ? formatTanggalIndo(date('Y-m-d', strtotime($item['tanggal_bayar']))) : '-'; ?></span>
            </div>
            <div class="flex">
                <span class="w-40 font-bold">Status Verifikasi</span>
                <span class="font-extrabold text-emerald-700">: [ <?= strtoupper(htmlspecialchars($item['status'])); ?> ] - REKENING KAMPUS VALID</span>
            </div>
        </div>

    <?php else: ?>
        <!-- Rekap Semua Keuangan -->
        <div class="text-center my-4">
            <h3 class="text-base font-extrabold uppercase underline tracking-wide">KARTU REKAPITULASI KEUANGAN MAHASISWA</h3>
            <p class="text-xs">NIM: <?= htmlspecialchars($currentUser['nim']); ?> &bull; <?= htmlspecialchars($currentUser['nama']); ?> &bull; Angkatan <?= htmlspecialchars($currentUser['angkatan'] ?? '2023'); ?></p>
        </div>

        <table class="w-full border-collapse border border-black text-xs mb-6">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="border border-black p-2">Kode</th>
                    <th class="border border-black p-2 text-left">Jenis Pembayaran</th>
                    <th class="border border-black p-2">Smt</th>
                    <th class="border border-black p-2">Nominal</th>
                    <th class="border border-black p-2">Metode</th>
                    <th class="border border-black p-2">Tgl Bayar</th>
                    <th class="border border-black p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; foreach ($items as $it): $total += $it['nominal']; ?>
                    <tr>
                        <td class="border border-black p-2 font-mono text-center"><?= htmlspecialchars($it['kode_transaksi']); ?></td>
                        <td class="border border-black p-2 font-semibold"><?= htmlspecialchars($it['jenis_pembayaran']); ?></td>
                        <td class="border border-black p-2 text-center"><?= $it['semester']; ?></td>
                        <td class="border border-black p-2 font-mono"><?= formatRupiah($it['nominal']); ?></td>
                        <td class="border border-black p-2"><?= htmlspecialchars($it['metode_pembayaran']); ?></td>
                        <td class="border border-black p-2 text-center"><?= $it['tanggal_bayar'] ? date('d/m/Y', strtotime($it['tanggal_bayar'])) : '-'; ?></td>
                        <td class="border border-black p-2 text-center font-bold"><?= htmlspecialchars($it['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-50">
                    <td colspan="3" class="border border-black p-2 text-right">TOTAL BIAYA:</td>
                    <td class="border border-black p-2 font-mono"><?= formatRupiah($total); ?></td>
                    <td colspan="3" class="border border-black p-2"></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <div class="flex justify-between items-end text-xs mt-10">
        <div>
            <p class="font-bold">Ketentuan Biro Keuangan:</p>
            <p>1. Dokumen ini adalah tanda bukti sah penerimaan kas universitas.</p>
            <p>2. Disimpan sebagai syarat registrasi yudisium & wisuda sarjana.</p>
        </div>
        <div class="text-center">
            <p>Yogyakarta, <?= date('d F Y'); ?></p>
            <p class="font-bold">Bagian Keuangan & Kasir,</p>
            <div class="h-16"></div>
            <p class="font-bold underline">Endang Wahyuni, S.E., M.Ak.</p>
            <p>NIP. 198006202005012003</p>
        </div>
    </div>

</body>
</html>
