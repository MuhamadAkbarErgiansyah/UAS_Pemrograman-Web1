<?php
require 'config.php';

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

$pageTitle = "Dashboard Analytics";
$currentPage = "analytics";

// Get monthly booking data for chart
$monthlyBookings = $pdo->query("
    SELECT 
        DATE_FORMAT(r.created_at, '%Y-%m') as month,
        COUNT(*) as total_bookings,
        COALESCE(SUM(i.price), 0) as revenue
    FROM reservations r
    LEFT JOIN items i ON r.package_id = i.id
    WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(r.created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

// Get rating distribution
$ratingDist = $pdo->query("
    SELECT rating, COUNT(*) as count
    FROM reviews
    GROUP BY rating
    ORDER BY rating DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get top packages
$topPackages = $pdo->query("
    SELECT i.title as name, COUNT(r.id) as bookings, COALESCE(SUM(i.price), 0) as revenue
    FROM items i
    LEFT JOIN reservations r ON i.id = r.package_id
    GROUP BY i.id
    ORDER BY bookings DESC
    LIMIT 5
")->fetchAll();

// Get visitor trend (booking by day of week)
$weekdayData = $pdo->query("
    SELECT 
        DAYOFWEEK(created_at) as day,
        COUNT(*) as count
    FROM reservations
    GROUP BY DAYOFWEEK(created_at)
    ORDER BY day
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get payment methods distribution (extract from note field)
$paymentMethods = [];
try {
    $notes = $pdo->query("SELECT note FROM reservations WHERE note LIKE '%Pembayaran:%'")->fetchAll(PDO::FETCH_COLUMN);
    $methods = [];
    foreach($notes as $note) {
        if(preg_match('/\[Pembayaran:\s*([^\]]+)\]/', $note, $matches)) {
            $method = trim($matches[1]);
            $methods[$method] = ($methods[$method] ?? 0) + 1;
        }
    }
    foreach($methods as $method => $count) {
        $paymentMethods[] = ['payment_method' => $method, 'count' => $count];
    }
} catch(Exception $e) {
    $paymentMethods = [];
}

// Summary stats - revenue from approved bookings
$totalRevenue = $pdo->query("
    SELECT COALESCE(SUM(i.price), 0) 
    FROM reservations r 
    LEFT JOIN items i ON r.package_id = i.id
    LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
    WHERE ba.status = 'approved'
")->fetchColumn() ?: 0;

$totalBookings = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$avgRating = $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?: 0;
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

require 'includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-chart-line me-2" style="color: #667eea;"></i>Dashboard Analytics</h2>
    <p class="text-muted mb-0">Analisis data dan performa bisnis</p>
  </div>
  <div class="btn-group">
    <button class="btn btn-outline-primary btn-sm active">12 Bulan</button>
    <button class="btn btn-outline-primary btn-sm">6 Bulan</button>
    <button class="btn btn-outline-primary btn-sm">30 Hari</button>
  </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="small opacity-75">Total Revenue</div>
            <div class="h4 mb-0 fw-bold">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></div>
          </div>
          <i class="fas fa-coins fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="small opacity-75">Total Booking</div>
            <div class="h4 mb-0 fw-bold"><?= number_format($totalBookings) ?></div>
          </div>
          <i class="fas fa-calendar-check fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="small opacity-75">Rating Rata-rata</div>
            <div class="h4 mb-0 fw-bold"><?= number_format($avgRating, 1) ?> <small><i class="fas fa-star"></i></small></div>
          </div>
          <i class="fas fa-star fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);">
      <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="small opacity-75">Total Pengguna</div>
            <div class="h4 mb-0 fw-bold"><?= number_format($totalUsers) ?></div>
          </div>
          <i class="fas fa-users fa-2x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Revenue Chart -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-chart-area me-2 text-primary"></i>Trend Revenue & Booking</h6>
      </div>
      <div class="card-body">
        <canvas id="revenueChart" height="100"></canvas>
      </div>
    </div>
  </div>
  
  <!-- Rating Distribution -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>Distribusi Rating</h6>
      </div>
      <div class="card-body">
        <canvas id="ratingChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Top Packages -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-trophy me-2 text-success"></i>Paket Terpopuler</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nama Paket</th>
                <th>Booking</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php $rank = 1; foreach($topPackages as $pkg): ?>
              <tr>
                <td>
                  <?php if($rank <= 3): ?>
                    <span class="badge <?= $rank == 1 ? 'bg-warning' : ($rank == 2 ? 'bg-secondary' : 'bg-danger') ?>"><?= $rank ?></span>
                  <?php else: ?>
                    <?= $rank ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($pkg['name']) ?></td>
                <td><span class="badge bg-primary-subtle text-primary"><?= $pkg['bookings'] ?></span></td>
                <td>Rp <?= number_format($pkg['revenue'] ?? 0, 0, ',', '.') ?></td>
              </tr>
              <?php $rank++; endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Weekday Distribution -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-calendar-week me-2 text-info"></i>Booking per Hari</h6>
      </div>
      <div class="card-body">
        <canvas id="weekdayChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Payment Methods -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>Metode Pembayaran</h6>
      </div>
      <div class="card-body">
        <canvas id="paymentChart" height="200"></canvas>
      </div>
    </div>
  </div>
  
  <!-- Quick Stats -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Insight Cepat</h6>
      </div>
      <div class="card-body">
        <?php
        $avgOrder = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;
        $bestDay = !empty($weekdayData) ? array_search(max($weekdayData), $weekdayData) : 0;
        $dayNames = ['', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        ?>
        <div class="d-flex align-items-center mb-3">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-shopping-cart text-white"></i>
          </div>
          <div>
            <div class="small text-muted">Rata-rata Nilai Order</div>
            <div class="fw-bold">Rp <?= number_format($avgOrder, 0, ',', '.') ?></div>
          </div>
        </div>
        <div class="d-flex align-items-center mb-3">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <i class="fas fa-calendar-day text-white"></i>
          </div>
          <div>
            <div class="small text-muted">Hari Tersibuk</div>
            <div class="fw-bold"><?= $dayNames[$bestDay] ?? '-' ?></div>
          </div>
        </div>
        <div class="d-flex align-items-center mb-3">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
            <i class="fas fa-percentage text-white"></i>
          </div>
          <div>
            <div class="small text-muted">Conversion Rate</div>
            <div class="fw-bold"><?= $totalUsers > 0 ? number_format(($totalBookings / $totalUsers) * 100, 1) : 0 ?>%</div>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
            <i class="fas fa-heart text-white"></i>
          </div>
          <div>
            <div class="small text-muted">Customer Satisfaction</div>
            <div class="fw-bold"><?= number_format(($avgRating / 5) * 100, 0) ?>%</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_map(function($m) { return date('M Y', strtotime($m['month'] . '-01')); }, $monthlyBookings)) ?>,
    datasets: [{
      label: 'Revenue (Juta Rp)',
      data: <?= json_encode(array_map(function($m) { return round(($m['revenue'] ?? 0) / 1000000, 1); }, $monthlyBookings)) ?>,
      borderColor: '#667eea',
      backgroundColor: 'rgba(102,126,234,0.1)',
      fill: true,
      tension: 0.4
    }, {
      label: 'Booking',
      data: <?= json_encode(array_map(function($m) { return $m['total_bookings']; }, $monthlyBookings)) ?>,
      borderColor: '#28a745',
      backgroundColor: 'transparent',
      tension: 0.4,
      yAxisID: 'y1'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: {
      y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Revenue (Juta Rp)' } },
      y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Booking' } }
    }
  }
});

// Rating Chart
const ratingCtx = document.getElementById('ratingChart').getContext('2d');
new Chart(ratingCtx, {
  type: 'doughnut',
  data: {
    labels: ['5 ⭐', '4 ⭐', '3 ⭐', '2 ⭐', '1 ⭐'],
    datasets: [{
      data: [
        <?= $ratingDist[5] ?? 0 ?>,
        <?= $ratingDist[4] ?? 0 ?>,
        <?= $ratingDist[3] ?? 0 ?>,
        <?= $ratingDist[2] ?? 0 ?>,
        <?= $ratingDist[1] ?? 0 ?>
      ],
      backgroundColor: ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545']
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Weekday Chart
const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
new Chart(weekdayCtx, {
  type: 'bar',
  data: {
    labels: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
    datasets: [{
      label: 'Booking',
      data: [
        <?= $weekdayData[1] ?? 0 ?>,
        <?= $weekdayData[2] ?? 0 ?>,
        <?= $weekdayData[3] ?? 0 ?>,
        <?= $weekdayData[4] ?? 0 ?>,
        <?= $weekdayData[5] ?? 0 ?>,
        <?= $weekdayData[6] ?? 0 ?>,
        <?= $weekdayData[7] ?? 0 ?>
      ],
      backgroundColor: 'rgba(102,126,234,0.8)',
      borderRadius: 8
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// Payment Chart
const paymentCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(paymentCtx, {
  type: 'pie',
  data: {
    labels: <?= json_encode(array_column($paymentMethods, 'payment_method')) ?>,
    datasets: [{
      data: <?= json_encode(array_column($paymentMethods, 'count')) ?>,
      backgroundColor: ['#667eea', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1']
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>

<?php require 'includes/admin_footer.php'; ?>
