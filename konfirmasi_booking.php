<?php
session_start();
require "config/koneksi.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu!');
        window.location.href='login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil booking terakhir yang belum dikonfirmasi
// Ambil booking terakhir yang belum dikonfirmasi
$query = $koneksi->prepare("
    SELECT b.*, j.tanggal, j.jam_mulai, j.jam_selesai, s.nama
    FROM booking b
    JOIN jadwal j ON b.id_jadwal = j.id_jadwal
    JOIN studio s ON b.id_studio = s.id_studio
    WHERE b.id_user = ? 
    ORDER BY b.id_order DESC LIMIT 1
");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
$booking = $result->fetch_assoc();

// Proses konfirmasi
if (isset($_POST['konfirmasi'])) {
    $update = $koneksi->prepare("UPDATE booking SET status = 'terkonfirmasi' WHERE id_order = ?");
    $update->bind_param("i", $booking['id_order']);
    $update->execute();
    echo "<script>
        alert('Booking berhasil dikonfirmasi!');
        window.location.href='riwayat_reservasi.php';
    </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .header { background-color: #000; color: white; padding: 12px 20px; border-radius: 10px; display: inline-block; }
        .btn-yellow { background-color: #FFD700; color: #000; font-weight: 600; border: none; }
        .btn-yellow:hover { background-color: #e6c200; }
    </style>
</head>
<body>

<div class="container my-5">
    <h3 class="header mb-4">Konfirmasi Booking</h3>
    
    <div class="card p-4">
        <h5 class="mb-3">Detail Reservasi Anda</h5>
        <table class="table">
            <tr><th>ID Order</th><td><?= htmlspecialchars($booking['id_order']) ?></td></tr>
            <tr><th>Studio</th><td><?= htmlspecialchars($booking['nama']) ?></td></tr>
            <tr><th>Tanggal</th><td><?= htmlspecialchars($booking['tanggal']) ?></td></tr>
            <tr><th>Jam</th><td><?= htmlspecialchars($booking['jam_mulai'] . " - " . $booking['jam_selesai']) ?></td></tr>
            <tr><th>Total Biaya</th><td>Rp <?= number_format($booking['total_tagihan'], 0, ',', '.') ?></td></tr>
            <tr><th>Status Saat Ini</th><td><span class="badge bg-warning text-dark"><?= htmlspecialchars($booking['status']) ?></span></td></tr>
            <?php if (!empty($booking['bukti_dp'])): ?>
                <tr>
                    <th>Bukti DP</th>
                    <td>
                        <a href="uploads/<?= htmlspecialchars($booking['bukti_dp']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti</a>
                    </td>
                </tr>
            <?php endif; ?>
        </table>

        <form method="post">
            <div class="d-flex justify-content-between">
                <a href="ketentuan.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" name="konfirmasi" class="btn btn-yellow">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
