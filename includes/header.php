<?php
require_once __DIR__ . '/../config/database.php';

// Auth Guard: Pastikan user hanya dapat mengakses portal jika sudah login
if (!isset($_SESSION['logged_in_user'])) {
    header('Location: login');
    exit;
}

// Detect active page
$currentPage = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - SIA ' . htmlspecialchars($currentTenant['nama_singkat']) : 'Sistem Informasi Akademik ' . htmlspecialchars($currentTenant['nama_kampus']); ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        usd: {
                            50: '#fdf2f2',
                            100: '#fde8e8',
                            200: '#fbd5d5',
                            300: '#f8b4b4',
                            400: '#f98080',
                            500: '#e02424',
                            600: '#991b1b',
                            700: '#800000',
                            800: '#6b0000',
                            900: '#450a0a',
                            950: '#2c0404'
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- QRCode & Barcode -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    
    <!-- HTML2Canvas (Export E-KTM to image) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Custom Style & Favicon -->
    <link rel="icon" type="image/png" href="assets/images/usd_logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased flex flex-col">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Top Navbar -->
            <?php include_once __DIR__ . '/navbar.php'; ?>

            <!-- Main Content Area -->
            <main class="w-full flex-grow p-6 md:p-8 bg-slate-50">
