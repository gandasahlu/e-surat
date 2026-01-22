<?php
require_once 'config.php';


if (is_logged_in()) {
    redirect($_SESSION['user_role'] . '/dashboard.php');
}


$error = '';
$role = 'penduduk'; // default role

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'penduduk';
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($identifier) || empty($password)) {
        $error = 'NIK/Username dan password harus diisi!';
    } else {
        try {
            if ($role === 'penduduk') {
                // Login penduduk dengan NIK
                $stmt = $pdo->prepare("SELECT * FROM penduduk WHERE nik = ?");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nik'] = $user['nik'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role'] = 'penduduk';
                    
                    flash_message('success', 'Login berhasil! Selamat datang ' . $user['nama']);
                    redirect('penduduk/dashboard.php');
                } else {
                    $error = 'NIK atau password salah!';
                }
            } elseif ($role === 'admin') {
                // Login admin
                $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role'] = 'admin';
                    
                    flash_message('success', 'Login berhasil! Selamat datang Admin');
                    redirect('admin/dashboard.php');
                } else {
                    $error = 'Username atau password salah!';
                }
            } elseif ($role === 'kepala_desa') {
                // Login kepala desa
                $stmt = $pdo->prepare("SELECT * FROM kepala_desa WHERE username = ?");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role'] = 'kepala_desa';
                    
                    flash_message('success', 'Login berhasil! Selamat datang ' . $user['nama']);
                    redirect('kepala_desa/dashboard.php');
                } else {
                    $error = 'Username atau password salah!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(243, 7, 7, 0.1);
            background: yellow;
        }
        .role-selector .nav-link {
            color: #06509a;
            font-weight: 500;
        }
        .role-selector .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h2 class="text-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </h2>
                <p class="text-muted">Masuk ke sistem e-Surat Desa</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Role Selector -->
            <ul class="nav nav-tabs role-selector mb-4" id="roleTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $role === 'penduduk' ? 'active' : ''; ?>" 
                            id="penduduk-tab" data-bs-toggle="tab" data-bs-target="#penduduk" type="button">
                        <i class="bi bi-person"></i> Penduduk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $role === 'admin' ? 'active' : ''; ?>" 
                            id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button">
                        <i class="bi bi-gear"></i> Admin
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $role === 'kepala_desa' ? 'active' : ''; ?>" 
                            id="kepala-tab" data-bs-toggle="tab" data-bs-target="#kepala" type="button">
                        <i class="bi bi-person-badge"></i> Kepala Desa
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Penduduk Login -->
                <div class="tab-pane fade <?php echo $role === 'penduduk' ? 'show active' : ''; ?>" 
                     id="penduduk" role="tabpanel">
                    <form method="POST">
                        <input type="hidden" name="role" value="penduduk">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="identifier" 
                                   placeholder="Masukkan NIK" required>
                            <div class="form-text">Gunakan NIK Anda sebagai username</div>
                        </div>
                        <div class="mb-3">
                            <label for="password_penduduk" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_penduduk" 
                                   name="password" placeholder="Masukkan password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Login sebagai Penduduk
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Admin Login -->
                <div class="tab-pane fade <?php echo $role === 'admin' ? 'show active' : ''; ?>" 
                     id="admin" role="tabpanel">
                    <form method="POST">
                        <input type="hidden" name="role" value="admin">
                        <div class="mb-3">
                            <label for="username_admin" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username_admin" 
                                   name="identifier" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_admin" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_admin" 
                                   name="password" placeholder="Masukkan password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-gear"></i> Login sebagai Admin
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Kepala Desa Login -->
                <div class="tab-pane fade <?php echo $role === 'kepala_desa' ? 'show active' : ''; ?>" 
                     id="kepala" role="tabpanel">
                    <form method="POST">
                        <input type="hidden" name="role" value="kepala_desa">
                        <div class="mb-3">
                            <label for="username_kepala" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username_kepala" 
                                   name="identifier" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_kepala" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_kepala" 
                                   name="password" placeholder="Masukkan password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-badge"></i> Login sebagai Kepala Desa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-2">Belum punya akun?</p>
                <p>Untuk penduduk baru, silahkan datang ke kantor desa untuk registrasi.</p>
                <p>akun demo</p>
                <p>penduduk nik:11111111111 pw:123456</p>
                <P>username: admin pw: admin123</P>
                <p>username:kepaladesa pw:kepala123</p>

                
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set active tab based on form submission
        const urlParams = new URLSearchParams(window.location.search);
        const roleParam = urlParams.get('role');
        if (roleParam) {
            const tab = document.querySelector(`#${roleParam}-tab`);
            if (tab) {
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
            }
        }
    </script>
</body>
</html>