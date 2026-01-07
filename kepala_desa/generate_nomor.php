<?php
require_once '../config.php';
require_role('kepala_desa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_surat_id = $_POST['jenis_surat_id'] ?? '';
    
    if (empty($jenis_surat_id)) {
        flash_message('danger', 'Pilih jenis surat terlebih dahulu');
        redirect('dashboard.php');
    }
    
    try {
        // Ambil kode surat
        $stmt = $pdo->prepare("SELECT kode_surat, nama_surat FROM jenis_surat WHERE id = ?");
        $stmt->execute([$jenis_surat_id]);
        $jenis = $stmt->fetch();
        
        if (!$jenis) {
            flash_message('danger', 'Jenis surat tidak ditemukan');
            redirect('dashboard.php');
        }
        
        $kode_surat = $jenis['kode_surat'];
        $nama_surat = $jenis['nama_surat'];
        
        // Generate nomor surat
        $bulan_romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        
        $bulan = $bulan_romawi[date('n')];
        $tahun = date('Y');
        
        // Hitung nomor urut untuk bulan ini
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM pengajuan_surat 
            WHERE MONTH(tanggal_pengajuan) = ? 
            AND YEAR(tanggal_pengajuan) = ?
            AND jenis_surat_id = ?
        ");
        $stmt->execute([date('n'), date('Y'), $jenis_surat_id]);
        $result = $stmt->fetch();
        
        $nomor_urut = str_pad($result['total'] + 1, 3, '0', STR_PAD_LEFT);
        $nomor_surat = "$nomor_urut/$kode_surat/KD/$bulan/$tahun";
        
        // Simpan ke database untuk referensi
        $stmt = $pdo->prepare("
            INSERT INTO nomor_surat_referensi (jenis_surat_id, nomor_surat, tahun, bulan, dibuat_oleh) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$jenis_surat_id, $nomor_surat, $tahun, $bulan, $_SESSION['user_nama']]);
        
        flash_message('success', 
            "Nomor surat berhasil digenerate!<br>
            <strong>Jenis:</strong> $nama_surat<br>
            <strong>Nomor:</strong> $nomor_surat<br>
            <small>Simpan nomor ini untuk digunakan saat TTD surat</small>"
        );
        
    } catch (PDOException $e) {
        flash_message('danger', 'Gagal generate nomor surat: ' . $e->getMessage());
    }
    
    redirect('dashboard.php');
} else {
    redirect('dashboard.php');
}
?>