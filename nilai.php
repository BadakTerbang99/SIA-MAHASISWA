<?php
$pageTitle = "Nilai Akademik";
$pageHeader = "Kartu Hasil Studi & Transkrip";
require_once __DIR__ . '/includes/header.php';

$mhsId = $currentUser['id'];

// Ambil daftar semester yang ada di database mahasiswa ini
$stmtSemesters = $pdo->prepare("SELECT DISTINCT semester FROM nilai_akademik WHERE mahasiswa_id = ? ORDER BY semester ASC");
$stmtSemesters->execute([$mhsId]);
$availableSemesters = $stmtSemesters->fetchAll(PDO::FETCH_COLUMN);

// Default semester = semester aktif atau yang terakhir
$currentViewSemester = $_GET['semester'] ?? (end($availableSemesters) ?: 1);

// Query Data Nilai
if ($currentViewSemester === 'transkrip') {
    $stmtNilai = $pdo->prepare("SELECT * FROM nilai_akademik WHERE mahasiswa_id = ? ORDER BY semester ASC, kode_mk ASC");
    $stmtNilai->execute([$mhsId]);
} else {
    $stmtNilai = $pdo->prepare("SELECT * FROM nilai_akademik WHERE mahasiswa_id = ? AND semester = ? ORDER BY kode_mk ASC");
    $stmtNilai->execute([$mhsId, (int)$currentViewSemester]);
}
$listNilai = $stmtNilai->fetchAll();

// Hitung Statistik Semester Terpilih
$semSks = 0;
$semMutu = 0;
$semLulusSks = 0;
foreach ($listNilai as $n) {
    $semSks += $n['sks'];
    $semMutu += ($n['sks'] * $n['bobot']);
    if ($n['status'] === 'Lulus') {
        $semLulusSks += $n['sks'];
    }
}
$ips = $semSks > 0 ? round($semMutu / $semSks, 2) : 0.00;

// Hitung IPK Keseluruhan (Semua Semester)
$stmtAll = $pdo->prepare("SELECT sks, bobot, status FROM nilai_akademik WHERE mahasiswa_id = ?");
$stmtAll->execute([$mhsId]);
$allRecords = $stmtAll->fetchAll();
$totalSksAll = 0;
$totalMutuAll = 0;
foreach ($allRecords as $r) {
    $totalSksAll += $r['sks'];
    $totalMutuAll += ($r['sks'] * $r['bobot']);
}
$ipkAll = $totalSksAll > 0 ? round($totalMutuAll / $totalSksAll, 2) : 0.00;
?>

<!-- Header Action & Semester Filter -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">
            <?= $currentViewSemester === 'transkrip' ? 'Transkrip Akademik Kumulatif' : 'Kartu Hasil Studi (KHS) Semester ' . htmlspecialchars($currentViewSemester); ?>
        </h2>
        <p class="text-xs text-slate-500">NIM: <?= htmlspecialchars($currentUser['nim']); ?> &bull; <?= htmlspecialchars($currentUser['nama']); ?></p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Cetak KHS / Transkrip -->
        <a href="cetak_khs.php?semester=<?= urlencode($currentViewSemester); ?>" target="_blank" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors">
            <i class="fas fa-print text-usd-700"></i> Cetak KHS
        </a>

        <!-- Simulasi / Input Nilai (CRUD) -->
        <button onclick="openAddModal()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-md shadow-usd-700/20 flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> Simulasi / Tambah Nilai MK
        </button>
    </div>
</div>

<!-- Semester Tab Navigation -->
<div class="flex items-center gap-2 overflow-x-auto pb-3 mb-6">
    <?php foreach ($availableSemesters as $sem): ?>
        <a href="nilai.php?semester=<?= $sem; ?>" 
           class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap <?= ((string)$currentViewSemester === (string)$sem) ? 'bg-usd-700 text-white shadow-sm shadow-usd-700/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
            Semester <?= $sem; ?>
        </a>
    <?php endforeach; ?>
    
    <a href="nilai.php?semester=transkrip" 
       class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap <?= ($currentViewSemester === 'transkrip') ? 'bg-usd-700 text-white shadow-sm shadow-usd-700/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
        <i class="fas fa-list-alt mr-1"></i> Transkrip Lengkap
    </a>
