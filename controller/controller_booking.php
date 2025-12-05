<?php
session_start();
require "../config/koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Method tidak valid!";
    header("Location: ../admin/tambah_booking.php");
    exit;
}

try {
    // Debug: log POST
    error_log("POST Data: " . print_r($_POST, true));

    // Mulai transaksi
    if (!$koneksi->begin_transaction()) {
        throw new Exception("Gagal memulai transaksi: " . $koneksi->error);
    }

    // Validasi input (sesuaikan dengan name di form)
    $required = ['jadwal_id', 'tanggal', 'nama', 'telepon', 'paket', 'totalTagihan', 'metodePembayaran', 'id_studio'];
    foreach ($required as $f) {
        if (!isset($_POST[$f]) || trim((string)$_POST[$f]) === '') {
            throw new Exception("Field '$f' wajib diisi!");
        }
    }

    // Ambil data POST -> pakai nama sesuai form
    $jadwal_id        = intval($_POST['jadwal_id']);   // id_jadwal (int)
    $id_studio        = trim($_POST['id_studio']);     // id_studio (varchar)
    $tanggal          = trim($_POST['tanggal']);       // YYYY-MM-DD
    $nama             = trim($_POST['nama']);
    $email            = trim($_POST['email'] ?? '');
    $telepon          = trim($_POST['telepon']);
    $jam_booking      = trim($_POST['jam_booking'] ?? '');
    $paket            = trim($_POST['paket']);
    $totalTagihan     = floatval($_POST['totalTagihan']);
    $metodePembayaran = trim($_POST['metodePembayaran']);

    // Basic sanity checks
    if ($jadwal_id <= 0) throw new Exception("ID jadwal tidak valid.");
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) throw new Exception("Format tanggal tidak valid.");

    error_log("Trying booking: jadwal_id={$jadwal_id}, studio={$id_studio}, tanggal={$tanggal}");

    // 1) Kunci baris jadwal (FOR UPDATE) untuk mencegah race condition
    $cek = $koneksi->prepare("
        SELECT id_jadwal, id_studio, tanggal, status, jam_mulai, jam_selesai
        FROM jadwal
        WHERE id_jadwal = ?
        FOR UPDATE
    ");
    if (!$cek) throw new Exception("Prepare cek jadwal gagal: " . $koneksi->error);
    $cek->bind_param("i", $jadwal_id);
    $cek->execute();
    $resCek = $cek->get_result();
    if ($resCek->num_rows === 0) {
        throw new Exception("Jadwal tidak ditemukan.");
    }
    $jadwal = $resCek->fetch_assoc();

    // Pastikan jadwal cocok dengan tanggal dan studio (hindari mismatch)
    if (isset($jadwal['id_studio']) && $jadwal['id_studio'] !== $id_studio) {
        throw new Exception("ID jadwal tidak cocok dengan studio yang dipilih.");
    }
    if (isset($jadwal['tanggal']) && $jadwal['tanggal'] !== $tanggal) {
        throw new Exception("Jadwal tidak ada pada tanggal tersebut.");
    }

    // Normalisasi status dan cek
    $status_jadwal = strtolower(str_replace(' ', '', (string)$jadwal['status'] ?? ''));
    if ($status_jadwal === 'dibooking') {
        throw new Exception("Jadwal ini sudah dibooking (status jadwal).");
    }

    // 2) Cek di booking_offline apakah sudah ada booking untuk jadwal+studio+tanggal
    $cekOffline = $koneksi->prepare("
        SELECT id_offline
        FROM booking_offline
        WHERE id_jadwal = ?
          AND tanggal = ?
          AND id_studio = ?
        LIMIT 1
    ");
    if (!$cekOffline) throw new Exception("Prepare cek booking_offline gagal: " . $koneksi->error);
    // tipe: i (int), s (string), s (string)
    $cekOffline->bind_param("iss", $jadwal_id, $tanggal, $id_studio);
    $cekOffline->execute();
    $resOffline = $cekOffline->get_result();
    if ($resOffline->num_rows > 0) {
        throw new Exception("Jadwal ini sudah dibooking oleh customer lain (booking_offline).");
    }

    // 3) Insert booking_offline
    // Ganti bagian INSERT jadi seperti ini:

$insert = $koneksi->prepare("
    INSERT INTO booking_offline 
    (id_jadwal, id_studio, nama_lengkap, email, whatsapp, tanggal, 
     jam_booking, paket, total_tagihan, metode_pembayaran, status, status_pembayaran, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'terkonfirmasi', 'lunas', NOW())
");

// Mapping metode pembayaran (sangat bagus!)
$metodeMap = [
    'qris'            => 'qris',
    'QRIS'            => 'qris',
    'va'              => 'va',
    'virtual account' => 'va',
    'Virtual Account' => 'va',
    'kes'             => 'kes',
    'tunai'           => 'kes',
    'Tunai'           => 'kes',
    'cash'            => 'kes',
    'Cash'            => 'kes'
];

$metodeDB = $metodeMap[strtolower(trim($_POST['metodePembayaran'] ?? ''))] ?? 'kes';

// PERBAIKAN UTAMA: bind_param hanya 10 parameter!
$insert->bind_param(
    "isssssssds",  // ← BENAR! 10 karakter untuk 10 placeholder
    $jadwal_id,      // i → int
    $id_studio,      // s → varchar
    $nama,           // s
    $email,          // s
    $telepon,        // s
    $tanggal,        // s (date)
    $jam_booking,    // s
    $paket,          // s
    $totalTagihan,   // d → decimal
    $metodeDB        // s → enum (qris/va/kes)
);

if (!$insert->execute()) {
    throw new Exception("Gagal menyimpan booking: " . $insert->error);
}

$id_offline = $insert->insert_id;
error_log("Booking offline berhasil! ID: {$id_offline}");

    // 4) Update status pada tabel jadwal menjadi 'Dibooking'
    $update = $koneksi->prepare("
        UPDATE jadwal
        SET status = 'Dibooking'
        WHERE id_jadwal = ?
    ");
    if (!$update) throw new Exception("Prepare update jadwal gagal: " . $koneksi->error);
    $update->bind_param("i", $jadwal_id);
    if (!$update->execute()) {
        throw new Exception("Gagal update status jadwal: " . $update->error);
    }

    // Commit transaksi
    if (!$koneksi->commit()) {
        throw new Exception("Commit gagal: " . $koneksi->error);
    }

    // Success set session and redirect
    $_SESSION['success'] = "Booking berhasil dilakukan!";
    $_SESSION['booking_id'] = $id_offline;
    $_SESSION['booking_nama'] = $nama;

    header("Location: ../admin/order.php?booking_success=1&id=" . $id_offline);
    exit();

} catch (Exception $e) {
    // Rollback & log
    if ($koneksi->connect_errno === 0) {
        $koneksi->rollback();
    }
    error_log("Booking Error: " . $e->getMessage());

    $_SESSION['error'] = $e->getMessage();
    header("Location: ../admin/tambah_booking.php");
    exit();

} finally {
    // tutup statement jika ada
    if (isset($cek) && $cek instanceof mysqli_stmt) $cek->close();
    if (isset($cekOffline) && $cekOffline instanceof mysqli_stmt) $cekOffline->close();
    if (isset($insert) && $insert instanceof mysqli_stmt) $insert->close();
    if (isset($update) && $update instanceof mysqli_stmt) $update->close();
    // jangan selalu menutup koneksi kalau aplikasi lain masih butuh, tapi boleh jika memang ingin
    // $koneksi->close();
}
