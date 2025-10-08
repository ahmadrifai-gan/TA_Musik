<?php
session_start();
require "config/koneksi.php";
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$step = 1; // 1 = masukkan email, 2 = masukkan OTP & password baru

// Jika user kirim email untuk reset password
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resetEmail'])) {
    $email = trim($_POST['resetEmail']);
    $stmt = $koneksi->prepare("SELECT id_user, nama_lengkap FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id_user, $nama);
        $stmt->fetch();

        // Buat kode OTP 6 digit
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Simpan ke kolom reset_token
        $update = $koneksi->prepare("UPDATE user SET reset_token = ?, reset_expiry = ? WHERE id_user = ?");
        $update->bind_param("ssi", $otp, $expiry, $id_user);
        $update->execute();
        $update->close();

        // Kirim email OTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'refangga1952@gmail.com'; // ganti dengan email kamu
            $mail->Password   = 'hwfx nsfo kwmy oduj';     // ganti dengan App Password Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('refangga1952@gmail.com', 'Admin Website');
            $mail->addAddress($email, $nama);
            $mail->isHTML(true);
            $mail->Subject = 'Kode OTP Reset Password';
            $mail->Body    = "
                <h3>Halo, $nama</h3>
                <p>Berikut kode OTP untuk reset password akun Anda:</p>
                <h2 style='color:blue;'>$otp</h2>
                <p>Kode ini berlaku selama 10 menit.</p>
            ";
            $mail->send();

            $_SESSION['reset_email'] = $email;
            $message = "Kode OTP telah dikirim ke email Anda.";
            $step = 2;
        } catch (Exception $e) {
            $message = "Gagal mengirim email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "Email tidak ditemukan.";
    }
    $stmt->close();
}

// Jika user kirim OTP & password baru
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    $newPass = $_POST['newPassword'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';

    if ($newPass === '' || $confirm === '') {
        $message = "Semua field wajib diisi.";
        $step = 2;
    } elseif ($newPass !== $confirm) {
        $message = "Konfirmasi password tidak sama.";
        $step = 2;
    } else {
        $stmt = $koneksi->prepare("SELECT id_user, reset_expiry FROM user WHERE email = ? AND reset_token = ?");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_user, $expiry);
            $stmt->fetch();

            if (strtotime($expiry) < time()) {
                $message = "Kode OTP sudah kedaluwarsa.";
                $step = 2;
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $update = $koneksi->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id_user = ?");
                $update->bind_param("si", $newHash, $id_user);
                $update->execute();
                $update->close();

                // Hapus session reset
                unset($_SESSION['reset_email']);

                echo "<script>
                    alert('Password berhasil diperbarui! Silakan login kembali.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            }
        } else {
            $message = "Kode OTP salah atau email tidak cocok.";
            $step = 2;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow p-4" style="width: 24rem; border-radius: 1rem;">
    <h3 class="text-center mb-3">Reset Password</h3>

    <?php if ($message): ?>
      <div class="alert alert-info py-2"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
      <form method="POST">
        <div class="mb-3">
          <label for="resetEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="resetEmail" name="resetEmail" placeholder="Masukkan email Anda" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim OTP</button>
      </form>
    <?php else: ?>
      <form method="POST">
        <div class="mb-3">
          <label for="otp" class="form-label">Kode OTP</label>
          <input type="text" class="form-control" id="otp" name="otp" maxlength="6" required>
        </div>
        <div class="mb-3">
          <label for="newPassword" class="form-label">Password Baru</label>
          <input type="password" class="form-control" id="newPassword" name="newPassword" required>
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Perbarui Password</button>
      </form>
    <?php endif; ?>

  </div>
</body>
</html>
