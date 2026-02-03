<?php
require 'config.php';
$pageTitle = "Admin Reports";
require 'header.php';

// Check admin
if(!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if(!$user || $user['role'] !== 'admin') {
    echo "<div class='container py-5'><div class='alert alert-danger'>Akses Ditolak!</div></div>";
    require 'footer.php';
    exit;
}

// Get monthly data
$month = $_GET['month'] ?? date('Y-m');
$monthStart = $month . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));

// Monthly stats
$monthlyStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        COALESCE(SUM(r.total_price), 0) as monthly_revenue,
        COALESCE(SUM(r.quantity), 0) as total_guests
    FROM reservations r
    WHERE DATE(r.created_at) BETWEEN ? AND ?
");
$monthlyStmt->execute([$monthStart, $monthEnd]);
$monthlyData = $monthlyStmt->fetch();

// Get bookings for this month
$bookingsStmt = $pdo->prepare("
    SELECT r.*, i.title as paket_title, u.name as username, u.email,
           COALESCE(ba.status, 'pending') as booking_status
    FROM reservations r
    LEFT JOIN items i ON r.package_id = i.id
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    ORDER BY r.created_at DESC
");
$bookingsStmt->execute([$monthStart, $monthEnd]);
$monthlyBookings = $bookingsStmt->fetchAll();

// Export handling
if($_GET['export'] ?? false) {
    $exportType = $_GET['export'];
    
    if($exportType === 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="laporan_' . $month . '.pdf"');
        
        $html = generatePDFReport($month, $monthlyData, $monthlyBookings);
        echo $html;
        exit;
    } elseif($exportType === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="laporan_' . $month . '.xls"');
        
        echo generateExcelReport($month, $monthlyData, $monthlyBookings);
        exit;
    }
}

function generatePDFReport($month, $data, $bookings) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Laporan Bulanan - ' . $month . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1, h2 { color: #667eea; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #667eea; color: white; padding: 10px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .summary { background: #f0f4ff; padding: 15px; border-radius: 8px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>LAPORAN PEMESANAN - ' . $month . '</h1>
        <p>Explore Bandung Admin Report</p>
        
        <div class="summary">
            <h3>Ringkasan Bulan</h3>
            <p><strong>Total Pemesanan:</strong> ' . $data['total_bookings'] . ' paket</p>
            <p><strong>Total Tamu:</strong> ' . $data['total_guests'] . ' orang</p>
            <p><strong>Total Pendapatan:</strong> Rp ' . number_format($data['monthly_revenue'], 0, ',', '.') . '</p>
        </div>
        
        <h2>Detail Pemesanan</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Paket</th>
                <th>Pelanggan</th>
                <th>Tamu</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>';
    
    foreach($bookings as $b) {
        $html .= '<tr>
            <td>EB-' . str_pad($b['id'], 5, '0', STR_PAD_LEFT) . '</td>
            <td>' . htmlspecialchars($b['paket_title']) . '</td>
            <td>' . htmlspecialchars($b['username']) . '</td>
            <td>' . $b['quantity'] . '</td>
            <td>Rp ' . number_format($b['total_price'], 0, ',', '.') . '</td>
            <td>' . ucfirst($b['booking_status']) . '</td>
            <td>' . date('d/m/Y', strtotime($b['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</table>
        <p style="text-align: center; margin-top: 40px; color: #666;">
            <small>Laporan otomatis - ' . date('d F Y H:i') . '</small>
        </p>
    </body>
    </html>';
    
    return $html;
}

function generateExcelReport($month, $data, $bookings) {
    $csv = "LAPORAN PEMESANAN - $month\n";
    $csv .= "Tanggal: " . date('d F Y H:i') . "\n\n";
    
    $csv .= "RINGKASAN BULAN\n";
    $csv .= "Total Pemesanan," . $data['total_bookings'] . "\n";
    $csv .= "Total Tamu," . $data['total_guests'] . "\n";
    $csv .= "Total Pendapatan,Rp " . number_format($data['monthly_revenue'], 0, ',', '.') . "\n\n";
    
    $csv .= "ID,Paket,Pelanggan,Email,Tamu,Harga,Status,Tanggal\n";
    foreach($bookings as $b) {
        $csv .= '"EB-' . str_pad($b['id'], 5, '0', STR_PAD_LEFT) . '",';
        $csv .= '"' . $b['paket_title'] . '",';
        $csv .= '"' . $b['username'] . '",';
        $csv .= '"' . $b['email'] . '",';
        $csv .= $b['quantity'] . ',';
        $csv .= $b['total_price'] . ',';
        $csv .= ucfirst($b['booking_status']) . ',';
        $csv .= date('d/m/Y', strtotime($b['created_at'])) . "\n";
    }
    
    return $csv;
}
?>

<!-- BREADCRUMB -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item"><a href="admin_dashboard.php" style="color: white;">Admin Dashboard</a></li>
        <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);">Laporan Bulanan</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <!-- Header -->
  <div class="mb-5" data-aos="fade-down">
    <h1 class="fw-bold mb-2" style="color: #1f2937;">
      <i class="fas fa-chart-bar me-2" style="color: #667eea;"></i>Laporan Bulanan
    </h1>
    <p class="text-muted">Download transkip pemesanan dalam PDF atau Excel</p>
  </div>

  <!-- Month Selector -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label fw-bold">Pilih Bulan</label>
        <input type="month" id="monthPicker" class="form-control form-control-lg rounded-3" 
               value="<?= $month ?>" onchange="filterMonth(this.value)">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Download Laporan</label>
        <div class="btn-group w-100" role="group">
          <a href="?month=<?= $month ?>&export=pdf" class="btn btn-outline-danger rounded-3">
            <i class="fas fa-file-pdf me-2"></i>PDF
          </a>
          <a href="?month=<?= $month ?>&export=excel" class="btn btn-outline-success rounded-3">
            <i class="fas fa-file-excel me-2"></i>Excel
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Monthly Summary -->
  <div class="row g-4 mb-4" data-aos="fade-up">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="opacity-75">Total Pemesanan</small>
            <h2 class="fw-bold mb-0"><?= $monthlyData['total_bookings'] ?></h2>
          </div>
          <i class="fas fa-clipboard-list fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); color: white;">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="opacity-75">Total Tamu</small>
            <h2 class="fw-bold mb-0"><?= $monthlyData['total_guests'] ?></h2>
          </div>
          <i class="fas fa-users fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="opacity-75">Total Pendapatan</small>
            <h2 class="fw-bold mb-0">Rp <?= number_format($monthlyData['monthly_revenue'], 0, ',', '.') ?></h2>
          </div>
          <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Table -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden" data-aos="fade-up">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; color: white;">
      <h5 class="fw-bold mb-0">
        <i class="fas fa-list me-2"></i>Detail Pemesanan Bulan <?= $month ?>
      </h5>
    </div>
    
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="bg-light">
          <tr>
            <th>ID Pemesanan</th>
            <th>Paket Wisata</th>
            <th>Pelanggan</th>
            <th>Tamu</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($monthlyBookings) > 0): ?>
            <?php foreach($monthlyBookings as $booking): ?>
              <tr>
                <td><strong>EB-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= htmlspecialchars($booking['paket_title']) ?></td>
                <td><?= htmlspecialchars($booking['username']) ?></td>
                <td class="text-center"><?= $booking['quantity'] ?> orang</td>
                <td><strong>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></strong></td>
                <td>
                  <span class="badge bg-<?= $booking['booking_status'] === 'approved' ? 'success' : 'warning' ?>">
                    <?= ucfirst($booking['booking_status']) ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($booking['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="fas fa-inbox me-2"></i>Tidak ada pemesanan untuk bulan ini
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  function filterMonth(month) {
    window.location.href = '?month=' + month;
  }
</script>

<?php require 'footer.php'; ?>
