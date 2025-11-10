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
if (empty($id_studio) || empty($tanggal) || empty($jam_booking)) {
    die("Data tidak lengkap. Mohon isi semua field yang diperlukan.");
}

// Format jam
$jam_mulai = str_replace('.', ':', $jam_mulai);
$jam_selesai = str_replace('.', ':', $jam_selesai);
if (substr_count($jam_mulai, ':') == 1) $jam_mulai .= ':00';
if (substr_count($jam_selesai, ':') == 1) $jam_selesai .= ':00';

// Hapus karakter non-numerik dari total tagihan
$total_tagihan_numeric = preg_replace('/[^0-9]/', '', $total_tagihan);
if ($total_tagihan_numeric === '') $total_tagihan_numeric = 0;

// Cek apakah tabel jadwal ada
$jadwal_aktif = false;
$id_jadwal = null;
$cek_tabel = $koneksi->query("SHOW TABLES LIKE 'jadwal'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $jadwal_aktif = true;
}

// =================================================================================
// === VALIDASI 1: CEK APAKAH STUDIO + TANGGAL + JAM + PAKET SUDAH ADA (EXACT MATCH)
// =================================================================================
$cekBookingExact = $koneksi->prepare("
    SELECT id_order FROM booking 
    WHERE id_studio = ? AND Tanggal = ? AND jam_booking = ? AND paket = ? 
      AND status != 'dibatalkan'
    LIMIT 1
");
$cekBookingExact->bind_param("ssss", $id_studio, $tanggal, $jam_booking, $paket);
$cekBookingExact->execute();
$hasilExact = $cekBookingExact->get_result();

if ($hasilExact && $hasilExact->num_rows > 0) {
    echo "<script>
        alert('Kombinasi Studio, Tanggal, Jam, dan Paket yang sama sudah dibooking!\\n\\nSilakan ubah salah satu:\\n- Pilih jam berbeda, ATAU\\n- Pilih paket berbeda.');
        window.history.back();
    </script>";
    exit;
}

// =================================================================================
// === VALIDASI 2: CEK TABEL JADWAL — IZINKAN PAKET BERBEDA PADA JAM SAMA
// =================================================================================
if ($jadwal_aktif) {
    $cekJadwal = $koneksi->prepare("
        SELECT j.id_jadwal, b.paket 
        FROM jadwal j 
        LEFT JOIN booking b ON j.id_jadwal = b.id_jadwal
        WHERE j.id_studio = ? AND j.Tanggal = ? AND j.jam_booking = ? 
          AND j.status != 'dibatalkan'
    ");
    $cekJadwal->bind_param("sss", $id_studio, $tanggal, $jam_booking);
    $cekJadwal->execute();
    $hasilJadwal = $cekJadwal->get_result();

    $jadwal_bentrok = false;
    while ($row = $hasilJadwal->fetch_assoc()) {
        // Jika paketnya sama persis → bentrok
        if (trim(strtolower($row['paket'])) === trim(strtolower($paket))) {
            $jadwal_bentrok = true;
            break;
        }
    }

    if ($jadwal_bentrok) {
        echo "<script>
            alert('Jam dan paket ini sudah dipakai pada tanggal tersebut!\\nSilakan pilih jam atau paket lain.');
            window.history.back();
        </script>";
        exit;
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
        // Insert jadwal baru jika belum ada
        $insert_jadwal = $koneksi->prepare("
            INSERT INTO jadwal (id_studio, Tanggal, jam_mulai, jam_selesai, jam_booking, status)
            VALUES (?, ?, ?, ?, ?, 'dibooking')
        ");
        $insert_jadwal->bind_param("sssss", $id_studio, $tanggal, $jam_mulai, $jam_selesai, $jam_booking);
        $insert_jadwal->execute();
        $id_jadwal = $insert_jadwal->insert_id;
    }
}

// Default jika jadwal belum aktif
if (empty($id_jadwal)) $id_jadwal = null;

// =================================================================================
// === SIMPAN DATA BOOKING ===
// =================================================================================
$stmt = $koneksi->prepare("
    INSERT INTO booking 
    (id_user, id_studio, id_jadwal, total_tagihan, status, status_pembayaran, Tanggal, jam_booking, paket)
    VALUES (?, ?, ?, ?, 'menunggu', 'belum_dibayar', ?, ?, ?)
");
$stmt->bind_param("isidsss", $id_user, $id_studio, $id_jadwal, $total_tagihan_numeric, $tanggal, $jam_booking, $paket);

if (!$stmt->execute()) {
    die('Gagal menyimpan booking: ' . $stmt->error);
}

$id_order = $stmt->insert_id;

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
    'status_pembayaran' => 'belum_dibayar'
];

header("Location: ketentuan.php");
exit;
?>
