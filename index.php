<?php
session_start();
require_once 'koneksi.php'; // Pastikan file koneksi database ada

// Ambil ulasan yang sudah diapprove
$query_ulasan = "SELECT nama, email, pesan FROM ulasan WHERE status = 'approved' ORDER BY tanggal_submit DESC LIMIT 10";
$result_ulasan = $conn->query($query_ulasan);

// Cek jika query gagal
if (!$result_ulasan) {
    die("Error query ulasan: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="color-scheme" content="dark light" />
  <title>Studio – Premium Music Studio</title>
  <meta name="description"
    content="Studio musik premium dengan ruang kedap suara, peralatan pro, dan engineer berpengalaman. Booking mudah, harga transparan." />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
    rel="stylesheet" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="icon"
    href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🎵</text></svg>">
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid px-3 px-md-5">
      <a class="navbar-brand d-inline-flex align-items-center gap-2" href="#home">
        <span class="badge text-bg-dark rounded-3">♬</span>
        <span class="fw-bold">Reys Music Studio</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="#services">Panduan Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="#studios">Studio & Jadwal</a></li>
          <li class="nav-item"><a class="nav-link" href="#pricing">Testimoni</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Lokasi</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2">
          <!-- Tombol Booking Kuning -->
          <a class="btn btn-warning btn-sm fw-bold text-dark px-3" href="#studios">Booking</a>
          
          <?php if (isset($_SESSION['username'])): ?>
            <div class="dropdown">
              <a class="btn btn-dark btn-sm dropdown-toggle" href="#" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                👤 <?php echo htmlspecialchars($_SESSION['username']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="riwayat_reservasi.php">Riwayat Reservasi</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="javascript:void(0);" onclick="confirmLogout()">Logout</a></li>
              </ul>
            </div>
            <script>
              function confirmLogout() {
                if (confirm("Apakah Anda yakin ingin logout?")) {
                  window.location.href = "logout.php";
                }
              }
            </script>
          <?php else: ?>
            <a class="btn btn-outline-dark btn-sm rounded-pill" href="login.php">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <main id="main">

    <!-- Hero Section -->
    <section id="home" class="py-5">
      <div class="container-fluid px-3 px-md-5 py-4">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <p class="text-uppercase small text-secondary fw-bold mb-2">WELCOME</p>
            <h1 class="display-3 fw-bold lh-sm" style="font-family:'Playfair Display', Georgia, serif;">Studio Latihan
              Musik<br />dengan Akustik &<br />Fasilitas Terbaik</h1>
            <p class="text-secondary">Ruang latihan dengan akustik terkontrol, peralatan musik berkualitas, dan suasana
              nyaman untuk band maupun solo.</p>
            <div class="d-flex flex-wrap gap-2 my-3">
              <a class="btn btn-warning btn-lg rounded-pill" href="#studios">Booking Now</a>
              <a class="btn btn-outline-warning btn-lg rounded-pill" href="#studios">Lihat Studio & Jadwal</a>
            </div>
            <ul class="list-inline text-secondary mb-0">
            </ul>
          </div>
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm overflow-hidden rounded-4 bg-body-tertiary" style="min-height:360px;">
              <div class="ratio ratio-4x3">
                <img src="assets/image/studio.jpg" alt="" class="w-100 h-100 object-fit-cover">
              </div>
              <div class="card-img-overlay d-flex gap-2 align-items-end p-3"
                style="background:linear-gradient(180deg,transparent,rgba(0,0,0,.55))">
                <div class="bg-dark bg-opacity-75 text-white rounded-3 p-2">
                  <div class="fw-bold">1200+</div>
                  <small class="text-white-50">Sesi/Tahun</small>
                </div>
                <div class="bg-dark bg-opacity-75 text-white rounded-3 p-2">
                  <div class="fw-bold">98%</div>
                  <small class="text-white-50">Kepuasan</small>
                </div>
                <div class="bg-dark bg-opacity-75 text-white rounded-3 p-2">
                  <div class="fw-bold">2</div>
                  <small class="text-white-50">Ruang</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Panduan Booking -->
    <section id="services" class="py-5">
      <div class="container-fluid px-3 px-md-5">
        <div class="card shadow-lg rounded-4 overflow-hidden">
          <!-- Header dengan Gambar -->
          <div class="position-relative" style="height: 250px; overflow: hidden;">
            <img src="assets/image/studio.jpg" alt="Studio Musik" class="w-100 h-100" style="object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%);">
              <div class="text-center text-white px-3">
                <h1 class="display-4 fw-bold mb-2">Panduan Booking</h1>
                <p class="lead mb-0">Langkah-langkah mudah untuk memesan studio latihan musik kami</p>
              </div>
            </div>
          </div>
          
          <!-- Content dengan Langkah-langkah -->
          <div class="card-body p-4 p-md-5">
            <div class="row g-4 mt-2">
              <!-- Langkah 1 -->
              <div class="col-md-6 col-lg-3">
                <div class="step-card h-100 p-4 rounded-4 border border-2 text-center position-relative">
                  <div class="step-number position-absolute top-0 start-50 translate-middle">
                    1
                  </div>
                  <div class="mt-4 pt-3">
                    <i class="bi bi-music-note-beamed display-4 text-warning mb-3"></i>
                    <h5 class="fw-bold mb-2">Pilih Studio</h5>
                    <p class="text-muted small mb-0">Pilih studio sesuai kebutuhan Anda di bagian <strong>Studio & Jadwal</strong></p>
                  </div>
                </div>
              </div>
              
              <!-- Langkah 2 -->
              <div class="col-md-6 col-lg-3">
                <div class="step-card h-100 p-4 rounded-4 border border-2 text-center position-relative">
                  <div class="step-number position-absolute top-0 start-50 translate-middle">
                    2
                  </div>
                  <div class="mt-4 pt-3">
                    <i class="bi bi-calendar-check display-4 text-warning mb-3"></i>
                    <h5 class="fw-bold mb-2">Cek Jadwal</h5>
                    <p class="text-muted small mb-0">Lihat ketersediaan waktu, lalu klik <strong>Booking</strong></p>
                  </div>
                </div>
              </div>
              
              <!-- Langkah 3 -->
              <div class="col-md-6 col-lg-3">
                <div class="step-card h-100 p-4 rounded-4 border border-2 text-center position-relative">
                  <div class="step-number position-absolute top-0 start-50 translate-middle">
                    3
                  </div>
                  <div class="mt-4 pt-3">
                    <i class="bi bi-clipboard-check display-4 text-warning mb-3"></i>
                    <h5 class="fw-bold mb-2">Isi Data</h5>
                    <p class="text-muted small mb-0">Isi data dan konfirmasi jadwal booking Anda</p>
                  </div>
                </div>
              </div>
              
              <!-- Langkah 4 -->
              <div class="col-md-6 col-lg-3">
                <div class="step-card h-100 p-4 rounded-4 border border-2 text-center position-relative">
                  <div class="step-number position-absolute top-0 start-50 translate-middle">
                    4
                  </div>
                  <div class="mt-4 pt-3">
                    <i class="bi bi-emoji-smile display-4 text-warning mb-3"></i>
                    <h5 class="fw-bold mb-2">Nikmati!</h5>
                    <p class="text-muted small mb-0">Datang sesuai jam yang telah dipesan dan nikmati fasilitas kami 🎶</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Studios -->
    <section id="studios" class="py-5 bg-body-secondary">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-4">
          <h2 class="fw-bold">Studio & Jadwal</h2>
        </header>
        <div class="row g-3">
          <!-- Studio A -->
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4 d-flex flex-column">
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="h5">Studio A – Studio Gold</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
                <div class="mb-3">
                  <p class="mb-1">- Reguler = 50K/jam</p>
                  <p class="mb-1">- Paket 2 jam = 90K</p>
                  <p class="mb-0">- Paket 3 jam = 130K</p>
                </div>
              
                <!-- Spacer untuk mendorong tombol ke bawah -->
                <div class="mt-auto">
                  <div class="d-flex gap-2">
                    <a href="booking.php?studio=gold" class="btn btn-outline-primary flex-fill">Booking</a>
                    <a href="lihat_jadwal.php?studio=gold" class="btn btn-outline-success flex-fill">Lihat Jadwal</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Studio B -->
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4 d-flex flex-column">
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="h5">Studio B – Studio Bronze</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
                <div class="mb-3">
                  <p class="mb-1">Reguler = 35K/jam (ALL INCLUDE NO KEYBOARD)</p>
                  <p class="mb-0">+ KEYBOARD = 5K/jam</p>
                </div>

                <!-- Spacer untuk mendorong tombol ke bawah -->
                <div class="mt-auto">
                  <div class="d-flex gap-2">
                    <a href="booking.php?studio=bronze" class="btn btn-outline-primary flex-fill">Booking</a>
                    <a href="lihat_jadwal.php?studio=bronze" class="btn btn-outline-success flex-fill">Lihat Jadwal</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimoni -->
    <section id="pricing" class="py-5 bg-white">
      <div class="container-fluid px-3 px-md-4">
        <header class="text-center mb-4">
          <p class="text-uppercase small text-primary fw-bold">Dipercaya Kreator</p>
          <h2 class="fw-bold">Apa Kata Mereka</h2>
        </header>

        <?php if ($result_ulasan && $result_ulasan->num_rows > 0): ?>
        <div class="position-relative">
          <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php 
              $counter = 0;
              while($row = $result_ulasan->fetch_assoc()): 
              ?>
              <div class="carousel-item <?php echo $counter == 0 ? 'active' : ''; ?>">
                <figure class="text-center p-4 border rounded-4 bg-light shadow-sm mx-auto" style="max-width:760px;">
                  <blockquote class="blockquote mb-2">"<?php echo htmlspecialchars($row['pesan']); ?>"</blockquote>
                  <figcaption class="blockquote-footer mb-0">
                    <?php echo htmlspecialchars($row['nama']); ?>
                  </figcaption>
                </figure>
              </div>
              <?php 
              $counter++;
              endwhile; 
              ?>
            </div>
            
            <!-- Carousel Indicators (dots) -->
            <?php if ($result_ulasan->num_rows > 1): ?>
            <div class="carousel-indicators position-static mt-3">
              <?php for($i = 0; $i < $result_ulasan->num_rows; $i++): ?>
              <button type="button" data-bs-target="#testiCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                      class="<?php echo $i == 0 ? 'active' : ''; ?>" 
                      aria-current="<?php echo $i == 0 ? 'true' : 'false'; ?>" 
                      aria-label="Slide <?php echo $i + 1; ?>">
              </button>
              <?php endfor; ?>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Navigation Buttons -->
          <?php if ($result_ulasan->num_rows > 1): ?>
          <div class="text-center mt-3">
            <button class="btn btn-outline-primary rounded-circle me-2" type="button" data-bs-target="#testiCarousel" data-bs-slide="prev" style="width: 50px; height: 50px;">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button class="btn btn-outline-primary rounded-circle" type="button" data-bs-target="#testiCarousel" data-bs-slide="next" style="width: 50px; height: 50px;">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
          <p class="mb-0">Belum ada testimoni. Jadilah yang pertama memberikan ulasan!</p>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Lokasi -->
    <section id="testimonials" class="py-5 bg-body-secondary">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-4">
          <h2 class="fw-bold">Lokasi Kami</h2>
        </header>
        <div style="width:100%; height:400px; border-radius:10px; overflow:hidden;">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.518820641404!2d113.70717147481444!3d-8.156332682104066!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd695dbb3cda6a9%3A0x4d056e98604f1da1!2sJl.%20Bedadung%20Blok%20Durenan%20No.30%2C%20Kp.%20Using%2C%20Jemberlor%2C%20Kec.%20Patrang%2C%20Kabupaten%20Jember%2C%20Jawa%20Timur%2068118!5e0!3m2!1sid!2sid!4v1698371782111!5m2!1sid!2sid"
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </section>

    <!-- Kontak -->
    <section id="contact" class="py-5 bg-white">
      <div class="container-fluid px-3 px-md-4">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php 
          echo htmlspecialchars($_SESSION['success']); 
          unset($_SESSION['success']);
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php 
          echo htmlspecialchars($_SESSION['error']); 
          unset($_SESSION['error']);
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-3 align-items-stretch">
          <div class="col-md-6 d-flex justify-content-center align-items-center">
            <div class="text-center">
              <h5>HUBUNGI KAMI</h5>
              <h3>Ayo Diskusi Apa Kek</h3>
              <p>
                DM Instagram 
                <a href="https://www.instagram.com/reys_musicstudio" target="_blank" class="text-decoration-none fw-semibold text-primary">
                  @reys_musicstudio
                </a>,
                WhatsApp 
                <a href="https://wa.me/6285606564811" target="_blank" class="text-decoration-none fw-semibold text-success">
                  085606564811
                </a>,
                atau kirim pesan via formulir.
              </p>
            </div>
          </div>
          <div class="col-md-6">
            <form action="submit_ulasan.php" method="POST" class="border rounded-4 bg-white p-3 p-md-4 shadow-sm">
              <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input id="name" name="name" class="form-control" required placeholder="Nama lengkap Anda" />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" required placeholder="nama@email.com" />
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Pesan / Ulasan</label>
                <textarea id="message" name="message" rows="5" class="form-control" required
                  placeholder="Ceritakan pengalaman atau kebutuhan Anda"></textarea>
              </div>
              <button class="btn btn-primary w-100" type="submit">Kirim Ulasan</button>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer class="bg-dark text-white pt-5 pb-3">
    <div class="container text-center small">
      <p class="mb-0">© <span id="year"></span> Reys Music Studio. All rights reserved.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Smooth Scroll & Year -->
  <script>
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });

    // Auto year
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>

  <style>
    /* Step Card Styles */
    .step-card {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .step-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }

    .step-number {
      background: #ffd700;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: bold;
      color: #000;
      box-shadow: 0 4px 10px rgba(255,215,0,0.3);
      transition: all 0.3s ease;
    }

    .step-card:hover .step-number {
      transform: scale(1.1);
      box-shadow: 0 6px 15px rgba(255,215,0,0.5);
    }
  </style>
</body>
</html>