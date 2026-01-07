<?php
require_once '../config.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_penduduk.php');
    exit;
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'tambah') {
    // Validasi input
    $nik = $_POST['nik'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Cek apakah NIK sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM penduduk WHERE nik = ?");
    $stmt->execute([$nik]);
    
    if ($stmt->fetch()) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'NIK sudah terdaftar!'
        ];
        header('Location: kelola_penduduk.php');
        exit;
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert data
    $stmt = $pdo->prepare("
        INSERT INTO penduduk (nik, nama, tempat_lahir, tanggal_lahir, alamat, no_telp, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $alamat, $no_telp, $password_hash])) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Data penduduk berhasil ditambahkan!'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Gagal menambahkan data penduduk!'
        ];
    }
    
} elseif ($action === 'edit' && $id > 0) {
    // Validasi input
    $nik = $_POST['nik'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $status = $_POST['status'] ?? 1;
    $password = $_POST['password'] ?? '';
    
    // Cek apakah NIK sudah digunakan oleh penduduk lain
    $stmt = $pdo->prepare("SELECT id FROM penduduk WHERE nik = ? AND id != ?");
    $stmt->execute([$nik, $id]);
    
    if ($stmt->fetch()) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'NIK sudah digunakan oleh penduduk lain!'
        ];
        header('Location: kelola_penduduk.php');
        exit;
    }
    
    // Jika password diisi, update password
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE penduduk 
            SET nik = ?, nama = ?, tempat_lahir = ?, tanggal_lahir = ?, 
                alamat = ?, no_telp = ?, status = ?, password = ?
            WHERE id = ?
        ");
        $success = $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $alamat, $no_telp, $status, $password_hash, $id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE penduduk 
            SET nik = ?, nama = ?, tempat_lahir = ?, tanggal_lahir = ?, 
                alamat = ?, no_telp = ?, status = ?
            WHERE id = ?
        ");
        $success = $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $alamat, $no_telp, $status, $id]);
    }
    
    if ($success) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Data penduduk berhasil diperbarui!'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Gagal memperbarui data penduduk!'
        ];
    }
}

header('Location: kelola_penduduk.php');
exit;