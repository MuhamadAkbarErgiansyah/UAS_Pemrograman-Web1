<?php
require 'config.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])){
  header('Location: index.php');
  exit;
}

// Handle login form
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  
  // Validation
  if(empty($username) || empty($password)){
    $error = "Username/Email dan password harus diisi!";
  } else {
    // Check user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])){
      // Set session
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['name'];
      $_SESSION['email'] = $user['email'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['login_time'] = time();
      
      // Redirect based on role
      if($user['role'] === 'admin'){
        header('Location: admin_dashboard.php');
      } else {
        header('Location: index.php');
      }
      exit;
    } else {
      $error = "Username/Email atau password salah!";
    }
  }
}

$pageTitle = "Login";
require 'header.php';
?>

<style>
  .login-container {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(106,17,203,0.1), rgba(37,117,252,0.1));
    padding: 20px;
  }

  .login-card {
    width: 100%;
    max-width: 420px;
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    overflow: hidden;
  }

  .login-header {
    background: linear-gradient(135deg, var(--primary-1), var(--primary-2));
    color: white;
    padding: 40px 30px;
    text-align: center;
  }

  .login-header h2 {
    margin: 0 0 5px 0;
    font-weight: 700;
    font-size: 28px;
  }

  .login-header p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
  }

  .login-body {
    padding: 40px 30px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
  }

  .form-control {
    padding: 12px 15px;
    border: 2px solid var(--border-light);
    border-radius: 12px;
    font-size: 14px;
  }

  .form-control:focus {
    border-color: var(--primary-2);
    box-shadow: 0 0 0 3px rgba(37,117,252,0.1);
  }

  .btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, var(--primary-1), var(--primary-2));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    margin-top: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(106,17,203,0.25);
  }

  .login-footer {
    text-align: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid var(--border-light);
  }

  .login-footer p {
    margin: 0;
    font-size: 14px;
    color: #4b5563;
  }

  .login-footer a {
    color: var(--primary-1);
    text-decoration: none;
    font-weight: 600;
  }

  .login-footer a:hover {
    text-decoration: underline;
  }

  .checkbox-remember {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    font-size: 14px;
  }

  .checkbox-remember input {
    margin-right: 8px;
  }
</style>

<div class="login-container">
  <div class="login-card">
    <!-- Header -->
    <div class="login-header">
      <h2>Selamat Datang</h2>
      <p>Masuk ke Explore Bandung</p>
    </div>

    <!-- Body -->
    <div class="login-body">
      <!-- Alert -->
      <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>
          <strong>Login Gagal!</strong><br>
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" id="loginForm" novalidate>
        <!-- Username/Email -->
        <div class="form-group">
          <label class="form-label" for="username">
            <i class="fas fa-user me-2"></i>Username atau Email
          </label>
          <input 
            type="text" 
            id="username"
            name="username" 
            class="form-control" 
            placeholder="Masukkan username atau email Anda"
            required
            autocomplete="username"
          >
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label" for="password">
            <i class="fas fa-lock me-2"></i>Password
          </label>
          <input 
            type="password" 
            id="password"
            name="password" 
            class="form-control" 
            placeholder="Masukkan password Anda"
            required
            autocomplete="current-password"
          >
        </div>

        <!-- Remember Me -->
        <div class="checkbox-remember" style="margin-bottom: 20px;">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember" style="margin: 0; cursor: pointer;">Ingat saya</label>
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt me-2"></i>LOGIN
        </button>
      </form>

      <!-- Footer -->
      <div class="login-footer">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e5e7eb;">
          <a href="admin_login.php" style="display: inline-flex; align-items: center; gap: 8px; color: #6a11cb; font-weight: 600; text-decoration: none;">
            <i class="fas fa-user-shield"></i> Login sebagai Admin
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript untuk validasi client-side -->
<script>
  document.getElementById('loginForm').addEventListener('submit', function(e){
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if(!username || !password){
      e.preventDefault();
      alert('Silakan isi semua field!');
    }
  });

  // Remember me functionality
  document.addEventListener('DOMContentLoaded', function(){
    const rememberCheckbox = document.getElementById('remember');
    const usernameField = document.getElementById('username');
    
    // Load saved username if exists
    if(localStorage.getItem('remembered_username')){
      usernameField.value = localStorage.getItem('remembered_username');
      rememberCheckbox.checked = true;
    }
    
    // Save/clear username on form submit
    document.getElementById('loginForm').addEventListener('submit', function(){
      if(rememberCheckbox.checked){
        localStorage.setItem('remembered_username', usernameField.value);
      } else {
        localStorage.removeItem('remembered_username');
      }
    });
  });
</script>

<?php require 'footer.php'; ?>

