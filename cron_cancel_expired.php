<?php
// ============================================================
// CRONJOB: Auto-Cancel Booking yang Expired
// ============================================================
// Jalankan setiap 5 menit via cron:
// */5 * * * * php /path/to/cron_cancel_expired.php
// 
// Atau via URL:
// */5 * * * * curl -s https://yoursite.com/cron_cancel_expired.php
// ============================================================

require __DIR__ . "/config/koneksi.php";

// 🔥 AMBIL SEMUA BOOKING YANG EXPIRED
$query = $koneksi->query("
    SELECT id_order, id_user, Tanggal, jam_booking, paket
    FROM booking
    WHERE status_pembayaran = 'belum_dibayar'
      AND status NOT IN ('dibatalkan', 'selesai')
      AND expired_at IS NOT NULL
      AND expired_at < NOW()
");

$cancelled_count = 0;

if ($query && $query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        // Update status booking menjadi dibatalkan
        $update = $koneksi->prepare("
            UPDATE booking 
            SET status = 'dibatalkan' 
            WHERE id_order = ?
        ");
        $update->bind_param("i", $row['id_order']);
        
        if ($update->execute()) {
            $cancelled_count++;
            
            // Log aktivitas (opsional)
            error_log(sprintf(
                "[AUTO-CANCEL] Booking ID: %d | User: %d | Tanggal: %s | Jam: %s | Paket: %s",
                $row['id_order'],
                $row['id_user'],
                $row['Tanggal'],
                $row['jam_booking'],
                $row['paket']
            ));
        }
    }
}

// Output hasil (untuk monitoring)
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'cancelled_bookings' => $cancelled_count,
    'message' => "$cancelled_count booking telah dibatalkan karena expired"
]);

$koneksi->close();
?>