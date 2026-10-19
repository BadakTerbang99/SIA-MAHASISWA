<?php
$pageTitle = "Kartu Keuangan";
$pageHeader = "Administrasi & Tagihan Keuangan Mahasiswa";
require_once __DIR__ . '/includes/header.php';

$mhsId = $currentUser['id'];

// Filter Status
$filterStatus = $_GET['status'] ?? 'Semua';

if ($filterStatus === 'Semua') {
    $stmtKeu = $pdo->prepare("SELECT * FROM keuangan_mahasiswa WHERE mahasiswa_id = ? ORDER BY semester DESC, id DESC");
    $stmtKeu->execute([$mhsId]);
} else {
    $stmtKeu = $pdo->prepare("SELECT * FROM keuangan_mahasiswa WHERE mahasiswa_id = ? AND status = ? ORDER BY semester DESC, id DESC");
    $stmtKeu->execute([$mhsId, $filterStatus]);
}
$listKeuangan = $stmtKeu->fetchAll();

// Hitung KPI Keuangan
$stmtKpi = $pdo->prepare("SELECT 
    COUNT(*) as total_transaksi,
    SUM(nominal) as total_nominal,
    SUM(CASE WHEN status = 'Lunas' THEN nominal ELSE 0 END) as total_lunas,
    SUM(CASE WHEN status = 'Belum Lunas' THEN nominal ELSE 0 END) as total_tunggakan
    FROM keuangan_mahasiswa WHERE mahasiswa_id = ?");
$stmtKpi->execute([$mhsId]);
$kpi = $stmtKpi->fetch();

$totalTagihan = $kpi['total_nominal'] ?? 0;
$totalLunas = $kpi['total_lunas'] ?? 0;
$totalTunggakan = $kpi['total_tunggakan'] ?? 0;
?>

<!-- Header Action Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Kartu Pembayaran & Tagihan Mahasiswa</h2>
        <p class="text-xs text-slate-500">Pantau riwayat pembayaran UKT, tagihan praktikum, dan nomor Virtual Account perbankan</p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <a href="cetak_keuangan.php" target="_blank" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors">
            <i class="fas fa-print text-usd-700"></i> Cetak Rekap Keuangan
        </a>

        <!-- Konfirmasi / Catat Pembayaran (CRUD) -->
        <button onclick="openAddModal()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-md shadow-usd-700/20 flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> Catat / Ajukan Pembayaran UKT
        </button>
    </div>
</div>

<!-- Financial KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Keseluruhan Tagihan</p>
            <p class="text-lg font-bold text-slate-800"><?= formatRupiah($totalTagihan); ?></p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Sudah Terbayar</p>
            <p class="text-lg font-bold text-emerald-600"><?= formatRupiah($totalLunas); ?></p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Sisa Tagihan Tertunggak</p>
            <p class="text-lg font-bold text-rose-600"><?= formatRupiah($totalTunggakan); ?></p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg <?= $totalTunggakan > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'; ?> flex items-center justify-center text-lg">
            <i class="fas <?= $totalTunggakan > 0 ? 'fa-clock' : 'fa-award'; ?>"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Status Administrasi</p>
            <p class="text-sm font-extrabold <?= $totalTunggakan > 0 ? 'text-amber-600' : 'text-emerald-600'; ?>">
                <?= $totalTunggakan > 0 ? 'Ada Tagihan Aktif' : 'Bebas Tanggungan'; ?>
            </p>
        </div>
    </div>
</div>

<!-- Filter Tab & Search Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <?php foreach (['Semua', 'Belum Lunas', 'Lunas'] as $st): ?>
            <a href="keuangan.php?status=<?= urlencode($st); ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap <?= ($filterStatus === $st) ? 'bg-usd-700 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
                <?= $st; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="w-full sm:w-64">
        <input type="text" id="searchKeuangan" onkeyup="filterTable('searchKeuangan', 'tableKeuangan')" 
               placeholder="Cari transaksi atau jenis tagihan..." 
               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-usd-600">
    </div>
</div>

<!-- Table Keuangan -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table id="tableKeuangan" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider font-bold text-slate-500 border-b border-slate-200">
                    <th class="py-3 px-4">Kode Transaksi</th>
                    <th class="py-3 px-4">Jenis Pembayaran & Semester</th>
                    <th class="py-3 px-4">Nominal</th>
                    <th class="py-3 px-4">Virtual Account & Metode</th>
                    <th class="py-3 px-4">Jatuh Tempo</th>
                    <th class="py-3 px-4">Tanggal Bayar</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi (CRUD & Bayar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (empty($listKeuangan)): ?>
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fas fa-receipt text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Tidak ada data transaksi keuangan untuk status ini</p>
                            <button onclick="openAddModal()" class="mt-2 text-xs text-indigo-600 font-bold hover:underline">
                                + Buat tagihan baru
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listKeuangan as $k): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Kode Transaksi -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                <?= htmlspecialchars($k['kode_transaksi']); ?>
                            </td>

                            <!-- Jenis Pembayaran -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block"><?= htmlspecialchars($k['jenis_pembayaran']); ?></span>
                                <span class="text-[11px] text-indigo-600 font-semibold">Semester <?= $k['semester']; ?></span>
                                <?php if (!empty($k['keterangan'])): ?>
                                    <p class="text-[10px] text-slate-400 mt-0.5 italic"><?= htmlspecialchars($k['keterangan']); ?></p>
                                <?php endif; ?>
                            </td>

                            <!-- Nominal -->
                            <td class="py-3 px-4 font-mono font-extrabold text-slate-800">
                                <?= formatRupiah($k['nominal']); ?>
                            </td>

                            <!-- Virtual Account & Metode -->
                            <td class="py-3 px-4">
                                <span class="font-mono font-semibold text-slate-700 block"><?= htmlspecialchars($k['nomor_va'] ?: '-'); ?></span>
                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($k['metode_pembayaran'] ?: 'Virtual Account'); ?></span>
                            </td>

                            <!-- Jatuh Tempo -->
                            <td class="py-3 px-4 font-medium text-slate-600">
                                <?= formatTanggalIndo($k['tanggal_jatuh_tempo']); ?>
                            </td>

                            <!-- Tanggal Bayar -->
                            <td class="py-3 px-4 font-medium text-slate-600">
                                <?= $k['tanggal_bayar'] ? formatTanggalIndo(date('Y-m-d', strtotime($k['tanggal_bayar']))) : '<span class="text-rose-400 italic">Belum Dibayar</span>'; ?>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                <?php if ($k['status'] === 'Lunas'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check mr-1"></i> Lunas
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 animate-pulse">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Belum Lunas
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi CRUD & Bayar -->
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Jika Belum Lunas: Tombol Bayar Sekarang -->
                                    <?php if ($k['status'] !== 'Lunas'): ?>
                                        <button onclick='openPayModal(<?= json_encode($k); ?>)' 
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] flex items-center gap-1 shadow-sm transition-colors" 
                                                title="Bayar Tagihan">
                                            <i class="fas fa-credit-card"></i> Bayar
                                        </button>
                                    <?php else: ?>
                                        <!-- Cetak Bukti Bayar / Kuitansi -->
                                        <a href="cetak_keuangan.php?id=<?= $k['id']; ?>" target="_blank" 
                                           class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                           title="Cetak Kuitansi Resmi">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Edit Tagihan -->
                                    <button onclick='openEditModal(<?= json_encode($k); ?>)' 
                                            class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors flex items-center justify-center text-xs" 
                                            title="Edit Tagihan">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <!-- Delete Tagihan -->
                                    <button onclick="confirmDelete('actions/KeuanganAction.php?action=delete&id=<?= $k['id']; ?>', 'Hapus Data Tagihan?', 'Tagihan <?= addslashes($k['jenis_pembayaran']); ?> akan dihapus permanen!')" 
                                            class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                            title="Hapus Tagihan">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL TAMBAH TAGIHAN ================= -->
<div id="modalAddKeuangan" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-indigo-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-file-invoice"></i> Buat Tagihan Keuangan Baru
            </h3>
            <button onclick="closeAddModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/KeuanganAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pembayaran *</label>
                <input type="text" name="jenis_pembayaran" placeholder="Contoh: Uang Kuliah Tunggal (UKT) Semester 6" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
                    <input type="number" name="semester" value="<?= $currentUser['semester_aktif']; ?>" min="1" max="14" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp) *</label>
                    <input type="number" name="nominal" placeholder="Contoh: 5500000" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jatuh Tempo *</label>
                    <input type="date" name="tanggal_jatuh_tempo" value="<?= date('Y-m-d', strtotime('+30 days')); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Bayar</label>
                    <select name="metode_pembayaran" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="Virtual Account Mandiri">VA Mandiri</option>
                        <option value="Virtual Account BNI">VA BNI</option>
                        <option value="Virtual Account BCA">VA BCA</option>
                        <option value="QRIS Dinamis Kampus">QRIS Kampus</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan tagihan..." class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                    Buat Tagihan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BAYAR SEKARANG ================= -->
