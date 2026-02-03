<?php
require 'config.php';
$pageTitle = "Detail Paket - " . ($_GET['slug'] ?? 'Paket');
require 'header.php';

// Ambil slug dari URL
$slug = $_GET['slug'] ?? '';

if(empty($slug)) {
  header('Location: paket.php');
  exit;
}

// Ambil data dari database berdasarkan slug
$stmt = $pdo->prepare("SELECT * FROM items WHERE slug = ?");
$stmt->execute([$slug]);
$item = $stmt->fetch();

// Hitung average rating
if($item && $item['id']) {
  // Ambil reviews untuk paket ini
  $reviewStmt = $pdo->prepare("SELECT r.id, r.item_id, r.user_id, r.rating, r.comment, r.created_at, 
                              COALESCE(u.name, 'Anonim') as username,
                              rr.reply_text, rr.created_at as reply_date
                              FROM reviews r 
                              LEFT JOIN users u ON r.user_id = u.id 
                              LEFT JOIN review_replies rr ON r.id = rr.review_id
                              WHERE r.item_id = ? 
                              ORDER BY r.created_at DESC LIMIT 5");
  $reviewStmt->execute([$item['id']]);
  $reviews = $reviewStmt->fetchAll();
  
  $ratingStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE item_id = ?");
  $ratingStmt->execute([$item['id']]);
  $ratingData = $ratingStmt->fetch();
  $avgRating = round($ratingData['avg_rating'] ?? 0, 1);
  $totalReviews = $ratingData['total_reviews'] ?? 0;
} else {
  $reviews = [];
  $avgRating = 0;
  $totalReviews = 0;
}

// Jika data tidak ditemukan
if(!$item){
  echo "<div class='container py-5'>
          <div class='alert alert-danger text-center'>
            ❌ Data tidak ditemukan untuk paket yang Anda cari
          </div>
          <div class='text-center'>
            <a href='index.php' class='btn btn-primary'>
              <i class='fas fa-arrow-left me-2'></i>Kembali ke Beranda
            </a>
          </div>
        </div>";
  require 'footer.php';
  exit;
}
?>

<!-- BREADCRUMB -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item"><a href="index.php" style="color: white;">Beranda</a></li>
        <li class="breadcrumb-item"><a href="paket.php" style="color: white;">Paket Wisata</a></li>
        <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);"><?= htmlspecialchars($item['title']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- HERO GALLERY SECTION -->
