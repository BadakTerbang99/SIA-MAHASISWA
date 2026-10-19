<?php
$pageTitle = "Jadwal Kuliah";
$pageHeader = "Jadwal Perkuliahan Mahasiswa";
require_once __DIR__ . '/includes/header.php';

// Filter Hari
$filterHari = $_GET['hari'] ?? 'Semua';
$mhsId = $currentUser['id'];

if ($filterHari === 'Semua') {
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
} else {
    $stmt = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE mahasiswa_id = ? AND hari = ? ORDER BY jam_mulai ASC");
    $stmt->execute([$mhsId, $filterHari]);
}
$jadwalList = $stmt->fetchAll();

// Total SKS terdaftar di jadwal
$stmtTotal = $pdo->prepare("SELECT SUM(sks) as total_sks, COUNT(*) as total_mk FROM jadwal_kuliah WHERE mahasiswa_id = ?");
$stmtTotal->execute([$mhsId]);
$totalStats = $stmtTotal->fetch();
$totalSks = $totalStats['total_sks'] ?? 0;
$totalMk = $totalStats['total_mk'] ?? 0;

$hariTabs = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>

<!-- Header Action & Stat Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Jadwal Kuliah Semester <?= htmlspecialchars($currentUser['semester_aktif']); ?></h2>
        <p class="text-xs text-slate-500">Program Studi: <?= htmlspecialchars($currentUser['prodi']); ?> &bull; T.A. 2026/2027 Ganjil</p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Print Button -->
        <a href="cetak_jadwal.php" target="_blank" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors">
            <i class="fas fa-print text-usd-700"></i> Cetak Jadwal
        </a>

        <!-- Tambah Jadwal Button (Modal Trigger) -->
        <button onclick="openAddModal()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-md shadow-usd-700/20 flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> Program KRS / Tambah MK
        </button>
    </div>
</div>

<!-- Mini Stats & Search Bar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-usd-50 text-usd-700 flex items-center justify-center text-lg">
            <i class="fas fa-book"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Mata Kuliah Diambil</p>
            <p class="text-lg font-bold text-slate-800"><?= $totalMk; ?> MK</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Beban SKS</p>
            <p class="text-lg font-bold text-slate-800"><?= $totalSks; ?> SKS</p>
        </div>
    </div>

    <div class="md:col-span-2 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-sm flex items-center">
        <div class="relative w-full">
            <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
            <input type="text" id="searchJadwal" onkeyup="filterTable('searchJadwal', 'tableJadwal')" 
                   placeholder="Cari mata kuliah, nama dosen, atau kode ruangan..." 
                   class="w-full pl-8 pr-4 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-usd-600">
        </div>
    </div>
</div>

<!-- Filter Tab Hari -->
<div class="flex items-center gap-1.5 overflow-x-auto pb-3 mb-4">
    <?php foreach ($hariTabs as $h): ?>
        <a href="jadwal.php?hari=<?= urlencode($h); ?>" 
           class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap <?= ($filterHari === $h) ? 'bg-usd-700 text-white shadow-sm shadow-usd-700/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
            <?= $h; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Table View -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table id="tableJadwal" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider font-bold text-slate-500 border-b border-slate-200">
                    <th class="py-3.5 px-4">Hari & Waktu</th>
                    <th class="py-3.5 px-4">Mata Kuliah</th>
                    <th class="py-3.5 px-4">SKS</th>
                    <th class="py-3.5 px-4">Ruang & Kelas</th>
                    <th class="py-3.5 px-4">Dosen Pengampu</th>
                    <th class="py-3.5 px-4">Kelas Online</th>
                    <th class="py-3.5 px-4 text-center">Aksi (CRUD)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (empty($jadwalList)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="far fa-folder-open text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada jadwal kuliah untuk hari <?= htmlspecialchars($filterHari); ?></p>
                            <button onclick="openAddModal()" class="mt-2 text-xs text-indigo-600 font-bold hover:underline">
                                + Tambah jadwal sekarang
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jadwalList as $j): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Hari & Waktu -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block"><?= htmlspecialchars($j['hari']); ?></span>
                                <span class="font-mono text-slate-500 text-[11px]"><i class="far fa-clock mr-1 text-slate-400"></i><?= htmlspecialchars($j['jam_mulai']) . ' - ' . htmlspecialchars($j['jam_selesai']); ?></span>
                            </td>

                            <!-- Mata Kuliah -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 text-sm block"><?= htmlspecialchars($j['nama_mk']); ?></span>
                                <span class="text-[11px] font-mono text-indigo-600 font-semibold"><?= htmlspecialchars($j['kode_mk']); ?> &bull; Smt <?= $j['semester']; ?></span>
                            </td>

                            <!-- SKS -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg font-bold text-slate-700"><?= $j['sks']; ?> SKS</span>
                            </td>

                            <!-- Ruang & Kelas -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-700 block"><i class="fas fa-door-open text-rose-500 mr-1"></i><?= htmlspecialchars($j['ruangan']); ?></span>
                                <span class="text-[11px] text-slate-400">Kelas: <?= htmlspecialchars($j['kelas']); ?></span>
                            </td>

                            <!-- Dosen -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700 block"><?= htmlspecialchars($j['dosen']); ?></span>
                            </td>

                            <!-- Link Kelas Online -->
                            <td class="py-3.5 px-4">
                                <?php if (!empty($j['link_kelas'])): ?>
                                    <a href="<?= htmlspecialchars($j['link_kelas']); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-semibold text-[11px] transition-colors">
                                        <i class="fas fa-video text-emerald-600"></i> Join Room
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 text-[11px] italic">Tatap Muka</span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi CRUD -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button onclick='openEditModal(<?= json_encode($j); ?>)' 
                                            class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                            title="Edit Jadwal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button onclick="confirmDelete('actions/JadwalAction.php?action=delete&id=<?= $j['id']; ?>', 'Hapus Jadwal Kuliah?', 'Jadwal <?= addslashes($j['nama_mk']); ?> akan dihapus!')" 
                                            class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                            title="Hapus Jadwal">
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

