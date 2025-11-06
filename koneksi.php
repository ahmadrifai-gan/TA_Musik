<?php
// File: koneksi.php
// Letakkan file ini di folder yang sama dengan index.php

$server = 'localhost';         // Host database
$username = 'root';            // Username database (default XAMPP: root)
$password = '';                // Password database (default XAMPP: kosong)
$db = 'ms_studio';             // Nama database Anda

// Membuat koneksi
$koneksi = mysqli_connect(hostname: $server, username: $username, password: $password, database: $db);
$conn = new mysqli($server, $username, $password, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset untuk mencegah masalah encoding
$conn->set_charset("utf8mb4");
?>