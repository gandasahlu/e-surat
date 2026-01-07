<?php
require_once '../config.php';
require_role('penduduk');

$user_id = $_SESSION['user_id'];

// Ambil jenis surat
$stmt = $pdo->query("SELECT * FROM jenis_surat ORDER BY nama_surat");
$jenis_surat = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_surat_id = $_POST['jenis_surat'];
    $keperluan = $_POST['keperluan'];
    $berkas = $_FILES['berkas_pendukung'];
    
    // Validasi
    if (empty($jenis_surat_id) || empty($keperluan)) {
        flash_message('danger', 'Harap isi semua field yang wajib diisi!');
    } else {
        try {
            // Handle file upload
            
            // Insert pengajuan
            $stmt = $pdo->prepare("
                INSERT INTO pengajuan_surat 
                (penduduk_id, jenis_surat_id, keperluan, berkas_pendukung, status) 
                VALUES (?, ?, ?, ?, 'menunggu')
            ");
            $stmt->execute([$user_id, $jenis_surat_id, $keperluan, $berkas_filename]);
            
            flash_message('success', 'Pengajuan surat berhasil dikirim! Menunggu verifikasi admin.');
            redirect('dashboard.php');
            
        } catch (PDOException $e) {
            flash_message('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Surat - <?php echo $site_name; ?></title>
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
                        <a class="nav-link active" href="ajukan_surat.php">
                            <i class="bi bi-plus-circle"></i> Ajukan Surat
                        </a>
                    </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Ajukan Surat Baru
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php display_flash_message(); ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="jenis_surat" class="form-label">Jenis Surat *</label>
                                <select class="form-select" id="jenis_surat" name="jenis_surat" required>
                                    <option value="">Pilih Jenis Surat</option>
                                    <?php foreach ($jenis_surat as $js): ?>
                                        <option value="<?php echo $js['id']; ?>">
                                            <?php echo $js['nama_surat']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="keperluan" class="form-label">Keperluan / Alasan *</label>
                                <textarea class="form-control" id="keperluan" name="keperluan" 
                                          rows="4" placeholder="Jelaskan keperluan pengajuan surat" required></textarea>
                                <div class="form-text">
                                    Contoh: Untuk mendaftar sekolah, keperluan pekerjaan, dll.
                                </div>
                            </div>
                            
                            
                            
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Informasi Penting:</h6>
                                <ul class="mb-0">
                                    <li>Pastikan data yang diisi sesuai dengan dokumen asli</li>
                                    <li>Pengajuan akan diverifikasi oleh admin dalam 1-2 hari kerja</li>
                                    <li>Status pengajuan dapat dipantau di halaman dashboard</li>
                                    <li>Surat yang sudah disetujui dapat diambil di kantor desa</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Kirim Pengajuan
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>