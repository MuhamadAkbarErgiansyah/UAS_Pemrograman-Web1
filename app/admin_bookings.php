<?php
require 'config.php';
$pageTitle = "Kelola Pemesanan";
$currentPage = "bookings";

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

// Get filter parameter
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Handle approval/rejection
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $bookingId = $_POST['booking_id'];
    $notes = $_POST['notes'] ?? '';

    $approvalStmt = $pdo->prepare("
        INSERT INTO booking_approvals (reservation_id, status, approved_by, notes, approval_date)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE status = VALUES(status), approved_by = VALUES(approved_by), notes = VALUES(notes), approval_date = NOW()
    ");
    
    $approvalStmt->execute([$bookingId, $action, $_SESSION['user_id'], $notes]);
    
    // Send notification to customer
    $bookingData = $pdo->prepare("SELECT user_id, package_title FROM reservations WHERE id = ?");
    $bookingData->execute([$bookingId]);
    $booking = $bookingData->fetch();
    
    if($booking && $booking['user_id']) {
        $statusText = $action === 'approved' ? 'disetujui' : 'ditolak';
        $notifMsg = "Pemesanan paket '{$booking['package_title']}' telah $statusText oleh admin.";
        if($notes) $notifMsg .= " Catatan: $notes";
        
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'approval', ?)");
        $notif->execute([$booking['user_id'], $notifMsg]);
    }
    
    header("Location: admin_bookings.php?status=$status&success=1");
    exit;
}

// Build query based on filters
$query = "SELECT r.*, u.name as user_name, u.email as user_email, 
          i.title as item_title, i.price as item_price,
          COALESCE(ba.status, 'pending') as approval_status,
          ba.notes as admin_notes, ba.approval_date
          FROM reservations r 
          LEFT JOIN users u ON r.user_id = u.id 
          LEFT JOIN items i ON r.package_id = i.id
          LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
          WHERE 1=1";

if($status !== 'all') {
    if($status === 'pending') {
        $query .= " AND (ba.status IS NULL OR ba.status = 'pending')";
    } else {
        $query .= " AND ba.status = '$status'";
    }
}

if($search) {
    $searchSafe = addslashes($search);
    $query .= " AND (r.name LIKE '%$searchSafe%' OR r.email LIKE '%$searchSafe%' OR r.package_title LIKE '%$searchSafe%')";
}

$query .= " ORDER BY r.created_at DESC";
$bookings = $pdo->query($query)->fetchAll();

// Count stats
$countAll = $pdo->query("SELECT COUNT(*) as c FROM reservations")->fetch()['c'];
$countPending = $pdo->query("SELECT COUNT(*) as c FROM reservations r LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id WHERE ba.status IS NULL OR ba.status = 'pending'")->fetch()['c'];
$countApproved = $pdo->query("SELECT COUNT(*) as c FROM booking_approvals WHERE status = 'approved'")->fetch()['c'];
$countRejected = $pdo->query("SELECT COUNT(*) as c FROM booking_approvals WHERE status = 'rejected'")->fetch()['c'];

