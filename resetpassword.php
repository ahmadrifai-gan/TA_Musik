<?php
ob_start();
session_start();

// Koneksi database langsung di file ini
$host = "localhost";
$user = "mifmyho2_arenafitclubjember";
$pass = "MIF@2025";
$db = "mifmyho2_arenafitclubjember";

// Buat koneksi
$con = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($con->connect_error) {
    die("Koneksi database gagal: " . $con->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

$error = "";
$success = "";
$valid_token = false;
$email = "";

// Cek apakah token ada di URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = "Token reset tidak valid atau sudah kadaluarsa.";
} else {
    $token = trim(htmlspecialchars($_GET['token']));

    // Validasi token
    $stmt = $con->prepare("SELECT email, reset_token_expiry FROM tbl_member WHERE reset_token = ? AND reset_token_expiry > NOW()");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $valid_token = true;
        } else {
            $error = "Token reset tidak valid atau sudah kadaluarsa. Silakan minta link reset password baru.";
        }
        $stmt->close();
    }
}

// Proses reset password
if (isset($_POST['resetbtn']) && $valid_token) {
    $new_password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Validasi
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Password dan konfirmasi password tidak boleh kosong!";
    } elseif (strlen($new_password) < 8) {
        $error = "Password minimal harus 8 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password dan hapus token
        $update = $con->prepare("UPDATE tbl_member SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
        if ($update) {
            $update->bind_param("ss", $hashed_password, $email);
            if ($update->execute()) {
                $success = "Password berhasil direset! Silakan login dengan password baru Anda.";
                $valid_token = false; // Token sudah digunakan
            } else {
                $error = "Gagal mereset password. Silakan coba lagi.";
            }
            $update->close();
        } else {
            $error = "Terjadi kesalahan pada sistem.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Arena Fit Gym Jember</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #2c3e50, #4a6491);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(to right, #2c3e50, #4a6491);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background-color: #ffe6e6;
            border: 1px solid #ff9999;
            color: #cc0000;
        }

        .alert-success {
            background-color: #e6ffe6;
            border: 1px solid #99ff99;
            color: #009900;
        }

        .alert-info {
            background-color: #e6f3ff;
            border: 1px solid #99ccff;
            color: #0066cc;
        }

        .alert i {
            font-size: 18px;
            margin-top: 2px;
        }

        .info-box {
            background-color: #f0f8ff;
            border: 1px solid #cce7ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0066cc;
        }

        .info-box i {
            margin-right: 8px;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            width: 100%;
            background: #eee;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
        }

        .weak {
            background: #ff4757;
            width: 33%;
        }

        .medium {
            background: #ffa502;
            width: 66%;
        }

        .strong {
            background: #2ed573;
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
            z-index: 2;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 16px;
            cursor: pointer;
            z-index: 2;
            background: none;
            border: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
            outline: none;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .password-rules {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding-left: 15px;
        }

        .password-rules ul {
            margin: 5px 0;
            padding-left: 15px;
        }

        .password-rules li {
            margin-bottom: 3px;
        }

        .password-rules .valid {
            color: #2ed573;
        }

        .password-rules .invalid {
            color: #ff4757;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #2980b9, #2573a7);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #ccc;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-size: 14px;
            position: relative;
        }

        .divider:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }

        .divider:after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-link {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .content {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .logo {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <div class="header">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena Fit Gym Jember Logo" class="logo">
            <h1>Reset Password</h1>
            <p>Buat password baru untuk akun Anda</p>
        </div>

        <div class="content">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    Password Anda telah berhasil direset. Silakan login ke sistem dengan password baru.
                </div>

                <div class="divider"></div>

                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Sekarang
                </a>

            <?php elseif ($valid_token): ?>
                <div class="info-box">
                    <i class="fas fa-shield-alt"></i>
                    Buat password baru yang kuat untuk akun Anda. Pastikan password minimal 8 karakter.
                </div>

                <form method="POST" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Masukkan password baru"
                                required
                                minlength="8">
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div>Kekuatan password:</div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                        </div>
                        <div class="password-rules" id="passwordRules">
                            <strong>Password harus mengandung:</strong>
                            <ul>
                                <li id="ruleLength" class="invalid">Minimal 8 karakter</li>
                                <li id="ruleLowercase" class="invalid">Huruf kecil</li>
                                <li id="ruleUppercase" class="invalid">Huruf besar</li>
                                <li id="ruleNumber" class="invalid">Angka</li>
                                <li id="ruleSpecial" class="invalid">Karakter khusus (@$!%*?&)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                placeholder="Konfirmasi password baru"
                                required
                                minlength="8">
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-rules">
                            <div id="passwordMatch" style="margin-top: 5px;"></div>
                        </div>
                    </div>

                    <button type="submit" name="resetbtn" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Reset Password
                    </button>
                </form>

                <div class="divider">atau</div>

                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Kembali ke Login
                </a>

            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Token reset tidak valid. Silakan minta link reset password baru.</span>
                </div>

                <a href="forgotpassword.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Minta Link Reset Baru
                </a>

                <div class="divider"></div>

                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Kembali ke Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatchDiv = document.getElementById('passwordMatch');

        function checkPasswordStrength(password) {
            let strength = 0;
            let rules = {
                length: false,
                lower: false,
                upper: false,
                number: false,
                special: false
            };

            // Length check
            if (password.length >= 8) {
                strength += 1;
                rules.length = true;
            }

            // Lowercase check
            if (/[a-z]/.test(password)) {
                strength += 1;
                rules.lower = true;
            }

            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 1;
                rules.upper = true;
            }

            // Number check
            if (/[0-9]/.test(password)) {
                strength += 1;
                rules.number = true;
            }

            // Special character check
            if (/[@$!%*?&]/.test(password)) {
                strength += 1;
                rules.special = true;
            }

            return {
                strength,
                rules
            };
        }

        function updateStrengthUI(strength, rules) {
            // Update strength bar
            strengthFill.className = 'strength-fill';
            if (strength <= 1) {
                strengthFill.classList.add('weak');
            } else if (strength <= 3) {
                strengthFill.classList.add('medium');
            } else {
                strengthFill.classList.add('strong');
            }

            // Update rules list
            document.getElementById('ruleLength').className = rules.length ? 'valid' : 'invalid';
            document.getElementById('ruleLowercase').className = rules.lower ? 'valid' : 'invalid';
            document.getElementById('ruleUppercase').className = rules.upper ? 'valid' : 'invalid';
            document.getElementById('ruleNumber').className = rules.number ? 'valid' : 'invalid';
            document.getElementById('ruleSpecial').className = rules.special ? 'valid' : 'invalid';
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (!password || !confirmPassword) {
                passwordMatchDiv.innerHTML = '';
                return;
            }

            if (password === confirmPassword) {
                passwordMatchDiv.innerHTML = '<span style="color:#2ed573;"><i class="fas fa-check"></i> Password cocok</span>';
            } else {
                passwordMatchDiv.innerHTML = '<span style="color:#ff4757;"><i class="fas fa-times"></i> Password tidak cocok</span>';
            }
        }

        passwordInput.addEventListener('input', function() {
            const {
                strength,
                rules
            } = checkPasswordStrength(this.value);
            updateStrengthUI(strength, rules);
            checkPasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form validation
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Check password strength
            const {
                strength
            } = checkPasswordStrength(password);
            if (strength < 3) {
                e.preventDefault();
                alert('Password terlalu lemah! Pastikan password memenuhi minimal 3 dari 5 kriteria keamanan.');
                return false;
            }

            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            // Minimum length
            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal harus 8 karakter!');
                return false;
            }
        });

        // Auto focus on password input if form is visible
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.focus();
            }
        });
    </script>
</body>

</html>
<?php
$con->close();
ob_end_flush();
?>