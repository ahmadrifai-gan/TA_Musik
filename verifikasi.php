<?php
session_start();
require "config/koneksi.php";

$verifikasi_msg = "";
$email = $_GET['email'] ?? '';
$kode = "";
$verified = false;

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

                $verifikasi_msg = "success";
                $verified = true;
            } else {
                $verifikasi_msg = "error_code";
            }
        } else {
            $verifikasi_msg = "error_email";
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
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow p-4" style="border-radius:1rem;">
        <h3 class="text-center mb-4">Verifikasi Akun</h3>

        <form action="" method="POST" id="verificationForm">
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
          <a href="login.php" class="btn btn-success w-100 mt-2">
            Kembali ke Login
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  <?php if ($verified && $verifikasi_msg === 'success'): ?>
    // Tampilkan SweetAlert2 saat verifikasi berhasil
    Swal.fire({
      title: "Verifikasi Berhasil!",
      text: "Akun Anda telah berhasil diverifikasi",
      icon: "success",
      draggable: true,
      showConfirmButton: false,
      timer: 2000
    });
    
    // Redirect ke login.php setelah 2 detik
    setTimeout(() => {
      window.location.href = 'login.php';
    }, 2000);
  <?php elseif ($verifikasi_msg === 'error_code'): ?>
    // Tampilkan SweetAlert2 saat kode verifikasi salah
    Swal.fire({
      title: "Kode Salah!",
      text: "Kode verifikasi yang Anda masukkan tidak sesuai",
      icon: "error",
      draggable: true
    });
  <?php elseif ($verifikasi_msg === 'error_email'): ?>
    // Tampilkan SweetAlert2 saat email tidak ditemukan
    Swal.fire({
      title: "Email Tidak Ditemukan!",
      text: "Email tidak terdaftar atau sudah terverifikasi",
      icon: "error",
      draggable: true
    });
  <?php elseif ($verifikasi_msg && $verifikasi_msg !== ''): ?>
    // Error lainnya
    Swal.fire({
      title: "Gagal!",
      text: "<?= htmlspecialchars($verifikasi_msg) ?>",
      icon: "error",
      draggable: true
    });
  <?php endif; ?>
</script>

</body>
</html>