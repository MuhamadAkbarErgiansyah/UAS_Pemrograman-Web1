<?php
require 'config.php';

// Redirect if already logged in as admin
if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
  header('Location: admin_dashboard.php');
  exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($username) || empty($password)){
        $error = 'Username dan password harus diisi!';
    } else {
        // Cek user dengan role admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (name = ? OR email = ?) AND role = 'admin'");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['name'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            $_SESSION['login_time'] = time();
            
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Username atau password admin salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Explore Bandung</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .login-container {
      width: 100%;
      max-width: 420px;
    }
    
    .logo-section {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo-icon {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 20px;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .logo-icon i {
      font-size: 2.5rem;
      color: #667eea;
    }
    
    .logo-section h1 {
      color: white;
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 5px;
    }
    
    .logo-section p {
      color: rgba(255,255,255,0.8);
      font-size: 14px;
    }
    
    .login-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .card-header-custom {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 25px 30px;
      color: white;
    }
    
    .card-header-custom h4 {
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .card-header-custom p {
      font-size: 14px;
      opacity: 0.8;
      margin: 0;
    }
    
    .card-body-custom {
      padding: 30px;
    }
    
    .form-label {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 8px;
    }
    
    .input-group-text {
      background: #f3f4f6;
      border: none;
      color: #667eea;
    }
    
    .form-control {
      background: #f3f4f6;
      border: none;
      padding: 12px 15px;
      font-size: 14px;
    }
    
    .form-control:focus {
      background: #f3f4f6;
      box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
    }
    
    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(102,126,234,0.4);
      color: white;
    }
    
    .credential-box {
      background: #f0f9ff;
      border: 1px dashed #667eea;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      margin-bottom: 20px;
    }
    
    .credential-box code {
      background: white;
      padding: 8px 15px;
      border-radius: 5px;
      font-size: 13px;
      display: inline-block;
      margin-top: 8px;
      color: #667eea;
    }
    
    .back-link {
      text-align: center;
      margin-top: 20px;
    }
    
    .back-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }
    
    .back-link a:hover {
      text-decoration: underline;
    }
    
    .card-footer-custom {
      background: #f9fafb;
      padding: 15px 30px;
      text-align: center;
      border-top: 1px solid #e5e7eb;
    }
    
    .card-footer-custom small {
      color: #6b7280;
    }
    
    .alert {
      border: none;
      border-radius: 10px;
      padding: 12px 15px;
      margin-bottom: 20px;
    }
    
    .security-note {
      background: rgba(255,255,255,0.95);
      border-radius: 15px;
      padding: 15px 20px;
      margin-top: 20px;
      font-size: 13px;
      color: #374151;
    }
    
    .security-note i {
      color: #667eea;
    }
  </style>
</head>
<body>

<div class="login-container">
  <!-- Logo Section -->
  <div class="logo-section">
    <div class="logo-icon">
      <i class="fas fa-user-shield"></i>
    </div>
    <h1>Admin Login</h1>
    <p>Kelola sistem Explore Bandung</p>
  </div>

  <!-- Login Card -->
  <div class="login-card">
    <div class="card-header-custom">
      <h4><i class="fas fa-lock me-2"></i>Selamat Datang Admin</h4>
      <p>Masukkan kredensial admin Anda</p>
    </div>

    <div class="card-body-custom">
      <?php if($error): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Credential Info -->
      <div class="credential-box">
        <small class="text-muted">
          <i class="fas fa-info-circle me-1"></i>Kredensial Admin Default:
        </small>
        <br>
        <code>Username: admin | Password: admin123</code>
      </div>

      <form method="POST">
        <!-- Username -->
        <div class="mb-3">
          <label class="form-label">
            <i class="fas fa-user me-2"></i>Username Admin
          </label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-user"></i>
            </span>
            <input type="text" name="username" class="form-control" 
                   placeholder="Masukkan username admin" required 
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="form-label">
            <i class="fas fa-lock me-2"></i>Password Admin
          </label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-lock"></i>
            </span>
            <input type="password" name="password" class="form-control" 
                   placeholder="Masukkan password admin" required>
          </div>
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt me-2"></i>Login sebagai Admin
        </button>
      </form>

      <hr style="margin: 25px 0;">

      <!-- Back Link -->
      <div class="back-link">
        <small class="text-muted d-block mb-2">Bukan admin?</small>
        <a href="login.php">
          <i class="fas fa-arrow-left me-1"></i>Kembali ke User Login
        </a>
      </div>
    </div>

    <div class="card-footer-custom">
      <small>
        <i class="fas fa-shield-alt me-1"></i>
        Halaman ini hanya untuk administrator sistem
      </small>
    </div>
  </div>

  <!-- Security Note -->
  <div class="security-note">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Catatan Keamanan:</strong> Jangan bagikan kredensial admin ke siapapun. 
    Logout setelah selesai bekerja.
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
