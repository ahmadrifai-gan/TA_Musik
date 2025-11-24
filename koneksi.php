<?php
// File: config/koneksi.php
// 🔥 Set timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi database
$server = 'localhost';
$username = 'root';
$password = '';
$db = 'ms_studio';

// ✅ KONEKSI UTAMA MENGGUNAKAN OOP (untuk semua file)
$koneksi = new mysqli($server, $username, $password, $db);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Set timezone untuk MySQL
$koneksi->query("SET time_zone = '+07:00'");

// Set charset untuk mencegah masalah encoding
$koneksi->set_charset("utf8mb4");

// ✅ ALIAS VARIABEL $conn untuk kompatibilitas dengan index.php
$conn = $koneksi;

// Optional: Tampilkan pesan sukses (hapus di production)
// echo "Koneksi database berhasil!";
?>