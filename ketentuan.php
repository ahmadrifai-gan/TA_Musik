<?php
session_start();
require "config/koneksi.php";

// ===== BAGIAN BARU: CEK APAKAH DARI RIWAYAT RESERVASI =====
if (isset($_GET['id_order']) && !empty($_GET['id_order'])) {
    $id_order = (int)$_GET['id_order'];
    
    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        echo "<script>
            alert('Anda harus login terlebih dahulu!');
            window.location.href='login.php';
        </script>";
        exit;
    }
    
    $id_user = $_SESSION['user_id'];
    
    // Ambil data booking berdasarkan id_order
    $stmt = $koneksi->prepare("SELECT * FROM booking WHERE id_order = ? AND id_user = ? LIMIT 1");
    $stmt->bind_param("ii", $id_order, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking_data = $result->fetch_assoc();
        $_SESSION['booking_data'] = $booking_data;
        $_SESSION['from_riwayat'] = true; // Tandai bahwa ini dari riwayat
    } else {
        echo "<script>
            alert('Data booking tidak ditemukan!');
            window.location.href='riwayat_reservasi.php';
        </script>";
        exit;
    }
}

// Cek session booking
if (!isset($_SESSION['booking_data'])) {
    header("Location: index.php");
    exit;
}

$booking = $_SESSION['booking_data'];