<div id="modalPayKeuangan" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-emerald-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-credit-card"></i> Konfirmasi Pembayaran Tagihan
            </h3>
            <button onclick="closePayModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/KeuanganAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" id="pay_id" name="id">

            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                <p class="text-xs text-slate-500">Jenis Tagihan:</p>
                <p id="pay_jenis" class="text-sm font-bold text-slate-800"></p>
                <p class="text-xs text-slate-500 mt-2">Total Tagihan:</p>
                <p id="pay_nominal" class="text-xl font-extrabold text-emerald-600"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Virtual Account Pembayaran</label>
                <input type="text" id="pay_va" readonly class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg font-mono font-bold text-slate-700">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Metode Transaksi</label>
                <select name="metode_pembayaran" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    <option value="Virtual Account Mandiri">Virtual Account Mandiri</option>
                    <option value="Virtual Account BNI">Virtual Account BNI</option>
                    <option value="Virtual Account BCA">Virtual Account BCA</option>
                    <option value="QRIS Dinamis Kampus">QRIS Realtime</option>
                </select>
            </div>

            <p class="text-[11px] text-slate-400 italic">
                *Klik tombol di bawah untuk menyimulasikan konfirmasi pembayaran instan (Status otomatis menjadi LUNAS).
            </p>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closePayModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700">
                    Konfirmasi Bayar Lunas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT TAGIHAN ================= -->
