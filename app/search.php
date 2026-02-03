<?php
require 'config.php';
$pageTitle = "Cari Paket Wisata";
require 'header.php';

// Get filter parameters
$priceMin = $_GET['price_min'] ?? 0;
$priceMax = $_GET['price_max'] ?? 10000000;
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';

// Build query
$query = "SELECT * FROM items WHERE 1=1";
$params = [];

if($search) {
    $query .= " AND (title LIKE ? OR content LIKE ? OR summary LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

$query .= " AND price BETWEEN ? AND ?";
$params = array_merge($params, [$priceMin, $priceMax]);

// Sorting
switch($sortBy) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY id DESC LIMIT 12";
        break;
    default: // newest
        $query .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Get price range for filter
$rangeStmt = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM items");
$rangeData = $rangeStmt->fetch();
$minPriceAvailable = $rangeData['min_price'] ?? 0;
$maxPriceAvailable = $rangeData['max_price'] ?? 10000000;
?>

<!-- BREADCRUMB -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item"><a href="index.php" style="color: white;">Beranda</a></li>
        <li class="breadcrumb-item active" style="color: rgba(255,255,255,0.8);">Cari Paket</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- SIDEBAR FILTER -->
    <div class="col-lg-3" data-aos="fade-right">
      <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
        <h5 class="fw-bold mb-4" style="color: #1f2937;">
          <i class="fas fa-filter me-2" style="color: #667eea;"></i>Filter Pencarian
        </h5>

        <form method="GET" id="filterForm" class="needs-validation">
          <!-- Search Input -->
          <div class="mb-4">
            <label class="form-label fw-bold">Cari Paket</label>
            <input type="text" name="search" class="form-control rounded-3" 
                   placeholder="Nama paket wisata..." value="<?= htmlspecialchars($search) ?>">
          </div>

          <!-- Price Range -->
          <div class="mb-4">
            <label class="form-label fw-bold">Harga</label>
            <small class="d-block text-muted mb-2">
              Rp <?= number_format($priceMin, 0, ',', '.') ?> - Rp <?= number_format($priceMax, 0, ',', '.') ?>
            </small>
            <input type="range" class="form-range" name="price_min" 
                   min="<?= $minPriceAvailable ?>" max="<?= $maxPriceAvailable ?>" 
                   value="<?= $priceMin ?>" id="priceMinSlider">
            <input type="range" class="form-range mt-2" name="price_max" 
                   min="<?= $minPriceAvailable ?>" max="<?= $maxPriceAvailable ?>" 
                   value="<?= $priceMax ?>" id="priceMaxSlider">
          </div>

          <!-- Sorting -->
          <div class="mb-4">
            <label class="form-label fw-bold">Urutkan</label>
            <select name="sort" class="form-select rounded-3">
              <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Terbaru</option>
              <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Populer</option>
              <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Harga Terendah</option>
              <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
            </select>
          </div>

          <!-- Categories -->
          <div class="mb-4">
            <label class="form-label fw-bold">Kategori</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cat1" value="adventure">
              <label class="form-check-label" for="cat1">Adventure</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cat2" value="photography">
              <label class="form-check-label" for="cat2">Photography</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cat3" value="camping">
              <label class="form-check-label" for="cat3">Camping</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="cat4" value="kuliner">
              <label class="form-check-label" for="cat4">Kuliner</label>
            </div>
          </div>

          <!-- Buttons -->
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-3 fw-bold">
              <i class="fas fa-search me-2"></i>Cari
            </button>
            <a href="paket.php" class="btn btn-outline-secondary rounded-3">
              <i class="fas fa-redo me-2"></i>Reset
            </a>
          </div>
        </form>

        <!-- Price Stats -->
        <hr class="my-4">
        <div class="p-3 rounded-3" style="background: #f0f4ff;">
          <small class="text-muted d-block mb-2">
            <i class="fas fa-tag me-1"></i>Harga Tersedia
          </small>
          <small class="fw-bold d-block">
            Rp <?= number_format($minPriceAvailable, 0, ',', '.') ?><br>
            s/d<br>
            Rp <?= number_format($maxPriceAvailable, 0, ',', '.') ?>
          </small>
        </div>
      </div>
    </div>

    <!-- RESULTS -->
    <div class="col-lg-9">
      <!-- Header -->
      <div class="mb-5" data-aos="fade-down">
        <h1 class="fw-bold mb-2" style="color: #1f2937;">
          <i class="fas fa-compass me-2" style="color: #667eea;"></i>Cari Paket Wisata
        </h1>
        <p class="text-muted">
          <?php if($search): ?>
            Hasil pencarian untuk "<strong><?= htmlspecialchars($search) ?></strong>" 
          <?php else: ?>
            Temukan paket wisata terbaik untuk petualangan Anda
          <?php endif; ?>
        </p>
      </div>

      <!-- Results Grid -->
      <div class="row g-4" data-aos="fade-up">
        <?php if(count($items) > 0): ?>
          <?php foreach($items as $item): 
            // Get rating
            $ratingStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE item_id = ?");
            $ratingStmt->execute([$item['id']]);
            $ratingData = $ratingStmt->fetch();
            $avgRating = round($ratingData['avg_rating'] ?? 0, 1);
          ?>
            <div class="col-md-6 col-lg-4">
              <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 transition-card" style="transition: all 0.3s;">
                <!-- Image -->
                <div class="position-relative" style="height: 250px; overflow: hidden;">
                  <img src="<?= htmlspecialchars($item['image']) ?>" 
                       alt="<?= htmlspecialchars($item['title']) ?>" 
                       class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s;">
                  <div class="position-absolute top-0 end-0 p-3">
                    <span class="badge bg-success">
                      <i class="fas fa-star me-1"></i><?= $avgRating ?>
                    </span>
                  </div>
                </div>

                <!-- Content -->
                <div class="card-body p-4">
                  <h5 class="card-title fw-bold mb-2" style="color: #1f2937;">
                    <?= htmlspecialchars(substr($item['title'], 0, 40)) ?>...
                  </h5>
                  
                  <p class="text-muted small mb-3">
                    <?= htmlspecialchars(substr($item['summary'], 0, 60)) ?>...
                  </p>

                  <h4 class="fw-bold" style="color: #667eea; margin-bottom: 1rem;">
                    <?= htmlspecialchars($item['price']) ?>
                  </h4>

                  <div class="d-grid gap-2">
                    <a href="detail.php?slug=<?= $item['slug'] ?>" class="btn btn-primary rounded-3">
                      <i class="fas fa-eye me-2"></i>Lihat Detail
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info text-center py-5 rounded-4">
              <i class="fas fa-search fa-3x mb-3" style="color: #667eea;"></i>
              <h5>Tidak ada paket yang ditemukan</h5>
              <p class="text-muted mb-0">Coba ubah filter atau kata kunci pencarian Anda</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Results Info -->
      <div class="mt-5 text-center text-muted">
        <small>Menampilkan <?= count($items) ?> paket wisata</small>
      </div>
    </div>
  </div>
</div>

<style>
  .transition-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2) !important;
  }
  .transition-card:hover img {
    transform: scale(1.1);
  }
</style>

<?php require 'footer.php'; ?>
