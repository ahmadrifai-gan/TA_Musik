<?php
session_start();
require('koneksi.php');

$error_message = "";

if (isset($_POST['submit'])) {
    $username = trim($_POST['txt_username']);
    $pass     = trim($_POST['txt_pass']);

    if (!empty($username) && !empty($pass)) {
        // pakai prepared statement biar aman
        $stmt = $koneksi->prepare("SELECT id_user, username, email, password, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $id       = $row['id_user'];
            $userVal  = $row['username'];
            $email    = $row['email'];
            $passVal  = $row['password'];
            $role     = $row['role'];

            if (password_verify($pass, $passVal)) {
                // login berhasil -> simpan session
                $_SESSION['id_user']       = $id;
                $_SESSION['username'] = $userVal;
                $_SESSION['email']    = $email;
                $_SESSION['role']     = $role;

                header("Location: index.php");
                exit;
            } else {
                $error_message = "Password salah!";
            }
        } else {
            $error_message = "Username tidak ditemukan!";
        }
    } else {
        $error_message = "Data tidak boleh kosong!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
</head>
<body>

<div class="container-fluid bg-light">
    <div class="row min-vh-100">
        <!-- Form kiri -->
        <div class="col-md-4 bg-white p-4 d-flex flex-column justify-content-center">
            <h4 class="fw-bold mb-4">Login Sekarang Juga</h4>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="txt_username" class="form-control" placeholder="Masukkan username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="txt_pass" class="form-control" placeholder="Masukkan Password" required>
                </div>
                <button type="submit" name="submit" class="btn btn-secondary w-100">Login</button>
            </form>
            
            <div class="text-center mt-3">
                <p class="mb-0">Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar di sini</a></p>
            </div>
          
        </div>

        <!-- Bagian kanan -->
        <div class="col-md-8 d-flex align-items-center justify-content-center bg-secondary-subtle">
            <h3 class="fw-bold">Foto Studio</h3>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
