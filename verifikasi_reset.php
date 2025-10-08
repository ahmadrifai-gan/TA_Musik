<?php
require "config/koneksi.php";

$email = $_GET['email'] ?? '';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($otp === '' || $newPass === '' || $confirm === '') {
        $msg = "Semua kolom wajib diisi!";
    } elseif ($newPass !== $confirm) {
        $msg = "Konfirmasi password tidak cocok!";
    } else {
        $stmt = $koneksi->prepare("SELECT reset_token, reset_expiry FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['reset_token'] == $otp && strtotime($row['reset_expiry']) > time()) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $update = $koneksi->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
                $update->bind_param("ss", $newHash, $email);
                $update->execute();
                $update->close();
                $msg = "✅ Password berhasil direset! Silakan login.";
            } else {
                $msg = "Kode OTP salah atau sudah kedaluwarsa.";
            }
        } else {
            $msg = "Email tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password dengan OTP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="card shadow p-4" style="width: 25rem; border-radius: 1rem;">
  <h4 class="text-center mb-3">Reset Password</h4>

  <?php if ($msg): ?>
    <div class="alert alert-info py-2"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <div class="mb-3">
      <label class="form-label">Kode OTP</label>
      <input type="text" name="otp" maxlength="6" class="form-control text-center" placeholder="Masukkan kode OTP" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password Baru</label>
      <input type="password" name="new_password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Konfirmasi Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Ubah Password</button>
  </form>
</div>
</body>
</html>
