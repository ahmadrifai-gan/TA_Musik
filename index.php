<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="color-scheme" content="dark light" />
  <title> Studio — Premium Music Studio</title>
  <meta name="description"
    content="Studio musik premium dengan ruang kedap suara, peralatan pro, dan engineer berpengalaman. Booking mudah, harga transparan." />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin /> <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
    rel="stylesheet" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


  <link rel="icon"
    href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🎵</text></svg>">
</head>

<body>

  <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid px-3 px-md-5">
      <a class="navbar-brand d-inline-flex align-items-center gap-2" href="#home" aria-label="Beranda Reys Music Studio">
        <span class="badge text-bg-dark rounded-3">♬</span>
        <span class="fw-bold">Reys Music Studio</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="#booking">Panduan Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="#studios">Studio & Jadwal</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Testimoni</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2">
          <?php if (isset($_SESSION['username'])): ?>
          <div class="dropdown">
            <a class="btn btn-dark btn-sm dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              👤 <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="riwayat_reservasi.php">Riwayat Reservasi</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
  <a class="dropdown-item" href="javascript:void(0);" onclick="confirmLogout()">Logout</a>
</li>
<script>
  function confirmLogout() {
    if (confirm("Apakah Anda yakin ingin logout?")) {
      window.location.href = "logout.php";
    }
  }
