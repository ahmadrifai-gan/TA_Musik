<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['booking_data'] = $_POST;
} else {
    header("Location: booking.php");
    exit;
}
$data = $_SESSION['booking_data'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ketentuan Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow p-4">
    <h3 class="text-center mb-4">Ketentuan Booking</h3>
    <ol>
      <li>DP minimal Rp20.000 melalui QRIS.</li>
      <li>Pelunasan di studio.</li>
      <li>Waktu booking sesuai jadwal, tidak ada toleransi keterlambatan.</li>
      <li>Reschedule hanya bisa H-1 sebelum jadwal.</li>
      <li>Dilarang membawa makanan/minuman ke studio.</li>
      <li>Bertanggung jawab atas peralatan studio.</li>
    </ol>
    <p class="fw-bold">Scan QRIS berikut untuk pembayaran DP Rp20.000:</p>
    <div class="text-center">
      <img src="qris.png" alt="QRIS" class="img-fluid" style="max-width:250px;">
    </div>
    <p class="text-center mt-3">Apakah Anda ingin DP sekarang?</p>
    <div class="d-flex justify-content-center gap-2">
      <form action="simpan_booking.php" method="POST">
        <button type="submit" class="btn btn-secondary">Lewati</button>
      </form>
      <form action="upload_dp.php" method="POST">
        <button type="submit" class="btn btn-primary">Upload Bukti DP</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
