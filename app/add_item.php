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

$pageTitle = "Tambah Paket";
$item = null;
$edit_mode = false;

// Check if editing
if(isset($_GET['id'])){
  $id = intval($_GET['id']);
  $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
  $stmt->execute([$id]);
  $item = $stmt->fetch();
  
  if(!$item){
    header('Location: manage_items.php');
    exit;
  }
  
  $pageTitle = "Edit Paket";
  $edit_mode = true;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $title = trim($_POST['title'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  $summary = trim($_POST['summary'] ?? '');
  $content = $_POST['content'] ?? '';
  $price = floatval($_POST['price'] ?? 0);

  // Validation
  $errors = [];
  if(empty($title)) $errors[] = "Judul paket harus diisi";
  if(empty($slug)) $errors[] = "Slug harus diisi";
  if($price <= 0) $errors[] = "Harga harus lebih dari 0";

  // Handle image upload
  $image = $item['image'] ?? ''; // Keep existing image if editing
  
  if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
    $uploadDir = 'images/';
    
    // Create directory if not exists
    if(!is_dir($uploadDir)){
      mkdir($uploadDir, 0755, true);
    }
    
    // Get file info
    $fileName = $_FILES['image']['name'];
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if(!in_array($fileExt, $allowedExts)){
      $errors[] = "Format gambar tidak valid. Gunakan: " . implode(', ', $allowedExts);
    } elseif($fileSize > 5 * 1024 * 1024) { // 5MB max
      $errors[] = "Ukuran gambar maksimal 5MB";
    } else {
      // Generate unique filename
      $newFileName = $slug . '-' . time() . '.' . $fileExt;
      $uploadPath = $uploadDir . $newFileName;
      
      if(move_uploaded_file($fileTmp, $uploadPath)){
        $image = $uploadPath;
      } else {
        $errors[] = "Gagal mengupload gambar";
      }
    }
  }

  if(empty($errors)){
    try {
      if($edit_mode && $item){
        // Update
        $stmt = $pdo->prepare("UPDATE items SET title=?, slug=?, summary=?, content=?, image=?, price=? WHERE id=?");
        $stmt->execute([$title, $slug, $summary, $content, $image, $price, $item['id']]);
        $success = "Paket berhasil diperbarui!";
        // Refresh item data
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$item['id']]);
        $item = $stmt->fetch();
      } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO items (title, slug, summary, content, image, price) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $summary, $content, $image, $price]);
        $success = "Paket berhasil ditambahkan!";
        // Redirect after success
        header('Location: manage_items.php?added=1');
        exit;
      }
    } catch(Exception $e){
      $errors[] = "Error: " . $e->getMessage();
    }
  }
}

$currentPage = 'add_item';
require 'includes/admin_header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><?= $pageTitle ?></h2>
    <p class="text-muted mb-0">Isi informasi paket wisata dengan detail lengkap</p>
  </div>
  <a href="manage_items.php" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i>Kembali
  </a>
</div>

<!-- Alerts -->
<?php if(isset($success)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= $success ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if(!empty($errors)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    <strong>Validasi Gagal:</strong>
    <ul class="mb-0 mt-2">
      <?php foreach($errors as $error): ?>
        <li><?= $error ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Form Card -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="row">
        <div class="col-lg-8">
          <!-- Title -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Judul Paket <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-lg" placeholder="Contoh: Paket Lembang Full Day" 
                   value="<?= htmlspecialchars($item['title'] ?? ($_POST['title'] ?? '')) ?>" required id="titleInput">
          </div>

          <!-- Slug -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Slug (URL) <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="form-control" placeholder="Contoh: paket-lembang-full-day" 
                   value="<?= htmlspecialchars($item['slug'] ?? ($_POST['slug'] ?? '')) ?>" required id="slugInput">
            <small class="text-muted">Gunakan huruf kecil dan tanda hubung (-). Otomatis dibuat dari judul.</small>
          </div>

          <!-- Summary -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Ringkasan Singkat</label>
            <textarea name="summary" class="form-control" rows="3" placeholder="Deskripsi singkat paket wisata..."><?= htmlspecialchars($item['summary'] ?? ($_POST['summary'] ?? '')) ?></textarea>
          </div>

          <!-- Content -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Lengkap</label>
            <textarea name="content" class="form-control" rows="10" placeholder="Deskripsi detail paket wisata. Contoh:&#10;&#10;Yang akan Anda nikmati:&#10;- Trekking ringan menuju Tebing Keraton&#10;- Kunjungan ke hutan pinus&#10;- Makan siang kuliner khas Sunda&#10;&#10;Cocok untuk:&#10;- Pecinta alam&#10;- Liburan keluarga"><?= htmlspecialchars($item['content'] ?? ($_POST['content'] ?? '')) ?></textarea>
            <small class="text-muted">Tulis deskripsi dengan teks biasa. Gunakan enter untuk baris baru.</small>
          </div>
        </div>

        <div class="col-lg-4">
          <!-- Image Upload -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Upload Gambar</label>
            <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
            <small class="text-muted">Format: JPG, JPEG, PNG, GIF, WebP. Maks: 5MB</small>
          </div>

          <!-- Image Preview -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Preview Gambar</label>
            <div class="border rounded p-2 bg-light text-center" style="min-height: 150px;">
              <?php 
              $imgSrc = $item['image'] ?? '';
              if($imgSrc): 
              ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Preview" class="img-fluid rounded" style="max-height: 200px;" id="imagePreview">
              <?php else: ?>
                <div class="text-muted py-5" id="noImageText">
                  <i class="fas fa-image fa-3x mb-2"></i>
                  <p class="mb-0">Tidak ada gambar</p>
                </div>
                <img src="" alt="Preview" class="img-fluid rounded d-none" style="max-height: 200px;" id="imagePreview">
              <?php endif; ?>
            </div>
            <?php if($imgSrc): ?>
              <small class="text-muted">Gambar saat ini: <?= htmlspecialchars($imgSrc) ?></small>
            <?php endif; ?>
          </div>

          <!-- Price -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="price" class="form-control" placeholder="250000" 
                     value="<?= htmlspecialchars($item['price'] ?? ($_POST['price'] ?? '')) ?>" step="1000" required>
            </div>
          </div>

          <!-- Info Card -->
          <div class="card bg-light border-0">
            <div class="card-body">
              <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Tips</h6>
              <ul class="small mb-0 ps-3">
                <li>Gunakan gambar dengan resolusi minimal 800x600</li>
                <li>Slug akan digunakan untuk URL halaman detail</li>
                <li>Isi deskripsi lengkap untuk SEO yang baik</li>
                <li>Gambar akan disimpan di folder images/</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <hr class="my-4">
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fas fa-save me-2"></i><?= $edit_mode ? 'Update Paket' : 'Tambah Paket' ?>
        </button>
        <a href="manage_items.php" class="btn btn-secondary btn-lg">
          <i class="fas fa-times me-2"></i>Batal
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script>
// Auto generate slug from title
document.getElementById('titleInput').addEventListener('input', function() {
  const title = this.value;
  const slug = title.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim();
  document.getElementById('slugInput').value = slug;
});

// Image preview from file input
document.getElementById('imageInput').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('imagePreview');
  const noImageText = document.getElementById('noImageText');
  
  if(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.classList.remove('d-none');
      if(noImageText) noImageText.classList.add('d-none');
    };
    reader.readAsDataURL(file);
  }
});
</script>

<?php require 'includes/admin_footer.php'; ?>
