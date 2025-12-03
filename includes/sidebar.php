<?php
/**
 * Sidebar Navigation
 */
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏕️ Toko Outdoor</h2>
        <p><?php echo ucfirst($_SESSION['role']); ?></p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                📊 Dashboard
            </a>
        </li>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="user_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'user_management.php' ? 'active' : ''; ?>">
                    👥 Manajemen User
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (in_array($_SESSION['role'], ['admin', 'kasir'])): ?>
            <li>
                <a href="produk_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'produk_management.php' ? 'active' : ''; ?>">
                    📦 Manajemen Produk
                </a>
            </li>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] === 'kasir'): ?>
            <li>
                <a href="transaksi_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'transaksi_management.php' ? 'active' : ''; ?>">
                    💳 Transaksi
                </a>
            </li>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] === 'owner'): ?>
            <li>
                <a href="laporan.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'laporan.php' ? 'active' : ''; ?>">
                    📈 Laporan
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">🚪 Logout</a>
    </div>
</div>
