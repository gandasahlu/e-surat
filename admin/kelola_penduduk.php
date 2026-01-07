<?php
require_once '../config.php';
require_role('admin');

$page_title = 'Kelola Data Penduduk';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah penduduk memiliki pengajuan
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_surat WHERE penduduk_id = ?");
    $stmt->execute([$id]);
    $has_pengajuan = $stmt->fetchColumn();
    
    if ($has_pengajuan > 0) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Tidak dapat menghapus penduduk yang memiliki pengajuan surat!'
        ];
    } else {
        $stmt = $pdo->prepare("DELETE FROM penduduk WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Data penduduk berhasil dihapus!'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Gagal menghapus data penduduk!'
            ];
        }
    }
    header('Location: kelola_penduduk.php');
    exit;
}

// Handle status change
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    
    $stmt = $pdo->prepare("UPDATE penduduk SET status = NOT status WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Status penduduk berhasil diubah!'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Gagal mengubah status penduduk!'
        ];
    }
    header('Location: kelola_penduduk.php');
    exit;
}

// Search functionality
$search = '';
$where = '';
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where = "WHERE nama LIKE ? OR nik LIKE ? OR alamat LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk $where");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get penduduk data
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM pengajuan_surat WHERE penduduk_id = p.id) as total_pengajuan
    FROM penduduk p 
    $where 
    ORDER BY p.nama ASC 
    LIMIT ? OFFSET ?
");
$search_params = array_merge($params, [$limit, $offset]);
$stmt->execute($search_params);
$penduduk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
                        <a class="nav-link" href="kelola_pengajuan.php">
                            <i class="bi bi-files"></i> Pengajuan Surat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kelola_penduduk.php">
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
        
        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-primary">
                    <i class="bi bi-people"></i> <?php echo $page_title; ?>
                </h2>
                <p class="text-muted">Total: <strong><?php echo $total_records; ?></strong> penduduk</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPendudukModal">
                    <i class="bi bi-person-plus"></i> Tambah Penduduk
                </button>
                <a href="cetak_penduduk.php" class="btn btn-success" target="_blank">
                    <i class="bi bi-printer"></i> Cetak
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Cari berdasarkan nama, NIK, atau alamat...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form>
                <?php if($search): ?>
                <div class="mt-2">
                    <small class="text-muted">
                        Menampilkan hasil pencarian: "<?php echo htmlspecialchars($search); ?>"
                        <a href="kelola_penduduk.php" class="text-danger ms-2">
                            <i class="bi bi-x-circle"></i> Hapus pencarian
                        </a>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="pendudukTable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Tempat/Tgl Lahir</th>
                                <th>Alamat</th>
                                <th>No. Telp</th>
                                <th>Total Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($penduduk_list) > 0): ?>
                                <?php foreach($penduduk_list as $index => $penduduk): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <code><?php echo $penduduk['nik']; ?></code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($penduduk['nama']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($penduduk['tempat_lahir']); ?><br>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($penduduk['tanggal_lahir'])); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($penduduk['alamat'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo $penduduk['no_telp'] ?: '-'; ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $penduduk['total_pengajuan']; ?>
                                            </span>
                                        </td>
                                       
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal<?php echo $penduduk['id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $penduduk['id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                
                                                <a href="?delete=<?php echo $penduduk['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Hapus data penduduk? Data yang dihapus tidak dapat dikembalikan!')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-1"></i><br>
                                            <h5>Belum ada data penduduk</h5>
                                            <p>Klik tombol "Tambah Penduduk" untuk menambahkan data</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                &laquo; Sebelumnya
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                Selanjutnya &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
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
                <form method="POST" action="proses_penduduk.php?action=tambah">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIK * <small class="text-muted">(16 digit)</small></label>
                            <input type="text" class="form-control" name="nik" required 
                                   pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                                   placeholder="Contoh: 3273010101010001">
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
                                <input type="date" class="form-control" name="tanggal_lahir" required 
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon/HP</label>
                            <input type="tel" class="form-control" name="no_telp" 
                                   pattern="[0-9]{10,13}" title="10-13 digit angka">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Default *</label>
                            <input type="text" class="form-control" name="password" value="123456" required>
                            <small class="text-muted">Password default untuk login pertama kali</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals for Detail and Edit -->
    <?php foreach($penduduk_list as $penduduk): ?>
    
    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal<?php echo $penduduk['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-person-badge"></i> Detail Penduduk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle-lg bg-primary text-white display-4 mb-3 mx-auto">
                            <?php echo strtoupper(substr($penduduk['nama'], 0, 1)); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($penduduk['nama']); ?></h4>
                        <span class="badge bg-<?php echo $penduduk['status'] ? 'success' : 'secondary'; ?>">
                            <?php echo $penduduk['status'] ? 'Aktif' : 'Nonaktif'; ?>
                        </span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>NIK:</strong><br>
                            <code><?php echo $penduduk['nik']; ?></code>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>No. Telepon:</strong><br>
                            <span class="text-muted"><?php echo $penduduk['no_telp'] ?: '-'; ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tempat Lahir:</strong><br>
                            <span class="text-muted"><?php echo htmlspecialchars($penduduk['tempat_lahir']); ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tanggal Lahir:</strong><br>
                            <span class="text-muted"><?php echo date('d/m/Y', strtotime($penduduk['tanggal_lahir'])); ?></span>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Alamat:</strong><br>
                            <span class="text-muted"><?php echo htmlspecialchars($penduduk['alamat']); ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Total Pengajuan Surat:</strong><br>
                            <span class="badge bg-info fs-6"><?php echo $penduduk['total_pengajuan']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal<?php echo $penduduk['id']; ?>"
                            data-bs-dismiss="modal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal<?php echo $penduduk['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Data Penduduk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="proses_penduduk.php?action=edit&id=<?php echo $penduduk['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIK *</label>
                            <input type="text" class="form-control" name="nik" required 
                                   pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                                   value="<?php echo $penduduk['nik']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="nama" required
                                   value="<?php echo htmlspecialchars($penduduk['nama']); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" name="tempat_lahir" required
                                       value="<?php echo htmlspecialchars($penduduk['tempat_lahir']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" name="tanggal_lahir" required
                                       value="<?php echo $penduduk['tanggal_lahir']; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($penduduk['alamat']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon/HP</label>
                            <input type="tel" class="form-control" name="no_telp" 
                                   pattern="[0-9]{10,13}" title="10-13 digit angka"
                                   value="<?php echo $penduduk['no_telp']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="1" <?php echo $penduduk['status'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$penduduk['status'] ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reset Password</label>
                            <input type="text" class="form-control" name="password" 
                                   placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pendudukTable').DataTable({
                "paging": false,
                "searching": false,
                "info": false,
                "ordering": true,
                "language": {
                    "emptyTable": "Tidak ada data penduduk",
                    "zeroRecords": "Tidak ada data yang cocok dengan pencarian"
                }
            });
        });
    </script>
</body>
</html>