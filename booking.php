<?php
session_start();
require "config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$studio = isset($_GET['studio']) ? $_GET['studio'] : '';

$namaLengkap = 'Guest';
$userId = $_SESSION['user_id'];
$result = $koneksi->query("SELECT nama_lengkap FROM user WHERE id_user = '$userId' LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $namaLengkap = $row['nama_lengkap'];
}

// 🔧 Tentukan id_studio sesuai database
$id_studio = '';
if ($studio === 'bronze') {
    $id_studio = 'ST001';
} elseif ($studio === 'gold') {
    $id_studio = 'ST002';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Booking</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.time-slot {
  display: inline-block;
  margin: 4px;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  cursor: pointer;
  user-select: none;
}
.time-slot.active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}
.card.bronze {
  background-color: #b46b2b;
  color: #fff;
}
.card.gold {
  background-color: #ffda00;
  color: #000;
}
.card.bronze .form-control,
.card.gold .form-control {
  background-color: #fff;
  color: #000;
}
.btn-back {
  background-color: #6c757d;
  color: white;
  margin-top: 10px;
}
.btn-back:hover {
  background-color: #5a6268;
  color: white;
}
</style>

<script>
function getDurasiPaket() {
  const paket = document.querySelector("input[name='paket']:checked");
  if (!paket) return 0;
  if (paket.value === "paket2") return 2;
  if (paket.value === "paket3") return 3;
  return 1;
}

function toggleSlot(el) {
  const durasi = getDurasiPaket();
  const slots = Array.from(document.querySelectorAll('.time-slot'));
  
  if (durasi === 1) {
    el.classList.toggle('active');
  } else {
    slots.forEach(s => s.classList.remove('active'));
    const index = slots.indexOf(el);
    for (let i = index; i < index + durasi && i < slots.length; i++) {
      slots[i].classList.add('active');
    }
  }
  updateDurasiDanJam();
}

function updateDurasiDanJam() {
  const selected = Array.from(document.querySelectorAll('.time-slot.active'));
  const durasi = selected.length;
  document.getElementById('durasi').value = durasi;

  if (durasi > 0) {
    const jamAwal = selected[0].dataset.start;
    const jamAkhir = selected[selected.length - 1].dataset.end;
    document.getElementById('jam_mulai').value = jamAwal;
    document.getElementById('jam_selesai').value = jamAkhir;
    document.getElementById('jam_booking').value = jamAwal + '-' + jamAkhir;
  } else {
    document.getElementById('jam_mulai').value = "";
    document.getElementById('jam_selesai').value = "";
    document.getElementById('jam_booking').value = "";
  }

  updateBooking();
}

function updateBooking() {
  const durasi = parseInt(document.getElementById("durasi").value) || 0;
  const paket = document.querySelector("input[name='paket']:checked");
  const studio = document.getElementById("studio_nama").value;
  let total = 0;

  if (paket) {
    if (studio === "bronze") {
      total = (paket.value === "without" ? 35000 : 40000) * durasi;
    } else if (studio === "gold") {
      if (paket.value === "reguler") total = 50000 * durasi;
      else if (paket.value === "paket2") total = 90000;
      else if (paket.value === "paket3") total = 130000;
    }
  }

  document.getElementById("total_harga").value = "Rp " + total.toLocaleString("id-ID");
  document.getElementById("total_tagihan").value = total;
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("input[name='paket']").forEach(p => {
    p.addEventListener("change", () => {
      document.querySelectorAll(".time-slot").forEach(s => s.classList.remove("active"));
      document.getElementById("durasi").value = "";
      document.getElementById("jam_mulai").value = "";
      document.getElementById("jam_selesai").value = "";
      document.getElementById("total_harga").value = "";
      document.getElementById("jam_booking").value = "";
      updateBooking();
    });
  });
});
</script>
</head>

<body class="bg-light">
<div class="container py-5">
  <div class="card shadow p-4 <?= $studio == 'bronze' ? 'bronze' : ($studio == 'gold' ? 'gold' : '') ?>">
    <?php if ($studio == "bronze" || $studio == "gold"): ?>
      <h3>Booking Studio <?= ucfirst($studio) ?></h3>
      <p class="mb-3">Pilih jadwal dan lengkapi detail Anda.</p>

      <form method="post" action="proses_booking.php" onsubmit="updateBooking()">
        <!-- ✅ TAMBAHKAN id_user -->
        <input type="hidden" name="id_user" value="<?= htmlspecialchars($userId) ?>">
        <input type="hidden" name="id_studio" id="id_studio" value="<?= htmlspecialchars($id_studio) ?>">
        <input type="hidden" name="studio_nama" id="studio_nama" value="<?= htmlspecialchars($studio) ?>">
        <input type="hidden" name="jam_booking" id="jam_booking">
        <input type="hidden" name="total_tagihan" id="total_tagihan">

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

        <!-- Jam -->
        <div class="mb-3">
          <label class="form-label">Pilih Jam</label><br>
          <div id="time-slots">
            <?php
              $slots = [
                ["10.00","11.00"],["11.00","12.00"],["12.00","13.00"],["13.00","14.00"],
                ["14.00","15.00"],["15.00","16.00"],["16.00","17.00"],["17.00","18.00"],
                ["18.00","19.00"],["19.00","20.00"],["20.00","21.00"],["21.00","22.00"]
              ];
              foreach ($slots as $s) {
                echo "<div class='time-slot' data-start='{$s[0]}' data-end='{$s[1]}' onclick='toggleSlot(this)'>{$s[0]} - {$s[1]}</div>";
              }
            ?>
          </div>
        </div>

        <!-- Durasi dan jam -->
        <div class="row mb-3">
          <div class="col-md-2">
            <label class="form-label">Durasi (jam)</label>
            <input type="number" id="durasi" name="durasi" class="form-control" readonly required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Jam Mulai</label>
            <input type="text" id="jam_mulai" name="jam_mulai" class="form-control" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Jam Selesai</label>
            <input type="text" id="jam_selesai" name="jam_selesai" class="form-control" readonly>
          </div>
        </div>

        <!-- Total harga -->
        <div class="mb-3">
          <label class="form-label">Total Harga</label>
          <input type="text" id="total_harga" class="form-control" readonly>
        </div>

        <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
        <a href="index.php" class="btn btn-back w-100">Kembali ke Halaman Utama</a>
      </form>
    <?php else: ?>
      <p class="text-danger">Studio tidak ditemukan.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>