<?php
$pageTitle = "Beranda Mahasiswa";
$pageHeader = "SIA Mahasiswa &bull; Beranda";
require_once __DIR__ . '/includes/header.php';

// Hitung Statistik IPK & SKS dari database
$stmtAllGrades = $pdo->prepare("SELECT * FROM nilai_akademik WHERE mahasiswa_id = ? ORDER BY semester ASC");
$stmtAllGrades->execute([$currentUser['id']]);
$allGrades = $stmtAllGrades->fetchAll();

$totalSks = 0;
$totalBobotSks = 0;
$semesterStats = [];

foreach ($allGrades as $g) {
    $totalSks += $g['sks'];
    $totalBobotSks += ($g['sks'] * $g['bobot']);
    $sem = $g['semester'];
    if (!isset($semesterStats[$sem])) {
        $semesterStats[$sem] = ['sks' => 0, 'mutu' => 0];
    }
    $semesterStats[$sem]['sks'] += $g['sks'];
    $semesterStats[$sem]['mutu'] += ($g['sks'] * $g['bobot']);
}
$ipk = $totalSks > 0 ? round($totalBobotSks / $totalSks, 2) : 0.00;

// Data IPS untuk grafik Chart.js
$ipsLabels = [];
$ipsValues = [];
ksort($semesterStats);
foreach ($semesterStats as $sem => $st) {
    $ipsLabels[] = "Semester $sem";
    $ipsValues[] = round($st['mutu'] / $st['sks'], 2);
}

// Jadwal Kuliah Hari Ini & Seluruh Jadwal Semester 7
$hariIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$currentDayName = $hariIndo[date('l')];

// Jadwal Hari Ini
$stmtToday = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE mahasiswa_id = ? AND hari = ? ORDER BY jam_mulai ASC");
$stmtToday->execute([$currentUser['id'], $currentDayName]);
$jadwalHariIni = $stmtToday->fetchAll();

// Semua Jadwal Semester 7 (18 SKS)
$stmtAllJadwal = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE mahasiswa_id = ? ORDER BY 
    CASE hari 
        WHEN 'Senin' THEN 1 
        WHEN 'Selasa' THEN 2 
        WHEN 'Rabu' THEN 3 
        WHEN 'Kamis' THEN 4 
        WHEN 'Jumat' THEN 5 
        WHEN 'Sabtu' THEN 6 
        ELSE 7 
    END, jam_mulai ASC");
$stmtAllJadwal->execute([$currentUser['id']]);
$allJadwalSemester = $stmtAllJadwal->fetchAll();

// Total SKS Aktif Semester ini dari Jadwal
$stmtSksCurrent = $pdo->prepare("SELECT SUM(sks) as total_sks_now, COUNT(*) as total_mk FROM jadwal_kuliah WHERE mahasiswa_id = ? AND semester = ?");
$stmtSksCurrent->execute([$currentUser['id'], $currentUser['semester_aktif']]);
$statNow = $stmtSksCurrent->fetch();
$sksSemesterNow = $statNow['total_sks_now'] ?? 18;
$totalMkNow = $statNow['total_mk'] ?? 6;

