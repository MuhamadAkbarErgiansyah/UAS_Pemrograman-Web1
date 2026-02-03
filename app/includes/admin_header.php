<?php
// Admin Header - reusable sidebar component
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
  header('Location: admin_login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Admin' ?> - Explore Bandung</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: #f5f7fb; min-height: 100vh; }
    
    .admin-sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 260px;
      height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px;
      overflow-y: auto;
      z-index: 1000;
    }
    
    .admin-sidebar .brand {
      text-align: center;
      padding: 20px 0;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      margin-bottom: 20px;
    }
    
    .admin-sidebar .brand-icon {
      width: 60px;
      height: 60px;
      background: rgba(255,255,255,0.2);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
    }
    
    .admin-sidebar .brand-icon i { font-size: 1.8rem; color: white; }
    .admin-sidebar .brand h5 { color: white; font-weight: 700; margin: 0; }
    .admin-sidebar .brand small { color: rgba(255,255,255,0.7); font-size: 12px; }
    
    .admin-sidebar .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 12px 15px;
      border-radius: 10px;
      margin-bottom: 5px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    
    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link.active {
      background: rgba(255,255,255,0.2);
      color: white;
    }
    
    .admin-sidebar .nav-link i { width: 20px; text-align: center; }
    
    .main-content {
      margin-left: 260px;
      padding: 30px;
      min-height: 100vh;
    }
    
    .card-custom {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    
    .card-custom .card-header {
      background: white;
      padding: 20px 25px;
      border-bottom: 1px solid #e5e7eb;
    }
    
    .status-badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    
    @media (max-width: 992px) {
      .admin-sidebar { width: 100%; height: auto; position: relative; }
      .main-content { margin-left: 0; }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="admin-sidebar">
  <div class="brand">
    <div class="brand-icon">
      <i class="fas fa-user-shield"></i>
    </div>
    <h5><?= htmlspecialchars($_SESSION['username']) ?></h5>
    <small>Administrator</small>
  </div>
  
  <nav class="nav flex-column">
    <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="admin_dashboard.php">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'analytics' ? 'active' : '' ?>" href="admin_analytics.php">
      <i class="fas fa-chart-line"></i> Analytics
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'manage' ? 'active' : '' ?>" href="manage_items.php">
      <i class="fas fa-box"></i> Kelola Paket
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'add' ? 'active' : '' ?>" href="add_item.php">
      <i class="fas fa-plus-circle"></i> Tambah Paket
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'bookings' ? 'active' : '' ?>" href="admin_bookings.php">
      <i class="fas fa-clipboard-list"></i> Kelola Pemesanan
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'admin_reviews' ? 'active' : '' ?>" href="admin_reviews.php">
      <i class="fas fa-star"></i> Monitoring Review
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'vouchers' ? 'active' : '' ?>" href="admin_vouchers.php">
      <i class="fas fa-tags"></i> Kelola Voucher
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'chat' ? 'active' : '' ?>" href="admin_chat.php">
      <i class="fas fa-comments"></i> Chat Pelanggan
      <?php
      // Get unread messages count
      try {
        $unreadChat = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user_id']} AND is_read = 0")->fetchColumn();
      } catch(Exception $e) {
        $unreadChat = 0;
      }
      if($unreadChat > 0): ?>
        <span class="badge bg-danger rounded-pill ms-auto"><?= $unreadChat ?></span>
      <?php endif; ?>
    </a>
    <a class="nav-link <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>" href="reports.php">
      <i class="fas fa-file-export"></i> Laporan & Export
    </a>
    
    <hr style="border-color: rgba(255,255,255,0.2); margin: 15px 0;">
    
    <a class="nav-link" href="logout.php" style="color: #fca5a5;">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </nav>
</div>

<!-- Main Content -->
<div class="main-content">
