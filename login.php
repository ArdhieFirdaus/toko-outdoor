<?php
/**
 * Halaman Login
 */
session_start();

require_once 'Config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['id_user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong';
    } else {
        // Hash password untuk comparison
        $password_hash = hash('sha256', $password);
        
        // Query user
        $result = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '" . mysqli_real_escape_string($koneksi, $username) . "'");
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if ($user['password'] === $password_hash) {
                // Login berhasil
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah';
            }
        } else {
            $error = 'Username tidak ditemukan';
        }
    }
}

// Cek message dari GET
if (isset($_GET['message'])) {
    $success = sanitize($_GET['message']);
}

// Fungsi sanitize
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🏕️ Toko Outdoor</h1>
            <p>Sistem Informasi Manajemen Toko</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                    <span class="close-alert" onclick="this.parentElement.style.display='none';">&times;</span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <span class="close-alert" onclick="this.parentElement.style.display='none';">&times;</span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            
            
        </div>
    </div>
</body>
</html>