<div class="position-relative" style="background: #f8f9fa; padding: 2rem 0;">
  <div class="container">
    <div class="row g-3">
      <!-- Main Image -->
      <div class="col-lg-7" data-aos="fade-right">
        <div class="position-relative rounded-4 overflow-hidden shadow-lg" style="height: 500px;">
          <img id="mainImage" src="<?= htmlspecialchars($item['image']) ?>" 
               alt="<?= htmlspecialchars($item['title']) ?>"
               class="w-100 h-100" style="object-fit: cover;">
          <div class="position-absolute top-0 start-0 p-3">
            <span class="badge bg-success" style="font-size: 0.9rem;">
              <i class="fas fa-check-circle me-1"></i>Tersedia
            </span>
          </div>
        </div>
      </div>

      <!-- BOOKING CARD -->
      <div class="col-lg-5" data-aos="fade-left">
        <div class="card border-0 shadow-lg rounded-4 sticky-top" style="top: 100px;">
          <div class="card-body p-4">
            <!-- Title & Rating -->
            <h2 class="fw-bold mb-2" style="color: #1f2937;"><?= htmlspecialchars($item['title']) ?></h2>
            
            <!-- Star Rating -->
            <div class="mb-3 d-flex align-items-center gap-2">
              <div class="d-flex gap-1">
                <?php for($i = 1; $i <= 5; $i++): ?>
                  <i class="fas fa-star" style="color: <?= $i <= round($avgRating) ? '#ffc107' : '#ddd' ?>; font-size: 1.1rem;"></i>
                <?php endfor; ?>
              </div>
              <small class="text-muted">
                <strong><?= $avgRating ?></strong> (<?= $totalReviews ?> review)
              </small>
            </div>

            <hr>

            <!-- Price Section -->
            <div class="mb-4">
              <small class="text-muted d-block mb-2">Harga Mulai Dari</small>
              <h3 class="fw-bold" style="color: #667eea; font-size: 2rem;">
                <?= htmlspecialchars($item['price']) ?>
              </h3>
              <small class="text-muted">/per orang (Min. 2 orang)</small>
            </div>

            <!-- Booking Buttons -->
            <div class="d-grid gap-2 mb-4">
              <a href="pemesanan.php?paket=<?= $item['slug'] ?>" class="btn btn-lg" 
                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-weight: 600;">
                <i class="fas fa-calendar-check me-2"></i>Pesan Sekarang
              </a>
              <?php if(isset($_SESSION['user_id'])): ?>
                <button type="button" class="btn btn-outline-primary btn-lg" id="addWishlistBtn">
                  <i class="fas fa-heart me-2"></i><span id="wishlistText">Simpan ke Wishlist</span>
                </button>
              <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary btn-lg w-100">
                  <i class="fas fa-heart me-2"></i>Simpan ke Wishlist
                </a>
              <?php endif; ?>
            </div>

            <!-- Quick Info -->
            <div class="row text-center g-2 small">
              <div class="col-6">
                <div class="p-2 rounded-3" style="background: #f0f4ff;">
                  <i class="fas fa-users text-primary"></i><br>
                  <strong>Grup Wisata</strong>
                </div>
              </div>
              <div class="col-6">
                <div class="p-2 rounded-3" style="background: #f0f4ff;">
                  <i class="fas fa-clock text-primary"></i><br>
                  <strong>1 Hari</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container py-5">
  <div class="row g-4">
    <!-- LEFT COLUMN -->
    <div class="col-lg-7">
      
      <!-- DESKRIPSI -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
        <h3 class="fw-bold mb-3" style="color: #1f2937;">
          <i class="fas fa-info-circle me-2" style="color: #667eea;"></i>Tentang Paket
        </h3>
        <p class="text-muted lh-lg">
          <?= nl2br(htmlspecialchars($item['summary'])) ?>
        </p>
        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-left: 4px solid #667eea;">
          <?= nl2br(htmlspecialchars($item['content'])) ?>
        </div>
      </div>

      <!-- ITINERARY -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
        <h3 class="fw-bold mb-4" style="color: #1f2937;">
          <i class="fas fa-map me-2" style="color: #667eea;"></i>Itinerary
        </h3>
        <div class="timeline">
          <div class="timeline-item mb-4">
            <div class="d-flex">
              <div class="timeline-marker" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-sun text-white"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold">04:30 - Penjemputan Peserta</h6>
                <p class="text-muted small mb-0">Kami akan menjemput Anda di lokasi yang telah ditentukan</p>
              </div>
            </div>
          </div>
          <div class="timeline-item mb-4">
            <div class="d-flex">
              <div class="timeline-marker" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-camera text-white"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold">05:15 - Tiba di Spot Sunrise</h6>
                <p class="text-muted small mb-0">Nikmati sensasi matahari terbit dari puncak gunung</p>
              </div>
            </div>
          </div>
          <div class="timeline-item mb-4">
            <div class="d-flex">
              <div class="timeline-marker" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-utensils text-white"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold">06:00 - Sesi Dokumentasi Foto/Video</h6>
                <p class="text-muted small mb-0">Abadikan momen indah bersama guide profesional kami</p>
              </div>
            </div>
          </div>
          <div class="timeline-item">
            <div class="d-flex">
              <div class="timeline-marker" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-home text-white"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold">07:00 - Sarapan & Kembali</h6>
                <p class="text-muted small mb-0">Sarapan khas lokal sebelum kembali ke tempat asal</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- INCLUDES -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
        <h3 class="fw-bold mb-4" style="color: #1f2937;">
          <i class="fas fa-check-circle me-2" style="color: #667eea;"></i>Apa Saja yang Didapat?
        </h3>
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="d-flex gap-3 p-3 rounded-3" style="background: #f0f4ff;">
              <div>
                <i class="fas fa-user-tie fa-lg" style="color: #667eea;"></i>
              </div>
              <div>
                <strong>Guide Lokal</strong>
                <p class="text-muted small mb-0">Berpengalaman & ramah</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex gap-3 p-3 rounded-3" style="background: #f0f4ff;">
              <div>
                <i class="fas fa-camera fa-lg" style="color: #667eea;"></i>
              </div>
              <div>
                <strong>Dokumentasi</strong>
                <p class="text-muted small mb-0">Foto & video profesional</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex gap-3 p-3 rounded-3" style="background: #f0f4ff;">
              <div>
                <i class="fas fa-bus fa-lg" style="color: #667eea;"></i>
              </div>
              <div>
                <strong>Transport</strong>
                <p class="text-muted small mb-0">AC & nyaman</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex gap-3 p-3 rounded-3" style="background: #f0f4ff;">
              <div>
                <i class="fas fa-ticket-alt fa-lg" style="color: #667eea;"></i>
              </div>
              <div>
                <strong>Tiket Masuk</strong>
                <p class="text-muted small mb-0">Sudah termasuk</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- REVIEWS -->
      <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold mb-0" style="color: #1f2937;">
            <i class="fas fa-comments me-2" style="color: #667eea;"></i>Review dari Pengguna
          </h3>
          <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
              <i class="fas fa-pen me-1"></i>Tulis Review
            </button>
          <?php else: ?>
            <a href="login.php" class="btn btn-sm btn-primary">
              <i class="fas fa-pen me-1"></i>Tulis Review
            </a>
          <?php endif; ?>
        </div>

        <div id="reviewsList">
          <?php if($totalReviews > 0): ?>
            <?php foreach($reviews as $review): ?>
              <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <strong><?= htmlspecialchars($review['username'] ?? 'Anonim') ?></strong><br>
                    <small class="text-muted"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                  </div>
                  <div class="text-end">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star" style="color: <?= $i <= $review['rating'] ? '#ffc107' : '#ddd' ?>; font-size: 0.9rem;"></i>
                    <?php endfor; ?>
                  </div>
                </div>
                <p class="text-muted small mb-2"><?= htmlspecialchars($review['comment']) ?></p>
                
                <?php if(!empty($review['reply_text'])): ?>
                <div class="bg-light p-3 rounded-3 mt-2 ms-4 border-start border-3 border-primary">
                  <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-reply text-primary me-2"></i>
                    <strong class="small text-primary">Balasan Admin</strong>
                    <small class="text-muted ms-2"><?= date('d M Y', strtotime($review['reply_date'])) ?></small>
                  </div>
                  <p class="text-muted small mb-0"><?= htmlspecialchars($review['reply_text']) ?></p>
                </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted text-center py-4">
              <i class="fas fa-comment-slash me-2"></i>Belum ada review. Jadilah yang pertama memberikan review!
            </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- REVIEW MODAL -->
      <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-primary text-white border-0">
              <h5 class="modal-title"><i class="fas fa-star me-2"></i>Tulis Review</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviewForm">
              <div class="modal-body p-4">
                <div class="mb-3">
                  <label class="form-label fw-bold">Rating</label>
                  <div class="rating-input d-flex gap-2">
                    <input type="hidden" id="ratingValue" value="0">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star fa-2x" style="color: #ddd; cursor: pointer;" data-rating="<?= $i ?>"></i>
                    <?php endfor; ?>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="reviewComment" class="form-label fw-bold">Komentar</label>
                  <textarea id="reviewComment" class="form-control rounded-3" rows="4" 
                            placeholder="Bagikan pengalaman Anda (minimal 10 karakter)"></textarea>
                </div>
              </div>
              <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-3">
                  <i class="fas fa-paper-plane me-2"></i>Kirim Review
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="col-lg-5">
      <!-- PAKET INFO CARD -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 sticky-top" style="top: 200px;" data-aos="fade-left">
        <h5 class="fw-bold mb-3" style="color: #1f2937;">
          <i class="fas fa-list-check me-2" style="color: #667eea;"></i>Informasi Paket
        </h5>
        <div class="mb-3">
          <small class="text-muted d-block mb-1">
            <i class="fas fa-clock me-2"></i>Durasi
          </small>
          <strong>1 Hari (Sunrise - Sarapan)</strong>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block mb-1">
            <i class="fas fa-users me-2"></i>Kapasitas Grup
          </small>
          <strong>Min. 2 - Maks. 15 Orang</strong>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block mb-1">
            <i class="fas fa-map-marker-alt me-2"></i>Lokasi
          </small>
          <strong>Gunung Putri Lembang</strong>
        </div>
        <div class="mb-3">
          <small class="text-muted d-block mb-1">
            <i class="fas fa-calendar me-2"></i>Tipe
          </small>
          <strong>Grup Wisata</strong>
        </div>
        <hr>
        <div>
          <small class="text-muted d-block mb-1">Kategori</small>
          <span class="badge bg-primary me-2">Adventure</span>
          <span class="badge bg-info">Photography</span>
        </div>
      </div>

      <!-- CONTACT & SUPPORT -->
      <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-left">
        <h5 class="fw-bold mb-3" style="color: #1f2937;">
          <i class="fas fa-headset me-2" style="color: #667eea;"></i>Butuh Bantuan?
        </h5>
        <p class="small text-muted mb-3">Hubungi tim kami untuk pertanyaan atau customisasi paket.</p>
        <div class="d-grid gap-2">
          <a href="https://wa.me/6289508891566" class="btn btn-success">
            <i class="fab fa-whatsapp me-2"></i>Chat via WhatsApp
          </a>
          <a href="mailto:info@explorebdg.id" class="btn btn-outline-secondary">
            <i class="fas fa-envelope me-2"></i>Email Kami
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .timeline-item {
    position: relative;
  }
  .timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    width: 2px;
    height: calc(100% - 40px);
    background: #ddd;
  }
  .sticky-top {
    z-index: 10;
  }
  @media (max-width: 991px) {
    .sticky-top {
      position: static;
      top: auto !important;
    }
  }
