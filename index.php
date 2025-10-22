<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="color-scheme" content="dark light" />
  <title>Studio — Premium Music Studio</title>
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
          <!-- ✅ perbaikan href agar menuju ke section yang sesuai -->
          <li class="nav-item"><a class="nav-link" href="#services">Panduan Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="#studios">Studio & Jadwal</a></li>
          <li class="nav-item"><a class="nav-link" href="#pricing">Testimoni</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Lokasi</a></li>
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
              nyaman untuk band maupun solo.</p>
            <div class="d-flex flex-wrap gap-2 my-3">
              <a class="btn btn-warning btn-lg rounded-pill" href="#studios">Booking Now</a>
              <a class="btn btn-outline-warning btn-lg rounded-pill" href="#studios">Lihat Studio & Jadwal</a>
            </div>
            <ul class="list-inline text-secondary mb-0">
              <li class="list-inline-item">24/7</li>
              <li class="list-inline-item">Pro Gear</li>
              <li class="list-inline-item">Engineer On-site</li>
              <li class="list-inline-item">Free Wi-Fi</li>
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
                  <div class="fw-bold">6</div>
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
        <div class="card shadow-lg rounded-4">
          <div class="card-body">
            <h1 class="text-center mt-3 mb-1">Panduan Booking</h1>
            <p class="text-secondary mb-0">Langkah-langkah mudah untuk memesan studio latihan musik kami:</p>
            <ol class="mt-3">
              <li>Pilih studio sesuai kebutuhan Anda di bagian <strong>Studio & Jadwal</strong>.</li>
              <li>Lihat ketersediaan waktu, lalu klik <strong>Booking</strong>.</li>
              <li>Isi data dan konfirmasi jadwal.</li>
              <li>Datang sesuai jam yang telah dipesan dan nikmati fasilitas kami 🎶</li>
            </ol>
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
            <div class="card h-100 shadow-lg overflow-hidden rounded-4">
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body">
                <h3 class="h5">Studio A — Studio Gold</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
               <p>- Reguler = 50K/jam<br>- Paket 2 jam = 90K<br>- Paket 3 jam = 130K</p>
              
                <div class="d-flex gap-2">
                  <a href="booking.php?studio=gold" class="btn btn-outline-primary flex-fill">Booking</a>
                  <a href="jadwal.php?studio=gold" class="btn btn-outline-success flex-fill">Lihat Jadwal</a>
                </div>
              </div>
            </div>
          </div>
          <!-- Studio B -->
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4">
              <div class="ratio ratio-16x9"
                style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover">
              </div>
              <div class="card-body">
                <h3 class="h5">Studio B — Studio Bronze</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
                <p>Reguler = 35K/jam (ALL INCLUDE NO KEYBOARD)<br>+ KEYBOARD = 5K/jam</p>

                <div class="d-flex gap-2">
                  <a href="booking.php?studio=bronze" class="btn btn-outline-primary flex-fill">Booking</a>
                  <a href="jadwal.php?studio=bronze" class="btn btn-outline-success flex-fill">Lihat Jadwal</a>
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

        <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <figure class="text-center p-4 border rounded-4 bg-light shadow-sm mx-auto" style="max-width:760px;">
                <blockquote class="blockquote mb-2">“Tempatnya nyaman banget! Peralatannya lengkap dan suaranya mantap.”</blockquote>
                <figcaption class="blockquote-footer mb-0">Rian, <cite title="Musisi">Musisi</cite></figcaption>
              </figure>
            </div>
            <div class="carousel-item">
              <figure class="text-center p-4 border rounded-4 bg-light shadow-sm mx-auto" style="max-width:760px;">
                <blockquote class="blockquote mb-2">“Engineer-nya sangat membantu dan hasil recording-nya keren banget.”</blockquote>
                <figcaption class="blockquote-footer mb-0">Dinda, <cite title="Podcaster">Podcaster</cite></figcaption>
              </figure>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#testiCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#testiCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </section>

    <!-- Lokasi -->
    <section id="testimonials" class="py-5 bg-body-secondary">
      <div style="width:100%; height:400px; border-radius:10px; overflow:hidden;">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.518820641404!2d113.70717147481444!3d-8.156332682104066!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd695dbb3cda6a9%3A0x4d056e98604f1da1!2sJl.%20Bedadung%20Blok%20Durenan%20No.30%2C%20Kp.%20Using%2C%20Jemberlor%2C%20Kec.%20Patrang%2C%20Kabupaten%20Jember%2C%20Jawa%20Timur%2068118!5e0!3m2!1sid!2sid!4v1698371782111!5m2!1sid!2sid"
          width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </section>

    <!-- Kontak -->
    <section id="contact" class="py-5 bg-white">
      <div class="container-fluid px-3 px-md-4">
        <div class="row g-3 align-items-stretch">
          <div class="col-md-6 d-flex justify-content-center align-items-center">
            <div class="text-center">
              <h5>HUBUNGI KAMI</h5>
              <h3>Ayo Diskusi Apa Kek</h3>
              <p>DM Instagram ‘@reys_musicstudio’ , WhatsApp ‘085606564811’ , atau kirim pesan via formulir.</p>
            </div>
          </div>
          <div class="col-md-6">
            <form class="border rounded-4 bg-white p-3 p-md-4 shadow-sm">
              <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input id="name" name="name" class="form-control" required placeholder="Nama lengkap Anda" />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" required placeholder="nama@email.com" />
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Pesan</label>
                <textarea id="message" name="message" rows="5" class="form-control" required
                  placeholder="Ceritakan kebutuhan Anda"></textarea>
              </div>
              <button class="btn btn-primary w-100" type="submit">Kirim</button>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer class="bg-dark text-white pt-5 pb-3">
    <div class="container text-center small">
      <p class="mb-0">© <span data-year></span>Reys Music Studio. All rights reserved.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Smooth Scroll -->
  <script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  </script>
</body>
</html>
