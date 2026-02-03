<?php
require 'config.php'; // <<< tambahkan ini supaya $pdo tersedia
$pageTitle = "Register";
require 'header.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])){
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $pass2 = $_POST['password2'];

  if($password !== $pass2){
    $error = "Password tidak sama.";
  } else {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    try {
      $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
      $stmt->execute([$name,$email,$hash]);
      header('Location: login.php');
      exit;
    } catch (Exception $e){
      $error = "Gagal registrasi: ".$e->getMessage();
    }
  }
}
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4 shadow-sm">
      <h3>Register</h3>
      <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
      <form method="post" onsubmit="return validateRegister()">
        <div class="mb-3"><label class="form-label">Nama</label><input name="name" id="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input name="email" id="emailReg" type="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input name="password" id="pwd1" type="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Konfirmasi Password</label><input name="password2" id="pwd2" type="password" class="form-control" required></div>
        <div class="d-flex justify-content-end"><button name="register_submit" class="btn btn-success">Daftar</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function validateRegister(){
  var n = document.getElementById('name').value.trim();
  var e = document.getElementById('emailReg').value.trim();
  var p1 = document.getElementById('pwd1').value;
  var p2 = document.getElementById('pwd2').value;
  if(n === '' || e === '' || p1 === '' || p2 === ''){
    alert('Semua field wajib diisi');
    return false;
  }
  if(p1 !== p2){
    alert('Password dan konfirmasi tidak sama');
    return false;
  }
  if(p1.length < 6){
    alert('Password minimal 6 karakter');
    return false;
  }
  return true;
}
</script>

<?php require 'footer.php'; ?>
