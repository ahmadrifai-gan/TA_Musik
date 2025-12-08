<?php
session_start();
require "config/koneksi.php";

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Cek apakah user sudah di halaman verifikasi dengan session yang valid
if (!isset($_SESSION['verify_email']) || empty($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];
$error = "";
$success = "";
$verified = false;

// Validasi email session dan cek apakah user ada di database
// PERBAIKAN: Menggunakan $koneksi bukan $con
$check_user = $koneksi->prepare("SELECT id_user, nama_lengkap, verification_code, is_verified FROM user WHERE email = ?");
$check_user->bind_param("s", $email);
$check_user->execute();
$check_result = $check_user->get_result();

if ($check_result->num_rows === 0) {
    // Jika user tidak ada di database, redirect ke registrasi
    unset($_SESSION['verify_email']);
    unset($_SESSION['registration_step']);
    unset($_SESSION['verify_user_id']);
    header("Location: register.php?error=user_not_found");
    exit();
}

$user_data = $check_result->fetch_assoc();
$user_id = $user_data['id_user'];
$user_name = $user_data['nama_lengkap'];
$is_verified = $user_data['is_verified'];

// Jika sudah terverifikasi, redirect ke login
if ($is_verified == 1) {
    unset($_SESSION['verify_email']);
    unset($_SESSION['registration_step']);
    unset($_SESSION['verify_user_id']);
    header("Location: login.php?verified=1");
    exit();
}

// Proses verifikasi kode
if (isset($_POST['verifybtn'])) {
    $input_code = trim($_POST['verification_code'] ?? '');

    if ($email === '' || $input_code === '') {
        $error = "❌ Email dan kode wajib diisi.";
    } else {
        // PERBAIKAN: Menggunakan $koneksi bukan $con
        $stmt = $koneksi->prepare("SELECT id_user, verification_code FROM user WHERE email = ? AND is_verified = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['verification_code'] == $input_code) {
                // Update status user jadi verified
                // PERBAIKAN: Menggunakan $koneksi bukan $con
                $update = $koneksi->prepare("UPDATE user SET is_verified = 1, verification_code = NULL WHERE id_user = ?");
                $update->bind_param("i", $row['id_user']);
                $update->execute();
                
                if ($update->affected_rows > 0) {
                    $verified = true;
                    
                    // Hapus session verifikasi
                    unset($_SESSION['verify_email']);
                    unset($_SESSION['registration_step']);
                    unset($_SESSION['verify_user_id']);

                    // Set session success
                    $_SESSION['verification_success'] = true;
                    $_SESSION['verified_email'] = $email;
                    $_SESSION['verified_user_id'] = $user_id;

                    // Redirect ke halaman login dengan pesan sukses
                    header("Location: login.php?verified=1");
                    exit();
                } else {
                    $error = "❌ Gagal memverifikasi akun. Silakan coba lagi.";
                }
                $update->close();
            } else {
                $error = "❌ Kode verifikasi salah! Periksa kembali kode Anda.";
            }
        } else {
            $error = "❌ Email tidak terdaftar atau sudah terverifikasi";
        }
        $stmt->close();
    }
}

