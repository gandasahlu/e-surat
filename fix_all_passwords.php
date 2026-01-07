<?php
// fix_all_passwords.php - HAPUS SETELAH DIGUNAKAN!

echo "<!DOCTYPE html>
<html>
<head>
    <title>Perbaiki Password Hash</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3>Perbaiki Password Hash di Database</h3>
            </div>
            <div class='card-body'>";

// Koneksi database
$host = 'localhost';
$dbname = 'esurat_desa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h4>Status Database Saat Ini:</h4>";
    
    // 1. CEK ADMIN
    $stmt = $pdo->query("SELECT username, password FROM admin");
    $admin = $stmt->fetch();
    echo "<div class='mb-3'>";
    echo "<strong>Admin:</strong><br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Password di DB: <code>" . htmlspecialchars($admin['password']) . "</code><br>";
    echo "Panjang: " . strlen($admin['password']) . " karakter<br>";
    
    if (strpos($admin['password'], '$2y$') === 0) {
        echo "<span class='badge bg-success'>✓ Sudah Hash</span>";
    } else {
        echo "<span class='badge bg-danger'>✗ Masih Plain Text</span>";
    }
    echo "</div>";
    
    // 2. CEK KEPALA DESA
    $stmt = $pdo->query("SELECT username, password FROM kepala_desa");
    $kepala = $stmt->fetch();
    echo "<div class='mb-3'>";
    echo "<strong>Kepala Desa:</strong><br>";
    echo "Username: " . $kepala['username'] . "<br>";
    echo "Password di DB: <code>" . htmlspecialchars($kepala['password']) . "</code><br>";
    echo "Panjang: " . strlen($kepala['password']) . " karakter<br>";
    
    if (password_verify('kepala123', $kepala['password'])) {
        echo "<span class='badge bg-success'>✓ Hash Valid</span>";
    } else {
        echo "<span class='badge bg-danger'>✗ Hash Tidak Valid</span>";
    }
    echo "</div>";
    
    // 3. PERBAIKI PASSWORD
    echo "<hr><h4>Memperbaiki Password...</h4>";
    
    // Generate hash yang benar
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $kepala_hash = password_hash('kepala123', PASSWORD_DEFAULT);
    
    echo "<div class='alert alert-info'>";
    echo "Hash baru untuk 'admin123':<br><small><code>" . $admin_hash . "</code></small><br><br>";
    echo "Hash baru untuk 'kepala123':<br><small><code>" . $kepala_hash . "</code></small>";
    echo "</div>";
    
    // Update password admin
    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
    if ($stmt->execute([$admin_hash])) {
        echo "<div class='alert alert-success'>✓ Password admin berhasil diperbarui</div>";
    } else {
        echo "<div class='alert alert-danger'>✗ Gagal update password admin</div>";
    }
    
    // Update password kepala desa
    $stmt = $pdo->prepare("UPDATE kepala_desa SET password = ? WHERE username = 'kepaladesa'");
    if ($stmt->execute([$kepala_hash])) {
        echo "<div class='alert alert-success'>✓ Password kepala desa berhasil diperbarui</div>";
    } else {
        echo "<div class='alert alert-danger'>✗ Gagal update password kepala desa</div>";
    }
    
    // 4. VERIFIKASI
    echo "<hr><h4>Verifikasi Perbaikan:</h4>";
    
    // Verifikasi admin
    $stmt = $pdo->query("SELECT password FROM admin WHERE username = 'admin'");
    $new_admin = $stmt->fetch();
    if (password_verify('admin123', $new_admin['password'])) {
        echo "<div class='alert alert-success'>✓ Admin: Hash valid dan password_verify() BERHASIL</div>";
    } else {
        echo "<div class='alert alert-danger'>✗ Admin: Masih bermasalah</div>";
    }
    
    // Verifikasi kepala desa
    $stmt = $pdo->query("SELECT password FROM kepala_desa WHERE username = 'kepaladesa'");
    $new_kepala = $stmt->fetch();
    if (password_verify('kepala123', $new_kepala['password'])) {
        echo "<div class='alert alert-success'>✓ Kepala Desa: Hash valid dan password_verify() BERHASIL</div>";
    } else {
        echo "<div class='alert alert-danger'>✗ Kepala Desa: Masih bermasalah</div>";
    }
    
    echo "<hr>";
    echo "<h4 class='text-success'>Perbaikan Selesai!</h4>";
    echo "<p>Silahkan login dengan:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
    echo "<li><strong>Kepala Desa:</strong> username: <code>kepaladesa</code>, password: <code>kepala123</code></li>";
    echo "</ul>";
    echo "<a href='login.php' class='btn btn-primary'>Login Sekarang</a>";
    
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "<hr><div class='alert alert-warning'>";
echo "<strong>PENTING:</strong> Hapus file ini setelah digunakan untuk keamanan!";
echo "</div>";

echo "</div></div></div></body></html>";
?>