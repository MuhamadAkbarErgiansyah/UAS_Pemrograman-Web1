<?php
require 'config.php';
$pageTitle = "Form Pemesanan";
require 'header.php';

// ======================
// CEK LOGIN
// ======================
if (!isset($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit;
}

// Ambil slug paket dari URL
$slug = $_GET['paket'] ?? '';

// Ambil info paket dari database
$stmt = $pdo->prepare("SELECT * FROM items WHERE slug = ?");
$stmt->execute([$slug]);
$paket = $stmt->fetch();

// Jika paket tidak ditemukan
if(!$paket){
    echo "<div class='container py-5'>
            <div class='alert alert-danger text-center'>
                ❌ Paket tidak ditemukan.
            </div>
            <div class='text-center'>
                <a href='paket.php' class='btn btn-primary'>Lihat Paket Lainnya</a>
            </div>
          </div>";
    require 'footer.php';
    exit;
}

// Format harga (PENTING: harus sebelum form processing)
$harga = floatval(preg_replace('/[^0-9.]/', '', $paket['price']));

// Ambil voucher yang sedang aktif dan berlaku
$today = date('Y-m-d');
$voucherStmt = $pdo->prepare("
    SELECT * FROM vouchers 
    WHERE is_active = 1 
    AND (valid_from IS NULL OR valid_from <= ?) 
    AND (valid_until IS NULL OR valid_until >= ?)
    AND (max_uses IS NULL OR used_count < max_uses)
    AND min_order <= ?
    ORDER BY discount_value DESC
");
$voucherStmt->execute([$today, $today, $harga]);
$availableVouchers = $voucherStmt->fetchAll();

// Proses form submit
$success = '';
$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $contact       = trim($_POST['phone'] ?? '');
    $package_id    = $paket['id'];
    $package_title = $paket['title'];
    $date_event    = $_POST['date'] ?? '';
    $note          = trim($_POST['notes'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $voucher_code  = strtoupper(trim($_POST['voucher_code'] ?? ''));
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    $userId        = $_SESSION['user_id'];

    // Validasi
    if(empty($name)) $errors[] = "Nama harus diisi";
    if(empty($email)) $errors[] = "Email harus diisi";
    if(empty($contact)) $errors[] = "No. Telepon harus diisi";
    if(empty($date_event)) $errors[] = "Tanggal perjalanan harus diisi";
    if(empty($payment_method)) $errors[] = "Pilih metode pembayaran";

    if(empty($errors)) {
        // Calculate final price
        $total_price = $harga - $discount_amount;
        
        // Simpan ke database - menggunakan kolom yang ada di tabel reservations
        $noteWithPayment = $note . " [Pembayaran: $payment_method, Total: Rp" . number_format($total_price, 0, ',', '.') . "]" . ($voucher_code ? " [Voucher: $voucher_code, Diskon: Rp" . number_format($discount_amount, 0, ',', '.') . "]" : "");
        
        $stmt = $pdo->prepare("INSERT INTO reservations
            (user_id, name, email, contact, package_id, package_title, date_event, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $name, $email, $contact, $package_id, $package_title, $date_event, $noteWithPayment]);
        
        // Update voucher usage if used
        if($voucher_code && $discount_amount > 0) {
            $updateVoucher = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE code = ?");
            $updateVoucher->execute([$voucher_code]);
        }
        
        // Create notification for user
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'booking', ?)");
        $notif->execute([$userId, "Pemesanan paket '$package_title' berhasil dibuat. Silakan tunggu konfirmasi admin."]);
        
        // Create notification for all admins
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll();
        foreach($admins as $admin) {
            $notifAdmin = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'booking', ?)");
            $notifAdmin->execute([$admin['id'], "Pemesanan baru dari $name untuk paket '$package_title'"]);
        }

        $success = "✅ Pemesanan berhasil! Terima kasih, $name. Silakan lakukan pembayaran sesuai metode yang dipilih.";
        
        // Redirect ke riwayat setelah 3 detik
        echo "<script>setTimeout(function(){ window.location.href = 'riwayat.php'; }, 3000);</script>";
    }
}
?>

<div class="container py-5">
  <div class="row">
    <!-- Form Pemesanan -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <h3 class="fw-bold mb-4">
            <i class="fas fa-clipboard-list me-2" style="color: #667eea;"></i>Form Pemesanan
          </h3>
          
          <?php if($success): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i><?= $success ?>
            </div>
          <?php endif; ?>
          
          <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle me-2"></i>
              <ul class="mb-0">
                <?php foreach($errors as $err): ?>
                  <li><?= $err ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" id="orderForm">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required 
                       value="<?= htmlspecialchars($_POST['name'] ?? $_SESSION['username'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxxxx"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Tanggal Perjalanan <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
              </div>
            </div>

            <!-- Pilih Voucher -->
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-tags me-1 text-success"></i>Gunakan Voucher</label>
              <?php if(!empty($availableVouchers)): ?>
              <select name="voucher_code" id="voucherSelect" class="form-select" onchange="applyVoucher()">
                <option value="">-- Tidak menggunakan voucher --</option>
                <?php foreach($availableVouchers as $v): 
                  $discountText = $v['discount_type'] === 'percent' || $v['discount_type'] === 'percentage' 
                    ? $v['discount_value'] . '%' 
                    : 'Rp ' . number_format($v['discount_value'], 0, ',', '.');
                ?>
                <option value="<?= htmlspecialchars($v['code']) ?>" 
                        data-type="<?= $v['discount_type'] ?>" 
                        data-value="<?= $v['discount_value'] ?>">
                  <?= htmlspecialchars($v['code']) ?> - Diskon <?= $discountText ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div id="voucherResult" class="mt-2"></div>
              <?php else: ?>
              <div class="alert alert-light border mb-0">
                <i class="fas fa-info-circle me-2 text-muted"></i>Tidak ada voucher yang tersedia untuk paket ini.
              </div>
              <?php endif; ?>
              <input type="hidden" name="discount_amount" id="discountAmount" value="0">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Catatan / Permintaan Khusus</label>
              <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Jumlah peserta, akomodasi khusus, kebutuhan diet, dll"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>

            <hr class="my-4">

            <!-- METODE PEMBAYARAN -->
            <h5 class="fw-bold mb-3">
              <i class="fas fa-credit-card me-2" style="color: #667eea;"></i>Pilih Metode Pembayaran
            </h5>

            <div class="row g-3 mb-4">
              <!-- Transfer Bank -->
              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="bank_bca" value="Transfer BCA" required>
                  <label class="form-check-label w-100" for="bank_bca">
                    <div class="d-flex align-items-center">
                      <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" alt="BCA" style="height: 24px; margin-right: 10px;">
                      <div>
                        <strong>Bank BCA</strong>
                        <small class="d-block text-muted">1234567890 a/n Explore Bandung</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="bank_mandiri" value="Transfer Mandiri">
                  <label class="form-check-label w-100" for="bank_mandiri">
                    <div class="d-flex align-items-center">
                      <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" alt="Mandiri" style="height: 24px; margin-right: 10px;">
                      <div>
                        <strong>Bank Mandiri</strong>
                        <small class="d-block text-muted">0987654321 a/n Explore Bandung</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="bank_bni" value="Transfer BNI">
                  <label class="form-check-label w-100" for="bank_bni">
                    <div class="d-flex align-items-center">
                      <img src="https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg" alt="BNI" style="height: 24px; margin-right: 10px;">
                      <div>
                        <strong>Bank BNI</strong>
                        <small class="d-block text-muted">1122334455 a/n Explore Bandung</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <!-- E-Wallet -->
              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="gopay" value="GoPay">
                  <label class="form-check-label w-100" for="gopay">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-wallet fa-2x me-3" style="color: #00AA13;"></i>
                      <div>
                        <strong>GoPay</strong>
                        <small class="d-block text-muted">089508891566</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="ovo" value="OVO">
                  <label class="form-check-label w-100" for="ovo">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-wallet fa-2x me-3" style="color: #4C3494;"></i>
                      <div>
                        <strong>OVO</strong>
                        <small class="d-block text-muted">089508891566</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-check payment-option p-3 border rounded-3">
                  <input class="form-check-input" type="radio" name="payment_method" id="dana" value="DANA">
                  <label class="form-check-label w-100" for="dana">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-wallet fa-2x me-3" style="color: #118EEA;"></i>
                      <div>
                        <strong>DANA</strong>
                        <small class="d-block text-muted">089508891566</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- QR Code Payment -->
            <div class="text-center mb-4 p-4 bg-light rounded-3" id="qrSection" style="display: none;">
              <h6 class="fw-bold mb-3">Scan QR Code untuk Pembayaran</h6>
              <div class="d-inline-block p-3 bg-white rounded shadow-sm">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://wa.me/6289508891566?text=Saya%20ingin%20membayar%20paket%20<?= urlencode($paket['title']) ?>" 
                     alt="QR Code Payment" style="width: 200px; height: 200px;">
              </div>
              <p class="text-muted small mt-3">Scan QR untuk chat via WhatsApp & konfirmasi pembayaran</p>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                <i class="fas fa-paper-plane me-2"></i>Kirim Pemesanan
              </button>
              <a href="paket.php" class="btn btn-outline-secondary btn-lg">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Sidebar - Detail Paket -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
        <div class="card-body p-4">
          <!-- Gambar Paket -->
          <?php if($paket['image']): ?>
            <img src="<?= htmlspecialchars($paket['image']) ?>" alt="<?= htmlspecialchars($paket['title']) ?>" 
                 class="w-100 rounded-3 mb-3" style="height: 200px; object-fit: cover;">
          <?php endif; ?>

          <h4 class="fw-bold mb-3" style="color: #1f2937;"><?= htmlspecialchars($paket['title']) ?></h4>
          
          <!-- Harga -->
          <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="d-flex justify-content-between align-items-center text-white">
              <span>Harga Paket:</span>
              <span class="h5 mb-0 fw-bold" id="originalPrice">Rp <?= number_format($harga, 0, ',', '.') ?></span>
            </div>
            <div id="discountRow" style="display: none;" class="d-flex justify-content-between align-items-center text-white-50 mt-2 pt-2 border-top border-white-50">
              <span>Diskon Voucher:</span>
              <span id="discountDisplay">-Rp 0</span>
            </div>
            <div id="totalRow" style="display: none;" class="d-flex justify-content-between align-items-center text-white mt-2 pt-2 border-top border-white-50">
              <span class="fw-bold">Total Bayar:</span>
              <span class="h4 mb-0 fw-bold" id="totalPrice">Rp <?= number_format($harga, 0, ',', '.') ?></span>
            </div>
          </div>

          <!-- Ringkasan -->
          <?php if($paket['summary']): ?>
            <p class="text-muted mb-3"><?= htmlspecialchars($paket['summary']) ?></p>
          <?php endif; ?>

          <!-- Deskripsi Detail -->
          <?php if($paket['content']): ?>
            <div class="border-top pt-3 mt-3">
              <h6 class="fw-bold mb-2">
                <i class="fas fa-info-circle me-2" style="color: #667eea;"></i>Detail Paket
              </h6>
              <div class="small text-muted">
                <?= nl2br(htmlspecialchars($paket['content'])) ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Info Kontak -->
          <div class="border-top pt-3 mt-3">
            <h6 class="fw-bold mb-2">
              <i class="fas fa-headset me-2" style="color: #667eea;"></i>Butuh Bantuan?
            </h6>
            <a href="https://wa.me/6289508891566" class="btn btn-success btn-sm w-100">
              <i class="fab fa-whatsapp me-2"></i>Chat WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.payment-option {
  cursor: pointer;
  transition: all 0.3s;
}
.payment-option:hover {
  border-color: #667eea !important;
  background: #f8f9ff;
}
.payment-option:has(input:checked) {
  border-color: #667eea !important;
  background: #f0f4ff;
  box-shadow: 0 0 0 2px #667eea;
}
</style>

<script>
// Show QR code when any payment method is selected
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.getElementById('qrSection').style.display = 'block';
  });
});

