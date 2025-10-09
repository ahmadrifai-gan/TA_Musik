<?php
session_start();
require "config/koneksi.php";

$login_error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['username'], $_POST['password'])) {
        $login_error = "Form belum lengkap.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Query menyesuaikan kolom database
        $sql = "SELECT id_user, username, password, is_verified, role FROM user WHERE username = ?";
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id_user, $userdb, $hash, $is_verified, $role);
                $stmt->fetch();

                if (!$is_verified) {
                    $login_error = "Akun belum diverifikasi. Silakan cek email untuk kode OTP.";
                } elseif (password_verify($password, $hash)) {
                    $_SESSION['user_id'] = $id_user;
                    $_SESSION['username'] = $userdb;
                    $_SESSION['role'] = $role ?: 'user';

                    // rehash jika perlu
                    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $u = $koneksi->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                        $u->bind_param("si", $newHash, $id_user);
                        $u->execute();
                        $u->close();
                    }

                    if (strtolower($_SESSION['role']) === 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $login_error = "Password salah!";
                }
            } else {
                $login_error = "Username tidak ditemukan!";
            }
            $stmt->close();
        } else {
            $login_error = "Gagal menyiapkan query.";
        }
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow p-4" style="width: 24rem; border-radius: 1rem;">
    <h3 class="text-center mb-4">Login</h3>
    <?php if ($login_error): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    
    <!-- Form Login -->
    <form action="" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" name="username" id="username" placeholder="Masukkan username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan password" required>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Ingat saya</label>
        </div>
        <!-- Trigger modal -->
        <a href="#" class="small" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Lupa Password?</a>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <!-- Tambahan link ke register -->
    <div class="text-center mt-3">
      <small>Belum punya akun? <a href="register.php">Daftar sekarang</a></small>
    </div>
  </div>

  <!-- Modal Lupa Password -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordLabel">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <p class="small">Masukkan email yang terdaftar. Link reset password akan dikirim ke email Anda.</p>
          <!-- Form Reset Password -->
          <form action="resetPass.php" method="POST">
            <div class="mb-3">
              <label for="resetEmail" class="form-label">Email</label>
              <input type="email" class="form-control" name="resetEmail" id="resetEmail" placeholder="nama@email.com" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
