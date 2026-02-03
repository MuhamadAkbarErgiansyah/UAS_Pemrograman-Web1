<?php
require 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: admin_login.php');
  exit;
}

// Check if user is admin
$userCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$userCheck->execute([$_SESSION['user_id']]);
$currentUser = $userCheck->fetch();

if(!$currentUser || $currentUser['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

// Handle delete review
if(isset($_POST['delete_review'])) {
  $reviewId = intval($_POST['review_id']);
  $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
  $stmt->execute([$reviewId]);
  $success = "Review berhasil dihapus!";
}

// Handle reply to review
if(isset($_POST['reply_review'])) {
  $reviewId = intval($_POST['review_id']);
  $replyText = trim($_POST['reply_text']);
  if($replyText) {
    // Check if reply exists
    $existingReply = $pdo->prepare("SELECT id FROM review_replies WHERE review_id = ?");
    $existingReply->execute([$reviewId]);
    
    if($existingReply->fetch()) {
      // Update existing
      $stmt = $pdo->prepare("UPDATE review_replies SET reply_text = ?, updated_at = NOW() WHERE review_id = ?");
      $stmt->execute([$replyText, $reviewId]);
    } else {
      // Insert new
      $stmt = $pdo->prepare("INSERT INTO review_replies (review_id, admin_id, reply_text) VALUES (?, ?, ?)");
      $stmt->execute([$reviewId, $_SESSION['user_id'], $replyText]);
    }
    
    // Send notification to user
    $reviewData = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
    $reviewData->execute([$reviewId]);
    $reviewUser = $reviewData->fetch();
    if($reviewUser) {
      $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'review', 'Admin telah membalas review Anda')");
      $notif->execute([$reviewUser['user_id']]);
    }
    
    $success = "Balasan berhasil disimpan!";
  }
}

// Get filter
$filter_rating = $_GET['rating'] ?? '';
$filter_package = $_GET['package'] ?? '';

// Build query
$query = "SELECT r.*, u.name as user_name, u.email as user_email, i.title as package_title, i.image as package_image,
          rr.reply_text, rr.created_at as reply_date 
          FROM reviews r 
          LEFT JOIN users u ON r.user_id = u.id 
          LEFT JOIN items i ON r.item_id = i.id 
          LEFT JOIN review_replies rr ON r.id = rr.review_id
          WHERE 1=1";
$params = [];

if($filter_rating && $filter_rating >= 1 && $filter_rating <= 5) {
  $query .= " AND r.rating = ?";
  $params[] = $filter_rating;
}

if($filter_package) {
  $query .= " AND r.item_id = ?";
  $params[] = $filter_package;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Get all packages for filter
$packages = $pdo->query("SELECT id, title FROM items ORDER BY title")->fetchAll();

// Get statistics
$stats = [];
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$stats['avg_rating'] = round($pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?? 0, 1);
$stats['5_star'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 5")->fetchColumn();
$stats['4_star'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 4")->fetchColumn();
$stats['3_star'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 3")->fetchColumn();
$stats['2_star'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 2")->fetchColumn();
$stats['1_star'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 1")->fetchColumn();

$pageTitle = "Monitoring Review";
$currentPage = "admin_reviews";
require 'includes/admin_header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-star me-2 text-warning"></i>Monitoring Review</h2>
    <p class="text-muted mb-0">Pantau feedback dari pelanggan untuk meningkatkan kualitas layanan</p>
  </div>
</div>

<?php if(isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fas fa-check-circle me-2"></i><?= $success ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="display-4 fw-bold text-primary mb-2"><?= $stats['total'] ?></div>
        <p class="text-muted mb-0">Total Review</p>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="display-4 fw-bold text-warning mb-2">
          <i class="fas fa-star"></i> <?= $stats['avg_rating'] ?>
        </div>
        <p class="text-muted mb-0">Rating Rata-rata</p>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Distribusi Rating</h6>
        <?php for($i = 5; $i >= 1; $i--): ?>
          <?php 
          $count = $stats[$i.'_star'];
          $percentage = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
          ?>
          <div class="d-flex align-items-center mb-2">
            <div style="width: 60px;">
              <?= $i ?> <i class="fas fa-star text-warning"></i>
            </div>
            <div class="flex-grow-1 mx-2">
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%"></div>
              </div>
            </div>
            <div style="width: 40px;" class="text-muted small"><?= $count ?></div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Filter Rating</label>
        <select name="rating" class="form-select">
          <option value="">Semua Rating</option>
          <?php for($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>" <?= $filter_rating == $i ? 'selected' : '' ?>>
              <?= $i ?> Bintang
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Filter Paket</label>
        <select name="package" class="form-select">
          <option value="">Semua Paket</option>
          <?php foreach($packages as $pkg): ?>
            <option value="<?= $pkg['id'] ?>" <?= $filter_package == $pkg['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($pkg['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary me-2">
          <i class="fas fa-filter me-2"></i>Filter
        </button>
        <a href="admin_reviews.php" class="btn btn-outline-secondary">
          <i class="fas fa-times me-2"></i>Reset
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Reviews List -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Daftar Review (<?= count($reviews) ?>)</h5>
  </div>
  <div class="card-body p-0">
    <?php if(!empty($reviews)): ?>
      <?php foreach($reviews as $review): ?>
      <div class="p-4 border-bottom">
        <div class="row">
          <div class="col-md-8">
            <div class="d-flex align-items-start">
              <!-- Avatar -->
              <div class="me-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px; font-size: 1.2rem;">
                  <?= strtoupper(substr($review['user_name'] ?? 'A', 0, 1)) ?>
                </div>
              </div>
              <div class="flex-grow-1">
                <!-- User & Rating -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($review['user_name'] ?? 'Anonim') ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($review['user_email'] ?? '-') ?></small>
                  </div>
                  <div class="text-end">
                    <div class="mb-1">
                      <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="color: <?= $i <= $review['rating'] ? '#ffc107' : '#ddd' ?>;"></i>
                      <?php endfor; ?>
                    </div>
                    <small class="text-muted"><?= date('d M Y, H:i', strtotime($review['created_at'])) ?></small>
                  </div>
                </div>
                
                <!-- Package Info -->
                <div class="mb-2">
                  <span class="badge bg-primary-subtle text-primary">
                    <i class="fas fa-box me-1"></i><?= htmlspecialchars($review['package_title'] ?? 'N/A') ?>
                  </span>
                </div>
                
                <!-- Comment -->
                <p class="mb-3 text-muted"><?= htmlspecialchars($review['comment']) ?></p>
                
                <!-- Admin Reply Section -->
                <?php if($review['reply_text']): ?>
                <div class="bg-success-subtle p-3 rounded mb-2">
                  <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-reply text-success me-2"></i>
                    <strong class="text-success">Balasan Admin</strong>
                    <small class="text-muted ms-2"><?= date('d M Y', strtotime($review['reply_date'])) ?></small>
                  </div>
                  <p class="mb-0"><?= htmlspecialchars($review['reply_text']) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Reply Form Toggle -->
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#replyForm<?= $review['id'] ?>">
                  <i class="fas fa-reply me-1"></i><?= $review['reply_text'] ? 'Edit Balasan' : 'Balas Review' ?>
                </button>
                
                <div class="collapse mt-3" id="replyForm<?= $review['id'] ?>">
                  <form method="POST">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                    <div class="mb-2">
                      <textarea name="reply_text" class="form-control" rows="3" placeholder="Tulis balasan untuk review ini..."><?= htmlspecialchars($review['reply_text'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="reply_review" class="btn btn-success btn-sm">
                      <i class="fas fa-paper-plane me-1"></i>Kirim Balasan
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 text-end d-flex align-items-start justify-content-end">
            <!-- Package Image -->
            <?php if($review['package_image']): ?>
              <img src="<?= htmlspecialchars($review['package_image']) ?>" alt="Package" 
                   class="rounded me-3" style="width: 80px; height: 60px; object-fit: cover;">
            <?php endif; ?>
            
            <!-- Actions -->
            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus review ini?')">
              <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
              <button type="submit" name="delete_review" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="fas fa-comment-slash fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada review</h5>
        <p class="text-muted">Review dari pelanggan akan muncul di sini</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
