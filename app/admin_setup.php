<?php
require 'config.php';

$message = '';
$error = '';

if($_POST['create_admin'] ?? false) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    if(!$username || !$password || !$email) {
        $error = 'Semua field harus diisi!';
    } else if(strlen($password) < 12) {
        $error = 'Password minimal 12 karakter!';
    } else {
        // Check if admin already exists
        $check = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $check->execute();
        if($check->fetch()['count'] > 0) {
            $error = 'Admin sudah ada di sistem!';
        } else {
            // Create admin with hashed password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
            
            if($stmt->execute([$username, $hashedPassword, $email])) {
                $message = "✅ Admin berhasil dibuat! Username: <strong>$username</strong>";
            } else {
                $error = 'Gagal membuat admin!';
            }
        }
    }
}

// Check if admin exists
$adminCheck = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$adminExists = $adminCheck->fetch()['count'] > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Setup - Explore Bandung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .setup-card { background: white; border-radius: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="setup-card shadow-lg p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-tools fa-3x" style="color: #667eea;"></i>
                        <h1 class="fw-bold mt-3" style="color: #1f2937;">Admin Setup</h1>
                        <p class="text-muted">Buat akun admin pertama untuk mengelola sistem</p>
                    </div>

                    <?php if($adminExists): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Admin sudah ada!</strong> Silakan login ke <a href="admin_login.php">Admin Login</a>
                        </div>
                    <?php else: ?>
                        <?php if($message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username Admin</label>
                                <input type="text" name="username" class="form-control form-control-lg rounded-3" 
                                       placeholder="Contoh: admin_bandung" required>
                                <small class="text-muted">Username untuk login admin</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Admin</label>
                                <input type="email" name="email" class="form-control form-control-lg rounded-3" 
                                       placeholder="admin@explorebandung.com" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Password Admin</label>
                                <input type="password" name="password" class="form-control form-control-lg rounded-3" 
                                       placeholder="Minimal 12 karakter yang kuat" required>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Password harus:
                                    <ul class="mb-0">
                                        <li>Minimal 12 karakter</li>
                                        <li>Kombinasi huruf besar, kecil, angka, dan simbol</li>
                                        <li>Contoh: Exploit@2024!Bd</li>
                                    </ul>
                                </small>
                            </div>

                            <button type="submit" name="create_admin" value="1" class="btn btn-lg w-100 fw-bold rounded-3" 
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                                <i class="fas fa-user-plus me-2"></i>Buat Admin
                            </button>
                        </form>

                        <hr>

                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>⚠️ PENTING:</strong> Simpan username dan password admin dengan aman! 
                            Jangan bagikan ke siapapun.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-4">
                    <small class="text-white">
                        <a href="index.php" class="text-white text-decoration-none">← Kembali ke Beranda</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
