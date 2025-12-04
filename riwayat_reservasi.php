<?php
session_start();
require "config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu!');
        window.location.href='login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil nama lengkap user
$namaLengkap = '';
$resultUser = $koneksi->query("SELECT nama_lengkap FROM user WHERE id_user = '$id_user' LIMIT 1");
if ($resultUser && $rowUser = $resultUser->fetch_assoc()) {
    $namaLengkap = $rowUser['nama_lengkap'];
}
$_SESSION['nama_lengkap'] = $namaLengkap;

// ========== PERBAIKAN AUTO-CANCEL ==========
$checkColumn = $koneksi->query("SHOW COLUMNS FROM booking LIKE 'expired_at'");
if ($checkColumn && $checkColumn->num_rows > 0) {
    $currentTime = date('Y-m-d H:i:s');
    
    // Langsung update semua booking yang expired dalam satu query
    $sqlAutoCancel = "UPDATE booking 
                      SET status = 'dibatalkan' 
                      WHERE status_pembayaran = 'belum_dibayar' 
                      AND status = 'menunggu'
                      AND expired_at IS NOT NULL
                      AND expired_at < '$currentTime'";
    
    $koneksi->query($sqlAutoCancel);
    $affectedRows = $koneksi->affected_rows;
    
    // Tampilkan notifikasi HANYA SEKALI menggunakan URL parameter
    if ($affectedRows > 0 && !isset($_GET['notif_shown'])) {
        echo "<script>
            alert('⚠️ $affectedRows booking Anda telah dibatalkan otomatis karena melewati batas waktu pembayaran (2 jam).');
            window.location.href='riwayat_reservasi.php?notif_shown=1';
        </script>";
        exit;
    }
}

