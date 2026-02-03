<?php
require 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: admin_login.php');
  exit;
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if(!$user || $user['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

// Get statistics
$stats = [];

// Total Users (non-admin)
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user' OR role IS NULL");
  $stats['total_users'] = $stmt->fetch()['count'];
} catch(Exception $e) {
  $stats['total_users'] = 0;
}

// Total Items/Paket
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
  $stats['total_items'] = $stmt->fetch()['count'];
} catch(Exception $e) {
  $stats['total_items'] = 0;
}

// Total Reservations
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
  $stats['total_reservations'] = $stmt->fetch()['count'];
} catch(Exception $e) {
  $stats['total_reservations'] = 0;
}

// Pending Reservations
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations r LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id WHERE ba.status IS NULL OR ba.status = 'pending'");
  $stats['pending_reservations'] = $stmt->fetch()['count'];
} catch(Exception $e) {
  $stats['pending_reservations'] = 0;
}

// Approved Reservations
try {
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM booking_approvals WHERE status = 'approved'");
  $stats['approved_reservations'] = $stmt->fetch()['count'];
} catch(Exception $e) {
  $stats['approved_reservations'] = 0;
}

// Recent Reservations
try {
  $stmt = $pdo->query("SELECT r.*, u.name as user_name, u.email as user_email, 
                       COALESCE(ba.status, 'pending') as approval_status
                       FROM reservations r 
                       LEFT JOIN users u ON r.user_id = u.id 
                       LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
                       ORDER BY r.created_at DESC LIMIT 5");
  $recent_reservations = $stmt->fetchAll();
} catch(Exception $e) {
  $recent_reservations = [];
}

// Top Packages
try {
  $stmt = $pdo->query("SELECT package_title as title, COUNT(*) as booking_count FROM reservations GROUP BY package_title ORDER BY booking_count DESC LIMIT 5");
  $top_packages = $stmt->fetchAll();
} catch(Exception $e) {
  $top_packages = [];
}

// Get Reviews Statistics
try {
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
  $stats['total_reviews'] = $stmt->fetch()['total'];
} catch(Exception $e) {
  $stats['total_reviews'] = 0;
}

try {
  $stmt = $pdo->query("SELECT AVG(rating) as avg FROM reviews");
  $stats['avg_rating'] = round($stmt->fetch()['avg'] ?? 0, 1);
} catch(Exception $e) {
  $stats['avg_rating'] = 0;
}

// Recent Reviews
try {
  $stmt = $pdo->query("SELECT r.*, u.name as user_name, i.title as package_title 
                       FROM reviews r 
                       LEFT JOIN users u ON r.user_id = u.id 
                       LEFT JOIN items i ON r.item_id = i.id 
                       ORDER BY r.created_at DESC LIMIT 5");
  $recent_reviews = $stmt->fetchAll();
} catch(Exception $e) {
  $recent_reviews = [];
}

$pageTitle = "Admin Dashboard";
$currentPage = "dashboard";
require 'includes/admin_header.php';
?>

<style>
  .stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }
  .stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 10px 0;
  }
  .stat-card p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
  }
  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  .card-custom {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  .card-custom .card-header {
    background: white;
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
  }
  .quick-action {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
  }
  .quick-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  .quick-action:hover i,
  .quick-action:hover p { color: white !important; }
  .quick-action i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #667eea;
  }
  .quick-action p { margin: 0; font-weight: 500; }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-tachometer-alt me-2" style="color: #667eea;"></i>Dashboard Admin</h2>
    <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>! Kelola sistem Explore Bandung dari sini.</p>
  </div>
  <div class="d-flex gap-2">
    <span class="badge bg-success p-2">
      <i class="fas fa-circle me-1"></i>Sistem Online
    </span>
    <span class="badge bg-info p-2">
      <i class="fas fa-calendar me-1"></i><?= date('d M Y') ?>
    </span>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p>Total Pengguna</p>
          <h3><?= number_format($stats['total_users']) ?></h3>
          <small class="text-success"><i class="fas fa-users me-1"></i>Terdaftar</small>
        </div>
        <div class="stat-icon" style="background: rgba(102,126,234,0.15); color: #667eea;">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p>Total Paket</p>
          <h3><?= number_format($stats['total_items']) ?></h3>
          <small class="text-success"><i class="fas fa-box me-1"></i>Aktif</small>
        </div>
        <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #10b981;">
          <i class="fas fa-box"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p>Total Pemesanan</p>
          <h3><?= number_format($stats['total_reservations']) ?></h3>
          <small class="text-info"><i class="fas fa-calendar-check me-1"></i>Reservasi</small>
        </div>
        <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: #3b82f6;">
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p>Menunggu Approval</p>
          <h3><?= number_format($stats['pending_reservations']) ?></h3>
          <small class="text-warning"><i class="fas fa-clock me-1"></i>Pending</small>
        </div>
        <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;">
          <i class="fas fa-hourglass-half"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mb-4">
  <div class="col-12">
    <h5 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h5>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="add_item.php" class="quick-action">
      <i class="fas fa-plus-circle"></i>
      <p>Tambah Paket</p>
    </a>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="admin_bookings.php?status=pending" class="quick-action">
      <i class="fas fa-clipboard-check"></i>
      <p>Proses Booking</p>
    </a>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="admin_reviews.php" class="quick-action">
      <i class="fas fa-star"></i>
      <p>Kelola Review</p>
    </a>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="admin_chat.php" class="quick-action">
      <i class="fas fa-comments"></i>
      <p>Chat Pelanggan</p>
    </a>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="admin_analytics.php" class="quick-action">
      <i class="fas fa-chart-line"></i>
      <p>Analytics</p>
    </a>
  </div>
  <div class="col-lg-2 col-md-4 col-6">
    <a href="reports.php" class="quick-action">
      <i class="fas fa-file-export"></i>
      <p>Export Laporan</p>
    </a>
  </div>
