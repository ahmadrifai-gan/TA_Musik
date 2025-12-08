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
          <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#services">Panduan Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="#facilities">Fasilitas & Peralatan</a></li>
          <li class="nav-item"><a class="nav-link" href="#studios">Studio & Jadwal</a></li>
          <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2">
          
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
              nyaman untuk band maupun solo. Tempat yang tepat untuk mewujudkan kreativitas musik Anda.</p>
            <div class="d-flex flex-wrap gap-2 my-3">
              <a class="btn btn-warning btn-lg rounded-pill" href="#studios">Booking Now</a>
              <a class="btn btn-outline-warning btn-lg rounded-pill" href="#studios">Lihat Studio & Jadwal</a>
            </div>
            <div class="d-flex flex-wrap gap-4 mt-4">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Ruang Kedap Suara</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Peralatan Profesional</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>24/7 Support</span>
              </div>
            </div>
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

    <!-- Kenapa Memilih Kami -->
    <section id="why-choose-us" class="py-5 bg-light">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-5">
          <h2 class="fw-bold mb-3">Kenapa Memilih Reys Music Studio?</h2>
          <p class="text-muted mx-auto" style="max-width: 700px;">Kami menyediakan pengalaman latihan musik terbaik dengan fasilitas lengkap dan pelayanan profesional</p>
        </header>
        
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 rounded-4 text-center">
              <div class="mb-3">
                <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block">
                  <i class="bi bi-music-note-beamed display-4 text-warning"></i>
                </div>
              </div>
              <h4 class="h5 fw-bold mb-2">Peralatan Lengkap</h4>
              <p class="text-muted">Drum set, gitar, bass, amplifier, keyboard, dan sound system berkualitas studio</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 rounded-4 text-center">
              <div class="mb-3">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                  <i class="bi bi-volume-up display-4 text-primary"></i>
                </div>
              </div>
              <h4 class="h5 fw-bold mb-2">Akustik Terbaik</h4>
              <p class="text-muted">Ruang kedap suara dengan perawatan akustik profesional untuk kualitas audio maksimal</p>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 p-4 rounded-4 text-center">
              <div class="mb-3">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 d-inline-block">
                  <i class="bi bi-headset display-4 text-success"></i>
                </div>
              </div>
              <h4 class="h5 fw-bold mb-2">Support 24/7</h4>
              <p class="text-muted">Tim support siap membantu Anda kapan saja untuk kelancaran sesi latihan</p>
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

    <!-- Fasilitas & Peralatan -->
    <section id="facilities" class="py-5 bg-body-tertiary">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-5">
          <h2 class="fw-bold mb-3">Fasilitas & Peralatan Lengkap</h2>
          <p class="text-muted">Kami menyediakan segala yang Anda butuhkan untuk latihan musik yang produktif</p>
        </header>
        
        <div class="row g-4">
          <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
              <div class="card-header bg-warning text-dark py-3">
                <h4 class="h5 mb-0 fw-bold"><i class="bi bi-mic-fill me-2"></i>Peralatan Audio</h4>
              </div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Mixer Audio 16 Channel</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Monitor Speaker Aktif</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Microphone Condenser & Dynamic</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Headphone Monitoring</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Audio Interface</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
              <div class="card-header bg-primary text-white py-3">
                <h4 class="h5 mb-0 fw-bold"><i class="bi bi-guitar me-2"></i>Peralatan Musik</h4>
              </div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Drum Set Lengkap</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Gitar Listrik & Akustik</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Bass & Amplifier</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Keyboard 88 Keys</span>
                    <span class="badge bg-warning text-dark">+5K/jam</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Pedal Efek & Accessories</span>
                    <span class="badge bg-success">Tersedia</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        
      </div>
    </section>

    <!-- Studios -->
    <section id="studios" class="py-5 bg-body-secondary">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-5">
          <h2 class="fw-bold mb-3">Studio & Jadwal</h2>
          <p class="text-muted mx-auto" style="max-width: 700px;">Pilih studio sesuai kebutuhan dan cek ketersediaan jadwal untuk booking</p>
        </header>
        <div class="row g-4">
          <!-- Studio A -->
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4 d-flex flex-column border-warning border-2">
              <div class="card-header bg-warning text-dark py-3">
                <h3 class="h5 mb-0"><i class="bi bi-star-fill me-2"></i>Studio A – Studio Gold</h3>
              </div>
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body d-flex flex-column">
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video. Ukuran lebih luas dengan peralatan premium.</p>
                <div class="mb-3">
                  <h5 class="h6 fw-bold mb-2">Spesifikasi:</h5>
                  <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Drum Set Pearl Export</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Gitar Fender & Marshall Amp</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Bass Ampeg Combo</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>PA System 2000W</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Ruang 4x6 meter</li>
                  </ul>
                </div>
                <div class="mb-4">
                  <h5 class="h6 fw-bold mb-2">Harga:</h5>
                  <div class="d-flex flex-wrap gap-3">
                    <div class="badge bg-dark p-2">Reguler = 50K/jam</div>
                    <div class="badge bg-success p-2">Paket 2 jam = 90K</div>
                    <div class="badge bg-primary p-2">Paket 3 jam = 130K</div>
                  </div>
                </div>
              
                <!-- Spacer untuk mendorong tombol ke bawah -->
                <div class="mt-auto">
                  <div class="d-grid gap-2">
                    <a href="booking.php?studio=gold" class="btn btn-warning btn-lg">Booking Studio Gold</a>
                    <a href="lihat_jadwal.php?studio=gold" class="btn btn-outline-warning">Lihat Jadwal Tersedia</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Studio B -->
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4 d-flex flex-column border-primary border-2">
              <div class="card-header bg-primary text-white py-3">
                <h3 class="h5 mb-0"><i class="bi bi-music-note-beamed me-2"></i>Studio B – Studio Bronze</h3>
              </div>
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body d-flex flex-column">
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video. Pilihan ekonomis dengan fasilitas lengkap.</p>
                <div class="mb-3">
                  <h5 class="h6 fw-bold mb-2">Spesifikasi:</h5>
                  <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Drum Set Standard</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Gitar & Amplifier</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Bass Combo</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>PA System 1500W</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Ruang 3.5x5 meter</li>
                  </ul>
                </div>
                <div class="mb-4">
                  <h5 class="h6 fw-bold mb-2">Harga:</h5>
                  <div class="d-flex flex-wrap gap-3">
                    <div class="badge bg-dark p-2">Reguler = 35K/jam</div>
                    <div class="badge bg-warning text-dark p-2">+ KEYBOARD = 5K/jam</div>
                  </div>
                  <small class="text-muted mt-2 d-block">ALL INCLUDE NO KEYBOARD</small>
                </div>

                <!-- Spacer untuk mendorong tombol ke bawah -->
                <div class="mt-auto">
                  <div class="d-grid gap-2">
                    <a href="booking.php?studio=bronze" class="btn btn-primary btn-lg">Booking Studio Bronze</a>
                    <a href="lihat_jadwal.php?studio=bronze" class="btn btn-outline-primary">Lihat Jadwal Tersedia</a>
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
        <header class="text-center mb-5">
          <p class="text-uppercase small text-primary fw-bold">Dipercaya Kreator</p>
          <h2 class="fw-bold">Apa Kata Mereka</h2>
          <p class="text-muted mx-auto" style="max-width: 700px;">Pengalaman nyata dari musisi yang telah menggunakan fasilitas kami</p>
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
                <div class="row justify-content-center">
                  <div class="col-lg-8">
                    <figure class="text-center p-4 p-md-5 border rounded-4 bg-light shadow-sm">
                      <div class="mb-4">
                        <div class="rating-stars d-inline-block">
                          <i class="bi bi-star-fill text-warning"></i>
                          <i class="bi bi-star-fill text-warning"></i>
                          <i class="bi bi-star-fill text-warning"></i>
                          <i class="bi bi-star-fill text-warning"></i>
                          <i class="bi bi-star-fill text-warning"></i>
                        </div>
                      </div>
                      <blockquote class="blockquote mb-3 fs-5">"<?php echo htmlspecialchars($row['pesan']); ?>"</blockquote>
                      <figcaption class="blockquote-footer mb-0 d-flex align-items-center justify-content-center">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                          <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                        </div>
                        <div class="text-start">
                          <div class="fw-bold"><?php echo htmlspecialchars($row['nama']); ?></div>
                          <small><?php echo htmlspecialchars($row['email']); ?></small>
                        </div>
                      </figcaption>
                    </figure>
                  </div>
                </div>
              </div>
              <?php 
              $counter++;
              endwhile; 
              ?>
            </div>
            
            <!-- Carousel Indicators (dots) -->
            <?php if ($result_ulasan->num_rows > 1): ?>
            <div class="carousel-indicators position-static mt-4">
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
          <div class="text-center mt-4">
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
        <div class="alert alert-info text-center mx-auto" style="max-width: 600px;">
          <i class="bi bi-info-circle-fill me-2"></i>
          <p class="mb-0">Belum ada testimoni. Jadilah yang pertama memberikan ulasan!</p>
          <a href="#contact" class="btn btn-outline-info btn-sm mt-2">Berikan Ulasan</a>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5 bg-light">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-5">
          <h2 class="fw-bold mb-3">Pertanyaan yang Sering Diajukan</h2>
          <p class="text-muted">Temukan jawaban untuk pertanyaan umum seputar booking dan fasilitas studio</p>
        </header>
        
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="accordion" id="faqAccordion">
              <!-- FAQ 1 -->
              <div class="accordion-item border rounded-3 mb-3">
                <h2 class="accordion-header">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                    Bagaimana cara booking studio?
                  </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    <p>Booking dapat dilakukan melalui 3 cara:</p>
                    <ol>
                      <li><strong>Online:</strong> Pilih studio, cek jadwal, dan booking langsung melalui website</li>
                      <li><strong>WhatsApp:</strong> Hubungi kami di 085606564811 untuk booking via chat</li>
                      <li><strong>Langsung ke Studio:</strong> Datang langsung untuk booking dan konsultasi</li>
                    </ol>
                  </div>
                </div>
              </div>
              
              <!-- FAQ 2 -->
              <div class="accordion-item border rounded-3 mb-3">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                    Apakah ada biaya tambahan?
                  </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    <p>Harga yang tertera sudah termasuk:</p>
                    <ul>
                      <li>Pemakaian semua peralatan musik (kecuali keyboard di Studio Bronze)</li>
                      <li>Pemakaian sound system dan mixer</li>
                      <li>Air mineral gratis</li>
                      <li>Parkir kendaraan</li>
                    </ul>
                    <p><strong>Biaya tambahan:</strong> Keyboard (+5K/jam untuk Studio Bronze)</p>
                  </div>
                </div>
              </div>
              
              <!-- FAQ 3 -->
              <div class="accordion-item border rounded-3 mb-3">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                    Berapa maksimal orang dalam satu studio?
                  </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    <p>Kapasitas maksimal yang disarankan:</p>
                    <ul>
                      <li><strong>Studio Gold:</strong> Maksimal 8 orang (ideal untuk band 4-6 orang + crew)</li>
                      <li><strong>Studio Bronze:</strong> Maksimal 6 orang (ideal untuk band 3-5 orang)</li>
                    </ul>
                    <p class="text-muted"><small>Untuk jumlah lebih banyak, silakan konsultasi terlebih dahulu</small></p>
                  </div>
                </div>
              </div>
              
              <!-- FAQ 4 -->
              <div class="accordion-item border rounded-3 mb-3">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                    Apakah bisa membawa alat musik sendiri?
                  </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    <p><strong>Boleh dan disarankan!</strong> Anda dapat membawa alat musik sendiri seperti:</p>
                    <ul>
                      <li>Gitar/bass pribadi (kami menyediakan amplifier)</li>
                      <li>Pedal efek</li>
                      <li>Stik drum pribadi</li>
                      <li>Microphone khusus</li>
                    </ul>
                    <p>Kami juga menyediakan locker untuk penyimpanan aman.</p>
                  </div>
                </div>
              </div>
              
              <!-- FAQ 5 -->
              <div class="accordion-item border rounded-3 mb-3">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                    Bagaimana jika ingin membatalkan booking?
                  </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    <p>Kebijakan pembatalan:</p>
                    <ul>
                      <li><strong>24 jam sebelum sesi:</strong> 100% refund</li>
                      <li><strong>12-24 jam sebelum sesi:</strong> 50% refund</li>
                      <li><strong>Kurang dari 12 jam:</strong> Tidak ada refund</li>
                    </ul>
                    <p>Untuk pembatalan, hubungi WhatsApp 085606564811 dengan menyertakan kode booking.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lokasi & Kontak -->
    <section id="contact" class="py-5 bg-white">
      <div class="container-fluid px-3 px-md-5">

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

        <div class="text-center mb-5">
          <h2 class="fw-bold">Hubungi & Kunjungi Kami</h2>
          <p class="text-muted mb-4" style="max-width: 700px; margin: 0 auto;">
            Kami siap membantu kebutuhan latihan musik Anda. Hubungi kami melalui berbagai channel yang tersedia.
          </p>
          
          <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
            <a href="https://www.instagram.com/reys_musicstudio" target="_blank" class="btn btn-outline-danger rounded-pill">
              <i class="bi bi-instagram me-2"></i>Instagram
            </a>
            <a href="https://wa.me/6285606564811" target="_blank" class="btn btn-outline-success rounded-pill">
              <i class="bi bi-whatsapp me-2"></i>WhatsApp
            </a>
            <a href="mailto:info@reysmusicstudio.com" class="btn btn-outline-dark rounded-pill">
              <i class="bi bi-envelope me-2"></i>Email
            </a>
          </div>
        </div>

        <div class="row g-4 align-items-stretch">
          <!-- Kiri: Info Kontak & Maps -->
          <div class="col-lg-6">
            <div class="h-100">
              <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden mb-3" style="min-height: 300px;">
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.518820641404!2d113.70717147481444!3d-8.156332682104066!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd695dbb3cda6a9%3A0x4d056e98604f1da1!2sJl.%20Bedadung%20Blok%20Durenan%20No.30%2C%20Kp.%20Using%2C%20Jemberlor%2C%20Kec.%20Patrang%2C%20Kabupaten%20Jember%2C%20Jawa%20Timur%2068118!5e0!3m2!1sid!2sid!4v1698371782111!5m2!1sid!2sid"
                  width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
              
              <div class="row g-3">
              </div>
            </div>
          </div>

          <!-- Kanan: Ulasan/Form -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
              <div class="card-header bg-dark text-white py-3">
                <h4 class="h5 mb-0"><i class="bi bi-chat-left-text me-2"></i>Kirim Ulasan</h4>
              </div>
              <form action="submit_ulasan.php" method="POST" class="p-4">
                <div class="mb-3">
                  <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                  <input id="name" name="name" class="form-control" required placeholder="Masukkan nama lengkap Anda" />
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label fw-bold">Email</label>
                  <input id="email" name="email" type="email" class="form-control" required placeholder="nama@email.com" />
                </div>
                <div class="mb-3">
                  <label for="message" class="form-label fw-bold">Pesan / Ulasan</label>
                  <textarea id="message" name="message" rows="5" class="form-control" required
                    placeholder="Ceritakan pengalaman Anda atau ajukan pertanyaan..."></textarea>
                </div>
                <div class="d-grid">
                  <button class="btn btn-primary btn-lg" type="submit">
                    <i class="bi bi-send me-2"></i>Kirim Ulasan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer class="bg-dark text-white pt-5 pb-4">
    <div class="container-fluid px-3 px-md-5">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="mb-4">
            <a class="navbar-brand d-inline-flex align-items-center gap-2 text-white mb-3" href="#home">
              <span class="badge text-bg-warning rounded-3">♬</span>
              <span class="fw-bold">Reys Music Studio</span>
            </a>
            <p class="text-white-50 mb-0">Studio musik premium dengan fasilitas lengkap untuk mewujudkan kreativitas musik Anda.</p>
          </div>
        </div>
        
        <div class="col-lg-2 col-md-6">
          <h6 class="fw-bold mb-3">Menu</h6>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="#home" class="text-white-50 text-decoration-none">Home</a></li>
            <li class="mb-2"><a href="#services" class="text-white-50 text-decoration-none">Panduan Booking</a></li>
            <li class="mb-2"><a href="#studios" class="text-white-50 text-decoration-none">Studio & Jadwal</a></li>
            <li class="mb-2"><a href="#pricing" class="text-white-50 text-decoration-none">Testimoni</a></li>
            <li><a href="#contact" class="text-white-50 text-decoration-none">Kontak</a></li>
          </ul>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <h6 class="fw-bold mb-3">Kontak</h6>
          <ul class="list-unstyled text-white-50">
            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Jl. Bedadung Blok Durenan No.30, Jember</li>
            <li class="mb-2"><i class="bi bi-whatsapp me-2"></i>085606564811</li>
            <li class="mb-2"><i class="bi bi-instagram me-2"></i>@reys_musicstudio</li>
            <li><i class="bi bi-envelope me-2"></i>info@reysmusicstudio.com</li>
          </ul>
        </div>
        
        <div class="col-lg-3">
          <h6 class="fw-bold mb-3">Jam Operasional</h6>
          <div class="text-white-50">
            <p class="mb-1">Senin - Minggu</p>
            <p class="mb-0">08:00 - 22:00 WIB</p>
          </div>
          <div class="mt-4">
            <a href="#studios" class="btn btn-warning rounded-pill">Booking Sekarang</a>
          </div>
        </div>
      </div>
      
      <hr class="my-4 text-white-50">
      
      <div class="text-center">
        <p class="mb-0 text-white-50">© <span id="year"></span> Reys Music Studio. All rights reserved.</p>
      </div>
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
    
    // FAQ accordion auto close others
    document.querySelectorAll('.accordion-button').forEach(button => {
      button.addEventListener('click', function() {
        // Remove 'show' class from all other accordion items
        document.querySelectorAll('.accordion-collapse').forEach(collapse => {
          if (collapse.id !== this.getAttribute('data-bs-target').substring(1)) {
            collapse.classList.remove('show');
          }
        });
      });
    });
  </script>

  <style>
    :root {
      --yellow: #ffd34f;
      --yellow-strong: #ffb703;
      --dark: #0f172a;
      --muted: #6b7280;
      --card: #ffffff;
    }

    body {
      background: radial-gradient(circle at 15% 20%, rgba(255, 215, 79, 0.12), transparent 28%),
                  radial-gradient(circle at 80% 0%, rgba(59, 130, 246, 0.12), transparent 26%),
                  #f8fafc;
      color: #0f172a;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .navbar {
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.92) !important;
      box-shadow: 0 10px 30px rgba(15,23,42,0.05);
    }

    .navbar-brand .badge {
      background: linear-gradient(135deg, var(--yellow), #ffefba);
      color: #000;
    }

    .navbar-nav .nav-link {
      font-weight: 600;
      letter-spacing: 0.2px;
      position: relative;
      padding: 0.35rem 0.75rem;
      transition: color 0.2s ease, transform 0.2s ease;
    }

    .navbar-nav .nav-link::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--yellow), var(--yellow-strong));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.2s ease;
    }

    .navbar-nav .nav-link:hover {
      color: var(--yellow-strong);
      transform: translateY(-1px);
    }

    .navbar-nav .nav-link:hover::after {
      transform: scaleX(1);
    }

    .hero-card {
      border: none;
      background: linear-gradient(135deg, #0f172a, #111827);
      color: #e2e8f0;
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.35);
    }

    .hero-card .card-img-overlay {
      background: linear-gradient(180deg, transparent, rgba(0,0,0,0.65));
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--yellow), var(--yellow-strong));
      border: none;
      color: #000;
      box-shadow: 0 12px 30px rgba(255, 183, 3, 0.35);
      transition: transform 0.15s ease, box-shadow 0.2s ease;
      border-radius: 999px;
    }

    .btn-warning:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 36px rgba(255, 183, 3, 0.4);
      color: #000;
    }

    .btn-outline-warning {
      border-width: 2px;
    }

    /* Section shells */
    section {
      position: relative;
      scroll-margin-top: 90px;
    }

    .section-header h2 {
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    /* Step Card Styles */
    .step-card {
      background: linear-gradient(145deg, #ffffff, #f3f6fb);
      border: 1px solid #e5e7eb;
      transition: all 0.3s ease;
      cursor: pointer;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
      border-radius: 18px;
    }

    .step-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
      border-color: var(--yellow);
    }

    .step-number {
      background: linear-gradient(135deg, var(--yellow), #ffe17d);
      width: 52px;
      height: 52px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 800;
      color: #000;
      box-shadow: 0 10px 25px rgba(255, 215, 79, 0.45);
      transition: all 0.3s ease;
    }

    .step-card:hover .step-number {
      transform: translateY(-2px) scale(1.05);
    }

    /* Studio cards */
    .card.shadow-lg {
      border: 1px solid #e5e7eb;
      background: #ffffff;
      transition: transform 0.2s ease, box-shadow 0.25s ease, border-color 0.2s ease;
      border-radius: 18px;
    }

    .card.shadow-lg:hover {
      transform: translateY(-8px);
      box-shadow: 0 18px 50px rgba(15, 23, 42, 0.15);
      border-color: var(--yellow);
    }

    /* Testimonial carousel */
    #pricing {
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .carousel-item blockquote {
      font-size: 1.05rem;
      color: #0f172a;
    }

    .carousel-item figure {
      border: 1px solid #e5e7eb;
    }

    /* Contact form */
    form.shadow-sm {
      border: 1px solid #e5e7eb;
      background: #ffffff;
      border-radius: 18px;
    }

    /* Footer */
    footer {
      background: #0f172a;
    }

    /* Icon wrapper */
    .icon-wrapper {
      transition: transform 0.3s ease;
    }
    
    .icon-wrapper:hover {
      transform: scale(1.1);
    }

    /* Smooth card hover lift */
    .card-hover {
      transition: transform 0.2s ease, box-shadow 0.25s ease;
    }

    .card-hover:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
    }

    /* FAQ accordion */
    .accordion-button:not(.collapsed) {
      background-color: rgba(255, 183, 3, 0.1);
      color: #000;
      border-color: var(--yellow);
    }

    .accordion-button:focus {
      box-shadow: 0 0 0 0.25rem rgba(255, 183, 3, 0.25);
      border-color: var(--yellow);
    }

    /* Rating stars */
    .rating-stars i {
      font-size: 1.2rem;
      margin: 0 2px;
    }

    /* Avatar */
    .avatar {
      font-weight: bold;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .display-3 {
        font-size: 2.5rem;
      }
      
      .carousel-item figure {
        padding: 1.5rem;
      }
      
      .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
      }
    }
  </style>
</body>
</html>