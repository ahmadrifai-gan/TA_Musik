<?php
require "../config/koneksi.php";

// Daftar nama bulan (Bahasa Indonesia)
$bulanList = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil data booking (reservasi)
$q1 = "SELECT MONTH(Tanggal) AS bulan,  COUNT(*) AS total_reservasi
       FROM booking
       WHERE status != 'dibatalkan'
       GROUP BY MONTH(Tanggal)";
$r1 = mysqli_query($koneksi, $q1);

$reservasi = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($r1)) {
    $reservasi[(int)$row['bulan']] = (int)$row['total_reservasi'];
}

// Ambil data pendapatan dari tabel booking (total_tagihan)
$q2 = "SELECT MONTH(Tanggal) AS bulan, SUM(total_tagihan) AS total_pendapatan
       FROM booking
       WHERE status != 'dibatalkan'
       GROUP BY MONTH(Tanggal)";
$r2 = mysqli_query($koneksi, $q2);

$pendapatan = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($r2)) {
    $pendapatan[(int)$row['bulan']] = (float)$row['total_pendapatan'];
}

// Hasil akhir
echo json_encode([
    "labels" => array_values($bulanList),
    "reservasi" => array_values($reservasi),
    "pendapatan" => array_values($pendapatan)
]);
?>