<?php
require_once '../config.php';
require_role('admin');

// Filter parameters
$format = $_GET['format'] ?? 'html'; // html, pdf, excel
$search = $_GET['search'] ?? '';

// Build query based on filters
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nama LIKE ? OR nik LIKE ? OR alamat LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Get data
$query = "
    SELECT p.*, 
           (SELECT COUNT(*) FROM pengajuan_surat WHERE penduduk_id = p.id) as total_pengajuan
    FROM penduduk p 
    $where_clause 
    ORDER BY p.nama ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$penduduk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export to Excel
if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_penduduk_' . date('Y-m-d') . '.xls"');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Data Penduduk - ' . $site_name . '</title>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-center { text-align: center; }
        </style>
    </head>
    <body>
        <h2>Data Penduduk - ' . $site_name . '</h2>
        <p>Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>
        <p>Total Data: ' . count($penduduk_list) . ' penduduk</p>
        
        <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Total Pengajuan</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach($penduduk_list as $index => $penduduk) {
        echo '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . $penduduk['nik'] . '</td>
                <td>' . htmlspecialchars($penduduk['nama']) . '</td>
                <td>' . htmlspecialchars($penduduk['tempat_lahir']) . '</td>
                <td>' . date('d/m/Y', strtotime($penduduk['tanggal_lahir'])) . '</td>
                <td>' . htmlspecialchars($penduduk['alamat']) . '</td>
                <td>' . ($penduduk['no_telp'] ?: '-') . '</td>
                <td class="text-center">' . $penduduk['total_pengajuan'] . '</td>
              </tr>';
    }
    
    echo '</tbody>
    </table>
    </body>
    </html>';
    exit;
}