</div>

<!-- Performance Cards (IPS / IPK / Total SKS) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium"><?= $currentViewSemester === 'transkrip' ? 'Rata-rata IPS' : 'IPS Semester ' . $currentViewSemester; ?></p>
            <p class="text-xl font-extrabold text-slate-800"><?= number_format($ips, 2); ?></p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
            <i class="fas fa-star"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">IPK Kumulatif</p>
            <p class="text-xl font-extrabold text-amber-600"><?= number_format($ipkAll, 2); ?></p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
            <i class="fas fa-book-open"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">SKS Diambil</p>
            <p class="text-xl font-extrabold text-slate-800"><?= $semSks; ?> SKS</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fas fa-check-double"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">SKS Lulus</p>
            <p class="text-xl font-extrabold text-emerald-600"><?= $semLulusSks; ?> SKS</p>
        </div>
    </div>
</div>

<!-- Table Nilai Card -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
        <div class="flex items-center gap-2">
            <h3 class="font-bold text-slate-800 text-sm">Daftar Hasil Studi Mata Kuliah</h3>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-semibold"><?= count($listNilai); ?> Mata Kuliah</span>
        </div>
        <div class="w-full sm:w-64">
            <input type="text" id="searchNilai" onkeyup="filterTable('searchNilai', 'tableNilai')" 
                   placeholder="Cari mata kuliah atau kode..." 
                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="tableNilai" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider font-bold text-slate-500 border-b border-slate-200">
                    <th class="py-3 px-4 text-center">No</th>
                    <?php if ($currentViewSemester === 'transkrip'): ?>
                        <th class="py-3 px-4">Semester</th>
                    <?php endif; ?>
                    <th class="py-3 px-4">Kode MK</th>
                    <th class="py-3 px-4">Nama Mata Kuliah</th>
                    <th class="py-3 px-4 text-center">SKS (K)</th>
                    <th class="py-3 px-4 text-center">Nilai Angka</th>
                    <th class="py-3 px-4 text-center">Nilai Huruf (HM)</th>
                    <th class="py-3 px-4 text-center">Bobot (AM)</th>
                    <th class="py-3 px-4 text-center">Mutu (M = K &times; AM)</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi (CRUD)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (empty($listNilai)): ?>
                    <tr>
                        <td colspan="<?= $currentViewSemester === 'transkrip' ? 11 : 10; ?>" class="py-12 text-center text-slate-400">
                            <i class="fas fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada data nilai akademik untuk semester ini</p>
                            <button onclick="openAddModal()" class="mt-2 text-xs text-indigo-600 font-bold hover:underline">
                                + Input nilai sekarang
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($listNilai as $n): ?>
                        <?php $mutu = $n['sks'] * $n['bobot']; ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 text-center font-mono text-slate-400"><?= $no++; ?></td>
                            <?php if ($currentViewSemester === 'transkrip'): ?>
                                <td class="py-3 px-4 font-bold text-indigo-600">Sem <?= $n['semester']; ?></td>
                            <?php endif; ?>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700"><?= htmlspecialchars($n['kode_mk']); ?></td>
                            <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($n['nama_mk']); ?></td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700"><?= $n['sks']; ?></td>
                            <td class="py-3 px-4 text-center font-mono"><?= number_format($n['nilai_angka'], 1); ?></td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded font-extrabold text-xs <?= in_array($n['nilai_huruf'], ['A', 'A-']) ? 'bg-emerald-100 text-emerald-800' : (in_array($n['nilai_huruf'], ['B+', 'B']) ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'); ?>">
                                    <?= htmlspecialchars($n['nilai_huruf']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-700"><?= number_format($n['bobot'], 2); ?></td>
                            <td class="py-3 px-4 text-center font-mono font-extrabold text-indigo-700"><?= number_format($mutu, 2); ?></td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $n['status'] === 'Lulus' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'; ?>">
                                    <?= htmlspecialchars($n['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button onclick='openEditModal(<?= json_encode($n); ?>)' 
                                            class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                            title="Edit Nilai">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button onclick="confirmDelete('actions/NilaiAction.php?action=delete&id=<?= $n['id']; ?>&semester=<?= $currentViewSemester; ?>', 'Hapus Data Nilai?', 'Nilai <?= addslashes($n['nama_mk']); ?> akan dihapus!')" 
                                            class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                            title="Hapus Nilai">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-slate-100/80 font-bold text-xs text-slate-800 border-t border-slate-200">
                    <td colspan="<?= $currentViewSemester === 'transkrip' ? 4 : 3; ?>" class="py-3 px-4 text-right">TOTAL:</td>
                    <td class="py-3 px-4 text-center"><?= $semSks; ?> SKS</td>
                    <td colspan="3" class="py-3 px-4 text-right">TOTAL MUTU (M):</td>
                    <td class="py-3 px-4 text-center font-extrabold text-indigo-700"><?= number_format($semMutu, 2); ?></td>
                    <td colspan="2" class="py-3 px-4 text-center">
                        <span class="text-slate-500 font-normal">IPS:</span> <strong class="text-indigo-700"><?= number_format($ips, 2); ?></strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- ================= MODAL TAMBAH NILAI ================= -->
<div id="modalAddNilai" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-indigo-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Input Nilai Akademik Baru
            </h3>
            <button onclick="closeAddModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/NilaiAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
                    <select name="semester" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i; ?>" <?= ($currentViewSemester == $i) ? 'selected' : ''; ?>>Semester <?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode MK *</label>
                    <input type="text" name="kode_mk" placeholder="Contoh: IF509" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Kuliah *</label>
                <input type="text" name="nama_mk" placeholder="Contoh: Pemrosesan Bahasa Alami (NLP)" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bobot SKS *</label>
                    <input type="number" name="sks" min="1" max="6" value="3" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Huruf *</label>
                    <select name="nilai_huruf" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-bold text-indigo-700">
                        <option value="A">A (4.0)</option>
                        <option value="A-">A- (3.7)</option>
                        <option value="B+">B+ (3.5)</option>
                        <option value="B">B (3.0)</option>
                        <option value="B-">B- (2.7)</option>
                        <option value="C+">C+ (2.5)</option>
                        <option value="C">C (2.0)</option>
                        <option value="D">D (1.0)</option>
                        <option value="E">E (0.0)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Angka</label>
                    <input type="number" step="0.1" name="nilai_angka" placeholder="85.0" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT NILAI ================= -->
<div id="modalEditNilai" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-edit text-indigo-400"></i> Koreksi / Edit Nilai
            </h3>
            <button onclick="closeEditModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/NilaiAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_id" name="id">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
                    <select id="edit_semester" name="semester" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i; ?>">Semester <?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode MK *</label>
                    <input type="text" id="edit_kode_mk" name="kode_mk" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Kuliah *</label>
                <input type="text" id="edit_nama_mk" name="nama_mk" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bobot SKS *</label>
                    <input type="number" id="edit_sks" name="sks" min="1" max="6" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Huruf *</label>
                    <select id="edit_nilai_huruf" name="nilai_huruf" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-bold text-indigo-700">
                        <option value="A">A (4.0)</option>
                        <option value="A-">A- (3.7)</option>
                        <option value="B+">B+ (3.5)</option>
                        <option value="B">B (3.0)</option>
                        <option value="B-">B- (2.7)</option>
                        <option value="C+">C+ (2.5)</option>
                        <option value="C">C (2.0)</option>
                        <option value="D">D (1.0)</option>
                        <option value="E">E (0.0)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Angka</label>
                    <input type="number" step="0.1" id="edit_nilai_angka" name="nilai_angka" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
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
    document.getElementById('modalAddNilai').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('modalAddNilai').classList.add('hidden');
}

function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_semester').value = data.semester;
    document.getElementById('edit_kode_mk').value = data.kode_mk;
    document.getElementById('edit_nama_mk').value = data.nama_mk;
    document.getElementById('edit_sks').value = data.sks;
    document.getElementById('edit_nilai_huruf').value = data.nilai_huruf;
    document.getElementById('edit_nilai_angka').value = data.nilai_angka;

    document.getElementById('modalEditNilai').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modalEditNilai').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
