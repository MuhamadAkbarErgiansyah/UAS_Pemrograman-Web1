<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Explore Bandung</title>
  <meta name="description" content="Jelajahi keindahan Bandung dengan paket wisata pilihan kami">
  <meta name="author" content="Explore Bandung">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- AOS Animation -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Inline Critical Styles -->
  <style>
    :root {
      --primary-1: #6a11cb;
      --primary-2: #2575fc;
      --accent: #ff5722;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --muted: #6b7280;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
      color: #1f2937;
    }

    .bg-gradient-primary {
      background: linear-gradient(135deg, var(--primary-1), var(--primary-2));
      color: #fff;
    }

    main {
      min-height: calc(100vh - 120px);
    }
  </style>
</head>

<body>

<!-- NAVBAR - Modern & Responsive -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top" style="box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
  <div class="container-lg">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
      <div style="width:48px; height:48px; border-radius:12px; background: linear-gradient(135deg, var(--primary-1), var(--primary-2)); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:1.3rem;">
        <i class="fas fa-map-location-dot"></i>
      </div>
      <div>
        <div style="font-weight:700; color:#1f2937; font-size:1.1rem;">Explore Bandung</div>
        <small style="color:#6b7280; font-weight:500;">Wisata • Paket • Guide</small>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <i class="fas fa-home me-2"></i>Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="paket.php">
            <i class="fas fa-box me-2"></i>Paket
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="guide.php">
            <i class="fas fa-person-hiking me-2"></i>Guide
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="galerry.php">
            <i class="fas fa-images me-2"></i>Galeri
          </a>
        </li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="wishlist.php">
              <i class="fas fa-heart me-2"></i>Wishlist
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="riwayat.php">
              <i class="fas fa-history me-2"></i>Pemesanan
            </a>
          </li>
          
          <!-- Notification Bell -->
          <li class="nav-item dropdown">
            <?php
            try {
              $unreadNotif = $pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = {$_SESSION['user_id']} AND is_read = 0")->fetchColumn();
            } catch(Exception $e) {
              $unreadNotif = 0;
            }
            ?>
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notifDropdown">
              <i class="fas fa-bell"></i>
              <?php if($unreadNotif > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;">
                <?= $unreadNotif > 9 ? '9+' : $unreadNotif ?>
              </span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 300px; max-height: 400px; overflow-y: auto;" aria-labelledby="notifDropdown">
              <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                <strong>Notifikasi</strong>
                <a href="#" onclick="markAllRead()" class="text-decoration-none small">Tandai Semua</a>
              </li>
              <div id="notificationList">
                <li class="text-center py-3 text-muted">
                  <i class="fas fa-spinner fa-spin"></i>
                </li>
              </div>
            </ul>
          </li>
          
          <!-- Chat Button -->
          <li class="nav-item">
            <a class="nav-link position-relative" href="chat.php" title="Chat dengan Admin">
              <i class="fas fa-comments"></i>
              <?php
              try {
                $unreadMsg = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user_id']} AND is_read = 0")->fetchColumn();
              } catch(Exception $e) {
                $unreadMsg = 0;
              }
              if($unreadMsg > 0):
              ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size:10px;">
                <?= $unreadMsg > 9 ? '9+' : $unreadMsg ?>
              </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endif; ?>
        
        <li class="nav-item ms-3">
          <?php if(isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
              <a class="btn btn-sm btn-outline-primary rounded-pill dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist Saya</a></li>
                <li><a class="dropdown-item" href="riwayat.php"><i class="fas fa-history me-2"></i>Riwayat Pemesanan</a></li>
                <?php 
                  // Check if user is admin
                  $userCheckStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                  $userCheckStmt->execute([$_SESSION['user_id']]);
                  $userRole = $userCheckStmt->fetch();
                  if($userRole && $userRole['role'] === 'admin'):
                ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-info" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                  <li><a class="dropdown-item text-info" href="admin_bookings.php"><i class="fas fa-clipboard-list me-2"></i>Kelola Pemesanan</a></li>
                  <li><a class="dropdown-item text-info" href="manage_items.php"><i class="fas fa-cube me-2"></i>Kelola Paket</a></li>
                  <li><a class="dropdown-item text-info" href="reports.php"><i class="fas fa-file-export me-2"></i>Laporan & Export</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a class="btn btn-sm btn-outline-primary rounded-pill" href="login.php">
              <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php if(isset($_SESSION['user_id'])): ?>
<script>
// Load notifications
function loadNotifications() {
  fetch('api/notifications.php?action=get&limit=10')
    .then(r => r.json())
    .then(data => {
      const list = document.getElementById('notificationList');
      if(data.success && data.notifications.length > 0) {
        list.innerHTML = data.notifications.map(n => `
          <li>
            <a class="dropdown-item py-2 ${n.is_read == 0 ? 'bg-light' : ''}" href="#" onclick="markRead(${n.id})">
              <div class="d-flex align-items-start">
                <i class="fas ${getNotifIcon(n.type)} me-2 mt-1 text-${getNotifColor(n.type)}"></i>
                <div>
                  <div class="small">${n.message}</div>
                  <small class="text-muted">${timeAgo(n.created_at)}</small>
                </div>
              </div>
            </a>
          </li>
        `).join('');
      } else {
        list.innerHTML = '<li class="text-center py-3 text-muted">Belum ada notifikasi</li>';
      }
    });
}

function getNotifIcon(type) {
  const icons = { booking: 'fa-calendar-check', approval: 'fa-check-circle', message: 'fa-envelope', review: 'fa-star' };
  return icons[type] || 'fa-bell';
}

function getNotifColor(type) {
  const colors = { booking: 'primary', approval: 'success', message: 'info', review: 'warning' };
  return colors[type] || 'secondary';
}

function timeAgo(date) {
  const now = new Date();
  const past = new Date(date);
  const diff = Math.floor((now - past) / 1000);
  if(diff < 60) return 'Baru saja';
  if(diff < 3600) return Math.floor(diff/60) + ' menit lalu';
  if(diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
  return Math.floor(diff/86400) + ' hari lalu';
}

function markRead(id) {
  fetch('api/notifications.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=mark_read&id=' + id
  }).then(() => loadNotifications());
}

function markAllRead() {
  fetch('api/notifications.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=mark_all_read'
  }).then(() => loadNotifications());
}

document.getElementById('notifDropdown')?.addEventListener('click', loadNotifications);
</script>
<?php endif; ?>

<!-- MAIN CONTENT WRAPPER -->
<main>
