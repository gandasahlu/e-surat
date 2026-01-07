<?php
require_once '../config.php';
require_role('admin');

// Hitung statistik
$stats = [];
$queries = [
    'total_penduduk' => "SELECT COUNT(*) as count FROM penduduk",
    'total_pengajuan' => "SELECT COUNT(*) as count FROM pengajuan_surat",
    'pengajuan_menunggu' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'menunggu'",
    'pengajuan_diproses' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'diproses'",
    'pengajuan_disetujui' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'disetujui'",
    'pengajuan_siap_ambil' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'siap_ambil'"
];

foreach ($queries as $key => $query) {
    $stmt = $pdo->query($query);
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Hitung statistik harian dan bulanan
$stmt = $pdo->query("SELECT COUNT(*) as count FROM pengajuan_surat WHERE DATE(tanggal_pengajuan) = CURDATE()");
$harian = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM pengajuan_surat WHERE MONTH(tanggal_pengajuan) = MONTH(CURDATE()) AND YEAR(tanggal_pengajuan) = YEAR(CURDATE())");
$bulanan = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-house-door"></i> <?php echo $site_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_pengajuan.php">
                            <i class="bi bi-files"></i> Pengajuan Surat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_penduduk.php">
                            <i class="bi bi-people"></i> Data Penduduk
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_nama']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(function_exists('display_flash_message')) display_flash_message(); ?>
        
        <!-- Welcome Card -->
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title text-primary">
                            <i class="bi bi-gear"></i> Dashboard Administrator
                        </h4>
                        <p class="card-text">
                            Selamat datang, <strong><?php echo $_SESSION['user_nama']; ?></strong><br>
                            Anda login sebagai Administrator Sistem e-Surat Desa
                        </p>
                        <div class="btn-group">
                            <a href="kelola_pengajuan.php" class="btn btn-primary">
                                <i class="bi bi-files"></i> Kelola Pengajuan
                            </a>
                            <a href="kelola_penduduk.php" class="btn btn-outline-primary">
                                <i class="bi bi-people"></i> Data Penduduk
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="avatar-circle bg-primary text-white display-4">
                            <i class="bi bi-gear"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Penduduk</h6>
                                <h2 class="card-text"><?php echo $stats['total_penduduk']; ?></h2>
                            </div>
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Pengajuan</h6>
                                <h2 class="card-text"><?php echo $stats['total_pengajuan']; ?></h2>
                            </div>
                            <i class="bi bi-files" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Menunggu</h6>
                                <h2 class="card-text"><?php echo $stats['pengajuan_menunggu']; ?></h2>
                            </div>
                            <i class="bi bi-clock" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Diproses</h6>
                                <h2 class="card-text"><?php echo $stats['pengajuan_diproses']; ?></h2>
                            </div>
                            <i class="bi bi-gear" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Pengajuan dan Quick Actions -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Pengajuan Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Penduduk</th>
                                        <th>Jenis Surat</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("
                                        SELECT p.*, pd.nama as nama_penduduk, js.nama_surat 
                                        FROM pengajuan_surat p 
                                        JOIN penduduk pd ON p.penduduk_id = pd.id 
                                        JOIN jenis_surat js ON p.jenis_surat_id = js.id 
                                        ORDER BY p.tanggal_pengajuan DESC 
                                        LIMIT 10
                                    ");
                                    $pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($pengajuan) > 0):
                                        foreach ($pengajuan as $index => $row):
                                            $status_badge = [
                                                'menunggu' => 'warning',
                                                'diproses' => 'info',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                                'siap_ambil' => 'primary'
                                            ];
                                            $badge_class = $status_badge[$row['status']] ?? 'secondary';
                                    ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $row['nama_penduduk']; ?></td>
                                            <td><?php echo $row['nama_surat']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="kelola_pengajuan.php?view=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada pengajuan surat</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <!-- 1. Verifikasi Pengajuan Baru -->
                            <a href="kelola_pengajuan.php?status=menunggu" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clock text-warning"></i> Verifikasi Pengajuan Baru
                                </div>
                                <span class="badge bg-warning rounded-pill"><?php echo $stats['pengajuan_menunggu']; ?></span>
                            </a>
                            
                            <!-- 2. Proses Pengajuan -->
                            <a href="kelola_pengajuan.php?status=diproses" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-gear text-info"></i> Proses Pengajuan
                                </div>
                                <span class="badge bg-info rounded-pill"><?php echo $stats['pengajuan_diproses']; ?></span>
                            </a>
                            
                            <!-- 3. Tambah Penduduk -->
                            <a href="#" class="list-group-item list-group-item-action" 
                               data-bs-toggle="modal" data-bs-target="#addPendudukModal">
                                <i class="bi bi-person-plus text-primary"></i> Tambah Penduduk Baru
                            </a>
                            
                            <!-- 4. Cetak Laporan -->
                            <a href="#" class="list-group-item list-group-item-action" 
                               data-bs-toggle="modal" data-bs-target="#printReportModal">
                                <i class="bi bi-printer text-secondary"></i> Cetak Laporan
                            </a>
                            
                            <!-- 5. Buat Surat Cepat -->
                            <a href="kelola_pengajuan.php?status=disetujui" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-check-circle text-success"></i> Surat Disetujui
                                </div>
                                <span class="badge bg-success rounded-pill"><?php echo $stats['pengajuan_disetujui']; ?></span>
                            </a>
                            
                            <!-- 6. Surat Siap Ambil -->
                            <a href="kelola_pengajuan.php?status=siap_ambil" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-check text-primary"></i> Surat Siap Ambil
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $stats['pengajuan_siap_ambil']; ?></span>
                            </a>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted">Total Hari Ini</small>
                                    <h6 class="mb-0"><?php echo $harian; ?></h6>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Total Bulan Ini</small>
                                    <h6 class="mb-0"><?php echo $bulanan; ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Penduduk -->
    <div class="modal fade" id="addPendudukModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Tambah Penduduk Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="tambah_penduduk.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIK *</label>
                            <input type="text" class="form-control" name="nik" required 
                                   placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat *</label>
                            <textarea class="form-control" name="alamat" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="no_telp" 
                                   placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Default *</label>
                            <input type="text" class="form-control" name="password" value="123456" required>
                            <small class="text-muted">Password default untuk login pertama</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Penduduk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cetak Laporan -->
    <div class="modal fade" id="printReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-printer"></i> Cetak Laporan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="cetak_laporan.php" target="_blank">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Laporan</label>
                            <select class="form-select" name="jenis">
                                <option value="semua">Semua Pengajuan</option>
                                <option value="harian">Harian</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                                <option value="status">Berdasarkan Status</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal" 
                                       value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" value="pdf" checked>
                                <label class="form-check-label">PDF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" value="excel">
                                <label class="form-check-label">Excel</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>