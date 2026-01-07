<?php
require_once '../config.php';
require_role('kepala_desa');

// Filter status
$status_filter = $_GET['status'] ?? 'diproses';
$where = "WHERE p.status = ?";

// Query pengajuan berdasarkan status
$stmt = $pdo->prepare("
    SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat, js.kode_surat
    FROM pengajuan_surat p 
    JOIN penduduk pd ON p.penduduk_id = pd.id 
    JOIN jenis_surat js ON p.jenis_surat_id = js.id 
    $where 
    ORDER BY p.tanggal_pengajuan DESC
");
$stmt->execute([$status_filter]);
$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total per status
$stmt = $pdo->query("
    SELECT 
        COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
        COUNT(CASE WHEN status = 'siap_ambil' THEN 1 END) as siap_ambil,
        COUNT(CASE WHEN status = 'disetujui' THEN 1 END) as disetujui
    FROM pengajuan_surat
");
$total_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Tangan Surat - <?php echo $site_name; ?></title>
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="ttd_surat.php">
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
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary">
                    <i class="bi bi-pen"></i> Tanda Tangan Surat
                </h2>
                <p class="text-muted">Tanda tangani surat yang sudah diverifikasi admin</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'diproses' ? 'active' : ''; ?>" 
                   href="?status=diproses">
                    <i class="bi bi-clock"></i> Menunggu TTD
                    <span class="badge bg-warning"><?php echo $total_stats['diproses']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'siap_ambil' ? 'active' : ''; ?>" 
                   href="?status=siap_ambil">
                    <i class="bi bi-check-circle"></i> Sudah TTD
                    <span class="badge bg-success"><?php echo $total_stats['siap_ambil']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter === 'disetujui' ? 'active' : ''; ?>" 
                   href="?status=disetujui">
                    <i class="bi bi-file-earmark-check"></i> Disetujui
                    <span class="badge bg-primary"><?php echo $total_stats['disetujui']; ?></span>
                </a>
            </li>
        </ul>

        <!-- Tabel Pengajuan -->
        <div class="card">
            <div class="card-body">
                <?php if (count($pengajuan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Penduduk</th>
                                    <th>Jenis Surat</th>
                                    <th>Kode</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pengajuan as $index => $row): 
                                    $badge_class = [
                                        'diproses' => 'warning',
                                        'siap_ambil' => 'success',
                                        'disetujui' => 'primary'
                                    ][$row['status']] ?? 'secondary';
                                ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_penduduk']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_surat']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $row['kode_surat']; ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php 
                                                $status_text = [
                                                    'diproses' => 'Menunggu TTD',
                                                    'siap_ambil' => 'Siap Ambil',
                                                    'disetujui' => 'Disetujui'
                                                ][$row['status']] ?? $row['status'];
                                                echo $status_text;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($row['status'] === 'diproses'): ?>
                                                    <button type="button" class="btn btn-outline-warning ttd-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#ttdModal"
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-nik="<?php echo htmlspecialchars($row['nik']); ?>"
                                                            data-nama="<?php echo htmlspecialchars($row['nama_penduduk']); ?>"
                                                            data-jenis="<?php echo htmlspecialchars($row['nama_surat']); ?>"
                                                            data-kode="<?php echo $row['kode_surat']; ?>"
                                                            data-tanggal="<?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?>">
                                                        <i class="bi bi-pen"></i> TTD
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-info btn-detail" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nik="<?php echo htmlspecialchars($row['nik']); ?>"
                                                        data-nama="<?php echo htmlspecialchars($row['nama_penduduk']); ?>"
                                                        data-jenis="<?php echo htmlspecialchars($row['nama_surat']); ?>"
                                                        data-tanggal="<?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?>"
                                                        data-keperluan="<?php echo htmlspecialchars($row['keperluan']); ?>"
                                                        data-catatan="<?php echo htmlspecialchars($row['catatan_admin'] ?? ''); ?>"
                                                        data-nomor="<?php echo htmlspecialchars($row['nomor_surat'] ?? ''); ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Tidak ada surat</h4>
                        <p class="text-muted">Tidak ada surat dengan status "<?php echo $status_filter; ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Pengajuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Data Penduduk</h6>
                            <p><strong>NIK:</strong> <span id="detail_nik"></span></p>
                            <p><strong>Nama:</strong> <span id="detail_nama"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Data Pengajuan</h6>
                            <p><strong>Jenis Surat:</strong> <span id="detail_jenis"></span></p>
                            <p><strong>Tanggal:</strong> <span id="detail_tanggal"></span></p>
                            <p><strong>Nomor Surat:</strong> <span id="detail_nomor"></span></p>
                        </div>
                        <div class="col-12 mt-3">
                            <h6>Keperluan / Alasan</h6>
                            <div class="border rounded p-3 bg-light">
                                <p id="detail_keperluan"></p>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <h6>Catatan Admin</h6>
                            <div class="border rounded p-3">
                                <p id="detail_catatan"></p>
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

    <!-- Modal TTD (sama dengan di dashboard) -->
    <div class="modal fade" id="ttdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Tanda Tangani Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="proses_ttd.php">
                    <div class="modal-body">
                        <input type="hidden" name="pengajuan_id" id="ttd_pengajuan_id" value="">
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi Surat</h6>
                            <p><strong>NIK:</strong> <span id="ttd_nik"></span></p>
                            <p><strong>Pemohon:</strong> <span id="ttd_nama"></span></p>
                            <p><strong>Jenis Surat:</strong> <span id="ttd_jenis"></span></p>
                            <p><strong>Kode:</strong> <span id="ttd_kode"></span></p>
                            <p><strong>Tanggal Pengajuan:</strong> <span id="ttd_tanggal"></span></p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ttd_nomor_surat" class="form-label">Nomor Surat *</label>
                            <input type="text" class="form-control" id="ttd_nomor_surat" 
                                   name="nomor_surat" placeholder="Contoh: 001/SK/KD/VI/2024" required>
                            <small class="text-muted">Format: [nomor]/[kode]/KD/[bulan romawi]/[tahun]</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ttd_catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="ttd_catatan" 
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal TTD
            const ttdButtons = document.querySelectorAll('.ttd-btn');
            ttdButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const data = {
                        id: this.getAttribute('data-id'),
                        nik: this.getAttribute('data-nik'),
                        nama: this.getAttribute('data-nama'),
                        jenis: this.getAttribute('data-jenis'),
                        kode: this.getAttribute('data-kode'),
                        tanggal: this.getAttribute('data-tanggal')
                    };
                    
                    document.getElementById('ttd_pengajuan_id').value = data.id;
                    document.getElementById('ttd_nik').textContent = data.nik;
                    document.getElementById('ttd_nama').textContent = data.nama;
                    document.getElementById('ttd_jenis').textContent = data.jenis;
                    document.getElementById('ttd_kode').textContent = data.kode;
                    document.getElementById('ttd_tanggal').textContent = data.tanggal;
                    
                    // Auto-generate nomor surat
                    const bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                    const bulan = bulanRomawi[new Date().getMonth()];
                    const tahun = new Date().getFullYear();
                    const nomorUrut = '001'; // Seharusnya dihitung dari database
                    
                    document.getElementById('ttd_nomor_surat').value = `${nomorUrut}/${data.kode}/KD/${bulan}/${tahun}`;
                    document.getElementById('ttd_catatan').value = '';
                });
            });
            
            // Modal Detail
            const detailButtons = document.querySelectorAll('.btn-detail');
            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const data = {
                        nik: this.getAttribute('data-nik'),
                        nama: this.getAttribute('data-nama'),
                        jenis: this.getAttribute('data-jenis'),
                        tanggal: this.getAttribute('data-tanggal'),
                        keperluan: this.getAttribute('data-keperluan'),
                        catatan: this.getAttribute('data-catatan'),
                        nomor: this.getAttribute('data-nomor')
                    };
                    
                    document.getElementById('detail_nik').textContent = data.nik;
                    document.getElementById('detail_nama').textContent = data.nama;
                    document.getElementById('detail_jenis').textContent = data.jenis;
                    document.getElementById('detail_tanggal').textContent = data.tanggal;
                    document.getElementById('detail_nomor').textContent = data.nomor || 'Belum ada nomor';
                    document.getElementById('detail_keperluan').textContent = data.keperluan;
                    document.getElementById('detail_catatan').textContent = data.catatan || 'Tidak ada catatan';
                });
            });
        });
    </script>
</body>
</html>