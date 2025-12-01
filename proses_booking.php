<?php
// proses_booking.php
session_start();
require "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}
if (!isset($_SESSION['user_id'])) {
    die("Harap login terlebih dahulu.");
}

$id_user = $_SESSION['user_id'];
$id_studio = trim($_POST['id_studio'] ?? '');
$studio_nama = trim($_POST['studio_nama'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$jam_booking = trim($_POST['jam_booking'] ?? '');
$jam_mulai = trim($_POST['jam_mulai'] ?? '');
$jam_selesai = trim($_POST['jam_selesai'] ?? '');
$durasi = (int)($_POST['durasi'] ?? 0);
$paket = trim($_POST['paket'] ?? '');
$total_tagihan = preg_replace('/[^0-9]/','', $_POST['total_tagihan'] ?? '0');
$nama = trim($_POST['nama'] ?? '');

// validasi dasar
if (empty($id_studio) || empty($tanggal) || empty($jam_booking) || empty($paket) || empty($nama)) {
    echo "<script>alert('Data tidak lengkap. Mohon lengkapi semua field.'); window.history.back();</script>";
    exit;
}

// format jam ke HH:MM:SS (mengganti '.' ke ':' jika ada)
$jam_mulai = str_replace('.', ':', $jam_mulai);
$jam_selesai = str_replace('.', ':', $jam_selesai);
if (strlen($jam_mulai) <= 5) $jam_mulai .= ':00';
if (strlen($jam_selesai) <= 5) $jam_selesai .= ':00';

// validasi waktu logis
if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
    echo "<script>alert('Jam mulai harus sebelum jam selesai.'); window.history.back();</script>";
    exit;
}

// set expired_at (misal 2 jam)
$expired_at = date('Y-m-d H:i:s', strtotime('+2 hours'));

// cek tabel jadwal ada atau tidak
$jadwal_aktif = false;
$id_jadwal = null;
if ($cek = $koneksi->query("SHOW TABLES LIKE 'jadwal'")) {
    if ($cek->num_rows > 0) $jadwal_aktif = true;
}

// === VALIDASI 1: cek duplikasi exact (studio,tanggal,jam_booking,paket) yang masih aktif ===
$dup = $koneksi->prepare("
    SELECT id_order FROM booking
    WHERE id_studio = ? AND Tanggal = ? AND jam_booking = ? AND paket = ?
      AND status NOT IN ('dibatalkan','selesai')
      AND (expired_at IS NULL OR expired_at > NOW())
    LIMIT 1
");
$dup->bind_param("ssss", $id_studio, $tanggal, $jam_booking, $paket);
$dup->execute();
$grdup = $dup->get_result();
if ($grdup && $grdup->num_rows > 0) {
    echo "<script>alert('Maaf, slot ini sudah dibooking oleh user lain. Silakan pilih slot lain.'); window.history.back();</script>";
    exit;
}

// === VALIDASI 2: cek overlap jam (lebih aman) ===
// Kita ambil semua booking existing untuk studio & tanggal yang masih aktif
$ov = $koneksi->prepare("
    SELECT jam_booking, paket FROM booking
    WHERE id_studio = ? AND Tanggal = ?
      AND status NOT IN ('dibatalkan','selesai')
      AND (expired_at IS NULL OR expired_at > NOW())
");
$ov->bind_param("ss", $id_studio, $tanggal);
$ov->execute();
$grov = $ov->get_result();
while ($row = $grov->fetch_assoc()) {
    $ex = explode('-', $row['jam_booking']);
    if (count($ex) != 2) continue;
    $ex_start = str_replace('.',':', trim($ex[0]));
    $ex_end = str_replace('.',':', trim($ex[1]));
    if (strlen($ex_start)<=5) $ex_start .= ':00';
    if (strlen($ex_end)<=5) $ex_end .= ':00';
    // overlap check: start < ex_end && ex_start < end
    if (strtotime($jam_mulai) < strtotime($ex_end) && strtotime($ex_start) < strtotime($jam_selesai)) {
        echo "<script>
            alert('Maaf, jam yang Anda pilih bentrok dengan booking lain (".$row['jam_booking']."). Silakan pilih waktu lain.');
            window.history.back();
        </script>";
        exit;
    }
}

// === JIKA ADA TABEL JADWAL: buat/ambil id_jadwal ===
if ($jadwal_aktif) {
    $q = $koneksi->prepare("SELECT id_jadwal FROM jadwal WHERE id_studio = ? AND Tanggal = ? AND jam_booking = ? LIMIT 1");
    $q->bind_param("sss", $id_studio, $tanggal, $jam_booking);
    $q->execute();
    $gq = $q->get_result();
    if ($gq && $gq->num_rows > 0) {
        $r = $gq->fetch_assoc();
        $id_jadwal = $r['id_jadwal'];
    } else {
        $insJ = $koneksi->prepare("INSERT INTO jadwal (id_studio, Tanggal, jam_mulai, jam_selesai, jam_booking, status) VALUES (?, ?, ?, ?, ?, 'dibooking')");
        $insJ->bind_param("sssss", $id_studio, $tanggal, $jam_mulai, $jam_selesai, $jam_booking);
        if (!$insJ->execute()) {
            // tidak fatal — lanjut tapi beri catatan
            error_log("Gagal insert jadwal: ".$insJ->error);
        } else {
            $id_jadwal = $insJ->insert_id;
        }
    }
}

// === INSERT booking dalam transaction untuk aman dari race ===
$koneksi->begin_transaction();
try {
    // 🔥 PERBAIKAN: Hilangkan created_at karena tidak ada di tabel
    $stmt = $koneksi->prepare("
        INSERT INTO booking
        (id_user, id_studio, id_jadwal, total_tagihan, status, status_pembayaran, Tanggal, jam_booking, paket, expired_at)
        VALUES (?, ?, ?, ?, 'menunggu', 'belum_dibayar', ?, ?, ?, ?)
    ");
    $stmt->bind_param("isidssss", $id_user, $id_studio, $id_jadwal, $total_tagihan, $tanggal, $jam_booking, $paket, $expired_at);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal insert booking: ".$stmt->error);
    }
    $id_order = $stmt->insert_id;

    // commit
    $koneksi->commit();
    
    // simpan summary di session
    $_SESSION['booking_data'] = [
        'id_order' => $id_order,
        'id_user' => $id_user,
        'id_studio' => $id_studio,
        'id_jadwal' => $id_jadwal,
        'tanggal' => $tanggal,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'jam_booking' => $jam_booking,
        'total' => $total_tagihan,
        'nama' => $nama,
        'durasi' => $durasi,
        'paket' => $paket,
        'status_pembayaran' => 'belum_dibayar',
        'expired_at' => $expired_at
    ];

    // redirect ke halaman ketentuan / pembayaran
    echo "<script>
        alert('✅ Booking berhasil dibuat! ID Order: {$id_order}\\n\\nHarap lakukan pembayaran sesuai ketentuan.');
        window.location.href = 'ketentuan.php';
    </script>";
    exit;
    
} catch (Exception $e) {
    $koneksi->rollback();
    error_log($e->getMessage());
    echo "<script>
        alert('❌ Terjadi kesalahan saat menyimpan booking:\\n" . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
    exit;
}
?>