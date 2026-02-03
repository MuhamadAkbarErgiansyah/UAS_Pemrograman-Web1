<?php
require 'config.php';

$bookingId = $_GET['booking_id'] ?? 0;

// Ambil data pemesanan dengan kolom yang benar
$stmt = $pdo->prepare("
    SELECT r.*, i.title as paket_title, u.email as user_email
    FROM reservations r 
    LEFT JOIN items i ON r.package_id = i.id 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if(!$booking) {
    die("<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body style='font-family:Arial;text-align:center;padding:50px;'><h2>Pemesanan tidak ditemukan!</h2><a href='riwayat.php'>Kembali</a></body></html>");
}

// Check if approved
$approvalStmt = $pdo->prepare("SELECT * FROM booking_approvals WHERE reservation_id = ? AND status = 'approved'");
$approvalStmt->execute([$bookingId]);
$approval = $approvalStmt->fetch();

if(!$approval) {
    die("<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body style='font-family:Arial;text-align:center;padding:50px;'><h2>Pemesanan belum disetujui!</h2><p>Sertifikat hanya tersedia untuk pemesanan yang sudah disetujui.</p><a href='riwayat.php'>Kembali</a></body></html>");
}

$customerName = $booking['name'];
$paketTitle = $booking['package_title'] ?? $booking['paket_title'] ?? 'Paket Wisata';
$dateEvent = date('d F Y', strtotime($booking['date_event']));
$bookingCode = 'EB-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Sertifikat Pemesanan - <?= htmlspecialchars($customerName) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f0f0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .certificate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 900px;
            padding: 50px;
            color: white;
            position: relative;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border-radius: 15px;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            pointer-events: none;
        }
        
        .content { position: relative; z-index: 1; }
        
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .logo i { margin-right: 10px; }
        
        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-text {
            font-size: 1.1rem;
            margin: 25px 0;
            opacity: 0.95;
        }
        
        .customer-name {
            font-size: 2rem;
            font-weight: 700;
            margin: 20px 0;
            padding: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.5);
            border-top: 2px solid rgba(255,255,255,0.5);
        }
        
        .booking-details {
            background: rgba(255,255,255,0.15);
            padding: 25px;
            border-radius: 10px;
            margin: 25px auto;
            max-width: 600px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 1rem;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .detail-row:last-child { border-bottom: none; }
        .detail-label { opacity: 0.8; }
        .detail-value { font-weight: 600; }
        
        .footer-cert {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        
        .footer-cert p { font-size: 0.9rem; opacity: 0.8; }
        
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        
        @media print {
            body { background: white; padding: 0; }
            .print-btn { display: none; }
            .certificate { box-shadow: none; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="certificate">
    <div class="content">
        <div class="logo">
            <i class="fas fa-map-location-dot"></i>Explore Bandung
        </div>
        <div class="subtitle">Wisata • Paket • Guide</div>
        
        <h1>Sertifikat Pemesanan</h1>
        
        <p class="certificate-text">Dengan ini menyatakan bahwa:</p>
        
        <div class="customer-name"><?= htmlspecialchars($customerName) ?></div>
        
        <p class="certificate-text">
            Telah berhasil melakukan pemesanan paket wisata dan<br>
            pemesanan telah <strong>DISETUJUI</strong> oleh tim Explore Bandung.
        </p>
        
        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">Kode Booking</span>
                <span class="detail-value"><?= $bookingCode ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Paket Wisata</span>
                <span class="detail-value"><?= htmlspecialchars($paketTitle) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tanggal Wisata</span>
                <span class="detail-value"><?= $dateEvent ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kontak</span>
                <span class="detail-value"><?= htmlspecialchars($booking['contact']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color: #90EE90;">✓ APPROVED</span>
            </div>
        </div>
        
        <div class="footer-cert">
            <p>Sertifikat ini berlaku sebagai bukti pemesanan resmi.</p>
            <p>Diterbitkan pada: <?= date('d F Y, H:i') ?> WIB</p>
        </div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">
    <i class="fas fa-print me-2"></i>Cetak Sertifikat
</button>

</body>
</html>
