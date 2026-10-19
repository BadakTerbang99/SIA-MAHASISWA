<?php
require_once __DIR__ . '/config/database.php';

// Ambil semua tenants
$tenants = $pdo->query("SELECT * FROM tenants ORDER BY id ASC")->fetchAll();

// Hitung Statistik Global SaaS
$totalTenants = count($tenants);

// Hitung total mahasiswa di seluruh tenant
$totalMhsAll = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();

// Hitung MRR (Monthly Recurring Revenue)
$mrr = 0;
foreach ($tenants as $t) {
    if ($t['status_langganan'] === 'Aktif') {
        $mrr += (float)$t['biaya_bulanan'];
    }
}

// Hitung jumlah mahasiswa per tenant
$mhsPerTenant = [];
$counts = $pdo->query("SELECT tenant_id, COUNT(*) as cnt FROM mahasiswa GROUP BY tenant_id")->fetchAll();
foreach ($counts as $c) {
    $mhsPerTenant[$c['tenant_id']] = $c['cnt'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Super Admin &bull; Platform Multi-Kampus SIA</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen antialiased flex flex-col">

    <!-- Top Admin Bar -->
    <header class="bg-slate-950 border-b border-slate-800 px-6 py-4 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white flex items-center gap-2">
                    SUPER ADMIN SAAS <span class="text-[10px] bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold">PLATFORM ENGINE</span>
                </h1>
                <p class="text-xs text-slate-400">Manajemen Multi-Tenancy & Lisensi Kampus Mitra</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="saas.php" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5">
                <i class="fas fa-globe text-indigo-400"></i> Landing Page
            </a>
            <a href="index.php" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md transition-colors flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali ke Portal Mahasiswa
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full space-y-8">
        
        <!-- Header Banner & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div>
                <h2 class="text-2xl font-extrabold text-white">Ringkasan Ekosistem Cloud SaaS</h2>
                <p class="text-xs text-slate-400 mt-1">Pantau seluruh perguruan tinggi mitra, kuota kapasitas mahasiswa, dan recurring revenue.</p>
            </div>

            <button onclick="openAddTenantModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/20 flex items-center gap-2 transition-all">
                <i class="fas fa-plus"></i> Tambah / Onboard Kampus Baru
            </button>
        </div>

        <!-- 4 Global SaaS Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- 1. Total Tenants -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perguruan Tinggi Mitra</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-university"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-3xl font-extrabold text-white"><?= $totalTenants; ?></span>
                    <span class="text-xs text-slate-500 ml-1">Institusi Aktif</span>
                </div>
                <p class="text-[11px] text-emerald-400 mt-2"><i class="fas fa-check-circle mr-1"></i>Multi-Tenant Terisolasi</p>
            </div>

            <!-- 2. MRR (Monthly Recurring Revenue) -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">MRR (Monthly Revenue)</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-extrabold text-emerald-400"><?= formatRupiah($mrr); ?></span>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">Akumulasi langganan aktif / bulan</p>
            </div>

            <!-- 3. Total Mahasiswa di Cloud -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Mahasiswa Cloud</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-3xl font-extrabold text-white"><?= number_format($totalMhsAll); ?></span>
                    <span class="text-xs text-slate-500 ml-1">Akun Terdaftar</span>
                </div>
                <p class="text-[11px] text-blue-400 mt-2"><i class="fas fa-database mr-1"></i>Tersinkronisasi Realtime</p>
            </div>

            <!-- 4. Cloud Server Uptime -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kesehatan Server SaaS</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-extrabold text-emerald-400">99.98%</span>
                    <span class="text-xs text-slate-500 ml-1">Uptime</span>
                </div>
                <p class="text-[11px] text-emerald-400 mt-2"><i class="fas fa-shield-alt mr-1"></i>Auto Failover Ready</p>
            </div>
        </div>

        <!-- Tenant Management Table (CRUD) -->
        <div class="bg-slate-950 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-white text-sm">Daftar Perguruan Tinggi / Tenant Terdaftar</h3>
                    <span class="text-xs bg-slate-800 px-2 py-0.5 rounded text-slate-300 font-mono"><?= $totalTenants; ?> Kampus</span>
                </div>

                <div class="w-full sm:w-64">
                    <input type="text" id="searchTenant" onkeyup="filterTable('searchTenant', 'tableTenants')" 
                           placeholder="Cari kampus atau paket..." 
                           class="w-full px-3 py-1.5 text-xs bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="tableTenants" class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900/80 text-[11px] uppercase tracking-wider font-bold text-slate-400 border-b border-slate-800">
                            <th class="py-3.5 px-4">Kampus & Tema</th>
                            <th class="py-3.5 px-4">Subdomain / Slug</th>
                            <th class="py-3.5 px-4">Paket Langganan</th>
                            <th class="py-3.5 px-4">Kapasitas Kuota</th>
                            <th class="py-3.5 px-4">Biaya Bulanan</th>
                            <th class="py-3.5 px-4">Status & Masa Aktif</th>
                            <th class="py-3.5 px-4 text-center">Aksi Manajemen (CRUD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($tenants as $tn): ?>
                            <?php $currentMhsCount = $mhsPerTenant[$tn['id']] ?? 0; ?>
                            <tr class="hover:bg-slate-900/50 transition-colors">
                                
                                <!-- Kampus & Warna Tema -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow" style="background-color: <?= htmlspecialchars($tn['warna_primer']); ?>;"></span>
                                        <div>
                                            <span class="font-bold text-white text-sm block"><?= htmlspecialchars($tn['nama_kampus']); ?></span>
                                            <span class="text-[11px] text-slate-400 font-semibold"><?= htmlspecialchars($tn['nama_singkat']); ?> &bull; <?= htmlspecialchars($tn['email_kontak']); ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Slug / Subdomain -->
                                <td class="py-3.5 px-4 font-mono text-indigo-400 font-semibold">
                                    <?= htmlspecialchars($tn['slug']); ?>.sia.cloud
                                </td>

                                <!-- Paket Langganan -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-lg font-bold text-[11px] <?= $tn['paket'] === 'Enterprise' ? 'bg-purple-950 text-purple-300 border border-purple-800' : ($tn['paket'] === 'Pro' ? 'bg-indigo-950 text-indigo-300 border border-indigo-800' : 'bg-slate-800 text-slate-300 border border-slate-700'); ?>">
                                        <?= htmlspecialchars($tn['paket']); ?>
                                    </span>
                                </td>

                                <!-- Kuota Mahasiswa -->
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-200">
                                        <?= $currentMhsCount; ?> / <?= number_format($tn['kuota_mahasiswa']); ?> Mhs
                                    </div>
                                    <div class="w-24 bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                        <?php $perc = min(100, round(($currentMhsCount / max(1, $tn['kuota_mahasiswa'])) * 100)); ?>
                                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?= $perc; ?>%"></div>
                                    </div>
                                </td>

                                <!-- Biaya Bulanan -->
                                <td class="py-3.5 px-4 font-mono font-bold text-emerald-400">
                                    <?= formatRupiah($tn['biaya_bulanan']); ?>
                                </td>

                                <!-- Status & Masa Aktif -->
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tn['status_langganan'] === 'Aktif' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800'; ?>">
                                        <?= htmlspecialchars($tn['status_langganan']); ?>
                                    </span>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Exp: <?= htmlspecialchars($tn['tanggal_kadaluarsa'] ?: '-'); ?></p>
                                </td>

                                <!-- Aksi CRUD & Switch Context -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Tombol Masuk ke Portal Kampus Ini -->
                                        <a href="actions/SaasAction.php?action=switch_tenant&tenant_id=<?= $tn['id']; ?>&redirect=../index.php" 
                                           class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-[11px] shadow transition-colors flex items-center gap-1"
                                           title="Buka Portal Mahasiswa Kampus Ini">
                                            <i class="fas fa-sign-in-alt"></i> Masuk
                                        </a>

                                        <!-- Edit Modal Button -->
                                        <button onclick='openEditTenantModal(<?= json_encode($tn); ?>)' 
                                                class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors flex items-center justify-center text-xs" 
                                                title="Edit Data Kampus">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <?php if ($tn['id'] > 1): ?>
                                            <button onclick="confirmDelete('actions/SaasAction.php?action=delete_tenant&id=<?= $tn['id']; ?>', 'Hapus Kampus Mitra?', 'Seluruh konfigurasi kampus <?= addslashes($tn['nama_kampus']); ?> akan dihapus!')" 
                                                    class="w-7 h-7 rounded-lg bg-rose-950/60 text-rose-400 hover:bg-rose-900 transition-colors flex items-center justify-center text-xs" 
                                                    title="Hapus Kampus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ================= MODAL TAMBAH KAMPUS / TENANT ================= -->
    <div id="modalAddTenant" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 bg-indigo-600 text-white flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Onboard Kampus Baru (SaaS Tenant)
                </h3>
                <button onclick="closeAddTenantModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <form action="actions/SaasAction.php" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" name="action" value="create_tenant">

                <div>
                    <label class="block font-bold text-slate-300 mb-1">Nama Resmi Perguruan Tinggi *</label>
                    <input type="text" name="nama_kampus" placeholder="Contoh: Institut Teknologi Sains Nusantara" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Singkatan / Akronim *</label>
                        <input type="text" name="nama_singkat" placeholder="Contoh: ITSN" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Slug Subdomain</label>
                        <input type="text" name="slug" placeholder="itsn" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Paket Langganan *</label>
                        <select name="paket" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-bold">
                            <option value="Starter">Starter (500 Mhs - Rp 1.5jt/bln)</option>
                            <option value="Pro" selected>Pro (2.500 Mhs - Rp 3.5jt/bln)</option>
                            <option value="Enterprise">Enterprise (Unlimited - Rp 7.5jt/bln)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Warna Identitas Kampus</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="warna_primer" value="#4f46e5" class="h-8 w-12 bg-transparent rounded cursor-pointer">
                            <span class="text-slate-400 text-[11px]">Primer</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Email Resmi Kampus *</label>
                        <input type="email" name="email_kontak" placeholder="info@kampus.ac.id" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Nomor Telepon</label>
                        <input type="text" name="no_telp" placeholder="031-1234567" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1">Alamat Kampus</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="closeAddTenantModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-lg shadow-indigo-600/30">
                        Simpan & Onboard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL EDIT KAMPUS / TENANT ================= -->
    <div id="modalEditTenant" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-5 bg-slate-800 text-white flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-edit text-amber-400"></i> Edit Konfigurasi Kampus & Paket
                </h3>
                <button onclick="closeEditTenantModal()" class="text-white/80 hover:text-white text-lg">&times;</button>
            </div>

            <form action="actions/SaasAction.php" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" name="action" value="update_tenant">
                <input type="hidden" id="edit_tenant_id" name="id">

                <div>
                    <label class="block font-bold text-slate-300 mb-1">Nama Kampus *</label>
                    <input type="text" id="edit_nama_kampus" name="nama_kampus" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Singkatan *</label>
                        <input type="text" id="edit_nama_singkat" name="nama_singkat" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Slug</label>
                        <input type="text" id="edit_slug" name="slug" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Paket</label>
                        <select id="edit_paket" name="paket" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-bold">
                            <option value="Starter">Starter</option>
                            <option value="Pro">Pro</option>
                            <option value="Enterprise">Enterprise</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Status</label>
                        <select id="edit_status" name="status_langganan" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-bold">
                            <option value="Aktif">Aktif</option>
                            <option value="Masa Percobaan">Masa Percobaan</option>
                            <option value="Kedaluwarsa">Kedaluwarsa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Kedaluwarsa</label>
                        <input type="date" id="edit_kadaluarsa" name="tanggal_kadaluarsa" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Kuota Mahasiswa</label>
                        <input type="number" id="edit_kuota" name="kuota_mahasiswa" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Biaya Bulanan (Rp)</label>
                        <input type="number" id="edit_biaya" name="biaya_bulanan" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Email Kontak</label>
                        <input type="email" id="edit_email" name="email_kontak" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1">Warna Primer</label>
                        <input type="color" id="edit_warna" name="warna_primer" class="h-8 w-full bg-transparent rounded cursor-pointer">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="closeEditTenantModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-lg shadow-indigo-600/30">
                        Update Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
    function openAddTenantModal() {
        document.getElementById('modalAddTenant').classList.remove('hidden');
    }
    function closeAddTenantModal() {
        document.getElementById('modalAddTenant').classList.add('hidden');
    }

    function openEditTenantModal(data) {
        document.getElementById('edit_tenant_id').value = data.id;
        document.getElementById('edit_nama_kampus').value = data.nama_kampus;
        document.getElementById('edit_nama_singkat').value = data.nama_singkat;
        document.getElementById('edit_slug').value = data.slug;
        document.getElementById('edit_paket').value = data.paket;
        document.getElementById('edit_status').value = data.status_langganan;
        document.getElementById('edit_kadaluarsa').value = data.tanggal_kadaluarsa || '';
        document.getElementById('edit_kuota').value = data.kuota_mahasiswa;
        document.getElementById('edit_biaya').value = data.biaya_bulanan;
        document.getElementById('edit_email').value = data.email_kontak || '';
        document.getElementById('edit_warna').value = data.warna_primer || '#4f46e5';

        document.getElementById('modalEditTenant').classList.remove('hidden');
    }
    function closeEditTenantModal() {
        document.getElementById('modalEditTenant').classList.add('hidden');
    }
    </script>

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
