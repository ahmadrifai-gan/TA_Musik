<?php
session_start();
$studio = isset($_GET['studio']) ? $_GET['studio'] : '';

$host="localhost"; 
$user="root"; 
$pass=""; 
$db="db_login";
$conn = new mysqli($host, $user, $pass, $db);

$namaLengkap = 'Guest';
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $result = $conn->query("SELECT nama_lengkap FROM users WHERE id = '$userId' LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $namaLengkap = $row['nama_lengkap'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  function updateBooking() {
    let durasi = parseInt(document.getElementById("durasi").value) || 0;
    let jamMulai = document.getElementById("jam").value;
    let paket = document.querySelector("input[name='paket']:checked");

    // Hitung jam selesai
    if (jamMulai && durasi > 0) {
      let [h, m] = jamMulai.split(":").map(Number);
      let endDate = new Date();
      endDate.setHours(h);
      endDate.setMinutes(m);
      endDate.setHours(endDate.getHours() + durasi);

      let hh = String(endDate.getHours()).padStart(2, '0');
      let mm = String(endDate.getMinutes()).padStart(2, '0');
      document.getElementById("jam_selesai").value = `${hh}:${mm}`;
    } else {
      document.getElementById("jam_selesai").value = "";
    }

    // Hitung harga otomatis
    let total = 0;
    if (paket) {
      let studio = document.getElementById("studio").value;
      if (studio === "bronze") {
        total = (paket.value === "without" ? 35000 : 40000) * durasi;
      } else if (studio === "gold") {
        if (paket.value === "reguler") total = 50000 * durasi;
        else if (paket.value === "paket2") {
          total = 90000; document.getElementById("durasi").value = 2;
        } else if (paket.value === "paket3") {
          total = 130000; document.getElementById("durasi").value = 3;
        }
      }
    }

    document.getElementById("total_harga").value = "Rp " + total.toLocaleString("id-ID");
    document.getElementById("total").value = total; // hidden utk dikirim form
  }

  // ⬇️ Jalankan updateBooking() otomatis saat user ubah input
  document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("durasi").addEventListener("input", updateBooking);
    document.getElementById("jam").addEventListener("input", updateBooking);
    document.querySelectorAll("input[name='paket']").forEach(el => {
      el.addEventListener("change", updateBooking);
    });
  });
</script>

</head>
<body class="bg-light">

<div class="container py-5">
  <div class="card shadow p-4">
    <?php if ($studio == "bronze" || $studio == "gold"): ?>
      <h3>Booking Studio <?= ucfirst($studio) ?></h3>
      <form method="post" action="konfirmasi_booking.php" onsubmit="updateBooking()">
        <input type="hidden" id="studio" name="studio" value="<?= htmlspecialchars($studio) ?>">

        <!-- Nama -->
        <div class="mb-3">
          <label class="form-label">Nama Pemesan</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($namaLengkap) ?>" readonly>
          <input type="hidden" name="nama" value="<?= htmlspecialchars($namaLengkap) ?>">
        </div>

        <!-- Paket -->
        <div class="mb-3">
          <label class="form-label">Paket</label><br>
          <?php if ($studio == "bronze"): ?>
            <input type="radio" name="paket" value="without" required> Tanpa Keyboard (35K/jam)<br>
            <input type="radio" name="paket" value="with"> Dengan Keyboard (40K/jam)
          <?php else: ?>
            <input type="radio" name="paket" value="reguler" required> Reguler (50K/jam)<br>
            <input type="radio" name="paket" value="paket2"> Paket 2 jam (90K)<br>
            <input type="radio" name="paket" value="paket3"> Paket 3 jam (130K)
          <?php endif; ?>
        </div>

        <!-- Tanggal -->
        <div class="mb-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>

        <!-- Durasi + Jam mulai + Jam selesai -->
        <div class="row mb-3">
          <div class="col-md-2">
            <label class="form-label">Durasi (jam)</label>
            <input type="number" id="durasi" name="durasi" class="form-control" min="1" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Jam Mulai</label>
            <input type="time" id="jam" name="jam" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Jam Selesai</label>
            <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" readonly>
          </div>
        </div>

        <!-- Total Harga -->
        <div class="mb-3">
          <label class="form-label">Total Harga</label>
          <input type="text" id="total_harga" class="form-control" readonly>
          <input type="hidden" id="total" name="total">
        </div>

        <button type="submit" class="btn btn-primary">Lanjutkan</button>
      </form>

    <?php else: ?>
      <p class="text-danger">Studio tidak ditemukan.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
