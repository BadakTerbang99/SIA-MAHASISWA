<?php
require_once __DIR__ . '/config/database.php';

// Ambil daftar kampus aktif untuk demo switcher
$tenants = $pdo->query("SELECT * FROM tenants ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIA Cloud &bull; Platform Sistem Informasi Akademik SaaS Multi-Kampus</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-blue-600 text-white text-xs font-semibold py-2 px-4 text-center flex items-center justify-center gap-2">
        <span class="bg-white/20 px-2 py-0.5 rounded-full text-[10px] font-bold">SAAS UPDATE</span>
        <span>Mendukung Multi-Tenancy Kampus, SIA E-KTM Digital NFC, & Auto Billing VA Bank!</span>
        <a href="#pricing" class="underline ml-1 font-bold hover:text-indigo-200">Lihat Paket Langganan &rarr;</a>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 p-2 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <img src="assets/images/logo.svg" alt="SaaS Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-white flex items-center gap-2">
                        SIA-CLOUD <span class="text-[10px] bg-indigo-500/20 text-indigo-400 font-bold px-2 py-0.5 rounded border border-indigo-500/30">SaaS Platform</span>
                    </span>
                    <p class="text-[11px] text-slate-400">Academic ERP for Higher Education</p>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#fitur" class="hover:text-white transition-colors">Modul Sistem</a>
                <a href="#demo" class="hover:text-white transition-colors">Demo Kampus</a>
                <a href="#arsitektur" class="hover:text-white transition-colors">Arsitektur Multi-Tenancy</a>
                <a href="#pricing" class="hover:text-white transition-colors">Paket Harga</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="saas_admin.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold border border-slate-700 transition-all flex items-center gap-1.5">
                    <i class="fas fa-shield-alt text-amber-400"></i> Super Admin
                </a>
                <button onclick="openRegisterModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                    Daftar Kampus Baru
                </button>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-20 pb-28 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.25),rgba(255,255,255,0))]"></div>
        <div class="max-w-6xl mx-auto px-6 relative z-10 text-center">
            
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-xs font-semibold text-indigo-400 mb-6 backdrop-blur">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Software as a Service (SaaS) Akademik No. 1</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight sm:leading-tight">
                Platform Sistem Informasi Akademik Cloud untuk <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-blue-400 to-teal-300">Perguruan Tinggi Modern</span>
            </h1>

            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mt-6 leading-relaxed">
                Kelola seluruh siklus akademik mahasiswa, kartu rencana studi, jadwal kuliah, penilaian, SIA E-KTM digital dengan chip NFC, dan tagihan UKT terintegrasi secara otomatis dalam satu platform cloud multi-tenancy.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-4 mt-10">
                <button onclick="openRegisterModal()" class="px-7 py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm rounded-xl shadow-xl shadow-indigo-600/25 transition-all flex items-center gap-2">
                    <i class="fas fa-rocket"></i> Mulai Uji Coba Gratis 30 Hari
                </button>
                <a href="#demo" class="px-6 py-3.5 bg-slate-800/90 hover:bg-slate-800 text-slate-200 border border-slate-700 font-semibold text-sm rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-play text-indigo-400 text-xs"></i> Uji Demo Multi-Kampus
                </a>
            </div>

            <!-- Trust Badge -->
            <div class="mt-12 pt-8 border-t border-slate-800/60 flex flex-wrap items-center justify-center gap-8 text-xs text-slate-500 font-medium">
                <span><i class="fas fa-check text-emerald-400 mr-1.5"></i> 100% Cloud-Native</span>
                <span><i class="fas fa-check text-emerald-400 mr-1.5"></i> Terisolasi per Tenant (Multi-Tenant)</span>
                <span><i class="fas fa-check text-emerald-400 mr-1.5"></i> Siap Pakai Tanpa Instalasi Server</span>
                <span><i class="fas fa-check text-emerald-400 mr-1.5"></i> Standar Akreditasi BAN-PT</span>
            </div>
        </div>
    </section>

    <!-- LIVE CAMPUS DEMO SWITCHER -->
    <section id="demo" class="py-16 bg-slate-950 border-y border-slate-800">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-2">Simulasi Multi-Tenant</h2>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">Jelajahi Demo Kampus Mitra</h3>
                <p class="text-slate-400 text-xs sm:text-sm mt-2">
                    Klik salah satu perguruan tinggi mitra di bawah ini untuk langsung masuk ke Portal SIA khusus kampus tersebut dengan warna tema, data mahasiswa, jadwal, dan nilai yang terisolasi mandiri.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($tenants as $tn): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-3 h-3 rounded-full" style="background-color: <?= htmlspecialchars($tn['warna_primer']); ?>"></span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                    Paket <?= htmlspecialchars($tn['paket']); ?>
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors">
                                <?= htmlspecialchars($tn['nama_kampus']); ?>
                            </h4>
                            <p class="text-xs text-slate-400 font-mono mt-1">Tenant ID: #<?= $tn['id']; ?> (<?= $tn['slug']; ?>)</p>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2"><?= htmlspecialchars($tn['alamat']); ?></p>

                            <div class="mt-4 pt-4 border-t border-slate-800/80 text-xs text-slate-400 space-y-1">
                                <div class="flex justify-between">
                                    <span>Kuota Mahasiswa:</span>
                                    <span class="font-bold text-slate-200"><?= number_format($tn['kuota_mahasiswa']); ?> Mhs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Status Lisensi:</span>
                                    <span class="font-bold text-emerald-400"><?= $tn['status_langganan']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="actions/SaasAction.php?action=switch_tenant&tenant_id=<?= $tn['id']; ?>&redirect=../index.php" 
                               class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-center block transition-all shadow-md text-white" 
                               style="background-color: <?= htmlspecialchars($tn['warna_primer']); ?>;">
                                Masuk ke Portal <?= htmlspecialchars($tn['nama_singkat']); ?> &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CORE 6 MODULES SHOWCASE -->
    <section id="fitur" class="py-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-2">Modul Lengkap SIA</h2>
                <h3 class="text-3xl font-extrabold text-white">6 Fitur Utama dalam Satu Ekosistem SaaS</h3>
                <p class="text-slate-400 text-sm mt-2">Didesain khusus untuk memenuhi kebutuhan administrasi mahasiswa, biro akademik, dan tata usaha perguruan tinggi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 1. Home Dashboard -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xl mb-4 border border-indigo-500/20">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">1. SIA Home Dashboard</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Ringkasan IPK, progres kelulusan SKS (target 144 SKS), jadwal kuliah hari ini, grafik tren capaian IPS semester, dan pengumuman kampus.
                    </p>
                </div>

                <!-- 2. Jadwal Kuliah -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl mb-4 border border-blue-500/20">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">2. Jadwal Kuliah + CRUD</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Filter per hari (Senin-Sabtu), pencarian instan nama dosen/ruangan, tautan Google Meet/Zoom, cetak jadwal, dan operasi Create-Read-Update-Delete.
                    </p>
                </div>

                <!-- 3. Nilai Akademik -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mb-4 border border-amber-500/20">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">3. Nilai Akademik (KHS)</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kartu Hasil Studi per semester, transkrip kumulatif lengkap, perhitungan otomatis mutu dan bobot nilai, serta template cetak resmi berkop universitas.
                    </p>
                </div>

                <!-- 4. Biodata Mahasiswa -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl mb-4 border border-purple-500/20">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">4. Biodata & Foto Profil</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Data kependudukan (NIK), kontak darurat, informasi orang tua, kartu Dosen PA, dan fitur upload foto profil yang otomatis tersinkron ke E-KTM.
                    </p>
                </div>

                <!-- 5. SIA E-KTM Digital -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl mb-4 border border-emerald-500/20">
                        <i class="fas fa-address-card"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">5. SIA E-KTM Digital 3D</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kartu tanda mahasiswa dengan efek 3D flip depan/belakang, Smartcard Gold Chip, QR Code verifikasi realtime, Barcode NIM Code-128, dan ekspor ke gambar PNG.
                    </p>
                </div>

                <!-- 6. Kartu Keuangan -->
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl mb-4 border border-rose-500/20">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h4 class="text-base font-bold text-white mb-2">6. Kartu Keuangan & Billing</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Monitoring tagihan UKT & praktikum, nomor Virtual Account Bank (Mandiri, BNI, BCA, QRIS), simulasi pembayaran instan, dan cetak kuitansi kasir kampus.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING TIERS -->
    <section id="pricing" class="py-24 bg-slate-950 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-2">Investasi Berkelanjutan</h2>
                <h3 class="text-3xl font-extrabold text-white">Paket Langganan SaaS Perguruan Tinggi</h3>
                <p class="text-slate-400 text-sm mt-2">Pilih paket yang paling sesuai dengan skala jumlah mahasiswa kampus Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                
                <!-- 1. Starter -->
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Sekolah Tinggi / Akademi</span>
                        <h4 class="text-2xl font-bold text-white mt-1">Paket Starter</h4>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold text-white">Rp 1.500.000</span>
                            <span class="text-xs text-slate-400">/ bulan</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Cocok untuk institusi dengan kapasitas mahasiswa hingga 500 orang.</p>

                        <ul class="mt-6 space-y-3 text-xs text-slate-300">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Hingga 500 Mahasiswa Aktif</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Modul Jadwal & Nilai KHS</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> E-KTM Digital Standar</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Billing Tagihan Keuangan Manual</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Support Tiket Email</li>
                        </ul>
                    </div>

                    <button onclick="openRegisterModal('Starter')" class="mt-8 w-full py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold border border-slate-700 transition-all">
                        Pilih Starter Plan
                    </button>
                </div>

                <!-- 2. Pro (Featured) -->
                <div class="p-8 rounded-2xl bg-gradient-to-b from-indigo-950/60 to-slate-900 border-2 border-indigo-500 relative flex flex-col justify-between shadow-2xl shadow-indigo-500/10">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-indigo-600 text-white text-[10px] font-extrabold uppercase tracking-wider">
                        PALING POPULER
                    </div>

                    <div>
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Institut / Politeknik</span>
                        <h4 class="text-2xl font-bold text-white mt-1">Paket Pro</h4>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-white">Rp 3.500.000</span>
                            <span class="text-xs text-slate-400">/ bulan</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Solusi ideal untuk kampus berkembang hingga 2.500 mahasiswa.</p>

                        <ul class="mt-6 space-y-3 text-xs text-slate-200">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> <strong>Hingga 2.500 Mahasiswa</strong></li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> Seluruh 6 Modul Lengkap SIA</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> E-KTM 3D + Smartcard Chip & QR</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> Virtual Account Multi-Bank (Mandiri, BNI, BCA)</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> Custom Warna & Branding Logo Kampus</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-400"></i> Support WhatsApp Prioritas</li>
                        </ul>
                    </div>

                    <button onclick="openRegisterModal('Pro')" class="mt-8 w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-extrabold shadow-lg shadow-indigo-600/30 transition-all">
                        Pilih Pro Plan (Rekomendasi)
                    </button>
                </div>

                <!-- 3. Enterprise -->
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Universitas Besar</span>
                        <h4 class="text-2xl font-bold text-white mt-1">Paket Enterprise</h4>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold text-white">Rp 7.500.000</span>
                            <span class="text-xs text-slate-400">/ bulan</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Dukungan tanpa batas untuk universitas berskala nasional.</p>

                        <ul class="mt-6 space-y-3 text-xs text-slate-300">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> <strong>Mahasiswa Unlimited (10.000+)</strong></li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Custom Subdomain / Domain Mandiri</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> SLA Uptime 99.9% Dedicated Server</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Multi-Kampus Cabang & Fakultas Tanpa Batas</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-indigo-400"></i> Dedicated Account Manager 24/7</li>
                        </ul>
                    </div>

                    <button onclick="openRegisterModal('Enterprise')" class="mt-8 w-full py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold border border-slate-700 transition-all">
                        Pilih Enterprise Plan
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-950 py-12 border-t border-slate-800 text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="assets/images/logo.svg" alt="Logo" class="w-6 h-6">
                <span class="font-bold text-slate-300">SIA-Cloud SaaS Multi-Tenancy</span>
                <span>&bull;</span>
                <span>Hak Cipta &copy; <?= date('Y'); ?></span>
            </div>
            <div class="flex gap-4">
                <a href="saas_admin.php" class="hover:text-indigo-400">Super Admin Panel</a>
                <a href="index.php" class="hover:text-indigo-400">Portal SIA Mahasiswa</a>
            </div>
        </div>
    </footer>

    <!-- ================= MODAL REGISTER KAMPUS BARU ================= -->
    <div id="modalRegister" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-university"></i> Daftarkan Perguruan Tinggi Baru
                    </h3>
                    <p class="text-xs text-indigo-100 mt-0.5">Mulai Uji Coba Gratis 30 Hari Tanpa Komitmen</p>
                </div>
                <button onclick="closeRegisterModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <form action="actions/SaasAction.php" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" name="action" value="register_tenant">

                <div>
                    <label class="block font-bold text-slate-300 mb-1">Nama Resmi Perguruan Tinggi *</label>
                    <input type="text" name="nama_kampus" placeholder="Contoh: Universitas Bina Mandiri Nusantara" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Singkatan / Akronim *</label>
                        <input type="text" name="nama_singkat" placeholder="Contoh: UBMN" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Subdomain Slug</label>
                        <input type="text" name="slug" placeholder="ubmn" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Pilihan Paket Langganan</label>
                        <select id="reg_paket" name="paket" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-semibold">
                            <option value="Starter">Paket Starter (Rp 1.5jt/bln)</option>
                            <option value="Pro" selected>Paket Pro (Rp 3.5jt/bln)</option>
                            <option value="Enterprise">Paket Enterprise (Rp 7.5jt/bln)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Warna Tema Kampus</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="warna_primer" value="#4f46e5" class="h-8 w-12 bg-transparent rounded cursor-pointer">
                            <span class="text-slate-400 text-[11px]">Primer</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Email Resmi Pendaftar *</label>
                        <input type="email" name="email_kontak" placeholder="rektorat@ubmn.ac.id" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_telp" placeholder="081234567890" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1">Alamat Kampus</label>
                    <textarea name="alamat" rows="2" placeholder="Jl. Raya Kampus..." class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="closeRegisterModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-lg shadow-indigo-600/30">
                        Aktivasi Akun SaaS &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRegisterModal(plan = 'Pro') {
        const select = document.getElementById('reg_paket');
        if (select) select.value = plan;
        document.getElementById('modalRegister').classList.remove('hidden');
    }
    function closeRegisterModal() {
        document.getElementById('modalRegister').classList.add('hidden');
    }
    </script>

    <!-- Trigger SweetAlert Flash -->
    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $flash['type'] === 'error' ? 'error' : 'success'; ?>',
                title: '<?= addslashes($flash['message']); ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        });
    </script>
    <?php endif; ?>

</body>
</html>