require 'includes/admin_header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-clipboard-list me-2" style="color: #667eea;"></i>Kelola Pemesanan</h2>
    <p class="text-muted mb-0">Approve, reject, atau proses pemesanan wisata dari pelanggan</p>
  </div>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fas fa-check-circle me-2"></i>Pemesanan berhasil diproses!
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card-custom p-4 mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-md-6">
      <label class="form-label fw-bold">Filter Status</label>
      <div class="btn-group w-100" role="group">
        <a href="?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
          Semua (<?= $countAll ?>)
        </a>
        <a href="?status=pending" class="btn <?= $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
          Pending (<?= $countPending ?>)
        </a>
        <a href="?status=approved" class="btn <?= $status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
          Approved (<?= $countApproved ?>)
        </a>
        <a href="?status=rejected" class="btn <?= $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
          Rejected (<?= $countRejected ?>)
        </a>
      </div>
    </div>
    <div class="col-md-6">
      <label class="form-label fw-bold">Cari Pemesanan</label>
      <form method="GET" class="d-flex gap-2">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
        <input type="text" name="search" class="form-control" placeholder="Nama, email, atau paket..." 
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Bookings Grid -->
<div class="row g-4">
  <?php if(!empty($bookings)): ?>
    <?php foreach($bookings as $booking): 
      $bookingStatus = $booking['approval_status'];
    ?>
    <div class="col-lg-6">
      <div class="card-custom overflow-hidden h-100">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 1.5rem;">
          <div class="d-flex justify-content-between align-items-start text-white">
            <div>
              <h5 class="fw-bold mb-1">
                <i class="fas fa-shopping-cart me-2"></i><?= htmlspecialchars($booking['package_title']) ?>
              </h5>
              <small>
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($booking['name']) ?> 
                (<?= htmlspecialchars($booking['email'] ?? $booking['user_email'] ?? '-') ?>)
              </small>
            </div>
            <span class="badge 
              <?php 
                if($bookingStatus === 'approved') echo 'bg-success';
                elseif($bookingStatus === 'rejected') echo 'bg-danger';
                else echo 'bg-warning text-dark';
              ?>">
              <?= ucfirst($bookingStatus) ?>
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <div class="row g-3 mb-4">
            <div class="col-6">
              <small class="text-muted d-block">Tanggal Wisata</small>
              <strong><?= date('d M Y', strtotime($booking['date_event'])) ?></strong>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">No. Telepon</small>
              <strong><?= htmlspecialchars($booking['contact']) ?></strong>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Harga Paket</small>
              <strong style="color: #667eea;"><?= htmlspecialchars($booking['item_price'] ?? '-') ?></strong>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Tanggal Pesan</small>
              <strong><?= date('d M Y H:i', strtotime($booking['created_at'])) ?></strong>
            </div>
          </div>

          <!-- Notes -->
          <div class="p-3 rounded-3 bg-light mb-4">
            <small class="text-muted d-block mb-1">Catatan Pelanggan:</small>
            <p class="mb-0 small"><?= htmlspecialchars($booking['note'] ?? 'Tidak ada catatan') ?></p>
          </div>

          <?php if($booking['admin_notes']): ?>
          <div class="p-3 rounded-3 mb-4" style="background: #f0f4ff;">
            <small class="text-muted d-block mb-1"><i class="fas fa-note-sticky me-1"></i>Catatan Admin:</small>
            <p class="mb-0 small"><?= htmlspecialchars($booking['admin_notes']) ?></p>
          </div>
          <?php endif; ?>

          <!-- Actions -->
          <?php if($bookingStatus === 'pending'): ?>
            <form method="POST">
              <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
              <textarea name="notes" class="form-control form-control-sm mb-3" 
                        placeholder="Tambahkan catatan untuk pelanggan (opsional)" rows="2"></textarea>
              <div class="d-grid gap-2">
                <button type="submit" name="action" value="approved" class="btn btn-success fw-bold">
                  <i class="fas fa-check-circle me-2"></i>Approve
                </button>
                <button type="submit" name="action" value="rejected" class="btn btn-danger fw-bold">
                  <i class="fas fa-times-circle me-2"></i>Reject
                </button>
              </div>
            </form>
          <?php else: ?>
            <div class="alert alert-info small mb-0">
              <i class="fas fa-info-circle me-2"></i>
              Sudah di-<strong><?= ucfirst($bookingStatus) ?></strong>
              <?php if($booking['approval_date']): ?>
                pada <?= date('d M Y H:i', strtotime($booking['approval_date'])) ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="card-custom p-5 text-center">
        <i class="fas fa-inbox fa-3x mb-3" style="color: #667eea;"></i>
        <h5>Tidak ada pemesanan</h5>
        <p class="text-muted mb-0">Coba ubah filter status atau kata kunci pencarian</p>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require 'includes/admin_footer.php'; ?>
