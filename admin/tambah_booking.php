<?php
// admin/tambah_booking.php
require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";

// Cek login sederhana (sesuaikan dengan session Anda)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Paket akan dimuat dinamis berdasarkan studio yang dipilih
$paketOptions = [];

// Ambil tanggal tersedia dari DB
$tanggalTersedia = [];
$tq = $koneksi->query("SELECT DISTINCT tanggal FROM jadwal WHERE status = 'Belum Dibooking' ORDER BY tanggal");
if ($tq) {
    while ($r = $tq->fetch_assoc()) {
        $tanggalTersedia[] = $r['tanggal'];
    }
}

// Ambil daftar studio
$studioList = [];
$q = $koneksi->query("SELECT id_studio, nama FROM studio ORDER BY nama");
if ($q && $q->num_rows > 0) {
    $studioList = $q->fetch_all(MYSQLI_ASSOC);
}

// Safety check
$tanggalTersedia = is_array($tanggalTersedia) ? $tanggalTersedia : [];
$studioList = is_array($studioList) ? $studioList : [];
$paketOptions = is_array($paketOptions) ? $paketOptions : [];
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
  * {
    font-family: 'Poppins', sans-serif;
  }

  .content-body {
    background: white;
    min-height: 100vh;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
  }
  

  .container-fluid {
    position: relative;
    z-index: 1;
    max-width: 1400px;
    margin: 0 auto;
  }

  /* Header Section */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px 0;
  }

  .header-left {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .header-left h4 {
    font-size: 28px;
    font-weight: 800;
    color: black;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .header-left p {
    color: rgba(255, 255, 255, 0.95);
    font-size: 16px;
    font-weight: 500;
    margin: 0;
  }

  .back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 10px 20px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    background: rgba(15, 23, 42, 0.25);
    color: white;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
  }

  .back-button:hover {
    background: rgba(15, 23, 42, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.45);
  }

  /* Form Card */
  .form-card {
    background: white;
    border-radius: 2rem;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
  }

  @keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 0;
  }

  .form-section {
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .form-section:first-child {
    border-right: 1px solid #e5e7eb;
  }

  .form-section:last-child {
    background: linear-gradient(135deg, #f9fafb 0%, #f3e8ff 100%);
  }

  .section-header {
    font-size: 1.375rem;
    font-weight: 700;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #7c3aed;
    margin-bottom: 0.5rem;
  }

  .section-header i {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    color: white;
    border-radius: 0.75rem;
    font-size: 1.25rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
  }

  .form-label {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .form-label .required {
    color: #ef4444;
    font-size: 1.125rem;
  }

  .input-wrapper {
    position: relative;
  }

  .input-icon {
    position: absolute;
    left: 1.125rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.25rem;
    transition: color 0.3s ease;
    z-index: 1;
  }

  .form-input:focus + .input-icon,
  .form-select:focus + .input-icon {
    color: #7c3aed;
  }

  .form-input,
  .form-select {
    width: 100%;
    padding: 1rem 1.125rem 1rem 3.5rem;
    border: 2px solid #d1d5db;
    border-radius: 1rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: white;
  }

  .form-input:hover,
  .form-select:hover {
    border-color: #9ca3af;
  }

  .form-input:focus,
  .form-select:focus {
    outline: none;
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
    background: white;
  }

  .form-input[readonly] {
    background: linear-gradient(135deg, #f9fafb, #faf5ff);
    color: #7c3aed;
    font-weight: 600;
    cursor: default;
  }

  /* Time Slots */
  .time-slots-container {
    margin-top: 0.5rem;
  }

  .legend {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
    padding: 0.875rem 1rem;
    background: white;
    border-radius: 0.75rem;
    border: 2px solid #e5e7eb;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
  }

  .legend-color {
    width: 24px;
    height: 24px;
    border-radius: 0.5rem;
    border: 2px solid #d1d5db;
  }

  .legend-color.available {
    background: linear-gradient(135deg, #ffffff, #f0f9ff);
    border-color: #7c3aed;
  }

  .legend-color.booked {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-color: #ef4444;
  }

  .time-slots {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.875rem;
    max-height: 320px;
    overflow-y: auto;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    background: white;
  }

  .time-slot {
    position: relative;
    cursor: pointer;
    padding: 1rem 0.75rem;
    border: 2px solid #d1d5db;
    border-radius: 0.875rem;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff, #fafafa);
    font-size: 0.9375rem;
  }

  .time-slot:hover:not(.booked) {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #faf5ff, #f0f9ff);
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(124, 58, 237, 0.2);
  }

  .time-slot.selected {
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    color: white;
    border-color: #7c3aed;
    font-weight: 700;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 20px rgba(124, 58, 237, 0.4);
  }

  .time-slot.booked {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #ef4444;
    border-color: #fca5a5;
    cursor: not-allowed;
    opacity: 0.75;
  }

  .time-slot input[type="radio"] {
    display: none;
  }

  .time-text {
    font-weight: 700;
    margin-bottom: 0.375rem;
    font-size: 1rem;
  }

  /* Summary Card */
  .summary-card {
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    border-radius: 1.5rem;
    padding: 2rem;
    color: white;
    box-shadow: 0 15px 35px rgba(124, 58, 237, 0.3);
  }

  .summary-title {
    font-size: 1.375rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
  }

  .summary-title i {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 0.9375rem;
    gap: 1rem;
  }

  .summary-item:last-of-type {
    border: none;
  }

  .summary-label {
    opacity: 0.95;
    font-weight: 600;
    flex-shrink: 0;
  }

  .summary-value {
    font-weight: 700;
    text-align: right;
    word-break: break-word;
  }

  .summary-total {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid rgba(255, 255, 255, 0.3);
  }

  .summary-total .summary-label {
    font-size: 1.25rem;
    font-weight: 800;
  }

  .summary-total .summary-value {
    font-size: 1.75rem;
    font-weight: 900;
  }

  /* Submit Button */
  .btn-submit {
    width: 100%;
    padding: 1.375rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 1rem;
    font-size: 1.25rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    margin-top: 1.5rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }

  .btn-submit:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
    background: linear-gradient(135deg, #059669, #047857);
  }

  .btn-submit:disabled {
    background: linear-gradient(135deg, #9ca3af, #6b7280);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
    opacity: 0.7;
  }

  /* Footer Note */
  .footer-note {
    text-align: center;
    margin-top: 2rem;
    color: white;
    font-size: 0.9375rem;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  .footer-note .required {
    color: #fca5a5;
    font-weight: 700;
  }

  /* Empty State */
  .empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-section:first-child {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
    }
  }

  @media (max-width: 768px) {
    .content-body {
        padding: 15px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .header-left h4 {
        font-size: 2rem;
    }

    .header-left p {
        font-size: 1rem;
    }

    .form-section {
        padding: 1.5rem;
    }

    .time-slots {
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        max-height: 250px;
    }

    .section-header {
        font-size: 1.125rem;
    }

    .summary-card {
        padding: 1.5rem;
    }
  }
</style>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Konten Utama -->
<div class="content-body">
  <div class="container-fluid">
    <!-- Header Page -->
    <div class="page-header">
      <div class="header-left">
        <h4>Booking Offline</h4>
      </div>
      <a href="order.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        <span>Kembali ke daftar booking</span>
      </a>
    </div>

    <!-- Form Card -->
    <div class="form-card">
      <form method="POST" action="../controller/controller_booking.php" id="bookingForm">
        <input type="hidden" name="tambah" value="1">
        <div class="form-grid">
          <!-- Kiri: Input Data -->
          <div class="form-section">
            <!-- Informasi Pelanggan -->
            <div>
              <div class="section-header">
                <i class="fas fa-user"></i>
                <span>Informasi Pelanggan</span>
              </div>
              <div class="form-group">
                <label class="form-label">
                  Nama Lengkap <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <input type="text" name="nama" class="form-input" placeholder="Masukkan nama lengkap" required autofocus>
                  <i class="fas fa-user input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Gunakan nama lengkap sesuai KTP atau identitas lainnya.</small>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrapper">
                  <input type="email" name="email" class="form-input" placeholder="email@contoh.com">
                  <i class="fas fa-envelope input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Opsional, digunakan untuk mengirim bukti booking.</small>
              </div>
              <div class="form-group">
                <label class="form-label">
                  WhatsApp <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <input type="tel" name="telepon" class="form-input" placeholder="08xxxxxxxxxx" pattern="08[0-9]{8,11}" required>
                  <i class="fab fa-whatsapp input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Nomor aktif WhatsApp pelanggan untuk pengingat & konfirmasi.</small>
              </div>
            </div>

            <!-- Jadwal & Paket -->
            <div>
              <div class="section-header">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal & Paket</span>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Pilih Studio <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <select name="id_studio" id="studio" class="form-select" required>
                    <option value="">-- Pilih Studio --</option>
                    <?php foreach ($studioList as $s): ?>
                      <option value="<?= htmlspecialchars($s['id_studio']) ?>">
                        <?= htmlspecialchars($s['nama']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <i class="fas fa-building input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Pilih ruangan studio yang akan digunakan pelanggan.</small>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Pilih Paket <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <select name="paket" id="paket" class="form-select" required disabled>
                    <option value="">-- Pilih Studio Terlebih Dahulu --</option>
                  </select>
                  <i class="fas fa-box input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Pilih studio terlebih dahulu untuk melihat paket yang tersedia.</small>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Pilih Tanggal <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <input type="text" id="tanggalPicker" class="form-input" placeholder="Klik untuk memilih tanggal" readonly required>
                  <input type="hidden" name="tanggal" id="tanggalHidden">
                  <i class="fas fa-calendar input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Hanya tanggal yang masih tersedia yang dapat dipilih.</small>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Pilih Jam <span class="required">*</span>
                </label>
                <div class="legend">
                  <div class="legend-item">
                    <div class="legend-color available"></div>
                    <span>Tersedia</span>
                  </div>
                  <div class="legend-item">
                    <div class="legend-color booked"></div>
                    <span>Sudah Dibooking</span>
                  </div>
                </div>
                <div class="time-slots-container">
                  <div class="time-slots" id="timeSlots">
                    <div class="empty-state">
                      <i class="fas fa-clock"></i>
                      <p>Pilih studio dan tanggal terlebih dahulu</p>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="jadwal_id" id="jadwalId" required>
                <input type="hidden" name="jam_booking" id="jamBooking">
                <input type="hidden" id="selectedJadwalIds" name="selected_jadwal_ids">
                <small id="jamInfo" style="font-size: 0.8rem; color: #6b7280; display:block; margin-top:0.25rem;">Pilih studio, tanggal, dan paket terlebih dahulu untuk melihat jadwal yang tersedia.</small>
              </div>

            </div>
          </div>

          <!-- Kanan: Ringkasan & Pembayaran -->
          <div class="form-section">
            <!-- Pembayaran -->
            <div>
              <div class="section-header">
                <i class="fas fa-credit-card"></i>
                <span>Pembayaran</span>
              </div>
              <div class="form-group">
                <label class="form-label">Total Tagihan</label>
                <div class="input-wrapper">
                  <input type="text" id="totalTagihanDisplay" class="form-input" readonly placeholder="Pilih paket terlebih dahulu">
                  <input type="hidden" name="totalTagihan" id="totalTagihan">
                  <i class="fas fa-rupiah-sign input-icon"></i>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">
                  Metode Pembayaran <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <select name="metodePembayaran" class="form-select" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="qris">QRIS</option>
                    <option value="cash">Tunai</option>
                  </select>
                  <i class="fas fa-wallet input-icon"></i>
                </div>
                <small style="font-size: 0.8rem; color: #6b7280;">Pilih metode pembayaran yang digunakan pelanggan saat ini.</small>
              </div>
            </div>

            <!-- Ringkasan Booking -->
            <div class="summary-card">
              <div class="summary-title">
                <i class="fas fa-receipt"></i>
                <span>Ringkasan Booking</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Pelanggan:</span>
                <span class="summary-value" id="summaryNama">-</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">WhatsApp:</span>
                <span class="summary-value" id="summaryTelepon">-</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Studio:</span>
                <span class="summary-value" id="summaryStudio">-</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Jadwal:</span>
                <span class="summary-value" id="summaryTanggalWaktu">-</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Paket:</span>
                <span class="summary-value" id="summaryPaket">-</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Pembayaran:</span>
                <span class="summary-value" id="summaryMetode">-</span>
              </div>
              <div class="summary-total">
                <div class="summary-item">
                  <span class="summary-label">Total:</span>
                  <span class="summary-value" id="summaryTotal">Rp 0</span>
                </div>
              </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn-submit" id="submitBtn" disabled>
              <i class="fas fa-save"></i>
              <span>Simpan Booking</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
      <p><span class="required">*</span> Wajib diisi</p>
    </div>
  </div>
</div>

<?php require "../master/footer.php"; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
// Format Rupiah
const formatRupiah = (num) => {
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
  }).format(num);
};

// Inisialisasi Flatpickr
const tanggalTersedia = <?= json_encode($tanggalTersedia) ?>;
const fp = flatpickr("#tanggalPicker", {
  locale: "id",
  dateFormat: "Y-m-d",
  altInput: true,
  altFormat: "d F Y",
  minDate: "today",
  enable: tanggalTersedia,
  onChange: function (selectedDates, dateStr) {
    document.getElementById('tanggalHidden').value = dateStr;
    document.getElementById('jadwalId').value = '';
    document.getElementById('jamBooking').value = '';
    document.getElementById('summaryTanggalWaktu').textContent = '-';
    
    // Reset semua slot waktu
    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
    
    const studioId = document.getElementById('studio').value;
    
    if (studioId) {
      loadJadwal(dateStr);
    } else {
      const container = document.getElementById('timeSlots');
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-building"></i>
          <p>Silakan pilih studio terlebih dahulu</p>
        </div>
      `;
    }
    
    checkForm();
  }
});

// Fungsi load jadwal
function loadJadwal(tanggal) {
  const container = document.getElementById('timeSlots');
  const studioId = document.getElementById('studio').value;

  if (!tanggal || !studioId) {
    container.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>Pilih studio dan tanggal terlebih dahulu</p>
      </div>
    `;
    return;
  }

  // Tampilkan loading
  container.innerHTML = `
    <div class="empty-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat jadwal...</p>
    </div>
  `;

  const url = `../controller/controller_booking.php?action=get_jadwal&tanggal=${encodeURIComponent(tanggal)}&studio=${encodeURIComponent(studioId)}`;

  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }
      
      if (!data || !Array.isArray(data) || data.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Tidak ada jadwal tersedia untuk tanggal dan studio ini</p>
          </div>
        `;
        return;
      }
      
      let html = '';
      data.forEach(j => {
        const booked = (j.status && (
          j.status.toLowerCase() === 'dibooking' || 
          j.status.toLowerCase() === 'booked'
        ));
        
        const mulai = j.jam_mulai.substring(0, 5);
        const selesai = j.jam_selesai.substring(0, 5);
        const statusText = booked ? 'Dibooking' : 'Tersedia';

        html += `
          <label class="time-slot ${booked ? 'booked' : ''}" 
            data-jadwal-id="${j.id_jadwal}"
            data-mulai="${mulai}"
            data-selesai="${selesai}"
            ${!booked ? `onclick="selectSlot(this, ${j.id_jadwal}, '${mulai}', '${selesai}', '${tanggal}')"` : ''}>
            <input type="radio" name="waktu" ${booked ? 'disabled' : ''}>
            <div class="time-text">${mulai} - ${selesai}</div>
            <div class="time-status">${statusText}</div>
          </label>
        `;
      });
      
      container.innerHTML = html;
    })
    .catch(err => {
      console.error("Error loading jadwal:", err);
      container.innerHTML = `
        <div class="empty-state" style="color: #ef4444;">
          <i class="fas fa-exclamation-triangle"></i>
          <p>Gagal memuat jadwal</p>
          <p style="font-size: 0.875rem; margin-top: 0.5rem;">${err.message}</p>
        </div>
      `;
    });
}

// Fungsi select slot waktu dengan auto-select berdasarkan durasi paket
function selectSlot(el, id, mulai, selesai, tanggal) {
  const paketSelect = document.getElementById('paket');
  const selectedPaket = paketSelect.options[paketSelect.selectedIndex];
  
  // Cek apakah paket sudah dipilih
  if (!selectedPaket || !selectedPaket.value) {
    alert('Silakan pilih paket terlebih dahulu sebelum memilih jadwal.');
    return;
  }
  
  const duration = selectedPaket ? parseInt(selectedPaket.dataset.duration) || 1 : 1;
  
  // Reset semua selection
  document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
  
  // Ambil semua slot waktu yang tersedia
  const allSlots = Array.from(document.querySelectorAll('.time-slot:not(.booked)'));
  const currentSlotIndex = allSlots.indexOf(el);
  
  if (currentSlotIndex === -1) return;
  
  // Cari slot-slot yang perlu dipilih berdasarkan durasi
  const selectedSlots = [];
  const selectedJadwalIds = [];
  let lastEndTime = '';
  
  for (let i = 0; i < duration && (currentSlotIndex + i) < allSlots.length; i++) {
    const slot = allSlots[currentSlotIndex + i];
    const slotId = slot.getAttribute('data-jadwal-id');
    const slotMulai = slot.getAttribute('data-mulai');
    const slotSelesai = slot.getAttribute('data-selesai');
    
    if (!slotId || slot.classList.contains('booked')) {
      // Jika ada slot yang sudah dibooking, batalkan semua selection
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      alert(`Tidak dapat memilih ${duration} jam berturut-turut. Ada slot yang sudah dibooking.`);
      return;
    }
    
    // Verifikasi slot-slot berturut-turut
    if (i > 0) {
      const prevSlot = allSlots[currentSlotIndex + i - 1];
      const prevSelesai = prevSlot.getAttribute('data-selesai');
      
      if (slotMulai !== prevSelesai) {
        // Slot tidak berturut-turut
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        alert(`Tidak dapat memilih ${duration} jam berturut-turut. Slot tidak tersedia.`);
        return;
      }
    }
    
    slot.classList.add('selected');
    selectedSlots.push(slot);
    selectedJadwalIds.push(slotId);
    lastEndTime = slotSelesai;
  }
  
  // Validasi apakah semua slot berhasil dipilih
  if (selectedJadwalIds.length < duration) {
    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
    alert(`Tidak dapat memilih ${duration} jam berturut-turut. Slot tidak tersedia.`);
    return;
  }
  
  // Set nilai form
  document.getElementById('jadwalId').value = selectedJadwalIds[0]; // ID jadwal pertama untuk kompatibilitas
  document.getElementById('selectedJadwalIds').value = selectedJadwalIds.join(',');
  document.getElementById('jamBooking').value = mulai;
  
  // Format tanggal untuk summary
  const date = new Date(tanggal + 'T00:00:00').toLocaleDateString('id-ID', { 
    weekday: 'long', 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  });
  
  // Format waktu untuk summary
  const waktuText = duration > 1 
    ? `${mulai} - ${lastEndTime} (${duration} jam)`
    : `${mulai} - ${selesai}`;
  
  document.getElementById('summaryTanggalWaktu').textContent = `${date}, ${waktuText}`;
  checkForm();
}

// Fungsi update total
function updateTotal() {
  const select = document.getElementById('paket');
  const opt = select.options[select.selectedIndex];
  const price = opt?.dataset.price;
  const duration = opt?.dataset.duration;
  
  if (price) {
    document.getElementById('totalTagihan').value = price;
    document.getElementById('totalTagihanDisplay').value = formatRupiah(price);
    document.getElementById('summaryTotal').textContent = formatRupiah(price);
    document.getElementById('summaryPaket').textContent = opt.text.split(' - ')[0];
    
    // Update info jam berdasarkan durasi paket
    const jamInfo = document.getElementById('jamInfo');
    if (duration) {
      jamInfo.style.color = '#7c3aed';
      jamInfo.style.fontWeight = '600';
    }
  } else {
    document.getElementById('totalTagihan').value = '';
    document.getElementById('totalTagihanDisplay').value = '';
    document.getElementById('summaryTotal').textContent = 'Rp 0';
    document.getElementById('summaryPaket').textContent = '-';
    
    const jamInfo = document.getElementById('jamInfo');
    jamInfo.textContent = 'Pilih studio, tanggal, dan paket terlebih dahulu untuk melihat jadwal yang tersedia.';
    jamInfo.style.color = '#6b7280';
    jamInfo.style.fontWeight = 'normal';
  }
  checkForm();
}

// Event listener untuk form input
document.getElementById('bookingForm').addEventListener('input', function(e) {
  const name = e.target.name;
  const val = e.target.value;
  
  switch (name) {
    case 'nama': 
      document.getElementById('summaryNama').textContent = val || '-'; 
      break;
    case 'telepon': 
      document.getElementById('summaryTelepon').textContent = val || '-'; 
      break;
    case 'metodePembayaran':
      const text = e.target.options[e.target.selectedIndex]?.text;
      document.getElementById('summaryMetode').textContent = text || '-'; 
      break;
    case 'paket':
      updateTotal();
      // Reset jadwal selection ketika paket berubah
      document.getElementById('jadwalId').value = '';
      document.getElementById('selectedJadwalIds').value = '';
      document.getElementById('jamBooking').value = '';
      document.getElementById('summaryTanggalWaktu').textContent = '-';
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      break;
  }
  checkForm();
});

// Fungsi untuk memuat paket berdasarkan studio
function loadPaketByStudio(studioId) {
  const paketSelect = document.getElementById('paket');
  
  if (!studioId) {
    paketSelect.innerHTML = '<option value="">-- Pilih Studio Terlebih Dahulu --</option>';
    paketSelect.disabled = true;
    return;
  }
  
  // Tampilkan loading
  paketSelect.innerHTML = '<option value="">Memuat paket...</option>';
  paketSelect.disabled = true;
  
  const url = `../controller/controller_booking.php?action=get_paket_by_studio&studio_id=${encodeURIComponent(studioId)}`;
  
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }
      
      if (!data || !Array.isArray(data) || data.length === 0) {
        paketSelect.innerHTML = '<option value="">Tidak ada paket tersedia</option>';
        paketSelect.disabled = true;
        return;
      }
      
      // Isi dropdown paket
      let html = '<option value="">-- Pilih Paket --</option>';
      data.forEach(p => {
        html += `<option value="${p.value}" data-price="${p.price}" data-duration="${p.duration}">
          ${p.label} - Rp ${new Intl.NumberFormat('id-ID').format(p.price)}
        </option>`;
      });
      
      paketSelect.innerHTML = html;
      paketSelect.disabled = false;
      
      // Reset total
      document.getElementById('totalTagihan').value = '';
      document.getElementById('totalTagihanDisplay').value = '';
      document.getElementById('summaryTotal').textContent = 'Rp 0';
      document.getElementById('summaryPaket').textContent = '-';
      
      checkForm();
    })
    .catch(err => {
      console.error("Error loading paket:", err);
      paketSelect.innerHTML = '<option value="">Gagal memuat paket</option>';
      paketSelect.disabled = true;
    });
}

// Event listener untuk perubahan studio
document.getElementById('studio').addEventListener('change', function() {
  const tanggal = document.getElementById('tanggalHidden').value;
  
  // Reset jadwal selection
  document.getElementById('jadwalId').value = '';
  document.getElementById('jamBooking').value = '';
  document.getElementById('selectedJadwalIds').value = '';
  document.getElementById('summaryTanggalWaktu').textContent = '-';
  document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
  
  // Reset paket
  loadPaketByStudio(this.value);
  
  // Update ringkasan studio
  const selectedOption = this.options[this.selectedIndex];
  document.getElementById('summaryStudio').textContent = selectedOption && selectedOption.value 
    ? selectedOption.text 
    : '-';
  
  if (tanggal && this.value) {
    loadJadwal(tanggal);
  } else if (!tanggal) {
    const container = document.getElementById('timeSlots');
    container.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-calendar"></i>
        <p>Silakan pilih tanggal terlebih dahulu</p>
      </div>
    `;
  }
  
  checkForm();
});

// Fungsi check form validity
function checkForm() {
  const required = ['nama', 'telepon', 'jadwal_id', 'paket', 'metodePembayaran', 'id_studio', 'tanggal'];
  const complete = required.every(field => {
    const el = document.querySelector(`[name="${field}"]`);
    const value = el ? el.value.toString().trim() : '';
    return value !== '';
  });
  
  document.getElementById('submitBtn').disabled = !complete;
}

// Initial check
checkForm();
</script>