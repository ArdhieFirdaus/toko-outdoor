<?php
/**
 * Transaksi Management - CRUD Transaksi
 * Hanya bisa diakses oleh Kasir
 */
require_once 'Config/koneksi.php';
require_once 'helper.php';

// Cek login dan role
require_role('kasir');

$message = get_message();
$transaksi_list = get_all_transaksi();
$produk_list = get_all_produk();

// Proses Create Transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    // CREATE NEW TRANSACTION
    if ($action === 'create') {
        // Mulai transaction
        $koneksi->begin_transaction();
        
        try {
            // Insert transaksi header
            $total_harga = 0;
            $detail_items = isset($_POST['items']) ? $_POST['items'] : [];
            
            // Validasi items
            if (empty($detail_items)) {
                throw new Exception('Minimal harus ada 1 item dalam transaksi');
            }
            
            // Hitung total dan validasi stok
            foreach ($detail_items as $item) {
                $id_produk = intval($item['id_produk'] ?? 0);
                $jumlah = intval($item['jumlah'] ?? 0);
                
                if ($id_produk <= 0 || $jumlah <= 0) {
                    continue;
                }
                
                // Check stok
                if (!check_stok($id_produk, $jumlah)) {
                    throw new Exception('Stok produk tidak cukup');
                }
                
                // Get harga produk
                $produk = get_produk_by_id($id_produk);
                $subtotal = $produk['harga'] * $jumlah;
                $total_harga += $subtotal;
            }
            
            if ($total_harga <= 0) {
                throw new Exception('Total harga harus lebih dari 0');
            }
            
            // Insert transaksi
            $insert_transaksi = query("INSERT INTO transaksi (id_user, total_harga, status) 
                                       VALUES (" . $_SESSION['id_user'] . ", " . $total_harga . ", 'selesai')");
            
            if (!$insert_transaksi) {
                throw new Exception('Gagal menambah transaksi');
            }
            
            $id_transaksi = $koneksi->insert_id;
            
            // Insert detail transaksi dan update stok
            foreach ($detail_items as $item) {
                $id_produk = intval($item['id_produk'] ?? 0);
                $jumlah = intval($item['jumlah'] ?? 0);
                
                if ($id_produk <= 0 || $jumlah <= 0) {
                    continue;
                }
                
                $produk = get_produk_by_id($id_produk);
                $harga_satuan = $produk['harga'];
                $subtotal = $harga_satuan * $jumlah;
                
                // Insert detail
                query("INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) 
                       VALUES (" . $id_transaksi . ", " . $id_produk . ", " . $jumlah . ", " . $harga_satuan . ", " . $subtotal . ")");
                
                // Update stok
                update_stok($id_produk, $jumlah, 'minus');
            }
            
            // Commit transaction
            $koneksi->commit();
            
            redirect_with_message('transaksi_management.php', 'Transaksi berhasil dibuat', 'success');
            
        } catch (Exception $e) {
            // Rollback transaction
            $koneksi->rollback();
            redirect_with_message('transaksi_management.php', 'Error: ' . $e->getMessage(), 'danger');
        }
    }
    
    // DELETE TRANSACTION
    elseif ($action === 'delete') {
        $id_transaksi = intval($_POST['id_transaksi'] ?? 0);
        
        if ($id_transaksi > 0) {
            // Get detail transaksi untuk rollback stok
            $details = get_detail_transaksi($id_transaksi);
            
            // Mulai transaction
            $koneksi->begin_transaction();
            
            try {
                // Delete transaksi (akan auto delete detail karena FK)
                if (!query("DELETE FROM transaksi WHERE id_transaksi = " . $id_transaksi)) {
                    throw new Exception('Gagal menghapus transaksi');
                }
                
                // Rollback stok
                foreach ($details as $detail) {
                    update_stok($detail['id_produk'], $detail['jumlah'], 'plus');
                }
                
                // Commit
                $koneksi->commit();
                
                redirect_with_message('transaksi_management.php', 'Transaksi berhasil dihapus', 'success');
                
            } catch (Exception $e) {
                $koneksi->rollback();
                redirect_with_message('transaksi_management.php', 'Error: ' . $e->getMessage(), 'danger');
            }
        } else {
            redirect_with_message('transaksi_management.php', 'Transaksi tidak valid', 'danger');
        }
    }
}

// Refresh transaksi list
$transaksi_list = get_all_transaksi();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Management - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .transaction-items {
            margin-bottom: 20px;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .item-row input,
        .item-row select {
            width: 100%;
        }
        
        .btn-remove-item {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-remove-item:hover {
            background-color: #c0392b;
        }
        
        .transaction-summary {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .total-display {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary-color);
            text-align: right;
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
                <h1>💳 Manajemen Transaksi</h1>
                <div class="user-info">
                    <span class="user-name">
                        Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
                    </span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            
            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                    <span class="close-alert" onclick="this.parentElement.style.display='none';">&times;</span>
                </div>
            <?php endif; ?>
            
            <!-- Button Group -->
            <div class="btn-group">
                <button class="btn btn-primary" onclick="openModal('addTransaksiModal')">+ Buat Transaksi</button>
            </div>
            
            <!-- Daftar Transaksi -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Transaksi</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($transaksi_list) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Transaksi</th>
                                        <th>Kasir</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($transaksi_list as $trx): ?>
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
                                            <td>
                                                <button class="btn btn-sm btn-secondary" onclick="detailTransaksi(<?php echo $trx['id_transaksi']; ?>)">Detail</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteTransaksi(<?php echo $trx['id_transaksi']; ?>)">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>Belum ada transaksi</h3>
                                <p>Klik tombol "Buat Transaksi" untuk membuat transaksi baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Buat Transaksi -->
    <div id="addTransaksiModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Buat Transaksi Baru</h2>
                <span class="close-modal" onclick="closeModal('addTransaksiModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">
                
                <div class="transaction-items">
                    <h4 style="margin-bottom: 15px;">Item Transaksi</h4>
                    <div id="itemsContainer">
                        <div class="item-row">
                            <div>
                                <label>Produk</label>
                                <select name="items[0][id_produk]" class="produk-select" onchange="updateTotal()">
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($produk_list as $p): ?>
                                        <option value="<?php echo $p['id_produk']; ?>" data-harga="<?php echo $p['harga']; ?>" data-stok="<?php echo $p['stok']; ?>">
                                            <?php echo htmlspecialchars($p['nama_produk']); ?> (Stok: <?php echo $p['stok']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Harga</label>
                                <input type="text" class="harga-display" readonly>
                            </div>
                            <div>
                                <label>Jumlah</label>
                                <input type="number" name="items[0][jumlah]" min="1" value="1" onchange="updateTotal()">
                            </div>
                            <div>
                                <label>Subtotal</label>
                                <input type="text" class="subtotal-display" readonly>
                            </div>
                            <button type="button" class="btn-remove-item" onclick="removeItem(this)">Hapus</button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" onclick="addItem()" style="margin-top: 10px;">+ Tambah Item</button>
                </div>
                
                <div class="transaction-summary">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                        <div>
                            <label>Jumlah Item:</label>
                            <p style="font-size: 18px; font-weight: bold; margin: 5px 0;" id="totalItems">0</p>
                        </div>
                        <div>
                            <label>Total Harga:</label>
                            <p class="total-display" id="totalHarga">Rp 0</p>
                        </div>
                    </div>
                </div>
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addTransaksiModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Detail Transaksi -->
    <div id="detailTransaksiModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Detail Transaksi</h2>
                <span class="close-modal" onclick="closeModal('detailTransaksiModal')">&times;</span>
            </div>
            <div id="detailContent"></div>
        </div>
    </div>
    
    <!-- Modal Delete Transaksi -->
    <div id="deleteTransaksiModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Hapus Transaksi</h2>
                <span class="close-modal" onclick="closeModal('deleteTransaksiModal')">&times;</span>
            </div>
            <p>Apakah Anda yakin ingin menghapus transaksi ini? Stok produk akan dikembalikan.</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_transaksi" id="deleteTransaksiId">
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteTransaksiModal')">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let itemCount = 1;
        
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Close modal ketika klik di luar
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
        
        // Add item
        function addItem() {
            const container = document.getElementById('itemsContainer');
            const div = document.createElement('div');
            div.className = 'item-row';
            div.innerHTML = `
                <div>
                    <select name="items[${itemCount}][id_produk]" class="produk-select" onchange="updateTotal()">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produk_list as $p): ?>
                            <option value="<?php echo $p['id_produk']; ?>" data-harga="<?php echo $p['harga']; ?>" data-stok="<?php echo $p['stok']; ?>">
                                <?php echo htmlspecialchars($p['nama_produk']); ?> (Stok: <?php echo $p['stok']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" class="harga-display" readonly>
                </div>
                <div>
                    <input type="number" name="items[${itemCount}][jumlah]" min="1" value="1" onchange="updateTotal()">
                </div>
                <div>
                    <input type="text" class="subtotal-display" readonly>
                </div>
                <button type="button" class="btn-remove-item" onclick="removeItem(this)">Hapus</button>
            `;
            container.appendChild(div);
            itemCount++;
        }
        
        // Remove item
        function removeItem(btn) {
            btn.parentElement.remove();
            updateTotal();
        }
        
        // Update total
        function updateTotal() {
            let grandTotal = 0;
            let totalItems = 0;
            
            document.querySelectorAll('.item-row').forEach((row) => {
                const select = row.querySelector('select');
                const hargaDisplay = row.querySelector('.harga-display');
                const jumlahInput = row.querySelector('input[type="number"]');
                const subtotalDisplay = row.querySelector('.subtotal-display');
                
                if (select.value) {
                    const option = select.options[select.selectedIndex];
                    const harga = parseInt(option.dataset.harga);
                    const jumlah = parseInt(jumlahInput.value) || 0;
                    const subtotal = harga * jumlah;
                    
                    hargaDisplay.value = formatCurrency(harga);
                    subtotalDisplay.value = formatCurrency(subtotal);
                    
                    grandTotal += subtotal;
                    totalItems += jumlah;
                } else {
                    hargaDisplay.value = '';
                    subtotalDisplay.value = '';
                }
            });
            
            document.getElementById('totalItems').textContent = totalItems;
            document.getElementById('totalHarga').textContent = formatCurrency(grandTotal);
        }
        
        // Format currency
        function formatCurrency(value) {
            return 'Rp ' + value.toLocaleString('id-ID');
        }
        
        // Detail transaksi
        function detailTransaksi(transactionId) {
            fetch('api/get_transaksi.php?id=' + transactionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const t = data.transaksi;
                        let html = `
                            <div style="margin-bottom: 15px;">
                                <p><strong>ID Transaksi:</strong> #${String(t.id_transaksi).padStart(5, '0')}</p>
                                <p><strong>Kasir:</strong> ${t.nama_lengkap}</p>
                                <p><strong>Status:</strong> ${t.status}</p>
                                <p><strong>Tanggal:</strong> ${t.created_at}</p>
                            </div>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background-color: #ecf0f1;">
                                        <th style="padding: 10px; border: 1px solid #bdc3c7; text-align: left;">Produk</th>
                                        <th style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">Harga</th>
                                        <th style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">Jumlah</th>
                                        <th style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        
                        data.details.forEach(detail => {
                            html += `
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #bdc3c7;">${detail.nama_produk}</td>
                                    <td style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">Rp ${parseInt(detail.harga_satuan).toLocaleString('id-ID')}</td>
                                    <td style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">${detail.jumlah}</td>
                                    <td style="padding: 10px; border: 1px solid #bdc3c7; text-align: right;">Rp ${parseInt(detail.subtotal).toLocaleString('id-ID')}</td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                </tbody>
                            </table>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #bdc3c7; text-align: right;">
                                <h3 style="margin: 0; color: var(--secondary-color);">Total: Rp ${parseInt(t.total_harga).toLocaleString('id-ID')}</h3>
                            </div>
                        `;
                        
                        document.getElementById('detailContent').innerHTML = html;
                        openModal('detailTransaksiModal');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Delete transaksi
        function deleteTransaksi(transactionId) {
            document.getElementById('deleteTransaksiId').value = transactionId;
            openModal('deleteTransaksiModal');
        }
    </script>
</body>
</html>
