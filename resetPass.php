<?php
session_start();
require "config/koneksi.php";

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

$message = "";
$step = 1; // 1 = masukkan email, 2 = masukkan kode verifikasi, 3 = password baru

// Jika user kirim email untuk reset password
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resetEmail'])) {
    $email = trim($_POST['resetEmail']);
    
    // Cek apakah email ada di database
    $stmt = $koneksi->prepare("SELECT id_user, nama_lengkap FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id_user, $nama);
        $stmt->fetch();

        // Generate kode verifikasi 6 digit
        $verification_code = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Simpan ke kolom reset_token (kita gunakan reset_token untuk menyimpan kode)
        $update = $koneksi->prepare("UPDATE user SET reset_token = ?, reset_espiry = ? WHERE id_user = ?");
        $update->bind_param("ssi", $verification_code, $expiry, $id_user);
        
        if ($update->execute()) {
            // Kirim email menggunakan PHP mail() function (sama seperti register)
            $subject = 'Kode Reset Password - Reys Studio';
            
            // HTML Email content
            $message_content = '
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
                        <p>Reset Password</p>
                    </div>
                    <div class="content">
                        <p>Halo <strong>' . htmlspecialchars($nama) . '</strong>,</p>
                        <p>Kami menerima permintaan reset password untuk akun Anda. Gunakan kode verifikasi berikut:</p>
                        <div class="code">' . $verification_code . '</div>
                        <p>Kode ini berlaku selama 10 menit.</p>
                        <div class="warning">
                            <p><strong>⚠ Kode ini berlaku selama 10 menit.</strong></p>
                            <p><strong>⚠ Jika Anda tidak meminta reset password, abaikan email ini.</strong></p>
                        </div>
                        <p>Silakan masukkan kode ini pada halaman reset password untuk melanjutkan.</p>
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

            // Kirim email
            if (mail($email, $subject, $message_content, $headers)) {
                // Simpan data ke session
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $id_user;
                $message = "✅ Kode verifikasi telah dikirim ke email Anda. Silakan cek email Anda.";
                $step = 2; // Pindah ke step 2 (masukkan kode)
            } else {
                $message = "❌ Gagal mengirim email. Silakan coba lagi.";
            }
        } else {
            $message = "❌ Gagal menyimpan kode verifikasi. Silakan coba lagi.";
        }
        $update->close();
    } else {
        $message = "❌ Email tidak ditemukan. Pastikan email yang Anda masukkan benar.";
    }
    $stmt->close();
}

// Jika user kirim kode verifikasi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $input_code = trim($_POST['verification_code']);
    $email = $_SESSION['reset_email'] ?? '';
    
    if (empty($email)) {
        $message = "❌ Sesi telah berakhir. Silakan mulai ulang.";
        $step = 1;
    } else {
        // Cek kode verifikasi dan expiry
        $stmt = $koneksi->prepare("SELECT id_user, reset_espiry FROM user WHERE email = ? AND reset_token = ?");
        $stmt->bind_param("ss", $email, $input_code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_user, $expiry);
            $stmt->fetch();
            
            // Cek apakah kode masih berlaku
            $current_time = time();
            $expiry_time = strtotime($expiry);
            
            if ($expiry_time < $current_time) {
                $message = "❌ Kode verifikasi sudah kadaluarsa. Silakan minta kode baru.";
                $step = 1;
            } else {
                // Kode valid, lanjut ke step 3 (password baru)
                $_SESSION['reset_verified'] = true;
                $message = "✅ Kode verifikasi benar. Silakan masukkan password baru.";
                $step = 3;
            }
        } else {
            $message = "❌ Kode verifikasi salah. Silakan coba lagi.";
            $step = 2;
        }
        $stmt->close();
    }
}

// Jika user kirim password baru
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';
    
    // Validasi
    if (empty($new_password) || empty($confirm_password)) {
        $message = "❌ Semua field wajib diisi.";
        $step = 3;
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Konfirmasi password tidak sama.";
        $step = 3;
    } elseif (!isset($_SESSION['reset_verified']) || !$_SESSION['reset_verified']) {
        $message = "❌ Verifikasi belum dilakukan. Silakan mulai ulang.";
        $step = 1;
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_verified']);
    } else {
        // Hash password baru
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password di database dan hapus token
        $update = $koneksi->prepare("UPDATE user SET password = ?, reset_token = '', reset_espiry = NULL WHERE email = ?");
        $update->bind_param("ss", $new_hash, $email);
        
        if ($update->execute()) {
            // Hapus semua session reset
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_verified']);
            unset($_SESSION['reset_user_id']);
            
            // Tampilkan pesan sukses dan redirect ke login
            echo "<script>
                alert('✅ Password berhasil diperbarui! Silakan login dengan password baru Anda.');
                window.location.href = 'login.php';
            </script>";
            exit();
        } else {
            $message = "❌ Gagal memperbarui password. Silakan coba lagi.";
            $step = 3;
        }
        $update->close();
    }
}

