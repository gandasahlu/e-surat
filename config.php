
<?php
session_start();

// Koneksi database
$host = 'localhost';
$dbname = 'esurat_desa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Konfigurasi
$site_name = "Sistem e-Surat Desa";
$site_url = "http://localhost/e-surat-desa/";

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan
function flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fungsi untuk menampilkan flash message
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $message = $_SESSION['flash_message']['message'];
        
        // Mapping type Bootstrap alert
        $bootstrap_types = [
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'danger' => 'danger'
        ];
        
        $bootstrap_type = $bootstrap_types[$type] ?? 'info';
        
        echo "<div class='alert alert-$bootstrap_type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        
        unset($_SESSION['flash_message']);
    }
}

// Fungsi sanitasi input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk generate nomor surat
function generate_nomor_surat($kode_surat) {
    $bulan_romawi = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
    ];
    
    $bulan = $bulan_romawi[date('n')];
    $tahun = date('Y');
    
    // Ambil nomor urut terakhir
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pengajuan_surat 
                          WHERE YEAR(tanggal_pengajuan) = ? AND MONTH(tanggal_pengajuan) = ?");
    $stmt->execute([date('Y'), date('n')]);
    $result = $stmt->fetch();
    $nomor_urut = str_pad($result['total'] + 1, 3, '0', STR_PAD_LEFT);
    
    return "$nomor_urut/$kode_surat/KD/$bulan/$tahun";
}

// Fungsi untuk format tanggal Indonesia
function format_tanggal_indonesia($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00') {
        return '-';
    }
    
    $hari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 
        'Kamis', 'Jumat', 'Sabtu'
    ];
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $hari_ini = $hari[date('w', $timestamp)];
    $tanggal_angka = date('j', $timestamp);
    $bulan_ini = $bulan[date('n', $timestamp)];
    $tahun_ini = date('Y', $timestamp);
    
    return "$hari_ini, $tanggal_angka $bulan_ini $tahun_ini";
}

// Cek login
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Cek role
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_penduduk() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'penduduk';
}

function is_kepala_desa() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'kepala_desa';
}

// Cek akses berdasarkan role
function check_access($allowed_roles) {
    if (!is_logged_in()) {
        flash_message('warning', 'Silahkan login terlebih dahulu.');
        redirect('login.php');
    }
    
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        flash_message('danger', 'Anda tidak memiliki akses ke halaman ini.');
        redirect($_SESSION['user_role'] . '/dashboard.php');
    }
}

// Logout jika tidak sesuai role
function require_role($role) {
    if (!is_logged_in()) {
        flash_message('warning', 'Silahkan login terlebih dahulu.');
        redirect('login.php');
    }
    
    $current_role = $_SESSION['user_role'];
    if ($current_role !== $role) {
        flash_message('danger', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect("$current_role/dashboard.php");
    }
}

// Fungsi untuk upload file
function upload_file($file, $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error upload file.'];
    }
    
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 2MB.'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Format file tidak didukung.'];
    }
    
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file['name']);
    $upload_dir = 'uploads/';
    
    // Buat folder uploads jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $destination = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Gagal menyimpan file.'];
    }
}

// Fungsi untuk mendapatkan status badge
function get_status_badge($status) {
    $status_classes = [
        'menunggu' => 'warning',
        'diproses' => 'info',
        'disetujui' => 'success',
        'ditolak' => 'danger',
        'siap_ambil' => 'primary'
    ];
    
    $class = $status_classes[$status] ?? 'secondary';
    $text = ucfirst(str_replace('_', ' ', $status));
    
    return "<span class='badge bg-$class'>$text</span>";
}

// Fungsi untuk cek jika penduduk sudah terdaftar
function is_penduduk_registered($nik) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM penduduk WHERE nik = ?");
    $stmt->execute([$nik]);
    $result = $stmt->fetch();
    return $result['total'] > 0;
}

// Fungsi untuk mendapatkan data penduduk by NIK
function get_penduduk_by_nik($nik) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM penduduk WHERE nik = ?");
    $stmt->execute([$nik]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan data admin by username
function get_admin_by_username($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan data kepala_desa by username
function get_kepala_desa_by_username($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM kepala_desa WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan jenis surat
function get_jenis_surat() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM jenis_surat ORDER BY nama_surat");
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan pengajuan by ID
function get_pengajuan_by_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, pd.nama as nama_penduduk, pd.nik, js.nama_surat, js.kode_surat
        FROM pengajuan_surat p 
        JOIN penduduk pd ON p.penduduk_id = pd.id 
        JOIN jenis_surat js ON p.jenis_surat_id = js.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan riwayat pengajuan penduduk
function get_riwayat_pengajuan($penduduk_id, $limit = null) {
    global $pdo;
    $sql = "
        SELECT p.*, js.nama_surat 
        FROM pengajuan_surat p 
        JOIN jenis_surat js ON p.jenis_surat_id = js.id 
        WHERE penduduk_id = ? 
        ORDER BY tanggal_pengajuan DESC
    ";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$penduduk_id]);
    return $stmt->fetchAll();
}

// Prevent XSS
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Set CSRF token untuk form
function csrf_field() {
    $token = generate_csrf_token();
    return "<input type='hidden' name='csrf_token' value='$token'>";
}

// Cek apakah request adalah AJAX
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Set waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

// Auto-hapus file setup jika ada
$setup_files = ['setup.php', 'setup_database.php', 'generate_hash.php'];
foreach ($setup_files as $setup_file) {
    if (file_exists($setup_file)) {
        // Hanya beri peringatan, tidak hapus otomatis untuk keamanan
        error_log("PERINGATAN: File setup $setup_file masih ada di server. Harap hapus manual.");
    }
}
?>