<div id="modalEditKeuangan" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-edit text-indigo-400"></i> Edit Data Tagihan Keuangan
            </h3>
            <button onclick="closeEditModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/KeuanganAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_keu_id" name="id">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pembayaran *</label>
                <input type="text" id="edit_jenis_pembayaran" name="jenis_pembayaran" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
                    <input type="number" id="edit_semester" name="semester" min="1" max="14" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp) *</label>
                    <input type="number" id="edit_nominal" name="nominal" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jatuh Tempo</label>
                    <input type="date" id="edit_jatuh_tempo" name="tanggal_jatuh_tempo" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Pembayaran</label>
                    <select id="edit_status" name="status" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-bold">
                        <option value="Belum Lunas">Belum Lunas</option>
                        <option value="Lunas">Lunas</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran</label>
                <input type="text" id="edit_metode" name="metode_pembayaran" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan</label>
                <textarea id="edit_keterangan" name="keterangan" rows="2" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalAddKeuangan').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('modalAddKeuangan').classList.add('hidden');
}

function openPayModal(data) {
    document.getElementById('pay_id').value = data.id;
    document.getElementById('pay_jenis').innerText = data.jenis_pembayaran;
    document.getElementById('pay_nominal').innerText = 'Rp ' + Number(data.nominal).toLocaleString('id-ID');
    document.getElementById('pay_va').value = data.nomor_va || '8872205120408801';

    document.getElementById('modalPayKeuangan').classList.remove('hidden');
}
function closePayModal() {
    document.getElementById('modalPayKeuangan').classList.add('hidden');
}

function openEditModal(data) {
    document.getElementById('edit_keu_id').value = data.id;
    document.getElementById('edit_jenis_pembayaran').value = data.jenis_pembayaran;
    document.getElementById('edit_semester').value = data.semester;
    document.getElementById('edit_nominal').value = data.nominal;
    document.getElementById('edit_jatuh_tempo').value = data.tanggal_jatuh_tempo;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_metode').value = data.metode_pembayaran || '';
    document.getElementById('edit_keterangan').value = data.keterangan || '';

    document.getElementById('modalEditKeuangan').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modalEditKeuangan').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
