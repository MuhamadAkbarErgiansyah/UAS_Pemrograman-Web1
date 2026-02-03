<?php
// Generate Excel Report (CSV Format)
require __DIR__ . '/../config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: ../login.php');
  exit;
}

// Get section from GET parameter
$section = $_GET['section'] ?? 'all';

// Function to convert array to CSV
function array_to_csv($array) {
    $csv = '';
    foreach ($array as $value) {
        $csv .= '"' . str_replace('"', '""', $value) . '",';
    }
    return rtrim($csv, ',') . "\n";
}

// Determine filename and get data
$filename = '';
$csv_data = '';

if($section === 'reservations'){
  $filename = 'Laporan_Pemesanan_' . date('Y-m-d_H-i-s') . '.csv';
  
  $stmt = $pdo->query("SELECT r.id, u.name, r.package_title, r.date_event, r.total_person, r.amount, r.status, r.created_at FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
  $data = $stmt->fetchAll();
  
  // Header
  $csv_data .= array_to_csv(['ID', 'Pengguna', 'Paket', 'Tanggal Event', 'Peserta', 'Jumlah', 'Status', 'Tanggal Pemesanan']);
  
  // Data
  foreach($data as $row){
    $csv_data .= array_to_csv([
      '#' . $row['id'],
      $row['name'] ?? 'N/A',
      $row['package_title'] ?? 'N/A',
      $row['date_event'],
      $row['total_person'],
      'Rp ' . number_format($row['amount'], 0, ',', '.'),
      ucfirst($row['status'] ?? 'unknown'),
      date('d M Y H:i', strtotime($row['created_at']))
    ]);
  }
  
} else if($section === 'users'){
  $filename = 'Laporan_Pengguna_' . date('Y-m-d_H-i-s') . '.csv';
  
  $stmt = $pdo->query("SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC");
  $data = $stmt->fetchAll();
  
  // Header
  $csv_data .= array_to_csv(['ID', 'Nama', 'Email', 'Telepon', 'Tgl Daftar']);
  
  // Data
  foreach($data as $row){
    $csv_data .= array_to_csv([
      '#' . $row['id'],
      $row['name'],
      $row['email'],
      $row['phone'] ?? '-',
      date('d M Y', strtotime($row['created_at']))
    ]);
  }
  
} else if($section === 'items'){
  $filename = 'Laporan_Paket_' . date('Y-m-d_H-i-s') . '.csv';
  
  $stmt = $pdo->query("SELECT id, title, slug, summary, price, created_at FROM items ORDER BY created_at DESC");
  $data = $stmt->fetchAll();
  
  // Header
  $csv_data .= array_to_csv(['ID', 'Judul Paket', 'Slug', 'Ringkasan', 'Harga', 'Dibuat']);
  
  // Data
  foreach($data as $row){
    $csv_data .= array_to_csv([
      '#' . $row['id'],
      $row['title'],
      $row['slug'],
      $row['summary'],
      'Rp ' . number_format($row['price'], 0, ',', '.'),
      date('d M Y', strtotime($row['created_at']))
    ]);
  }
  
} else {
  // All - Summary statistics
  $filename = 'Laporan_Sistem_' . date('Y-m-d_H-i-s') . '.csv';
  
  $csv_data .= "LAPORAN SISTEM EXPLORE BANDUNG\n";
  $csv_data .= "Tanggal: " . date('d M Y H:i:s') . "\n\n";
  
  // Statistics
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
  $total_users = $stmt->fetch()['count'];
  
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
  $total_items = $stmt->fetch()['count'];
  
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
  $total_reservations = $stmt->fetch()['count'];
  
  $stmt = $pdo->query("SELECT SUM(amount) as total FROM reservations WHERE status = 'completed'");
  $total_revenue = $stmt->fetch()['total'] ?? 0;
  
  $csv_data .= array_to_csv(['STATISTIK SISTEM', '']);
  $csv_data .= array_to_csv(['Total Pengguna', $total_users]);
  $csv_data .= array_to_csv(['Total Paket', $total_items]);
  $csv_data .= array_to_csv(['Total Pemesanan', $total_reservations]);
  $csv_data .= array_to_csv(['Total Pendapatan', 'Rp ' . number_format($total_revenue, 0, ',', '.')]);
  $csv_data .= "\n";
  
  // Recent Reservations
  $csv_data .= array_to_csv(['PEMESANAN TERBARU', '', '', '', '', '', '', '']);
  $csv_data .= array_to_csv(['ID', 'Pengguna', 'Paket', 'Tanggal Event', 'Peserta', 'Jumlah', 'Status', 'Tanggal']);
  
  $stmt = $pdo->query("SELECT r.id, u.name, r.package_title, r.date_event, r.total_person, r.amount, r.status, r.created_at FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 20");
  $recent = $stmt->fetchAll();
  
  foreach($recent as $row){
    $csv_data .= array_to_csv([
      '#' . $row['id'],
      $row['name'] ?? 'N/A',
      $row['package_title'] ?? 'N/A',
      $row['date_event'],
      $row['total_person'],
      'Rp ' . number_format($row['amount'], 0, ',', '.'),
      ucfirst($row['status'] ?? 'unknown'),
      date('d M Y H:i', strtotime($row['created_at']))
    ]);
  }
}

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
echo $csv_data;
exit;
?>
