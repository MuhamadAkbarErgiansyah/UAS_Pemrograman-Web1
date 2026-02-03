<?php
require '../config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Check login
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

switch($action) {
    case 'send':
        $receiverId = intval($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if(empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $receiverId, $message]);
        
        // Create notification for receiver
        $senderName = $_SESSION['username'] ?? 'User';
        $notifMsg = $isAdmin ? "Pesan baru dari Admin: " . substr($message, 0, 50) : "Pesan baru dari $senderName: " . substr($message, 0, 50);
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'message', ?)");
        $notifStmt->execute([$receiverId, $notifMsg]);
        
        echo json_encode(['success' => true, 'message' => 'Pesan terkirim']);
        break;
        
    case 'get_messages':
        $otherId = intval($_GET['other_id'] ?? 0);
        
        // Get messages between current user and other user
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   CASE WHEN m.sender_id = ? THEN 'sent' ELSE 'received' END as direction,
                   u.name as sender_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$userId, $userId, $otherId, $otherId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark as read
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$otherId, $userId]);
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;
        
    case 'get_conversations':
        if($isAdmin) {
            // Admin sees all users who have messaged
            $stmt = $pdo->query("
                SELECT u.id, u.name, u.email,
                       (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = (SELECT id FROM users WHERE role = 'admin' LIMIT 1) AND is_read = 0) as unread_count,
                       (SELECT message FROM messages WHERE (sender_id = u.id OR receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE (sender_id = u.id OR receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_time
                FROM users u
                WHERE u.role != 'admin' 
                AND u.id IN (SELECT DISTINCT sender_id FROM messages UNION SELECT DISTINCT receiver_id FROM messages)
                ORDER BY last_time DESC
            ");
        } else {
            // User sees conversation with admin
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, 'Admin' as display_name,
                       (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count,
                       (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message
                FROM users u
                WHERE u.role = 'admin'
                LIMIT 1
            ");
            $stmt->execute([$userId, $userId, $userId]);
        }
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        break;
        
    case 'get_unread_count':
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();
        
        echo json_encode(['success' => true, 'count' => $count]);
        break;
        
    case 'get_admin_id':
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        echo json_encode(['success' => true, 'admin_id' => $admin['id'] ?? 0]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
