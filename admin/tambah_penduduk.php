<?php
require_once '../config.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = $_POST['nik'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $password = $_POST['password'] ?? '123456';
    
    // Validasi NIK
    if (strlen($nik) !== 16 || !is_numeric($nik)) {
        flash_message('danger', 'NIK harus 16 digit angka');
        redirect('dashboard.php');
    }
    
    // Cek apakah NIK sudah terdaftar
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM penduduk WHERE nik = ?");
    $stmt->execute([$nik]);
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        flash_message('warning', 'NIK sudah terdaftar');
        redirect('dashboard.php');
    }
    
    try {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert penduduk baru
        $stmt = $pdo->prepare("
            INSERT INTO penduduk (nik, nama, tempat_lahir, tanggal_lahir, alamat, no_telp, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $alamat, $no_telp, $password_hash]);
        
        flash_message('success', 'Penduduk berhasil ditambahkan!<br>NIK: ' . $nik . '<br>Password: ' . $password);
        
    } catch (PDOException $e) {
        flash_message('danger', 'Gagal menambahkan penduduk: ' . $e->getMessage());
    }
    
    redirect('dashboard.php');
} else {
    redirect('dashboard.php');
}
?>