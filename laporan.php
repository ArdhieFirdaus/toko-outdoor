<?php
/**
 * Laporan - Dashboard untuk Owner
 * Hanya bisa diakses oleh Owner (Read-only)
 */
require_once 'Config/koneksi.php';
require_once 'helper.php';

// Cek login dan role
require_role('owner');

// Get statistics
$total_transaksi = get_total_transaksi();
$total_produk = get_total_produk();
$total_penjualan = get_total_penjualan();

// Get transaksi grouped by month untuk chart
$monthly_sales = [];
$result = query("SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as jumlah, SUM(total_harga) as total 
                FROM transaksi 
                WHERE status = 'selesai'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY bulan DESC 
                LIMIT 12");
$monthly_sales = fetch_all($result);

// Get top produk penjualan
$top_produk = [];
$result = query("SELECT p.nama_produk, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_nilai 
                FROM detail_transaksi dt 
                JOIN produk p ON dt.id_produk = p.id_produk 
                GROUP BY dt.id_produk 
                ORDER BY total_terjual DESC 
                LIMIT 10");
$top_produk = fetch_all($result);

// Get recent transaksi
$recent_transaksi = get_all_transaksi(10);

// Get user statistics
$user_stats = [];
$result = query("SELECT role, COUNT(*) as jumlah FROM user GROUP BY role");
$user_stats = fetch_all($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            width: 100%;
            height: 400px;
            margin-bottom: 30px;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .two-column {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>📈 Laporan Penjualan</h1>
                <div class="user-info">
                    <span class="user-name">
                        Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
                    </span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            
            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">Total Penjualan</div>
                    <div class="stat-value"><?php echo format_currency($total_penjualan); ?></div>
                    <div class="stat-change">Seluruh transaksi selesai</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total Transaksi</div>
                    <div class="stat-value"><?php echo $total_transaksi; ?></div>
                    <div class="stat-change">Transaksi tercatat</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total Produk</div>
                    <div class="stat-value"><?php echo $total_produk; ?></div>
                    <div class="stat-change">Dalam inventori</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Rata-rata Transaksi</div>
                    <div class="stat-value">
                        <?php 
                        $avg = $total_transaksi > 0 ? $total_penjualan / $total_transaksi : 0;
                        echo format_currency($avg);
                        ?>
                    </div>
                    <div class="stat-change">Per transaksi</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="two-column">
                <!-- Monthly Sales Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2>Penjualan Bulanan</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- User Statistics Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2>Statistik Pengguna</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h2>10 Produk Terlaris</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($top_produk) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Total Terjual (Unit)</th>
                                        <th>Total Nilai Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($top_produk as $p): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                                            <td><?php echo $p['total_terjual']; ?> unit</td>
                                            <td><?php echo format_currency($p['total_nilai']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>Belum ada data penjualan</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2>10 Transaksi Terbaru</h2>
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Monthly Sales Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyLabels = [];
        const monthlySales = [];
        
        <?php foreach (array_reverse($monthly_sales) as $m): ?>
            monthlyLabels.push('<?php echo $m['bulan']; ?>');
            monthlySales.push(<?php echo $m['total']; ?>);
        <?php endforeach; ?>
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: monthlySales,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        
        // User Statistics Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        const userLabels = [];
        const userData = [];
        const userColors = ['#e74c3c', '#3498db', '#27ae60'];
        
        <?php foreach ($user_stats as $u): ?>
            userLabels.push('<?php echo ucfirst($u['role']); ?>');
            userData.push(<?php echo $u['jumlah']; ?>);
        <?php endforeach; ?>
        
        new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: userLabels,
                datasets: [{
                    data: userData,
                    backgroundColor: userColors,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
