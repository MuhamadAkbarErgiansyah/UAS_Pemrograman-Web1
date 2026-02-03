<?php 
require 'config.php';
require 'header.php'; 

// Ambil semua paket dari database
$stmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$paketList = $stmt->fetchAll();
?>

<div class="container mt-5" data-animate>
  <h2>Paket Pilihan</h2>
  <p class="text-muted">Pilih paket sesuai minatmu dan pesan langsung.</p>

  <?php if(empty($paketList)): ?>
    <div class="alert alert-info text-center">
      <i class="fas fa-info-circle me-2"></i>Belum ada paket wisata tersedia.
    </div>
  <?php endif; ?>

  <?php foreach($paketList as $p): ?>
    <div class="card mb-3 card-modern" data-animate>
      <div class="row g-0">
        <div class="col-md-4 img-hover-zoom">
          <?php 
          $imgSrc = $p['image'] ?? 'images/default-paket.jpg';
          ?>
          <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%; height:100%; object-fit:cover; min-height:300px;">
        </div>

        <div class="col-md-8">
          <div class="p-3">
            <h5><?= htmlspecialchars($p['title']) ?></h5>
            <p class="text-muted small">Durasi: Full Day • Transportasi termasuk</p>

            <div class="kv fw-bold mb-2" style="color: #667eea; font-size: 1.3rem;">
              Rp <?= number_format($p['price'],0,',','.') ?>
            </div>

            <!-- DESKRIPSI dari summary atau content -->
            <div class="small text-muted mb-3">
              <?= htmlspecialchars($p['summary'] ?? '') ?>
            </div>

            <div class="d-flex justify-content-end mt-3 gap-2">
              <a href="detail.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-info-circle me-2"></i>Detail
              </a>

              <a href="pemesanan.php?paket=<?= htmlspecialchars($p['slug']) ?>" class="btn btn-primary">
                <i class="fas fa-calendar-check me-2"></i>Pilih Paket
              </a>
            </div>

          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

</div>

<?php include 'footer.php'; ?>

<style>
.card-modern {
  border: none;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}
.card-modern:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.img-hover-zoom {
  overflow: hidden;
}
.img-hover-zoom img {
  transition: transform 0.5s;
}
.img-hover-zoom:hover img {
  transform: scale(1.05);
}
</style>
