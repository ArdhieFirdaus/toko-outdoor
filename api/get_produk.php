<?php
/**
 * API - Get Produk Data
 */
require_once '../Config/koneksi.php';
require_once '../helper.php';

header('Content-Type: application/json');

// Cek login
require_login();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $produk = get_produk_by_id($id);
    
    if ($produk) {
        echo json_encode([
            'success' => true,
            'produk' => $produk
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID produk tidak diberikan'
    ]);
}
?>
