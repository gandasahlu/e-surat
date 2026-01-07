<?php
require_once '../config.php';
require_role('kepala_desa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengajuan_id = $_POST['pengajuan_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $nomor_surat = $_POST['nomor_surat'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    if (empty($pengajuan_id) || empty($action)) {
        flash_message('danger', 'Data tidak lengkap');
        redirect('dashboard.php');
    }
    
    try {
        if ($action === 'ttd') {
            if (empty($nomor_surat)) {
                flash_message('danger', 'Nomor surat harus diisi');
                redirect('dashboard.php');
            }
            
            // Validasi format nomor surat
            if (empty(trim($nomor_surat))) {
    flash_message('danger', 'Nomor surat harus diisi');
    redirect('dashboard.php');
            }
            
            // Update status menjadi siap ambil
            $stmt = $pdo->prepare("
                UPDATE pengajuan_surat 
                SET status = 'siap_ambil', 
                    nomor_surat = ?, 
                    catatan_admin = CONCAT(COALESCE(catatan_admin, ''), ' | TTD Kepala Desa: ', ?),
                    tanggal_ttd = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$nomor_surat, $catatan, $pengajuan_id]);
            
            flash_message('success', 'Surat berhasil ditandatangani dengan nomor: ' . $nomor_surat);
            
        } elseif ($action === 'tolak') {
            // Update status menjadi ditolak
            $stmt = $pdo->prepare("
                UPDATE pengajuan_surat 
                SET status = 'ditolak', 
                    catatan_admin = CONCAT(COALESCE(catatan_admin, ''), ' | Ditolak oleh Kepala Desa: ', ?),
                    tanggal_ttd = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$catatan, $pengajuan_id]);
            
            flash_message('warning', 'Surat telah ditolak');
        }
        
    } catch (PDOException $e) {
        flash_message('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    
    redirect('dashboard.php');
} else {
    redirect('dashboard.php');
}
?>