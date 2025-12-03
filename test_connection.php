<?php
/**
 * Database Connection Test
 * Jalankan file ini untuk memverifikasi koneksi database
 */
require_once 'Config/koneksi.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Database Connection Test</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; }";
echo ".success { background: #d5f4e6; padding: 15px; border-radius: 5px; color: #27ae60; margin: 10px 0; }";
echo ".error { background: #fadbd8; padding: 15px; border-radius: 5px; color: #c0392b; margin: 10px 0; }";
echo ".warning { background: #fce4b6; padding: 15px; border-radius: 5px; color: #d68910; margin: 10px 0; }";
echo "table { border-collapse: collapse; width: 100%; margin: 20px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }";
echo "th { background: #2c3e50; color: white; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 Database Connection Test</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    if ($koneksi->connect_error) {
        echo "<div class='error'>❌ Koneksi gagal: " . $koneksi->connect_error . "</div>";
    } else {
        echo "<div class='success'>✅ Koneksi berhasil</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

// Test 2: Database Info
echo "<h2>Test 2: Database Information</h2>";
echo "<table>";
echo "<tr><th>Parameter</th><th>Value</th></tr>";
echo "<tr><td>Host</td><td>" . DB_HOST . "</td></tr>";
echo "<tr><td>Database</td><td>" . DB_NAME . "</td></tr>";
echo "<tr><td>User</td><td>" . DB_USER . "</td></tr>";
echo "<tr><td>Version</td><td>" . $koneksi->server_info . "</td></tr>";
echo "</table>";

// Test 3: Tables Check
echo "<h2>Test 3: Tables Check</h2>";
$tables = ['user', 'produk', 'transaksi', 'detail_transaksi'];
$result = query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'");
$existing_tables = [];
while ($row = fetch_assoc($result)) {
    $existing_tables[] = $row['TABLE_NAME'];
}

echo "<table>";
echo "<tr><th>Table Name</th><th>Status</th></tr>";
foreach ($tables as $table) {
    $status = in_array($table, $existing_tables) ? '✅ Exists' : '❌ Missing';
    $class = in_array($table, $existing_tables) ? 'success' : 'error';
    echo "<tr>";
    echo "<td>" . $table . "</td>";
    echo "<td style='color: " . (in_array($table, $existing_tables) ? '#27ae60' : '#c0392b') . "'>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 4: Data Count
echo "<h2>Test 4: Data Count</h2>";
echo "<table>";
echo "<tr><th>Table</th><th>Record Count</th></tr>";

foreach ($tables as $table) {
    $result = query("SELECT COUNT(*) as count FROM " . $table);
    $row = fetch_assoc($result);
    echo "<tr>";
    echo "<td>" . $table . "</td>";
    echo "<td>" . $row['count'] . " records</td>";
    echo "</tr>";
}
echo "</table>";

// Test 5: User Login Test
echo "<h2>Test 5: User Login Test</h2>";
$result = query("SELECT * FROM user WHERE username = 'admin'");
if (num_rows($result) > 0) {
    $user = fetch_assoc($result);
    echo "<div class='success'>✅ Admin user found</div>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Username</td><td>" . $user['username'] . "</td></tr>";
    echo "<tr><td>Nama Lengkap</td><td>" . $user['nama_lengkap'] . "</td></tr>";
    echo "<tr><td>Role</td><td>" . $user['role'] . "</td></tr>";
    echo "</table>";
} else {
    echo "<div class='error'>❌ Admin user not found</div>";
}

echo "<hr>";
echo "<p><strong>Next Step:</strong> Buka <a href='login.php'>login.php</a> untuk mulai menggunakan sistem</p>";
echo "</body>";
echo "</html>";
?>
