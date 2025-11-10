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

// PERBAIKAN: Gunakan LEFT JOIN dan ambil data dari tabel booking langsung
$query = $koneksi->prepare("
    SELECT 
        b.*,
        s.nama as nama_studio,
        COALESCE(j.tanggal, b.Tanggal) as tanggal_booking,
        COALESCE(j.jam_mulai, SUBSTRING_INDEX(b.jam_booking, '-', 1)) as jam_mulai_booking,
        COALESCE(j.jam_selesai, SUBSTRING_INDEX(b.jam_booking, '-', -1)) as jam_selesai_booking
    FROM booking b
    JOIN studio s ON b.id_studio = s.id_studio
    LEFT JOIN jadwal j ON b.id_jadwal = j.id_jadwal
    WHERE b.id_user = ? 
    ORDER BY b.id_order DESC LIMIT 1
");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
$booking = $result->fetch_assoc();

// Jika tidak ada booking ditemukan
if (!$booking) {
    echo "<script>
        alert('Tidak ada data booking ditemukan.');
        window.location.href='index.php';
    </script>";
    exit;
}

// Proses konfirmasi
if (isset($_POST['konfirmasi'])) {
    // User hanya menandai bahwa booking dikirim, tapi status tetap menunggu
    $update = $koneksi->prepare("UPDATE booking SET status = 'menunggu' WHERE id_order = ?");
    $update->bind_param("i", $booking['id_order']);
    $update->execute();

    echo "<script>
        alert('Booking telah dikirim! Tunggu konfirmasi dari admin.');
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
        .paket-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .paket-bronze-tanpa { background-color: #e8daef; color: #633974; }
        .paket-bronze-dengan { background-color: #d7bde2; color: #4a235a; }
        .paket-gold-reguler { background-color: #fff9e6; color: #b8860b; }
        .paket-gold-2jam { background-color: #ffe680; color: #856404; }
        .paket-gold-3jam { background-color: #ffcc00; color: #664d00; }
    </style>
</head>
<body>

<div class="container my-5">
    <h3 class="header mb-4">Konfirmasi Booking</h3>
    
    <div class="card p-4">
        <h5 class="mb-3">Detail Reservasi Anda</h5>
        <table class="table">
            <tr><th>ID Order</th><td><?= htmlspecialchars($booking['id_order']) ?></td></tr>
            <tr><th>Studio</th><td><?= htmlspecialchars($booking['nama_studio']) ?></td></tr>
            
            <!-- TAMPILKAN PAKET -->
            <tr>
                <th>Paket</th>
                <td>
                    <?php
                    $paket = $booking['paket'] ?? '';
                    $namaStudio = $booking['nama_studio'] ?? '';
                    
                    if (empty($paket)) {
                        echo '<span class="text-muted">Tidak ada info paket</span>';
                    } else {
                        $classPaket = 'paket-badge';
                        $paketText = htmlspecialchars($paket);
                        
                        // Studio Bronze
                        if (stripos($namaStudio, 'bronze') !== false) {
                            if (stripos($paket, 'tanpa') !== false || stripos($paket, '35') !== false) {
                                $classPaket .= ' paket-bronze-tanpa';
                                $paketText = 'Tanpa Keyboard (35K/jam)';
                            } elseif (stripos($paket, 'dengan') !== false || stripos($paket, '40') !== false) {
                                $classPaket .= ' paket-bronze-dengan';
                                $paketText = 'Dengan Keyboard (40K/jam)';
                            }
                        }
                        // Studio Gold
                        elseif (stripos($namaStudio, 'gold') !== false) {
                            if (stripos($paket, 'reguler') !== false || stripos($paket, '50') !== false) {
                                $classPaket .= ' paket-gold-reguler';
                                $paketText = 'Reguler (50K/jam)';
                            } elseif (stripos($paket, '2 jam') !== false || stripos($paket, '90') !== false) {
                                $classPaket .= ' paket-gold-2jam';
                                $paketText = 'Paket 2 jam (90K)';
                            } elseif (stripos($paket, '3 jam') !== false || stripos($paket, '130') !== false) {
                                $classPaket .= ' paket-gold-3jam';
                                $paketText = 'Paket 3 jam (130K)';
                            }
                        }
                        
                        echo "<span class='$classPaket'>$paketText</span>";
                    }
                    ?>
                </td>
            </tr>
            
            <tr><th>Tanggal</th><td><?= htmlspecialchars($booking['tanggal_booking']) ?></td></tr>
            <tr><th>Jam</th><td><?= htmlspecialchars($booking['jam_booking']) ?></td></tr>
            <tr><th>Total Biaya</th><td>Rp <?= number_format($booking['total_tagihan'], 0, ',', '.') ?></td></tr>
            <tr><th>Status Saat Ini</th><td><span class="badge bg-warning text-dark"><?= htmlspecialchars($booking['status']) ?></span></td></tr>
            <?php if (!empty($booking['bukti_dp'])): ?>
                <tr>
                    <th>Bukti DP</th>
                    <td>
                        <a href="uploads/bukti_dp/<?= htmlspecialchars($booking['bukti_dp']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti</a>
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