</script>

            </ul>
          </div>
          <?php else: ?>
          <a class="btn btn-outline-dark btn-sm rounded-pill" href="login.php">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <main id="main">
    <!-- Hero -->
    <section id="home" class="py-5" aria-label="Pembuka">
      <div class="container-fluid px-3 px-md-5 py-4">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <p class="text-uppercase small text-secondary fw-bold mb-2">WELCOME</p>
            <h1 class="display-3 fw-bold lh-sm" style="font-family:'Playfair Display', Georgia, serif;">Studio Latihan Musik<br/>dengan Akustik &<br/>Fasilitas Terbaik</h1>
            <p class="text-secondary">Ruang latihan dengan akustik terkontrol, peralatan musik berkualitas, dan suasana nyaman untuk band maupun solo.</p>
            <div class="d-flex flex-wrap gap-2 my-3">
              <a class="btn btn-warning btn-lg rounded-pill" href="#booking" data-open-booking>Booking Now</a>
              <a class="btn btn-outline-warning btn-lg rounded-pill" href="#studios">Lihat Studio & Jadwal</a>
            </div>
            <ul class="list-inline text-secondary mb-0">
              <li class="list-inline-item">24/7</li>
              <li class="list-inline-item">Pro Gear</li>
              <li class="list-inline-item">Engineer On-site</li>
              <li class="list-inline-item">Free Wi‑Fi</li>
            </ul>
          </div>
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm overflow-hidden rounded-4 bg-body-tertiary" style="min-height:360px;">
              <div class="ratio ratio-4x3" role="img" aria-label="Placeholder media studio" style="background:#ddd;">
                <img src="assets/image/studio.jpg" alt="">
              </div>
              <div class="card-img-overlay d-flex gap-2 align-items-end p-3" style="background:linear-gradient(180deg,transparent,rgba(0,0,0,.55))">
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

    <!-- Services -->
    <section id="services" class="py-5" aria-labelledby="services-title">
      <div class="container-fluid px-3 px-md-5">
        <div class="row">
            <div class="card h-100 shadow-lg rounded-4">
              <div class="card-body"> 
                <h1 class=" mt-3 mb-1 text-center">Panduan Booking</h1>
                <p class="text-secondary mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Magnam quasi velit molestias amet aperiam <br> doloremque expedita tempora animi suscipit adipisci ipsum ab, ipsa eius quia laudantium excepturi et asperiores eaque nam deserunt, <br> voluptatum numquam! Nisi, eius. Delectus non officia, quia sequi molestias animi impedit laudantium, illo eveniet reprehenderit dolor fuga doloribus. Quo dicta, quod quas doloribus quos consectetur possimus delectus odit voluptates corrupti vero, dolorum labore excepturi maxime. Quam debitis est veritatis vel nulla facilis recusandae fugiat quos ex corporis hic, iste facere adipisci veniam impedit at animi eaque velit vitae repellat obcaecati eos expedita illum. Eligendi a laboriosam cum!</p>
            </div>
          </div>
          
        </div>
      </div>
    </section>

    <!-- Studios -->
    <section id="studios" class="py-5 bg-body-secondary" aria-labelledby="studios-title">
      <div class="container-fluid px-3 px-md-5">
        <header class="text-center mb-4">
          <h2 id="studios-title" class="fw-bold">Studio & Jadwal</h2>
        </header>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4">
              <div class="ratio ratio-16x9" role="img" aria-label="Studio B dengan drum kit dan dinding akustik" style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover"></div>
              <div class="card-body">
                <h3 class="h5">Studio A — Studio Gold</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
                <ul class="list-inline text-secondary small mb-3">
                  <li class="list-inline-item">Drum: Maple Custom</li>
                  <li class="list-inline-item">Mic: SM7B, e609, D112</li>
                  <li class="list-inline-item">Monitoring: In‑ear & wedges</li>
                </ul>
                <a class="btn btn-outline-secondary btn-sm" href="#booking" data-open-booking data-studio="Studio B">Booking</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100 shadow-lg overflow-hidden rounded-4">
              <div class="ratio ratio-16x9" role="img" aria-label="Studio B dengan drum kit dan dinding akustik" style="background:url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1600&auto=format&fit=crop') center/cover"></div>
              <div class="card-body">
                <h3 class="h5">Studio B — Studio Bronze</h3>
                <p class="text-secondary">Ruang live untuk band full, drum tracking, dan live session video.</p>
                <ul class="list-inline text-secondary small mb-3">
                  <li class="list-inline-item">All Include Fullset</li>
                </ul>
                  <p>- Reguler = 50K/jam<br>
           - Paket 2 jam = 90K<br>
           - Paket 3 jam = 130K</p>
        <a href="booking.php?studio=gold" class="btn btn-outline-primary">Booking</a>
      </div>
            </div>
          </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-5" aria-labelledby="pricing-title">
      <div class="container-fluid px-3 px-md-4">
        <header class="text-center mb-4">
          <p class="text-uppercase small text-primary fw-bold">Harga transparan</p>
          <h2 id="pricing-title" class="fw-bold">Paket Fleksibel</h2>
        </header>
        <div class="row row-cols-1 row-cols-md-3 g-3">
          <div class="col">
            <div class="card h-100 shadow-lg rounded-4">
              <div class="card-body d-flex flex-column">
                <h3 class="h5">Rehearsal</h3>
                <p class="fs-4 fw-bold"><span class="text-secondary">Rp</span>120K<span class="text-secondary">/jam</span></p>
                <ul class="text-secondary">
                  <li>Ruang kedap suara</li>
                  <li>Backline standar</li>
                  <li>Gratis air mineral</li>
                </ul>
                <a class="btn btn-primary mt-auto w-100" href="#booking" data-open-booking data-studio="Rehearsal">Pilih</a>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 shadow-lg rounded-4 border-primary">
              <div class="card-body d-flex flex-column position-relative">
                <span class="position-absolute top-0 end-0 m-2 badge text-bg-primary">Favorit</span>
                <h3 class="h5">Recording</h3>
                <p class="fs-4 fw-bold"><span class="text-secondary">Rp</span>350K<span class="text-secondary">/jam</span></p>
                <ul class="text-secondary">
                  <li>Engineer on‑site</li>
                  <li>Mic premium</li>
                  <li>Editing dasar</li>
                </ul>
                <a class="btn btn-primary mt-auto w-100" href="#booking" data-open-booking data-studio="Recording">Pilih</a>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 shadow-lg rounded-4">
              <div class="card-body d-flex flex-column">
                <h3 class="h5">Mixing & Mastering</h3>
                <p class="fs-4 fw-bold"><span class="text-secondary">Rp</span>1.8Jt<span class="text-secondary">/track</span></p>
                <ul class="text-secondary">
                  <li>3x revisi</li>
                  <li>Radio-ready</li>
                  <li>Delivery WAV + MP3</li>
                </ul>
                <a class="btn btn-primary mt-auto w-100" href="#booking" data-open-booking data-studio="Mixing">Pilih</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-5 bg-body-secondary" aria-labelledby="testimonials-title">
      <div class="container-fluid px-3 px-md-4">
        <header class="text-center mb-4">
          <p class="text-uppercase small text-primary fw-bold">Dipercaya kreator</p>
          <h2 id="testimonials-title" class="fw-bold">Apa Kata Mereka</h2>
        </header>
        <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#testiCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#testiCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <figure class="text-center p-4 border rounded-4 bg-white shadow-sm mx-auto" style="max-width:760px;">
                <blockquote class="blockquote mb-2">“Monitoring paling jernih yang pernah saya coba. Engineer-nya cepat nangkep kemauan saya.”</blockquote>
                <figcaption class="blockquote-footer mb-0">Raka Pratama, <cite title="Produser">Produser</cite></figcaption>
              </figure>
            </div>
            <div class="carousel-item">
              <figure class="text-center p-4 border rounded-4 bg-white shadow-sm mx-auto" style="max-width:760px;">
                <blockquote class="blockquote mb-2">“Setup podcast lengkap, tinggal duduk rekam. Hasilnya rapi dan cepat.”</blockquote>
                <figcaption class="blockquote-footer mb-0">Sinta & Naya, <cite title="Podcaster">Podcaster</cite></figcaption>
              </figure>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#testiCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#testiCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Berikutnya</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-5 bg-white" aria-labelledby="contact-title">
      <div class="container-fluid px-3 px-md-4">

        <div class="row g-3 align-items-stretch">
          <div class="col-md-6 d-flex justify-content-center align-items-center">
            <div class="text-center">
              <h5>HUBUNGI KAMI</h5>
              <h3>Ayo Diskusi Apa Kek</h3>
              <p>DM Instagram ‘@reys_musicstudio’ , WhatsApp ‘085606564811’ , atau <br> kirim pesan via formulir.</p>
            </div>
          </div>
          <div class="col-md-6">
            <form class="border rounded-4 bg-white p-3 p-md-4 shadow-sm" aria-label="Formulir kontak" data-contact-form>
              <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input id="name" name="name" class="form-control" required autocomplete="name" placeholder="Nama lengkap Anda" />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" required autocomplete="email" placeholder="nama@email.com" />
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Pesan</label>
                <textarea id="message" name="message" rows="5" class="form-control" required placeholder="Ceritakan kebutuhan Anda"></textarea>
              </div>
              <button class="btn btn-primary w-100" type="submit">Kirim</button>
              <p class="form-text" role="status" aria-live="polite" hidden data-form-note></p>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-5">
          <a class="d-inline-flex align-items-center gap-2 text-white text-decoration-none" href="#home">
            <span class="badge text-bg-primary rounded-3">♬</span>
            <span class="fw-bold">ARIA Studio</span>
          </a>
          <p class="text-white-50 mt-2">Studio musik premium untuk rehearsal, recording, mixing & mastering. Akustik terkontrol, gear profesional, dan engineer berpengalaman.</p>
        </div>
        <div class="col-6 col-lg-3">
          <h6 class="text-uppercase text-white-50">Jam Operasional</h6>
          <ul class="list-unstyled small mb-0 text-white-50">
            <li>Senin - Jumat: 10.00 – 22.00</li>
            <li>Sabtu - Minggu: 09.00 – 23.00</li>
            <li>By appointment</li>
          </ul>
        </div>
        <div class="col-6 col-lg-4">
          <h6 class="text-uppercase text-white-50">Ikuti Kami</h6>
          <div class="d-grid gap-2">
            <a href="#" class="text-white-50 text-decoration-none"><i class="bi bi-instagram me-2"></i>@pai.studio</a>
            <a href="#" class="text-white-50 text-decoration-none"><i class="bi bi-youtube me-2"></i>ARIA Studio</a>
            <a href="#" class="text-white-50 text-decoration-none"><i class="bi bi-tiktok me-2"></i>@aria.studio</a>
          </div>
        </div>
      </div>
      <hr class="border-secondary my-4" />
      <div class="d-flex justify-content-between align-items-center small text-white-50">
        <p class="mb-0">© <span data-year></span> ARIA Studio. All rights reserved.</p>
        <div class="d-none d-md-inline">Made with <i class="bi bi-heart-fill text-danger"></i> & Bootstrap</div>
      </div>
    </div>
  </footer>

  <!-- Booking Modal -->
  <dialog class="modal" data-booking-modal aria-labelledby="modal-title">
    <form method="dialog" class="modal-panel glow" data-booking-form>
      <button class="modal-close" value="close" aria-label="Tutup">✕</button>
      <header class="modal-header">
        <h3 id="modal-title">Booking Studio</h3>
        <p class="modal-sub">Pilih jadwal dan lengkapi detail Anda.</p>
      </header>
      <div class="modal-body">
        <div class="field">
          <label for="studio">Studio</label>
          <select id="studio" name="studio" required>
            <option value="Rehearsal">Rehearsal</option>
            <option value="Recording">Recording</option>
            <option value="Mixing">Mixing & Mastering</option>
            <option value="Studio A">Studio A</option>
            <option value="Studio B">Studio B</option>
          </select>
        </div>
        <div class="field grid">
          <div>
            <label for="date">Tanggal</label>
            <input id="date" name="date" type="date" required />
          </div>
          <div>
            <label for="time">Jam</label>
            <input id="time" name="time" type="time" required />
          </div>
        </div>
        <div class="field grid">
          <div>
            <label for="duration">Durasi (jam)</label>
            <input id="duration" name="duration" type="number" min="1" max="12" value="2" required />
          </div>
          <div>
            <label for="people">Jumlah orang</label>
            <input id="people" name="people" type="number" min="1" max="10" value="2" required />
          </div>
        </div>
        <div class="field">
          <label for="fullname">Nama lengkap</label>
          <input id="fullname" name="fullname" required />
        </div>
        <div class="field">
          <label for="phone">Nomor WhatsApp</label>
          <input id="phone" name="phone" type="tel" required />
        </div>
      </div>
      <footer class="modal-footer">
        <button class="btn btn-ghost" value="close">Batal</button>
        <button class="btn btn-primary" value="submit">Kirim Permintaan</button>
      </footer>
      <p class="form-note" role="status" aria-live="polite" hidden data-booking-note></p>
    </form>
  </dialog>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js" defer></script>
</body>

</html>