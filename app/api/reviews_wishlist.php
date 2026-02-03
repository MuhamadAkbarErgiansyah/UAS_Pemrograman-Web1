<?php
require '../config.php';

// Cek jika user sudah login
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

$action = $_GET['action'] ?? '';

if($action === 'submit_review') {
    $itemId = $_POST['item_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $userId = $_SESSION['user_id'];

    // Validasi
    if($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
        exit;
    }

    if(strlen($comment) < 10) {
        echo json_encode(['success' => false, 'message' => 'Komentar minimal 10 karakter']);
        exit;
    }

    // Cek apakah user pernah membooking paket ini
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM reservations 
        WHERE user_id = ? AND package_id = ?
    ");
    $checkStmt->execute([$userId, $itemId]);
    $checkResult = $checkStmt->fetch();

    if($checkResult['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Anda harus memesan paket ini terlebih dahulu untuk memberikan review']);
        exit;
    }

    // Insert or update review
    $stmt = $pdo->prepare("
        INSERT INTO reviews (item_id, user_id, rating, comment, created_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = NOW()
    ");

    if($stmt->execute([$itemId, $userId, $rating, $comment])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Review berhasil ditambahkan!',
            'rating' => $rating,
            'comment' => $comment
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan review']);
    }
}
elseif($action === 'get_reviews') {
    $itemId = $_GET['item_id'] ?? 0;

    $stmt = $pdo->prepare("
        SELECT r.id, r.item_id, r.user_id, r.rating, r.comment, r.created_at, COALESCE(u.username, 'Anonim') as username FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.item_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$itemId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reviews' => $reviews]);
}
elseif($action === 'add_wishlist') {
    $itemId = $_POST['item_id'] ?? 0;
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO wishlists (user_id, item_id, created_at)
        VALUES (?, ?, NOW())
    ");

    if($stmt->execute([$userId, $itemId])) {
        echo json_encode(['success' => true, 'message' => 'Paket ditambahkan ke Wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Paket sudah ada di Wishlist']);
    }
}
elseif($action === 'remove_wishlist') {
    $itemId = $_POST['item_id'] ?? 0;
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND item_id = ?");

    if($stmt->execute([$userId, $itemId])) {
        echo json_encode(['success' => true, 'message' => 'Paket dihapus dari Wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari Wishlist']);
    }
}
elseif($action === 'check_wishlist') {
    $itemId = $_GET['item_id'] ?? 0;
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $itemId]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true, 
        'in_wishlist' => $result['count'] > 0
    ]);
}
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action tidak diketahui']);
}
?>