// Handle ubah jadwal
if (isset($_POST['ubah_jadwal'])) {
    $id_order = $_POST['id_order'];
    $tanggal_baru = $_POST['tanggal_baru'];
    $jam_baru = $_POST['jam_baru'];
    $studio_baru = $_POST['studio_baru'] ?? '';
    $paket_baru = $_POST['paket_baru'] ?? '';

    if (!empty($tanggal_baru) && !empty($jam_baru) && !empty($studio_baru) && !empty($paket_baru)) {
        
        // CEK APAKAH KOMBINASI STUDIO + TANGGAL + JAM + PAKET SUDAH ADA (EXACT MATCH)
        $cekExact = $koneksi->prepare("
            SELECT id_order 
            FROM booking 
            WHERE id_studio = ? 
              AND Tanggal = ? 
              AND jam_booking = ? 
              AND paket = ?
              AND status != 'dibatalkan'
              AND id_order != ?
            LIMIT 1
        ");
        $cekExact->bind_param("ssssi", $studio_baru, $tanggal_baru, $jam_baru, $paket_baru, $id_order);
        $cekExact->execute();
        $hasilExact = $cekExact->get_result();
        
        if ($hasilExact && $hasilExact->num_rows > 0) {
            echo "<script>
                alert('Kombinasi Studio, Jam, dan Paket yang sama sudah dibooking!\\n\\nSilakan ubah salah satu:\\n- Pilih studio berbeda, ATAU\\n- Pilih jam berbeda, ATAU\\n- Pilih paket berbeda');
                window.history.back();
            </script>";
            exit;
        }
        
        // Reset expired_at saat mengubah jadwal
        $checkColumn = $koneksi->query("SHOW COLUMNS FROM booking LIKE 'expired_at'");
        if ($checkColumn && $checkColumn->num_rows > 0) {
            $expiredAtBaru = date('Y-m-d H:i:s', strtotime('+2 hours'));
            $updateJadwal = $koneksi->prepare("UPDATE booking SET Tanggal = ?, jam_booking = ?, id_studio = ?, paket = ?, expired_at = ? WHERE id_order = ? AND id_user = ?");
            $updateJadwal->bind_param("sssssii", $tanggal_baru, $jam_baru, $studio_baru, $paket_baru, $expiredAtBaru, $id_order, $id_user);
        } else {
            $updateJadwal = $koneksi->prepare("UPDATE booking SET Tanggal = ?, jam_booking = ?, id_studio = ?, paket = ? WHERE id_order = ? AND id_user = ?");
            $updateJadwal->bind_param("ssssii", $tanggal_baru, $jam_baru, $studio_baru, $paket_baru, $id_order, $id_user);
        }
        
        if ($updateJadwal->execute()) {
            $updateJadwal->close();
            echo "<script>
                alert('Jadwal berhasil diubah!');
                window.location.href='riwayat_reservasi.php?updated=' + Date.now();
            </script>";
            exit;
        } else {
            echo "<script>alert('Gagal mengubah jadwal: " . $koneksi->error . "');</script>";
        }
    } else {
        echo "<script>alert('Semua field harus diisi!');</script>";
    }
}

// Aksi batal manual
if (isset($_GET['batal'])) {
    $id_order = $_GET['batal'];
    $update = $koneksi->prepare("UPDATE booking SET status = 'dibatalkan' WHERE id_order = ? AND id_user = ?");
    $update->bind_param("ii", $id_order, $id_user);
    $update->execute();
    $update->close();
    echo "<script>
        alert('Reservasi berhasil dibatalkan.');
        window.location.href='riwayat_reservasi.php';
    </script>";
    exit;
}

// Filter
$filterStatusPembayaran = $_GET['status_pembayaran'] ?? '';
$filterTanggalAwal = $_GET['tanggal_awal'] ?? '';
$filterTanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$showEntries = $_GET['entries'] ?? 10;

// Query dasar
$query = "SELECT * FROM booking WHERE id_user = ?";
$params = [$id_user];
$types = "i";

// Filter berdasarkan status pembayaran
if (!empty($filterStatusPembayaran)) {
    $query .= " AND status_pembayaran = ?";
    $params[] = $filterStatusPembayaran;
    $types .= "s";
}

// Filter berdasarkan tanggal booking
if (!empty($filterTanggalAwal) && !empty($filterTanggalAkhir)) {
    $query .= " AND (Tanggal BETWEEN ? AND ?)";
    $params[] = $filterTanggalAwal;
    $params[] = $filterTanggalAkhir;
    $types .= "ss";
} elseif (!empty($filterTanggalAwal)) {
    $query .= " AND Tanggal >= ?";
    $params[] = $filterTanggalAwal;
    $types .= "s";
} elseif (!empty($filterTanggalAkhir)) {
    $query .= " AND Tanggal <= ?";
    $params[] = $filterTanggalAkhir;
    $types .= "s";
}

$query .= " ORDER BY id_order DESC LIMIT ?";
$params[] = (int)$showEntries;
$types .= "i";

$stmt = $koneksi->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Reservasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            font-style: italic;
        }
        .header-title {
            background-color: #000;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            display: inline-block;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            background-color: white;
        }
        .btn-filter {
            background-color: #FFD700;
            border: none;
            color: #000;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-filter:hover { background-color: #f2c200; }
        .btn-reset {
            background-color: #6c757d;
            color: white;
            border: none;
            font-weight: 600;
        }
        .btn-reset:hover { background-color: #5a6268; }
        .badge-warning { background-color: #f9e79f; color: #856404; }
        .badge-danger { background-color: #f5b7b1; color: #721c24; }
        .badge-success { background-color: #abebc6; color: #155724; }
        .btn-blue { background-color: #0d6efd; color: white; border: none; }
        .btn-blue:hover { background-color: #0b5ed7; }
        .btn-red { background-color: #ff4d4d; color: white; border: none; }
        .btn-red:hover { background-color: #e63946; }
        .footer-note {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 20px;
        }
        .studio-gold {
            background-color: gold !important;
            color: black;
            font-weight: 600;
            border-radius: 5px;
            padding: 5px 10px;
        }
        .studio-bronze {
            background-color: #cd7f32 !important;
            color: white;
            font-weight: 600;
            border-radius: 5px;
            padding: 5px 10px;
        }
        .paket-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.4;
            text-align: center;
        }
        .paket-bronze-tanpa {
            background-color: #e8daef;
            color: #633974;
        }
        .paket-bronze-dengan {
            background-color: #d7bde2;
            color: #4a235a;
        }
        .paket-gold-reguler {
            background-color: #fff9e6;
            color: #b8860b;
        }
        .paket-gold-2jam {
            background-color: #ffe680;
            color: #856404;
        }
        .paket-gold-3jam {
            background-color: #ffcc00;
            color: #664d00;
        }
        /* Timer Countdown Style */
        .timer-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .timer-label {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 5px;
        }
        .timer-countdown {
            font-size: 1.4rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .timer-warning {
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .timer-warning .timer-countdown {
            color: #ffeb3b;
        }
        .timer-expired {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .timer-expired .timer-countdown {
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Reys Music Studio</a>
    <a href="index.php" class="text-dark text-decoration-none fw-semibold">Home</a>
  </div>
</nav>

<div class="container my-5">
    <div class="header-title mb-4">Riwayat Reservasi</div>

    <div class="card p-4">
        <form class="mb-4" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select">
                        <option value="">Semua Transaksi</option>
                        <option value="belum_dibayar" <?= $filterStatusPembayaran == 'belum_dibayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                        <option value="dp_dibayar" <?= $filterStatusPembayaran == 'dp_dibayar' ? 'selected' : '' ?>>DP Dibayar</option>
                        <option value="lunas" <?= $filterStatusPembayaran == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($filterTanggalAwal) ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($filterTanggalAkhir) ?>" class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-filter w-50">FILTER</button>
                    <a href="riwayat_reservasi.php" class="btn btn-reset w-50">RESET</a>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label mb-1">Show Entries</label>
                    <select name="entries" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="10" <?= $showEntries == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $showEntries == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $showEntries == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $showEntries == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Id Order</th>
                        <th>Nama</th>
                        <th>Studio</th>
                        <th>Paket</th>
                        <th>Tanggal Booking</th>
                        <th>Jam Booking</th>
                        <th>Total Tagihan</th>
                        <th>Waktu Upload DP</th>
                        <th>Status Konfirmasi</th>
                        <th>Status Pembayaran</th>
                        <th>Bukti DP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id_order'] ?></td>
                                <td><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $namaLengkap) ?></td>
                                <td>
                                    <?php
                                    $idStudio = $row['id_studio'];
                                    $namaStudio = '-';
                                    $queryStudio = $koneksi->query("SELECT nama FROM studio WHERE id_studio = '$idStudio' LIMIT 1");
                                    if ($queryStudio && $rowStudio = $queryStudio->fetch_assoc()) {
                                        $namaStudio = $rowStudio['nama'];
                                    }

                                    $classStudio = '';
                                    if (stripos($namaStudio, 'gold') !== false) {
                                        $classStudio = 'studio-gold';
                                    } elseif (stripos($namaStudio, 'bronze') !== false) {
                                        $classStudio = 'studio-bronze';
                                    }

                                    echo "<span class='$classStudio'>" . htmlspecialchars($namaStudio) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $paket = $row['paket'] ?? '';
                                    $classPaket = 'paket-badge';
                                    $paketText = '';
                                    
                                    if (empty($paket) || $paket === '-' || $paket === 'without' || $paket === 'null') {
                                        $paketText = '<span class="text-muted">Tidak ada info paket</span>';
                                    }
                                    elseif (stripos($namaStudio, 'bronze') !== false) {
                                        if (stripos($paket, 'tanpa') !== false || stripos($paket, 'without') !== false || stripos($paket, '35') !== false) {
                                            $classPaket .= ' paket-bronze-tanpa';
                                            $paketText = 'Tanpa Keyboard<br>(35K/jam)';
                                        } elseif (stripos($paket, 'dengan') !== false || stripos($paket, 'with') !== false || stripos($paket, '40') !== false) {
                                            $classPaket .= ' paket-bronze-dengan';
                                            $paketText = 'Dengan Keyboard<br>(40K/jam)';
                                        } else {
                                            $paketText = htmlspecialchars($paket);
                                        }
                                    }
                                    elseif (stripos($namaStudio, 'gold') !== false) {
                                        if (stripos($paket, 'reguler') !== false || stripos($paket, 'regular') !== false || stripos($paket, '50') !== false) {
                                            $classPaket .= ' paket-gold-reguler';
                                            $paketText = 'Reguler<br>(50K/jam)';
                                        } elseif (stripos($paket, '2 jam') !== false || stripos($paket, '2jam') !== false || stripos($paket, '90') !== false) {
                                            $classPaket .= ' paket-gold-2jam';
                                            $paketText = 'Paket 2 jam<br>(90K)';
                                        } elseif (stripos($paket, '3 jam') !== false || stripos($paket, '3jam') !== false || stripos($paket, '130') !== false) {
                                            $classPaket .= ' paket-gold-3jam';
                                            $paketText = 'Paket 3 jam<br>(130K)';
                                        } else {
                                            $paketText = htmlspecialchars($paket);
                                        }
                                    }
                                    else {
                                        $paketText = htmlspecialchars($paket);
                                    }
                                    
                                    if ($classPaket !== 'paket-badge') {
                                        echo "<span class='$classPaket'>" . $paketText . "</span>";
                                    } else {
                                        echo $paketText;
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['Tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['jam_booking']) ?></td>
                                <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                                
                                <!-- KOLOM COUNTDOWN TIMER -->
                                <td>
                                    <?php 
                                    // Cek apakah kolom expired_at ada
                                    $checkColumn = $koneksi->query("SHOW COLUMNS FROM booking LIKE 'expired_at'");
                                    $hasExpiredAt = ($checkColumn && $checkColumn->num_rows > 0);
                                    
                                    // Hanya tampilkan timer jika status belum dibayar dan belum dibatalkan
                                    if ($row['status_pembayaran'] === 'belum_dibayar' && $row['status'] === 'menunggu') {
                                        if ($hasExpiredAt && !empty($row['expired_at'])) {
                                            $expiredAt = strtotime($row['expired_at']);
                                            $sisaWaktu = $expiredAt - time();
                                            
                                            if ($sisaWaktu > 0) {
                                                // Hitung jam, menit, detik untuk tampilan awal
                                                $hours = floor($sisaWaktu / 3600);
                                                $minutes = floor(($sisaWaktu % 3600) / 60);
                                                $seconds = $sisaWaktu % 60;
                                                $formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                                
                                                echo "<div class='timer-container' data-waktu-expire='$expiredAt' data-id-order='{$row['id_order']}'>";
                                                echo "<div class='timer-label'><i class='bi bi-clock-history'></i> Booking Akan Expire Dalam:</div>";
                                                echo "<div class='timer-countdown'>$formatted</div>";
                                                echo "<small style='font-size: 0.7rem; opacity: 0.9;'>Segera upload DP untuk mengamankan booking Anda!</small>";
                                                echo "</div>";
                                            } else {
                                                echo "<div class='timer-container timer-expired'>";
                                                echo "<div class='timer-label'><i class='bi bi-x-circle'></i> Waktu Habis</div>";
                                                echo "<div class='timer-countdown'>EXPIRED</div>";
                                                echo "<small style='font-size: 0.7rem; opacity: 0.9;'>Booking otomatis dibatalkan</small>";
                                                echo "</div>";
                                            }
                                        } else {
                                            // Jika kolom tidak ada, tampilkan pesan untuk segera upload
                                            echo "<div class='alert alert-warning p-2 m-0' style='font-size: 0.85rem;'>";
                                            echo "<i class='bi bi-exclamation-triangle'></i> Segera upload DP!";
                                            echo "</div>";
                                        }
                                    } elseif ($row['status'] === 'dibatalkan') {
                                        echo "<span class='text-muted'>-</span>";
                                    } else {
                                        echo "<span class='text-muted'>-</span>";
                                    }
                                    ?>
                                </td>
                                
                                <td>
                                    <?php
                                    if ($row['status'] === 'menunggu') {
                                        echo "<span class='badge badge-warning p-2'>Menunggu Konfirmasi</span>";
                                    } elseif ($row['status'] === 'terkonfirmasi') {
                                        echo "<span class='badge badge-success p-2'>Terkonfirmasi</span>";
                                    } elseif ($row['status'] === 'dibatalkan') {
                                        echo "<span class='badge badge-danger p-2'>Dibatalkan</span>";
                                    } else {
                                        echo "<span class='badge badge-secondary p-2'>Tidak Diketahui</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                     if ($row['status_pembayaran'] === 'belum_dibayar') {
                                        echo "<span class='badge badge-danger p-2'>DP Belum Dibayar</span>";
                                     } elseif ($row['status_pembayaran'] === 'dp_dibayar') {
                                        echo "<span class='badge badge-success p-2'>DP Terbayar</span>";
                                     } elseif ($row['status_pembayaran'] === 'lunas') {
                                        echo "<span class='badge badge-success p-2'>Lunas</span>";
                                     } else {
                                        echo "<span class='badge badge-secondary p-2'>Tidak Diketahui</span>";
                                     }
                                    ?>
                                </td>
                                
                                <td>
                                    <?php if ($row['status_pembayaran'] === 'belum_dibayar' && $row['status'] !== 'dibatalkan'): ?>
                                        <div>
                                            <span class="text-muted d-block mb-2">Belum upload</span>
                                            <a href="ketentuan.php?id_order=<?= $row['id_order'] ?>" class="btn btn-sm" style="background-color: #FFD700; color: #000; font-weight: 600; border: none;">
                                                <i class="bi bi-cloud-upload"></i> Upload DP
                                            </a>
                                        </div>
                                    <?php elseif (!empty($row['bukti_dp'])): ?>
                                        <a href="uploads/bukti_dp/<?= urlencode($row['bukti_dp']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-earmark-image"></i> Lihat
                                        </a>
                                    <?php elseif ($row['status'] === 'dibatalkan'): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <span class="text-muted">Belum upload</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
    <?php if ($row['status'] !== 'dibatalkan'): ?>
        <button class="btn btn-blue btn-sm mb-1" 
                data-bs-toggle="modal" 
                data-bs-target="#ubahModal" 
                data-id="<?= $row['id_order'] ?>"
                data-tanggal="<?= $row['Tanggal'] ?>"
                data-jam="<?= $row['jam_booking'] ?>"
                data-studio="<?= $row['id_studio'] ?>"
                data-paket="<?= htmlspecialchars($row['paket'] ?? '') ?>">
            Ubah Jadwal
        </button><br>
        
        <?php if ($row['status_pembayaran'] !== 'lunas'): ?>
            <a href="?batal=<?= $row['id_order'] ?>" 
               onclick="return confirm('Batalkan pesanan ini?')" 
               class="btn btn-red btn-sm">
                Batalkan
            </a>
        <?php endif; ?>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>

                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-muted">Belum ada reservasi ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="footer-note">© 2025 Reys Music Studio</p>
</div>

<!-- Modal Ubah Jadwal -->
<div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="ubahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="formUbahJadwal" class="modal-content">
      <input type="hidden" name="ubah_jadwal" value="1">
      <div class="modal-header">
        <h5 class="modal-title" id="ubahModalLabel">Ubah Jadwal Reservasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_order" id="id_order">
        
        <div class="mb-3">
          <label class="form-label fw-semibold">Nama</label>
          <p class="mb-0"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $namaLengkap) ?></p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Studio dan Paket Lama</label>
          <p class="mb-0" id="studio_paket_lama">-</p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Tanggal dan jam booking lama</label>
          <p class="mb-0" id="jadwal_lama">-</p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Studio</label>
          <select class="form-select" name="studio_baru" id="studio_baru_select" required onchange="updatePaketOptions()">
            <option value="">-- Pilih Studio --</option>
            <?php
            $studioQuery = $koneksi->query("SELECT id_studio, nama FROM studio");
            while ($studioRow = $studioQuery->fetch_assoc()) {
                echo "<option value='" . $studioRow['id_studio'] . "'>" . htmlspecialchars($studioRow['nama']) . "</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Paket</label>
          <select class="form-select" name="paket_baru" id="paket_baru_select" required>
            <option value="">-- Pilih Studio Terlebih Dahulu --</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Tanggal Baru</label>
          <input type="date" name="tanggal_baru" id="tanggal_baru" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Pilih Jam Baru</label>
          <input type="text" name="jam_baru" id="jam_baru" class="form-control" placeholder="Contoh: 15.00-16.00" required>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-filter px-4" id="btnSimpanPerubahan">Simpan Perubahan</button>
        <button type="button" class="btn btn-reset px-4" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <h5>Apakah anda yakin ingin menyimpan perubahan?</h5>
        <div class="mt-4 d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-konfirmasi-batal" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-konfirmasi-ya" id="konfirmasiYa">Iya</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Data paket untuk setiap studio
const paketStudio = {
    'bronze': [
        { value: 'Tanpa Keyboard (35K/jam)', text: 'Tanpa Keyboard (35K/jam)' },
        { value: 'Dengan Keyboard (40K/jam)', text: 'Dengan Keyboard (40K/jam)' }
    ],
    'gold': [
        { value: 'Reguler (50K/jam)', text: 'Reguler (50K/jam)' },
        { value: 'Paket 2 jam (90K)', text: 'Paket 2 jam (90K)' },
        { value: 'Paket 3 jam (130K)', text: 'Paket 3 jam (130K)' }
    ]
};

function updatePaketOptions() {
    const studioSelect = document.getElementById('studio_baru_select');
    const paketSelect = document.getElementById('paket_baru_select');
    const selectedText = studioSelect.options[studioSelect.selectedIndex].text.toLowerCase();
    
    paketSelect.innerHTML = '<option value="">-- Pilih Paket --</option>';
    
    let jenisPaket = null;
    if (selectedText.includes('bronze')) {
        jenisPaket = 'bronze';
    } else if (selectedText.includes('gold')) {
        jenisPaket = 'gold';
    }
    
    if (jenisPaket && paketStudio[jenisPaket]) {
        paketStudio[jenisPaket].forEach(paket => {
            const option = document.createElement('option');
            option.value = paket.value;
            option.textContent = paket.text;
            paketSelect.appendChild(option);
        });
    }
}

const ubahModal = document.getElementById('ubahModal');
ubahModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const idOrder = button.getAttribute('data-id');
    const tanggal = button.getAttribute('data-tanggal');
    const jam = button.getAttribute('data-jam');
    const idStudio = button.getAttribute('data-studio');
    const paket = button.getAttribute('data-paket');
    
    document.getElementById('id_order').value = idOrder;
    document.getElementById('tanggal_baru').value = tanggal;
    document.getElementById('jam_baru').value = jam;
    document.getElementById('jadwal_lama').textContent = tanggal + ', ' + jam;
    
    if (idStudio) {
        document.getElementById('studio_baru_select').value = idStudio;
        updatePaketOptions();
        
        if (paket) {
            setTimeout(() => {
                const paketSelect = document.getElementById('paket_baru_select');
                for (let i = 0; i < paketSelect.options.length; i++) {
                    if (paketSelect.options[i].value.includes(paket) || paket.includes(paketSelect.options[i].value.split('(')[0].trim())) {
                        paketSelect.value = paketSelect.options[i].value;
                        break;
                    }
                }
            }, 100);
        }
    }
    
    const studioText = button.closest('tr').querySelector('td:nth-child(3)').textContent.trim();
    document.getElementById('studio_paket_lama').textContent = studioText + ' - ' + (paket || 'Tidak ada info paket');
});

document.getElementById('btnSimpanPerubahan').addEventListener('click', function(e) {
    e.preventDefault();
    
    const tanggalBaru = document.getElementById('tanggal_baru').value;
    const jamBaru = document.getElementById('jam_baru').value;
    const studioBaru = document.getElementById('studio_baru_select').value;
    const paketBaru = document.getElementById('paket_baru_select').value;
    
    if (!tanggalBaru || !jamBaru || !studioBaru || !paketBaru) {
        alert('Semua field harus diisi!');
        return;
    }
    
    const konfirmasiModal = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
    konfirmasiModal.show();
});

document.getElementById('konfirmasiYa').addEventListener('click', function() {
    document.getElementById('formUbahJadwal').submit();
});

// ============= COUNTDOWN TIMER LOGIC =============
// ============= IMPROVED COUNTDOWN TIMER WITH ALERT =============
let expiredNotified = false; // Flag untuk memastikan alert hanya muncul sekali

function updateTimers() {
    const timerContainers = document.querySelectorAll('.timer-container:not(.timer-expired)');
    let hasNewExpired = false;
    
    timerContainers.forEach(container => {
        const expireTime = parseInt(container.getAttribute('data-waktu-expire'));
        const idOrder = container.getAttribute('data-id-order');
        const now = Math.floor(Date.now() / 1000);
        const timeLeft = expireTime - now;
        
        if (timeLeft <= 0) {
            hasNewExpired = true;
            
            // Tandai sebagai expired
            container.classList.add('timer-expired');
            container.querySelector('.timer-label').innerHTML = '<i class="bi bi-x-circle"></i> Waktu Habis';
            container.querySelector('.timer-countdown').textContent = 'EXPIRED';
            
            const small = container.querySelector('small');
            if (small) {
                small.textContent = 'Memproses pembatalan...';
            }
            return;
        }
        
        // Hitung jam, menit, detik
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;
        
        // Format dengan leading zero
        const formatted = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
        
        container.querySelector('.timer-countdown').textContent = formatted;
        
        // Tambah warning animation jika kurang dari 30 menit
        if (timeLeft < 1800) {
            container.classList.add('timer-warning');
        }
        
        // Ubah background menjadi merah jika kurang dari 10 menit
        if (timeLeft < 600) {
            container.style.background = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
        }
    });
    
    // Jika ada yang baru expired DAN belum pernah muncul notifikasi
    if (hasNewExpired && !expiredNotified) {
        expiredNotified = true; // Set flag agar tidak muncul lagi
        
        // Tampilkan alert
        alert('⚠️ Waktu pembayaran DP Anda telah habis!\n\nBooking Anda akan dibatalkan secara otomatis.');
        
        // Setelah klik OK, redirect untuk trigger auto-cancel di PHP
        window.location.href = 'riwayat_reservasi.php';
    }
}

// Update timer setiap detik
const timerInterval = setInterval(updateTimers, 1000);
updateTimers();

// Background check setiap 30 detik untuk sinkronisasi dengan server
setInterval(() => {
    // Cek apakah ada timer yang expired
    const expiredTimers = document.querySelectorAll('.timer-expired');
    
    if (expiredTimers.length > 0 && !expiredNotified) {
        expiredNotified = true;
        clearInterval(timerInterval); // Stop timer interval
        
        alert('⚠️ Waktu pembayaran DP Anda telah habis!\n\nBooking Anda akan dibatalkan secara otomatis.');
        window.location.href = 'riwayat_reservasi.php';
    }
}, 30000);

// Update timer setiap detik
setInterval(updateTimers, 1000);
// Update segera saat halaman load
updateTimers();

// Cek status expired setiap 30 detik (untuk memastikan sinkronisasi dengan server)
setInterval(() => {
    const expiredTimers = document.querySelectorAll('.timer-expired');
    if (expiredTimers.length > 0) {
        // Jika ada timer yang expired, reload untuk update dari server
        window.location.reload();
    }
}, 30000); // 30 detik
</script>
</body>
</html