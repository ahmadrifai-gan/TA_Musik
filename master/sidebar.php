<?php
// Menentukan halaman aktif berdasarkan nama file PHP yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
  <div class="menu">
    <a href="../admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-table-columns"></i>Dashboard
    </a>
    <a href="../admin/order.php" class="<?= ($current_page == 'order.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-cart-shopping"></i>Order
    </a>
    <a href="../admin/studio.php" class="<?= ($current_page == 'studio.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-music"></i>Studio
    </a>
    <a href="../admin/report.php" class="<?= ($current_page == 'report.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-bar"></i>Report
    </a>
    <a href="../admin/pelanggan.php" class="<?= ($current_page == 'pelanggan.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-user"></i>Pelanggan
    </a>
    <a href="../admin/jadwal.php" class="<?= ($current_page == 'jadwal.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-days"></i>Jadwal
    </a>
  </div>
</div>

<!-- Font dan Icon -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 230px;
    background-color: #0D1321;
    display: flex;
    flex-direction: column;
    padding: 25px 20px;
    box-sizing: border-box;
  }

  /* Tambahkan jarak atas agar tidak mentok navbar */
  .menu {
    margin-top: 90px; /* sebelumnya 20px, dinaikkan agar turun ke bawah */
  }

  .menu a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: white;
    padding: 11px 14px;
    margin-bottom: 8px;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 17px;
    font-weight: 400;
  }

  .menu a i {
    margin-right: 12px;
    width: 22px;
    text-align: center;
    font-size: 18px;
  }

  .menu a.active {
    background-color: #ffd700;
    color: black;
    font-weight: 600;
  }

  .menu a:hover {
    background-color: #ffd700;
    color: black;
  }
</style>
