<?php
/**
 * Vercel Serverless PHP Router
 * Router serbaguna untuk seluruh route aplikasi SIA Mahasiswa di Vercel
 */

// Pindah working directory ke root project agar include relatif dan file database bekerja tepat
chdir(dirname(__DIR__));

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($uri, '/');

// 1. Root URL atau index
if ($path === '' || $path === 'index' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// 2. Direct match file (misal: login.php, actions/AuthAction.php, dll)
$target = __DIR__ . '/../' . $path;
if (file_exists($target) && !is_dir($target)) {
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $target;
        exit;
    }
    
    // Static asset fallback
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'json' => 'application/json'
    ];
    $contentType = isset($mimes[$ext]) ? $mimes[$ext] : mime_content_type($target);
    header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
    readfile($target);
    exit;
}

// 3. Match tanpa ekstensi .php (misal: /login atau /jadwal atau /nilai)
if (file_exists($target . '.php')) {
    require $target . '.php';
    exit;
}

// 4. Fallback 404
http_response_code(404);
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'>";
echo "<h2>Halaman Tidak Ditemukan (404)</h2>";
echo "<p><a href='/login.php'>Masuk ke SIA Mahasiswa</a></p>";
echo "</body></html>";
exit;
