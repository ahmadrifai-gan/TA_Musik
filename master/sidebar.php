<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
  <div class="menu">
    <a href="../admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-table-columns"></i><span class="menu-text">Dashboard</span>
    </a>
    <a href="../admin/order.php" class="<?= ($current_page == 'order.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-cart-shopping"></i><span class="menu-text">Order</span>
    </a>
    <a href="../admin/studio.php" class="<?= ($current_page == 'studio.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-music"></i><span class="menu-text">Studio</span>
    </a>
    <a href="../admin/report.php" class="<?= ($current_page == 'report.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-bar"></i><span class="menu-text">Report</span>
    </a>
    <a href="../admin/pelanggan.php" class="<?= ($current_page == 'pelanggan.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-user"></i><span class="menu-text">Pelanggan</span>
    </a>
    <a href="../admin/jadwal.php" class="<?= ($current_page == 'jadwal.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-days"></i><span class="menu-text">Jadwal</span>
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
    transition: all 0.3s ease;
    overflow: hidden;
  }

  .sidebar.collapsed {
    width: 70px;
    padding: 25px 10px;
  }

  .menu {
    margin-top: 90px;
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
    transition: margin 0.3s;
  }

  .sidebar.collapsed .menu a i {
    margin-right: 0;
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

  .menu-text {
    transition: opacity 0.2s, visibility 0.2s;
  }

  .sidebar.collapsed .menu-text {
    opacity: 0;
    visibility: hidden;
  }
</style>

<script>
  // Script untuk toggle sidebar
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("hamburger"); // pastikan id ini ada di navbar.php

    if (toggleBtn) {
      toggleBtn.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
      });
    }
  });
</script>