// Kirim ulang kode verifikasi
if (isset($_POST['resendbtn'])) {
    // Generate kode baru
    $new_code = sprintf("%06d", mt_rand(1, 999999));
    
    // Update kode di database - HANYA jika user belum terverifikasi
    // PERBAIKAN: Menggunakan $koneksi bukan $con
    $update = $koneksi->prepare("UPDATE user SET verification_code = ? WHERE email = ? AND is_verified = 0");
    $update->bind_param("ss", $new_code, $email);

    if ($update->execute() && $update->affected_rows > 0) {
        // Kirim email verifikasi
        $subject = 'Kode Verifikasi Baru - Reys Studio';

        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; }
                .header { background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .code { font-size: 32px; font-weight: bold; color: #1976d2; text-align: center; margin: 20px 0; letter-spacing: 8px; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Reys Studio</h1>
                    <p>Verifikasi Email Anda</p>
                </div>
                <div class="content">
                    <p>Halo <strong>' . htmlspecialchars($user_name) . '</strong>,</p>
                    <p>Berikut adalah kode verifikasi baru untuk akun Reys Studio Anda:</p>
                    <div class="code">' . $new_code . '</div>
                    <p>Kode ini berlaku selama 1 jam.</p>
                    <div class="warning">
                        <p><strong>⚠ Kode ini berlaku selama 1 jam.</strong></p>
                    </div>
                    <p>Silakan masukkan kode ini pada halaman verifikasi untuk mengaktifkan akun Anda.</p>
                    <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Reys Studio. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Headers untuk HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Reys Studio <noreply@reysmusicstudio.mif.myhost.id>' . "\r\n";
        $headers .= 'Reply-To: noreply@reysmusicstudio.mif.myhost.id' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();

        if (mail($email, $subject, $message, $headers)) {
            $success = "✅ Kode verifikasi baru telah dikirim ke email Anda!";
        } else {
            $error = "❌ Gagal mengirim email. Silakan coba lagi.";
        }
    } else {
        $error = "❌ Akun sudah terverifikasi atau tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Reys Studio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            color: #0f172a;
        }

        body::before, body::after {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(255, 215, 79, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        body::before { top: -220px; right: -180px; }
        body::after { bottom: -220px; left: -180px; }

        .verify-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 480px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
            animation: slideIn 0.4s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-section img {
            width: 82px;
            height: 82px;
            border-radius: 16px;
            margin-bottom: 14px;
            border: 2px solid #fcd34d;
            object-fit: contain;
            background: #fff8e1;
            padding: 8px;
        }

        .logo-section h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .logo-section .text-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-section p {
            color: #475569;
            font-size: 0.95rem;
        }

        .email-info {
            background: #fff8e1;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            margin-bottom: 22px;
        }

        .email-info p {
            color: #6b7280;
            margin: 0 0 6px 0;
            font-size: 0.9rem;
        }

        .email-info strong {
            color: #d97706;
            font-size: 1rem;
            display: block;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.9rem;
            margin-bottom: 18px;
            border: none;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecdd3;
            color: #b91c1c;
        }

        .alert-success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fef08a;
            color: #b45309;
        }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #0f172a;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 10px;
            text-align: center;
            transition: all 0.25s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #fbbf24;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.25);
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            border: none;
            border-radius: 12px;
            color: #0f172a;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-bottom: 12px;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.25);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 34px rgba(249, 115, 22, 0.3);
        }

        .btn-resend {
            width: 100%;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-resend:hover {
            background: #fff;
            border-color: #fbbf24;
            box-shadow: 0 8px 18px rgba(251, 191, 36, 0.2);
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            margin-top: 18px;
            text-align: center;
        }

        .info-box p {
            color: #475569;
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.5;
        }

        .links {
            text-align: center;
            margin-top: 18px;
        }

        .links a {
            color: #d97706;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .links a:hover {
            color: #f59e0b;
            text-decoration: underline;
        }

        .links p {
            color: #475569;
            margin: 8px 0;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .verify-container { padding: 30px 20px; }
            .logo-section h1 { font-size: 1.55rem; }
            .form-control { font-size: 1.3rem; letter-spacing: 8px; }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="logo-section">
            <img src="assets/image/logo2.png" alt="Reys Studio Logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnptMCAxOGMtNC40MSAwLTgtMy41OS04LThzMy41OS04IDgtOCA4IDMuNTkgOCA4LTMuNTkgOC04IDh6bS0yLTMuNWwyLTNjMC0uNTUuNDUtMSAxLTFzMSAuNDUgMSAxbDIgMy41aC00em0zLTkuNWMwLS44My0uNjctMS41LTEuNS0xLjVzLTEuNS42Ny0xLjUgMS41LjY3IDEuNSAxLjUgMS41IDEuNS0uNjcgMS41LTEuNXoiIGZpbGw9IiM0MmE1ZjUiLz48L3N2Zz4=';">
            <h1>Verifikasi <span class="text-primary">Email</span></h1>
            <p>Masukkan kode 6 digit yang dikirim ke email Anda</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'user_not_found'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Data registrasi tidak lengkap. Silakan registrasi ulang.
                <a href="register.php" class="alert-link">Klik di sini untuk registrasi ulang</a>
            </div>
        <?php endif; ?>

        <div class="email-info">
            <p>Kode verifikasi dikirim ke:</p>
            <strong><?= htmlspecialchars($email); ?></strong>
            <?php if (isset($user_name)): ?>
                <p>Nama: <strong><?= htmlspecialchars($user_name); ?></strong></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="verifyForm">
            <div class="form-group">
                <label class="form-label">Kode Verifikasi</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        name="verification_code"
                        class="form-control"
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        inputmode="numeric"
                        value="<?= isset($_POST['verification_code']) ? htmlspecialchars($_POST['verification_code']) : '' ?>">
                </div>
            </div>

            <button type="submit" name="verifybtn" class="btn-verify">
                <i class="fas fa-check"></i> Verifikasi Email
            </button>
        </form>

        <form method="POST" style="margin-top: 15px;">
            <button type="submit" name="resendbtn" class="btn-resend">
                <i class="fas fa-redo"></i> Kirim Ulang Kode
            </button>
        </form>

        <div class="info-box">
            <p><i class="fas fa-lightbulb"></i> Kode verifikasi berlaku selama 1 jam. Periksa folder spam jika tidak menemukan email.</p>
        </div>

        <div class="links">
            <p><a href="register.php"><i class="fas fa-arrow-left"></i> Kembali ke Registrasi</a></p>
            <p><a href="login.php"><i class="fas fa-sign-in-alt"></i> Sudah verifikasi? Login di sini</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto format input kode verifikasi (hanya angka)
        const codeInput = document.querySelector('input[name="verification_code"]');

        codeInput.addEventListener('input', function(e) {
            // Hanya izinkan angka
            this.value = this.value.replace(/[^0-9]/g, '');

            // Auto submit jika sudah 6 digit
            if (this.value.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });

        // Auto focus ke input kode
        codeInput.focus();

        // Auto select existing code
        codeInput.select();

        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>