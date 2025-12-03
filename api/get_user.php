<?php
/**
 * API - Get User Data
 */
require_once '../Config/koneksi.php';
require_once '../helper.php';

header('Content-Type: application/json');

// Cek login
require_login();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user = get_user_info($id);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID user tidak diberikan'
    ]);
}
?>
