<?php
session_start();

// Cek apakah ada data booking di session
if (!isset($_SESSION['booking_data'])) {
  header("Location: index.php");
  exit;
}

$booking = $_SESSION['booking_data'];

// Validasi id_order
if (!isset($booking['id_order']) || empty($booking['id_order'])) {
    die("Error: ID Order tidak ditemukan. Silakan ulangi proses booking dari awal.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ketentuan Booking - Reys Music Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
  background: linear-gradient(135deg, #fff3cd 0%, #ffec99 50%, #ffd43b 100%);
  min-height: 100vh;
  padding: 20px 0;
}
.card {
  max-width: 650px;
  margin: 30px auto;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  overflow: hidden;
  background-color: #fffdf3;
}
.card-header {
  background: linear-gradient(135deg, #ffb703 0%, #ff9f1c 100%);
  color: white;
  padding: 25px;
  text-align: center;
}
.card-header h3 {
  margin: 0;
  font-weight: 700;
}
.qr-box {
  border: 3px dashed #ffb703;
  height: 200px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  border-radius: 15px;
  background-color: #fffbea;
  color: #e09f00;
  font-size: 1.2rem;
}
.btn-skip {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s;
}
.btn-skip:hover {
  background-color: #5a6268;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.btn-upload {
  background: linear-gradient(135deg, #ffe066 0%, #fab005 100%);
  color: #4b4b4b;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.3s;
}
.btn-upload:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 200, 50, 0.5);
  color: #212529;
}
.booking-info {
  background: linear-gradient(135deg, #fff9db 0%, #fff3bf 100%);
  padding: 20px;
  border-radius: 15px;
  margin-bottom: 25px;
}
.booking-info p {
  margin-bottom: 8px;
}
ol {
  padding-left: 20px;
}
ol li {
  margin-bottom: 10px;
  line-height: 1.6;
}
.info-badge {
  background-color: #ffb703;
  color: #212529;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 700;
}
.alert-warning {
  background-color: #fff3cd;
  border-color: #ffe69c;
  color: #664d03;
}
a.text-secondary:hover {
  color: #ffb703 !important;
}
</style>
</head>
<body>

<div class="card">
  <div class="card-header">
    <i class="bi bi-file-earmark-text fs-1"></i>
    <h3 class="mt-2">Ketentuan Booking</h3>
    <p class="mb-0 small">Harap baca dengan teliti sebelum melanjutkan</p>
  </div>
  
  <div class="card-body p-4">
    <!-- Info Booking -->
    <div class="booking-info">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong><i class="bi bi-receipt"></i> ID Order:</strong>
        <span class="info-badge">#<?= htmlspecialchars($booking['id_order']) ?></span>
      </div>
      <hr class="my-2">
      <p class="mb-1"><i class="bi bi-person-fill"></i> <strong>Nama:</strong> <?= htmlspecialchars($booking['nama'] ?? '-') ?></p>
      <p class="mb-1"><i class="bi bi-calendar-event"></i> <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($booking['tanggal'] ?? 'now')) ?></p>
      <p class="mb-1"><i class="bi bi-clock-fill"></i> <strong>Jam:</strong> <?= htmlspecialchars($booking['jam_booking'] ?? '-') ?></p>
      <p class="mb-0"><i class="bi bi-cash-stack"></i> <strong>Total:</strong> <span class="fw-bold text-danger">Rp <?= number_format((int)($booking['total'] ?? 0), 0, ',', '.') ?></span></p>
    </div>

    <h5 class="mb-3 text-warning"><i class="bi bi-info-circle-fill"></i> Syarat & Ketentuan:</h5>
    <ol>
      <li>DP (Down Payment) minimal <strong class="text-danger">Rp20.000</strong> melalui QRIS untuk mengamankan jadwal.</li>
      <li>Pelunasan dilakukan langsung di studio.</li>
      <li>Waktu booking dihitung mulai dari jam yang sudah disepakati.</li>
      <li>Jika datang terlambat, waktu tetap berjalan sesuai jadwal booking.</li>
      <li>Reschedule (ubah jadwal) hanya bisa dilakukan maksimal H-1 dari jadwal booking.</li>
      <li>Dilarang membawa makanan/minuman ke dalam ruangan studio.</li>
      <li>Bertanggung jawab atas peralatan studio selama penggunaan (apabila ada kerusakan akan dikenakan biaya ganti rugi).</li>
    </ol>

    <div class="alert alert-warning mt-4">
      <i class="bi bi-exclamation-triangle-fill"></i> 
      <strong>Penting!</strong> Silakan scan QR Code untuk melakukan pembayaran DP sebesar <strong>Rp20.000</strong>
    </div>

    <div class="qr-box my-3">
      <div class="text-center">
        <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
        <div class="mt-2">QR CODE QRIS</div>
        <small class="text-muted">Scan untuk membayar DP</small>
      </div>
    </div>

    <p class="text-center fw-semibold mb-4">
      <i class="bi bi-question-circle"></i> Apakah Anda ingin melakukan DP booking?
    </p>

    <div class="d-flex justify-content-between gap-3 mt-4">
      <a href="konfirmasi_booking.php" class="btn btn-skip flex-fill">
        <i class="bi bi-skip-forward"></i> Lewati
      </a>
      <a href="upload_dp.php" class="btn btn-upload flex-fill">
        <i class="bi bi-cloud-upload"></i> Upload Bukti DP
      </a>
    </div>

    <div class="text-center mt-4">
      <a href="index.php" class="text-secondary text-decoration-none">
        <i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