<!-- ================= MODAL TAMBAH / CREATE JADWAL ================= -->
<div id="modalAddJadwal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-indigo-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-calendar-plus"></i> Tambah Jadwal Kuliah Baru
            </h3>
            <button onclick="closeAddModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/JadwalAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode MK *</label>
                    <input type="text" name="kode_mk" placeholder="Contoh: IF508" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bobot SKS *</label>
                    <input type="number" name="sks" min="1" max="6" value="3" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Kuliah *</label>
                <input type="text" name="nama_mk" placeholder="Contoh: Pemrograman Framework Lanjut" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Hari *</label>
                    <select name="hari" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" value="08:00" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" value="10:30" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ruangan / Lab *</label>
                    <input type="text" name="ruangan" placeholder="Contoh: Lab Jaringan 2" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                    <input type="text" name="kelas" value="IF-5A" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dosen Pengampu *</label>
                <input type="text" name="dosen" placeholder="Contoh: Prof. Dr. Ir. Budi Santoso, M.Kom." required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Link Kelas Virtual / E-Learning (Opsional)</label>
                <input type="url" name="link_kelas" placeholder="https://meet.google.com/xxx-xxxx-xxx" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT / UPDATE JADWAL ================= -->
<div id="modalEditJadwal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-edit text-indigo-400"></i> Edit Jadwal Kuliah
            </h3>
            <button onclick="closeEditModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/JadwalAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_id" name="id">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode MK *</label>
                    <input type="text" id="edit_kode_mk" name="kode_mk" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bobot SKS *</label>
                    <input type="number" id="edit_sks" name="sks" min="1" max="6" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Kuliah *</label>
                <input type="text" id="edit_nama_mk" name="nama_mk" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Hari *</label>
                    <select id="edit_hari" name="hari" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai *</label>
                    <input type="time" id="edit_jam_mulai" name="jam_mulai" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai *</label>
                    <input type="time" id="edit_jam_selesai" name="jam_selesai" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ruangan / Lab *</label>
                    <input type="text" id="edit_ruangan" name="ruangan" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas</label>
                    <input type="text" id="edit_kelas" name="kelas" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dosen Pengampu *</label>
                <input type="text" id="edit_dosen" name="dosen" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Link Kelas Virtual</label>
                <input type="url" id="edit_link_kelas" name="link_kelas" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                    Update Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalAddJadwal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('modalAddJadwal').classList.add('hidden');
}

function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_kode_mk').value = data.kode_mk;
    document.getElementById('edit_nama_mk').value = data.nama_mk;
    document.getElementById('edit_sks').value = data.sks;
    document.getElementById('edit_hari').value = data.hari;
    document.getElementById('edit_jam_mulai').value = data.jam_mulai;
    document.getElementById('edit_jam_selesai').value = data.jam_selesai;
    document.getElementById('edit_ruangan').value = data.ruangan;
    document.getElementById('edit_kelas').value = data.kelas;
    document.getElementById('edit_dosen').value = data.dosen;
    document.getElementById('edit_link_kelas').value = data.link_kelas || '';

    document.getElementById('modalEditJadwal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modalEditJadwal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
