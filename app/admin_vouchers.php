<?php
require 'config.php';

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

$pageTitle = "Kelola Voucher";
$currentPage = "vouchers";
$message = '';
$messageType = '';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action == 'add') {
        $code = strtoupper(trim($_POST['code']));
        $discountType = $_POST['discount_type'];
        $discountValue = floatval($_POST['discount_value']);
        $minOrder = floatval($_POST['min_order'] ?? 0);
        $maxUses = intval($_POST['max_uses'] ?? 0);
        $validFrom = $_POST['valid_from'] ?? null;
        $validUntil = $_POST['valid_until'] ?? null;
        
        // Check if code exists
        $check = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
        $check->execute([$code]);
        if($check->fetch()) {
            $message = "Kode voucher sudah ada!";
            $messageType = "danger";
        } else {
            $stmt = $pdo->prepare("INSERT INTO vouchers (code, discount_type, discount_value, min_order, max_uses, valid_from, valid_until) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $discountType, $discountValue, $minOrder, $maxUses ?: null, $validFrom ?: null, $validUntil ?: null]);
            $message = "Voucher berhasil ditambahkan!";
            $messageType = "success";
        }
    } elseif($action == 'toggle') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE vouchers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Status voucher berhasil diubah!";
        $messageType = "success";
    } elseif($action == 'delete') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Voucher berhasil dihapus!";
        $messageType = "success";
    }
}

// Get all vouchers
$vouchers = $pdo->query("SELECT * FROM vouchers ORDER BY created_at DESC")->fetchAll();

// Stats
$totalActive = $pdo->query("SELECT COUNT(*) FROM vouchers WHERE is_active = 1")->fetchColumn();
$totalUsed = $pdo->query("SELECT SUM(used_count) FROM vouchers")->fetchColumn() ?: 0;

require 'includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-tags me-2" style="color: #667eea;"></i>Kelola Voucher</h2>
    <p class="text-muted mb-0">Buat dan kelola kode promo</p>
  </div>
  <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
    <i class="fas fa-plus me-2"></i>Tambah Voucher
  </button>
</div>

<?php if($message): ?>
  <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= $message ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm stat-card" style="border-left: 4px solid #667eea !important;">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-tags text-white fa-lg"></i>
          </div>
          <div>
            <div class="small text-muted">Total Voucher</div>
            <div class="h4 mb-0 fw-bold"><?= count($vouchers) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm stat-card" style="border-left: 4px solid #28a745 !important;">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <i class="fas fa-check-circle text-white fa-lg"></i>
          </div>
          <div>
            <div class="small text-muted">Voucher Aktif</div>
            <div class="h4 mb-0 fw-bold"><?= $totalActive ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm stat-card" style="border-left: 4px solid #ffc107 !important;">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
            <i class="fas fa-receipt text-white fa-lg"></i>
          </div>
          <div>
            <div class="small text-muted">Total Penggunaan</div>
            <div class="h4 mb-0 fw-bold"><?= $totalUsed ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Voucher Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
          <tr>
            <th>Kode</th>
            <th>Diskon</th>
            <th>Min. Order</th>
            <th>Penggunaan</th>
            <th>Berlaku</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($vouchers as $v): ?>
          <tr>
            <td><span class="badge bg-primary-subtle text-primary px-3 py-2 fs-6"><?= htmlspecialchars($v['code']) ?></span></td>
            <td>
              <?php if($v['discount_type'] == 'percent'): ?>
                <span class="text-success fw-bold"><?= $v['discount_value'] ?>%</span>
              <?php else: ?>
                <span class="text-success fw-bold">Rp <?= number_format($v['discount_value'], 0, ',', '.') ?></span>
              <?php endif; ?>
            </td>
            <td>Rp <?= number_format($v['min_order'], 0, ',', '.') ?></td>
            <td>
              <?= $v['used_count'] ?><?= $v['max_uses'] ? '/' . $v['max_uses'] : '' ?>
            </td>
            <td>
              <?php if($v['valid_from'] && $v['valid_until']): ?>
                <small><?= date('d/m/Y', strtotime($v['valid_from'])) ?> - <?= date('d/m/Y', strtotime($v['valid_until'])) ?></small>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($v['is_active']): ?>
                <span class="badge bg-success">Aktif</span>
              <?php else: ?>
                <span class="badge bg-secondary">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                  <i class="fas fa-power-off"></i>
                </button>
              </form>
              <form method="POST" class="d-inline" onsubmit="return confirm('Hapus voucher ini?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Voucher Modal -->
<div class="modal fade" id="addVoucherModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Tambah Voucher Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Voucher</label>
            <input type="text" name="code" class="form-control text-uppercase" placeholder="CONTOH: DISKON20" required>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tipe Diskon</label>
              <select name="discount_type" class="form-select" required>
                <option value="percent">Persentase (%)</option>
                <option value="fixed">Nominal (Rp)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nilai Diskon</label>
              <input type="number" name="discount_value" class="form-control" placeholder="10" step="0.01" required>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Min. Order (Rp)</label>
              <input type="number" name="min_order" class="form-control" placeholder="100000" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Maks. Penggunaan</label>
              <input type="number" name="max_uses" class="form-control" placeholder="0 = unlimited">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Berlaku Dari</label>
              <input type="date" name="valid_from" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Berlaku Sampai</label>
              <input type="date" name="valid_until" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-save me-2"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/admin_footer.php'; ?>
