<?php
/**
 * Halaman Logout
 */
session_start();

// Hapus session
session_destroy();

// Redirect ke login
header('Location: login.php?message=Anda berhasil logout');
exit();
?>
