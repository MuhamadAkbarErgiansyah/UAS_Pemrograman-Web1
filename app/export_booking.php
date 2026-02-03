<?php
/**
 * Export Individual Booking Confirmation
 * URL: export_booking.php?id=1&format=pdf atau export_booking.php?id=1&format=excel
 */

require 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('Location: login.php');
  exit;
}

$bookingId = intval($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'pdf';

if($bookingId <= 0) {
    die("Invalid booking ID");
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as customer_name, 
           u.email as customer_email,
           i.title as package_title, 
           i.price as package_price,
           i.summary as package_summary,
           COALESCE(ba.status, 'pending') as booking_status,
           ba.approval_date,
           ba.notes as admin_notes
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN items i ON r.package_id = i.id
    LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if(!$booking) {
    die("Booking not found or access denied");
}

// Generate output based on format
if($format === 'excel') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Pemesanan_' . $bookingId . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['SURAT KONFIRMASI PEMESANAN']);
    fputcsv($output, ['Explore Bandung - Tour & Travel']);
    fputcsv($output, ['']);
    fputcsv($output, ['INFORMASI PEMESANAN']);
    fputcsv($output, ['No. Booking', '#' . $booking['id']]);
    fputcsv($output, ['Tanggal Pemesanan', date('d F Y H:i', strtotime($booking['created_at']))]);
    fputcsv($output, ['Status', strtoupper($booking['booking_status'])]);
    fputcsv($output, ['']);
    fputcsv($output, ['INFORMASI PELANGGAN']);
    fputcsv($output, ['Nama', $booking['name']]);
    fputcsv($output, ['Email', $booking['email']]);
    fputcsv($output, ['Kontak', $booking['contact']]);
    fputcsv($output, ['']);
    fputcsv($output, ['DETAIL PAKET']);
    fputcsv($output, ['Paket', $booking['package_title']]);
    fputcsv($output, ['Harga', $booking['package_price']]);
    fputcsv($output, ['Tanggal Keberangkatan', date('d F Y', strtotime($booking['date_event']))]);
    fputcsv($output, ['Catatan', $booking['note'] ?? '-']);
    
    if($booking['admin_notes']) {
        fputcsv($output, ['']);
        fputcsv($output, ['CATATAN ADMIN']);
        fputcsv($output, ['', $booking['admin_notes']]);
    }
    
    fputcsv($output, ['']);
    fputcsv($output, ['Terima kasih telah mempercayai Explore Bandung!']);
    
    fclose($output);
    exit;
    
} else {
    // PDF Export (HTML format for printing)
    header('Content-Type: text/html; charset=utf-8');
    
    $statusColor = [
        'pending' => '#f59e0b',
        'approved' => '#10b981',
        'rejected' => '#ef4444',
        'completed' => '#6366f1'
    ];
    
    $statusBg = $statusColor[$booking['booking_status']] ?? '#6b7280';
    
    echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan #' . $booking['id'] . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            padding: 40px; 
            background: #f3f4f6;
            color: #1f2937;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            background: ' . $statusBg . ';
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h2 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section h2:before {
            content: "■";
            margin-right: 10px;
            font-size: 12px;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            flex: 1;
            color: #1f2937;
        }
        .highlight-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .footer {
            background: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .print-button:hover {
            background: #5568d3;
        }
        @media print {
            body { padding: 0; background: white; }
            .print-button { display: none; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print / Save PDF</button>
    
    <div class="container">
        <div class="header">
            <h1>SURAT KONFIRMASI PEMESANAN</h1>
            <p>Explore Bandung - Tour & Travel</p>
        </div>
        
        <div class="content">
            <div style="text-align: center;">
                <span class="status-badge">Status: ' . strtoupper($booking['booking_status']) . '</span>
            </div>
            
            <div class="section">
                <h2>Informasi Pemesanan</h2>
                <div class="info-row">
                    <div class="info-label">No. Booking</div>
                    <div class="info-value"><strong>#' . $booking['id'] . '</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pemesanan</div>
                    <div class="info-value">' . date('d F Y, H:i', strtotime($booking['created_at'])) . ' WIB</div>
                </div>
                ' . ($booking['approval_date'] ? '
                <div class="info-row">
                    <div class="info-label">Tanggal Approval</div>
                    <div class="info-value">' . date('d F Y, H:i', strtotime($booking['approval_date'])) . ' WIB</div>
                </div>' : '') . '
            </div>
            
            <div class="section">
                <h2>Informasi Pelanggan</h2>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-value">' . htmlspecialchars($booking['name']) . '</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">' . htmlspecialchars($booking['email']) . '</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. Kontak</div>
                    <div class="info-value">' . htmlspecialchars($booking['contact']) . '</div>
                </div>
            </div>
            
            <div class="section">
                <h2>Detail Paket Wisata</h2>
                <div class="info-row">
                    <div class="info-label">Paket</div>
                    <div class="info-value"><strong>' . htmlspecialchars($booking['package_title']) . '</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Harga</div>
                    <div class="info-value"><strong>' . htmlspecialchars($booking['package_price']) . '</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Keberangkatan</div>
                    <div class="info-value">' . date('d F Y', strtotime($booking['date_event'])) . '</div>
                </div>
                ' . ($booking['note'] ? '
                <div class="info-row">
                    <div class="info-label">Catatan Khusus</div>
                    <div class="info-value">' . nl2br(htmlspecialchars($booking['note'])) . '</div>
                </div>' : '') . '
            </div>
            
            ' . ($booking['admin_notes'] ? '
            <div class="highlight-box">
                <strong>📝 Catatan dari Admin:</strong><br>
                ' . nl2br(htmlspecialchars($booking['admin_notes'])) . '
            </div>' : '') . '
            
            ' . ($booking['booking_status'] === 'approved' ? '
            <div class="highlight-box">
                <strong>✅ Pemesanan Anda telah disetujui!</strong><br>
                Silahkan lakukan konfirmasi dan pembayaran sesuai instruksi yang telah dikirimkan ke email Anda.
            </div>' : '') . '
        </div>
        
        <div class="footer">
            <p><strong>Explore Bandung</strong></p>
            <p>Email: contact@exploreBandung.com | WhatsApp: +62 812-3456-7890</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Dokumen ini digenerate otomatis pada ' . date('d F Y H:i') . ' WIB
            </p>
        </div>
    </div>
    
    <script>
        // Auto open print dialog jika dari parameter print=1
        if(window.location.search.includes("print=1")) {
            window.print();
        }
    </script>
</body>
</html>';
    exit;
}
?>
