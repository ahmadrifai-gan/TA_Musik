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
    $country_code = trim($_POST['country_code'] ?? '');
    $phone_number_only = trim($_POST['phone_number_only'] ?? '');
    $whatsapp = $country_code . $phone_number_only; // Gabungkan kode negara + nomor
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($nama === '' || $email === '' || $username === '' || $password === '' || $confirm === '' || $phone_number_only === '') {
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

  <style>
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
            <div class="phone-input-group">
              <select class="form-select country-select" name="country_code" id="countryCode" required>
                <option value="+62" selected>🇮🇩 Indonesia (+62)</option>
                <option value="+60">🇲🇾 Malaysia (+60)</option>
                <option value="+65">🇸🇬 Singapore (+65)</option>
                <option value="+66">🇹🇭 Thailand (+66)</option>
                <option value="+63">🇵🇭 Philippines (+63)</option>
                <option value="+84">🇻🇳 Vietnam (+84)</option>
                <option value="+95">🇲🇲 Myanmar (+95)</option>
                <option value="+673">🇧🇳 Brunei (+673)</option>
                <option value="+856">🇱🇦 Laos (+856)</option>
                <option value="+855">🇰🇭 Cambodia (+855)</option>
                <option value="+1">🇺🇸 USA (+1)</option>
                <option value="+44">🇬🇧 UK (+44)</option>
                <option value="+61">🇦🇺 Australia (+61)</option>
                <option value="+81">🇯🇵 Japan (+81)</option>
                <option value="+82">🇰🇷 South Korea (+82)</option>
                <option value="+86">🇨🇳 China (+86)</option>
                <option value="+91">🇮🇳 India (+91)</option>
                <option value="+971">🇦🇪 UAE (+971)</option>
                <option value="+966">🇸🇦 Saudi Arabia (+966)</option>
              </select>
              <input type="text" class="form-control phone-number" name="phone_number" id="phoneNumber" value="+62" required>
              <input type="hidden" name="phone_number_only" id="phoneNumberOnly">
            </div>
            <small class="text-muted">Nomor lengkap: <span id="fullNumber">+62</span></small>
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
  const countryCode = document.getElementById('countryCode');
  const phoneNumber = document.getElementById('phoneNumber');
  const phoneNumberOnly = document.getElementById('phoneNumberOnly');
  const fullNumber = document.getElementById('fullNumber');
  
  let lastValidValue = '+62';

  // Update preview nomor lengkap
  function updateFullNumber() {
    const code = countryCode.value;
    const inputValue = phoneNumber.value;
    
    // Ambil hanya angka setelah kode negara
    const numberOnly = inputValue.slice(code.length).replace(/\D/g, '');
    
    // Update display
    fullNumber.textContent = code + numberOnly;
    
    // Update hidden input untuk dikirim ke server (hanya nomor tanpa kode negara)
    phoneNumberOnly.value = numberOnly;
  }

  // Event listener untuk perubahan kode negara
  countryCode.addEventListener('change', function() {
    const newCode = this.value;
    const currentValue = phoneNumber.value;
    
    // Ambil nomor tanpa kode negara lama
    const oldCode = lastValidValue.match(/^\+\d+/)[0];
    const numberOnly = currentValue.slice(oldCode.length).replace(/\D/g, '');
    
    // Set input value dengan kode negara baru
    phoneNumber.value = newCode + numberOnly;
    lastValidValue = phoneNumber.value;
    
    updateFullNumber();
    
    // Focus dan taruh cursor di akhir
    phoneNumber.focus();
    phoneNumber.setSelectionRange(phoneNumber.value.length, phoneNumber.value.length);
  });
  
  // Event listener untuk input nomor telepon
  phoneNumber.addEventListener('input', function(e) {
    const code = countryCode.value;
    let value = e.target.value;
    
    // Jika input lebih pendek dari kode negara atau tidak dimulai dengan kode negara
    if (value.length < code.length || !value.startsWith(code)) {
      // Kembalikan ke nilai terakhir yang valid
      phoneNumber.value = lastValidValue;
      // Set cursor di akhir
      const length = phoneNumber.value.length;
      phoneNumber.setSelectionRange(length, length);
      return;
    }
    
    // Ambil bagian nomor (setelah kode negara)
    let numberPart = value.slice(code.length);
    
    // Hanya izinkan angka
    numberPart = numberPart.replace(/\D/g, '');
    
    // Hapus angka 0 di awal
    while (numberPart.startsWith('0')) {
      numberPart = numberPart.substring(1);
    }
    
    // Gabungkan kode negara + nomor bersih
    const finalValue = code + numberPart;
    
    // Set nilai input
    phoneNumber.value = finalValue;
    lastValidValue = finalValue;
    
    updateFullNumber();
  });

  // Prevent user dari menghapus atau mengedit kode negara
  phoneNumber.addEventListener('keydown', function(e) {
    const code = countryCode.value;
    const cursorPos = e.target.selectionStart;
    const cursorEnd = e.target.selectionEnd;
    
    // Jika ada seleksi yang mencakup kode negara
    if (cursorPos < code.length) {
      // Hanya izinkan arrow keys, tab, dan shortcut copy
      const allowedKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
      
      if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
        e.preventDefault();
        // Pindahkan cursor ke akhir kode negara
        phoneNumber.setSelectionRange(code.length, code.length);
      }
    }
  });

  // Saat user klik, pindahkan cursor setelah kode negara jika di area kode
  phoneNumber.addEventListener('click', function(e) {
    const code = countryCode.value;
    const cursorPos = e.target.selectionStart;
    
    if (cursorPos < code.length) {
      setTimeout(() => {
        phoneNumber.setSelectionRange(code.length, code.length);
      }, 0);
    }
  });
  
  // Prevent select di area kode negara
  phoneNumber.addEventListener('select', function(e) {
    const code = countryCode.value;
    if (e.target.selectionStart < code.length) {
      e.target.setSelectionRange(code.length, e.target.selectionEnd);
    }
  });
  
  // Saat focus, taruh cursor setelah kode negara
  phoneNumber.addEventListener('focus', function(e) {
    const code = countryCode.value;
    const value = e.target.value;
    
    // Jika input kosong atau hanya kode negara, set cursor di akhir
    if (value === code) {
      setTimeout(() => {
        e.target.setSelectionRange(code.length, code.length);
      }, 0);
    }
  });

  // Set initial value
  updateFullNumber();
</script>
</body>
</html>