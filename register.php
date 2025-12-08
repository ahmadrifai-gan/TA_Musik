<?php
session_start();
require "config/koneksi.php";

// Pastikan koneksi berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$register_msg = "";
$nama = $email = $username = $whatsapp = "";

// === Load PHPMailer via Composer ===
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($nama === '' || $email === '' || $username === '' || $password === '' || $confirm === '' || $whatsapp === '') {
        $register_msg = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_msg = "Format email tidak valid.";
    } elseif ($password !== $confirm) {
        $register_msg = "Konfirmasi password tidak sama!";
    } else {
        $stmt = $koneksi->prepare("SELECT id_user FROM user WHERE username = ? OR email = ?");
        if ($stmt === false) {
            $register_msg = "Gagal menyiapkan statement: " . $koneksi->error;
        } else {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $register_msg = "Username atau email sudah terdaftar!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $verification_code = rand(100000, 999999);

                // Tambahkan reset_token jika diperlukan oleh tabel
                $reset_token = ""; // Atau gunakan bin2hex(random_bytes(16)) untuk token acak
                $insert = $koneksi->prepare("INSERT INTO user (nama_lengkap, username, password, email, whatsapp, verification_code, is_verified, reset_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($insert === false) {
                    $register_msg = "Gagal menyiapkan insert: " . $koneksi->error;
                } else {
                    $is_verified = 0; // 0 = belum terverifikasi, 1 = sudah terverifikasi
                    $insert->bind_param("ssssssis", $nama, $username, $hash, $email, $whatsapp, $verification_code, $is_verified, $reset_token);

                    if ($insert->execute()) {
                        // kirim email
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'nisrinafirdaus02@gmail.com'; // ganti dengan email Anda
                            $mail->Password   = 'lurk svtg ihli uyhr';     // ganti dengan App Password Gmail
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;

                            $mail->setFrom('nisrinafirdaus02@gmail.com', 'Admin Reys Studio Music');
                            $mail->addAddress($email, $nama);

                            $mail->isHTML(true);
                            $mail->Subject = 'Kode Verifikasi Akun Anda';
                            $mail->Body    = "
                                <h3>Halo, $nama</h3>
                                <p>Terima kasih telah mendaftar. Berikut kode verifikasi akun Anda:</p>
                                <h2 style='color:green;'>$verification_code</h2>
                                <p>Masukkan kode ini di halaman <a href='http://localhost/verifikasi.php'>verifikasi</a> untuk mengaktifkan akun.</p>
                            ";

                            $mail->send();

                            // === Redirect ke halaman verifikasi dengan email sebagai parameter ===
                            header("Location: verifikasi.php?email=" . urlencode($email));
                            exit;

                        } catch (Exception $e) {
                            $register_msg = "Registrasi berhasil, tapi gagal mengirim email. Error: {$mail->ErrorInfo}";
                        }
                    } else {
                        $register_msg = "Gagal menyimpan data: " . $koneksi->error;
                    }
                    $insert->close();
                }
            }
            $stmt->close();
        }
    }
}
// mysqli_close($koneksi); // Tutup koneksi jika tidak diperlukan lagi
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
.password-wrapper {
    position: relative;
}
.password-wrapper input {
    padding-right: 40px;
}
.password-wrapper .toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 1.2rem;
    color: #6c757d;
}
.password-wrapper .toggle-password:hover {
    color: #000;
}
</style>

</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow p-4" style="border-radius:1rem;">
        <h3 class="text-center mb-4">Register</h3>

        <?php if ($register_msg): ?>
          <div class="alert alert-info py-2"><?= htmlspecialchars($register_msg) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" required value="<?= htmlspecialchars($nama) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($email) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required value="<?= htmlspecialchars($username) ?>">
          </div>
          <div class="mb-3">
    <label class="form-label">Password</label>
    <div class="password-wrapper">
        <input type="password" class="form-control" name="password" id="password" required>
        <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('password', this)"></i>
    </div>
</div>
          <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <div class="password-wrapper">
        <input type="password" class="form-control" name="confirm" id="confirm" required>
        <i class="bi bi-eye-slash toggle-password" onclick="togglePassword('confirm', this)"></i>
    </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Nomor WhatsApp</label>
            <input type="text" class="form-control" name="whatsapp" required value="<?= htmlspecialchars($whatsapp) ?>">
          </div>
          <button type="submit" class="btn btn-success w-100">Register</button>
        </form>

        <div class="text-center mt-3">
          <small>Sudah punya akun? <a href="login.php">Login</a></small>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);

    if (field.type === "password") {
        // buka password
        field.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        // tutup password
        field.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
</script>
</body>
</html>