<?php
session_start();
require "config/koneksi.php";

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

$register_msg = "";
$nama = $email = $username = $whatsapp = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nama = trim($_POST['nama_lengkap'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $country_code = trim($_POST['country_code'] ?? '');
  $phone_number_only = trim($_POST['phone_number_only'] ?? '');
  $whatsapp = $country_code . $phone_number_only;
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';

    if ($nama === '' || $email === '' || $username === '' || $password === '' || $confirm === '' || $phone_number_only === '') {
        $register_msg = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_msg = "Format email tidak valid.";
    } elseif ($password !== $confirm) {
        $register_msg = "Konfirmasi password tidak sama!";
    } elseif (strlen($phone_number_only) < 7) {
        $register_msg = "Nomor WhatsApp minimal 7 digit.";
    } elseif (strlen($phone_number_only) > 15) {
        $register_msg = "Nomor WhatsApp maksimal 15 digit.";
    } else {
      $stmt->bind_param("ss", $username, $email);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        $register_msg = "Username atau email sudah terdaftar!";
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $verification_code = sprintf("%06d", mt_rand(1, 999999));
        
        // Set default role sebagai 'user'
        $role = 'user';
        $reset_token = "";
        $is_verified = 0;

        // Insert data ke tabel user
        // PERBAIKAN: Menggunakan $koneksi bukan $con
        $insert = $koneksi->prepare("INSERT INTO user (nama_lengkap, username, password, email, whatsapp, role, reset_token, is_verified, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($insert === false) {
          $register_msg = "Gagal menyiapkan insert: " . $koneksi->error;
        } else {
          $insert->bind_param("sssssssis", $nama, $username, $hash, $email, $whatsapp, $role, $reset_token, $is_verified, $verification_code);

          if ($insert->execute()) {
            // Set session untuk verifikasi
            $_SESSION['verify_email'] = $email;
            $_SESSION['verify_user_id'] = $koneksi->insert_id; // PERBAIKAN: $koneksi->insert_id

            // Kirim email verifikasi
            $subject = 'Kode Verifikasi Email - Reys Studio';

            // HTML Email content
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
                        <p>Halo <strong>' . htmlspecialchars($nama) . '</strong>,</p>
                        <p>Terima kasih telah mendaftar di Reys Studio. Untuk mengaktifkan akun Anda, silakan masukkan kode verifikasi berikut:</p>
                        <div class="code">' . $verification_code . '</div>
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

// Setelah email dikirim, redirect user ke halaman verifikasi
header("Location: verifikasi.php?email=" . urlencode($email));
exit;


            // Kirim email menggunakan PHP mail()
            if (mail($email, $subject, $message, $headers)) {
                // Redirect ke halaman verify
                header("Location: verifikasi.php");
                exit();
            } else {
                // Jika email gagal, tetap redirect ke verify dengan info
                header("Location: verifikasi.php?email_failed=1");
                exit();
            }
          } else {
            $register_msg = "Gagal menyimpan data: " . $koneksi->error; // PERBAIKAN: $koneksi->error
          }
          $insert->close();
        }
      }
      $stmt->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Reys Studio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
.password-wrapper { position: relative; }
.password-wrapper input { padding-right: 40px; }
.password-wrapper .toggle-password {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    cursor: pointer; font-size: 1.2rem; color: #6c757d;
}

.phone-input-group { display: flex; gap: 10px; }
.country-select { flex: 0 0 140px; }
.phone-number { flex: 1; }

.is-valid { border-color: #28a745 !important; }
.is-invalid { border-color: #dc3545 !important; }
</style>

.phone-input-group {
    display: flex;
    gap: 10px;
}
.country-select {
    flex: 0 0 140px;
}
.phone-number {
    flex: 1;
}
.text-danger {
    color: #dc3545;
    font-size: 0.875rem;
}
.text-success {
    color: #198754;
    font-size: 0.875rem;
}
</style>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow p-4" style="border-radius:1rem;">
          <h3 class="text-center mb-4">Register - Reys Studio</h3>

          <?php if ($register_msg): ?>
            <div class="alert alert-info py-2"><?= htmlspecialchars($register_msg) ?></div>
          <?php endif; ?>

        <form action="" method="POST" id="registerForm">
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
            <div class="phone-input-group mb-3">
              <select class="form-select country-select" name="country_code" id="countryCode" required>
                <option value="+62" selected>🇮🇩 Indonesia (+62)</option>
                <option value="+60">🇲🇾 Malaysia (+60)</option>
                <option value="+65">🇸🇬 Singapore (+65)</option>
                <option value="+66">🇹🇭 Thailand (+66)</option>
                <option value="+63">🇵🇭 Philippines (+63)</option>
                <option value="+84">🇻🇳 Vietnam (+84)</option>
              </select>
              <input type="text" class="form-control phone-number" id="phoneNumber" placeholder="812345678" required>
              <input type="hidden" name="phone_number_only" id="phoneNumberOnly">
            </div>
            <small class="text-muted">Nomor lengkap: <span id="fullNumber">+62</span></small>
            <div id="phoneValidation" class="mt-1"></div>
          </div>

          <button type="submit" class="btn btn-success w-100" id="submitBtn">Register</button>
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
    const countryCode = document.getElementById('countryCode');
    const phoneNumber = document.getElementById('phoneNumber');
    const phoneNumberOnly = document.getElementById('phoneNumberOnly');
    const fullNumber = document.getElementById('fullNumber');
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    field.type = field.type === "password" ? "text" : "password";
    icon.classList.toggle("bi-eye");
    icon.classList.toggle("bi-eye-slash");
}

    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        field.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
  const countryCode = document.getElementById('countryCode');
  const phoneNumber = document.getElementById('phoneNumber');
  const phoneNumberOnly = document.getElementById('phoneNumberOnly');
  const fullNumber = document.getElementById('fullNumber');
  
  let lastValidValue = '+62';

    let lastValidValue = '+62';

    function updateFullNumber() {
      const code = countryCode.value;
      const inputValue = phoneNumber.value;
      const numberOnly = inputValue.slice(code.length).replace(/\D/g, '');
      fullNumber.textContent = code + numberOnly;
      phoneNumberOnly.value = numberOnly;
    }

    countryCode.addEventListener('change', function () {
      const newCode = this.value;
      const oldCode = lastValidValue.match(/^\+\d+/)[0];
      const numberOnly = phoneNumber.value.slice(oldCode.length).replace(/\D/g, '');
      phoneNumber.value = newCode + numberOnly;
      lastValidValue = phoneNumber.value;
      updateFullNumber();
      phoneNumber.focus();
      phoneNumber.setSelectionRange(phoneNumber.value.length, phoneNumber.value.length);
    });

    phoneNumber.addEventListener('input', function (e) {
      const code = countryCode.value;
      let value = e.target.value;
      
      if (value.length < code.length || !value.startsWith(code)) {
        phoneNumber.value = lastValidValue;
        const length = phoneNumber.value.length;
        phoneNumber.setSelectionRange(length, length);
        return;
      }

      let numberPart = value.slice(code.length);
      numberPart = numberPart.replace(/\D/g, '');
      while (numberPart.startsWith('0')) {
        numberPart = numberPart.substring(1);
      }
      const finalValue = code + numberPart;
      phoneNumber.value = finalValue;
      lastValidValue = finalValue;
      updateFullNumber();
    });

    phoneNumber.addEventListener('keydown', function (e) {
      const code = countryCode.value;
      const cursorPos = e.target.selectionStart;
      if (cursorPos < code.length) {
        const allowedKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
        if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
          e.preventDefault();
          phoneNumber.setSelectionRange(code.length, code.length);
        }
      }
    });

    phoneNumber.addEventListener('click', function (e) {
      const code = countryCode.value;
      const cursorPos = e.target.selectionStart;
      if (cursorPos < code.length) {
        setTimeout(() => {
          phoneNumber.setSelectionRange(code.length, code.length);
        }, 0);
      }
    });

    phoneNumber.addEventListener('focus', function (e) {
      const code = countryCode.value;
      const value = e.target.value;
      if (value === code) {
        setTimeout(() => {
          e.target.setSelectionRange(code.length, code.length);
        }, 0);
      }
    });

    updateFullNumber();
  </script>
</body>
</html>
