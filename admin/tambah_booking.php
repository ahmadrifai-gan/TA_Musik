<?php
// admin/tambah_booking.php (FULL - diperbaiki)
session_start();
require "../config/koneksi.php";

// ---------- cek koneksi ----------
if ($koneksi->connect_errno) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// default paket (jika session tidak punya)
$paketOptions = [
    ['value' => 'paket-bronze', 'label' => 'Paket Bronze', 'price' => 500000],
    ['value' => 'paket-silver', 'label' => 'Paket Silver', 'price' => 1000000],
    ['value' => 'paket-gold', 'label' => 'Paket Gold', 'price' => 2000000],
    ['value' => 'paket-platinum', 'label' => 'Paket Platinum', 'price' => 3500000]
];

// ---------- ambil tanggal tersedia dari session atau DB ----------
$tanggalTersedia = $_SESSION['booking_data']['tanggalTersedia'] ?? [];
if (!is_array($tanggalTersedia) || empty($tanggalTersedia)) {
    $tq = $koneksi->query("SELECT DISTINCT tanggal FROM jadwal WHERE status = 'Belum Dibooking' ORDER BY tanggal");
    $tanggalTersedia = [];
    if ($tq) {
        while ($r = $tq->fetch_assoc()) {
            $tanggalTersedia[] = $r['tanggal'];
        }
    }
}

// ---------- ambil daftar studio (dengan fallback dan cek nama kolom) ----------
$studioList = $_SESSION['booking_data']['studioList'] ?? [];

if (!is_array($studioList) || empty($studioList)) {
    // coba query dengan kolom 'nama'
    $q1 = $koneksi->query("SELECT id_studio, nama FROM studio ORDER BY nama");
    if ($q1 && $q1->num_rows > 0) {
        $studioList = $q1->fetch_all(MYSQLI_ASSOC);
        // pastikan setiap row punya key 'nama'
        foreach ($studioList as &$r) {
            if (!isset($r['nama']))
                $r['nama'] = $r['nama_studio'] ?? '';
        }
        unset($r);
    } else {
        // jika gagal (kolom 'nama' mungkin tidak ada), coba 'nama_studio'
        $q2 = $koneksi->query("SELECT id_studio, nama_studio FROM studio ORDER BY nama_studio");
        $studioList = [];
        if ($q2 && $q2->num_rows > 0) {
            while ($s = $q2->fetch_assoc()) {
                // normalisasi key jadi 'nama'
                $studioList[] = [
                    'id_studio' => $s['id_studio'],
                    'nama' => $s['nama_studio']
                ];
            }
        }
    }
}

// jika session punya paketOptions gunakan itu
if (isset($_SESSION['booking_data']['paketOptions']) && is_array($_SESSION['booking_data']['paketOptions'])) {
    $paketOptions = $_SESSION['booking_data']['paketOptions'];
}

