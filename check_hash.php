<?php
// check_hash.php

// Koneksi database
$host = 'localhost';
$dbname = 'esurat_desa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Cek data admin
$stmt = $pdo->query("SELECT username, password FROM admin WHERE username = 'admin'");
$admin = $stmt->fetch();

echo "<h3>Data Admin di Database:</h3>";
echo "Username: " . $admin['username'] . "<br>";
echo "Password Hash: " . $admin['password'] . "<br>";
echo "Panjang Hash: " . strlen($admin['password']) . " karakter<br><br>";

// Test password_verify
$input_password = 'admin123';
$hash_from_db = $admin['password'];

echo "<h3>Testing password_verify:</h3>";
echo "Password Input: " . $input_password . "<br>";
echo "Hash dari DB: " . $hash_from_db . "<br>";

if (password_verify($input_password, $hash_from_db)) {
    echo "<span style='color:green;'>SUKSES: password_verify() return TRUE</span>";
} else {
    echo "<span style='color:red;'>GAGAL: password_verify() return FALSE</span>";
    
    // Coba generate hash baru dan bandingkan
    echo "<br><br><h3>Generate Hash Baru:</h3>";
    $new_hash = password_hash($input_password, PASSWORD_DEFAULT);
    echo "Hash baru untuk 'admin123': " . $new_hash . "<br>";
    
    // Bandingkan string hash
    if ($hash_from_db === $new_hash) {
        echo "<span style='color:green;'>Hash sama persis</span>";
    } else {
        echo "<span style='color:red;'>Hash berbeda</span>";
    }
}

// Cek juga kepala_desa
echo "<hr>";
$stmt = $pdo->query("SELECT username, password FROM kepala_desa WHERE username = 'kepaladesa'");
$kepala = $stmt->fetch();

echo "<h3>Data Kepala Desa di Database:</h3>";
echo "Username: " . $kepala['username'] . "<br>";
echo "Password Hash: " . $kepala['password'] . "<br>";
echo "Panjang Hash: " . strlen($kepala['password']) . " karakter<br>";

// Test password_verify untuk kepala desa
$input_password = 'kepala123';
$hash_from_db = $kepala['password'];

echo "<h3>Testing password_verify:</h3>";
echo "Password Input: " . $input_password . "<br>";
echo "Hash dari DB: " . $hash_from_db . "<br>";

if (password_verify($input_password, $hash_from_db)) {
    echo "<span style='color:green;'>SUKSES: password_verify() return TRUE</span>";
} else {
    echo "<span style='color:red;'>GAGAL: password_verify() return FALSE</span>";
}
?>