// Reset session jika user kembali ke awal
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_verified']);
    unset($_SESSION['reset_user_id']);
    $step = 1;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Reys Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-container {
            background: rgba(13, 27, 42, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 480px;
            border: 1px solid rgba(66, 165, 245, 0.2);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .logo-section p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .alert {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-info {
            background: rgba(66, 165, 245, 0.15);
            border: 1px solid rgba(66, 165, 245, 0.3);
            color: #93c5fd;
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        
        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            background: rgba(25, 118, 210, 0.05);
            border: 1px solid rgba(66, 165, 245, 0.2);
            border-radius: 10px;
            color: #fff;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(25, 118, 210, 0.08);
            border-color: #42a5f5;
            box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.1);
            color: #fff;
        }
        
        .form-control.code-input {
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 10px;
            text-align: center;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.3);
        }
        
        .btn-secondary {
            width: 100%;
            padding: 14px;
            background: rgba(66, 165, 245, 0.1);
            border: 1px solid rgba(66, 165, 245, 0.3);
            border-radius: 10px;
            color: #42a5f5;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: rgba(66, 165, 245, 0.2);
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #42a5f5;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .links a:hover {
            color: #64b5f6;
            text-decoration: underline;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: rgba(66, 165, 245, 0.3);
            z-index: 0;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(66, 165, 245, 0.2);
            color: #42a5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            border: 2px solid rgba(66, 165, 245, 0.3);
        }
        
        .step.active .step-number {
            background: #42a5f5;
            color: #fff;
            border-color: #42a5f5;
        }
        
        .step-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
        
        .step.active .step-label {
            color: #42a5f5;
            font-weight: 600;
        }
        
        @media (max-width: 576px) {
            .reset-container {
                padding: 30px 20px;
            }
            .form-control.code-input {
                font-size: 1.2rem;
                letter-spacing: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo-section">
            <h2>Reset Password</h2>
            <p>Lupa password? Kami akan membantu Anda</p>
        </div>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?= $step == 1 ? 'active' : '' ?>">
                <div class="step-number">1</div>
                <div class="step-label">Email</div>
            </div>
            <div class="step <?= $step == 2 ? 'active' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Verifikasi</div>
            </div>
            <div class="step <?= $step == 3 ? 'active' : '' ?>">
                <div class="step-number">3</div>
                <div class="step-label">Password Baru</div>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : (strpos($message, '❌') !== false ? 'alert-danger' : 'alert-info') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <!-- Step 1: Input Email -->
        <?php if ($step == 1): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Terdaftar</label>
                    <input type="email" class="form-control" name="resetEmail" 
                           placeholder="Masukkan email yang terdaftar" required>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Kode Verifikasi
                </button>
            </form>
        <?php endif; ?>
        
        <!-- Step 2: Input Verification Code -->
        <?php if ($step == 2): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kode Verifikasi</label>
                    <input type="text" class="form-control code-input" name="verification_code" 
                           placeholder="000000" maxlength="6" pattern="[0-9]{6}" required
                           autocomplete="one-time-code" inputmode="numeric">
                    <small class="text-muted" style="color: rgba(255,255,255,0.6) !important; display: block; margin-top: 5px;">
                        <i class="fas fa-info-circle"></i> Masukkan 6 digit kode yang dikirim ke email Anda
                    </small>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Verifikasi Kode
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='?reset=1'">
                    <i class="fas fa-redo"></i> Gunakan Email Lain
                </button>
            </form>
        <?php endif; ?>
        
        <!-- Step 3: Input New Password -->
        <?php if ($step == 3): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" class="form-control" name="new_password" 
                           placeholder="Masukkan password baru" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="confirm_password" 
                           placeholder="Ulangi password baru" required minlength="6">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Password Baru
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='?reset=1'">
                    <i class="fas fa-times"></i> Batalkan
                </button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <p><a href="login.php"><i class="fas fa-arrow-left"></i> Kembali ke Login</a></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto format untuk input kode verifikasi
        const codeInput = document.querySelector('input[name="verification_code"]');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                // Hanya izinkan angka
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto submit jika sudah 6 digit
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Auto focus
            codeInput.focus();
        }
        
        // Password strength indicator (opsional)
        const newPassword = document.querySelector('input[name="new_password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        if (newPassword && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#ef4444';
                } else {
                    confirmPassword.style.borderColor = '#22c55e';
                }
            });
        }
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>