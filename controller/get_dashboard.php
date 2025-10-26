<?php
require_once "../config/koneksi.php";

// Total pendapatan per bulan
$queryPendapatan = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total 
    FROM transaksi 
    WHERE MONTH(tanggal) = MONTH(CURDATE()) 
      AND YEAR(tanggal) = YEAR(CURDATE())
");
$dataPendapatan = mysqli_fetch_assoc($queryPendapatan);
$totalPendapatan = $dataPendapatan['total'];

// Total reserfasi per bulan
$queryReserfasi = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM booking 
    WHERE MONTH(Tanggal) = MONTH(CURDATE()) 
      AND YEAR(Tanggal) = YEAR(CURDATE())
");
$dataReserfasi = mysqli_fetch_assoc($queryReserfasi);
$totalReserfasi = $dataReserfasi['total'];

// Total customer unik per bulan
$queryCustomer = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT id_user) AS total 
    FROM booking 
    WHERE MONTH(Tanggal) = MONTH(CURDATE()) 
      AND YEAR(Tanggal) = YEAR(CURDATE())
");
$dataCustomer = mysqli_fetch_assoc($queryCustomer);
$totalCustomer = $dataCustomer['total'];
?>
