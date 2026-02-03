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

$pageTitle = "Chat dengan Pelanggan";
$currentPage = "chat";

// Get selected user
$selectedUserId = intval($_GET['user'] ?? 0);
$selectedUser = null;

if($selectedUserId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch();
}

// Get all users with messages
$usersStmt = $pdo->query("
    SELECT DISTINCT u.id, u.name, u.email,
           (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = {$_SESSION['user_id']} AND is_read = 0) as unread,
           (SELECT created_at FROM messages WHERE sender_id = u.id OR receiver_id = u.id ORDER BY created_at DESC LIMIT 1) as last_msg_time
    FROM users u
    INNER JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id
    WHERE u.role != 'admin'
    GROUP BY u.id
    ORDER BY last_msg_time DESC
");
$users = $usersStmt->fetchAll();

require 'includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1"><i class="fas fa-comments me-2" style="color: #667eea;"></i>Chat Pelanggan</h2>
    <p class="text-muted mb-0">Kelola percakapan dengan pelanggan</p>
  </div>
</div>

<div class="row g-4">
  <!-- User List -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm" style="height: 600px;">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Pelanggan</h6>
      </div>
      <div class="card-body p-0" style="overflow-y: auto;">
        <?php if(!empty($users)): ?>
          <?php foreach($users as $u): ?>
          <a href="?user=<?= $u['id'] ?>" class="d-flex align-items-center p-3 border-bottom text-decoration-none <?= $selectedUserId == $u['id'] ? 'bg-primary-subtle' : '' ?>">
            <div class="me-3">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <?= strtoupper(substr($u['name'], 0, 1)) ?>
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
            </div>
            <?php if($u['unread'] > 0): ?>
              <span class="badge bg-danger rounded-pill"><?= $u['unread'] ?></span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center text-muted py-5">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>Belum ada percakapan</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Chat Area -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm" style="height: 600px;">
      <?php if($selectedUser): ?>
        <!-- Chat Header -->
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <div class="d-flex align-items-center">
            <div class="me-3">
              <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <?= strtoupper(substr($selectedUser['name'], 0, 1)) ?>
              </div>
            </div>
            <div>
              <h6 class="mb-0"><?= htmlspecialchars($selectedUser['name']) ?></h6>
              <small class="opacity-75"><?= htmlspecialchars($selectedUser['email']) ?></small>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="card-body p-3" id="chatMessages" style="height: 400px; overflow-y: auto; background: #f8f9fa;">
          <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
          </div>
        </div>

        <!-- Input -->
        <div class="card-footer bg-white p-3">
          <form id="chatForm" class="d-flex gap-2">
            <input type="hidden" id="receiverId" value="<?= $selectedUser['id'] ?>">
            <input type="text" id="messageInput" class="form-control rounded-pill" placeholder="Ketik balasan..." autocomplete="off">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      <?php else: ?>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div class="text-center text-muted">
            <i class="fas fa-comments fa-4x mb-3"></i>
            <h5>Pilih pelanggan untuk memulai chat</h5>
            <p>Klik nama pelanggan di sebelah kiri</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if($selectedUser): ?>
<style>
.message-bubble {
  max-width: 75%;
  padding: 12px 16px;
  border-radius: 18px;
  margin-bottom: 10px;
  word-wrap: break-word;
}
.message-sent {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  margin-left: auto;
  border-bottom-right-radius: 4px;
}
.message-received {
  background: white;
  color: #333;
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.message-time {
  font-size: 10px;
  opacity: 0.7;
  margin-top: 4px;
}
</style>

<script>
const receiverId = document.getElementById('receiverId').value;
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');

function loadMessages() {
  fetch(`api/chat.php?action=get_messages&other_id=${receiverId}`)
    .then(r => r.json())
    .then(data => {
      if(data.success && data.messages.length > 0) {
        let html = '';
        data.messages.forEach(msg => {
          const time = new Date(msg.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
          const isMe = msg.direction === 'sent';
          html += `
            <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'}">
              <div class="message-bubble ${isMe ? 'message-sent' : 'message-received'}">
                ${msg.message}
                <div class="message-time text-end">${time}</div>
              </div>
            </div>
          `;
        });
        chatMessages.innerHTML = html;
        chatMessages.scrollTop = chatMessages.scrollHeight;
      } else {
        chatMessages.innerHTML = '<div class="text-center text-muted py-5"><p>Belum ada pesan</p></div>';
      }
    });
}

chatForm.addEventListener('submit', function(e) {
  e.preventDefault();
  const message = messageInput.value.trim();
  if(!message) return;

  const formData = new FormData();
  formData.append('action', 'send');
  formData.append('receiver_id', receiverId);
  formData.append('message', message);

  fetch('api/chat.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if(data.success) {
        messageInput.value = '';
        loadMessages();
      }
    });
});

loadMessages();
setInterval(loadMessages, 3000);
</script>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>
