<?php
require 'config.php';
$pageTitle = "Kelola Paket";
$currentPage = "manage";

// Check admin
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

// Handle delete action
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete'){
  $id = intval($_POST['id']);
  try {
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $success_msg = "Paket berhasil dihapus!";
  } catch(Exception $e){
    $error_msg = "Gagal menghapus paket: " . $e->getMessage();
  }
}

// Get all items
$stmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$items = $stmt->fetchAll();

require 'includes/admin_header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-box me-2" style="color: #667eea;"></i>Kelola Paket Wisata</h2>
    <p class="text-muted mb-0">Tambah, edit, atau hapus paket wisata</p>
  </div>
  <a href="add_item.php" class="btn btn-primary">
    <i class="fas fa-plus-circle me-2"></i>Tambah Paket
  </a>
</div>

<!-- Alerts -->
<?php if(isset($success_msg)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if(isset($error_msg)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Items Table -->
<div class="card-custom">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th width="50">No</th>
          <th>Gambar</th>
          <th>Judul Paket</th>
          <th>Slug</th>
          <th>Harga</th>
          <th>Dibuat</th>
          <th width="150">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $idx => $item): ?>
        <tr>
          <td><?= $idx + 1 ?></td>
          <td>
            <img src="<?= htmlspecialchars($item['image'] ?? 'images/default.jpg') ?>" 
                 alt="<?= htmlspecialchars($item['title']) ?>" 
                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
          </td>
          <td>
            <div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div>
            <small class="text-muted"><?= htmlspecialchars(substr($item['summary'] ?? '', 0, 50)) ?>...</small>
          </td>
          <td><code><?= htmlspecialchars($item['slug']) ?></code></td>
          <td><strong><?= htmlspecialchars($item['price']) ?></strong></td>
          <td>
            <small class="text-muted"><?= date('d M Y', strtotime($item['created_at'])) ?></small>
          </td>
          <td>
            <a href="add_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['title'])) ?>')" title="Hapus">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($items)): ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-5">
            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
            Belum ada paket wisata. <a href="add_item.php">Tambah sekarang</a>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Penghapusan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus paket <strong id="deleteTitle"></strong>?</p>
        <p class="text-muted small">Tindakan ini tidak dapat diurungkan.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" id="deleteId">
          <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function confirmDelete(id, title) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }
</script>

<?php require 'includes/admin_footer.php'; ?>
