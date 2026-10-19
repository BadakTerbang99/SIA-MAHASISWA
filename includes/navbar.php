<?php
// Top Navigation Bar - Khusus Portal SIA Mahasiswa
?>

<!-- Top Navigation Bar -->
<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 md:px-6 bg-white border-b border-slate-200 shadow-sm shrink-0">
    <!-- Left: Hamburger button & Breadcrumb -->
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="p-2 text-slate-500 rounded-lg lg:hidden hover:bg-slate-100 focus:outline-none">
            <i class="fas fa-bars text-lg"></i>
        </button>
        
        <div>
            <nav class="flex text-xs font-medium text-slate-500 gap-1.5 items-center">
                <a href="index.php" class="hover:text-usd-700 transition-colors">Portal SIA</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Beranda'; ?></span>
            </nav>
            <h1 class="text-base md:text-lg font-bold text-slate-800 leading-tight hidden sm:block truncate max-w-xs md:max-w-md">
                <?= isset($pageHeader) ? htmlspecialchars($pageHeader) : 'Sistem Informasi Akademik'; ?>
            </h1>
        </div>
    </div>

    <!-- Center/Right: University Info, Notifications & Student Profile -->
    <div class="flex items-center gap-2 md:gap-3">
        
        <!-- Campus Badge -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-usd-50 border border-usd-100 text-xs text-usd-800 font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Universitas Sanata Dharma</span>
            <span class="text-[10px] bg-usd-700 text-amber-300 font-extrabold px-1.5 py-0.5 rounded">Sem 7</span>
        </div>

        <!-- Quick Notifications Icon -->
        <div class="relative group">
            <button class="p-2 text-slate-500 hover:text-usd-700 rounded-lg hover:bg-slate-100 transition-colors relative" title="Notifikasi Akademik">
                <i class="far fa-bell text-base"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full"></span>
            </button>
            <div class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 p-3 hidden group-hover:block z-50 text-xs animate-in fade-in duration-200">
                <div class="font-bold text-slate-800 border-b border-slate-100 pb-2 mb-2 flex justify-between items-center">
                    <span>Notifikasi Akademik</span>
                    <span class="text-[10px] text-usd-700 font-semibold">Terkini</span>
                </div>
                <div class="space-y-2">
                    <div class="p-2 bg-emerald-50 rounded-xl border border-emerald-100">
                        <p class="font-bold text-emerald-900 text-[11px]"><i class="fas fa-check-circle text-emerald-600 mr-1"></i> UKT Semester 7 Lunas</p>
                        <p class="text-emerald-700 text-[10px] mt-0.5">Seluruh tagihan kuliah telah dibayar tuntas. Status: Bebas Tanggungan.</p>
                    </div>
                    <div class="p-2 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="font-bold text-amber-900 text-[11px]"><i class="fas fa-calendar-check text-amber-600 mr-1"></i> Jadwal Kuliah 18 SKS Aktif</p>
                        <p class="text-amber-700 text-[10px] mt-0.5">6 mata kuliah semester 7 telah diverifikasi oleh DPA Ir. Rosalia Arum Kumalasanti M.T.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Student Profile Pill -->
        <a href="biodata.php" class="flex items-center gap-2 pl-2 border-l border-slate-200 group">
            <img src="<?= (isset($currentUser['foto']) && file_exists(__DIR__ . '/../uploads/photos/' . $currentUser['foto'])) ? 'uploads/photos/' . htmlspecialchars($currentUser['foto']) : ((isset($currentUser['foto']) && file_exists(__DIR__ . '/../assets/images/' . $currentUser['foto'])) ? 'assets/images/' . htmlspecialchars($currentUser['foto']) : 'assets/images/shella.svg'); ?>" 
                 alt="Avatar Shella" 
                 class="w-8 h-8 rounded-full object-cover ring-2 ring-usd-600/30 group-hover:ring-usd-600 transition-all">
            <div class="hidden xl:block text-left">
                <p class="text-xs font-bold text-slate-800 group-hover:text-usd-700 transition-colors leading-tight">
                    <?= htmlspecialchars(explode(' ', $currentUser['nama'])[0]); ?>
                </p>
                <p class="text-[10px] text-slate-400 font-mono leading-tight">
                    <?= htmlspecialchars($currentUser['nim']); ?>
                </p>
            </div>
        </a>

        <!-- Logout Button -->
        <a href="actions/AuthAction.php?action=logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun SIA Mahasiswa?');" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors ml-1" title="Keluar / Logout Akun">
            <i class="fas fa-sign-out-alt text-base"></i>
        </a>
    </div>
</header>
