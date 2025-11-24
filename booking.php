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

// 🔥 AMBIL JAM YANG SUDAH DIBOOKING UNTUK TANGGAL TERTENTU
$bookedSlots = [];
if (isset($_GET['check_date']) && !empty($_GET['check_date'])) {
    $check_date = $_GET['check_date'];
    
    // Query untuk mengambil semua jam yang sudah dibooking (termasuk yang belum expired)
    $query = $koneksi->prepare("
        SELECT jam_booking, paket, expired_at 
        FROM booking 
        WHERE id_studio = ? 
        AND Tanggal = ? 
        AND status NOT IN ('dibatalkan', 'selesai')
        AND (expired_at IS NULL OR expired_at > NOW())
    ");
    $query->bind_param("ss", $id_studio, $check_date);
    $query->execute();
    $result = $query->get_result();
    
   while ($row = $result->fetch_assoc()) {
    // Hilangkan spasi agar format sama dengan front-end: 14.00-15.00
    $jamNormalized = str_replace([' ', ' - '], ['','-'], $row['jam_booking']);
    
    $bookedSlots[] = [
        'jam' => $jamNormalized,
        'paket' => $row['paket']
    ];
}
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
  transition: all 0.3s;
}
.time-slot.active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}
/* 🔥 STYLE UNTUK JAM YANG SUDAH DIBOOKING */
.time-slot.booked {
  background-color: #e0e0e0;
  color: #888;
  border-color: #999;
  cursor: not-allowed;
  position: relative;
}
.time-slot.booked::after {
  content: "DIBOOKING";
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #dc3545;
  color: white;
  font-size: 9px;
  padding: 2px 5px;
  border-radius: 3px;
  font-weight: bold;
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
// 🔥 DATA JAM YANG SUDAH DIBOOKING DARI PHP
const bookedSlots = <?= json_encode($bookedSlots) ?>;

function isSlotBooked(jamStart, jamEnd) {
 const jamBooking = (jamStart + '-' + jamEnd).replace(/\s/g, '');
return bookedSlots.some(slot => slot.jam === jamBooking);
}

function getDurasiPaket() {
  const paket = document.querySelector("input[name='paket']:checked");
  if (!paket) return 0;
  const val = paket.value;
  if (val.includes('2 jam') || val.includes('2jam')) return 2;
  if (val.includes('3 jam') || val.includes('3jam')) return 3;
  return 1;
}

function toggleSlot(el) {
  // 🔥 CEK APAKAH SLOT SUDAH DIBOOKING
  if (el.classList.contains('booked')) {
    return; // Tidak bisa diklik
  }

  const durasi = getDurasiPaket();
  const slots = Array.from(document.querySelectorAll('.time-slot'));
  
  if (durasi === 1) {
    el.classList.toggle('active');
  } else {
    slots.forEach(s => s.classList.remove('active'));
    const index = slots.indexOf(el);
    
    // 🔥 CEK APAKAH SLOT BERIKUTNYA ADA YANG SUDAH DIBOOKING
    let canBook = true;
    for (let i = index; i < index + durasi && i < slots.length; i++) {
      if (slots[i].classList.contains('booked')) {
        canBook = false;
        break;
      }
    }
    
    if (!canBook) {
      alert('❌ Tidak bisa memilih slot ini karena ada jam yang sudah dibooking!');
      return;
    }
    
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
    const val = paket.value;
    if (studio === "bronze") {
      total = (val.includes('Tanpa') || val.includes('35') ? 35000 : 40000) * durasi;
    } else if (studio === "gold") {
      if (val.includes('Reguler') || val.includes('50')) total = 50000 * durasi;
      else if (val.includes('2 jam') || val.includes('90')) total = 90000;
      else if (val.includes('3 jam') || val.includes('130')) total = 130000;
    }
  }

  document.getElementById("total_harga").value = "Rp " + total.toLocaleString("id-ID");
  document.getElementById("total_tagihan").value = total;
}

// 🔥 RELOAD JAM SAAT TANGGAL BERUBAH
function reloadSlots() {
  const tanggal = document.querySelector("input[name='tanggal']").value;
  if (tanggal) {
    const studio = new URLSearchParams(window.location.search).get('studio');
    window.location.href = `?studio=${studio}&check_date=${tanggal}`;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  // 🔥 TANDAI SLOT YANG SUDAH DIBOOKING
  document.querySelectorAll(".time-slot").forEach(slot => {
    const jamStart = slot.dataset.start;
    const jamEnd = slot.dataset.end;
    if (isSlotBooked(jamStart, jamEnd)) {
      slot.classList.add('booked');
    }
  });

  document.querySelectorAll("input[name='paket']").forEach(p => {
    p.addEventListener("change", () => {
      document.querySelectorAll(".time-slot").forEach(s => {
        if (!s.classList.contains('booked')) {
          s.classList.remove("active");
        }
      });
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
            <input type="radio" name="paket" value="Tanpa Keyboard (35K/jam)" required> Tanpa Keyboard (35K/jam)<br>
            <input type="radio" name="paket" value="Dengan Keyboard (40K/jam)"> Dengan Keyboard (40K/jam)
          <?php else: ?>
            <input type="radio" name="paket" value="Reguler (50K/jam)" required> Reguler (50K/jam)<br>
            <input type="radio" name="paket" value="Paket 2 jam (90K)"> Paket 2 jam (90K)<br>
            <input type="radio" name="paket" value="Paket 3 jam (130K)"> Paket 3 jam (130K)
          <?php endif; ?>
        </div>

        <!-- Tanggal -->
        <div class="mb-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" 
                 value="<?= isset($_GET['check_date']) ? htmlspecialchars($_GET['check_date']) : '' ?>"
                 onchange="reloadSlots()" required>
        </div>

        <!-- Jam -->
        <div class="mb-3">
          <label class="form-label">Pilih Jam</label><br>
          <small class="text-muted">Slot abu-abu sudah dibooking oleh user lain</small><br>
          <div id="time-slots">
            <?php
              $slots = [
                ["10.00","11.00"],["11.00","12.00"],["12.00","13.00"],["13.00","14.00"],
                ["14.00","15.00"],["15.00","16.00"],["16.00","17.00"],["17.00","18.00"],
                ["18.00","19.00"],["19.00","20.00"],["20.00","21.00"],["21.00","22.00"]
              ];
              foreach ($slots as $s) {
                echo "<div class='time-slot' 
        data-start='{$s[0]}' 
        data-end='{$s[1]}' 
        data-jam='" . str_replace(' ', '', $s[0] . '-' . $s[1]) . "'
        onclick='toggleSlot(this)'>
      {$s[0]} - {$s[1]}
      </div>";
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