</style>

<script>
  const itemId = <?= $item['id'] ?>;
  const userId = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>;

  // Initialize wishlist button
  if(userId) {
    checkWishlistStatus();
  }

  function checkWishlistStatus() {
    fetch(`api/reviews_wishlist.php?action=check_wishlist&item_id=${itemId}`)
    .then(r => r.json())
    .then(data => {
      if(data.in_wishlist) {
        updateWishlistButton(true);
      }
    });
  }

  function updateWishlistButton(isInWishlist) {
    const btn = document.getElementById('addWishlistBtn');
    const text = document.getElementById('wishlistText');
    
    if(isInWishlist) {
      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-primary');
      text.textContent = 'Sudah Tersimpan';
    } else {
      btn.classList.add('btn-outline-primary');
      btn.classList.remove('btn-primary');
      text.textContent = 'Simpan ke Wishlist';
    }
  }

  // Wishlist button click
  document.getElementById('addWishlistBtn')?.addEventListener('click', function() {
    const formData = new FormData();
    formData.append('item_id', itemId);

    const btn = this;
    const text = document.getElementById('wishlistText');

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
            updateWishlistButton(false);
            showNotification('Paket dihapus dari Wishlist', 'warning');
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
            updateWishlistButton(true);
            showNotification('Paket ditambahkan ke Wishlist!', 'success');
          } else {
            showNotification(data.message || 'Gagal menambah wishlist', 'danger');
          }
        });
      }
    });
  });

  // Rating star functionality
  document.querySelectorAll('.rating-input i').forEach(star => {
    star.addEventListener('click', function() {
      const rating = this.dataset.rating;
      document.getElementById('ratingValue').value = rating;
      updateStarDisplay(rating);
    });
    
    star.addEventListener('mouseover', function() {
      const rating = this.dataset.rating;
      document.querySelectorAll('.rating-input i').forEach((s, idx) => {
        s.style.color = idx < rating ? '#ffc107' : '#ddd';
      });
    });
  });

  document.querySelector('.rating-input')?.addEventListener('mouseleave', function() {
    const rating = document.getElementById('ratingValue').value;
    updateStarDisplay(rating);
  });

  function updateStarDisplay(rating) {
    document.querySelectorAll('.rating-input i').forEach((s, idx) => {
      s.style.color = idx < rating ? '#ffc107' : '#ddd';
    });
  }

  // Submit review
  document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rating = document.getElementById('ratingValue').value;
    const comment = document.getElementById('reviewComment').value;

    if(!rating) {
      showNotification('Pilih rating terlebih dahulu', 'warning');
      return;
    }

    if(comment.length < 10) {
      showNotification('Komentar minimal 10 karakter', 'warning');
      return;
    }

    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('rating', rating);
    formData.append('comment', comment);

    fetch('api/reviews_wishlist.php?action=submit_review', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if(data.success) {
        showNotification('Review berhasil ditambahkan!', 'success');
        document.getElementById('reviewForm').reset();
        document.getElementById('ratingValue').value = 0;
        updateStarDisplay(0);
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
        if(modal) modal.hide();
        
        // Reload reviews
        setTimeout(() => location.reload(), 1500);
      } else {
        showNotification(data.message, 'danger');
      }
    })
    .catch(err => {
      console.error(err);
      showNotification('Terjadi kesalahan', 'danger');
    });
  });

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
