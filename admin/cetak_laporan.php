<?php
require_once '../config.php';
require_role('admin');

// Ambil parameter filter
$jenis = $_GET['jenis'] ?? 'semua';
$dari_tanggal = $_GET['dari_tanggal'] ?? date('Y-m-01');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'pdf';

// Query berdasarkan filter
$where = "WHERE tanggal_pengajuan BETWEEN :dari AND :sampai";
$params = [
    ':dari' => $dari_tanggal . ' 00:00:00',
    ':sampai' => $sampai_tanggal . ' 23:59:59'
];

if ($jenis === 'harian') {
    $where = "WHERE DATE(tanggal_pengajuan) = CURDATE()";
    $params = [];
} elseif ($jenis === 'bulanan') {
    $where = "WHERE MONTH(tanggal_pengajuan) = MONTH(CURDATE()) AND YEAR(tanggal_pengajuan) = YEAR(CURDATE())";
    $params = [];
} elseif ($jenis === 'tahunan') {
    $where = "WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE())";
    $params = [];
} elseif ($jenis === 'status') {
    $status = $_GET['status'] ?? 'menunggu';
    $where = "WHERE status = :status AND tanggal_pengajuan BETWEEN :dari AND :sampai";
    $params = [
        ':status' => $status,
        ':dari' => $dari_tanggal . ' 00:00:00',
        ':sampai' => $sampai_tanggal . ' 23:59:59'
    ];
}

$query = "
    SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat 
    FROM pengajuan_surat p 
    JOIN penduduk pd ON p.penduduk_id = pd.id 
    JOIN jenis_surat js ON p.jenis_surat_id = js.id 
    $where 
    ORDER BY p.tanggal_pengajuan DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$stats = [
    'total' => count($data),
    'menunggu' => 0,
    'diproses' => 0,
    'disetujui' => 0,
    'ditolak' => 0,
    'siap_ambil' => 0
];

foreach ($data as $row) {
    $stats[$row['status']]++;
}

// Jika format PDF (simple HTML print)
if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="laporan_surat_' . date('Ymd') . '.pdf"');
    // Note: Butuh library seperti TCPDF untuk generate PDF asli
    // Ini contoh sederhana untuk print HTML
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan e-Surat Desa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #4361ee; }
        .header p { margin: 5px 0; color: #666; }
        .info-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .stats { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .stat-box { flex: 1; text-align: center; padding: 10px; margin: 0 5px; border-radius: 5px; color: white; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .table tr:nth-child(even) { background-color: #f8f9fa; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; color: #666; border-top: 1px solid #dee2e6; padding-top: 20px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <div class="header">
        <h1>LAPORAN SISTEM e-SURAT DESA</h1>
        <p>Periode: <?php echo date('d/m/Y', strtotime($dari_tanggal)) . ' - ' . date('d/m/Y', strtotime($sampai_tanggal)); ?></p>
        <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="info-box">
        <h4>Filter Laporan</h4>
        <p><strong>Jenis:</strong> <?php echo ucfirst($jenis); ?></p>
        <p><strong>Periode:</strong> <?php echo date('d/m/Y', strtotime($dari_tanggal)) . ' - ' . date('d/m/Y', strtotime($sampai_tanggal)); ?></p>
    </div>

    <div class="stats">
        <div class="stat-box" style="background: #f59e0b;">Menunggu: <?php echo $stats['menunggu']; ?></div>
        <div class="stat-box" style="background: #3b82f6;">Diproses: <?php echo $stats['diproses']; ?></div>
        <div class="stat-box" style="background: #10b981;">Disetujui: <?php echo $stats['disetujui']; ?></div>
        <div class="stat-box" style="background: #ef4444;">Ditolak: <?php echo $stats['ditolak']; ?></div>
        <div class="stat-box" style="background: #8b5cf6;">Siap Ambil: <?php echo $stats['siap_ambil']; ?></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>NIK</th>
                <th>Penduduk</th>
                <th>Jenis Surat</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Keperluan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data) > 0): ?>
                <?php foreach ($data as $index => $row): 
                    $status_colors = [
                        'menunggu' => '#f59e0b',
                        'diproses' => '#3b82f6',
                        'disetujui' => '#10b981',
                        'ditolak' => '#ef4444',
                        'siap_ambil' => '#8b5cf6'
                    ];
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $row['nik']; ?></td>
                        <td><?php echo $row['nama_penduduk']; ?></td>
                        <td><?php echo $row['nama_surat']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                        <td>
                            <span class="badge" style="background: <?php echo $status_colors[$row['status']]; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo substr($row['keperluan'], 0, 50) . '...'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data untuk periode ini</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan dihasilkan oleh Sistem e-Surat Desa</p>
        <p>Kantor Desa, <?php echo date('Y'); ?></p>
    </div>

    <script>
        // Auto print jika PDF
        <?php if ($format === 'pdf'): ?>
        window.onload = function() {
            window.print();
        }
        <?php endif; ?>
    </script>
</body>
</html>