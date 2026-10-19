<?php
$pageTitle = "Biodata Mahasiswa";
$pageHeader = "Profil & Biodata Mahasiswa";
require_once __DIR__ . '/includes/header.php';

// Ambil list semua mahasiswa
$allMhs = $pdo->query("SELECT * FROM mahasiswa ORDER BY nama ASC")->fetchAll();
?>

<!-- Header Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Biodata Lengkap Mahasiswa</h2>
        <p class="text-xs text-slate-500">Kelola dan perbarui data diri, data akademik, serta kontak resmi kemahasiswaan</p>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="ektm.php" class="px-3.5 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition-all">
            <i class="fas fa-id-card"></i> Lihat E-KTM Digital
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Profile Card & Academic Info (1/3) -->
    <div class="space-y-6">
        
        <!-- Profile Picture Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm text-center">
            <div class="relative inline-block mx-auto mb-4">
                <img src="<?= (!empty($currentUser['foto']) && file_exists(__DIR__ . '/uploads/photos/' . $currentUser['foto'])) ? 'uploads/photos/' . htmlspecialchars($currentUser['foto']) : ((!empty($currentUser['foto']) && file_exists(__DIR__ . '/assets/images/' . $currentUser['foto'])) ? 'assets/images/' . htmlspecialchars($currentUser['foto']) : 'assets/images/shella.svg'); ?>" 
                     alt="Foto Mahasiswa" 
                     class="w-32 h-32 rounded-2xl object-cover ring-4 ring-usd-600/20 shadow-md mx-auto">
                <span class="absolute bottom-2 right-2 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full" title="Mahasiswa Aktif"></span>
            </div>

            <h3 class="text-base font-bold text-slate-800"><?= htmlspecialchars($currentUser['nama']); ?></h3>
            <p class="text-xs font-mono font-bold text-indigo-600 mt-0.5"><?= htmlspecialchars($currentUser['nim']); ?></p>
            
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap justify-center gap-2">
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold">
                    Status: <?= htmlspecialchars($currentUser['status_akademik']); ?>
                </span>
                <span class="px-2.5 py-1 bg-usd-50 text-usd-700 border border-usd-200 rounded-lg text-xs font-bold">
                    Semester <?= htmlspecialchars($currentUser['semester_aktif']); ?>
                </span>
                <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold font-mono">
                    Angkatan <?= htmlspecialchars($currentUser['angkatan'] ?? 2023); ?>
                </span>
            </div>

            <p class="text-xs text-slate-400 mt-3 italic">
                *Foto profil ditampilkan pada Kartu Tanda Mahasiswa (E-KTM).
            </p>
        </div>

        <!-- Dosen Pembimbing Akademik (DPA) Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fas fa-chalkboard-teacher text-usd-700"></i> Dosen Pembimbing Akademik (PA)
            </h4>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs font-bold text-slate-800"><?= htmlspecialchars($currentUser['dosen_pa'] ?: 'Ir. Rosalia Arum Kumalasanti M.T.'); ?></p>
                <p class="text-[11px] font-mono text-slate-500 mt-0.5">NIP. <?= htmlspecialchars($currentUser['nip_dosen_pa'] ?: '196803201994032001'); ?></p>
                <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex flex-col gap-1.5 text-[11px]">
                    <a href="mailto:arum@usd.ac.id" class="text-usd-700 hover:underline flex items-center gap-1.5 font-semibold">
                        <i class="far fa-envelope text-usd-700"></i>
                        <span>arum@usd.ac.id</span>
                    </a>
                    <a href="https://wa.me/6282134567890?text=Halo%20Ibu%20Ir.%20Rosalia%20Arum%20Kumalasanti%20M.T.,%20saya%20Shella%20Ardana%20(NIM%20235314182)%20ingin%20berkonsultasi%20akademik." target="_blank" class="text-emerald-600 hover:underline flex items-center gap-1.5 font-semibold">
                        <i class="fab fa-whatsapp text-emerald-600"></i>
                        <span>Konsultasi WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Kartu Layanan BAAK Kampus Paingan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fas fa-headset text-amber-500"></i> Bantuan Layanan Akademik
            </h4>
            <div class="space-y-2 text-xs text-slate-600">
                <p class="leading-relaxed text-[11px]">
                    Untuk pengurusan Kartu Rencana Studi (KRS), surat keterangan aktif kuliah, dan legalisir transkrip, hubungi kantor BAAK:
                </p>
                <div class="p-2.5 rounded-lg bg-amber-50/60 border border-amber-200/60 text-[11px] font-medium text-amber-900">
                    <p class="font-bold">Biro Administrasi Akademik (BAAK)</p>
                    <p class="text-slate-600 mt-0.5">Kampus III Paingan, Maguwoharjo, Sleman</p>
                    <p class="text-slate-600 mt-0.5">Email: <a href="mailto:akademik@usd.ac.id" class="text-usd-700 font-semibold underline">akademik@usd.ac.id</a></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column: Edit Biodata Form (2/3) -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fas fa-user-edit text-indigo-600"></i> Formulir Perubahan Biodata
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pastikan data yang diinput sesuai dengan dokumen kependudukan (KTP/KK)</p>
                </div>
            </div>

            <form action="actions/MahasiswaAction.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <input type="hidden" name="action" value="update_biodata">
                <input type="hidden" name="id" value="<?= $currentUser['id']; ?>">

                <!-- SECTION 1: DATA PRIBADI -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-usd-700 mb-3 flex items-center gap-1.5 pb-2 border-b border-usd-50">
                        <i class="fas fa-id-card"></i> 1. Informasi Data Pribadi
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Induk Mahasiswa (NIM)</label>
                            <input type="text" value="<?= htmlspecialchars($currentUser['nim']); ?>" readonly class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 font-mono font-bold cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIK (Nomor Induk Kependudukan) *</label>
                            <input type="text" name="nik" value="<?= htmlspecialchars($currentUser['nik']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Mahasiswa *</label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($currentUser['nama']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($currentUser['tempat_lahir']); ?>" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($currentUser['tanggal_lahir']); ?>" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="Laki-laki" <?= ($currentUser['jenis_kelamin'] === 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($currentUser['jenis_kelamin'] === 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                            <select name="agama" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="Islam" <?= ($currentUser['agama'] === 'Islam') ? 'selected' : ''; ?>>Islam</option>
                                <option value="Kristen Protestan" <?= ($currentUser['agama'] === 'Kristen Protestan') ? 'selected' : ''; ?>>Kristen Protestan</option>
                                <option value="Katolik" <?= ($currentUser['agama'] === 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                                <option value="Hindu" <?= ($currentUser['agama'] === 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                                <option value="Buddha" <?= ($currentUser['agama'] === 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                                <option value="Konghucu" <?= ($currentUser['agama'] === 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Darah</label>
                            <input type="text" name="golongan_darah" value="<?= htmlspecialchars($currentUser['golongan_darah']); ?>" placeholder="A / B / AB / O" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Profil Baru (Maks 2MB)</label>
                            <input type="file" name="foto" accept="image/*" class="w-full text-xs px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: DATA AKADEMIK & KONTAK -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-usd-700 mb-3 flex items-center gap-1.5 pb-2 border-b border-usd-50">
                        <i class="fas fa-graduation-cap"></i> 2. Akademik & Kontak
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Fakultas *</label>
                            <input type="text" name="fakultas" value="<?= htmlspecialchars($currentUser['fakultas']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Program Studi *</label>
                            <input type="text" name="prodi" value="<?= htmlspecialchars($currentUser['prodi']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Angkatan *</label>
                            <input type="number" name="angkatan" value="<?= htmlspecialchars($currentUser['angkatan'] ?? 2023); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700 font-mono font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Studi</label>
                            <input type="text" value="Strata 1 (S1)" readonly class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 font-bold cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi Mahasiswa *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP *</label>
                            <input type="text" name="no_hp" value="<?= htmlspecialchars($currentUser['no_hp']); ?>" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili Lengkap</label>
                            <textarea name="alamat" rows="2" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700"><?= htmlspecialchars($currentUser['alamat']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: DATA ORANG TUA / WALI -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-usd-700 mb-3 flex items-center gap-1.5 pb-2 border-b border-usd-100">
                        <i class="fas fa-users"></i> 3. Data Orang Tua / Wali
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Ayah</label>
                            <input type="text" name="nama_ayah" value="<?= htmlspecialchars($currentUser['nama_ayah']); ?>" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Ibu</label>
                            <input type="text" name="nama_ibu" value="<?= htmlspecialchars($currentUser['nama_ibu']); ?>" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. HP Orang Tua / Wali</label>
                            <input type="text" name="no_hp_ortu" value="<?= htmlspecialchars($currentUser['no_hp_ortu']); ?>" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-usd-700">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-md shadow-usd-700/20 flex items-center gap-2 transition-all">
                        <i class="fas fa-save"></i> Simpan Perubahan Biodata
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH MAHASISWA BARU ================= -->
<div id="modalAddStudent" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-emerald-600 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Tambah Mahasiswa Baru
            </h3>
            <button onclick="closeAddStudentModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
        </div>

        <form action="actions/MahasiswaAction.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create_mahasiswa">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">NIM Mahasiswa *</label>
                <input type="text" name="nim" placeholder="Contoh: 22051204099" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="nama" placeholder="Contoh: Bunga Citra Lestari" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Program Studi *</label>
                    <input type="text" name="prodi" value="S1 Teknik Informatika" required class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Semester Aktif</label>
                    <input type="number" name="semester_aktif" value="5" min="1" max="14" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" placeholder="bunga@student.ac.id" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="no_hp" placeholder="081299887766" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddStudentModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700">
                    Tambah & Aktifkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddStudentModal() {
    document.getElementById('modalAddStudent').classList.remove('hidden');
}
function closeAddStudentModal() {
    document.getElementById('modalAddStudent').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
