<?php
require("config/koneksi.php");

if (isset($_POST['register'])) {
    $email    = trim($_POST['txt_email']);
    $username = trim($_POST['txt_username']);
    $nama     = trim($_POST['txt_nama']);
    $pass     = trim($_POST['txt_pass']);
    $wa       = trim($_POST['txt_wa']);

    if (!empty($email) && !empty($username) && !empty($username) && !empty($pass) && !empty($wa)) {
        $check = mysqli_query($koneksi, "SELECT * FROM user WHERE email='$email' OR username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Email atau Username sudah terdaftar.');</script>";
        } else {
            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
            $query = "INSERT INTO user (email, username, nama, password, telp, role) 
                      VALUES ('$email', '$username', '$nama', '$hashedPass', '$wa', 2)";
            $result = mysqli_query($koneksi, $query);

            if ($result) {
                header("Location: login.php");
                exit;
            } else {
                echo "<script>alert('Gagal register: " . mysqli_error($koneksi) . "');</script>";
            }
        }
    } else {
        echo "<script>alert('Data tidak boleh kosong!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e5e5e5;
        }
        .register-container {
            min-height: 100vh;
        }
        .form-section {
            background: #fff;
            padding: 40px;
        }
        .right-section {
            background: #d9d9d9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="container-fluid register-container">
    <div class="row  min-vh-100">
        <!-- Form kiri -->
        <div class="col-md-4 form-section d-flex flex-column justify-content-center">
            <h4 class="fw-bold mb-4">Daftar Sekarang Juga</h4>
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="txt_username" class="form-control" placeholder="Masukkan username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="txt_nama" class="form-control" placeholder="Masukkan nama" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="txt_email" class="form-control" placeholder="Masukkan email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="txt_pass" class="form-control" placeholder="Masukkan Password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. WhatsApp</label>
                    <input type="text" name="txt_wa" class="form-control" placeholder="Masukkan Nomor" required>
                </div>
                <button type="submit" name="register" class="btn btn-secondary w-100">Register</button>
            </form>
            <p class="mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
        </div>

        <!-- Bagian kanan -->
        <div class="col-md-8 right-section">
            <h3 class="fw-bold">Foto Studio</h3>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
