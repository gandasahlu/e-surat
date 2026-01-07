<?php
require_once '../config.php';
require_role('penduduk');

$user_id = $_SESSION['user_id'];
$user_nik = $_SESSION['user_nik'];

// Ambil data penduduk
$stmt = $pdo->prepare("SELECT * FROM penduduk WHERE id = ?");
$stmt->execute([$user_id]);
$penduduk = $stmt->fetch(PDO::FETCH_ASSOC);

// Hitung statistik lengkap
$stats = [];
$statuses = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'siap_ambil'];

foreach ($statuses as $status) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pengajuan_surat WHERE penduduk_id = ? AND status = ?");
    $stmt->execute([$user_id, $status]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats[$status] = $result['count'];
}

// Total semua pengajuan
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pengajuan_surat WHERE penduduk_id = ?");
$stmt->execute([$user_id]);
$total_pengajuan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Hitung persentase
$persentase_disetujui = $total_pengajuan > 0 ? round(($stats['disetujui'] / $total_pengajuan) * 100, 1) : 0;

// Ambil pengajuan terbaru
$stmt = $pdo->prepare("
    SELECT p.*, j.nama_surat, j.kode_surat 
    FROM pengajuan_surat p 
    JOIN jenis_surat j ON p.jenis_surat_id = j.id 
    WHERE penduduk_id = ? 
    ORDER BY tanggal_pengajuan DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$pengajuan_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah ada surat yang siap ambil
$surat_siap_ambil = false;
foreach ($pengajuan_terbaru as $p) {
    if ($p['status'] === 'siap_ambil') {
        $surat_siap_ambil = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penduduk - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .progress-ring {
            width: 80px;
            height: 80px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }
        .quick-action-card {
            transition: all 0.3s ease;
            border-left: 4px solid #4361ee;
        }
        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
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
                        <a class="nav-link" href="ajukan_surat.php">
                            <i class="bi bi-plus-circle"></i> Ajukan Surat
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $penduduk['nama']; ?>
                             
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profile">
                                <i class="bi bi-person"></i> Profil
                            </a></li>
                            
                            <li><hr class="dropdown-divider"></li>
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
        <?php display_flash_message(); ?>
        
        <!-- Notifikasi Surat Siap Ambil -->
        <?php if ($surat_siap_ambil): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h5 class="alert-heading mb-1">Ada Surat Siap Ambil!</h5>
                    <p class="mb-0">Beberapa surat Anda sudah siap diambil di kantor desa.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Welcome Card -->
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title text-primary">
                            <i class="bi bi-person-badge"></i> Selamat Datang, <?php echo $penduduk['nama']; ?>
                        </h4>
                        <p class="card-text mb-2">
                            <strong>NIK:</strong> <?php echo $penduduk['nik']; ?> | 
                            <strong>Total Pengajuan:</strong> <?php echo $total_pengajuan; ?>
                        </p>
                        <p class="card-text text-muted mb-3">
                            <small><i class="bi bi-geo-alt"></i> <?php echo $penduduk['alamat']; ?></small>
                        </p>
                        <div class="btn-group">
                            <a href="ajukan_surat.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Ajukan Surat Baru
                            </a>
                            
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="position-relative">
                            <div class="avatar-circle bg-primary text-white display-4 mb-2">
                                <?php echo strtoupper(substr($penduduk['nama'], 0, 1)); ?>
                            </div>
                            
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Kolom Kiri: Statistik dan Pengajuan -->
            <div class="col-lg-8">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-warning">
                            <div class="card-body p-3">
                                <div class="text-warning mb-2">
                                    <i class="bi bi-clock" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $stats['menunggu']; ?></h4>
                                <small class="text-muted">Menunggu</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-info">
                            <div class="card-body p-3">
                                <div class="text-info mb-2">
                                    <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $stats['diproses']; ?></h4>
                                <small class="text-muted">Diproses</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-success">
                            <div class="card-body p-3">
                                <div class="text-success mb-2">
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $stats['disetujui']; ?></h4>
                                <small class="text-muted">Disetujui</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-danger">
                            <div class="card-body p-3">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-x-circle" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $stats['ditolak']; ?></h4>
                                <small class="text-muted">Ditolak</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-primary">
                            <div class="card-body p-3">
                                <div class="text-primary mb-2">
                                    <i class="bi bi-file-earmark-check" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $stats['siap_ambil']; ?></h4>
                                <small class="text-muted">Siap Ambil</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center border-secondary">
                            <div class="card-body p-3">
                                <div class="text-secondary mb-2">
                                    <i class="bi bi-files" style="font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-1"><?php echo $total_pengajuan; ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Pengajuan -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check"></i> Pengajuan Terbaru
                        </h5>
                      
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Jenis Surat</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Keperluan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($pengajuan_terbaru) > 0): ?>
                                        <?php foreach ($pengajuan_terbaru as $index => $row): 
                                            $status_badge = [
                                                'menunggu' => 'warning',
                                                'diproses' => 'info',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                                'siap_ambil' => 'primary'
                                            ];
                                            $badge_class = $status_badge[$row['status']] ?? 'secondary';
                                            $status_text = ucfirst($row['status']);
                                            if ($row['status'] === 'siap_ambil') $status_text = 'Siap Ambil';
                                        ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo $row['nama_surat']; ?></strong>
                                                        <?php if ($row['nomor_surat']): ?>
                                                            <br><small class="text-muted"><?php echo $row['nomor_surat']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo substr($row['keperluan'], 0, 40); ?><?php echo strlen($row['keperluan']) > 40 ? '...' : ''; ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary btn-detail" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal"
                                                                data-id="<?php echo $row['id']; ?>"
                                                                data-jenis="<?php echo htmlspecialchars($row['nama_surat']); ?>"
                                                                data-kode="<?php echo $row['kode_surat']; ?>"
                                                                data-tanggal="<?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?>"
                                                                data-status="<?php echo $row['status']; ?>"
                                                                data-status-class="<?php echo $badge_class; ?>"
                                                                data-keperluan="<?php echo htmlspecialchars($row['keperluan']); ?>"
                                                                data-catatan="<?php echo htmlspecialchars($row['catatan_admin'] ?? ''); ?>"
                                                                data-nomor="<?php echo htmlspecialchars($row['nomor_surat'] ?? ''); ?>"
                                                                data-berkas="<?php echo htmlspecialchars($row['berkas_pendukung'] ?? ''); ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <?php if ($row['status'] === 'siap_ambil'): ?>
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    onclick="alert('Silahkan ambil surat ini di kantor desa dengan menunjukkan NIK Anda.\\n\\nNomor Surat: ' + '<?php echo $row['nomor_surat'] ?? "Belum ada nomor"; ?>')">
                                                                <i class="bi bi-download"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="py-5">
                                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                                                    <h5 class="mt-3 text-muted">Belum ada pengajuan</h5>
                                                    <p class="text-muted">Mulai ajukan surat pertama Anda</p>
                                                    <a href="ajukan_surat.php" class="btn btn-primary mt-2">
                                                        <i class="bi bi-plus-circle"></i> Ajukan Surat Pertama
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Quick Actions dan Info -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="ajukan_surat.php" class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="me-3 text-primary">
                                    <i class="bi bi-plus-circle" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Ajukan Surat Baru</h6>
                                    <small class="text-muted">Buat pengajuan surat baru</small>
                                </div>
                                <i class="bi bi-arrow-right text-muted"></i>
                            
                            
                            
                            
                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#caraPengajuan">
                                <div class="me-3 text-warning">
                                    <i class="bi bi-question-circle" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Cara Pengajuan</h6>
                                    <small class="text-muted">Panduan lengkap</small>
                                </div>
                                <i class="bi bi-arrow-right text-muted"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Cepat -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Info Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                    
                        
                        <div class="mb-3">
                            <h6><i class="bi bi-clock"></i> Waktu Proses</h6>
                            <ul class="list-unstyled mb-0">
                                <li><small>✓ Verifikasi: 1-2 hari kerja</small></li>
                                <li><small>✓ Tanda Tangan: 1-3 hari kerja</small></li>
                                <li><small>✓ Total: 2-5 hari kerja</small></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h6><i class="bi bi-telephone"></i> Kontak</h6>
                            <ul class="list-unstyled mb-0">
                                <li><small><i class="bi bi-geo-alt"></i> Kantor Desa</small></li>
                                <li><small><i class="bi bi-clock"></i> Senin-Jumat: 08:00-16:00</small></li>
                                <li><small><i class="bi bi-phone"></i> (021) 123456</small></li>
                            </ul>
                        </div>
                    </div>
                </div>

              

    <!-- Modal Detail Pengajuan -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detail Pengajuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informasi Surat</h6>
                            <p><strong>Jenis Surat:</strong> <span id="detail_jenis"></span></p>
                            <p><strong>Kode Surat:</strong> <span id="detail_kode"></span></p>
                            <p><strong>Tanggal Pengajuan:</strong> <span id="detail_tanggal"></span></p>
                            <p><strong>Nomor Surat:</strong> <span id="detail_nomor"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Status</h6>
                            <p id="detail_status_badge"></p>
                            <div id="detail_catatan_container">
                                <strong>Catatan:</strong>
                                <div class="border rounded p-2 mt-1 bg-light" id="detail_catatan"></div>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <h6>Keperluan / Alasan</h6>
                            <div class="border rounded p-3">
                                <p id="detail_keperluan"></p>
                            </div>
                        </div>
                       
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <?php if ($stats['siap_ambil'] > 0): ?>
            
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Profil (sudah ada) -->
    <div class="modal fade" id="profile" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Profil Penduduk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle-lg bg-primary text-white display-4 mb-3 mx-auto">
                            <?php echo strtoupper(substr($penduduk['nama'], 0, 1)); ?>
                        </div>
                        <h4><?php echo $penduduk['nama']; ?></h4>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>NIK:</strong><br>
                            <span class="text-muted"><?php echo $penduduk['nik']; ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tempat Lahir:</strong><br>
                            <span class="text-muted"><?php echo $penduduk['tempat_lahir']; ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tanggal Lahir:</strong><br>
                            <span class="text-muted"><?php echo date('d/m/Y', strtotime($penduduk['tanggal_lahir'])); ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>No. Telepon:</strong><br>
                            <span class="text-muted"><?php echo $penduduk['no_telp'] ?? '-'; ?></span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Alamat:</strong><br>
                            <span class="text-muted"><?php echo $penduduk['alamat']; ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Statistik:</strong><br>
                            <div class="row mt-2">
                                <div class="col-4 text-center">
                                    <h5 class="mb-0"><?php echo $total_pengajuan; ?></h5>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4 text-center">
                                    <h5 class="mb-0"><?php echo $stats['disetujui']; ?></h5>
                                    <small class="text-muted">Disetujui</small>
                                </div>
                                <div class="col-4 text-center">
                                    <h5 class="mb-0"><?php echo $stats['siap_ambil']; ?></h5>
                                    <small class="text-muted">Siap Ambil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cara Pengajuan -->
    <div class="modal fade" id="caraPengajuan" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-question-circle"></i> Cara Pengajuan Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-1-circle text-primary"></i> Ajukan Surat</h6>
                            <p>Pilih jenis surat yang dibutuhkan dan isi formulir dengan lengkap.</p>
                            
                            <h6><i class="bi bi-2-circle text-primary"></i> Verifikasi Admin</h6>
                            <p>Admin akan memverifikasi data Anda dalam 1-2 hari kerja.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-3-circle text-primary"></i> Tanda Tangan</h6>
                            <p>Kepala desa akan menandatangani surat yang sudah diverifikasi.</p>
                            
                            <h6><i class="bi bi-4-circle text-primary"></i> Ambil Surat</h6>
                            <p>Surat siap diambil di kantor desa dengan menunjukkan NIK.</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Persyaratan Umum:</h6>
                        <ul class="mb-0">
                            <li>NIK harus valid dan terdaftar</li>
                            <li>Data diri harus lengkap dan benar</li>
                            <li>Berkas pendukung sesuai kebutuhan</li>
                            <li>Surat bisa diambil dalam 30 hari</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="ajukan_surat.php" class="btn btn-primary">Ajukan Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript untuk detail modal
        document.addEventListener('DOMContentLoaded', function() {
            const detailButtons = document.querySelectorAll('.btn-detail');
            const detailModal = document.getElementById('detailModal');
            
            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari atribut data-*
                    const data = {
                        id: this.getAttribute('data-id'),
                        jenis: this.getAttribute('data-jenis'),
                        kode: this.getAttribute('data-kode'),
                        tanggal: this.getAttribute('data-tanggal'),
                        status: this.getAttribute('data-status'),
                        statusClass: this.getAttribute('data-status-class'),
                        keperluan: this.getAttribute('data-keperluan'),
                        catatan: this.getAttribute('data-catatan'),
                        nomor: this.getAttribute('data-nomor'),
                        berkas: this.getAttribute('data-berkas')
                    };
                    
                    // Isi data ke modal
                    document.getElementById('detail_jenis').textContent = data.jenis;
                    document.getElementById('detail_kode').textContent = data.kode;
                    document.getElementById('detail_tanggal').textContent = data.tanggal;
                    document.getElementById('detail_nomor').textContent = data.nomor || 'Belum ada nomor';
                    document.getElementById('detail_keperluan').textContent = data.keperluan;
                    document.getElementById('detail_catatan').textContent = data.catatan || 'Tidak ada catatan';
                    
                    // Set status badge
                    let statusText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    if (data.status === 'siap_ambil') statusText = 'Siap Ambil';
                    const statusBadge = `<span class="badge bg-${data.statusClass}">${statusText}</span>`;
                    document.getElementById('detail_status_badge').innerHTML = `<strong>Status:</strong> ${statusBadge}`;
                    
                    // Handle berkas pendukung
                    const berkasContainer = document.getElementById('detail_berkas_container');
                    const berkasLink = document.getElementById('detail_berkas_link');
                    
                    if (data.berkas && data.berkas.trim() !== '') {
                        berkasContainer.style.display = 'block';
                        berkasLink.href = '../uploads/' + encodeURIComponent(data.berkas);
                    } else {
                        berkasContainer.style.display = 'none';
                    }
                });
            });
            
            // Reset modal ketika ditutup
            if (detailModal) {
                detailModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('detail_jenis').textContent = '';
                    document.getElementById('detail_kode').textContent = '';
                    document.getElementById('detail_tanggal').textContent = '';
                    document.getElementById('detail_nomor').textContent = '';
                    document.getElementById('detail_keperluan').textContent = '';
                    document.getElementById('detail_catatan').textContent = '';
                    document.getElementById('detail_status_badge').innerHTML = '';
                });
            }
            
            // Auto focus pada modal pertama kali
            const firstModal = document.querySelector('[data-bs-toggle="modal"]');
            if (firstModal && document.querySelectorAll('.btn-detail').length === 0) {
                setTimeout(() => {
                    firstModal.focus();
                }, 1000);
            }
            
            // Notifikasi surat siap ambil
            const suratSiapAmbil = <?php echo $stats['siap_ambil'] > 0 ? 'true' : 'false'; ?>;
            if (suratSiapAmbil) {
                // Tampilkan notifikasi otomatis setelah 2 detik
                setTimeout(() => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.maxWidth = '300px';
                    alertDiv.innerHTML = `
                        <h6><i class="bi bi-check-circle"></i> Surat Siap Ambil!</h6>
                        <small>Anda memiliki <?php echo $stats['siap_ambil']; ?> surat yang bisa diambil di kantor desa.</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    
                    // Auto dismiss setelah 10 detik
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 10000);
                }, 2000);
            }
        });
    </script>
</body>
</html>