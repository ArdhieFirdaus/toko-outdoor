<?php
/**
 * File Helper - Fungsi-fungsi umum
 */

require_once __DIR__ . '/config.php';

session_start();

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['id_user']) && isset($_SESSION['role']);
}

// Fungsi untuk cek role
function check_role($required_roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    if (is_string($required_roles)) {
        return $_SESSION['role'] === $required_roles;
    }
    
    if (is_array($required_roles)) {
        return in_array($_SESSION['role'], $required_roles);
    }
    
    return false;
}

// Fungsi untuk redirect jika tidak login
function require_login() {
    if (!is_logged_in()) {
        header('Location: ../login.php?message=Silahkan login terlebih dahulu');
        exit();
    }
}

// Fungsi untuk redirect jika tidak punya akses
function require_role($roles) {
    require_login();
    
    if (!check_role($roles)) {
        header('Location: ../dashboard.php?message=Anda tidak memiliki akses ke halaman ini');
        exit();
    }
}

// Fungsi untuk mendapatkan info user
function get_user_info($id_user) {
    global $koneksi;
    $result = query("SELECT * FROM user WHERE id_user = " . intval($id_user));
    return fetch_assoc($result);
}

// Fungsi untuk hash password
function hash_password($password) {
    return hash('sha256', $password);
}

// Fungsi untuk format currency
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Fungsi untuk format tanggal
function format_date($date) {
    $date_obj = new DateTime($date);
    return $date_obj->format('d-m-Y H:i');
}

// Fungsi untuk format tanggal pendek
function format_date_short($date) {
    $date_obj = new DateTime($date);
    return $date_obj->format('d-m-Y');
}

// Fungsi untuk validasi email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk validasi username (minimal 3 karakter, hanya alphanumeric dan underscore)
function is_valid_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,}$/', $username);
}

// Fungsi untuk cek username sudah ada
function username_exists($username, $exclude_id = null) {
    global $koneksi;
    
    $query = "SELECT COUNT(*) as total FROM user WHERE username = '" . escape($username) . "'";
    
    if ($exclude_id) {
        $query .= " AND id_user != " . intval($exclude_id);
    }
    
    $result = query($query);
    $row = fetch_assoc($result);
    
    return $row['total'] > 0;
}

// Fungsi untuk sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk redirect dengan message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit();
}

// Fungsi untuk get message
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}

// Fungsi untuk display message
function display_message() {
    $msg = get_message();
    
    if ($msg) {
        echo '<div class="alert alert-' . htmlspecialchars($msg['type']) . '">';
        echo htmlspecialchars($msg['message']);
        echo '<span class="close-alert" onclick="this.parentElement.style.display=\'none\';">&times;</span>';
        echo '</div>';
    }
}

// Fungsi untuk hitung total transaksi
function get_total_transaksi() {
    global $koneksi;
    $result = query("SELECT COUNT(*) as total FROM transaksi");
    $row = fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk hitung total produk
function get_total_produk() {
    global $koneksi;
    $result = query("SELECT COUNT(*) as total FROM produk");
    $row = fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk hitung total user
function get_total_user() {
    global $koneksi;
    $result = query("SELECT COUNT(*) as total FROM user");
    $row = fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk hitung total penjualan
function get_total_penjualan() {
    global $koneksi;
    $result = query("SELECT SUM(total_harga) as total FROM transaksi WHERE status = 'selesai'");
    $row = fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Fungsi untuk get list user dengan role
function get_users_by_role($role = null) {
    global $koneksi;
    
    $query = "SELECT * FROM user";
    
    if ($role) {
        $query .= " WHERE role = '" . escape($role) . "'";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $result = query($query);
    return fetch_all($result);
}

// Fungsi untuk get list produk
function get_all_produk() {
    global $koneksi;
    $result = query("SELECT * FROM produk ORDER BY nama_produk ASC");
    return fetch_all($result);
}

// Fungsi untuk get produk by id
function get_produk_by_id($id) {
    global $koneksi;
    $result = query("SELECT * FROM produk WHERE id_produk = " . intval($id));
    return fetch_assoc($result);
}

// Fungsi untuk get list transaksi
function get_all_transaksi($limit = null) {
    global $koneksi;
    
    $query = "SELECT t.*, u.nama_lengkap FROM transaksi t 
              JOIN user u ON t.id_user = u.id_user 
              ORDER BY t.created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $result = query($query);
    return fetch_all($result);
}

// Fungsi untuk get detail transaksi
function get_detail_transaksi($id_transaksi) {
    global $koneksi;
    $result = query("SELECT dt.*, p.nama_produk FROM detail_transaksi dt 
                    JOIN produk p ON dt.id_produk = p.id_produk 
                    WHERE dt.id_transaksi = " . intval($id_transaksi));
    return fetch_all($result);
}

// Fungsi untuk get transaksi by id
function get_transaksi_by_id($id) {
    global $koneksi;
    $result = query("SELECT * FROM transaksi WHERE id_transaksi = " . intval($id));
    return fetch_assoc($result);
}

// Fungsi untuk validasi stok
function check_stok($id_produk, $jumlah) {
    global $koneksi;
    $produk = get_produk_by_id($id_produk);
    
    if (!$produk) {
        return false;
    }
    
    return $produk['stok'] >= $jumlah;
}

// Fungsi untuk update stok
function update_stok($id_produk, $jumlah, $type = 'minus') {
    global $koneksi;
    
    if ($type === 'minus') {
        query("UPDATE produk SET stok = stok - " . intval($jumlah) . " WHERE id_produk = " . intval($id_produk));
    } else if ($type === 'plus') {
        query("UPDATE produk SET stok = stok + " . intval($jumlah) . " WHERE id_produk = " . intval($id_produk));
    }
}
?>
