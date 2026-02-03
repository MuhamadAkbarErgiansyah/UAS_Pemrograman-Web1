<?php
require 'config.php';
$pageTitle = "Wishlist Saya";
require 'header.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user wishlists
$stmt = $pdo->prepare("
    SELECT i.*, w.created_at as wishlisted_at, 
           (SELECT AVG(rating) FROM reviews WHERE item_id = i.id) as avg_rating
    FROM wishlists w
    LEFT JOIN items i ON w.item_id = i.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$userId]);
$wishlists = $stmt->fetchAll();

// Handle delete
if($_POST['action'] ?? false === 'delete') {
    $itemId = $_POST['item_id'] ?? 0;
    $deleteStmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND item_id = ?");
    if($deleteStmt->execute([$userId, $itemId])) {
        echo "<div class='alert alert-success alert-dismissible fade show' style='margin: 1rem;'>
                Paket dihapus dari Wishlist!
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        header("Location: wishlist.php");
        exit;
    }
}
?>

<!-- BREADCRUMB -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item"><a href="index.php" style="color: white;">Beranda</a></li>
        <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);">Wishlist Saya</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <!-- Header -->
  <div class="mb-5" data-aos="fade-down">
    <h1 class="fw-bold mb-2" style="color: #1f2937;">
      <i class="fas fa-heart me-2" style="color: #667eea;"></i>Wishlist Saya
    </h1>
    <p class="text-muted">Paket-paket pilihan yang ingin Anda booking nanti</p>
  </div>

  <!-- Results -->
  <div class="row g-4" data-aos="fade-up">
    <?php if(count($wishlists) > 0): ?>
      <?php foreach($wishlists as $item): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <!-- Image -->
            <div class="position-relative" style="height: 250px; overflow: hidden;">
              <img src="<?= htmlspecialchars($item['image']) ?>" 
                   alt="<?= htmlspecialchars($item['title']) ?>" 
                   class="w-100 h-100" style="object-fit: cover;">
              <div class="position-absolute top-0 end-0 p-3">
                <span class="badge bg-success">
                  <i class="fas fa-star me-1"></i><?= round($item['avg_rating'] ?? 0, 1) ?>
                </span>
              </div>
              <div class="position-absolute top-0 start-0 p-3">
                <small class="badge bg-primary">
                  <i class="fas fa-heart me-1"></i>Tersimpan
                </small>
              </div>
            </div>

            <!-- Content -->
            <div class="card-body p-4">
              <h5 class="card-title fw-bold mb-2" style="color: #1f2937;">
                <?= htmlspecialchars($item['title']) ?>
              </h5>
              
              <p class="text-muted small mb-3">
                <?= htmlspecialchars(substr($item['summary'], 0, 60)) ?>...
              </p>

              <h4 class="fw-bold mb-3" style="color: #667eea;">
                <?= htmlspecialchars($item['price']) ?>
              </h4>

              <small class="text-muted d-block mb-3">
                <i class="fas fa-calendar me-1"></i>Disimpan pada <?= date('d M Y', strtotime($item['wishlisted_at'])) ?>
              </small>

              <div class="d-grid gap-2">
                <a href="detail.php?slug=<?= $item['slug'] ?>" class="btn btn-primary rounded-3">
                  <i class="fas fa-eye me-2"></i>Lihat Detail
                </a>
                <a href="pemesanan.php?paket=<?= $item['slug'] ?>" class="btn btn-success rounded-3">
                  <i class="fas fa-calendar-check me-2"></i>Pesan Sekarang
                </a>
              </div>

              <form method="POST" class="mt-3">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" name="action" value="delete" class="btn btn-outline-danger w-100 rounded-3">
                  <i class="fas fa-trash me-2"></i>Hapus dari Wishlist
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info text-center py-5 rounded-4">
          <i class="fas fa-heart fa-3x mb-3" style="color: #667eea; opacity: 0.3;"></i>
          <h5>Wishlist Anda kosong</h5>
          <p class="text-muted mb-4">Mulai tambahkan paket favorit Anda sekarang</p>
          <a href="paket.php" class="btn btn-primary rounded-3">
            <i class="fas fa-compass me-2"></i>Jelajahi Paket
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require 'footer.php'; ?>
