<?php
/**
 * Configuration File
 * Setup global configuration untuk aplikasi
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error di production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session
ini_set('session.gc_maxlifetime', 3600); // 1 jam
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => false, // Set to true jika HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Output buffering
ob_start();

// Constants
define('APP_NAME', 'Sistem Informasi Toko Outdoor');
define('APP_VERSION', '1.0');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/toko-outdoor/');

?>
