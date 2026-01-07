<?php
require_once '../config.php';
require_role('kepala_desa');

// Hitung statistik
$stats = [];
$queries = [
    'menunggu_ttd' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'diproses'",
    'sudah_ttd' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = 'siap_ambil'",
    'total_ttd' => "SELECT COUNT(*) as count FROM pengajuan_surat WHERE status IN ('siap_ambil', 'disetujui')"
];

foreach ($queries as $key => $query) {
    $stmt = $pdo->query($query);
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Ambil pengajuan yang menunggu tanda tangan
$stmt = $pdo->query("
    SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat 
    FROM pengajuan_surat p 
    JOIN penduduk pd ON p.penduduk_id = pd.id 
    JOIN jenis_surat js ON p.jenis_surat_id = js.id 
    WHERE p.status = 'diproses' 
    ORDER BY p.tanggal_pengajuan DESC 
    LIMIT 5
");
$pengajuan_ttd = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Desa - <?php echo $site_name; ?></title>
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
                        <a class="nav-link" href="ttd_surat.php">
                            <i class="bi bi-pen"></i> Tanda Tangan Surat
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
        <?php display_flash_message(); ?>
        
        <!-- Welcome Card -->
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title text-primary">
                            <i class="bi bi-person-badge"></i> Dashboard Kepala Desa
                        </h4>
                        <p class="card-text">
                            Selamat datang, <strong><?php echo $_SESSION['user_nama']; ?></strong><br>
                            Anda login sebagai Kepala Desa
                        </p>
                        <a href="ttd_surat.php" class="btn btn-primary">
                            <i class="bi bi-pen"></i> Tanda Tangani Surat
                        </a>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="avatar-circle bg-primary text-white display-4">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards dan Quick Actions -->
        <div class="row mb-4">
            <!-- Stats Cards -->
            <div class="col-md-8">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Menunggu TTD</h6>
                                        <h2 class="card-text"><?php echo $stats['menunggu_ttd']; ?></h2>
                                    </div>
                                    <i class="bi bi-clock" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Sudah TTD</h6>
                                        <h2 class="card-text"><?php echo $stats['sudah_ttd']; ?></h2>
                                    </div>
                                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total TTD</h6>
                                        <h2 class="card-text"><?php echo $stats['total_ttd']; ?></h2>
                                    </div>
                                    <i class="bi bi-pen" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Surat Menunggu TTD -->
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock"></i> Surat Menunggu Tanda Tangan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>NIK</th>
                                        <th>Penduduk</th>
                                        <th>Jenis Surat</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($pengajuan_ttd) > 0): ?>
                                        <?php foreach ($pengajuan_ttd as $index => $row): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_penduduk']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_surat']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-warning ttd-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#ttdModal"
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-nik="<?php echo htmlspecialchars($row['nik']); ?>"
                                                            data-nama="<?php echo htmlspecialchars($row['nama_penduduk']); ?>"
                                                            data-jenis="<?php echo htmlspecialchars($row['nama_surat']); ?>"
                                                            data-tanggal="<?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?>">
                                                        <i class="bi bi-pen"></i> Tanda Tangani
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="bi bi-check2-circle" style="font-size: 3rem; color: #ccc;"></i>
                                                <h5 class="mt-3">Tidak ada surat yang menunggu</h5>
                                                <p class="text-muted">Semua surat sudah ditandatangani</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="ttd_surat.php" class="btn btn-outline-warning">
                                <i class="bi bi-list-check"></i> Lihat Semua Surat
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <!-- 1. Tanda Tangani Surat -->
                            <a href="ttd_surat.php" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-pen text-warning"></i> Tanda Tangani Surat
                                </div>
                                <span class="badge bg-warning rounded-pill"><?php echo $stats['menunggu_ttd']; ?></span>
                            </a>
                            
                            <!-- 2. Surat Sudah TTD -->
                            <a href="ttd_surat.php?status=siap_ambil" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-check-circle text-success"></i> Surat Sudah TTD
                                </div>
                                <span class="badge bg-success rounded-pill"><?php echo $stats['sudah_ttd']; ?></span>
                            </a>
                            
                            <!-- 3. Buat Nomor Surat -->
                            
                            <!-- 4. Riwayat TTD -->
                            <a href="#" class="list-group-item list-group-item-action" id="openRiwayatBtn">
                                <i class="bi bi-clock-history text-info"></i> Riwayat Tanda Tangan
                            </a>
                            
                            <!-- 5. Statistik TTD -->
                            <a href="#" class="list-group-item list-group-item-action"
                               data-bs-toggle="modal" data-bs-target="#statsModal">
                                <i class="bi bi-bar-chart text-primary"></i> Statistik TTD
                            </a>
                        </div>
                        
                        <!-- Quick Stats TTD -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="bi bi-graph-up"></i> Statistik Mingguan</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <?php 
                                $total_minggu = $stats['total_ttd'] + $stats['menunggu_ttd'];
                                $persen = $total_minggu > 0 ? ($stats['total_ttd'] / $total_minggu * 100) : 0;
                                ?>
                                <div class="progress-bar bg-success" style="width: <?php echo $persen; ?>%">
                                    <?php echo $stats['total_ttd']; ?> TTD
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php echo round($persen, 1); ?>% dari total <?php echo $total_minggu; ?> surat
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Hitung statistik tambahan
                        $stmt = $pdo->prepare("
                            SELECT 
                                COUNT(CASE WHEN DATE(tanggal_ttd) = CURDATE() THEN 1 END) as ttd_hari_ini,
                                COUNT(CASE WHEN MONTH(tanggal_ttd) = MONTH(CURDATE()) AND YEAR(tanggal_ttd) = YEAR(CURDATE()) THEN 1 END) as ttd_bulan_ini
                            FROM pengajuan_surat 
                            WHERE tanggal_ttd IS NOT NULL
                        ");
                        $stmt->execute();
                        $info_stats = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="mb-2">
                            <small class="text-muted">TTD Hari Ini</small>
                            <h5 class="mb-0"><?php echo $info_stats['ttd_hari_ini']; ?> surat</h5>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">TTD Bulan Ini</small>
                            <h5 class="mb-0"><?php echo $info_stats['ttd_bulan_ini']; ?> surat</h5>
                        </div>
                        <div>
                            <small class="text-muted">Rata-rata per Hari</small>
                            <h5 class="mb-0">
                                <?php 
                                $hari_ini = date('j');
                                $rata_rata = $hari_ini > 0 ? round($info_stats['ttd_bulan_ini'] / $hari_ini, 1) : 0;
                                echo $rata_rata;
                                ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal TTD -->
    <div class="modal fade" id="ttdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Tanda Tangani Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="proses_ttd.php">
                    <div class="modal-body">
                        <input type="hidden" name="pengajuan_id" id="pengajuan_id" value="">
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi Surat</h6>
                            <p><strong>NIK:</strong> <span id="modal_nik"></span></p>
                            <p><strong>Pemohon:</strong> <span id="modal_nama"></span></p>
                            <p><strong>Jenis Surat:</strong> <span id="modal_jenis"></span></p>
                            <p><strong>Tanggal Pengajuan:</strong> <span id="modal_tanggal"></span></p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nomor_surat" class="form-label">Nomor Surat *</label>
                            <input type="text" class="form-control" id="nomor_surat" 
                                   name="nomor_surat" placeholder="Contoh: 001/SK/KD/VI/2024" required>
                            <small class="text-muted">Format: [nomor]/[kode]/KD/[bulan romawi]/[tahun]</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" 
                                      name="catatan" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" name="action" value="ttd">
                            <i class="bi bi-pen"></i> Tanda Tangani
                        </button>
                        <button type="submit" class="btn btn-danger" name="action" value="tolak">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Generate Nomor Surat -->
    <div class="modal fade" id="generateNomorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-hash"></i> Generate Nomor Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="generate_nomor.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Surat</label>
                            <select class="form-select" name="jenis_surat_id" required>
                                <option value="">Pilih Jenis Surat</option>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM jenis_surat ORDER BY nama_surat");
                                while ($row = $stmt->fetch()) {
                                    echo '<option value="' . $row['id'] . '">' . $row['nama_surat'] . ' (' . $row['kode_surat'] . ')</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format Nomor</label>
                            <input type="text" class="form-control" id="preview_nomor" readonly>
                            <small class="text-muted">Format otomatis akan di-generate</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Statistik -->
    <div class="modal fade" id="statsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-bar-chart"></i> Statistik Tanda Tangan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Ambil statistik per jenis surat
                    $stmt = $pdo->query("
                        SELECT js.nama_surat, js.kode_surat,
                               COUNT(CASE WHEN p.status = 'siap_ambil' THEN 1 END) as sudah_ttd,
                               COUNT(CASE WHEN p.status = 'diproses' THEN 1 END) as menunggu_ttd
                        FROM jenis_surat js
                        LEFT JOIN pengajuan_surat p ON js.id = p.jenis_surat_id
                        GROUP BY js.id
                        ORDER BY js.nama_surat
                    ");
                    $stats_surat = $stmt->fetchAll();
                    ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Jenis Surat</th>
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Sudah TTD</th>
                                    <th class="text-center">Menunggu TTD</th>
                                    <th class="text-center">Total</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_surat as $stat): ?>
                                    <?php 
                                    $total = $stat['sudah_ttd'] + $stat['menunggu_ttd'];
                                    $persen = $total > 0 ? ($stat['sudah_ttd'] / $total * 100) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['nama_surat']); ?></td>
                                        <td class="text-center"><span class="badge bg-secondary"><?php echo $stat['kode_surat']; ?></span></td>
                                        <td class="text-center"><span class="badge bg-success"><?php echo $stat['sudah_ttd']; ?></span></td>
                                        <td class="text-center"><span class="badge bg-warning"><?php echo $stat['menunggu_ttd']; ?></span></td>
                                        <td class="text-center"><strong><?php echo $total; ?></strong></td>
                                        <td>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo $persen; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo round($persen, 1); ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Chart Stats -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Distribusi Status</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="200" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Ringkasan</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Total Surat:</span>
                                            <strong><?php echo $stats['total_ttd'] + $stats['menunggu_ttd']; ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>TTD Berhasil:</span>
                                            <strong><?php echo $stats['total_ttd']; ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Menunggu TTD:</span>
                                            <strong><?php echo $stats['menunggu_ttd']; ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Rasio TTD:</span>
                                            <strong><?php echo round($persen, 1); ?>%</strong>
                                        </li>
                                    </ul>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // JavaScript untuk mengisi modal TTD
        document.addEventListener('DOMContentLoaded', function() {
            const ttdButtons = document.querySelectorAll('.ttd-btn');
            const ttdModal = document.getElementById('ttdModal');
            
            ttdButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari atribut data-*
                    const pengajuanId = this.getAttribute('data-id');
                    const nik = this.getAttribute('data-nik');
                    const nama = this.getAttribute('data-nama');
                    const jenis = this.getAttribute('data-jenis');
                    const tanggal = this.getAttribute('data-tanggal');
                    
                    // Isi data ke modal
                    document.getElementById('pengajuan_id').value = pengajuanId;
                    document.getElementById('modal_nik').textContent = nik;
                    document.getElementById('modal_nama').textContent = nama;
                    document.getElementById('modal_jenis').textContent = jenis;
                    document.getElementById('modal_tanggal').textContent = tanggal;
                    
                    // Reset form input
                    document.getElementById('nomor_surat').value = '';
                    document.getElementById('catatan').value = '';
                });
            });
            
            // Reset modal ketika ditutup
            ttdModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('pengajuan_id').value = '';
                document.getElementById('modal_nik').textContent = '';
                document.getElementById('modal_nama').textContent = '';
                document.getElementById('modal_jenis').textContent = '';
                document.getElementById('modal_tanggal').textContent = '';
                document.getElementById('nomor_surat').value = '';
                document.getElementById('catatan').value = '';
            });
            
            // Preview nomor surat saat pilih jenis surat
            const jenisSuratSelect = document.querySelector('#generateNomorModal select[name="jenis_surat_id"]');
            const previewNomor = document.getElementById('preview_nomor');
            
            if (jenisSuratSelect && previewNomor) {
                jenisSuratSelect.addEventListener('change', function() {
                    if (this.value) {
                        const bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                        const bulan = bulanRomawi[new Date().getMonth()];
                        const tahun = new Date().getFullYear();
                        const nomorUrut = '001'; // Default
                        const kode = this.options[this.selectedIndex].text.match(/\(([^)]+)\)/)[1];
                        
                        previewNomor.value = `${nomorUrut}/${kode}/KD/${bulan}/${tahun}`;
                    } else {
                        previewNomor.value = '';
                    }
                });
            }
            
            // Buka riwayat TTD di tab baru
            document.getElementById('openRiwayatBtn').addEventListener('click', function(e) {
                e.preventDefault();
                window.open('riwayat_ttd.php', '_blank', 'width=1200,height=800');
            });
            
            // Chart untuk statistik
            const statusChart = document.getElementById('statusChart');
            if (statusChart) {
                const ctx = statusChart.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah TTD', 'Menunggu TTD'],
                        datasets: [{
                            data: [<?php echo $stats['total_ttd']; ?>, <?php echo $stats['menunggu_ttd']; ?>],
                            backgroundColor: [
                                '#10b981',
                                '#f59e0b'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>