<?php
session_start();
require "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Harap login terlebih dahulu sebelum melakukan booking.");
}

// Ambil data dari form
$id_user = $_SESSION['user_id'];
$id_studio = trim($_POST['id_studio'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$jam_mulai = trim($_POST['jam_mulai'] ?? '');
$jam_selesai = trim($_POST['jam_selesai'] ?? '');
$jam_booking = trim($_POST['jam_booking'] ?? '');
$total_tagihan = trim($_POST['total_tagihan'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$durasi = (int)($_POST['durasi'] ?? 0);
$paket = trim($_POST['paket'] ?? '');

// Validasi input dasar
if (empty($id_studio) || empty($tanggal) || empty($jam_booking) || empty($paket)) {
    echo "<script>
        alert('Data tidak lengkap. Mohon isi semua field yang diperlukan.');
        window.history.back();
    </script>";
    exit;
}

// Format jam
$jam_mulai = str_replace('.', ':', $jam_mulai);
$jam_selesai = str_replace('.', ':', $jam_selesai);
if (substr_count($jam_mulai, ':') == 1) $jam_mulai .= ':00';
if (substr_count($jam_selesai, ':') == 1) $jam_selesai .= ':00';

// Hapus karakter non-numerik dari total tagihan
$total_tagihan_numeric = preg_replace('/[^0-9]/', '', $total_tagihan);
if ($total_tagihan_numeric === '') $total_tagihan_numeric = 0;

// 🔥 SET EXPIRED TIME (2 JAM DARI SEKARANG)
$expired_at = date('Y-m-d H:i:s', strtotime('+2 hours'));

// Cek apakah tabel jadwal ada
$jadwal_aktif = false;
$id_jadwal = null;
$cek_tabel = $koneksi->query("SHOW TABLES LIKE 'jadwal'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $jadwal_aktif = true;
}

// =================================================================================
// === VALIDASI UTAMA: CEK DUPLIKASI BERDASARKAN STUDIO, TANGGAL, JAM & PAKET ===
// === 🔥 TAMBAHKAN FILTER EXPIRED_AT ===
// =================================================================================
$cekDuplikasi = $koneksi->prepare("
    SELECT id_order, paket 
    FROM booking 
    WHERE id_studio = ? 
      AND Tanggal = ? 
      AND jam_booking = ? 
      AND paket = ?
      AND status NOT IN ('dibatalkan', 'selesai')
      AND (expired_at IS NULL OR expired_at > NOW())
    LIMIT 1
");
$cekDuplikasi->bind_param("ssss", $id_studio, $tanggal, $jam_booking, $paket);
$cekDuplikasi->execute();
$hasilDuplikasi = $cekDuplikasi->get_result();

if ($hasilDuplikasi && $hasilDuplikasi->num_rows > 0) {
    echo "<script>
        alert('❌ BOOKING GAGAL!\\n\\nJadwal ini sudah dibooking oleh user lain:\\n\\n📅 Tanggal: {$tanggal}\\n🕐 Jam: {$jam_booking}\\n📦 Paket: {$paket}\\n\\nSilakan pilih:\\n✓ Jam yang berbeda, ATAU\\n✓ Paket yang berbeda, ATAU\\n✓ Tanggal yang berbeda');
        window.history.back();
    </script>";
    exit;
}

// =================================================================================
// === VALIDASI TAMBAHAN: CEK OVERLAP JAM UNTUK STUDIO DAN TANGGAL YANG SAMA ===
// === 🔥 TAMBAHKAN FILTER EXPIRED_AT ===
// =================================================================================
$jam_parts = explode('-', $jam_booking);
$booking_start = isset($jam_parts[0]) ? trim($jam_parts[0]) : '';
$booking_end = isset($jam_parts[1]) ? trim($jam_parts[1]) : '';

if (!empty($booking_start) && !empty($booking_end)) {
    $booking_start = str_replace('.', ':', $booking_start);
    $booking_end = str_replace('.', ':', $booking_end);
    
    $cekOverlap = $koneksi->prepare("
        SELECT id_order, jam_booking, paket 
        FROM booking 
        WHERE id_studio = ? 
          AND Tanggal = ? 
          AND status NOT IN ('dibatalkan', 'selesai')
          AND (expired_at IS NULL OR expired_at > NOW())
    ");
    $cekOverlap->bind_param("ss", $id_studio, $tanggal);
    $cekOverlap->execute();
    $hasilOverlap = $cekOverlap->get_result();
    
    while ($row = $hasilOverlap->fetch_assoc()) {
        $existing_jam = explode('-', $row['jam_booking']);
        if (count($existing_jam) == 2) {
            $existing_start = str_replace('.', ':', trim($existing_jam[0]));
            $existing_end = str_replace('.', ':', trim($existing_jam[1]));
            
            if (($booking_start < $existing_end) && ($existing_start < $booking_end)) {
                echo "<script>
                    alert('❌ BOOKING GAGAL!\\n\\nJam yang Anda pilih bentrok dengan booking lain:\\n\\n🕐 Jam Existing: {$row['jam_booking']}\\n📦 Paket: {$row['paket']}\\n\\nSilakan pilih jam yang tidak overlap!');
                    window.history.back();
                </script>";
                exit;
            }
        }
    }
}

// =================================================================================
// === VALIDASI JADWAL (JIKA TABEL JADWAL AKTIF) ===
// =================================================================================
if ($jadwal_aktif) {
    $cekJadwal = $koneksi->prepare("
        SELECT j.id_jadwal, b.paket 
        FROM jadwal j 
        LEFT JOIN booking b ON j.id_jadwal = b.id_jadwal
        WHERE j.id_studio = ? 
          AND j.Tanggal = ? 
          AND j.jam_booking = ? 
          AND j.status NOT IN ('dibatalkan', 'selesai')
    ");
    $cekJadwal->bind_param("sss", $id_studio, $tanggal, $jam_booking);
    $cekJadwal->execute();
    $hasilJadwal = $cekJadwal->get_result();

    while ($row = $hasilJadwal->fetch_assoc()) {
        if (!empty($row['paket']) && trim(strtolower($row['paket'])) === trim(strtolower($paket))) {
            echo "<script>
                alert('❌ BOOKING GAGAL!\\n\\nJadwal dengan paket yang sama sudah ada!\\n\\nSilakan pilih jam atau paket yang berbeda.');
                window.history.back();
            </script>";
            exit;
        }
    }
}

// =================================================================================
// === JIKA FITUR JADWAL AKTIF, BUAT / GUNAKAN JADWAL ===
// =================================================================================
if ($jadwal_aktif) {
    $cek_jadwal = $koneksi->prepare("
        SELECT id_jadwal FROM jadwal 
        WHERE id_studio = ? AND Tanggal = ? AND jam_booking = ?
    ");
    $cek_jadwal->bind_param("sss", $id_studio, $tanggal, $jam_booking);
    $cek_jadwal->execute();
    $res_jadwal = $cek_jadwal->get_result();

    if ($res_jadwal && $res_jadwal->num_rows > 0) {
        $row = $res_jadwal->fetch_assoc();
        $id_jadwal = $row['id_jadwal'];
    } else {
        $insert_jadwal = $koneksi->prepare("
            INSERT INTO jadwal (id_studio, Tanggal, jam_mulai, jam_selesai, jam_booking, status)
            VALUES (?, ?, ?, ?, ?, 'dibooking')
        ");
        $insert_jadwal->bind_param("sssss", $id_studio, $tanggal, $jam_mulai, $jam_selesai, $jam_booking);
        $insert_jadwal->execute();
        $id_jadwal = $insert_jadwal->insert_id;
    }
}

if (empty($id_jadwal)) $id_jadwal = null;

// =================================================================================
// === SIMPAN DATA BOOKING DENGAN EXPIRED_AT ===
// =================================================================================
$stmt = $koneksi->prepare("
    INSERT INTO booking 
    (id_user, id_studio, id_jadwal, total_tagihan, status, status_pembayaran, Tanggal, jam_booking, paket, expired_at)
    VALUES (?, ?, ?, ?, 'menunggu', 'belum_dibayar', ?, ?, ?, ?)
");
$stmt->bind_param("isidssss", $id_user, $id_studio, $id_jadwal, $total_tagihan_numeric, $tanggal, $jam_booking, $paket, $expired_at);

if (!$stmt->execute()) {
    echo "<script>
        alert('❌ Gagal menyimpan booking: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
    exit;
}

$id_order = $stmt->insert_id;

require 'config/config_email.php'; // (DITAMBAHKAN)

// Data email yang akan dikirim
$dataEmail = [
    'id_order' => $id_order,
    'nama' => $nama,
    'id_studio' => $id_studio,
    'tanggal' => $tanggal,
    'jam_booking' => $jam_booking,
    'paket' => $paket,
    'total' => $total_tagihan_numeric
];

// Proses kirim email
$emailStatus = sendBookingEmail($dataEmail);

// =================================================================================
// === SIMPAN DATA KE SESSION DAN REDIRECT ===
// =================================================================================
$_SESSION['booking_data'] = [
    'id_order' => $id_order,
    'id_user' => $id_user,
    'id_studio' => $id_studio,
    'id_jadwal' => $id_jadwal,
    'tanggal' => $tanggal,
    'jam_mulai' => $jam_mulai,
    'jam_selesai' => $jam_selesai,
    'jam_booking' => $jam_booking,
    'total' => $total_tagihan_numeric,
    'nama' => $nama,
    'durasi' => $durasi,
    'paket' => $paket,
    'status_pembayaran' => 'belum_dibayar',
    'expired_at' => $expired_at
];

echo "<script>
    alert('✅ Booking berhasil dibuat!\\n\\nID Order: {$id_order}\\n\\n⏰ Booking akan expire dalam 2 jam jika tidak dibayar DP.');
</script>";

header("Location: ketentuan.php");
exit;
?>