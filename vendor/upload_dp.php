<?php
session_start();
if (!isset($_SESSION['booking_data'])) {
    header("Location: booking.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Upload Bukti DP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow p-4">
    <h3 class="mb-3">Upload Bukti DP</h3>
    <form action="simpan_booking.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Unggah Bukti Pembayaran</label>
        <input type="file" name="bukti_dp" class="form-control" required>
      </div>
      <div class="d-flex justify-content-between">
        <a href="ketentuan.php" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-success">Lanjutkan</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
