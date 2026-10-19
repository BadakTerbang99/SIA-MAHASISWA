<?php
$pageTitle = "SIA E-KTM Digital";
$pageHeader = "Kartu Tanda Mahasiswa Elektronik";
require_once __DIR__ . '/includes/header.php';

// Data E-KTM
$fotoPath = (!empty($currentUser['foto']) && file_exists(__DIR__ . '/uploads/photos/' . $currentUser['foto'])) ? 'uploads/photos/' . htmlspecialchars($currentUser['foto']) : ((!empty($currentUser['foto']) && file_exists(__DIR__ . '/assets/images/' . $currentUser['foto'])) ? 'assets/images/' . htmlspecialchars($currentUser['foto']) : 'assets/images/shella.svg');
$masaBerlaku = ($currentUser['angkatan'] ?? 2023) + 4;
?>

<!-- Header Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">SIA E-KTM (Electronic Student Identity Card)</h2>
        <p class="text-xs text-slate-500">Kartu Tanda Mahasiswa Digital resmi <?= htmlspecialchars($currentTenant['nama_kampus']); ?> dilengkapi NFC & QR Code</p>
    </div>

    <div class="flex items-center gap-2.5">
        <button onclick="flipCard()" class="px-4 py-2 bg-usd-700 hover:bg-usd-800 text-white rounded-xl text-xs font-bold shadow-md shadow-usd-700/20 flex items-center gap-2 transition-all">
            <i class="fas fa-sync-alt"></i> Putar Kartu (Depan / Belakang)
        </button>
        <button onclick="downloadKtmCard()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center gap-2 transition-all">
            <i class="fas fa-download"></i> Unduh Kartu (PNG)
        </button>
        <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm flex items-center gap-2 transition-colors">
            <i class="fas fa-print text-usd-700"></i> Cetak Kartu
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left Column: 3D Card Showcase (7/12) -->
    <div class="lg:col-span-7 flex flex-col items-center">
        
        <!-- Card Container with 3D Perspective -->
        <div class="perspective-1000 w-full max-w-[500px] h-[310px] sm:h-[320px] select-none cursor-pointer" onclick="flipCard()">
            <div id="ktm-card-inner" class="card-inner w-full h-full relative">
                
                <!-- ================= SISI DEPAN (FRONT) ================= -->
                <div id="ktm-front" class="card-front bg-gradient-to-tr from-slate-950 via-usd-900 to-slate-900 text-white p-5 shadow-2xl border border-amber-500/30 flex flex-col justify-between overflow-hidden relative">
                    <!-- Hologram Shimmer Ribbon -->
                    <div class="absolute inset-0 hologram-shimmer pointer-events-none opacity-20"></div>
                    <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-amber-500/10 blur-xl pointer-events-none"></div>

                    <!-- Header Kartu -->
                    <div class="flex items-center justify-between pb-3 border-b border-amber-500/30 relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white p-1 border border-white/30 shadow-sm flex items-center justify-center shrink-0">
                                <img src="assets/images/<?= htmlspecialchars($currentTenant['logo']); ?>" alt="Univ Logo" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold tracking-wider uppercase text-white leading-tight">
                                    <?= htmlspecialchars($currentTenant['nama_kampus']); ?>
                                </h3>
                                <p class="text-[10px] font-semibold text-amber-300 tracking-widest uppercase">
                                    KARTU TANDA MAHASISWA ELEKTRONIK
                                </p>
                            </div>
                        </div>

                        <!-- Contactless NFC Wave Icon -->
                        <div class="flex items-center text-amber-300 text-lg">
                            <i class="fas fa-wifi rotate-90" title="NFC Ready"></i>
                        </div>
                    </div>

                    <!-- Body Kartu: Chip, Foto, dan Data -->
                    <div class="flex items-center gap-4 py-2 relative z-10">
                        <!-- Foto Mahasiswa -->
                        <div class="relative shrink-0">
                            <img src="<?= $fotoPath; ?>" 
                                 alt="Foto Mahasiswa" 
                                 class="w-24 h-28 sm:w-28 sm:h-32 rounded-xl object-cover ring-2 ring-amber-400/50 shadow-md">
                            <div class="absolute bottom-1 right-1 bg-usd-700 text-white text-[8px] font-bold px-1.5 py-0.5 rounded backdrop-blur">
                                VALID
                            </div>
                        </div>

                        <!-- Smartcard Chip & Biodata Ringkas -->
                        <div class="flex-1 overflow-hidden space-y-1.5">
                            <!-- Smartcard Gold Chip SVG -->
                            <div class="w-10 h-8 mb-1">
                                <img src="assets/images/chip.svg" alt="Chip" class="w-full h-full">
                            </div>

                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-amber-300 font-bold">Nama Lengkap</p>
                                <h4 class="text-sm font-extrabold text-white truncate tracking-tight"><?= htmlspecialchars($currentUser['nama']); ?></h4>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-[9px] uppercase tracking-wider text-amber-300 font-bold">NIM</p>
                                    <p class="text-xs font-mono font-bold text-amber-300"><?= htmlspecialchars($currentUser['nim']); ?></p>
                                </div>
                                <div>
                                    <p class="text-[9px] uppercase tracking-wider text-amber-300 font-bold">Program Studi</p>
                                    <p class="text-[11px] font-bold text-slate-200 truncate"><?= htmlspecialchars($currentUser['prodi']); ?></p>
                                </div>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-indigo-300 font-bold">Fakultas</p>
                                <p class="text-[10px] text-slate-300 truncate"><?= htmlspecialchars($currentUser['fakultas']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu Depan -->
                    <div class="flex items-center justify-between pt-2 border-t border-indigo-500/30 text-[9px] text-indigo-200 font-medium relative z-10">
                        <span>Angkatan: <?= htmlspecialchars($currentUser['angkatan'] ?? '2022'); ?></span>
                        <span class="font-mono tracking-widest text-indigo-300">UTN-MHS-SMARTCARD</span>
                        <span>Berlaku Hingga: <?= $masaBerlaku; ?></span>
                    </div>
                </div>

                <!-- ================= SISI BELAKANG (BACK) ================= -->
                <div id="ktm-back" class="card-back bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950 text-white p-5 shadow-2xl border border-indigo-500/30 flex flex-col justify-between overflow-hidden relative">
                    <!-- Magnetic Stripe Simulation -->
                    <div class="w-full h-9 bg-slate-950 -mx-5 px-5 flex items-center justify-between border-y border-slate-800">
                        <span class="text-[9px] font-mono text-slate-500">ELECTRONIC IDENTITY STORAGE CHIP (UTN-E-ID)</span>
                    </div>

                    <!-- Ketentuan & QR Code -->
                    <div class="grid grid-cols-12 gap-3 py-1 items-center">
                        <div class="col-span-8 space-y-1 text-[9px] text-slate-300 leading-tight">
                            <p class="font-bold text-white uppercase text-[10px] mb-1">Ketentuan Penggunaan:</p>
                            <p>1. Kartu ini merupakan identitas resmi civitas akademika <?= htmlspecialchars($currentTenant['nama_kampus']); ?>.</p>
                            <p>2. Digunakan untuk akses presensi perkuliahan RFID, peminjaman buku perpustakaan & transaksi digital kampus.</p>
                            <p>3. Jika kartu ini hilang atau ditemukan, harap diserahkan kepada Biro Administrasi Akademik (BAAK) Kampus Paingan USD.</p>
                        </div>

                        <!-- Dynamic QR Code Container -->
                        <div class="col-span-4 flex flex-col items-center">
                            <div id="qrcode" class="p-1.5 bg-white rounded-lg shadow"></div>
                            <span class="text-[8px] font-mono text-amber-300 mt-1">Scan Verifikasi</span>
                        </div>
                    </div>

                    <!-- Barcode NIM & Pejabat Pengesah -->
                    <div class="flex items-end justify-between pt-2 border-t border-slate-800">
                        <!-- Barcode NIM SVG -->
                        <div class="bg-white px-2 py-0.5 rounded flex items-center">
                            <svg id="barcode" class="h-9"></svg>
                        </div>

                        <!-- Tanda Tangan & Stempel -->
                        <div class="text-right text-[8px] text-slate-300">
                            <p>Yogyakarta, <?= date('d F Y'); ?></p>
                            <p class="font-semibold text-white">Wakil Rektor Bidang Akademik,</p>
                            <div class="h-6 flex items-center justify-end font-serif italic text-amber-300 text-xs">
                                Prof. Ir. Sudi Mungkasi, Ph.D.
                            </div>
                            <p class="font-mono text-slate-400">NIP. 197805202003121002</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <p class="text-xs text-slate-400 mt-4 flex items-center gap-1.5">
            <i class="fas fa-info-circle text-indigo-500"></i> Klik kartu atau tombol "Putar Kartu" untuk melihat bagian belakang kartu.
        </p>

    </div>

    <!-- Right Column: Card Features & Security Details (5/12) -->
    <div class="lg:col-span-5 space-y-6">
        
        <!-- Status Verifikasi Kartu -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fas fa-shield-alt text-emerald-500"></i> Status Keabsahan E-KTM
                </h3>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                    TERVERIFIKASI AKTIF
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Nomor Kartu Digital:</span>
                    <span class="font-mono font-bold text-slate-700">EKTM-<?= htmlspecialchars($currentUser['nim']); ?>-2026</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Teknologi Tersemat:</span>
                    <span class="font-semibold text-indigo-600">NFC Smartcard + QR Code 2D</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Status Pembayaran UKT:</span>
                    <span class="font-bold text-emerald-600">Bebas Tanggungan</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Masa Aktif Kartu:</span>
                    <span class="font-bold text-slate-700">Hingga <?= $masaBerlaku; ?> / Lulus S1</span>
                </div>
            </div>
        </div>

        <!-- Fitur Layanan Terintegrasi E-KTM -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-network-wired text-indigo-600"></i> Integrasi Layanan Kampus
            </h3>
            
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-wifi text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Presensi RFID Otomatis</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Cukup tempelkan E-KTM fisik atau ponsel ber-NFC pada sensor reader kelas.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-book-reader text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Akses Perpustakaan & E-Journal</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Akses masuk gate perpustakaan pusat dan peminjaman buku otomatis via barcode.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-coins text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Dompet Digital Kampus (UTN-Pay)</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Dapat digunakan sebagai uang elektronik untuk pembayaran kantin dan fotokopi.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Inisialisasi QR Code, Barcode, & Download Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR Code
    const qrData = "https://sia.univ.ac.id/verify?nim=<?= urlencode($currentUser['nim']); ?>&mhs=<?= urlencode($currentUser['nama']); ?>";
    new QRCode(document.getElementById("qrcode"), {
        text: qrData,
        width: 72,
        height: 72,
        colorDark : "#0f172a",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });

    // Generate Barcode
    JsBarcode("#barcode", "<?= $currentUser['nim']; ?>", {
        format: "CODE128",
        width: 1.2,
        height: 30,
        displayValue: true,
        fontSize: 9,
        margin: 2
    });
});

// Download Card Front as Image PNG
function downloadKtmCard() {
    const cardFront = document.getElementById('ktm-front');
    Swal.fire({
        title: 'Memproses Gambar...',
        text: 'Sedang menyiapkan E-KTM Digital Anda.',
        timer: 1000,
        didOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        html2canvas(cardFront, { scale: 2 }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'EKTM_<?= $currentUser['nim']; ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Diunduh!',
                text: 'Kartu E-KTM telah tersimpan di komputer Anda.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
