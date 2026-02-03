<?php
$pageTitle = "Menu Utama";
require 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: login.php');
  exit;
}

require 'header.php';

// welcome animation after login
$just_logged_in = isset($_SESSION['just_logged_in']) ? $_SESSION['just_logged_in'] : false;
unset($_SESSION['just_logged_in']);

// rekomendasi (ambil 3 teratas)
$stmt = $pdo->query("SELECT id,title,summary,image,slug FROM items ORDER BY created_at DESC LIMIT 3");
$rekom = $stmt->fetchAll();

// reservation history for user
$stmtR = $pdo->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC");
$stmtR->execute([$_SESSION['user_id']]);
$history = $stmtR->fetchAll();
?>

<?php if($just_logged_in): ?>
  <div class="text-center my-4" data-aos="fade-in">
    <h2 class="text-glow">Terima kasih sudah menjadi bagian dari kami</h2>
    <p>Ayo explore Jawa Barat — selamat menikmati pengalaman</p>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <img src="images/default-pp.png" alt="pp" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover">
      <h5><?= htmlspecialchars($_SESSION['username']) ?></h5>
      <small><?= htmlspecialchars($_SESSION['email']) ?></small>
    </div>

    <div class="card p-3 mt-3">
      <h6>History Reservasi</h6>
      <?php if($history): ?>
        <ul class="list-group list-group-flush">
          <?php foreach($history as $h): ?>
            <li class="list-group-item">
              <strong><?=$h['package_title']?></strong><br>
              <small><?=$h['date_event']?> — <?=$h['created_at']?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="small text-muted">Belum ada riwayat pemesanan.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-8">
    <h
