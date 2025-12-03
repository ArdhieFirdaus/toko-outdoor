<?php
/**
 * Index Page - Redirect ke login atau dashboard
 */
require_once 'Config/koneksi.php';

session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['id_user'])) {
    header('Location: dashboard.php');
    exit();
} else {
    // Jika belum login, redirect ke login
    header('Location: login.php');
    exit();
}
?>
