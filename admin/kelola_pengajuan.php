<?php
require_once '../config.php';
require_role('admin');

// Filter status
$status_filter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Jumlah data per halaman
$offset = ($page - 1) * $limit;

// Query dengan filter
$where = "";
$params = [];

if ($status_filter !== 'all') {
    $where = "WHERE p.status = ?";
    $params[] = $status_filter;
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM pengajuan_surat p";
if ($where) {
    $count_query .= " $where";
}
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_data = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

// Query data dengan pagination
$query = "
    SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat 
    FROM pengajuan_surat p 
    JOIN penduduk pd ON p.penduduk_id = pd.id 
    JOIN jenis_surat js ON p.jenis_surat_id = js.id 
";

if ($where) {
    $query .= " $where ";
}

$query .= " ORDER BY p.tanggal_pengajuan DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung jumlah per status untuk badges
$statuses = ['menunggu', 'diproses', 'disetujui', 'ditolak', 'siap_ambil'];
$status_counts = [];
foreach ($statuses as $status) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pengajuan_surat WHERE status = ?");
    $stmt->execute([$status]);
    $status_counts[$status] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Warna untuk status
$status_colors = [
    'menunggu' => 'warning',
    'diproses' => 'info',
    'disetujui' => 'success',
    'ditolak' => 'danger',
    'siap_ambil' => 'primary'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengajuan - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .pagination {
            margin-bottom: 0;
        }
        .no-data {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-house-door"></i> <?php echo htmlspecialchars($site_name); ?>
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
                        <a class="nav-link active" href="kelola_pengajuan.php">
                            <i class="bi bi-files"></i> Pengajuan Surat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_penduduk.php">
                            <i class="bi bi-people"></i> Data Penduduk
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
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
        <?php if (function_exists('display_flash_message')) display_flash_message(); ?>
        
        <!-- Header dan Filter -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary">
                    <i class="bi bi-files"></i> Kelola Pengajuan Surat
                </h2>
                <p class="text-muted">Verifikasi dan kelola pengajuan surat dari penduduk</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel"></i> Filter Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?status=all">
                        <i class="bi bi-list"></i> Semua Status
                        <span class="badge bg-secondary float-end"><?php echo $total_data; ?></span>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php foreach ($statuses as $status): ?>
                    <li>
                        <a class="dropdown-item" href="?status=<?php echo $status; ?>">
                            <i class="bi bi-circle-fill text-<?php echo $status_colors[$status]; ?>"></i> 
                            <?php echo ucfirst($status); ?>
                            <span class="badge bg-<?php echo $status_colors[$status]; ?> float-end">
                                <?php echo $status_counts[$status]; ?>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Filter Badges -->
        <div class="mb-3">
            <a href="?status=all" class="badge bg-secondary text-decoration-none me-1 mb-1 <?php echo $status_filter == 'all' ? 'border border-primary' : ''; ?>">
                Semua <span class="badge bg-light text-dark"><?php echo $total_data; ?></span>
            </a>
            <?php foreach ($statuses as $status): ?>
                <a href="?status=<?php echo $status; ?>" 
                   class="badge bg-<?php echo $status_colors[$status]; ?> text-decoration-none me-1 mb-1 <?php echo $status_filter == $status ? 'border border-white' : ''; ?>">
                    <?php echo ucfirst($status); ?> 
                    <span class="badge bg-light text-dark"><?php echo $status_counts[$status]; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Statistik Singkat -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="row text-center">
                            <?php foreach ($statuses as $status): ?>
                            <div class="col">
                                <small class="text-muted d-block"><?php echo ucfirst($status); ?></small>
                                <strong class="text-<?php echo $status_colors[$status]; ?>">
                                    <?php echo $status_counts[$status]; ?>
                                </strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pengajuan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pengajuan</h5>
                <span class="badge bg-info">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>NIK</th>
                                <th>Penduduk</th>
                                <th>Jenis Surat</th>
                                <th width="120">Tanggal</th>
                                <th width="100">Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pengajuan) > 0): ?>
                                <?php 
                                $start_number = ($page - 1) * $limit + 1;
                                foreach ($pengajuan as $index => $row): 
                                    $badge_class = $status_colors[$row['status']] ?? 'secondary';
                                ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $start_number + $index; ?></td>
                                        <td><code><?php echo htmlspecialchars($row['nik']); ?></code></td>
                                        <td><?php echo htmlspecialchars($row['nama_penduduk']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_surat']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_class; ?> status-badge">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nik="<?php echo htmlspecialchars($row['nik']); ?>"
                                                        data-nama="<?php echo htmlspecialchars($row['nama_penduduk']); ?>"
                                                        data-surat="<?php echo htmlspecialchars($row['nama_surat']); ?>"
                                                        data-tanggal="<?php echo date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])); ?>"
                                                        data-status="<?php echo $row['status']; ?>"
                                                        data-keperluan="<?php echo htmlspecialchars($row['keperluan']); ?>"
                                                        data-berkas="<?php echo htmlspecialchars($row['berkas_pendukung'] ?? ''); ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if ($row['status'] == 'menunggu'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#prosesModal"
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-nama="<?php echo htmlspecialchars($row['nama_penduduk']); ?>"
                                                            data-surat="<?php echo htmlspecialchars($row['nama_surat']); ?>">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                <?php elseif ($row['status'] == 'diproses'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#selesaiModal"
                                                            data-id="<?php echo $row['id']; ?>">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php elseif ($row['status'] == 'disetujui'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="markAsReady(<?php echo $row['id']; ?>)">
                                                        <i class="bi bi-file-earmark-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 no-data">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <h5 class="mt-3 text-muted">Tidak ada data pengajuan</h5>
                                        <p class="text-muted">Belum ada pengajuan surat dengan status ini</p>
                                        <a href="?status=all" class="btn btn-outline-primary">
                                            <i class="bi bi-arrow-left"></i> Lihat Semua Pengajuan
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?php echo $status_filter; ?>&page=<?php echo $page + 1; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail (Single Modal untuk semua data) -->
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
                            <h6><i class="bi bi-person"></i> Data Penduduk</h6>
                            <p><strong>NIK:</strong> <span id="detail-nik"></span></p>
                            <p><strong>Nama:</strong> <span id="detail-nama"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-file-text"></i> Data Pengajuan</h6>
                            <p><strong>Jenis Surat:</strong> <span id="detail-surat"></span></p>
                            <p><strong>Tanggal:</strong> <span id="detail-tanggal"></span></p>
                            <p><strong>Status:</strong> <span id="detail-status"></span></p>
                        </div>
                        <div class="col-12 mt-3">
                            <h6><i class="bi bi-card-text"></i> Keperluan / Alasan</h6>
                            <div class="border rounded p-3 bg-light">
                                <p id="detail-keperluan" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-12 mt-3" id="detail-berkas-section" style="display: none;">
                            <h6><i class="bi bi-paperclip"></i> Berkas Pendukung</h6>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Proses (Single Modal) -->
    <div class="modal fade" id="prosesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Proses Pengajuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="proses_verifikasi.php">
                    <div class="modal-body">
                        <input type="hidden" name="pengajuan_id" id="proses-id">
                        <div class="mb-3">
                            <p>Anda akan memproses pengajuan dari:</p>
                            <div class="alert alert-info">
                                <strong id="proses-nama"></strong><br>
                                <small id="proses-surat"></small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" 
                                       id="action-setuju" value="setuju" checked>
                                <label class="form-check-label" for="action-setuju">
                                    <i class="bi bi-check-circle text-success"></i> Setujui dan Lanjut ke Kepala Desa
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" 
                                       id="action-tolak" value="tolak">
                                <label class="form-check-label" for="action-tolak">
                                    <i class="bi bi-x-circle text-danger"></i> Tolak Pengajuan
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                      placeholder="Berikan catatan untuk penduduk..."></textarea>
                            <small class="text-muted">Catatan ini akan dikirim ke penduduk via email/SMS</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle modal detail
        document.getElementById('detailModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var modal = this;
            
            modal.querySelector('#detail-nik').textContent = button.getAttribute('data-nik');
            modal.querySelector('#detail-nama').textContent = button.getAttribute('data-nama');
            modal.querySelector('#detail-surat').textContent = button.getAttribute('data-surat');
            modal.querySelector('#detail-tanggal').textContent = button.getAttribute('data-tanggal');
            modal.querySelector('#detail-keperluan').textContent = button.getAttribute('data-keperluan');
            
            var status = button.getAttribute('data-status');
            var statusColors = {
                'menunggu': 'warning',
                'diproses': 'info',
                'disetujui': 'success',
                'ditolak': 'danger',
                'siap_ambil': 'primary'
            };
            
            var statusBadge = `<span class="badge bg-${statusColors[status]}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
            modal.querySelector('#detail-status').innerHTML = statusBadge;
            
            // Handle berkas pendukung
            var berkas = button.getAttribute('data-berkas');
            var berkasSection = modal.querySelector('#detail-berkas-section');
            var berkasLink = modal.querySelector('#detail-berkas-link');
            
            if (berkas && berkas.trim() !== '') {
                berkasSection.style.display = 'block';
                berkasLink.href = '../uploads/' + berkas;
            } else {
                berkasSection.style.display = 'none';
            }
        });
        
        // Handle modal proses
        document.getElementById('prosesModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var modal = this;
            
            modal.querySelector('#proses-id').value = button.getAttribute('data-id');
            modal.querySelector('#proses-nama').textContent = button.getAttribute('data-nama');
            modal.querySelector('#proses-surat').textContent = button.getAttribute('data-surat');
        });
        
        // Handle modal selesai
        document.getElementById('selesaiModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            document.getElementById('selesai-id').value = button.getAttribute('data-id');
        });
        
        // Fungsi untuk tandai siap ambil
        function markAsReady(pengajuanId) {
            if (confirm('Tandai surat ini sebagai siap diambil oleh penduduk?')) {
                fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'pengajuan_id=' + pengajuanId + '&status=siap_ambil'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Status berhasil diperbarui!');
                        location.reload();
                    } else {
                        alert('Gagal memperbarui status: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
            }
        }
        
        // Auto-focus pada input catatan saat modal proses dibuka
        document.getElementById('prosesModal').addEventListener('shown.bs.modal', function() {
            document.getElementById('catatan').focus();
        });
    </script>
</body>
</html>