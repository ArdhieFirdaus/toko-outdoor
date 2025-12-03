<?php
/**
 * API - Get Transaksi Data
 */
require_once '../Config/koneksi.php';
require_once '../helper.php';

header('Content-Type: application/json');

// Cek login
require_login();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $transaksi = get_transaksi_by_id($id);
    $details = get_detail_transaksi($id);
    
    if ($transaksi) {
        $result = query("SELECT u.nama_lengkap FROM user u WHERE u.id_user = " . $transaksi['id_user']);
        $user = fetch_assoc($result);
        
        $transaksi['nama_lengkap'] = $user['nama_lengkap'];
        
        echo json_encode([
            'success' => true,
            'transaksi' => $transaksi,
            'details' => $details
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID transaksi tidak diberikan'
    ]);
}
?>
