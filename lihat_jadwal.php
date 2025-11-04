<?php
session_start();
require "config/koneksi.php";

// Ambil parameter studio dari URL
$studio_param = $_GET['studio'] ?? '';

if (empty($studio_param)) {
    echo "<script>alert('Parameter studio tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// Tentukan id_studio berdasarkan parameter
if ($studio_param === 'gold') {
    $id_studio = 'ST002';
    $nama_studio = 'Studio Gold';
    $harga_info = 'Reguler: 50K/jam | Paket 2 jam: 90K | Paket 3 jam: 130K';
} elseif ($studio_param === 'bronze') {
    $id_studio = 'ST001';
    $nama_studio = 'Studio Bronze';
    $harga_info = 'Reguler: 35K/jam (ALL INCLUDE NO KEYBOARD) | + KEYBOARD: 5K/jam';
} else {
    echo "<script>alert('Studio tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// Ambil booking yang sudah ada untuk studio ini (status tidak dibatalkan)
$bookings = [];
$queryBooking = $koneksi->prepare("SELECT Tanggal, jam_booking FROM booking WHERE id_studio = ? AND status != 'dibatalkan'");
$queryBooking->bind_param("s", $id_studio);
$queryBooking->execute();
$resultBooking = $queryBooking->get_result();

if ($resultBooking) {
    while ($row = $resultBooking->fetch_assoc()) {
        $bookings[] = [
            'tanggal' => $row['Tanggal'],
            'jam' => $row['jam_booking']
        ];
    }
}
$queryBooking->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal <?= htmlspecialchars($nama_studio) ?> - Reys Music Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .studio-header {
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .studio-header.gold {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        }
        .studio-header.bronze {
            background: linear-gradient(135deg, #cd7f32 0%, #b87333 100%);
        }
        .container-custom {
            max-width: 900px;
            margin: 0 auto;
        }
        .foto-studio {
            background: #ddd;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: #666;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .price-info {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-lihat-jadwal {
            background: #ffd700;
            color: #000;
            font-weight: 600;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn-lihat-jadwal:hover {
            background: #ffed4e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        .calendar-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .calendar-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            text-align: center;
        }
        .calendar-day-header {
            font-weight: 600;
            padding: 10px 5px;
            font-size: 0.85rem;
            color: #666;
        }
        .calendar-day {
            padding: 12px 5px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-size: 0.9rem;
        }
        .calendar-day:hover:not(.other-month) {
            background: #e9ecef;
            transform: scale(1.05);
        }
        .calendar-day.today {
            background: #ffd700;
            color: #000;
            font-weight: bold;
        }
        .calendar-day.selected {
            background: #0066ff;
            color: white;
            font-weight: bold;
        }
        .calendar-day.other-month {
            color: #ccc;
            background: transparent;
            cursor: default;
        }
        .time-slots-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }
        .time-slots {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        .time-slot {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .time-slot:hover:not(.booked) {
            border-color: #0066ff;
            background: #f0f7ff;
        }
        .time-slot.booked {
            background: #ffe0e0;
            border-color: #ff4d4d;
            color: #c00;
            cursor: not-allowed;
        }
        .time-slot.available {
            background: #e8f5e9;
            border-color: #4caf50;
        }
        .legend {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        .legend-box {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid;
        }
        .legend-box.available {
            background: #e8f5e9;
            border-color: #4caf50;
        }
        .legend-box.booked {
            background: #ffe0e0;
            border-color: #ff4d4d;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container-fluid px-3 px-md-5">
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="index.php">
            <span class="badge bg-dark rounded-3">♬</span>
            <span class="fw-bold">Reys Music Studio</span>
        </a>
        <a href="index.php#studios" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</nav>

<!-- Header -->
<div class="studio-header <?= $studio_param ?>">
    <div class="container-custom px-3">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="foto-studio">
                    FOTO
                </div>
            </div>
            <div class="col-md-6">
                <div class="price-info">
                    <h4 class="mb-2"><?= htmlspecialchars($nama_studio) ?></h4>
                    <p class="mb-2" style="font-size: 0.9rem;"><?= htmlspecialchars($harga_info) ?></p>
                    <button class="btn btn-lihat-jadwal" onclick="document.getElementById('jadwalSection').scrollIntoView({behavior: 'smooth'})">
                        LIHAT JADWAL <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kalender & Jadwal -->
<div class="container-custom px-3 mb-5" id="jadwalSection">
    <div class="row g-4">
        <!-- Calendar -->
        <div class="col-md-6">
            <div class="calendar-container">
                <div class="calendar-header">
                    <div class="calendar-nav">
                        <button class="btn btn-sm btn-outline-secondary" id="prevMonth">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="nextMonth">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <h5 id="currentMonth">November 2025</h5>
                </div>
                <div class="calendar-grid" id="calendarGrid"></div>
            </div>
        </div>

        <!-- Time Slots -->
        <div class="col-md-6">
            <div class="time-slots-container">
                <p class="text-muted small mb-0" id="selectedDateDisplay">Pilih tanggal terlebih dahulu</p>
                <div class="time-slots" id="timeSlots"></div>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-box booked"></div>
                        <span>Sudah dibooking</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box available"></div>
                        <span>Belum dibooking</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0 small">© 2025 Reys Music Studio. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Data booking dari PHP (data yang sudah dibooking)
const bookingsData = <?= json_encode($bookings) ?>;
const studioParam = '<?= $studio_param ?>';

console.log('Bookings Data:', bookingsData); // Debug

// Time slots available
const timeSlots = [
    '10.00-11.00', '11.00-12.00', '12.00-13.00', '13.00-14.00',
    '14.00-15.00', '15.00-16.00', '16.00-17.00', '17.00-18.00',
    '18.00-19.00', '19.00-20.00', '20.00-21.00', '21.00-22.00'
];

let currentDate = new Date();
let selectedDate = null;

// Format date to YYYY-MM-DD
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Check if time slot is booked
function isBooked(date, time) {
    const dateStr = formatDate(date);
    const isBookedSlot = bookingsData.some(booking => {
        const match = booking.tanggal === dateStr && booking.jam === time;
        if (match) {
            console.log('Found booked slot:', dateStr, time);
        }
        return match;
    });
    return isBookedSlot;
}

// Render calendar
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update header
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    // Clear existing calendar
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';
    
    // Add headers
    ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].forEach(day => {
        const header = document.createElement('div');
        header.className = 'calendar-day-header';
        header.textContent = day;
        grid.appendChild(header);
    });
    
    // Add previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day other-month';
        dayDiv.textContent = day;
        grid.appendChild(dayDiv);
    }
    
    // Add current month days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day';
        dayDiv.textContent = day;
        
        const dateObj = new Date(year, month, day);
        
        // Check if today
        if (dateObj.toDateString() === today.toDateString()) {
            dayDiv.classList.add('today');
        }
        
        // Check if selected
        if (selectedDate && dateObj.toDateString() === selectedDate.toDateString()) {
            dayDiv.classList.add('selected');
        }
        
        // Add click event
        dayDiv.addEventListener('click', () => selectDate(dateObj));
        
        grid.appendChild(dayDiv);
    }
    
    // Add next month days to fill grid
    const totalCells = grid.children.length - 7;
    const remainingCells = 42 - totalCells - 7;
    for (let day = 1; day <= remainingCells; day++) {
        const dayDiv = document.createElement('div');
        dayDiv.className = 'calendar-day other-month';
        dayDiv.textContent = day;
        grid.appendChild(dayDiv);
    }
}

// Select date
function selectDate(date) {
    selectedDate = date;
    renderCalendar();
    renderTimeSlots();
    
    // Update display
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('selectedDateDisplay').textContent = 
        'Jadwal untuk: ' + date.toLocaleDateString('id-ID', options);
}

// Render time slots
function renderTimeSlots() {
    const container = document.getElementById('timeSlots');
    container.innerHTML = '';
    
    if (!selectedDate) {
        const msg = document.createElement('p');
        msg.className = 'text-muted text-center mt-3';
        msg.textContent = 'Pilih tanggal di kalender untuk melihat jadwal';
        container.appendChild(msg);
        return;
    }
    
    timeSlots.forEach(time => {
        const slot = document.createElement('div');
        const booked = isBooked(selectedDate, time);
        
        slot.className = `time-slot ${booked ? 'booked' : 'available'}`;
        slot.textContent = time;
        
        if (booked) {
            slot.innerHTML = time + '<br><small>Sudah dibooking</small>';
        }
        
        if (!booked) {
            slot.addEventListener('click', () => {
                if (confirm(`Ingin booking jam ${time}?\n\nKlik OK untuk melanjutkan ke halaman booking.`)) {
                    window.location.href = 'booking.php?studio=' + studioParam;
                }
            });
        }
        
        container.appendChild(slot);
    });
}

// Navigation
document.getElementById('prevMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    if (selectedDate) renderTimeSlots();
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    if (selectedDate) renderTimeSlots();
});

// Initial render
renderCalendar();
renderTimeSlots();
</script>

</body>
</html>