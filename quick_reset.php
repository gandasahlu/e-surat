<?php
// quick_reset.php - Reset lengkap dengan password hash yang benar

// Koneksi tanpa database dulu
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Reset Database Lengkap</h3>";
    
    // Hapus dan buat ulang database
    $pdo->exec("DROP DATABASE IF EXISTS esurat_desa");
    $pdo->exec("CREATE DATABASE esurat_desa");
    $pdo->exec("USE esurat_desa");
    
    echo "Database dibuat ulang.<br>";
    
} catch(PDOException $e) {
    die("Error koneksi: " . $e->getMessage());
}

// SQL untuk membuat tabel
$sql = [];

// Tabel penduduk
$sql[] = "CREATE TABLE penduduk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NOT NULL,
    no_telp VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabel admin
$sql[] = "CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabel kepala_desa
$sql[] = "CREATE TABLE kepala_desa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) DEFAULT 'Kepala Desa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabel jenis_surat
$sql[] = "CREATE TABLE jenis_surat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_surat VARCHAR(10) UNIQUE NOT NULL,
    nama_surat VARCHAR(100) NOT NULL,
    template TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabel pengajuan_surat
$sql[] = "CREATE TABLE pengajuan_surat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    penduduk_id INT NOT NULL,
    jenis_surat_id INT NOT NULL,
    keperluan TEXT NOT NULL,
    berkas_pendukung VARCHAR(255),
    status ENUM('menunggu', 'diproses', 'disetujui', 'ditolak', 'siap_ambil') DEFAULT 'menunggu',
    catatan_admin TEXT,
    nomor_surat VARCHAR(50),
    tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_verifikasi TIMESTAMP NULL,
    tanggal_ttd TIMESTAMP NULL,
    FOREIGN KEY (penduduk_id) REFERENCES penduduk(id),
    FOREIGN KEY (jenis_surat_id) REFERENCES jenis_surat(id)
)";

// Eksekusi pembuatan tabel
foreach ($sql as $query) {
    try {
        $pdo->exec($query);
        echo "Tabel dibuat: OK<br>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

// GENERATE HASH PASSWORD YANG BENAR
$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$kepala_hash = password_hash('kepala123', PASSWORD_DEFAULT);
$penduduk_hash = password_hash('123456', PASSWORD_DEFAULT);

echo "<br>Generated Hashes:<br>";
echo "Admin: " . substr($admin_hash, 0, 30) . "...<br>";
echo "Kepala Desa: " . substr($kepala_hash, 0, 30) . "...<br>";

// Insert data dengan HASH yang benar
try {
    // Insert admin
    $stmt = $pdo->prepare("INSERT INTO admin (username, password, nama) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $admin_hash, 'Administrator Desa']);
    echo "✓ Admin inserted<br>";
    
    // Insert kepala desa
    $stmt = $pdo->prepare("INSERT INTO kepala_desa (username, password, nama) VALUES (?, ?, ?)");
    $stmt->execute(['kepaladesa', $kepala_hash, 'Bapak Kepala Desa']);
    echo "✓ Kepala Desa inserted<br>";
    
    // Insert jenis surat
    $jenis_surat = [
        ['S-001', 'Surat Keterangan Domisili'],
        ['S-002', 'Surat Keterangan Tidak Mampu (SKTM)'],
        ['S-003', 'Surat Keterangan Usaha'],
        ['S-004', 'Surat Keterangan Kelahiran'],
        ['S-005', 'Surat Keterangan Kematian']
    ];
    
    foreach ($jenis_surat as $surat) {
        $stmt = $pdo->prepare("INSERT INTO jenis_surat (kode_surat, nama_surat) VALUES (?, ?)");
        $stmt->execute($surat);
    }
    echo "✓ Jenis surat inserted<br>";
    
    // Insert contoh penduduk
    $stmt = $pdo->prepare("INSERT INTO penduduk (nik, nama, tempat_lahir, tanggal_lahir, alamat, no_telp, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        '1234567890123456',
        'Budi Santoso',
        'Jakarta',
        '1990-01-15',
        'Jl. Merdeka No. 123',
        '081234567890',
        $penduduk_hash
    ]);
    echo "✓ Contoh penduduk inserted<br>";
    
    // TEST VERIFIKASI
    echo "<hr><h4>Testing Password Verification:</h4>";
    
    // Test admin
    $stmt = $pdo->query("SELECT password FROM admin WHERE username = 'admin'");
    $test_admin = $stmt->fetch();
    if (password_verify('admin123', $test_admin['password'])) {
        echo "✓ Admin: password_verify('admin123') = TRUE<br>";
    } else {
        echo "✗ Admin: password_verify('admin123') = FALSE<br>";
    }
    
    // Test kepala desa
    $stmt = $pdo->query("SELECT password FROM kepala_desa WHERE username = 'kepaladesa'");
    $test_kepala = $stmt->fetch();
    if (password_verify('kepala123', $test_kepala['password'])) {
        echo "✓ Kepala Desa: password_verify('kepala123') = TRUE<br>";
    } else {
        echo "✗ Kepala Desa: password_verify('kepala123') = FALSE<br>";
    }
    
    echo "<hr>";
    echo "<h3 style='color:green;'>RESET BERHASIL!</h3>";
    echo "<p>Sekarang login dengan:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
    echo "<li><strong>Kepala Desa:</strong> username: <code>kepaladesa</code>, password: <code>kepala123</code></li>";
    echo "<li><strong>Penduduk contoh:</strong> NIK: <code>1234567890123456</code>, password: <code>123456</code></li>";
    echo "</ul>";
    echo "<p><a href='login.php' class='btn btn-success'>Login Sekarang</a></p>";
    
} catch (PDOException $e) {
    echo "Error insert: " . $e->getMessage() . "<br>";
}

echo "<hr><p style='color:red;'><strong>HAPUS FILE INI SETELAH DIGUNAKAN!</strong></p>";
?>