// 🔥 HANDLE TOMBOL LEWATI - LANGSUNG KE KONFIRMASI
if (isset($_POST['lewati'])) {
    // Update status booking menjadi 'menunggu' tanpa DP
    if (isset($booking['id_order'])) {
        $id_order = (int)$booking['id_order'];
        $stmt = $koneksi->prepare("UPDATE booking SET status = 'menunggu', status_pembayaran = 'belum_dibayar' WHERE id_order = ?");
        $stmt->bind_param("i", $id_order);
        $stmt->execute();
        $stmt->close();
    }
    
    // Jika dari riwayat, redirect ke riwayat
    if (isset($_SESSION['from_riwayat']) && $_SESSION['from_riwayat'] === true) {
        unset($_SESSION['from_riwayat']);
        header("Location: riwayat_reservasi.php");
    } else {
        // Redirect ke konfirmasi booking
        header("Location: konfirmasi_booking.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - Reys Music Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #FFD54F 0%, #FFB300 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            padding: 20px 0;
        }
        .container {
            max-width: 800px;
        }
        .card {
            background-color: #fffbea;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h3 {
            color: #ff9800;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .syarat-item {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #FFB300;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .syarat-item h5 {
            color: #FF8F00;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .syarat-item ol, .syarat-item ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .syarat-item li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px dashed #ffc107;
        }
        .highlight strong {
            color: #ff6b00;
        }
        .qr-section {
            background: linear-gradient(135deg, #fff9e6 0%, #ffe680 100%);
            border: 3px dashed #FFB300;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
        .qr-code {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            margin: 20px 0;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #FFD54F, #FFB300);
            border: none;
            color: #4e342e;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #FFCA28, #FFA000);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 179, 0, 0.4);
            color: #3e2723;
        }
        .btn-secondary-custom {
            background-color: #6c757d;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-secondary-custom:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #795548;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h3><i class="bi bi-info-circle"></i> Syarat & Ketentuan Booking</h3>

        <div class="syarat-item">
            <h5><i class="bi bi-exclamation-triangle-fill text-warning"></i> Syarat & Ketentuan:</h5>
            <ol>
                <li><strong>DP (Down Payment)</strong> minimal <span style="color: #d32f2f; font-weight: 700;">Rp20.000</span> melalui QRIS untuk mengamankan jadwal.</li>
                <li>Pelunasan dilakukan langsung di studio.</li>
                <li>Waktu booking dihitung mulai dari jam yang sudah disepakati.</li>
                <li>Jika datang terlambat, waktu tetap berjalan sesuai jadwal booking.</li>
                <li>Reschedule (ubah jadwal) hanya bisa dilakukan maksimal <strong>H-1</strong> dari jadwal booking.</li>
                <li>Dilarang membawa makanan/minuman ke dalam ruangan studio.</li>
                <li>Bertanggung jawab atas peralatan studio selama penggunaan (apabila ada kerusakan akan dikenakan biaya ganti rugi).</li>
            </ol>
        </div>

        <div class="highlight">
            <i class="bi bi-info-circle-fill text-warning"></i>
            <strong>Penting!</strong> Silakan scan QR Code untuk melakukan pembayaran DP sebesar <strong>Rp20.000</strong>
        </div>

        <div class="qr-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/QRIS_logo.svg/2560px-QRIS_logo.svg.png" alt="QRIS Logo" style="height: 40px;">
                <img src="https://upload.wikimedia.org/wikipedia/id/thumb/8/8b/Logo_GPN.svg/1200px-Logo_GPN.svg.png" alt="GPN Logo" style="height: 50px;">
            </div>
            
            <h4 style="color: #000; font-weight: 700; margin-bottom: 5px;">
                REYS MUSIC STUDIO
            </h4>
            <p style="font-weight: 600; color: #6d4c41; margin-bottom: 5px;">NMID: ID1024362031436</p>
            <p style="font-weight: 600; color: #6d4c41; margin-bottom: 20px;">A01</p>
            
            <div class="qr-code">
                <!-- QR Code QRIS Reys Music Studio -->
                <img src="img/qris.jpg" alt="QRIS Reys Music Studio" style="max-width: 280px; width: 100%; height: auto; border-radius: 8px;">
            </div>
            
            <h5 style="color: #FF8F00; font-weight: 700; margin-top: 20px;">
                SATU QRIS UNTUK SEMUA
            </h5>
            <p style="font-size: 0.9rem; color: #6d4c41; margin-bottom: 15px;">
                Cek aplikasi penyelenggara di:<br>
                <a href="https://www.aspi-qris.id" target="_blank" style="color: #007bff; font-weight: 600;">www.aspi-qris.id</a>
            </p>
            
            <div class="d-flex justify-content-center gap-3 mt-3">
                <div class="text-center" style="font-size: 0.75rem;">
                    <i class="bi bi-phone" style="font-size: 1.5rem; color: #FF8F00;"></i>
                    <p class="mb-0 mt-1">Buka Aplikasi<br>Berlogo QRIS</p>
                </div>
                <div class="text-center" style="font-size: 0.75rem;">
                    <i class="bi bi-qr-code-scan" style="font-size: 1.5rem; color: #FF8F00;"></i>
                    <p class="mb-0 mt-1">Scan dan Cek<br>Nominal</p>
                </div>
                <div class="text-center" style="font-size: 0.75rem;">
                    <i class="bi bi-credit-card-2-front" style="font-size: 1.5rem; color: #FF8F00;"></i>
                    <p class="mb-0 mt-1">Bayar</p>
                </div>
            </div>
            
            <p style="font-size: 0.7rem; color: #999; margin-top: 15px; margin-bottom: 0;">
                Dicetak oleh: 93600915<br>
                Versi cetak: 1.0.21.03.25
            </p>
        </div>

        <div class="alert alert-info mt-3" role="alert">
            <i class="bi bi-question-circle-fill"></i> 
            <strong>Apakah Anda ingin melakukan DP booking?</strong>
        </div>

        <div class="button-group">
            <form method="post" style="display: inline;">
                <button type="submit" name="lewati" class="btn btn-secondary-custom">
                    <i class="bi bi-skip-forward"></i> Lewati
                </button>
            </form>
            
            <a href="upload_dp.php" class="btn btn-primary-custom">
                <i class="bi bi-cloud-upload"></i> Upload Bukti DP
            </a>
        </div>

        <div class="back-link">
            <?php if (isset($_SESSION['from_riwayat']) && $_SESSION['from_riwayat'] === true): ?>
                <a href="riwayat_reservasi.php">
                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Riwayat Reservasi
                </a>
            <?php else: ?>
                <a href="booking.php?studio=<?= $_SESSION['booking_data']['id_studio'] == 'ST001' ? 'bronze' : 'gold' ?>">
                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Halaman Booking
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>