// Voucher selection
const originalPrice = <?= $harga ?>;
let currentDiscount = 0;

function applyVoucher() {
  const select = document.getElementById('voucherSelect');
  if(!select) return;
  
  const resultDiv = document.getElementById('voucherResult');
  const selectedOption = select.options[select.selectedIndex];
  
  if(!select.value) {
    // No voucher selected
    currentDiscount = 0;
    document.getElementById('discountAmount').value = 0;
    document.getElementById('discountRow').style.display = 'none';
    document.getElementById('totalRow').style.display = 'none';
    resultDiv.innerHTML = '';
    return;
  }
  
  const discountType = selectedOption.dataset.type;
  const discountValue = parseFloat(selectedOption.dataset.value);
  
  // Calculate discount
  if(discountType === 'percent' || discountType === 'percentage') {
    currentDiscount = originalPrice * (discountValue / 100);
  } else {
    currentDiscount = discountValue;
  }
  
  const finalPrice = originalPrice - currentDiscount;
  
  document.getElementById('discountAmount').value = currentDiscount;
  document.getElementById('discountRow').style.display = 'flex';
  document.getElementById('totalRow').style.display = 'flex';
  document.getElementById('discountDisplay').innerText = '-Rp ' + currentDiscount.toLocaleString('id-ID');
  document.getElementById('totalPrice').innerText = 'Rp ' + finalPrice.toLocaleString('id-ID');
  
  resultDiv.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Voucher ${select.value} diterapkan! Hemat Rp ${currentDiscount.toLocaleString('id-ID')}</span>`;
}
</script>

<?php require 'footer.php'; ?>
