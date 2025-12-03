# Sistem Informasi Toko Outdoor

Sistem manajemen toko outdoor berbasis web dengan fitur CRUD lengkap, role-based access control, dan dashboard analytics.

## 📋 Fitur Utama

### 1. **Role-Based Access Control**

- **Admin** → Akses penuh ke semua fitur
- **Kasir** → Manajemen produk dan transaksi
- **Owner** → Akses read-only ke laporan penjualan

### 2. **CRUD Operations (3 Modul)**

#### a. **User Management (Admin Only)**

- ✅ Create user dengan validasi username & password
- ✅ Read/View semua user
- ✅ Update user profile & role
- ✅ Delete user dengan cascade ke transaksi

#### b. **Product Management (Admin & Kasir)**

- ✅ Create produk dengan kategori, harga, stok
- ✅ Read/View daftar produk
- ✅ Update data produk
- ✅ Delete produk (admin only)

#### c. **Transaction Management (Kasir Only)**

- ✅ Create transaksi dengan multiple items
- ✅ Read/View daftar & detail transaksi
- ✅ Kalkulasi otomatis total harga
- ✅ Delete transaksi dengan rollback stok

### 3. **Fitur Tambahan**

- 🔐 Login & Session Management dengan role-based redirect
- 📊 Dashboard dengan statistik real-time
- 📈 Laporan penjualan untuk owner dengan chart
- 🎨 UI responsif dengan CSS modern
- ⚡ Modal dialog untuk CRUD operations
- 💾 Database transaction untuk konsistensi data

## 🗂️ Struktur File

```
toko-outdoor/
├── index.php                          # Entry point
├── login.php                          # Halaman login
├── logout.php                         # Logout & session destroy
├── dashboard.php                      # Dashboard utama
├── user_management.php                # CRUD User
├── produk_management.php              # CRUD Produk
├── transaksi_management.php           # CRUD Transaksi
├── laporan.php                        # Laporan penjualan (Owner)
├── helper.php                         # Fungsi-fungsi umum
│
├── Config/
│   └── koneksi.php                    # Database connection
│
├── Database/
│   └── database.sql                   # Schema & data default
│
├── includes/
│   └── sidebar.php                    # Navigation sidebar
│
├── api/
│   ├── get_user.php                   # API get user
│   ├── get_produk.php                 # API get produk
│   └── get_transaksi.php              # API get transaksi
│
└── assets/
    ├── css/
    │   └── style.css                  # Styling utama
    └── js/
        └── (untuk future JS files)
```

## 🚀 Installation & Setup

### 1. **Persiapan Database**

```sql
-- Buka phpMyAdmin di http://localhost/phpmyadmin
-- Buat database baru bernama "toko_outdoor"
-- Import file Database/database.sql
```

Atau jalankan query secara manual:

```bash
mysql -u root -p < Database/database.sql
```

### 2. **Konfigurasi Database**

Edit file `Config/koneksi.php` jika perlu menyesuaikan:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Ubah jika ada password
define('DB_NAME', 'toko_outdoor');
```

### 3. **Jalankan Aplikasi**

```
URL: http://localhost/toko-outdoor/
atau
URL: http://localhost/toko-outdoor/login.php
```

## 👤 Demo Login

### Admin

- **Username:** `admin`
- **Password:** `admin123`

### Kasir

- **Username:** `kasir1`
- **Password:** `kasir123`

### Owner

- **Username:** `owner`
- **Password:** `owner123`

## 📊 Database Schema

### Tabel `user`

```
id_user (INT, PK, AI)
username (VARCHAR, UNIQUE)
password (VARCHAR, SHA256)
role (ENUM: admin, kasir, owner)
nama_lengkap (VARCHAR)
email (VARCHAR)
no_telp (VARCHAR)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Tabel `produk`

```
id_produk (INT, PK, AI)
nama_produk (VARCHAR)
kategori (VARCHAR)
deskripsi (TEXT)
harga (DECIMAL)
stok (INT)
id_user (INT, FK)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Tabel `transaksi`

```
id_transaksi (INT, PK, AI)
id_user (INT, FK)
total_harga (DECIMAL)
status (ENUM: pending, selesai, batal)
keterangan (TEXT)
created_at (TIMESTAMP)
```

### Tabel `detail_transaksi`

```
id_detail (INT, PK, AI)
id_transaksi (INT, FK)
id_produk (INT, FK)
jumlah (INT)
harga_satuan (DECIMAL)
subtotal (DECIMAL)
```

## 🔐 Security Features

- ✅ Session-based authentication
- ✅ Password hashing dengan SHA256
- ✅ SQL escape untuk prevent injection
- ✅ Role-based access control (RBAC)
- ✅ CSRF protection melalui session
- ✅ Input validation & sanitization

## 🎨 UI/UX Features

### Layout

- Sidebar navigation fixed
- Main content responsive
- Mobile-friendly design

### Components

- Modal dialog untuk CRUD
- Alert notifications
- Data table dengan sorting
- Empty state messages
- Loading indicators
- Currency formatting

### Colors

- Primary: #2c3e50 (Dark Blue)
- Secondary: #3498db (Light Blue)
- Success: #27ae60 (Green)
- Danger: #e74c3c (Red)
- Warning: #f39c12 (Orange)

## 📱 Responsive Design

```css
- Desktop: Full layout dengan sidebar
- Tablet: Responsive grid (2 columns)
- Mobile: Single column stack
```

## 🔄 Workflow Example

### Kasir membuat transaksi:

1. Login dengan akun kasir
2. Menu "Transaksi" → "Buat Transaksi Baru"
3. Pilih produk dan jumlah
4. Sistem otomatis hitung total
5. Submit → Stok berkurang, transaksi tercatat
6. Owner bisa lihat laporan penjualan

### Admin mengelola user:

1. Login dengan akun admin
2. Menu "Manajemen User" → "Tambah User"
3. Input username, password, nama, role
4. Submit → User dapat digunakan
5. Bisa edit/delete user kapan saja

## 🛠️ Troubleshooting

### Error: "Gagal terkoneksi ke database"

- Pastikan XAMPP/MySQL running
- Cek credential di `Config/koneksi.php`
- Jalankan file SQL di phpMyAdmin

### Error: "Session expired"

- Clear browser cookies
- Login kembali

### Stok tidak berkurang

- Pastikan quantity di form valid
- Periksa database transaksi

## 📝 Notes

- Semua password di-hash dengan SHA256
- Timestamp menggunakan UTC
- Currency format Indonesia (Rp)
- Date format: DD-MM-YYYY
- Database transaction untuk konsistensi stok

## 👨‍💻 Developer

Sistem ini dibuat sebagai template untuk manajemen toko outdoor.
Siap untuk dikembangkan lebih lanjut sesuai kebutuhan.

## 📞 Support

Untuk pertanyaan atau issue, hubungi developer.

---

**Versi:** 1.0  
**Last Updated:** December 3, 2025  
**Status:** ✅ Production Ready
