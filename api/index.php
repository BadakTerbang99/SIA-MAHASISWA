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

// 2. Request Action Scripts (misal: actions/AuthAction.php, actions/JadwalAction.php, dll)
if (strpos($path, 'actions/') === 0) {
    $actionFile = __DIR__ . '/../' . $path;
    if (file_exists($actionFile) && !is_dir($actionFile)) {
        require $actionFile;
        exit;
    }
}

// 3. Auto-redirect URL berakhiran .php ke Clean URL untuk request GET di luar folder actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && substr($path, -4) === '.php') {
    $cleanPath = substr($path, 0, -4);
    if ($cleanPath === 'index') {
        $cleanPath = '';
    }
    $query = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: /' . $cleanPath . $query, true, 301);
    exit;
}

// 4. Direct match file PHP
$target = __DIR__ . '/../' . $path;
if (file_exists($target) && !is_dir($target)) {
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $target;
        exit;
    }
    
    // Static asset fallback jika terlewat
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

// 5. Match tanpa ekstensi .php (misal: /login, /jadwal, /nilai, /biodata, /ektm, /keuangan)
if (file_exists($target . '.php')) {
    require $target . '.php';
    exit;
}

// 6. Fallback 404
http_response_code(404);
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'>";
echo "<h2>Halaman Tidak Ditemukan (404)</h2>";
echo "<p><a href='/login'>Masuk ke SIA Mahasiswa</a></p>";
echo "</body></html>";
exit;
