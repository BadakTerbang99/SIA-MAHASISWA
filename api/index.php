<?php
/**
 * Vercel Serverless PHP Router
 * Mengarahkan seluruh request Vercel ke file PHP yang sesuai di root project
 */

// Pindah working directory ke root project agar path relatif bekerja normal
chdir(dirname(__DIR__));

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($requestUri, '/');

// Jika akses root atau index
if ($path === '' || $path === 'index' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// Cek apakah file yang diminta langsung ada
$targetFile = __DIR__ . '/../' . $path;

if (file_exists($targetFile) && !is_dir($targetFile)) {
    $ext = pathinfo($targetFile, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        require $targetFile;
        exit;
    }
}

// Cek jika tanpa ekstensi .php (misal /login)
if (file_exists($targetFile . '.php')) {
    require $targetFile . '.php';
    exit;
}

// Cek file di dalam folder actions/ (misal /actions/AuthAction.php)
if (strpos($path, 'actions/') === 0 && file_exists(__DIR__ . '/../' . $path)) {
    require __DIR__ . '/../' . $path;
    exit;
}

// Fallback 404
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'>";
echo "<h1>404 - Halaman Tidak Ditemukan</h1>";
echo "<p><a href='/'>Kembali ke Beranda</a></p>";
echo "</body></html>";
exit;
