<?php
session_start();
require "config/koneksi.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu!');
        window.location.href='login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil booking terbaru user (seperti file asli)
$query = $koneksi->prepare("
    SELECT 
        b.*,
        s.nama as nama_studio,
        COALESCE(j.tanggal, b.Tanggal) as tanggal_booking,
        COALESCE(j.jam_mulai, SUBSTRING_INDEX(b.jam_booking, '-', 1)) as jam_mulai_booking,
        COALESCE(j.jam_selesai, SUBSTRING_INDEX(b.jam_booking, '-', -1)) as jam_selesai_booking
    FROM booking b
    JOIN studio s ON b.id_studio = s.id_studio
    LEFT JOIN jadwal j ON b.id_jadwal = j.id_jadwal
    WHERE b.id_user = ? 
    ORDER BY b.id_order DESC LIMIT 1
");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<script>
        alert('Tidak ada data booking ditemukan.');
        window.location.href='index.php';
    </script>";
    exit;
}

// Jika booking expired (server-side check)
if ($booking['status_pembayaran'] === 'belum_dibayar' && !empty($booking['expired_at']) && strtotime($booking['expired_at']) < time()) {
    $update = $koneksi->prepare("UPDATE booking SET status = 'dibatalkan' WHERE id_order = ?");
    $update->bind_param("i", $booking['id_order']);
    $update->execute();
    
    echo "<script>
        alert('⏰ Booking Anda telah EXPIRED!\\n\\nBooking telah dibatalkan karena tidak ada pembayaran DP dalam 2 jam.\\n\\nSilakan booking ulang.');
        window.location.href='index.php';
    </script>";
    exit;
}

