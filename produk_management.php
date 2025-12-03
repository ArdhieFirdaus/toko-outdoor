<?php
/**
 * Produk Management - CRUD Produk
 * Bisa diakses oleh Admin dan Kasir
 */
require_once 'Config/koneksi.php';
require_once 'helper.php';

// Cek login dan role
require_role(['admin', 'kasir']);

$message = get_message();
$produk_list = get_all_produk();

// Proses Create Produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    // CREATE
    if ($action === 'create') {
        $nama_produk = sanitize($_POST['nama_produk'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $harga = floatval($_POST['harga'] ?? 0);
        $stok = intval($_POST['stok'] ?? 0);
        
        // Validasi
        $errors = [];
        
        if (empty($nama_produk)) {
            $errors[] = 'Nama produk tidak boleh kosong';
        }
        
        if (empty($kategori)) {
            $errors[] = 'Kategori tidak boleh kosong';
        }
        
        if ($harga <= 0) {
            $errors[] = 'Harga harus lebih dari 0';
        }
        
        if ($stok < 0) {
            $errors[] = 'Stok tidak boleh negatif';
        }
        
        if (empty($errors)) {
            // Insert ke database
            $insert = query("INSERT INTO produk (nama_produk, kategori, deskripsi, harga, stok, id_user) 
                           VALUES ('" . escape($nama_produk) . "', '" . escape($kategori) . "', 
                                   '" . escape($deskripsi) . "', " . $harga . ", " . $stok . ", " . $_SESSION['id_user'] . ")");
            
            if ($insert) {
                redirect_with_message('produk_management.php', 'Produk berhasil ditambahkan', 'success');
            } else {
                redirect_with_message('produk_management.php', 'Gagal menambah produk', 'danger');
            }
        } else {
            $_SESSION['message'] = implode('<br>', $errors);
            $_SESSION['message_type'] = 'danger';
        }
    }
    
    // UPDATE
    elseif ($action === 'update') {
        $id_produk = intval($_POST['id_produk'] ?? 0);
        $nama_produk = sanitize($_POST['nama_produk'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $harga = floatval($_POST['harga'] ?? 0);
        $stok = intval($_POST['stok'] ?? 0);
        
        // Validasi
        $errors = [];
        
        if ($id_produk <= 0) {
            $errors[] = 'Produk tidak valid';
        }
        
        if (empty($nama_produk)) {
            $errors[] = 'Nama produk tidak boleh kosong';
        }
        
        if (empty($kategori)) {
            $errors[] = 'Kategori tidak boleh kosong';
        }
        
        if ($harga <= 0) {
            $errors[] = 'Harga harus lebih dari 0';
        }
        
        if ($stok < 0) {
            $errors[] = 'Stok tidak boleh negatif';
        }
        
        if (empty($errors)) {
            // Update
            $update_query = "UPDATE produk SET 
                           nama_produk = '" . escape($nama_produk) . "', 
                           kategori = '" . escape($kategori) . "', 
                           deskripsi = '" . escape($deskripsi) . "', 
                           harga = " . $harga . ", 
                           stok = " . $stok . " 
                           WHERE id_produk = " . $id_produk;
            
            if (query($update_query)) {
                redirect_with_message('produk_management.php', 'Produk berhasil diperbarui', 'success');
            } else {
                redirect_with_message('produk_management.php', 'Gagal memperbarui produk', 'danger');
            }
        } else {
            $_SESSION['message'] = implode('<br>', $errors);
            $_SESSION['message_type'] = 'danger';
        }
    }
    
    // DELETE
    elseif ($action === 'delete') {
        $id_produk = intval($_POST['id_produk'] ?? 0);
        
        if ($id_produk > 0) {
            if (query("DELETE FROM produk WHERE id_produk = " . $id_produk)) {
                redirect_with_message('produk_management.php', 'Produk berhasil dihapus', 'success');
            } else {
                redirect_with_message('produk_management.php', 'Gagal menghapus produk', 'danger');
            }
        } else {
            redirect_with_message('produk_management.php', 'Produk tidak valid', 'danger');
        }
    }
}

// Refresh produk list
$produk_list = get_all_produk();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Management - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>📦 Manajemen Produk</h1>
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
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="openModal('addProdukModal')">+ Tambah Produk</button>
                </div>
            <?php endif; ?>
            
            <!-- Table Produk -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Produk</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($produk_list) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Deskripsi</th>
                                        <th>Dibuat</th>
                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($produk_list as $p): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                                            <td><?php echo htmlspecialchars($p['kategori']); ?></td>
                                            <td><?php echo format_currency($p['harga']); ?></td>
                                            <td>
                                                <span style="padding: 5px 10px; border-radius: 5px; background-color: 
                                                    <?php echo $p['stok'] > 10 ? '#27ae60' : ($p['stok'] > 0 ? '#f39c12' : '#e74c3c'); ?>; 
                                                    color: white;">
                                                    <?php echo $p['stok']; ?> unit
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($p['deskripsi'] ?? '-', 0, 50)); ?></td>
                                            <td><?php echo format_date_short($p['created_at']); ?></td>
                                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="editProduk(<?php echo $p['id_produk']; ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduk(<?php echo $p['id_produk']; ?>)">Hapus</button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>Belum ada produk</h3>
                                <p>Klik tombol "Tambah Produk" untuk membuat produk baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Produk -->
    <div id="addProdukModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Produk Baru</h2>
                <span class="close-modal" onclick="closeModal('addProdukModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" placeholder="Contoh: Tenda, Sleeping Bag, Tas" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga" min="0" step="1000" placeholder="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" placeholder="Deskripsi produk"></textarea>
                </div>
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProdukModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Edit Produk -->
    <div id="editProdukModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Produk</h2>
                <span class="close-modal" onclick="closeModal('editProdukModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_produk" id="editProdukId">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" id="editNamaProduk" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" id="editKategori" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga" id="editHarga" min="0" step="1000" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" id="editStok" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi"></textarea>
                </div>
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProdukModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Delete Produk -->
    <div id="deleteProdukModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Hapus Produk</h2>
                <span class="close-modal" onclick="closeModal('deleteProdukModal')">&times;</span>
            </div>
            <p>Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_produk" id="deleteProdukId">
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteProdukModal')">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
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
        
        // Edit produk
        function editProduk(produkId) {
            // Get produk data via fetch
            fetch('api/get_produk.php?id=' + produkId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const produk = data.produk;
                        document.getElementById('editProdukId').value = produk.id_produk;
                        document.getElementById('editNamaProduk').value = produk.nama_produk;
                        document.getElementById('editKategori').value = produk.kategori;
                        document.getElementById('editHarga').value = produk.harga;
                        document.getElementById('editStok').value = produk.stok;
                        document.getElementById('editDeskripsi').value = produk.deskripsi;
                        openModal('editProdukModal');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Delete produk
        function deleteProduk(produkId) {
            document.getElementById('deleteProdukId').value = produkId;
            openModal('deleteProdukModal');
        }
    </script>
</body>
</html>
