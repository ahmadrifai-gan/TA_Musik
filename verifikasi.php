<?php
session_start();
require "config/koneksi.php";
// $host="localhost"; $user="root"; $pass=""; $db="db_login";
// $conn = new mysqli($host, $user, $pass, $db);
// if ($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

$verifikasi_msg = "";
$email = $_GET['email'] ?? '';  // ambil email dari URL
$kode = "";
$verified = false; // status berhasil verifikasi

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $kode  = trim($_POST['kode'] ?? '');

    if ($email === '' || $kode === '') {
        $verifikasi_msg = "Email dan kode wajib diisi.";
    } else {
        $stmt = $koneksi->prepare("SELECT id_user, verification_code FROM user WHERE email = ? AND is_verified = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['verification_code'] == $kode) {
                // update status user jadi verified
                $update = $koneksi->prepare("UPDATE user SET is_verified = 1 WHERE id_user = ?");
                $update->bind_param("i", $row['id_user']);
                $update->execute();
                $update->close();

                $verifikasi_msg = "✅ Verifikasi berhasil! Silakan login.";
                $verified = true; // tandai sudah diverifikasi
            } else {
                $verifikasi_msg = "❌ Kode verifikasi salah.";
            }
        } else {
            $verifikasi_msg = "Email tidak ditemukan atau sudah terverifikasi.";
        }
        $stmt->close();
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Akun</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow p-4" style="border-radius:1rem;">
        <h3 class="text-center mb-4">Verifikasi Akun</h3>

        <?php if ($verifikasi_msg): ?>
          <div class="alert alert-info py-2"><?= htmlspecialchars($verifikasi_msg) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required 
                   value="<?= htmlspecialchars($email) ?>" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Kode Verifikasi</label>
            <input type="text" class="form-control" name="kode" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
        </form>

        <div class="text-center mt-3">
          <!-- Tombol login disabled sebelum verifikasi -->
          <a href="login.php" class="btn btn-success w-100 mt-2 <?= $verified ? '' : 'disabled' ?>">
            Login
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
