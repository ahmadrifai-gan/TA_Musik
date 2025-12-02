<?php
// booking.php
session_start();
require "config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$studio = isset($_GET['studio']) ? $_GET['studio'] : ''; // 'bronze' atau 'gold'
$userId = $_SESSION['user_id'];
$namaLengkap = 'Guest';
$resUser = $koneksi->prepare("SELECT nama_lengkap FROM user WHERE id_user = ? LIMIT 1");
$resUser->bind_param("s", $userId);
$resUser->execute();
$ru = $resUser->get_result();
if ($ru && $rur = $ru->fetch_assoc()) $namaLengkap = $rur['nama_lengkap'];

// mapping studio ke id_studio (sesuaikan dengan DB-mu)
$id_studio = '';
if ($studio === 'bronze') $id_studio = 'ST001';
elseif ($studio === 'gold') $id_studio = 'ST002';

// bila ada tanggal yang dipilih, ambil booked slots untuk tanggal itu
$check_date = isset($_GET['check_date']) ? $_GET['check_date'] : '';
$bookedSlots = [];

if ($check_date && $id_studio) {
    // 🔥 DEBUG: Cek dulu apa ada data untuk tanggal ini
    $debug_query = "
        SELECT id_order, Tanggal, jam_booking, paket, status, expired_at
        FROM booking
        WHERE id_studio = '$id_studio'
          AND DATE(Tanggal) = '$check_date'
    ";
    error_log("=== DEBUG QUERY ===");
    error_log("Studio: $id_studio");
    error_log("Check Date: $check_date");
    error_log("Query: $debug_query");
    
    $debug_result = $koneksi->query($debug_query);
    if ($debug_result) {
        error_log("Total rows found: " . $debug_result->num_rows);
        while ($debug_row = $debug_result->fetch_assoc()) {
            error_log("Found booking: ID={$debug_row['id_order']}, Date={$debug_row['Tanggal']}, Jam={$debug_row['jam_booking']}, Status={$debug_row['status']}");
        }
    }
    
    // 🔥 Query tanpa filter expired_at untuk development/testing
    // Untuk production, tambahkan kembali: AND (expired_at IS NULL OR expired_at > NOW())
    $q = $koneksi->prepare("
        SELECT jam_booking, paket, status, expired_at
        FROM booking
        WHERE id_studio = ?
          AND DATE(Tanggal) = ?
          AND status NOT IN ('dibatalkan','selesai')
    ");
    $q->bind_param("ss", $id_studio, $check_date);
    $q->execute();
    $gr = $q->get_result();
    
    error_log("=== FILTERED RESULTS ===");
    error_log("Rows after filter: " . $gr->num_rows);
    
    while ($row = $gr->fetch_assoc()) {
        // 🔥 Normalisasi: hilangkan SEMUA spasi, tab, dan ganti : jadi .
        $jamRaw = $row['jam_booking'];
        $jamNormalized = str_replace([' ', "\t", "\r", "\n", ':'], ['', '', '', '', '.'], $jamRaw);
        $bookedSlots[] = [
            'jam' => $jamNormalized,
            'paket' => $row['paket'],
            'status' => $row['status'],
            'original' => $jamRaw // untuk debugging
        ];
        error_log("Added to bookedSlots: {$jamRaw} -> {$jamNormalized} ({$row['paket']})");
    }
    
    error_log("=== FINAL BOOKED SLOTS ===");
    error_log("Total booked slots: " . count($bookedSlots));
    error_log(print_r($bookedSlots, true));
}

// daftar slot yang ditampilkan (sesuaikan bila mau ubah)
$slots = [
    ["10.00","11.00"],["11.00","12.00"],["12.00","13.00"],["13.00","14.00"],
    ["14.00","15.00"],["15.00","16.00"],["16.00","17.00"],["17.00","18.00"],
    ["18.00","19.00"],["19.00","20.00"],["20.00","21.00"],["21.00","22.00"]
];

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Booking Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.time-slot { display:inline-block; margin:4px; padding:8px 12px; border:1px solid #ccc; border-radius:8px; cursor:pointer; user-select:none; transition:all .2s; }
.time-slot.booked { background:#e0e0e0; color:#777; border-color:#999; cursor:not-allowed; position:relative; }
.time-slot.booked::after{ content:"DIBOOKING"; position:absolute; top:-8px; right:-8px; background:#dc3545; color:#fff; font-size:9px; padding:2px 5px; border-radius:3px; font-weight:bold;}
.time-slot.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
.card.bronze { background:#b46b2b; color:#fff; }
.card.gold { background:#ffda00; color:#000; }
.small-muted { font-size: .9rem; color: #6c757d; }
</style>
<script>
const bookedSlots = <?= json_encode($bookedSlots) ?>;
const studioParam = '<?= $studio ?>';
const checkDate = '<?= $check_date ?>';

console.log('\n========================================');
console.log('🏠 BOOKING SYSTEM INITIALIZED');
console.log('========================================');
console.log('📅 Check Date:', checkDate || 'Not selected');
console.log('🎵 Studio:', studioParam);
console.log('📊 Booked Slots Count:', bookedSlots.length);
console.log('\n📋 Booked Slots Detail:');
bookedSlots.forEach((slot, idx) => {
    console.log(`  ${idx + 1}. ${slot.jam} (${slot.paket})` + (slot.original ? ` [Original: ${slot.original}]` : ''));
});
console.log('========================================\n');

// 🔥 FUNGSI UNTUK CONVERT JAM KE MENIT
function jamToMinutes(jamStr) {
    const cleaned = jamStr.replace(/[:.]/g, '');
    const hours = parseInt(cleaned.substring(0, 2));
    const minutes = parseInt(cleaned.substring(2, 4) || '0');
    return hours * 60 + minutes;
}

// 🔥 FUNGSI UNTUK NORMALISASI JAM (hilangkan semua spasi dan karakter aneh)
function normalizeJam(jamStr) {
    if (!jamStr) return '';
    // Hilangkan semua spasi, tab, dan karakter whitespace
    return jamStr.replace(/\s+/g, '').replace(/:/g, '.').trim();
}

// 🔥 FUNGSI UNTUK PARSE JAM RANGE
function parseJamRange(jamStr) {
    if (!jamStr) return null;
    jamStr = normalizeJam(jamStr);
    const parts = jamStr.split('-');
    if (parts.length !== 2) return null;
    return {
        start: parts[0].trim(),
        end: parts[1].trim()
    };
}

// 🔥 FUNGSI UNTUK CEK OVERLAP
function isTimeInRange(slotStart, slotEnd, bookingJam) {
    const bookingRange = parseJamRange(bookingJam);
    if (!bookingRange) return false;
    
    const slotStartMin = jamToMinutes(slotStart);
    const slotEndMin = jamToMinutes(slotEnd);
    const bookStartMin = jamToMinutes(bookingRange.start);
    const bookEndMin = jamToMinutes(bookingRange.end);
    
    // Cek apakah ada overlap: slot start berada dalam range booking
    // ATAU booking start berada dalam range slot
    return (slotStartMin >= bookStartMin && slotStartMin < bookEndMin) ||
           (bookStartMin >= slotStartMin && bookStartMin < slotEndMin);
}

// 🔥 FUNGSI UNTUK CEK APAKAH SLOT SUDAH DIBOOKING
function isSlotBooked(start, end) {
    const normalizedSlot = normalizeJam(start + '-' + end);
    
    console.log(`\n=== Checking slot: ${start}-${end} (normalized: ${normalizedSlot}) ===`);
    
    for (let booking of bookedSlots) {
        const normalizedBooking = normalizeJam(booking.jam);
        console.log(`  Against: ${booking.jam} -> ${normalizedBooking} (${booking.paket})`);
        
        // Exact match dengan normalisasi
        if (normalizedBooking === normalizedSlot) {
            console.log(`  ✅✅✅ EXACT MATCH! Slot DIBOOKING!`);
            return true;
        }
        
        // Overlap check (untuk paket multi-jam)
        if (isTimeInRange(start, end, booking.jam)) {
            console.log(`  ✅✅✅ OVERLAP DETECTED! Slot DIBOOKING!`);
            return true;
        }
    }
    
    console.log(`  ✔ Slot TERSEDIA`);
    return false;
}

function getDurasiPaket(){
  const p = document.querySelector("input[name='paket']:checked");
  if(!p) return 0;
  const v = p.value.toLowerCase();
  if(v.includes('2 jam') || v.includes('2jam') || v.includes('90')) return 2;
  if(v.includes('3 jam') || v.includes('3jam') || v.includes('130')) return 3;
  return 1;
}

function toggleSlot(el){
  if(el.classList.contains('booked')) {
    alert('❌ Slot ini sudah dibooking oleh user lain! Silakan pilih slot lain.');
    return;
  }
  
  const dur = getDurasiPaket();
  if(dur === 0) {
    alert('⚠️ Silakan pilih paket terlebih dahulu!');
    return;
  }
  
  const slots = Array.from(document.querySelectorAll('.time-slot'));
  
  if(dur <= 1){
    // Single slot
    el.classList.toggle('active');
  } else {
    // Multi-jam booking
    slots.forEach(s=>s.classList.remove('active'));
    const idx = slots.indexOf(el);
    if(idx === -1) return;
    
    // Cek apakah ada cukup slot tersisa
    if(idx + dur > slots.length) {
      alert(`⚠️ Tidak cukup slot untuk paket ${dur} jam dari waktu yang dipilih.`);
      return;
    }
    
    // 🔥 CEK APAKAH SEMUA SLOT DALAM RANGE BEBAS
    for(let i = idx; i < idx + dur && i < slots.length; i++){
      if(slots[i].classList.contains('booked')){
        alert(`❌ Tidak bisa memilih karena slot ${slots[i].textContent.trim()} sudah dibooking.\n\nUntuk paket ${dur} jam, semua slot harus tersedia.`);
        return;
      }
    }
    
    // Tandai semua slot sebagai aktif
    for(let i = idx; i < idx + dur && i < slots.length; i++) {
      slots[i].classList.add('active');
    }
  }
  
  updateHiddenFields();
}

function updateHiddenFields(){
  const selected = Array.from(document.querySelectorAll('.time-slot.active'));
  const dur = selected.length;
  document.getElementById('durasi').value = dur;
  
  if(dur > 0){
    const start = selected[0].dataset.start;
    const end = selected[selected.length-1].dataset.end;
    document.getElementById('jam_mulai').value = start;
    document.getElementById('jam_selesai').value = end;
    document.getElementById('jam_booking').value = start + '-' + end;
  } else {
    document.getElementById('jam_mulai').value = '';
    document.getElementById('jam_selesai').value = '';
    document.getElementById('jam_booking').value = '';
  }
  updateTotal();
}

function updateTotal(){
  const paket = document.querySelector("input[name='paket']:checked");
  const studio = document.getElementById('studio_nama').value;
  const dur = parseInt(document.getElementById('durasi').value) || 0;
  let total = 0;
  
  if(paket){
    const v = paket.value;
    if(studio === 'bronze'){
      // Bronze: Tanpa Keyboard (35K/jam) atau Dengan Keyboard (40K/jam)
      total = v.includes('35') ? 35000 * dur : 40000 * dur;
    } else if(studio === 'gold'){
      // Gold: cek apakah paket khusus atau reguler
      if(v.includes('50')) {
        // Reguler 50K/jam
        total = 50000 * dur;
      } else if(v.includes('90')) {
        // Paket 2 jam = 90K (fixed price)
        total = 90000;
      } else if(v.includes('130')) {
        // Paket 3 jam = 130K (fixed price)
        total = 130000;
      }
    }
  }
  
  document.getElementById('total_harga').value = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : '';
  document.getElementById('total_tagihan').value = total;
}

function reloadSlots(){
  const tanggal = document.getElementById('tanggal').value;
  if(!tanggal) {
    alert('Silakan pilih tanggal terlebih dahulu!');
    return;
  }
  
  // 🔥 VALIDASI: Tanggal tidak boleh sebelum hari ini
  const selectedDate = new Date(tanggal);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (selectedDate < today) {
    alert('⚠️ Tidak dapat melakukan booking untuk tanggal yang sudah lewat!\nSilakan pilih tanggal hari ini atau setelahnya.');
    document.getElementById('tanggal').value = '';
    return;
  }
  
  const studio = document.getElementById('studio_nama').value;
  // reload page dengan param check_date supaya PHP mengambil booked slots
  window.location.href = `?studio=${encodeURIComponent(studio)}&check_date=${encodeURIComponent(tanggal)}`;
}

document.addEventListener('DOMContentLoaded', function(){
  console.log('\n========== INITIALIZATION ==========');
  console.log('Booked Slots from Server:', bookedSlots);
  
  // 🔥 TANDAI SLOT YANG SUDAH DIBOOKING
  let markedCount = 0;
  document.querySelectorAll('.time-slot').forEach(slot => {
    const start = slot.dataset.start;
    const end = slot.dataset.end;
    
    if(isSlotBooked(start, end)){
      slot.classList.add('booked');
      slot.title = 'Sudah dibooking oleh user lain';
      markedCount++;
      console.log(`✅ Marked as BOOKED: ${start}-${end}`);
    }
  });
  
  console.log(`\n📊 Total slots marked as booked: ${markedCount}`);
  console.log('====================================\n');
  
  // Reset jam ketika ganti paket
  document.querySelectorAll("input[name='paket']").forEach(p => {
    p.addEventListener('change', () => {
      // Clear selected slots
      document.querySelectorAll('.time-slot').forEach(s => { 
        if(!s.classList.contains('booked')) {
          s.classList.remove('active'); 
        }
      });
      
      // Clear form values
      document.getElementById('durasi').value = '';
      document.getElementById('jam_mulai').value = '';
      document.getElementById('jam_selesai').value = '';
      document.getElementById('jam_booking').value = '';
      document.getElementById('total_harga').value = '';
      document.getElementById('total_tagihan').value = '';
    });
  });
});
</script>
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow p-4 <?= ($studio=='bronze') ? 'bronze' : (($studio=='gold')? 'gold':'') ?>">
    <?php if($studio=='bronze' || $studio=='gold'): ?>
      <h3>Booking Studio <?= htmlspecialchars(ucfirst($studio)) ?></h3>
      <p>Pilih jadwal & paket. Slot berwarna abu-abu = sudah dibooking.</p>
      
      <form method="post" action="proses_booking.php" onsubmit="return validateForm()">
        <input type="hidden" name="id_user" value="<?= htmlspecialchars($userId) ?>">
        <input type="hidden" name="id_studio" id="id_studio" value="<?= htmlspecialchars($id_studio) ?>">
        <input type="hidden" name="studio_nama" id="studio_nama" value="<?= htmlspecialchars($studio) ?>">
        <input type="hidden" name="jam_booking" id="jam_booking">
        <input type="hidden" name="jam_mulai" id="jam_mulai">
        <input type="hidden" name="jam_selesai" id="jam_selesai">
        <input type="hidden" name="durasi" id="durasi">
        <input type="hidden" name="total_tagihan" id="total_tagihan">

        <div class="mb-3">
          <label class="form-label">Nama Pemesan</label>
          <input class="form-control" value="<?= htmlspecialchars($namaLengkap) ?>" readonly>
          <input type="hidden" name="nama" value="<?= htmlspecialchars($namaLengkap) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Paket</label><br>
          <?php if($studio=='bronze'): ?>
            <input type="radio" name="paket" value="Tanpa Keyboard (35K/jam)" required> Tanpa Keyboard (35K/jam)<br>
            <input type="radio" name="paket" value="Dengan Keyboard (40K/jam)"> Dengan Keyboard (40K/jam)
          <?php else: ?>
            <input type="radio" name="paket" value="Reguler (50K/jam)" required> Reguler (50K/jam)<br>
            <input type="radio" name="paket" value="Paket 2 jam (90K)"> Paket 2 jam (90K)<br>
            <input type="radio" name="paket" value="Paket 3 jam (130K)"> Paket 3 jam (130K)
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Tanggal</label>
          <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= htmlspecialchars($check_date) ?>" min="<?= date('Y-m-d') ?>" onchange="reloadSlots()" required>
          <small class="text-muted">📅 Booking hanya dapat dilakukan untuk hari ini atau setelahnya</small>
        </div>

        <div class="mb-3">
          <label class="form-label">Pilih Jam</label><br>
          <small class="small-muted">⚠️ Slot abu-abu sudah dibooking oleh user lain. Untuk paket multi-jam, semua slot harus tersedia.</small>
          <?php if($check_date && count($bookedSlots) > 0): ?>
            <br><small class="text-danger">🔴 <strong><?= count($bookedSlots) ?> slot</strong> sudah dibooking untuk tanggal ini</small>
          <?php endif; ?>
          <br><br>
          <div id="time-slots">
            <?php foreach($slots as $s): ?>
              <div class="time-slot" data-start="<?= $s[0] ?>" data-end="<?= $s[1] ?>" onclick="toggleSlot(this)">
                <?= $s[0] ?> - <?= $s[1] ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mb-3 row">
          <div class="col-md-3">
            <label class="form-label">Total Harga</label>
            <input type="text" id="total_harga" class="form-control" readonly>
          </div>
        </div>

        <button class="btn btn-primary w-100" type="submit">Lanjutkan</button>
        <a class="btn btn-secondary w-100 mt-2" href="index.php">Kembali</a>
      </form>
    <?php else: ?>
      <p class="text-danger">Studio tidak ditemukan. Gunakan parameter ?studio=bronze atau ?studio=gold</p>
    <?php endif; ?>
  </div>
</div>

<script>
function validateForm() {
  const paket = document.querySelector("input[name='paket']:checked");
  if (!paket) {
    alert('⚠️ Silakan pilih paket terlebih dahulu!');
    return false;
  }
  
  const tanggal = document.getElementById('tanggal').value;
  if (!tanggal) {
    alert('⚠️ Silakan pilih tanggal!');
    return false;
  }
  
  // 🔥 VALIDASI: Tanggal tidak boleh sebelum hari ini
  const selectedDate = new Date(tanggal);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (selectedDate < today) {
    alert('⚠️ Tidak dapat melakukan booking untuk tanggal yang sudah lewat!\nSilakan pilih tanggal hari ini atau setelahnya.');
    return false;
  }
  
  const jamBooking = document.getElementById('jam_booking').value;
  if (!jamBooking) {
    alert('⚠️ Silakan pilih jam booking!');
    return false;
  }
  
  const durasi = parseInt(document.getElementById('durasi').value);
  const paketDurasi = getDurasiPaket();
  
  if (durasi !== paketDurasi) {
    alert(`⚠️ Pilihan jam tidak sesuai dengan paket!\nPaket membutuhkan ${paketDurasi} jam, tetapi Anda hanya memilih ${durasi} jam.`);
    return false;
  }
  
  const total = document.getElementById('total_tagihan').value;
  if (!total || total == '0') {
    alert('⚠️ Total harga tidak valid!');
    return false;
  }
  
  return true;
}
</script>
</body>
</html>