</div>

<!-- Recent Bookings & Top Packages -->
<div class="row g-4 mb-4">
  <!-- Recent Reservations -->
  <div class="col-lg-8">
    <div class="card-custom">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-clock me-2 text-primary"></i>Pemesanan Terbaru</h5>
        <a href="admin_bookings.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Tanggal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($recent_reservations)): ?>
                <?php foreach($recent_reservations as $res): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:35px;height:35px;font-size:0.8rem;">
                        <?= strtoupper(substr($res['user_name'] ?? $res['name'], 0, 1)) ?>
                      </div>
                      <div>
                        <div class="fw-bold small"><?= htmlspecialchars($res['user_name'] ?? $res['name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($res['email']) ?></small>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge bg-light text-dark"><?= htmlspecialchars($res['package_title']) ?></span></td>
                  <td><small><?= date('d M Y', strtotime($res['date_event'])) ?></small></td>
                  <td>
                    <?php
                    $status = $res['approval_status'] ?? 'pending';
                    $badges = [
                      'pending' => 'bg-warning',
                      'approved' => 'bg-success',
                      'rejected' => 'bg-danger'
                    ];
                    ?>
                    <span class="badge <?= $badges[$status] ?? 'bg-secondary' ?>"><?= ucfirst($status) ?></span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada pemesanan</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Top Packages -->
  <div class="col-lg-4">
    <div class="card-custom h-100">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Paket Terpopuler</h5>
      </div>
      <div class="card-body">
        <?php if(!empty($top_packages)): ?>
          <?php foreach($top_packages as $idx => $pkg): ?>
          <div class="d-flex align-items-center mb-3">
            <div class="me-3">
              <span class="badge <?= $idx < 3 ? 'bg-warning' : 'bg-secondary' ?>" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                <?= $idx + 1 ?>
              </span>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold small"><?= htmlspecialchars($pkg['title']) ?></div>
              <small class="text-muted"><?= $pkg['booking_count'] ?> booking</small>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted text-center">Belum ada data</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent Reviews -->
<div class="row g-4">
  <div class="col-12">
    <div class="card-custom">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>Review Terbaru</h5>
        <a href="admin_reviews.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="card-body">
        <?php if(!empty($recent_reviews)): ?>
          <div class="row">
            <?php foreach($recent_reviews as $review): ?>
            <div class="col-md-6 col-lg-4 mb-3">
              <div class="border rounded p-3">
                <div class="d-flex align-items-center mb-2">
                  <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width:35px;height:35px;font-size:0.8rem;">
                    <?= strtoupper(substr($review['user_name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold small"><?= htmlspecialchars($review['user_name'] ?? 'Anonim') ?></div>
                    <small class="text-muted"><?= htmlspecialchars($review['package_title'] ?? 'N/A') ?></small>
                  </div>
                  <div>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star" style="color: <?= $i <= $review['rating'] ? '#ffc107' : '#ddd' ?>; font-size: 0.7rem;"></i>
                    <?php endfor; ?>
                  </div>
                </div>
                <small class="text-muted"><?= htmlspecialchars(substr($review['comment'], 0, 80)) ?>...</small>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted text-center py-3 mb-0">Belum ada review</p>
        <?php endif; ?>
        
        <!-- Review Stats Summary -->
        <div class="bg-light rounded p-3 mt-3">
          <div class="row text-center">
            <div class="col-6">
              <h4 class="mb-0 text-primary"><?= $stats['total_reviews'] ?></h4>
              <small class="text-muted">Total Review</small>
            </div>
            <div class="col-6">
              <h4 class="mb-0 text-warning">
                <i class="fas fa-star"></i> <?= $stats['avg_rating'] ?>
              </h4>
              <small class="text-muted">Rating Rata-rata</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
