<?php
require 'config.php';
$pageTitle = "Chat dengan Admin";

if(!isset($_SESSION['user_id'])){
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require 'header.php';

// Get admin ID
$adminStmt = $pdo->query("SELECT id, name FROM users WHERE role = 'admin' LIMIT 1");
$admin = $adminStmt->fetch();
$adminId = $admin['id'] ?? 0;
$adminName = $admin['name'] ?? 'Admin';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Chat Header -->
        <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <div class="d-flex align-items-center">
            <div class="me-3">
              <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="fas fa-headset fa-lg"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">Chat dengan Admin</h5>
              <small class="opacity-75"><i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>Online</small>
            </div>
          </div>
        </div>

        <!-- Chat Messages -->
        <div class="card-body p-0">
          <div id="chatMessages" class="p-4" style="height: 400px; overflow-y: auto; background: #f8f9fa;">
            <div class="text-center text-muted py-5">
              <i class="fas fa-comments fa-3x mb-3"></i>
              <p>Memuat pesan...</p>
            </div>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="card-footer bg-white p-3">
          <form id="chatForm" class="d-flex gap-2">
            <input type="hidden" id="receiverId" value="<?= $adminId ?>">
            <input type="text" id="messageInput" class="form-control rounded-pill" placeholder="Ketik pesan..." autocomplete="off">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      </div>

      <!-- Quick Questions -->
      <div class="mt-4">
        <h6 class="text-muted mb-3">Pertanyaan Cepat:</h6>
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-sm btn-outline-primary rounded-pill quick-msg" data-msg="Halo, saya ingin bertanya tentang paket wisata">
            <i class="fas fa-box me-1"></i>Tentang Paket
          </button>
          <button class="btn btn-sm btn-outline-primary rounded-pill quick-msg" data-msg="Bagaimana cara melakukan pembayaran?">
            <i class="fas fa-credit-card me-1"></i>Pembayaran
          </button>
          <button class="btn btn-sm btn-outline-primary rounded-pill quick-msg" data-msg="Apakah bisa reschedule tanggal perjalanan?">
            <i class="fas fa-calendar me-1"></i>Reschedule
          </button>
          <button class="btn btn-sm btn-outline-primary rounded-pill quick-msg" data-msg="Saya ingin tahu tentang promo yang sedang berlaku">
            <i class="fas fa-tags me-1"></i>Promo
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

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

// Load messages
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
        chatMessages.innerHTML = `
          <div class="text-center text-muted py-5">
            <i class="fas fa-comments fa-3x mb-3"></i>
            <p>Belum ada pesan. Mulai percakapan!</p>
          </div>
        `;
      }
    });
}

// Send message
chatForm.addEventListener('submit', function(e) {
  e.preventDefault();
  const message = messageInput.value.trim();
  if(!message) return;

  const formData = new FormData();
  formData.append('action', 'send');
  formData.append('receiver_id', receiverId);
  formData.append('message', message);

  fetch('api/chat.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if(data.success) {
      messageInput.value = '';
      loadMessages();
    }
  });
});

// Quick messages
document.querySelectorAll('.quick-msg').forEach(btn => {
  btn.addEventListener('click', function() {
    messageInput.value = this.dataset.msg;
    messageInput.focus();
  });
});

// Initial load and auto-refresh
loadMessages();
setInterval(loadMessages, 5000); // Refresh every 5 seconds
</script>

<?php require 'footer.php'; ?>
