<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_name; ?> - Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a2530 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .hero-section {
            background: linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9)), 
                        url('aset/OIP.jpeg') center/cover no-repeat;
            padding: 80px 0;
            position: relative;
        }
        
        .hero-section h1 {
            color: var(--primary-color);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        
        .hero-section .lead {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
        }
        
        .hero-image-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .hero-image-container:hover {
            transform: translateY(-5px);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .card-1 .card-icon {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .card-2 .card-icon {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .card-3 .card-icon {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 40px;
            text-align: center;
            color: var(--primary-color);
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .about-item, .step-item {
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .about-item i, .step-item i {
            color: var(--secondary-color);
            margin-right: 10px;
        }
        
        .footer {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a2530 100%);
            color: white;
            padding-top: 50px;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .contact-info i {
            width: 20px;
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--secondary-color), #2980b9);
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            padding: 10px 26px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        .stats-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        
        .stat-box {
            text-align: center;
            padding: 30px 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 50px 0;
                text-align: center;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door me-2"></i><?php echo $site_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#layanan">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i><?php echo $_SESSION['user_nama']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo $_SESSION['user_role']; ?>/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold">E-Surat Desa Sukarame Baru</h1>
                    <p class="lead">Permudah proses pengurusan surat secara online. Ajukan surat domisili, SKTM, dan surat lainnya tanpa harus antri di kantor desa.</p>
                    
                    <?php if (!is_logged_in()): ?>
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login Sekarang
                            </a>
                            <a href="#layanan" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-info-circle me-2"></i> Lihat Layanan
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <a href="<?php echo $_SESSION['user_role']; ?>/dashboard.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-speedometer2 me-2"></i> Ke Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mt-5 pt-3">
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="fw-bold text-primary">500+</h4>
                                <p class="text-muted">Warga Terdaftar</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="fw-bold text-success">1,200+</h4>
                                <p class="text-muted">Surat Diproses</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="fw-bold text-warning">24 Jam</h4>
                                <p class="text-muted">Layanan Online</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img src="aset/OIP.jpeg" alt="Desa Sukarame Baru" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-box">
                        <div class="stat-number">4</div>
                        <div class="stat-label">Jenis Layanan</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-box">
                        <div class="stat-number">15</div>
                        <div class="stat-label">Menit Rata-Rata</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-box">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Kepuasan Warga</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-box">
                        <div class="stat-number">7</div>
                        <div class="stat-label">Hari Kerja</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Layanan Section -->
    <section id="layanan" class="py-5">
        <div class="container">
            <h2 class="section-title">Layanan Surat Yang Tersedia</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-1">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="bi bi-house-door"></i>
                            </div>
                            <h4 class="card-title fw-bold">Surat Domisili</h4>
                            <p class="card-text">Surat keterangan tempat tinggal untuk keperluan administrasi seperti pendaftaran sekolah, pembuatan KTP, dan lainnya.</p>
                            <div class="mt-4">
                                <span class="badge bg-primary">Waktu: 1-2 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-2">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4 class="card-title fw-bold">Surat SKTM</h4>
                            <p class="card-text">Surat Keterangan Tidak Mampu untuk berbagai keperluan seperti bantuan sosial, pendaftaran sekolah, atau kesehatan.</p>
                            <div class="mt-4">
                                <span class="badge bg-success">Waktu: 2-3 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 card-3">
                        <div class="card-body text-center p-4">
                            <div class="card-icon">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <h4 class="card-title fw-bold">Surat Usaha</h4>
                            <p class="card-text">Surat keterangan usaha untuk keperluan bisnis, perizinan, pengajuan kredit, dan keperluan administrasi lainnya.</p>
                            <div class="mt-4">
                                <span class="badge bg-warning">Waktu: 3-4 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="#tentang" class="btn btn-outline-primary">Lihat Semua Layanan <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Tentang Sistem e-Surat</h2>
            <div class="row">
                <div class="col-lg-6 mb-5">
                    <h4 class="mb-4 fw-bold text-primary">Manfaat Sistem</h4>
                    <div class="about-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="fw-bold">Proses pengajuan surat lebih cepat</span>
                        <p class="mt-2">Pengajuan surat dapat dilakukan kapan saja tanpa harus menunggu jam kerja kantor desa.</p>
                    </div>
                    <div class="about-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="fw-bold">Tidak perlu antri di kantor desa</span>
                        <p class="mt-2">Semua proses dilakukan secara online, menghemat waktu dan tenaga warga.</p>
                    </div>
                    <div class="about-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="fw-bold">Pantau status pengajuan online</span>
                        <p class="mt-2">Warga dapat memantau status pengajuan surat mereka secara real-time melalui sistem.</p>
                    </div>
                    <div class="about-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span class="fw-bold">Surat bisa diambil kapan saja</span>
                        <p class="mt-2">Setelah surat selesai diproses, warga dapat mengambilnya sesuai waktu yang diinginkan.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h4 class="mb-4 fw-bold text-primary">Cara Menggunakan</h4>
                    <div class="step-item">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-3">1</span>
                            <span class="fw-bold">Login dengan NIK dan password</span>
                        </div>
                        <p>Gunakan NIK dan password yang telah didaftarkan untuk masuk ke sistem.</p>
                    </div>
                    <div class="step-item">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-3">2</span>
                            <span class="fw-bold">Pilih jenis surat yang dibutuhkan</span>
                        </div>
                        <p>Pilih layanan surat yang sesuai dengan kebutuhan Anda dari daftar yang tersedia.</p>
                    </div>
                    <div class="step-item">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-3">3</span>
                            <span class="fw-bold">Isi formulir pengajuan</span>
                        </div>
                        <p>Isi data yang diperlukan pada formulir elektronik dengan lengkap dan benar.</p>
                    </div>
                    <div class="step-item">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-3">4</span>
                            <span class="fw-bold">Tunggu verifikasi dari admin</span>
                        </div>
                        <p>Admin akan memverifikasi data dan mengonfirmasi via WhatsApp atau email.</p>
                    </div>
                    <div class="step-item">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-3">5</span>
                            <span class="fw-bold">Ambil surat setelah disetujui</span>
                        </div>
                        <p>Setelah disetujui, Anda dapat mengambil surat di kantor desa atau melalui kurir.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?php echo $site_name; ?></h5>
                    <p class="mt-3" style="color: rgba(255,255,255,0.8);">Sistem pengajuan surat desa secara online untuk memudahkan masyarakat Desa Sukarame Baru dalam mengurus surat menyurat secara efektif dan efisien.</p>
                    <div class="mt-4">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-whatsapp fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-envelope fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php">Beranda</a></li>
                        <li class="mb-2"><a href="#layanan">Layanan</a></li>
                        <li class="mb-2"><a href="#tentang">Tentang</a></li>
                        <li class="mb-2"><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Layanan</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#">Surat Domisili</a></li>
                        <li class="mb-2"><a href="#">Surat SKTM</a></li>
                        <li class="mb-2"><a href="#">Surat Usaha</a></li>
                        <li class="mb-2"><a href="#">Surat Keterangan Lainnya</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Kontak</h5>
                    <div class="contact-info">
                        <p class="mb-3">
                            <i class="bi bi-geo-alt"></i> 
                            <span>Kantor Desa Sukarame Baru, Jl. Aek Nabara</span>
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-telephone"></i> 
                            <span>0822-7646-5029</span>
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-clock"></i> 
                            <span>Senin - Jumat: 08:00 - 16:00</span>
                        </p>
                        <p>
                            <i class="bi bi-envelope"></i> 
                            <span>desa.sukaramebaru@email.com</span>
                        </p>
                    </div>
                </div>
            </div>
            <hr class="bg-light my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Dikembangkan oleh <a href="#" class="text-white">Tim IT Desa Sukarame Baru</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            } else {
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>