<?php
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';

// 1. Process Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        setFlash('error', 'NIM/Username dan Password wajib diisi!');
        header('Location: ../login.php');
        exit;
    }

    // Cari user mahasiswa berdasarkan NIM atau username
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ? OR username = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user) {
        $valid = false;
        
        // Verifikasi hash password atau kecocokan akun Shella Ardana Andryansah
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            $valid = true;
        } elseif ($password === '1122' && ($user['nim'] === '235314182' || ($user['username'] ?? '') === '235314182')) {
            $valid = true;
        } elseif (($user['password'] ?? '') === $password) {
            $valid = true;
        }

        if ($valid) {
            $_SESSION['logged_in_user'] = [
                'id' => $user['id'],
                'nim' => $user['nim'],
                'nama' => $user['nama'],
                'tenant_id' => $user['tenant_id'],
                'foto' => $user['foto'],
                'role' => $user['role'] ?? 'mahasiswa'
            ];
            $_SESSION['active_mahasiswa_id'] = $user['id'];
            $_SESSION['active_tenant_id'] = $user['tenant_id'];

            // Cookie persisten untuk lingkungan Serverless (Vercel)
            $authPayload = json_encode([
                'id' => $user['id'],
                'nim' => $user['nim'],
                'exp' => time() + 86400 * 7
            ]);
            $authHash = hash_hmac('sha256', $authPayload, 'sia_usd_secret_key_2026');
            $authCookie = base64_encode($authPayload . '||' . $authHash);
            setcookie('sia_auth_session', $authCookie, [
                'expires' => time() + 86400 * 7,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            setFlash('success', 'Selamat datang di SIA Sanata Dharma, ' . htmlspecialchars($user['nama']) . '!');
            header('Location: ../');
            exit;
        }
    }

    setFlash('error', 'Kombinasi NIM/Username dan Password tidak valid!');
    header('Location: ../login');
    exit;
}

// 2. Process Logout
if ($action === 'logout') {
    unset($_SESSION['logged_in_user']);
    unset($_SESSION['active_mahasiswa_id']);
    setcookie('sia_auth_session', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    setFlash('info', 'Anda telah berhasil logout dari Portal SIA Mahasiswa.');
    header('Location: ../login');
    exit;
}

header('Location: ../login');
exit;
