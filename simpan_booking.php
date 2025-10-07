<?php
$host="localhost"; 
$user="root"; 
$pass=""; 
$db="db_login";
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama       = $_POST['nama'];
    $studio     = $_POST['studio'];
    $paket      = $_POST['paket'];
    $tanggal    = $_POST['tanggal'];
    $jam_mulai  = $_POST['jam'];
    $jam_selesai= $_POST['jam_selesai'];
    $durasi     = $_POST['durasi'];
    $total      = $_POST['total'];

    // Cek apakah ada booking bentrok
    $cek = $conn->query("SELECT * FROM booking 
                         WHERE studio='$studio' 
                         AND tanggal='$tanggal' 
                         AND (jam_mulai < '$jam_selesai' AND jam_selesai > '$jam_mulai')");

    if ($cek->num_rows > 0) {
        echo "<script>alert('Jadwal bentrok, silakan pilih jam lain!');window.location.href='booking.php?studio=$studio';</script>";
        exit;
    }

    // Jika tidak bentrok, simpan
    $sql = "INSERT INTO booking (nama, studio, paket, tanggal, jam_mulai, jam_selesai, durasi, total) 
            VALUES ('$nama','$studio','$paket','$tanggal','$jam_mulai','$jam_selesai','$durasi','$total')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Booking berhasil disimpan!');window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: index.php");
}
?>
