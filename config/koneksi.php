<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Memulai koneksi...<br>";

$server   = "localhost";
$username = "root";
$password = "";
$db       = "ms_studio";

$koneksi = mysqli_connect($server, $username, $password, $db);

if (!$koneksi) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
} else {
    echo "✅ Koneksi database berhasil!";
}
?>
