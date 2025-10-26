<?php
require "config/koneksi.php";

// Data untuk akun admin
$username      = 'admin';
$password_plain = 'admin123';
$password_hash  = password_hash($password_plain, PASSWORD_BCRYPT);
$role           = 'admin';
$nama_lengkap   = 'Admin User';
$email          = 'rifaiuye241@gmail.com';
$whatsapp       = '082331844335';
$reset_token    = '';  // Kosongkan karena belum perlu reset password
$is_verified    = 1;   // Langsung verified agar bisa login

// Persiapan statement
$stmt = $koneksi->prepare("
    INSERT INTO user 
    (username, password, role, nama_lengkap, email, whatsapp, reset_token, is_verified) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

// Bind parameter
$stmt->bind_param(
    'sssssssi', 
    $username, 
    $password_hash, 
    $role, 
    $nama_lengkap, 
    $email, 
    $whatsapp, 
    $reset_token, 
    $is_verified
);

// Eksekusi
if ($stmt->execute()) {
    echo "Akun admin berhasil dibuat: $username";
} else {
    echo "Gagal membuat akun: " . $stmt->error;
}

// Tutup statement dan koneksi
$stmt->close();
$koneksi->close();
?>
