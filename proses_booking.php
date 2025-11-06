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

// Ambil semua data dari form
$id_user = $_SESSION['user_id'];
$id_studio = isset($_POST['id_studio']) ? trim($_POST['id_studio']) : '';
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
$jam_mulai = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
$jam_selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';
$jam_booking = isset($_POST['jam_booking']) ? trim($_POST['jam_booking']) : '';
$total_tagihan = isset($_POST['total_tagihan']) ? trim($_POST['total_tagihan']) : 0;
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$durasi = isset($_POST['durasi']) ? (int)$_POST['durasi'] : 0;

// Validasi dasar
if (empty($id_studio) || empty($tanggal) || empty($jam_booking)) {
    die("Data tidak lengkap. Mohon isi semua field yang diperlukan.");
}

// Format jam (ubah 10.00 → 10:00:00)
if (!empty($jam_mulai) && strpos($jam_mulai, '.') !== false) {
    $jam_mulai = str_replace('.', ':', $jam_mulai);
}
if (!empty($jam_selesai) && strpos($jam_selesai, '.') !== false) {
    $jam_selesai = str_replace('.', ':', $jam_selesai);
}

// Tambahkan detik jika belum ada
if (!empty($jam_mulai) && substr_count($jam_mulai, ':') == 1) {
    $jam_mulai .= ':00';
}
if (!empty($jam_selesai) && substr_count($jam_selesai, ':') == 1) {
    $jam_selesai .= ':00';
}

// Normalisasi total tagihan (hapus "Rp" dan titik)
$total_tagihan_numeric = preg_replace('/[^0-9]/', '', $total_tagihan);
if ($total_tagihan_numeric === '') $total_tagihan_numeric = 0;

// === CEK APAKAH FITUR JADWAL SUDAH TERSEDIA ===
$id_jadwal = null;
$jadwal_aktif = false;

// Coba cek apakah tabel jadwal ada
$cek_tabel = $koneksi->query("SHOW TABLES LIKE 'jadwal'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $jadwal_aktif = true;
}

// === CEK APAKAH SUDAH ADA BOOKING DI TANGGAL DAN JAM TERSEBUT ===
// Ubah 'id_booking' → 'id_order' (karena itu nama kolom primary di tabel booking)
$cekBooking = $koneksi->prepare("
    SELECT id_order 
    FROM booking 
    WHERE id_studio = ? 
      AND Tanggal = ? 
      AND jam_booking = ? 
      AND status != 'Dibatalkan'
");
$cekBooking->bind_param("sss", $id_studio, $tanggal, $jam_booking);
$cekBooking->execute();
$hasilCek = $cekBooking->get_result();

if ($hasilCek && $hasilCek->num_rows > 0) {
    echo "<script>
        alert('Jam $jam_booking pada tanggal $tanggal sudah dibooking. Silakan pilih jam lain.');
        window.history.back();
    </script>";
    exit;
}

// === JIKA FITUR JADWAL AKTIF, CEK JUGA DI TABEL JADWAL ===
if ($jadwal_aktif) {
    $cekJadwal = $koneksi->prepare("
        SELECT id_jadwal 
        FROM jadwal 
        WHERE id_studio = ? 
          AND Tanggal = ? 
          AND jam_booking = ? 
          AND status != 'dibatalkan'
    ");
    $cekJadwal->bind_param("sss", $id_studio, $tanggal, $jam_booking);
    $cekJadwal->execute();
    $hasilJadwal = $cekJadwal->get_result();

    if ($hasilJadwal && $hasilJadwal->num_rows > 0) {
        echo "<script>
            alert('Jam $jam_booking sudah digunakan di jadwal studio ini. Silakan pilih jam lain.');
            window.history.back();
        </script>";
        exit;
    }
}

// === 1. JIKA FITUR JADWAL SUDAH ADA, PROSES NORMAL ===
if ($jadwal_aktif) {
    $query_cek = $koneksi->prepare("
        SELECT id_jadwal FROM jadwal 
        WHERE id_studio = ? AND Tanggal = ? AND jam_booking = ?
    ");
    if ($query_cek) {
        $query_cek->bind_param("sss", $id_studio, $tanggal, $jam_booking);
        $query_cek->execute();
        $result = $query_cek->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_jadwal = $row['id_jadwal'];
        } else {
            // Insert jadwal baru
            $insert_jadwal = $koneksi->prepare("
                INSERT INTO jadwal (id_studio, Tanggal, jam_mulai, jam_selesai, jam_booking, status)
                VALUES (?, ?, ?, ?, ?, 'diboking')
            ");
            if ($insert_jadwal) {
                $insert_jadwal->bind_param("sssss", $id_studio, $tanggal, $jam_mulai, $jam_selesai, $jam_booking);
                if ($insert_jadwal->execute()) {
                    $id_jadwal = $insert_jadwal->insert_id;
                }
            }
        }
    }
}

// === 2. JIKA FITUR JADWAL BELUM ADA, GUNAKAN NILAI DEFAULT ===
if (empty($id_jadwal) || $id_jadwal == 0) {
    $id_jadwal = null; // boleh NULL kalau kolomnya diizinkan NULL
}

// === 3. SIMPAN DATA BOOKING ===
$stmt = $koneksi->prepare("
    INSERT INTO booking 
    (id_user, id_studio, id_jadwal, total_tagihan, status, status_pembayaran, Tanggal, jam_booking)
    VALUES (?, ?, ?, ?, 'menunggu', 'belum_dibayar', ?, ?)
");

if (!$stmt) {
    die("Error prepare insert booking: " . $koneksi->error);
}

$stmt->bind_param("ssisss",
    $id_user,
    $id_studio,
    $id_jadwal,
    $total_tagihan_numeric,
    $tanggal,
    $jam_booking
);

if (!$stmt->execute()) {
    die("Gagal menyimpan booking: " . $stmt->error);
}

$id_order = $stmt->insert_id;

if ($id_order == 0 || $id_order === null) {
    die("Error: ID Order tidak valid.");
}

// === 4. SIMPAN KE SESSION ===
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
    'status_pembayaran' => 'belum_dibayar'
];

// === 5. REDIRECT KE KETENTUAN ===
header("Location: ketentuan.php");
exit;
?>
