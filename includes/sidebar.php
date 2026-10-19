<?php
// Sidebar Component - Khusus Portal SIA Mahasiswa
?>

<!-- Backdrop Mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden hidden transition-opacity"></div>

<!-- Sidebar Component -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-slate-900 text-slate-300 transition-transform duration-300 transform -translate-x-full lg:static lg:translate-x-0 shrink-0 shadow-2xl">
    
    <!-- Logo & Branding Kampus Sanata Dharma -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800 bg-slate-950/40">
        <div class="w-10 h-10 rounded-xl bg-white p-1 border border-white/20 shadow-md flex items-center justify-center shrink-0">
            <img src="assets/images/usd_logo.png" alt="Logo USD" class="w-full h-full object-contain">
        </div>
        <div class="overflow-hidden">
            <h1 class="text-sm font-extrabold tracking-tight text-white flex items-center gap-1.5 truncate">
                SIA MAHASISWA
            </h1>
            <p class="text-[11px] text-amber-400 font-semibold truncate" title="Universitas Sanata Dharma">
                Universitas Sanata Dharma
            </p>
        </div>
    </div>

    <!-- Mini Student Profile Widget -->
    <div class="px-5 py-4 mx-3 my-4 bg-slate-800/70 rounded-2xl border border-slate-700/60 shadow-inner">
        <div class="flex items-center gap-3">
            <div class="relative shrink-0">
                <img src="<?= (isset($currentUser['foto']) && file_exists(__DIR__ . '/../uploads/photos/' . $currentUser['foto'])) ? 'uploads/photos/' . htmlspecialchars($currentUser['foto']) : ((isset($currentUser['foto']) && file_exists(__DIR__ . '/../assets/images/' . $currentUser['foto'])) ? 'assets/images/' . htmlspecialchars($currentUser['foto']) : 'assets/images/shella.svg'); ?>" 
                     alt="Foto Mahasiswa" 
                     class="w-11 h-11 rounded-full object-cover ring-2 ring-usd-600/60 shadow">
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full" title="Mahasiswa Aktif"></span>
            </div>
            <div class="overflow-hidden">
                <h2 class="text-xs font-bold text-white truncate" title="<?= htmlspecialchars($currentUser['nama']); ?>">
                    <?= htmlspecialchars($currentUser['nama']); ?>
                </h2>
                <p class="text-[11px] font-mono text-amber-300 font-semibold"><?= htmlspecialchars($currentUser['nim']); ?></p>
                <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($currentUser['prodi']); ?> &bull; Sem <?= htmlspecialchars($currentUser['semester_aktif']); ?></p>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-700/60 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Status Akademik:</span>
            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[10px] border border-emerald-500/30">
                Aktif
            </span>
        </div>
    </div>

    <!-- Navigation Menu Links Khusus Mahasiswa -->
    <nav class="flex-1 px-3 space-y-1.5 overflow-y-auto">
        <div class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
            Menu Mahasiswa
        </div>

        <!-- 1. Home Dashboard -->
        <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'index' || $currentPage === '') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-chart-pie w-5 text-center text-sm <?= ($currentPage === 'index' || $currentPage === '') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>SIA Home Dashboard</span>
        </a>

        <!-- 2. Jadwal Kuliah -->
        <a href="jadwal.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'jadwal') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-calendar-alt w-5 text-center text-sm <?= ($currentPage === 'jadwal') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>Jadwal Kuliah</span>
            <span class="ml-auto text-[10px] bg-usd-900/80 px-2 py-0.5 rounded-full text-amber-300 border border-usd-600/40">18 SKS</span>
        </a>

        <!-- 3. Nilai Akademik -->
        <a href="nilai.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'nilai') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-graduation-cap w-5 text-center text-sm <?= ($currentPage === 'nilai') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>Nilai Akademik (KHS)</span>
            <span class="ml-auto text-[10px] bg-slate-800 px-2 py-0.5 rounded-full text-slate-300 font-mono">IPK 3.33</span>
        </a>

        <!-- 4. Biodata -->
        <a href="biodata.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'biodata') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-id-badge w-5 text-center text-sm <?= ($currentPage === 'biodata') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>Biodata Mahasiswa</span>
        </a>

        <!-- 5. SIA E-KTM -->
        <a href="ektm.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'ektm') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-address-card w-5 text-center text-sm <?= ($currentPage === 'ektm') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>SIA E-KTM Digital</span>
            <span class="ml-auto text-[10px] bg-emerald-500/20 text-emerald-300 font-semibold px-2 py-0.5 rounded-full border border-emerald-500/30">NFC</span>
        </a>

        <!-- 6. Kartu Keuangan -->
        <a href="keuangan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= ($currentPage === 'keuangan') ? 'bg-usd-700 text-white shadow-lg shadow-usd-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fas fa-receipt w-5 text-center text-sm <?= ($currentPage === 'keuangan') ? 'text-amber-300' : 'text-slate-400'; ?>"></i>
            <span>Kartu Keuangan</span>
            <span class="ml-auto text-[10px] bg-emerald-500/20 text-emerald-300 font-semibold px-2 py-0.5 rounded-full border border-emerald-500/30">Lunas</span>
        </a>

        <!-- Logout Button -->
        <div class="pt-3 border-t border-slate-800 mt-3">
            <a href="actions/AuthAction.php?action=logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun SIA Mahasiswa?');" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all text-rose-400 hover:bg-rose-950/40">
                <i class="fas fa-sign-out-alt w-5 text-center text-sm text-rose-500"></i>
                <span>Keluar (Logout)</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar Bottom Status Khusus Mahasiswa -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/70 text-xs">
        <div class="flex items-center justify-between text-slate-400">
            <span>Dosen Pembimbing:</span>
        </div>
        <p class="font-bold text-amber-300 truncate mt-0.5" title="<?= htmlspecialchars($currentUser['dosen_pa'] ?? 'Ir. Rosalia Arum Kumalasanti M.T.'); ?>">
            <?= htmlspecialchars($currentUser['dosen_pa'] ?? 'Ir. Rosalia Arum Kumalasanti M.T.'); ?>
        </p>
        <div class="flex items-center justify-between text-slate-400 mt-2 pt-2 border-t border-slate-800/80 text-[11px]">
            <span>Semester Aktif:</span>
            <span class="px-1.5 py-0.5 bg-usd-900 text-amber-300 rounded font-bold border border-usd-700">Semester 7</span>
        </div>
    </div>
</aside>