// Export to PDF (using HTML to PDF)
if ($format === 'pdf') {
    // Simple HTML to PDF conversion (for real PDF, use Dompdf or TCPDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="data_penduduk_' . date('Y-m-d') . '.pdf"');
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Data Penduduk - ' . $site_name . '</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h2 { text-align: center; color: #2c3e50; }
            .header-info { text-align: center; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #3498db; color: white; padding: 8px; text-align: left; }
            td { padding: 6px; border: 1px solid #ddd; }
            .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #7f8c8d; }
        </style>
    </head>
    <body>
        <h2>DATA PENDUDUK</h2>
        <div class="header-info">
            <strong>Desa: ' . $site_name . '</strong><br>
            Dicetak pada: ' . date('d/m/Y H:i:s') . '<br>
            Total Data: ' . count($penduduk_list) . ' penduduk';
    
    $html .= '</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>Alamat</th>
                    <th>Telp</th>
                    <th>Pengajuan</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach($penduduk_list as $index => $penduduk) {
        $html .= '<tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . $penduduk['nik'] . '</td>
                    <td>' . htmlspecialchars($penduduk['nama']) . '</td>
                    <td>' . htmlspecialchars($penduduk['tempat_lahir']) . '<br>' . date('d/m/Y', strtotime($penduduk['tanggal_lahir'])) . '</td>
                    <td>' . htmlspecialchars(substr($penduduk['alamat'], 0, 50)) . '</td>
                    <td>' . ($penduduk['no_telp'] ?: '-') . '</td>
                    <td>' . $penduduk['total_pengajuan'] . '</td>
                  </tr>';
    }
    
    $html .= '</tbody>
        </table>
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh Sistem e-Surat Desa ' . $site_name . '</p>
        </div>
    </body>
    </html>';
    
    // For real PDF generation, you would use:
    // require_once 'vendor/autoload.php';
    // $dompdf = new Dompdf\Dompdf();
    // $dompdf->loadHtml($html);
    // $dompdf->setPaper('A4', 'landscape');
    // $dompdf->render();
    // $dompdf->stream();
    
    // For now, output HTML that can be printed as PDF
    echo $html;
    exit;
}

// HTML View for Print
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Penduduk - <?php echo $site_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { font-size: 12px; }
            table { font-size: 11px; }
            .header { margin-bottom: 20px; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; }
            .table th { background-color: #dee2e6 !important; }
        }
        .print-only { display: none; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .table th { background-color: #f8f9fa; }
        .total-badge { font-size: 14px; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="container mt-4">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="print-only">
                        <!-- Logo or seal would go here -->
                        <div style="border: 2px solid #000; width: 100px; height: 100px; margin: 0 auto;">
                            <br><br>LOGO
                        </div>
                    </div>
                </div>
                <div class="col-md-8 text-center">
                    <h4 class="mb-1">DATA PENDUDUK</h4>
                    <h5 class="mb-1">DESA <?php echo strtoupper($site_name); ?></h5>
                    <p class="mb-0"><?php echo date('d F Y, H:i:s'); ?></p>
                </div>
                <div class="col-md-2 text-center print-only">
                    <small>Halaman <span id="pageNumber"></span></small>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info p-2">
                    <div class="row text-center">
                        <div class="col">
                            <strong>Total Data:</strong>
                            <span class="badge bg-primary total-badge"><?php echo count($penduduk_list); ?></span>
                        </div>
                        <div class="col">
                            <strong>Total Pengajuan:</strong>
                            <span class="badge bg-info total-badge">
                                <?php 
                                $total_pengajuan = array_sum(array_column($penduduk_list, 'total_pengajuan'));
                                echo $total_pengajuan;
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th>NIK</th>
                        <th>NAMA LENGKAP</th>
                        <th>TEMPAT/TGL LAHIR</th>
                        <th>ALAMAT</th>
                        <th>NO. TELP</th>
                        <th width="80">PENGAJUAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($penduduk_list) > 0): ?>
                        <?php foreach($penduduk_list as $index => $penduduk): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><code><?php echo $penduduk['nik']; ?></code></td>
                                <td><strong><?php echo htmlspecialchars($penduduk['nama']); ?></strong></td>
                                <td>
                                    <small>
                                        <?php echo htmlspecialchars($penduduk['tempat_lahir']); ?><br>
                                        <?php echo date('d/m/Y', strtotime($penduduk['tanggal_lahir'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($penduduk['alamat']); ?></small>
                                </td>
                                <td class="text-center"><?php echo $penduduk['no_telp'] ?: '-'; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo $penduduk['total_pengajuan']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people display-6"></i><br>
                                    <h5>Belum ada data penduduk</h5>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="print-only">
                        <br><br>
                        <div style="text-align: center">
                            <hr style="width: 200px;">
                            <strong>KEPALA DESA</strong><br>
                            <?php echo $site_name; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <div class="print-only">
                        <br><br>
                        <div style="text-align: center">
                            <hr style="width: 200px;">
                            <strong>OPERATOR</strong><br>
                            <?php echo $_SESSION['user_nama']; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <small class="text-muted">
                    Dicetak dari Sistem e-Surat Desa <?php echo $site_name; ?> | 
                    <?php echo date('d/m/Y H:i:s'); ?>
                </small>
            </div>
        </div>

        <!-- Action Buttons (Not printed) -->
        <div class="no-print mt-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="btn-group" role="group">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                        <a href="cetak_penduduk.php?format=excel&search=<?php echo urlencode($search); ?>" 
                           class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        
                        <a href="kelola_penduduk.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                    
                    <!-- Filter Form -->
                    <div class="mt-3">
                        <form method="GET" class="row g-2 justify-content-center">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Cari nama/NIK/alamat...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="cetak_penduduk.php" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add page numbers when printing
        window.onbeforeprint = function() {
            var pages = document.querySelectorAll('tbody tr');
            var pageSize = 30; // Number of rows per page
            var pageNumber = 1;
            
            for(var i = 0; i < pages.length; i++) {
                if(i > 0 && i % pageSize === 0) {
                    pageNumber++;
                }
                if(i % pageSize === 0) {
                    pages[i].insertAdjacentHTML('beforebegin', 
                        '<tr class="page-break"><td colspan="7" style="text-align:center;padding:10px;background:#f8f9fa;">Halaman ' + pageNumber + '</td></tr>');
                }
            }
            
            document.getElementById('pageNumber').textContent = pageNumber;
        };

        // Print dialog opens immediately
        <?php if(isset($_GET['auto_print']) && $_GET['auto_print'] == '1'): ?>
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        <?php endif; ?>
    </script>
</body>
</html>