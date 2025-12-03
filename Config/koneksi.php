<?php
/**
 * File Koneksi Database
 * Menggunakan mysqli_connect untuk koneksi ke database toko_outdoor
 */

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toko_outdoor');

// Membuat koneksi ke database
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Pengecekan koneksi
if (!$koneksi) {
    die("Gagal terkoneksi ke database: " . mysqli_connect_error());
}

// Set charset ke utf8mb4
mysqli_set_charset($koneksi, "utf8mb4");

// Fungsi untuk query
function query($sql) {
    global $koneksi;
    $result = mysqli_query($koneksi, $sql);
    
    if (!$result) {
        die("Query Error: " . mysqli_error($koneksi));
    }
    
    return $result;
}

// Fungsi untuk mengambil satu baris data
function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mengambil semua data
function fetch_all($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Fungsi untuk menghitung jumlah baris
function num_rows($result) {
    return mysqli_num_rows($result);
}

// Fungsi untuk escape string
function escape($string) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, $string);
}
?>
