<?php
require 'config.php';
$pageTitle = "Laporan & Export";
$currentPage = "reports";

// Check admin
if(!isset($_SESSION['user_id'])){
  header('Location: admin_login.php');
  exit;
}

$userCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$userCheck->execute([$_SESSION['user_id']]);
$currentUser = $userCheck->fetch();

if(!$currentUser || $currentUser['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

// Get statistics
$total_users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$total_items = $pdo->query("SELECT COUNT(*) as count FROM items")->fetch()['count'];
$total_reservations = $pdo->query("SELECT COUNT(*) as count FROM reservations")->fetch()['count'];
$total_approved = $pdo->query("SELECT COUNT(*) as count FROM booking_approvals WHERE status = 'approved'")->fetch()['count'];

// Handle export PDF
if(isset($_GET['export']) && $_GET['export'] === 'pdf'){
  $section = $_GET['section'] ?? 'all';
  
  // Set headers for PDF download (HTML to print)
  $filename = 'Laporan_Explore_Bandung_' . date('Y-m-d') . '.html';
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Cache-Control: private, max-age=0, must-revalidate');
  header('Pragma: public');
  
  echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Explore Bandung</title>';
  echo '<style>body{font-family:Arial,sans-serif;margin:20px;} h1{color:#667eea;} table{width:100%;border-collapse:collapse;margin:20px 0;} th{background:#667eea;color:white;padding:10px;} td{padding:8px;border-bottom:1px solid #ddd;} @media print { body{margin:0;} }</style>';
  echo '</head><body>';
  echo '<h1>Laporan Sistem Explore Bandung</h1>';
  echo '<p>Tanggal: ' . date('d M Y H:i:s') . '</p>';
  echo '<p><small>Buka file ini di browser lalu gunakan Ctrl+P untuk menyimpan sebagai PDF</small></p>';
  
  echo '<h2>Statistik</h2>';
  echo '<table><tr><th>Keterangan</th><th>Jumlah</th></tr>';
  echo '<tr><td>Total Pengguna</td><td>' . $total_users . '</td></tr>';
  echo '<tr><td>Total Paket</td><td>' . $total_items . '</td></tr>';
  echo '<tr><td>Total Pemesanan</td><td>' . $total_reservations . '</td></tr>';
  echo '<tr><td>Pemesanan Disetujui</td><td>' . $total_approved . '</td></tr>';
  echo '</table>';
  
  if($section === 'reservations' || $section === 'all') {
    echo '<h2>Data Pemesanan</h2>';
    $stmt = $pdo->query("SELECT r.*, COALESCE(ba.status, 'pending') as approval_status FROM reservations r LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id ORDER BY r.created_at DESC");
    $reservations = $stmt->fetchAll();
    
    echo '<table><tr><th>ID</th><th>Nama</th><th>Paket</th><th>Tanggal</th><th>Status</th></tr>';
    foreach($reservations as $r) {
      echo '<tr><td>#'.$r['id'].'</td><td>'.htmlspecialchars($r['name']).'</td><td>'.htmlspecialchars($r['package_title']).'</td><td>'.date('d M Y', strtotime($r['date_event'])).'</td><td>'.ucfirst($r['approval_status']).'</td></tr>';
    }
    echo '</table>';
  }
  
  echo '<p style="margin-top:40px;text-align:center;color:#666;">&copy; ' . date('Y') . ' Explore Bandung</p>';
  echo '</body></html>';
  exit;
}

// Handle export Excel
if(isset($_GET['export']) && $_GET['export'] === 'excel'){
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=Laporan_Explore_Bandung_' . date('Y-m-d') . '.csv');
  
  $output = fopen('php://output', 'w');
  fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for UTF-8
  
  // Header
  fputcsv($output, ['LAPORAN SISTEM EXPLORE BANDUNG']);
  fputcsv($output, ['Tanggal Export: ' . date('d M Y H:i:s')]);
  fputcsv($output, []);
  
  // Stats
  fputcsv($output, ['STATISTIK']);
  fputcsv($output, ['Total Pengguna', $total_users]);
  fputcsv($output, ['Total Paket', $total_items]);
  fputcsv($output, ['Total Pemesanan', $total_reservations]);
  fputcsv($output, ['Pemesanan Disetujui', $total_approved]);
  fputcsv($output, []);
  
  // Reservations
  fputcsv($output, ['DATA PEMESANAN']);
  fputcsv($output, ['ID', 'Nama', 'Email', 'Telepon', 'Paket', 'Tanggal Wisata', 'Status', 'Tanggal Pesan']);
  
  $stmt = $pdo->query("SELECT r.*, COALESCE(ba.status, 'pending') as approval_status FROM reservations r LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id ORDER BY r.created_at DESC");
  $reservations = $stmt->fetchAll();
  
  foreach($reservations as $r) {
    fputcsv($output, [
      '#'.$r['id'],
      $r['name'],
      $r['email'] ?? '-',
      $r['contact'],
      $r['package_title'],
      date('d M Y', strtotime($r['date_event'])),
      ucfirst($r['approval_status']),
      date('d M Y H:i', strtotime($r['created_at']))
    ]);
  }
  
  fclose($output);
  exit;
}

require 'includes/admin_header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-file-export me-2" style="color: #667eea;"></i>Laporan & Export Data</h2>
    <p class="text-muted mb-0">Kelola dan export laporan sistem Explore Bandung</p>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card-custom p-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <p class="text-muted mb-1">Total Pengguna</p>
          <h3 class="fw-bold mb-0"><?= number_format($total_users) ?></h3>
        </div>
        <div style="width:50px;height:50px;background:rgba(102,126,234,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-users" style="color:#667eea;font-size:1.3rem;"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6">
    <div class="card-custom p-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <p class="text-muted mb-1">Total Paket</p>
          <h3 class="fw-bold mb-0"><?= number_format($total_items) ?></h3>
        </div>
        <div style="width:50px;height:50px;background:rgba(16,185,129,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-box" style="color:#10b981;font-size:1.3rem;"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6">
    <div class="card-custom p-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <p class="text-muted mb-1">Total Pemesanan</p>
          <h3 class="fw-bold mb-0"><?= number_format($total_reservations) ?></h3>
        </div>
        <div style="width:50px;height:50px;background:rgba(59,130,246,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-calendar-check" style="color:#3b82f6;font-size:1.3rem;"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6">
    <div class="card-custom p-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <p class="text-muted mb-1">Disetujui</p>
          <h3 class="fw-bold mb-0"><?= number_format($total_approved) ?></h3>
        </div>
        <div style="width:50px;height:50px;background:rgba(16,185,129,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-check-circle" style="color:#10b981;font-size:1.3rem;"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Export Options -->
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card-custom">
      <div class="card-header">
        <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Laporan Lengkap</h5>
      </div>
      <div class="p-4">
        <p class="text-muted mb-4">Ekspor laporan lengkap sistem termasuk statistik dan data pemesanan</p>
        <div class="d-flex gap-3">
          <a href="?export=pdf" class="btn btn-danger flex-fill">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
          </a>
          <a href="?export=excel" class="btn btn-success flex-fill">
            <i class="fas fa-file-excel me-2"></i>Export Excel
          </a>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-6">
    <div class="card-custom">
      <div class="card-header">
        <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2 text-warning"></i>Laporan Pemesanan</h5>
      </div>
      <div class="p-4">
        <p class="text-muted mb-4">Ekspor daftar lengkap pemesanan dengan detail pelanggan</p>
        <div class="d-flex gap-3">
          <a href="?export=pdf&section=reservations" class="btn btn-danger flex-fill">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
          </a>
          <a href="?export=excel&section=reservations" class="btn btn-success flex-fill">
            <i class="fas fa-file-excel me-2"></i>Export Excel
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Data Preview -->
<div class="card-custom mt-4">
  <div class="card-header">
    <h5 class="mb-0 fw-bold"><i class="fas fa-eye me-2"></i>Preview Data Pemesanan</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Paket</th>
          <th>Tanggal Wisata</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $preview = $pdo->query("SELECT r.*, COALESCE(ba.status, 'pending') as approval_status FROM reservations r LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id ORDER BY r.created_at DESC LIMIT 10")->fetchAll();
        foreach($preview as $r):
        ?>
        <tr>
          <td><small class="text-muted">#<?= $r['id'] ?></small></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['package_title']) ?></td>
          <td><?= date('d M Y', strtotime($r['date_event'])) ?></td>
          <td>
            <span class="status-badge <?= $r['approval_status'] === 'approved' ? 'bg-success' : ($r['approval_status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
              <?= ucfirst($r['approval_status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
