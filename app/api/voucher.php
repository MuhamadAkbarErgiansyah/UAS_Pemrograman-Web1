<?php
require '../config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'validate':
        $code = strtoupper(trim($_GET['code'] ?? $_POST['code'] ?? ''));
        $orderTotal = floatval($_GET['order_total'] ?? $_POST['total'] ?? 0);
        
        if(empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Kode voucher tidak boleh kosong']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $voucher = $stmt->fetch();
        
        if(!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Kode voucher tidak valid']);
            exit;
        }
        
        // Check validity period
        $now = date('Y-m-d');
        if($voucher['valid_from'] && $now < $voucher['valid_from']) {
            echo json_encode(['success' => false, 'message' => 'Voucher belum berlaku']);
            exit;
        }
        if($voucher['valid_until'] && $now > $voucher['valid_until']) {
            echo json_encode(['success' => false, 'message' => 'Voucher sudah kadaluarsa']);
            exit;
        }
        
        // Check max uses
        if($voucher['max_uses'] !== null && $voucher['used_count'] >= $voucher['max_uses']) {
            echo json_encode(['success' => false, 'message' => 'Voucher sudah habis digunakan']);
            exit;
        }
        
        // Check min order
        if($orderTotal < $voucher['min_order']) {
            echo json_encode(['success' => false, 'message' => 'Minimum order Rp ' . number_format($voucher['min_order'], 0, ',', '.')]);
            exit;
        }
        
        // Calculate discount
        if($voucher['discount_type'] === 'percent' || $voucher['discount_type'] === 'percentage') {
            $discount = $orderTotal * ($voucher['discount_value'] / 100);
        } else {
            $discount = $voucher['discount_value'];
        }
        
        $finalTotal = max(0, $orderTotal - $discount);
        
        echo json_encode([
            'success' => true, 
            'valid' => true,
            'message' => 'Voucher berhasil diterapkan!',
            'discount_amount' => $discount,
            'voucher' => [
                'code' => $voucher['code'],
                'discount_type' => $voucher['discount_type'],
                'discount_value' => $voucher['discount_value'],
                'discount_amount' => $discount,
                'final_total' => $finalTotal
            ]
        ]);
        break;
        
    case 'use':
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $stmt = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE code = ?");
        $stmt->execute([$code]);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
