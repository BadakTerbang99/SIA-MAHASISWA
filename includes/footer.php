            </main>

            <!-- Bottom Footer Bar -->
            <footer class="bg-white border-t border-slate-200 py-4 px-6 text-center md:flex md:justify-between md:items-center text-xs text-slate-500 shrink-0">
                <p>&copy; <?= date('Y'); ?> <strong>Portal SIA Mahasiswa</strong> &bull; Universitas Teknologi Nusantara. All rights reserved.</p>
                <div class="mt-2 md:mt-0 space-x-4">
                    <a href="biodata.php" class="hover:text-indigo-600 transition-colors">Bantuan SIA</a>
                    <span>&bull;</span>
                    <a href="ektm.php" class="hover:text-indigo-600 transition-colors">E-KTM Digital</a>
                    <span>&bull;</span>
                    <span class="text-emerald-600 font-medium"><i class="fas fa-circle text-[8px] mr-1"></i>Server Aktif</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global App Scripts -->
    <script src="assets/js/app.js"></script>

    <!-- Trigger SweetAlert for Flash Messages -->
    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'success'); ?>',
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
