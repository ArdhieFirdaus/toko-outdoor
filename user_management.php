<?php
/**
 * User Management - CRUD User
 * Hanya bisa diakses oleh Admin
 */
require_once 'Config/koneksi.php';
require_once 'helper.php';

// Cek login dan role
require_role('admin');

$message = get_message();
$users = get_users_by_role();

// Proses Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    // CREATE
    if ($action === 'create') {
        $username = sanitize($_POST['username'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        $confirm_password = sanitize($_POST['confirm_password'] ?? '');
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $no_telp = sanitize($_POST['no_telp'] ?? '');
        $role = sanitize($_POST['role'] ?? '');
        
        // Validasi
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username tidak boleh kosong';
        } elseif (!is_valid_username($username)) {
            $errors[] = 'Username harus minimal 3 karakter dan hanya alphanumeric + underscore';
        } elseif (username_exists($username)) {
            $errors[] = 'Username sudah terdaftar';
        }
        
        if (empty($password)) {
            $errors[] = 'Password tidak boleh kosong';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }
        
        if (empty($nama_lengkap)) {
            $errors[] = 'Nama lengkap tidak boleh kosong';
        }
        
        if (!empty($email) && !is_valid_email($email)) {
            $errors[] = 'Email tidak valid';
        }
        
        if (!in_array($role, ['admin', 'kasir', 'owner'])) {
            $errors[] = 'Role tidak valid';
        }
        
        if (empty($errors)) {
            // Hash password
            $password_hash = hash('sha256', $password);
            
            // Insert ke database
            $email_esc = escape($email);
            $no_telp_esc = escape($no_telp);
            
            $insert = query("INSERT INTO user (username, password, nama_lengkap, email, no_telp, role) 
                           VALUES ('" . escape($username) . "', '" . $password_hash . "', 
                                   '" . escape($nama_lengkap) . "', '" . $email_esc . "', 
                                   '" . $no_telp_esc . "', '" . escape($role) . "')");
            
            if ($insert) {
                redirect_with_message('user_management.php', 'User berhasil ditambahkan', 'success');
            } else {
                redirect_with_message('user_management.php', 'Gagal menambah user', 'danger');
            }
        } else {
            $_SESSION['message'] = implode('<br>', $errors);
            $_SESSION['message_type'] = 'danger';
        }
    }
    
    // UPDATE
    elseif ($action === 'update') {
        $id_user = intval($_POST['id_user'] ?? 0);
        $username = sanitize($_POST['username'] ?? '');
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $no_telp = sanitize($_POST['no_telp'] ?? '');
        $role = sanitize($_POST['role'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        
        // Validasi
        $errors = [];
        
        if ($id_user <= 0) {
            $errors[] = 'User tidak valid';
        }
        
        if (empty($username)) {
            $errors[] = 'Username tidak boleh kosong';
        } elseif (username_exists($username, $id_user)) {
            $errors[] = 'Username sudah terdaftar';
        }
        
        if (empty($nama_lengkap)) {
            $errors[] = 'Nama lengkap tidak boleh kosong';
        }
        
        if (!empty($email) && !is_valid_email($email)) {
            $errors[] = 'Email tidak valid';
        }
        
        if (!in_array($role, ['admin', 'kasir', 'owner'])) {
            $errors[] = 'Role tidak valid';
        }
        
        if (empty($errors)) {
            // Update
            $email_esc = escape($email);
            $no_telp_esc = escape($no_telp);
            
            $update_query = "UPDATE user SET username = '" . escape($username) . "', 
                           nama_lengkap = '" . escape($nama_lengkap) . "', 
                           email = '" . $email_esc . "', 
                           no_telp = '" . $no_telp_esc . "', 
                           role = '" . escape($role) . "'";
            
            // Jika password diisi, update password
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    redirect_with_message('user_management.php', 'Password minimal 6 karakter', 'danger');
                    exit();
                }
                $password_hash = hash('sha256', $password);
                $update_query .= ", password = '" . $password_hash . "'";
            }
            
            $update_query .= " WHERE id_user = " . $id_user;
            
            if (query($update_query)) {
                redirect_with_message('user_management.php', 'User berhasil diperbarui', 'success');
            } else {
                redirect_with_message('user_management.php', 'Gagal memperbarui user', 'danger');
            }
        } else {
            $_SESSION['message'] = implode('<br>', $errors);
            $_SESSION['message_type'] = 'danger';
        }
    }
    
    // DELETE
    elseif ($action === 'delete') {
        $id_user = intval($_POST['id_user'] ?? 0);
        
        if ($id_user > 0) {
            if (query("DELETE FROM user WHERE id_user = " . $id_user)) {
                redirect_with_message('user_management.php', 'User berhasil dihapus', 'success');
            } else {
                redirect_with_message('user_management.php', 'Gagal menghapus user', 'danger');
            }
        } else {
            redirect_with_message('user_management.php', 'User tidak valid', 'danger');
        }
    }
}

// Refresh users list
$users = get_users_by_role();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Sistem Informasi Toko Outdoor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>👥 Manajemen User</h1>
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
                <button class="btn btn-primary" onclick="openModal('addUserModal')">+ Tambah User</button>
            </div>
            
            <!-- Table Users -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar User</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($users) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>No. Telp</th>
                                        <th>Role</th>
                                        <th>Tgl Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($user['no_telp'] ?? '-'); ?></td>
                                            <td>
                                                <span class="btn btn-sm" style="background-color: 
                                                    <?php echo $user['role'] === 'admin' ? '#e74c3c' : ($user['role'] === 'kasir' ? '#3498db' : '#27ae60'); ?>; 
                                                    color: white; border: none; cursor: default;">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($user['created_at']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo $user['id_user']; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id_user']; ?>)">Hapus</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>Belum ada user</h3>
                                <p>Klik tombol "Tambah User" untuk membuat user baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah User -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah User Baru</h2>
                <span class="close-modal" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Minimal 3 karakter alphanumeric" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="confirm_password" placeholder="Ulangi password" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email (opsional)">
                </div>
                
                <div class="form-group">
                    <label>No. Telp</label>
                    <input type="tel" name="no_telp" placeholder="No. telp (opsional)">
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Edit User -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close-modal" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_user" id="editUserId">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="editUsername" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="editNamaLengkap" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail">
                </div>
                
                <div class="form-group">
                    <label>No. Telp</label>
                    <input type="tel" name="no_telp" id="editNoTelp">
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="editRole" required>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter">
                </div>
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Delete User -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Hapus User</h2>
                <span class="close-modal" onclick="closeModal('deleteUserModal')">&times;</span>
            </div>
            <p>Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_user" id="deleteUserId">
                
                <div class="form-button">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Batal</button>
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
        
        // Edit user
        function editUser(userId) {
            // Get user data via fetch
            fetch('api/get_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('editUserId').value = user.id_user;
                        document.getElementById('editUsername').value = user.username;
                        document.getElementById('editNamaLengkap').value = user.nama_lengkap;
                        document.getElementById('editEmail').value = user.email;
                        document.getElementById('editNoTelp').value = user.no_telp;
                        document.getElementById('editRole').value = user.role;
                        openModal('editUserModal');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Delete user
        function deleteUser(userId) {
            document.getElementById('deleteUserId').value = userId;
            openModal('deleteUserModal');
        }
    </script>
</body>
</html>
