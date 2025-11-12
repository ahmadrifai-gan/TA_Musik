<?php
session_start();
require "../config/koneksi.php";

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['user_id'];

// Ambil data admin
$stmt = $koneksi->prepare("SELECT * FROM user WHERE id_user = ? AND role = 'admin'");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Hitung statistik
$total_order = $koneksi->query("SELECT COUNT(*) as total FROM booking")->fetch_assoc()['total'];
$total_pelanggan = $koneksi->query("SELECT COUNT(*) as total FROM user WHERE role = 'user'")->fetch_assoc()['total'];
$total_studio = $koneksi->query("SELECT COUNT(*) as total FROM studio")->fetch_assoc()['total'];

$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Reys Studio Musik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .profile-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .profile-header {
            background: linear-gradient(135deg, #0D1321 0%, #1a1d29 100%);
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 5px solid #ffd700;
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
        }

        .profile-avatar i {
            font-size: 70px;
            color: #0D1321;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .profile-role {
            display: inline-block;
            background: #ffd700;
            color: #000;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .profile-body {
            padding: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0D1321;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #ffd700;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #ffd700;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #0D1321;
            display: flex;
            align-items: center;
            gap: 10px;
            word-break: break-word;
        }

        .info-value i {
            color: #ffd700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 200px;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-warning {
            background: #ffd700;
            color: #000;
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: #fff;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .status-verified {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #28a745;
        }

        @media (max-width: 768px) {
            .profile-body {
                padding: 20px;
            }

            .profile-header {
                padding: 30px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                min-width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Dashboard
        </a>

        <div class="profile-card">
            <!-- Header Section -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($admin['nama_lengkap']) ?></h1>
                <span class="profile-role">Administrator</span>
            </div>

            <!-- Body Section -->
            <div class="profile-body">
                <!-- Statistics -->
                <h2 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Statistik Dashboard
                </h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-value"><?= $total_order ?></div>
                        <div class="stat-label">Total Order</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?= $total_pelanggan ?></div>
                        <div class="stat-label">Total Pelanggan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-music"></i>
                        </div>
                        <div class="stat-value"><?= $total_studio ?></div>
                        <div class="stat-label">Studio Aktif</div>
                    </div>
                </div>

                <!-- Personal Information -->
                <h2 class="section-title">
                    <i class="fas fa-id-card"></i>
                    Informasi Pribadi
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($admin['username']) ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">
                            <i class="fas fa-id-badge"></i>
                            <?= htmlspecialchars($admin['nama_lengkap']) ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <i class="fas fa-envelope"></i>
                            <?= htmlspecialchars($admin['email']) ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. WhatsApp</div>
                        <div class="info-value">
                            <i class="fab fa-whatsapp"></i>
                            <?= htmlspecialchars($admin['whatsapp']) ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            <i class="fas fa-shield-alt"></i>
                            <?= ucfirst($admin['role']) ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status Verifikasi</div>
                        <div class="info-value">
                            <?php if ($admin['is_verified'] == 1): ?>
                                <span class="status-verified">
                                    <i class="fas fa-check-circle"></i>
                                    Terverifikasi
                                </span>
                            <?php else: ?>
                                <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                                Belum Terverifikasi
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    Pengaturan Akun
                </h2>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="alert('Fitur Edit Profil akan segera hadir!')">
                        <i class="fas fa-edit"></i>
                        Edit Profil
                    </button>
                    <button class="btn btn-warning" onclick="alert('Fitur Ganti Password akan segera hadir!')">
                        <i class="fas fa-key"></i>
                        Ganti Password
                    </button>
                    <button class="btn btn-danger" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Apakah Anda yakin ingin logout?")) {
                window.location.href = "../logout.php";
            }
        }
    </script>
</body>
</html>

<?php
$koneksi->close();
?>