// safety: pastikan array
$tanggalTersedia = is_array($tanggalTersedia) ? $tanggalTersedia : [];
$studioList = is_array($studioList) ? $studioList : [];
$paketOptions = is_array($paketOptions) ? $paketOptions : [];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Walk-in Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --success: #10b981;
            --danger: #ef4444;
            --gray-100: #f3f4f6;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-900: #1f2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f3e8ff 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            color: var(--gray-900);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--gray-500);
            font-size: 1rem;
        }

        .form-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .form-section {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .section-header {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0e7ff;
        }

        .section-header i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .form-label .required {
            color: var(--danger);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.125rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-input[readonly] {
            background: #f9fafb;
            color: var(--primary);
            font-weight: 600;
        }

        .time-slots-container {
            margin-top: 1rem;
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 0.75rem;
            max-height: 280px;
            overflow-y: auto;
            padding: 0.5rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.75rem;
            background: #fafafa;
        }

        .time-slot {
            position: relative;
            cursor: pointer;
            padding: 0.875rem 0.5rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            text-align: center;
            transition: all 0.3s;
            background: white;
            font-size: 0.875rem;
        }

        .time-slot:hover:not(.booked) {
            border-color: var(--primary);
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .time-slot.selected {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            font-weight: 600;
        }

        .time-slot.booked {
            background: var(--gray-100);
            color: #9ca3af;
            border-color: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .time-slot input[type="radio"] {
            display: none;
        }

        .time-text {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .time-status {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 1rem;
            padding: 1.5rem;
            color: white;
        }

        .summary-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
        }

        .summary-item:last-child {
            border: none;
        }

        .summary-label {
            opacity: 0.9;
        }

        .summary-value {
            font-weight: 600;
            text-align: right;
        }

        .summary-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
        }

        .summary-total .summary-label {
            font-size: 1.125rem;
            font-weight: 700;
        }

        .summary-total .summary-value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .btn-submit {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            margin-top: 1rem;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }

        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .footer-note {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .legend {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 18px;
            height: 18px;
            border-radius: 0.25rem;
            border: 2px solid var(--gray-300);
        }

        .legend-color.available {
            background: white;
        }

        .legend-color.booked {
            background: var(--gray-100);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 1.875rem;
            }

            .time-slots {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
        .time-slot.booked {
    background: #ffdddd;
    border: 1px solid #ff6b6b;
    color: #b40000;
    cursor: not-allowed;
    opacity: 0.7;
}
.time-slot.booked .time-status {
    color: #b40000;
    font-weight: bold;
}

    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-icon"><i class="fas fa-calendar-check"></i></div>
            <h1>Booking Walk-in</h1>
            <p>Lengkapi data pelanggan yang datang langsung ke studio</p>
        </div>

        <form method="POST" action="../controller/controller_booking.php" class="form-card" id="bookingForm">
            <div class="form-grid">
                <!-- Kiri: Input -->
                <div class="form-section">
                    <div>
                        <div class="section-header"><i class="fas fa-user"></i> Informasi Pelanggan</div>
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fas fa-user input-icon"></i>
                                <input type="text" name="nama" class="form-input" placeholder="Nama lengkap" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div class="input-wrapper"><i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-input" placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fab fa-whatsapp input-icon"></i>
                                <input type="tel" name="telepon" class="form-input" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="section-header"><i class="fas fa-calendar-alt"></i> Jadwal & Paket</div>

                        <!-- Pilih Studio -->
                        <div class="form-group">
                            <label class="form-label">Pilih Studio <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fas fa-building input-icon"></i>
                                <select name="id_studio" id="studio" class="form-select" required
                                    onchange="loadJadwal(document.getElementById('tanggalHidden').value)">
                                    <option value="">-- Pilih Studio --</option>
                                    <?php foreach ($studioList as $s): ?>
                                        <option value="<?= htmlspecialchars($s['id_studio']) ?>">
                                            <?= htmlspecialchars($s['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="form-group">
                            <label class="form-label">Pilih Tanggal <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fas fa-calendar input-icon"></i>
                                <input type="text" id="tanggalPicker" class="form-input"
                                    placeholder="Klik untuk memilih" readonly required>
                                <input type="hidden" name="tanggal" id="tanggalHidden">
                            </div>
                        </div>

                        <!-- Jam -->
                        <div class="form-group">
                            <label class="form-label">Pilih Jam <span class="required">*</span></label>
                            <div class="legend">
                                <div class="legend-item">
                                    <div class="legend-color available"></div><span>Belum Dibooking</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color booked"></div><span>Dibooking</span>
                                </div>
                            </div>
                            <div class="time-slots-container">
                                <div class="time-slots" id="timeSlots">
                                    <div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
                                        <i class="fas fa-clock" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                                        <p>Pilih studio dan tanggal terlebih dahulu</p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="jadwal_id" id="jadwalId" required>
                            <input type="hidden" name="jam_booking" id="jamBooking">
                        </div>

                        <!-- Paket -->
                        <div class="form-group">
                            <label class="form-label">Pilih Paket <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fas fa-box input-icon"></i>
                                <select name="paket" id="paket" class="form-select" required onchange="updateTotal()">
                                    <option value="">-- Pilih Paket --</option>
                                    <?php foreach ($paketOptions as $p): ?>
                                        <option value="<?= htmlspecialchars($p['value']) ?>"
                                            data-price="<?= htmlspecialchars($p['price']) ?>">
                                            <?= htmlspecialchars($p['label']) ?> - Rp
                                            <?= number_format($p['price'], 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Ringkasan & Pembayaran -->
                <div class="form-section">
                    <div>
                        <div class="section-header"><i class="fas fa-credit-card"></i> Pembayaran</div>
                        <div class="form-group">
                            <label class="form-label">Total Tagihan</label>
                            <div class="input-wrapper"><i class="fas fa-rupiah-sign input-icon"></i>
                                <input type="text" id="totalTagihanDisplay" class="form-input" readonly
                                    placeholder="Pilih paket">
                                <input type="hidden" name="totalTagihan" id="totalTagihan">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Metode Pembayaran <span class="required">*</span></label>
                            <div class="input-wrapper"><i class="fas fa-wallet input-icon"></i>
                                <select name="metodePembayaran" class="form-select" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="qris">QRIS</option>
                                    <option value="va">Virtual Account</option>
                                    <option value="kes">Tunai</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-title"><i class="fas fa-receipt"></i> Ringkasan</div>
                        <div class="summary-item"><span class="summary-label">Pelanggan:</span><span
                                class="summary-value" id="summaryNama">-</span></div>
                        <div class="summary-item"><span class="summary-label">WhatsApp:</span><span
                                class="summary-value" id="summaryTelepon">-</span></div>
                        <div class="summary-item"><span class="summary-label">Jadwal:</span><span class="summary-value"
                                id="summaryTanggalWaktu">-</span></div>
                        <div class="summary-item"><span class="summary-label">Paket:</span><span class="summary-value"
                                id="summaryPaket">-</span></div>
                        <div class="summary-item"><span class="summary-label">Pembayaran:</span><span
                                class="summary-value" id="summaryMetode">-</span></div>
                        <div class="summary-total">
                            <div class="summary-item"><span class="summary-label">Total:</span><span
                                    class="summary-value" id="summaryTotal">Rp 0</span></div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" disabled><i class="fas fa-save"></i> Simpan
                        Booking</button>
                </div>
            </div>
        </form>

        <div class="footer-note">
            <p><span class="required">*</span> Wajib diisi</p>
        </div>
    </div>

<script>
    const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(num);

    // Inisialisasi Flatpickr
    flatpickr("#tanggalPicker", {
        locale: "id",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d F Y",
        minDate: "today",
        enable: <?= json_encode($tanggalTersedia) ?>.map(d => new Date(d)),
        onChange: function (selectedDates, dateStr) {
            console.log("Tanggal dipilih:", dateStr); // Debug
            
            document.getElementById('tanggalHidden').value = dateStr;
            document.getElementById('jadwalId').value = '';
            document.getElementById('jamBooking').value = '';
            document.getElementById('summaryTanggalWaktu').textContent = '-';
            
            // Reset semua slot waktu
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            
            const studioId = document.getElementById('studio').value;
            console.log("Studio ID:", studioId); // Debug
            
            if (studioId) {
                loadJadwal(dateStr);
            } else {
                // Tampilkan pesan untuk pilih studio dulu
                const container = document.getElementById('timeSlots');
                container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
                    <i class="fas fa-building" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                    <p>Silakan pilih studio terlebih dahulu</p>
                </div>`;
            }
            
            checkForm();
        }
    });

    // Fungsi load jadwal yang diperbaiki
    function loadJadwal(tanggal) {
        const container = document.getElementById('timeSlots');
        const studioId = document.getElementById('studio').value;

        console.log("loadJadwal called - Tanggal:", tanggal, "Studio:", studioId); // Debug

        if (!tanggal || !studioId) {
            container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
                <i class="fas fa-info-circle" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                <p>Pilih studio dan tanggal terlebih dahulu</p>
            </div>`;
            return;
        }

        // Tampilkan loading
        container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
            <i class="fas fa-spinner fa-spin" style="font-size:2rem;margin-bottom:0.5rem;"></i>
            <p>Memuat jadwal...</p>
        </div>`;

        const url = `get_jadwal.php?tanggal=${encodeURIComponent(tanggal)}&studio=${encodeURIComponent(studioId)}`;
        console.log("Fetching URL:", url); // Debug

        fetch(url)
            .then(response => {
                console.log("Response status:", response.status); // Debug
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Data received:", data); // Debug
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (!data || !Array.isArray(data) || data.length === 0) {
                    container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
                        <i class="fas fa-calendar-times" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                        <p>Tidak ada jadwal tersedia untuk tanggal dan studio ini</p>
                    </div>`;
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
                    const statusText = booked ? 'Dibooking' : 'Belum Dibooking';

                    html += `<label class="time-slot ${booked ? 'booked' : ''}" 
                        ${!booked ? `onclick="selectSlot(this, ${j.id_jadwal}, '${mulai}', '${selesai}', '${tanggal}')"` : ''}>
                        <input type="radio" name="waktu" ${booked ? 'disabled' : ''}>
                        <div class="time-text">${mulai} - ${selesai}</div>
                        <div class="time-status">${statusText}</div>
                    </label>`;
                });
                
                container.innerHTML = html;
                console.log("Jadwal berhasil dimuat:", data.length, "slot"); // Debug
            })
            .catch(err => {
                console.error("Error loading jadwal:", err); // Debug
                container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                    <p>Gagal memuat jadwal</p>
                    <p style="font-size:0.8rem;margin-top:0.5rem;">${err.message}</p>
                </div>`;
            });
    }

    function selectSlot(el, id, mulai, selesai, tanggal) {
        console.log("Slot selected:", id, mulai, selesai); // Debug
        
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        if (el) el.classList.add('selected');
        
        document.getElementById('jadwalId').value = id;
        document.getElementById('jamBooking').value = mulai;
        
        const date = new Date(tanggal).toLocaleDateString('id-ID', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
        
        document.getElementById('summaryTanggalWaktu').textContent = `${date}, ${mulai} - ${selesai}`;
        checkForm();
    }

    function updateTotal() {
        const select = document.getElementById('paket');
        const opt = select.options[select.selectedIndex];
        const price = opt?.dataset.price;
        
        if (price) {
            document.getElementById('totalTagihan').value = price;
            document.getElementById('totalTagihanDisplay').value = formatRupiah(price);
            document.getElementById('summaryTotal').textContent = formatRupiah(price);
            document.getElementById('summaryPaket').textContent = opt.text.split(' - ')[0];
        } else {
            document.getElementById('totalTagihan').value = '';
            document.getElementById('totalTagihanDisplay').value = '';
            document.getElementById('summaryTotal').textContent = 'Rp 0';
            document.getElementById('summaryPaket').textContent = '-';
        }
        checkForm();
    }

    // Event listener untuk form input
    document.getElementById('bookingForm').addEventListener('input', e => {
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
        }
        checkForm();
    });

    function checkForm() {
        const required = ['nama', 'telepon', 'jadwal_id', 'paket', 'metodePembayaran', 'id_studio', 'tanggal'];
        const complete = required.every(field => {
            const el = document.querySelector(`[name="${field}"]`);
            const value = el ? el.value.toString().trim() : '';
            console.log(`Field ${field}:`, value); // Debug
            return value !== '';
        });
        
        document.getElementById('submitBtn').disabled = !complete;
        console.log("Form complete:", complete); // Debug
    }

    // Event listener untuk perubahan studio
    document.getElementById('studio').addEventListener('change', function() {
        console.log("Studio changed:", this.value); // Debug
        
        const tanggal = document.getElementById('tanggalHidden').value;
        console.log("Current tanggal:", tanggal); // Debug
        
        // Reset jadwal selection
        document.getElementById('jadwalId').value = '';
        document.getElementById('jamBooking').value = '';
        document.getElementById('summaryTanggalWaktu').textContent = '-';
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        
        if (tanggal && this.value) {
            loadJadwal(tanggal);
        } else if (!tanggal) {
            const container = document.getElementById('timeSlots');
            container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#9ca3af;">
                <i class="fas fa-calendar" style="font-size:2rem;margin-bottom:0.5rem;"></i>
                <p>Silakan pilih tanggal terlebih dahulu</p>
            </div>`;
        }
        
        checkForm();
    });

    // Initial check
    checkForm();
</script>
</body>

</html>