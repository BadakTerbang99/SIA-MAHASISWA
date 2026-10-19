<?php
require_once __DIR__ . '/config/database.php';

// Jika sudah login, langsung alihkan ke index.php
if (isset($_SESSION['logged_in_user'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIA Mahasiswa &bull; Universitas Sanata Dharma</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        usd: {
                            50: '#fdf4f4',
                            100: '#fbe6e6',
                            200: '#f7d0d0',
                            600: '#941717',
                            700: '#7a0c0c',
                            800: '#580a0a',
                            900: '#3d0505',
                        },
                        gold: {
                            50: '#fefbf3',
                            100: '#fcf4dc',
                            500: '#c59b27',
                            600: '#a8821d',
                            700: '#896716',
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

    <!-- Fonts & Style -->
    <link rel="icon" type="image/png" href="assets/images/usd_logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between selection:bg-usd-700 selection:text-white font-['Plus_Jakarta_Sans']">

    <!-- Top Clean Navigation Bar (Inspired by student.ciputra.ac.id) -->
    <header class="w-full bg-white border-b border-slate-200 shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 py-3.5 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 p-1 flex items-center justify-center shrink-0">
                    <img src="assets/images/usd_logo.png" alt="Logo USD" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-usd-800 tracking-tight leading-tight">
                        UNIVERSITAS SANATA DHARMA
                    </h1>
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider leading-none mt-0.5">
                        Portal Layanan Akademik Mahasiswa
                    </p>
                </div>
            </div>

            <!-- Helpdesk Contacts -->
            <div class="hidden md:flex items-center gap-5 text-xs font-semibold text-slate-600">
                <a href="https://www.usd.ac.id" target="_blank" class="hover:text-usd-700 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-globe text-slate-400"></i>
                    <span>usd.ac.id</span>
                </a>
                <span class="text-slate-300">|</span>
                <a href="mailto:akademik@usd.ac.id" class="hover:text-usd-700 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-envelope text-slate-400"></i>
                    <span>akademik@usd.ac.id</span>
                </a>
                <span class="text-slate-300">|</span>
                <a href="https://wa.me/6282134567890" target="_blank" class="hover:text-emerald-600 transition-colors flex items-center gap-1.5">
                    <i class="fab fa-whatsapp text-emerald-600"></i>
                    <span>Layanan BAAK: 0821-3456-7890</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-8 py-10 sm:py-16 flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-16">
        
        <!-- Left Side: Clean Academic Focus -->
        <div class="w-full lg:w-7/12 space-y-6 text-center lg:text-left">
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-usd-50 border border-usd-200 text-usd-700 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-usd-700"></span>
                <span>Tahun Akademik 2026/2027 &bull; Semester Ganjil</span>
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Sistem Informasi Akademik <br class="hidden sm:inline">
                    <span class="text-usd-700">Mahasiswa Sanata Dharma</span>
                </h2>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
                    Satu portal terpadu untuk mengakses rencana studi (KRS), jadwal perkuliahan, capaian transkrip nilai (KHS), kartu tanda mahasiswa digital (E-KTM), serta administrasi keuangan perkuliahan.
                </p>
            </div>

            <!-- Authentic Feature List -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-w-lg mx-auto lg:mx-0 text-left pt-2">
                <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-sm flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-usd-50 text-usd-700 flex items-center justify-center shrink-0 text-sm font-bold mt-0.5">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Jadwal Kuliah & KRS</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ruang kuliah, lab, dan tautan kelas online.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-sm flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 text-sm font-bold mt-0.5">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">KHS & Transkrip Kumulatif</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kalkulasi IPK dan riwayat nilai resmi.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-sm flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 text-sm font-bold mt-0.5">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">SIA E-KTM Digital</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kartu 3D, NFC & verifikasi QR Code.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-sm flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 text-sm font-bold mt-0.5">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Administrasi UKT</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Status tagihan dan bukti pembayaran lunas.</p>
                    </div>
                </div>
            </div>

            <div class="pt-2 text-xs text-slate-500">
                <p><i class="fas fa-map-marker-alt text-usd-700 mr-1.5"></i> Kampus III Paingan, Maguwoharjo, Sleman, D.I. Yogyakarta</p>
            </div>
        </div>

        <!-- Right Side: Clean Login Card -->
        <div class="w-full lg:w-5/12 max-w-md mx-auto lg:mx-0">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-lg shadow-slate-200/50 p-7 sm:p-8">
                
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                        Masuk ke Akun Anda
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Gunakan NIM dan kata sandi yang telah terdaftar resmi di BAAK.
                    </p>
                </div>

                <form action="actions/AuthAction.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="login">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>NIM / Username Mahasiswa</span>
                            <span class="text-[11px] font-normal text-slate-400">Contoh: 235314182</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">
                                <i class="fas fa-user-graduate"></i>
                            </span>
                            <input type="text" id="input_username" name="username" placeholder="Masukkan NIM Anda" required 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-usd-700 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Kata Sandi / Password</span>
                            <a href="#" onclick="forgotPasswordNotice()" class="text-[11px] text-usd-700 font-semibold hover:underline">Lupa sandi?</a>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="input_password" name="password" placeholder="Masukkan password Anda" required 
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-usd-700 focus:border-transparent transition-all">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-2.5 text-slate-400 hover:text-slate-600 text-sm focus:outline-none">
                                <i id="password_toggle_icon" class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-usd-700 focus:ring-usd-700">
                            <span class="text-slate-600 text-xs">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-usd-700 hover:bg-usd-800 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                            <span>Masuk ke SIA Mahasiswa</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                    <p>Butuh bantuan? Hubungi <a href="mailto:akademik@usd.ac.id" class="text-usd-700 font-bold hover:underline">BAAK Sanata Dharma</a></p>
                </div>

            </div>
        </div>

    </main>

    <!-- Clean Footer Bar -->
    <footer class="w-full bg-white border-t border-slate-200 py-4 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <p>&copy; <?= date('Y'); ?> Universitas Sanata Dharma &bull; Fakultas Sains dan Teknologi</p>
            <div class="flex items-center gap-4">
                <a href="https://www.usd.ac.id" target="_blank" class="hover:text-usd-700 transition-colors">Portal Utama</a>
                <span>&bull;</span>
                <a href="mailto:akademik@usd.ac.id" class="hover:text-usd-700 transition-colors">Bantuan BAAK</a>
                <span>&bull;</span>
                <a href="https://wa.me/6282134567890" target="_blank" class="hover:text-usd-700 transition-colors">WhatsApp Hotline</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('input_password');
        const icon = document.getElementById('password_toggle_icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function forgotPasswordNotice() {
        Swal.fire({
            icon: 'info',
            title: 'Reset Kata Sandi',
            html: 'Untuk akun mahasiswa semester 7, password default Anda adalah <b>1122</b>.<br><br>Jika Anda lupa sandi yang diubah, silakan hubungi bagian BAAK Kampus Paingan USD melalui WhatsApp <b>0821-3456-7890</b>.',
            confirmButtonColor: '#7a0c0c',
            confirmButtonText: 'Tutup'
        });
    }
    </script>

    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'info' ? 'info' : 'success'); ?>',
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
