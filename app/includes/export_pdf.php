<?php
// Generate PDF Report
require __DIR__ . '/../config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: ../login.php');
  exit;
}

// Get section from GET parameter
$section = $_GET['section'] ?? 'all';

// Determine file name and data based on section
switch($section){
  case 'reservations':
    $stmt = $pdo->query("SELECT r.*, u.name FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
    $data = $stmt->fetchAll();
    $filename = 'Laporan_Pemesanan_' . date('Y-m-d_H-i-s') . '.html';
    break;
  
  case 'users':
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    $filename = 'Laporan_Pengguna_' . date('Y-m-d_H-i-s') . '.html';
    break;
  
  case 'items':
    $stmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    $filename = 'Laporan_Paket_' . date('Y-m-d_H-i-s') . '.html';
    break;
  
  case 'all':
  default:
    // Get overall statistics
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
    $stats['total_items'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $stats['total_reservations'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM reservations WHERE status = 'completed'");
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Get recent reservations
    $stmt = $pdo->query("SELECT r.*, u.name FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 10");
    $recent_data = $stmt->fetchAll();
    
    $filename = 'Laporan_Sistem_' . date('Y-m-d_H-i-s') . '.html';
    break;
}

// Generate HTML Content
$html = '';
$html .= '<!DOCTYPE html>';
$html .= '<html lang="id">';
$html .= '<head>';
$html .= '<meta charset="UTF-8">';
$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
$html .= '<title>' . htmlspecialchars($filename) . '</title>';
$html .= '<style>';
$html .= 'body { font-family: Arial, sans-serif; margin: 20px; color: #333; }';
$html .= 'h1 { color: #6a11cb; border-bottom: 3px solid #2575fc; padding-bottom: 10px; }';
$html .= 'h2 { color: #2575fc; margin-top: 30px; }';
$html .= '.header { text-align: center; margin-bottom: 30px; }';
$html .= '.stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }';
$html .= '.stat-box { background: #f3f4f6; padding: 15px; border-radius: 8px; text-align: center; }';
$html .= '.stat-box h3 { margin: 0 0 10px 0; color: #6a11cb; font-size: 24px; }';
$html .= '.stat-box p { margin: 0; color: #6b7280; font-size: 12px; }';
$html .= 'table { width: 100%; border-collapse: collapse; margin: 20px 0; }';
$html .= 'th { background: #6a11cb; color: white; padding: 12px; text-align: left; font-weight: bold; }';
$html .= 'td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }';
$html .= 'tr:nth-child(even) { background: #f9fafb; }';
$html .= '.footer { text-align: center; margin-top: 40px; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 20px; }';
$html .= '.timestamp { color: #6b7280; font-size: 12px; }';
$html .= '</style>';
$html .= '</head>';
$html .= '<body>';

// Header
$html .= '<div class="header">';
$html .= '<h1>Explore Bandung</h1>';
$html .= '<p style="margin: 5px 0; color: #6b7280;">Laporan Sistem Wisata & Pemesanan</p>';
$html .= '<p class="timestamp">Tanggal: ' . date('d M Y H:i:s') . '</p>';
$html .= '</div>';

// Content based on section
if($section === 'all'){
  // Statistics
  $html .= '<h2>Statistik Sistem</h2>';
  $html .= '<div class="stats">';
  $html .= '<div class="stat-box"><h3>' . number_format($stats['total_users']) . '</h3><p>Total Pengguna</p></div>';
  $html .= '<div class="stat-box"><h3>' . number_format($stats['total_items']) . '</h3><p>Total Paket</p></div>';
  $html .= '<div class="stat-box"><h3>' . number_format($stats['total_reservations']) . '</h3><p>Total Pemesanan</p></div>';
  $html .= '<div class="stat-box"><h3>Rp ' . number_format($stats['total_revenue'], 0, ',', '.') . '</h3><p>Total Pendapatan</p></div>';
  $html .= '</div>';
  
  // Recent Reservations Table
  $html .= '<h2>Pemesanan Terbaru</h2>';
  $html .= '<table>';
  $html .= '<thead><tr>';
  $html .= '<th>ID</th><th>Pengguna</th><th>Paket</th><th>Tanggal Event</th><th>Jumlah Peserta</th><th>Status</th><th>Tanggal</th>';
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  
  foreach($recent_data as $res){
    $html .= '<tr>';
    $html .= '<td>#' . $res['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($res['name'] ?? 'N/A') . '</td>';
    $html .= '<td>' . htmlspecialchars($res['package_title'] ?? 'N/A') . '</td>';
    $html .= '<td>' . $res['date_event'] . '</td>';
    $html .= '<td>' . $res['total_person'] . ' orang</td>';
    $html .= '<td>' . ucfirst($res['status'] ?? 'unknown') . '</td>';
    $html .= '<td>' . date('d M Y', strtotime($res['created_at'])) . '</td>';
    $html .= '</tr>';
  }
  
  $html .= '</tbody></table>';
  
} else if($section === 'reservations'){
  $html .= '<h2>Daftar Pemesanan</h2>';
  $html .= '<table>';
  $html .= '<thead><tr>';
  $html .= '<th>ID</th><th>Pengguna</th><th>Paket</th><th>Tanggal Event</th><th>Peserta</th><th>Status</th><th>Tgl Pesan</th>';
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  
  foreach($data as $res){
    $html .= '<tr>';
    $html .= '<td>#' . $res['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($res['name'] ?? 'N/A') . '</td>';
    $html .= '<td>' . htmlspecialchars($res['package_title'] ?? 'N/A') . '</td>';
    $html .= '<td>' . $res['date_event'] . '</td>';
    $html .= '<td>' . $res['total_person'] . '</td>';
    $html .= '<td>' . ucfirst($res['status'] ?? 'unknown') . '</td>';
    $html .= '<td>' . date('d M Y', strtotime($res['created_at'])) . '</td>';
    $html .= '</tr>';
  }
  
  $html .= '</tbody></table>';
  $html .= '<p><strong>Total Record:</strong> ' . count($data) . '</p>';
  
} else if($section === 'users'){
  $html .= '<h2>Daftar Pengguna</h2>';
  $html .= '<table>';
  $html .= '<thead><tr>';
  $html .= '<th>ID</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Tgl Daftar</th>';
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  
  foreach($data as $user){
    $html .= '<tr>';
    $html .= '<td>#' . $user['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($user['name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
    $html .= '<td>' . htmlspecialchars($user['phone'] ?? '-') . '</td>';
    $html .= '<td>' . date('d M Y', strtotime($user['created_at'])) . '</td>';
    $html .= '</tr>';
  }
  
  $html .= '</tbody></table>';
  $html .= '<p><strong>Total Pengguna:</strong> ' . count($data) . '</p>';
  
} else if($section === 'items'){
  $html .= '<h2>Daftar Paket Wisata</h2>';
  $html .= '<table>';
  $html .= '<thead><tr>';
  $html .= '<th>ID</th><th>Judul</th><th>Slug</th><th>Harga</th><th>Dibuat</th>';
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  
  foreach($data as $item){
    $html .= '<tr>';
    $html .= '<td>#' . $item['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($item['title']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['slug']) . '</td>';
    $html .= '<td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>';
    $html .= '<td>' . date('d M Y', strtotime($item['created_at'])) . '</td>';
    $html .= '</tr>';
  }
  
  $html .= '</tbody></table>';
  $html .= '<p><strong>Total Paket:</strong> ' . count($data) . '</p>';
}

// Footer
$html .= '<div class="footer">';
$html .= '<p>&copy; 2025 Explore Bandung. Laporan ini dibuat secara otomatis oleh sistem.</p>';
$html .= '<p>Untuk pertanyaan, hubungi admin@explorebandung.com</p>';
$html .= '</div>';

$html .= '</body></html>';

// Output
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($html));
echo $html;
exit;
?>
