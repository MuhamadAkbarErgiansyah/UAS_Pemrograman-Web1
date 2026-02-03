<?php
require 'config.php';
$pageTitle = "Riwayat Pemesanan";
require 'header.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get bookings - dengan kolom yang BENAR
$stmt = $pdo->prepare("
    SELECT r.*,
           COALESCE(ba.status, 'pending') as booking_status,
           ba.approval_date, ba.approved_by,
           i.slug
    FROM reservations r
    LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
    LEFT JOIN items i ON r.package_id = i.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item"><a href="index.php" style="color: white;">Beranda</a></li>
        <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);">Riwayat Pemesanan</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <div class="mb-5" data-aos="fade-down">
    <h1 class="fw-bold mb-2" style="color: #1f2937;">
      <i class="fas fa-clipboard-list me-2" style="color: #667eea;"></i>Riwayat Pemesanan Saya
    </h1>
    <p class="text-muted">Pantau status pemesanan wisata Anda</p>
  </div>

  <div class="row" data-aos="fade-up">
    <?php if(count($bookings) > 0): ?>
      <?php foreach($bookings as $booking): 
        $status = $booking['booking_status'];
        $statusIcons = ['pending' => 'fas fa-hourglass-half', 'approved' => 'fas fa-check-circle', 'rejected' => 'fas fa-times-circle', 'completed' => 'fas fa-flag-checkered'];
        $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'completed' => 'info'];
        $statusLabels = ['pending' => 'Menunggu Approval', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai'];
      ?>
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="d-flex gap-3 p-4" style="background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
              <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="<?= $statusIcons[$status] ?> fa-lg text-white"></i>
              </div>
              <div class="flex-grow-1">
                <h5 class="fw-bold mb-1" style="color: #1f2937;">
                  <?= htmlspecialchars($booking['package_title']) ?>
                </h5>
                <small class="text-muted">
                  ID: <strong>EB-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                </small>
              </div>
              <div class="text-end">
                <span class="badge bg-<?= $statusColors[$status] ?>" style="font-size: 0.85rem;">
                  <?= $statusLabels[$status] ?>
                </span>
              </div>
            </div>

            <div class="card-body p-4">
              <div class="mb-4">
                <div class="progress rounded-3" style="height: 8px;">
                  <div class="progress-bar" role="progressbar" style="width: <?= ($status === 'completed' || $status === 'approved' ? 100 : ($status === 'pending' ? 30 : 0)) ?>%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                </div>
              </div>

              <div class="row g-2 mb-4">
                <div class="col-6">
                  <small class="text-muted d-block mb-1">Tanggal Perjalanan</small>
                  <strong><?= date('d M Y', strtotime($booking['date_event'] ?? 'now')) ?></strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block mb-1">Nama Pemesan</small>
                  <strong><?= htmlspecialchars($booking['name']) ?></strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block mb-1">No. Telepon</small>
                  <strong><?= htmlspecialchars($booking['contact']) ?></strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block mb-1">Tanggal Pesan</small>
                  <strong><?= date('d M Y', strtotime($booking['created_at'] ?? 'now')) ?></strong>
                </div>
              </div>

              <div class="d-grid gap-2">
                <a href="detail.php?slug=<?= htmlspecialchars($booking['slug'] ?? '') ?>" class="btn btn-sm btn-outline-primary rounded-3">
                  <i class="fas fa-eye me-2"></i>Lihat Detail Paket
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 wishlist-btn" data-item-id="<?= $booking['package_id'] ?>" onclick="toggleWishlistFromBooking(this, <?= $booking['package_id'] ?>)">
                  <i class="fas fa-heart me-2"></i><span class="wishlist-text">Simpan ke Wishlist</span>
                </button>
                
                <!-- Export Buttons -->
                <div class="btn-group" role="group">
                  <a href="export_booking.php?id=<?= $booking['id'] ?>&format=pdf" target="_blank" class="btn btn-sm btn-outline-secondary rounded-start">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                  </a>
                  <a href="export_booking.php?id=<?= $booking['id'] ?>&format=excel" class="btn btn-sm btn-outline-secondary rounded-end">
                    <i class="fas fa-file-excel me-1"></i>Excel
                  </a>
                </div>
                
                <?php if($status === 'approved'): ?>
                  <button class="btn btn-sm btn-outline-success rounded-3" onclick="window.location.href=`generate_certificate.php?booking_id=<?= $booking['id'] ?>&customer_name=<?= urlencode($_SESSION['username'] ?? 'Customer') ?>`">
                    <i class="fas fa-certificate me-2"></i>Cetak Sertifikat
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info text-center py-5 rounded-4">
          <i class="fas fa-inbox fa-3x mb-3" style="color: #667eea; opacity: 0.3;"></i>
          <h5>Belum ada pemesanan</h5>
          <p class="text-muted mb-4">Mulai pesan paket wisata favorit Anda sekarang</p>
          <a href="paket.php" class="btn btn-primary rounded-3">
            <i class="fas fa-compass me-2"></i>Jelajahi Paket
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Initialize wishlist status on page load
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
      checkWishlistStatusForButton(btn);
    });
  });

  function checkWishlistStatusForButton(btn) {
    const itemId = btn.dataset.itemId;
    fetch(`api/reviews_wishlist.php?action=check_wishlist&item_id=${itemId}`)
    .then(r => r.json())
    .then(data => {
      if(data.in_wishlist) {
        updateWishlistButtonStatus(btn, true);
      } else {
        updateWishlistButtonStatus(btn, false);
      }
    });
  }

  function updateWishlistButtonStatus(btn, isInWishlist) {
    const text = btn.querySelector('.wishlist-text');
    if(isInWishlist) {
      btn.classList.remove('btn-outline-danger');
      btn.classList.add('btn-danger');
      text.textContent = 'Sudah Disimpan';
    } else {
      btn.classList.add('btn-outline-danger');
      btn.classList.remove('btn-danger');
      text.textContent = 'Simpan ke Wishlist';
    }
  }

  function toggleWishlistFromBooking(btn, itemId) {
    const formData = new FormData();
    formData.append('item_id', itemId);

    // Check current status
    fetch(`api/reviews_wishlist.php?action=check_wishlist&item_id=${itemId}`)
    .then(r => r.json())
    .then(data => {
      if(data.in_wishlist) {
        // Remove from wishlist
        fetch('api/reviews_wishlist.php?action=remove_wishlist', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          if(data.success) {
            updateWishlistButtonStatus(btn, false);
            showNotification('Dihapus dari Wishlist', 'warning');
          }
        });
      } else {
        // Add to wishlist
        fetch('api/reviews_wishlist.php?action=add_wishlist', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(data => {
          if(data.success) {
            updateWishlistButtonStatus(btn, true);
            showNotification('Ditambahkan ke Wishlist!', 'success');
          }
        });
      }
    });
  }

  function showNotification(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '80px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 3000);
  }
</script>

<?php require 'footer.php'; ?>
