<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama   = $_POST['nama'];
    $studio = $_POST['studio'];
    $paket  = $_POST['paket'];
    $tanggal= $_POST['tanggal'];
    $jam    = $_POST['jam'];
    $jam_selesai = $_POST['jam_selesai'];
    $durasi = $_POST['durasi'];
    $total  = $_POST['total'];
} else {
    header("Location: booking.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Konfirmasi Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow p-4">
    <h3 class="mb-3">Konfirmasi Booking</h3>
    <ul class="list-group mb-3">
      <li class="list-group-item"><strong>Nama:</strong> <?= $nama ?></li>
      <li class="list-group-item"><strong>Studio:</strong> <?= $studio ?></li>
      <li class="list-group-item"><strong>Paket:</strong> <?= $paket ?></li>
      <li class="list-group-item"><strong>Tanggal:</strong> <?= $tanggal ?></li>
      <li class="list-group-item"><strong>Jam:</strong> <?= $jam ?></li>
      <li class="list-group-item"><strong>Jam Selesai:</strong> <?= $jam_selesai ?></li>
      <li class="list-group-item"><strong>Durasi:</strong> <?= $durasi ?> Jam</li>
      <li class="list-group-item"><strong>Total:</strong> Rp <?= number_format($total,0,',','.') ?></li>
    </ul>

    <form action="ketentuan.php" method="POST">
      <input type="hidden" name="nama" value="<?= $nama ?>">
      <input type="hidden" name="studio" value="<?= $studio ?>">
      <input type="hidden" name="paket" value="<?= $paket ?>">
      <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
      <input type="hidden" name="jam" value="<?= $jam ?>">
      <input type="hidden" name="durasi" value="<?= $durasi ?>">
      <input type="hidden" name="total" value="<?= $total ?>">
      <button type="submit" class="btn btn-success">Simpan Booking</button>
    </form>
  </div>
</div>
</body>
</html>
