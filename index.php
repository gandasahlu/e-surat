<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?> - Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door"></i> <?php echo $site_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#layanan">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_nama']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo $_SESSION['user_role']; ?>/dashboard.php">Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold text-denger">E-Surat Desa Sukarame Baru</h1>
                    <p class="lead">Permudah proses pengurusan surat secara online. Ajukan surat domisili, SKTM, dan surat lainnya tanpa harus antri.</p>
                    <?php if (!is_logged_in()): ?>
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg me-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
                            </a>
                            <a href="#layanan" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-info-circle"></i> Pelajari Lebih Lanjut
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <a href="<?php echo $_SESSION['user_role']; ?>/dashboard.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-speedometer2"></i> Ke Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <img src="aset/OIP.jpeg"
                         alt="Desa Digital" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Layanan Section -->
    <section id="layanan" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Layanan Surat Yang Tersedia</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-house-door text-primary" style="font-size: 3rem;"></i>
                            <h4 class="card-title mt-3">Surat Domisili</h4>
                            <p class="card-text">Surat keterangan tempat tinggal untuk keperluan administrasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                            <h4 class="card-title mt-3">Surat SKTM</h4>
                            <p class="card-text">Surat Keterangan Tidak Mampu untuk berbagai keperluan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-briefcase text-warning" style="font-size: 3rem;"></i>
                            <h4 class="card-title mt-3">Surat Usaha</h4>
                            <p class="card-text">Surat keterangan usaha untuk keperluan bisnis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Tentang Sistem e-Surat</h2>
            <div class="row">
                <div class="col-md-6">
                    <h4>Manfaat Sistem</h4>
                    <ul>
                        <li>Proses pengajuan surat lebih cepat</li>
                        <li>Tidak perlu antri di kantor desa</li>
                        <li>Dapat memantau status pengajuan online</li>
                        <li>Surat bisa diambil kapan saja</li>
                        <li>Dokumen tersimpan rapi secara digital</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4>Cara Menggunakan</h4>
                    <ol>
                        <li>Login dengan NIK dan password</li>
                        <li>Pilih jenis surat yang dibutuhkan</li>
                        <li>Isi formulir pengajuan</li>
                        <li>Tunggu verifikasi dari admin</li>
                        <li>Ambil surat setelah disetujui</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo $site_name; ?></h5>
                    <p>Sistem pengajuan surat desa secara online untuk memudahkan masyarakat.</p>
                </div>
                <div class="col-md-3">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Beranda</a></li>
                        <li><a href="#layanan" class="text-white-50">Layanan</a></li>
                        <li><a href="#tentang" class="text-white-50">Tentang</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Kontak</h5>
                    <p><i class="bi bi-geo-alt"></i> Kantor Desa, Jl.Aek Nabara</p>
                    <p><i class="bi bi-telephone"></i> 082276465029</p>
                </div>
            </div>
            <hr class="bg-light">
            <p class="text-center mb-0">&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Semua hak dilindungi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>