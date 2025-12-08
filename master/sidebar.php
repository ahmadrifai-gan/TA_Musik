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
    
    <!-- Menu Order dengan Submenu -->

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

  /* Styling untuk menu dengan submenu */
  .menu-item {
    position: relative;
    margin-bottom: 8px;
  }

  .menu-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #ffffff;
    padding: 12px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
    font-weight: 400;
    justify-content: space-between;
  }

  .submenu-arrow {
    font-size: 12px;
    margin-left: auto;
    transition: transform 0.3s ease;
  }

  .menu-item.active .submenu-arrow {
    transform: rotate(180deg);
  }

  .menu-item.has-submenu:hover .submenu-arrow {
    transform: rotate(180deg);
  }

  /* Submenu styling */
  .submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    margin-left: 20px;
  }

  .menu-item.active .submenu,
  .menu-item:hover .submenu {
    max-height: 200px;
  }

  .submenu-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #b0b3b8;
    padding: 10px 15px;
    margin-bottom: 5px;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 14px;
    background-color: rgba(255, 255, 255, 0.05);
  }

  .submenu-link i {
    font-size: 14px;
    margin-right: 12px;
    width: 20px;
    text-align: center;
  }

  .submenu-link:hover {
    background-color: rgba(255, 215, 0, 0.1);
    color: #ffd700;
    transform: translateX(5px);
  }

  .submenu-link.active {
    background-color: rgba(255, 215, 0, 0.2);
    color: #ffd700;
    font-weight: 500;
  }

  /* Sidebar collapsed state untuk submenu */
  .sidebar.collapsed .submenu {
    display: none;
  }

  .sidebar.collapsed .menu-item:hover .submenu {
    display: block;
    position: absolute;
    left: 70px;
    top: 0;
    background-color: #0D1321;
    min-width: 180px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    max-height: 200px;
    z-index: 1000;
    margin-left: 0;
  }

  .sidebar.collapsed .submenu-link {
    margin: 5px;
    padding: 10px 15px;
  }

  /* Active & Hover States */
  .menu a.active,
  .menu-link.active {
    background-color: #ffd700;
    color: #000000;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
  }

  .menu a:hover:not(.submenu-link),
  .menu-link:hover {
    background-color: #ffd700;
    color: #000000;
    transform: translateX(5px);
  }

  .sidebar.collapsed .menu a:hover:not(.submenu-link),
  .sidebar.collapsed .menu-link:hover {
    transform: translateX(0);
  }

  /* Tooltip untuk collapsed state */
  .sidebar.collapsed .menu a,
  .sidebar.collapsed .menu-link {
    position: relative;
  }

  .sidebar.collapsed .menu a::after,
  .sidebar.collapsed .menu-link::after {
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

  .sidebar.collapsed .menu a:hover::after,
  .sidebar.collapsed .menu-link:hover::after {
    opacity: 1;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .sidebar:not(.collapsed) {
      width: 250px;
    }
    
    .sidebar.collapsed .menu-item:hover .submenu {
      left: 80px;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("hamburger");
    
    // Tambahkan data-tooltip untuk setiap menu item
    const menuItems = document.querySelectorAll('.menu a, .menu-link');
    menuItems.forEach(item => {
      const textElement = item.querySelector('.menu-text');
      if (textElement) {
        const text = textElement.textContent;
        item.setAttribute('data-tooltip', text);
      }
    });
    
    // Toggle submenu ketika diklik
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        if (this.parentElement.classList.contains('has-submenu')) {
          e.preventDefault();
          const menuItem = this.parentElement;
          
          // Tutup submenu lainnya
          document.querySelectorAll('.menu-item').forEach(item => {
            if (item !== menuItem) {
              item.classList.remove('active');
            }
          });
          
          // Toggle submenu yang diklik
          menuItem.classList.toggle('active');
        }
      });
    });
    
    // Tutup submenu ketika klik di luar
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.menu-item')) {
        document.querySelectorAll('.menu-item').forEach(item => {
          item.classList.remove('active');
        });
      }
    });
    
    if (toggleBtn) {
      toggleBtn.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        // Tutup semua submenu ketika sidebar collapsed
        if (sidebar.classList.contains('collapsed')) {
          document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
          });
        }
      });
    }
    
    // Auto aktifkan menu item jika salah satu submenu aktif
    const currentPage = '<?= $current_page ?>';
    const orderPages = ['order.php', 'order-offline.php', 'order-online.php'];
    
    if (orderPages.includes(currentPage)) {
      const orderMenuItem = document.querySelector('.menu-item.has-submenu');
      if (orderMenuItem) {
        orderMenuItem.classList.add('active');
      }
    }
  });
</script>