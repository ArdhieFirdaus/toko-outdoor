-- Database untuk Sistem Informasi Toko Outdoor
CREATE DATABASE IF NOT EXISTS toko_outdoor;
USE toko_outdoor;

-- Tabel User (Admin, Kasir, Owner)
CREATE TABLE IF NOT EXISTS user (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir', 'owner') NOT NULL DEFAULT 'kasir',
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Produk
CREATE TABLE IF NOT EXISTS produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    nama_produk VARCHAR(150) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10, 2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    id_user INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'selesai', 'batal') DEFAULT 'selesai',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Detail Transaksi (Item dalam transaksi)
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data default user untuk testing
INSERT INTO user (username, password, role, nama_lengkap, email, no_telp) VALUES
('admin', SHA2('admin123', 256), 'admin', 'Administrator', 'admin@toko.com', '081234567890'),
('kasir', SHA2('kasir123', 256), 'kasir', 'Kasir Satu', 'kasir@toko.com', '081234567891'),
('owner', SHA2('owner123', 256), 'owner', 'Pemilik Toko', 'owner@toko.com', '081234567892');

-- Insert data produk default untuk testing
INSERT INTO produk (nama_produk, kategori, deskripsi, harga, stok, id_user) VALUES
('Tenda Outdoor 2 Orang', 'Tenda', 'Tenda berkualitas tinggi untuk camping', 350000, 20, 1),
('Sleeping Bag Premium', 'Tidur', 'Sleeping bag dengan insulation terbaik', 250000, 15, 1),
('Backpack 60L', 'Tas', 'Tas ransel besar untuk pendakian', 450000, 10, 1),
('Matras Yoga', 'Alas', 'Matras yang nyaman dan praktis', 150000, 25, 1),
('Lampu Camping LED', 'Pencahayaan', 'Lampu LED hemat energi', 120000, 30, 1),
('Sepatu Hiking', 'Alas Kaki', 'Sepatu khusus untuk hiking', 550000, 8, 1);

-- Insert data transaksi default untuk testing
INSERT INTO transaksi (id_user, total_harga, status) VALUES
(2, 600000, 'selesai'),
(2, 400000, 'selesai');

-- Insert detail transaksi
INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal) VALUES
(1, 1, 1, 350000, 350000),
(1, 2, 1, 250000, 250000),
(2, 3, 1, 450000, 450000);
