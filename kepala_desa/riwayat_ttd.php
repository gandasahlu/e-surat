<?php
require_once '../config.php';
require_role('kepala_desa');

// Ambil parameter filter
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$jenis_surat = $_GET['jenis_surat'] ?? '';

// Query riwayat TTD dengan filter
$where = "WHERE p.status IN ('siap_ambil', 'disetujui') AND p.tanggal_ttd IS NOT NULL";
$params = [];

if (!empty($bulan) && $bulan !== 'all') {
    $where .= " AND MONTH(p.tanggal_ttd) = ?";
    $params[] = $bulan;
}

if (!empty($tahun) && $tahun !== 'all') {
    $where .= " AND YEAR(p.tanggal_ttd) = ?";
    $params[] = $tahun;
}

if (!empty($jenis_surat) && $jenis_surat !== 'all') {
    $where .= " AND p.jenis_surat_id = ?";
    $params[] = $jenis_surat;
}

$query = "
    SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat, js.kode_surat
    FROM pengajuan_surat p 
    JOIN penduduk pd ON p.penduduk_id = pd.id 
    JOIN jenis_surat js ON p.jenis_surat_id = js.id 
    $where 
    ORDER BY p.tanggal_ttd DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil opsi filter
$stmt = $pdo->query("SELECT * FROM jenis_surat ORDER BY nama_surat");
$jenis_surat_options = $stmt->fetchAll();

// Hitung statistik
$total_surat = count($riwayat);
$total_ttd_bulan_ini = 0;
$total_ttd_tahun_ini = 0;

if ($riwayat) {
    $stmt = $pdo->query("
        SELECT 
            COUNT(CASE WHEN MONTH(tanggal_ttd) = MONTH(CURDATE()) THEN 1 END) as bulan_ini,
            COUNT(CASE WHEN YEAR(tanggal_ttd) = YEAR(CURDATE()) THEN 1 END) as tahun_ini
        FROM pengajuan_surat 
        WHERE tanggal_ttd IS NOT NULL
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_ttd_bulan_ini = $stats['bulan_ini'];
    $total_ttd_tahun_ini = $stats['tahun_ini'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tanda Tangan - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { padding: 20px; }
        .print-header { display: none; }
        @media print {
            .no-print { display: none !important; }
            .print-header { display: block; text-align: center; margin-bottom: 30px; }
            .table th { background-color: #f8f9fa !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="no-print mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="bi bi-clock-history"></i> Riwayat Tanda Tangan
                </h2>
                <div>
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="bulan">
                                <option value="all">Semua Bulan</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" 
                                        <?php echo $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select class="form-select" name="tahun">
                                <option value="all">Semua Tahun</option>
                                <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis Surat</label>
                            <select class="form-select" name="jenis_surat">
                                <option value="all">Semua Jenis</option>
                                <?php foreach ($jenis_surat_options as $jenis): ?>
                                    <option value="<?php echo $jenis['id']; ?>" 
                                        <?php echo $jenis_surat == $jenis['id'] ? 'selected' : ''; ?>>
                                        <?php echo $jenis['nama_surat']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Print Header -->
        <div class="print-header">
            <h2>RIWAYAT TANDA TANGAN SURAT</h2>
            <p>Sistem e-Surat Desa - <?php echo date('d/m/Y H:i:s'); ?></p>
            <hr>
        </div>

        <!-- Statistik -->
        <div class="row mb-4 no-print">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total TTD</h5>
                        <h2 class="card-text"><?php echo $total_surat; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Bulan Ini</h5>
                        <h2 class="card-text"><?php echo $total_ttd_bulan_ini; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Tahun Ini</h5>
                        <h2 class="card-text"><?php echo $total_ttd_tahun_ini; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Rata-rata/Bulan</h5>
                        <h2 class="card-text"><?php echo $total_ttd_tahun_ini > 0 ? round($total_ttd_tahun_ini / date('n'), 1) : 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat -->
        <div class="card">
            <div class="card-body">
                <?php if ($total_surat > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal TTD</th>
                                    <th>NIK</th>
                                    <th>Penduduk</th>
                                    <th>Jenis Surat</th>
                                    <th>Nomor Surat</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat as $index => $row): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_ttd'])); ?></td>
                                        <td><?php echo $row['nik']; ?></td>
                                        <td><?php echo $row['nama_penduduk']; ?></td>
                                        <td><?php echo $row['nama_surat']; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $row['nomor_surat'] ?? 'Belum ada nomor'; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'siap_ambil' ? 'success' : 'primary'; ?>">
                                                <?php echo $row['status'] === 'siap_ambil' ? 'Siap Ambil' : 'Disetujui'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Ringkasan:</h6>
                        <p>Total surat yang sudah ditandatangani: <strong><?php echo $total_surat; ?></strong></p>
                        <?php if ($riwayat): ?>
                            <?php 
                            $last_ttd = $riwayat[0];
                            echo "<p>Terakhir TTD: <strong>" . date('d/m/Y H:i', strtotime($last_ttd['tanggal_ttd'])) . "</strong></p>";
                            ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Tidak ada riwayat</h4>
                        <p class="text-muted">Belum ada surat yang ditandatangani dengan filter yang dipilih</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto print jika di-popup
        if (window.opener) {
            document.querySelector('button[onclick="window.print()"]').addEventListener('click', function() {
                window.print();
            });
        }
        
        // Auto close setelah print
        window.addEventListener('afterprint', function() {
            if (window.opener) {
                setTimeout(() => {
                    window.close();
                }, 1000);
            }
        });
    </script>
</body>
</html>