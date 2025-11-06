<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $profesi = mysqli_real_escape_string($conn, trim($_POST['profesi']));
    $ulasan = mysqli_real_escape_string($conn, trim($_POST['ulasan']));
    
    // Validasi input
    if (empty($nama) || empty($profesi) || empty($ulasan)) {
        $_SESSION['error_message'] = "Semua field harus diisi!";
        header("Location: index.php#pricing");
        exit();
    }
    
    // Insert ke database
    $query = "INSERT INTO ulasan (nama, profesi, ulasan, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $nama, $profesi, $ulasan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Terima kasih! Ulasan Anda telah berhasil dikirim.";
    } else {
        $_SESSION['error_message'] = "Gagal mengirim ulasan. Silakan coba lagi.";
    }
    
    mysqli_stmt_close($stmt);
    header("Location: index.php#pricing");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>