// Proses konfirmasi (tombol Konfirmasi pada halaman)
if (isset($_POST['konfirmasi'])) {
    unset($_SESSION['booking_data']);
    // Jika konfirmasi berarti user setuju dan admin akan verifikasi
    echo "<script>
        alert('✅ Booking berhasil dikonfirmasi!\\n\\nData reservasi Anda sudah tersimpan.\\nSilakan cek di Riwayat Reservasi.');
        window.location.href='riwayat_reservasi.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Poppins', sans-serif; 
        }
        .card { 
            border-radius: 15px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            background-color: #000; 
            color: white; 
            padding: 12px 20px; 
            border-radius: 10px; 
            display: inline-block; 
        }
        .btn-yellow { 
            background-color: #FFD700; 
            color: #000; 
            font-weight: 600; 
            border: none; 
        }
        .btn-yellow:hover { 
            background-color: #e6c200; 
            color: #000;
        }
        .paket-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .paket-bronze-tanpa { background-color: #e8daef; color: #633974; }
        .paket-bronze-dengan { background-color: #d7bde2; color: #4a235a; }
        .paket-gold-reguler { background-color: #fff9e6; color: #b8860b; }
        .paket-gold-2jam { background-color: #ffe680; color: #856404; }
        .paket-gold-3jam { background-color: #ffcc00; color: #664d00; }
        
        .bukti-preview {
            max-width: 300px;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            background: #f9f9f9;
        }
        .bukti-preview img {
            max-width: 100%;
            border-radius: 8px;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .status-menunggu {
            background-color: #fff3cd;
            color: #856404;
        }
        .info-alert {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* STYLE TIMER */
        .timer-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.3);
        }
        .timer-display {
            font-size: 2.4rem;
            font-weight: bold;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .qr-section {
            background: linear-gradient(135deg, #fff9e6 0%, #ffe680 100%);
            border: 2px dashed #FFB300;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        .qr-code img { max-width: 260px; border-radius: 8px; }
        .file-preview { max-width: 220px; max-height: 220px; object-fit: contain; border-radius: 8px; border:1px solid #ddd; padding:6px; background:#fff; }
        .small-muted { font-size: 0.85rem; color:#666; }
    </style>
</head>
<body>

<div class="container my-5">
    <h3 class="header mb-4"><i class="bi bi-check-circle"></i> Konfirmasi Booking</h3>
    
    <!-- TIMER COUNTDOWN (HANYA JIKA BELUM BAYAR DP) -->
    <?php if ($booking['status_pembayaran'] === 'belum_dibayar' && !empty($booking['expired_at'])): ?>
    <div class="timer-box timer-warning" id="timerBox">
        <h5><i class="bi bi-alarm"></i> Booking Akan Expire Dalam:</h5>
        <div class="timer-display" id="countdown">
            <span id="hours">00</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
        </div>
        <small>Segera upload DP untuk mengamankan booking Anda!</small>
    </div>
    <?php endif; ?>
    
    <div class="info-alert">
        <i class="bi bi-info-circle-fill text-primary"></i>
        <strong>Informasi:</strong> Silakan periksa kembali detail booking Anda sebelum melakukan konfirmasi final.
    </div>
    
    <div class="card p-4">
        <h5 class="mb-3"><i class="bi bi-clipboard-check"></i> Detail Reservasi Anda</h5>
        <table class="table table-borderless">
            <tr>
                <th style="width: 200px;">ID Order</th>
                <td><span class="badge bg-dark">#<?= htmlspecialchars($booking['id_order']) ?></span></td>
            </tr>
            <tr>
                <th>Studio</th>
                <td><strong><?= htmlspecialchars($booking['nama_studio']) ?></strong></td>
            </tr>
            
            <!-- PAKET -->
            <tr>
                <th>Paket</th>
                <td>
                    <?php
                    $paket = $booking['paket'] ?? '';
                    $namaStudio = $booking['nama_studio'] ?? '';
                    
                    if (empty($paket)) {
                        echo '<span class="text-muted">Tidak ada info paket</span>';
                    } else {
                        $classPaket = 'paket-badge';
                        $paketText = htmlspecialchars($paket);
                        
                        if (stripos($namaStudio, 'bronze') !== false) {
                            if (stripos($paket, 'tanpa') !== false || stripos($paket, '35') !== false) {
                                $classPaket .= ' paket-bronze-tanpa';
                                $paketText = 'Tanpa Keyboard (35K/jam)';
                            } elseif (stripos($paket, 'dengan') !== false || stripos($paket, '40') !== false) {
                                $classPaket .= ' paket-bronze-dengan';
                                $paketText = 'Dengan Keyboard (40K/jam)';
                            }
                        }
                        elseif (stripos($namaStudio, 'gold') !== false) {
                            if (stripos($paket, 'reguler') !== false || stripos($paket, '50') !== false) {
                                $classPaket .= ' paket-gold-reguler';
                                $paketText = 'Reguler (50K/jam)';
                            } elseif (stripos($paket, '2 jam') !== false || stripos($paket, '90') !== false) {
                                $classPaket .= ' paket-gold-2jam';
                                $paketText = 'Paket 2 jam (90K)';
                            } elseif (stripos($paket, '3 jam') !== false || stripos($paket, '130') !== false) {
                                $classPaket .= ' paket-gold-3jam';
                                $paketText = 'Paket 3 jam (130K)';
                            }
                        }
                        
                        echo "<span class='$classPaket'>$paketText</span>";
                    }
                    ?>
                </td>
            </tr>
            
            <tr>
                <th>Tanggal</th>
                <td><i class="bi bi-calendar-event"></i> <?= date('d F Y', strtotime($booking['tanggal_booking'])) ?></td>
            </tr>
            <tr>
                <th>Jam</th>
                <td><i class="bi bi-clock"></i> <?= htmlspecialchars($booking['jam_booking']) ?></td>
            </tr>
            <tr>
                <th>Total Biaya</th>
                <td><strong class="text-success">Rp <?= number_format($booking['total_tagihan'], 0, ',', '.') ?></strong></td>
            </tr>
            <tr>
                <th>Status Pembayaran</th>
                <td>
                    <?php if ($booking['status_pembayaran'] === 'dp_dibayar'): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> DP Sudah Dibayar</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Belum Dibayar</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Status Booking</th>
                <td><span class="status-badge status-menunggu"><?= htmlspecialchars($booking['status']) ?></span></td>
            </tr>
        </table>

        <!-- TAMPILKAN BUKTI DP JIKA ADA -->
        <?php if (!empty($booking['bukti_dp'])): ?>
        <div class="mt-3 p-3 bg-light rounded">
            <h6 class="mb-2"><i class="bi bi-image"></i> Bukti Pembayaran DP</h6>
            <div class="bukti-preview">
                <?php
                $file_ext = strtolower(pathinfo($booking['bukti_dp'], PATHINFO_EXTENSION));
                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])):
                ?>
                    <img src="uploads/bukti_dp/<?= htmlspecialchars($booking['bukti_dp']) ?>" alt="Bukti DP" class="img-fluid">
                <?php else: ?>
                    <div class="text-center p-3">
                        <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                        <p class="mb-0 mt-2">File PDF</p>
                    </div>
                <?php endif; ?>
                <a href="uploads/bukti_dp/<?= htmlspecialchars($booking['bukti_dp']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 mt-2">
                    <i class="bi bi-eye"></i> Lihat File Lengkap
                </a>
            </div>
        </div>
        <?php endif; ?>

        <hr class="my-4">

        <!-- QRIS + UPLOAD FORM -->
        <div class="row">
            <div class="col-md-6">
                <div class="qr-section">
                    <div class="d-flex justify-content-center align-items-center mb-2 gap-3">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/QRIS_logo.svg/2560px-QRIS_logo.svg.png" alt="QRIS Logo" style="height: 36px;">
                        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/8/8b/Logo_GPN.svg/1200px-Logo_GPN.svg.png" alt="GPN Logo" style="height: 42px;">
                    </div>
                    <h5 style="color:#000; font-weight:700; margin-bottom:4px;">REYS MUSIC STUDIO</h5>
                    <p class="small-muted mb-2">NMID: <strong>ID1024362031436</strong> &nbsp; | &nbsp; A01</p>

                    <div class="qr-code mb-3">
                        <img src="img/qris.jpg" alt="QRIS Reys Music Studio">
                    </div>

                    <p class="small-muted mb-1">Scan QRIS di atas lalu transfer DP sebesar <strong>Rp20.000</strong></p>
                    <p class="small-muted mb-0">Setelah transfer: simpan bukti pembayaran lalu upload melalui form di samping.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded">
                    <h6 class="mb-3"><i class="bi bi-cloud-upload"></i> Upload Bukti DP</h6>

                    <?php if ($booking['status_pembayaran'] === 'belum_dibayar' && $booking['status'] !== 'dibatalkan'): ?>
                        <form action="upload_dp.php" method="post" enctype="multipart/form-data" id="formUploadDP">
                            <input type="hidden" name="id_order" value="<?= (int)$booking['id_order'] ?>">
                            <!-- Jika datang dari riwayat mungkin flagged, saya sertakan dari_riwayat ketika session ada -->
                            <?php if (isset($_SESSION['from_riwayat']) && $_SESSION['from_riwayat'] === true): ?>
                                <input type="hidden" name="from_riwayat" value="1">
                            <?php endif; ?>

                            <div class="mb-2">
                                <label for="bukti_dp" class="form-label">Pilih File Bukti (JPG/PNG/PDF max 5MB)</label>
                                <input type="file" name="bukti_dp" id="bukti_dp" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>

                            <div class="mb-2 d-flex gap-2 align-items-center">
                                <div id="previewBox" style="display:none;">
                                    <img id="previewImg" class="file-preview" src="#" alt="Preview">
                                </div>
                                <div id="previewPdf" style="display:none;">
                                    <i class="bi bi-file-earmark-pdf" style="font-size:2.2rem;color:#d32f2f;"></i>
                                    <div class="small-muted">File PDF</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-yellow w-100 mb-2">
                                <i class="bi bi-cloud-upload"></i> Upload Bukti & Simpan
                            </button>

                            <div class="small-muted text-center">
                                Atau jika ingin melewati dan konfirmasi tanpa DP, klik tombol <strong>Lewati</strong> di bawah.
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle"></i> Status saat ini: <strong><?= htmlspecialchars($booking['status_pembayaran']) ?></strong>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-3">
                        <button type="submit" name="konfirmasi" class="btn btn-secondary w-100">
                            <i class="bi bi-check-lg"></i> Konfirmasi Booking (tanpa DP / sudah upload)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Catatan:</strong> Setelah upload, sistem akan menyimpan bukti DP dan status akan menjadi <em>DP Terbayar</em>. Admin akan melakukan verifikasi.
        </div>

        <div class="d-flex justify-content-between gap-2">
            <a href="upload_dp.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Halaman Upload (Alternatif)
            </a>
            <a href="riwayat_reservasi.php" class="btn btn-outline-primary">
                <i class="bi bi-clock-history"></i> Lihat Riwayat Reservasi
            </a>
        </div>

    </div>
</div>

<!-- TIMER SCRIPT -->
<?php if ($booking['status_pembayaran'] === 'belum_dibayar' && !empty($booking['expired_at'])): ?>
<script>
// sinkronisasi server time
const serverNow = <?= time() * 1000 ?>;
const expiredAt = <?= strtotime($booking['expired_at']) * 1000 ?>;
const timeDiff = Date.now() - serverNow;

const countdown = setInterval(() => {
    const now = Date.now() - timeDiff;
    const distance = expiredAt - now;

    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    if (distance >= 0) {
        document.getElementById("hours").innerText = String(hours).padStart(2, '0');
        document.getElementById("minutes").innerText = String(minutes).padStart(2, '0');
        document.getElementById("seconds").innerText = String(seconds).padStart(2, '0');
    }

    if (distance < 0) {
        clearInterval(countdown);
        alert('⏰ WAKTU HABIS!\\n\\nBooking Anda telah dibatalkan karena tidak ada pembayaran DP dalam 2 jam.\\n\\nSlot booking telah dibuka kembali untuk user lain.');
        window.location.href = 'index.php';
    }

    if (distance < 600000 && distance > 599000) {
        document.getElementById("timerBox").style.background = "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)";
        // jangan spam alert setiap detik, jadi cuma cek kondisi awal (di atas)
    }
}, 1000);
</script>
<?php endif; ?>

<!-- PREVIEW FILE JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('bukti_dp')?.addEventListener('change', function(e) {
    const file = this.files[0];
    const previewBox = document.getElementById('previewBox');
    const previewImg = document.getElementById('previewImg');
    const previewPdf = document.getElementById('previewPdf');

    if (!file) {
        previewBox.style.display = 'none';
        previewPdf.style.display = 'none';
        return;
    }

    const ext = file.name.split('.').pop().toLowerCase();
    if (['jpg','jpeg','png','gif'].includes(ext)) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            previewBox.style.display = 'inline-block';
            previewPdf.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else if (ext === 'pdf') {
        previewBox.style.display = 'none';
        previewPdf.style.display = 'inline-block';
    } else {
        previewBox.style.display = 'none';
        previewPdf.style.display = 'none';
    }
});
</script>
</body>
</html>
