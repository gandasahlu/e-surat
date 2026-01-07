<?php
require_once '../config.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengajuan_id = $_POST['pengajuan_id'];
    $action = $_POST['action'];
    $catatan = $_POST['catatan'];
    
    try {
        $status = ($action === 'setuju') ? 'diproses' : 'ditolak';
        
        $stmt = $pdo->prepare("
            UPDATE pengajuan_surat 
            SET status = ?, catatan_admin = ?, tanggal_verifikasi = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $catatan, $pengajuan_id]);
        
        if ($action === 'setuju') {
            flash_message('success', 'Pengajuan berhasil disetujui dan dikirim ke Kepala Desa untuk ditandatangani.');
        } else {
            flash_message('warning', 'Pengajuan telah ditolak.');
        }
        
    } catch (PDOException $e) {
        flash_message('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    
    redirect('kelola_pengajuan.php');
} else {
    redirect('kelola_pengajuan.php');
}
?>