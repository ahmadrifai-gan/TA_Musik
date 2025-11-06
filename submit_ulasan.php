<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pesan = trim($_POST['message']);
    
    // Validasi
    if (empty($nama) || empty($email) || empty($pesan)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("Location: index.php#contact");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid!";
        header("Location: index.php#contact");
        exit();
    }
    
    // Sanitasi input
    $nama = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $pesan = htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8');
    
    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO ulasan (nama, email, pesan, status) VALUES (?, ?, ?, 'approved')");
    $stmt->bind_param("sss", $nama, $email, $pesan);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Terima kasih! Ulasan Anda telah dikirim dan akan segera ditampilkan.";
    } else {
        $_SESSION['error'] = "Gagal mengirim ulasan. Silakan coba lagi.";
    }
    
    $stmt->close();
    $conn->close();
    header("Location: index.php#contact");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>