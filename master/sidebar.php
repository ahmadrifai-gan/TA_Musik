<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Font dan Icon -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="sidebar" id="sidebar">
  <!-- Header dengan Logo - UPDATED -->
  <div class="sidebar-brand">
    <a href="index.php" class="brand-container">
      <img src="../assets/image/logo2.png" alt="Logo" class="brand-logo">
      <div class="brand-info">
        <h4>Reys Studio<br><span>Musik</span></h4>
      </div>
    </a>
  </div>

  <!-- Menu Navigation -->
  <div class="menu">
    <a href="../admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-table-columns"></i>
      <span class="menu-text">Dashboard</span>
    </a>
    <a href="../admin/order.php" class="<?= ($current_page == 'order.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-cart-shopping"></i>
      <span class="menu-text">Order</span>
    </a>
    <a href="../admin/studio.php" class="<?= ($current_page == 'studio.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-music"></i>
      <span class="menu-text">Studio</span>
    </a>
    <a href="../admin/report.php" class="<?= ($current_page == 'report.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-bar"></i>
      <span class="menu-text">Report</span>
    </a>
    <a href="../admin/pelanggan.php" class="<?= ($current_page == 'pelanggan.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-user"></i>
      <span class="menu-text">Pelanggan</span>
    </a>
    <a href="../admin/jadwal.php" class="<?= ($current_page == 'jadwal.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-days"></i>
      <span class="menu-text">Jadwal</span>
    </a>
  </div>
</div>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Poppins', sans-serif;
  }

  /* Sidebar Container */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background-color: #0D1321;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
    overflow: hidden;
    z-index: 1000;
  }

  .sidebar.collapsed {
    width: 80px;
  }

  /* Header Section - UPDATED CSS */
  .sidebar-brand {
    background: linear-gradient(135deg, #000000 0%, #1a1d29 100%);
    padding: 18px 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #2a2e3a;
    min-height: 90px;
    flex-shrink: 0;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .brand-container {
    display: flex;
    align-items: center;
    text-decoration: none;
    gap: 12px;
    width: 100%;
    transition: all 0.3s ease;
  }

  .brand-container:hover .brand-logo {
    transform: scale(1.05) rotate(5deg);
  }

  .brand-logo {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    display: block;
    border: 2px solid #ffd700;
    box-shadow: 0 4px 8px rgba(255, 215, 0, 0.2);
    transition: all 0.3s ease;
  }

  .brand-info {
    overflow: hidden;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 0;
    line-height: 1.3;
  }

  .brand-info h4 {
    font-family: 'Montserrat', sans-serif;
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
    white-space: normal;
    opacity: 1;
    transition: opacity 0.3s ease;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .brand-info h4 span {
    color: #ffd700;
    font-weight: 800;
    display: inline-block;
  }

  /* Ketika sidebar collapsed */
  .sidebar.collapsed .brand-info {
    opacity: 0;
    width: 0;
  }

  .sidebar.collapsed .brand-logo {
    margin: 0 auto;
  }

  /* Menu Section */
  .menu {
    padding: 20px 15px;
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    margin-top: 10px;
  }

  .menu::-webkit-scrollbar {
    width: 5px;
  }

  .menu::-webkit-scrollbar-track {
    background: transparent;
  }

  .menu::-webkit-scrollbar-thumb {
    background: #2a2e3a;
    border-radius: 5px;
  }

  .menu a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #ffffff;
    padding: 12px 15px;
    margin-bottom: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
    font-weight: 400;
    position: relative;
  }

  .menu a i {
    width: 20px;
    text-align: center;
    font-size: 18px;
    flex-shrink: 0;
    margin-right: 15px;
    transition: margin 0.3s;
  }

  .sidebar.collapsed .menu a i {
    margin-right: 0;
  }

  .menu-text {
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease, width 0.3s ease;
  }

  .sidebar.collapsed .menu-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
  }

  /* Active & Hover States */
  .menu a.active {
    background-color: #ffd700;
    color: #000000;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
  }

  .menu a:hover {
    background-color: #ffd700;
    color: #000000;
    transform: translateX(5px);
  }

  .sidebar.collapsed .menu a:hover {
    transform: translateX(0);
  }

  /* Tooltip untuk collapsed state */
  .sidebar.collapsed .menu a {
    position: relative;
  }

  .sidebar.collapsed .menu a::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 70px;
    background-color: #ffd700;
    color: #000000;
    padding: 8px 12px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    font-size: 14px;
    font-weight: 500;
    z-index: 1000;
  }

  .sidebar.collapsed .menu a:hover::after {
    opacity: 1;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .sidebar:not(.collapsed) {
      width: 250px;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("hamburger");

    // Tambahkan data-tooltip untuk setiap menu item
    const menuItems = document.querySelectorAll('.menu a');
    menuItems.forEach(item => {
      const text = item.querySelector('.menu-text').textContent;
      item.setAttribute('data-tooltip', text);
    });

    if (toggleBtn) {
      toggleBtn.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
      });
    }
  });
</script>