// Keuangan Mahasiswa
$stmtKeu = $pdo->prepare("SELECT 
    COUNT(*) as total_tagihan,
    SUM(CASE WHEN status = 'Belum Lunas' THEN nominal ELSE 0 END) as belum_lunas,
    SUM(CASE WHEN status = 'Lunas' THEN nominal ELSE 0 END) as sudah_lunas
    FROM keuangan_mahasiswa WHERE mahasiswa_id = ?");
$stmtKeu->execute([$currentUser['id']]);
$keuanganStat = $stmtKeu->fetch();

// Pengumuman Kampus
$pengumumanList = $pdo->query("SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 3")->fetchAll();

// Path Foto Mahasiswa
$fotoPath = (!empty($currentUser['foto']) && file_exists(__DIR__ . '/uploads/photos/' . $currentUser['foto'])) 
    ? 'uploads/photos/' . htmlspecialchars($currentUser['foto']) 
    : ((!empty($currentUser['foto']) && file_exists(__DIR__ . '/assets/images/' . $currentUser['foto'])) 
        ? 'assets/images/' . htmlspecialchars($currentUser['foto']) 
        : 'assets/images/shella.svg');
?>

<!-- 1. STUDENT PROFILE STRIP (Clean, Crisp, Human-Crafted) -->
<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 sm:p-6 mb-7">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
        
        <!-- Left: Student Photo & Meta -->
        <div class="flex items-center gap-4 sm:gap-5">
            <div class="relative shrink-0">
                <img src="<?= $fotoPath; ?>" 
                     alt="Foto <?= htmlspecialchars($currentUser['nama']); ?>" 
                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover ring-2 ring-slate-100 shadow-sm bg-slate-50">
                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full" title="Mahasiswa Aktif"></span>
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-usd-50 text-usd-700 border border-usd-200">
                        Semester <?= htmlspecialchars($currentUser['semester_aktif']); ?> Aktif
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Bebas Tanggungan UKT
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                    <?= htmlspecialchars($currentUser['nama']); ?>
                </h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">
                    NIM: <strong class="text-slate-800"><?= htmlspecialchars($currentUser['nim']); ?></strong> &bull; <?= htmlspecialchars($currentUser['prodi']); ?> (S1) &bull; <?= htmlspecialchars($currentUser['fakultas']); ?>
                </p>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex flex-wrap items-center gap-2.5 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
            <a href="ektm.php" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fas fa-id-card"></i>
                <span>E-KTM Digital</span>
            </a>
            <a href="cetak_khs.php?semester=transkrip" target="_blank" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                <i class="fas fa-print text-slate-500"></i>
                <span>Cetak Transkrip</span>
            </a>
        </div>

    </div>
</div>

<!-- 2. FITUR AKSES CEPAT (Quick Access Launcher - Clean, Functional Grid) -->
<div class="mb-7">
    <div class="flex items-center justify-between mb-3.5">
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-th-large text-usd-700"></i>
                <span>Fitur Akses Cepat Mahasiswa</span>
            </h3>
        </div>
        <span class="text-xs text-slate-500 font-medium">Pintasan Layanan Akademik</span>
    </div>

    <!-- 6 Primary Launcher Tiles -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-3.5">
        
        <!-- Tile 1: Jadwal Kuliah -->
        <a href="jadwal.php" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-usd-700 flex items-center justify-center text-base group-hover:bg-usd-700 group-hover:text-white transition-colors">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono">
                    18 SKS
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">Jadwal Kuliah</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">6 Mata Kuliah</p>
            </div>
        </a>

        <!-- Tile 2: Nilai KHS & Transkrip -->
        <a href="nilai.php" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-amber-600 flex items-center justify-center text-base group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                    3,33
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">Nilai & KHS</h4>
                <p class="text-[11px] text-slate-400 mt-0.5"><?= $totalSks; ?> SKS Lulus</p>
            </div>
        </a>

        <!-- Tile 3: SIA E-KTM Digital -->
        <a href="ektm.php" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-emerald-600 flex items-center justify-center text-base group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <i class="fas fa-id-card"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    NFC
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">E-KTM Digital</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Kartu 3D & Unduh</p>
            </div>
        </a>

        <!-- Tile 4: Kartu Keuangan -->
        <a href="keuangan.php" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-teal-600 flex items-center justify-center text-base group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <i class="fas fa-receipt"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Lunas
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">Administrasi UKT</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Tagihan Rp 0</p>
            </div>
        </a>

        <!-- Tile 5: Biodata Mahasiswa -->
        <a href="biodata.php" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-sky-600 flex items-center justify-center text-base group-hover:bg-sky-600 group-hover:text-white transition-colors">
                    <i class="fas fa-user-circle"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                    Profil
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">Biodata Diri</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Data & Upload Foto</p>
            </div>
        </a>

        <!-- Tile 6: Cetak Dokumen -->
        <a href="cetak_jadwal.php" target="_blank" class="hub-tile group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-purple-600 flex items-center justify-center text-base group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <i class="fas fa-file-alt"></i>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">
                    Cetak
                </span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-usd-700 transition-colors">Cetak Jadwal</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Lembar Resmi USD</p>
            </div>
        </a>

    </div>
</div>

<!-- 3. KEY ACADEMIC METRICS (Clean Data Density) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    
    <!-- 1. IPK Kumulatif -->
    <div class="card-clean p-4 sm:p-5">
        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
            <span>IPK Kumulatif</span>
            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-xs">
                <i class="fas fa-star"></i>
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-slate-900 font-mono"><?= number_format($ipk, 2); ?></span>
            <span class="text-xs text-slate-400 font-medium">/ 4.00</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">
            Status: <strong class="text-amber-800">Sangat Memuaskan</strong> &bull; Mutu: <?= number_format($totalBobotSks, 1); ?>
        </p>
    </div>

    <!-- 2. SKS Tempuh Lulus -->
    <div class="card-clean p-4 sm:p-5">
        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
            <span>Total SKS Lulus</span>
            <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs">
                <i class="fas fa-graduation-cap"></i>
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-slate-900 font-mono"><?= $totalSks; ?></span>
            <span class="text-xs text-slate-400 font-medium">/ 144 SKS (Target S1)</span>
        </div>
        <div class="mt-2.5 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <?php $persenSks = min(100, round(($totalSks / 144) * 100)); ?>
            <div class="bg-usd-700 h-1.5 rounded-full" style="width: <?= $persenSks; ?>%"></div>
        </div>
        <p class="text-[11px] text-slate-400 mt-1.5 flex justify-between">
            <span>Semester 1 s/d 6</span>
            <span><?= $persenSks; ?>% kelulusan</span>
        </p>
    </div>

    <!-- 3. SKS Semester Ini -->
    <div class="card-clean p-4 sm:p-5">
        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
            <span>Beban Semester 7</span>
            <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-xs">
                <i class="fas fa-book-open"></i>
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-extrabold text-slate-900 font-mono"><?= $sksSemesterNow; ?></span>
            <span class="text-xs text-slate-400 font-medium">SKS Terprogram</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">
            <i class="fas fa-check-circle text-emerald-600 mr-1"></i> <?= $totalMkNow; ?> Mata Kuliah Terverifikasi KRS
        </p>
    </div>

    <!-- 4. Keuangan / Tagihan -->
    <div class="card-clean p-4 sm:p-5">
        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
            <span>Status UKT Kuliah</span>
            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs">
                <i class="fas fa-shield-alt"></i>
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-extrabold text-emerald-700">100% LUNAS</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">
            Total Bayar: <strong class="text-slate-800">Rp 80.671.000</strong> &bull; Tunggakan: Rp 0
        </p>
    </div>

</div>

<!-- 4. MAIN CONTENT AREA: JADWAL & AKADEMIK -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-7 mb-7">
    
    <!-- Left Column: Jadwal Kuliah Semester 7 (7/12) -->
    <div class="lg:col-span-7 space-y-5">
        
        <div class="card-clean p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fas fa-calendar-day text-usd-700"></i>
                        <span>Jadwal Perkuliahan Semester 7</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Hari ini: <strong><?= $currentDayName; ?>, <?= formatTanggalIndo(date('Y-m-d')); ?></strong></p>
                </div>
                <a href="jadwal.php" class="text-xs text-usd-700 font-bold hover:underline">
                    Kelola KRS & Jadwal &rarr;
                </a>
            </div>

            <!-- List Jadwal Semester 7 -->
            <div class="divide-y divide-slate-100 mt-2">
                <?php foreach ($allJadwalSemester as $j): ?>
                    <?php $isToday = ($j['hari'] === $currentDayName); ?>
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 <?= $isToday ? 'bg-amber-50/50 -mx-4 px-4 rounded-xl border border-amber-200/60' : ''; ?>">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $isToday ? 'bg-usd-700 text-white' : 'bg-slate-100 text-slate-700'; ?>">
                                    <?= htmlspecialchars($j['hari']); ?>
                                </span>
                                <span class="font-mono text-xs font-semibold text-slate-600">
                                    <?= htmlspecialchars($j['jam_mulai']) . ' - ' . htmlspecialchars($j['jam_selesai']); ?>
                                </span>
                                <span class="text-[11px] text-slate-400">&bull;</span>
                                <span class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($j['kode_mk']); ?></span>
                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">
                                    <?= $j['sks']; ?> SKS
                                </span>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900">
                                <?= htmlspecialchars($j['nama_mk']); ?> 
                                <span class="text-slate-400 font-normal">(Kelas <?= htmlspecialchars($j['kelas']); ?>)</span>
                            </h4>
                            <p class="text-xs text-slate-500">
                                <i class="fas fa-map-marker-alt text-slate-400 mr-1"></i><?= htmlspecialchars($j['ruangan']); ?> &bull; 
                                <span class="text-slate-600 font-medium"><?= htmlspecialchars($j['dosen']); ?></span>
                            </p>
                        </div>

                        <?php if (!empty($j['link_kelas'])): ?>
                            <a href="<?= htmlspecialchars($j['link_kelas']); ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:text-usd-700 text-xs font-semibold shrink-0 flex items-center justify-center gap-1.5 shadow-sm transition-all">
                                <i class="fas fa-video text-slate-400 text-xs"></i>
                                <span>Link Kelas</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Right Column: Grafik IPS, DPA & Pengumuman (5/12) -->
    <div class="lg:col-span-5 space-y-5">
        
        <!-- Grafik Tren IPS -->
        <div class="card-clean p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-chart-line text-usd-700"></i>
                    <span>Tren Indeks Prestasi Semester</span>
                </h3>
                <span class="text-xs font-mono font-bold text-usd-700">IPK: <?= number_format($ipk, 2); ?></span>
            </div>
            <div class="relative h-48 w-full">
                <canvas id="ipsChart"></canvas>
            </div>
        </div>

        <!-- Dosen Pembimbing Akademik Widget -->
        <div class="card-clean p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-chalkboard-teacher text-usd-700"></i>
                    <span>Dosen Pembimbing Akademik</span>
                </h3>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aktif
                </span>
            </div>

            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-usd-50 text-usd-700 border border-usd-200 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                        Ir. Rosalia Arum Kumalasanti M.T.
                    </h4>
                    <p class="text-[11px] font-mono text-slate-500">NIP. 196803201994032001</p>
                    <p class="text-[11px] text-slate-600 truncate mt-0.5">Program Studi Informatika &bull; FST USD</p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                <a href="mailto:arum@usd.ac.id" class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold flex items-center justify-center gap-1.5 transition-colors">
                    <i class="far fa-envelope text-slate-400"></i>
                    <span>Kirim Email</span>
                </a>
                <a href="https://wa.me/6282134567890?text=Halo%20Ibu%20Ir.%20Rosalia%20Arum%20Kumalasanti%20M.T.,%20saya%20Shella%20Ardana%20(NIM%20235314182)%20ingin%20berkonsultasi." target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-semibold flex items-center justify-center gap-1.5 transition-colors">
                    <i class="fab fa-whatsapp text-emerald-600"></i>
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>

        <!-- Pengumuman Akademik Terbaru -->
        <div class="card-clean p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-bullhorn text-amber-600"></i>
                    <span>Pengumuman Akademik</span>
                </h3>
                <span class="text-[11px] text-slate-400">USD Feed</span>
            </div>

            <div class="space-y-3">
                <?php foreach ($pengumumanList as $p): ?>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center justify-between text-[10px] mb-1">
                            <span class="font-bold uppercase tracking-wider text-usd-700"><?= htmlspecialchars($p['kategori']); ?></span>
                            <span class="text-slate-400 font-mono"><?= date('d/m/Y', strtotime($p['tanggal'])); ?></span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900"><?= htmlspecialchars($p['judul']); ?></h4>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed"><?= htmlspecialchars($p['isi']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<!-- Inisialisasi Chart.js Grafik IPS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('ipsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($ipsLabels); ?>,
            datasets: [{
                label: 'Indeks Prestasi Semester',
                data: <?= json_encode($ipsValues); ?>,
                borderColor: '#7a0c0c',
                backgroundColor: 'rgba(122, 12, 12, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#7a0c0c',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4.5,
                pointHoverRadius: 6.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 2.75,
                    max: 4.0,
                    ticks: {
                        stepSize: 0.25,
                        font: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                        color: '#64748b'
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                        color: '#64748b'
                    },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' IPS: ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
