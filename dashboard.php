<?php
/**
 * Dashboard - Main Page
 */
require_once 'Config/koneksi.php';
require_once 'helper.php';

// Cek login
require_login();

// Get statistics
$total_user = get_total_user();
$total_produk = get_total_produk();
$total_transaksi = get_total_transaksi();
$total_penjualan = get_total_penjualan();

// Get recent transactions
$recent_transaksi = get_all_transaksi(5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>📊 Dashboard</h1>
                <div class="user-info">
                    <span class="user-name">
                        Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
                    </span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total User</div>
                        <div class="stat-value"><?php echo $total_user; ?></div>
                        <div class="stat-change">Semua pengguna sistem</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Produk</div>
                        <div class="stat-value"><?php echo $total_produk; ?></div>
                        <div class="stat-change">Produk dalam inventori</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Transaksi</div>
                        <div class="stat-value"><?php echo $total_transaksi; ?></div>
                        <div class="stat-change">Semua transaksi penjualan</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Penjualan</div>
                        <div class="stat-value"><?php echo format_currency($total_penjualan); ?></div>
                        <div class="stat-change">Nilai penjualan total</div>
                    </div>
                </div>
            <?php elseif ($_SESSION['role'] === 'kasir'): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Produk</div>
                        <div class="stat-value"><?php echo $total_produk; ?></div>
                        <div class="stat-change">Produk tersedia</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Transaksi</div>
                        <div class="stat-value"><?php echo $total_transaksi; ?></div>
                        <div class="stat-change">Transaksi penjualan</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Penjualan</div>
                        <div class="stat-value"><?php echo format_currency($total_penjualan); ?></div>
                        <div class="stat-change">Nilai penjualan total</div>
                    </div>
                </div>
            <?php elseif ($_SESSION['role'] === 'owner'): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Transaksi</div>
                        <div class="stat-value"><?php echo $total_transaksi; ?></div>
                        <div class="stat-change">Transaksi penjualan</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Penjualan</div>
                        <div class="stat-value"><?php echo format_currency($total_penjualan); ?></div>
                        <div class="stat-change">Nilai penjualan total</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Total Produk</div>
                        <div class="stat-value"><?php echo $total_produk; ?></div>
                        <div class="stat-change">Produk dalam inventori</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2>Transaksi Terbaru</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($recent_transaksi) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Transaksi</th>
                                        <th>Kasir</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($recent_transaksi as $trx): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>#<?php echo str_pad($trx['id_transaksi'], 5, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($trx['nama_lengkap']); ?></td>
                                            <td><?php echo format_currency($trx['total_harga']); ?></td>
                                            <td>
                                                <span class="btn btn-sm" style="background-color: 
                                                    <?php echo $trx['status'] === 'selesai' ? '#27ae60' : ($trx['status'] === 'pending' ? '#f39c12' : '#e74c3c'); ?>; 
                                                    color: white; border: none; cursor: default;">
                                                    <?php echo ucfirst($trx['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($trx['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>Belum ada transaksi</h3>
                                <p>Mulai dengan membuat transaksi baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
