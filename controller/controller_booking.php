<?php
// controller/controller_booking.php
session_start();
require "../config/koneksi.php";

class BookingController {
    private $koneksi;
    
    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
    }
    
    // Public function untuk menambah booking
    public function tambahBooking($data) {
        // Validasi data yang diperlukan
        $required = ['jadwal_id', 'id_studio', 'nama', 'telepon', 'tanggal', 'jam_booking', 'paket', 'totalTagihan', 'metodePembayaran'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field $field harus diisi"];
            }
        }
        
        // Escape data
        $id_jadwal = mysqli_real_escape_string($this->koneksi, $data['jadwal_id']);
        $id_studio = mysqli_real_escape_string($this->koneksi, $data['id_studio']);
        $nama_lengkap = mysqli_real_escape_string($this->koneksi, $data['nama']);
        $email = mysqli_real_escape_string($this->koneksi, $data['email'] ?? '');
        $whatsapp = mysqli_real_escape_string($this->koneksi, $data['telepon']);
        $tanggal = mysqli_real_escape_string($this->koneksi, $data['tanggal']);
        $jam_booking = mysqli_real_escape_string($this->koneksi, $data['jam_booking']);
        $paket = mysqli_real_escape_string($this->koneksi, $data['paket']);
        $total_tagihan = mysqli_real_escape_string($this->koneksi, $data['totalTagihan']);
        $metode_pembayaran = mysqli_real_escape_string($this->koneksi, $data['metodePembayaran']);
        
        // Ambil semua jadwal ID yang dipilih (untuk multi-hour booking)
        $selected_jadwal_ids = [];
        if (!empty($data['selected_jadwal_ids'])) {
            $selected_jadwal_ids = explode(',', $data['selected_jadwal_ids']);
            $selected_jadwal_ids = array_map(function($id) {
                return mysqli_real_escape_string($this->koneksi, trim($id));
            }, $selected_jadwal_ids);
            $selected_jadwal_ids = array_filter($selected_jadwal_ids);
        }
        
        // Jika tidak ada selected_jadwal_ids, gunakan jadwal_id saja
        if (empty($selected_jadwal_ids)) {
            $selected_jadwal_ids = [$id_jadwal];
        }
        
        // Mulai transaksi
        mysqli_begin_transaction($this->koneksi);
        
        try {
            // 1. Insert data booking
            $query_booking = "INSERT INTO booking_offline 
                             (id_jadwal, id_studio, nama_lengkap, email, whatsapp, tanggal, jam_booking, 
                             paket, total_tagihan, metode_pembayaran, status, status_pembayaran) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu', 'dp_dibayar')";
            
            $stmt_booking = mysqli_prepare($this->koneksi, $query_booking);
            mysqli_stmt_bind_param($stmt_booking, "isssssssss", 
                $id_jadwal, $id_studio, $nama_lengkap, $email, $whatsapp, 
                $tanggal, $jam_booking, $paket, $total_tagihan, $metode_pembayaran);
            
            if (!mysqli_stmt_execute($stmt_booking)) {
                throw new Exception("Gagal menyimpan booking: " . mysqli_error($this->koneksi));
            }
            
            // 2. Update status semua jadwal yang dipilih menjadi 'Dibooking'
            foreach ($selected_jadwal_ids as $jadwal_id) {
                $query_update_jadwal = "UPDATE jadwal SET status = 'Dibooking' WHERE id_jadwal = ?";
                $stmt_update = mysqli_prepare($this->koneksi, $query_update_jadwal);
                mysqli_stmt_bind_param($stmt_update, "i", $jadwal_id);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception("Gagal update status jadwal: " . mysqli_error($this->koneksi));
                }
            }
            
            // Commit transaksi
            mysqli_commit($this->koneksi);
            
            $id_booking = mysqli_insert_id($this->koneksi);
            
            return [
                'success' => true,
                'message' => 'Booking berhasil ditambahkan',
                'id_booking' => $id_booking,
                'data' => [
                    'nama' => $nama_lengkap,
                    'whatsapp' => $whatsapp,
                    'tanggal' => $tanggal,
                    'jam' => $jam_booking,
                    'studio' => $id_studio,
                    'paket' => $paket,
                    'total' => $total_tagihan,
                    'metode' => $metode_pembayaran
                ]
            ];
            
        } catch (Exception $e) {
            mysqli_rollback($this->koneksi);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Public function untuk mendapatkan jadwal
    public function getJadwal($tanggal, $studio_id) {
        $tanggal = mysqli_real_escape_string($this->koneksi, $tanggal);
        $studio_id = mysqli_real_escape_string($this->koneksi, $studio_id);
        
        $query = "SELECT j.*, s.nama as nama_studio 
                  FROM jadwal j 
                  JOIN studio s ON j.id_studio = s.id_studio 
                  WHERE j.tanggal = ? AND j.id_studio = ? 
                  ORDER BY j.jam_mulai";
        
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ss", $tanggal, $studio_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $jadwal = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $jadwal[] = [
                'id_jadwal' => $row['id_jadwal'],
                'jam_mulai' => $row['jam_mulai'],
                'jam_selesai' => $row['jam_selesai'],
                'status' => $row['status'],
                'nama_studio' => $row['nama_studio']
            ];
        }
        
        return $jadwal;
    }
    
    // Public function untuk mendapatkan daftar studio
    public function getStudioList() {
        $query = "SELECT id_studio, nama FROM studio ORDER BY nama";
        $result = mysqli_query($this->koneksi, $query);
        
        $studio = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $studio[] = $row;
        }
        
        return $studio;
    }
    
    // Public function untuk mendapatkan paket berdasarkan studio
    public function getPaketByStudio($studio_id) {
        $studio_id = mysqli_real_escape_string($this->koneksi, $studio_id);
        
        // Ambil nama studio untuk menentukan paket
        $query_studio = "SELECT nama FROM studio WHERE id_studio = ?";
        $stmt_studio = mysqli_prepare($this->koneksi, $query_studio);
        mysqli_stmt_bind_param($stmt_studio, "s", $studio_id);
        mysqli_stmt_execute($stmt_studio);
        $result_studio = mysqli_stmt_get_result($stmt_studio);
        $studio_data = mysqli_fetch_assoc($result_studio);
        
        if (!$studio_data) {
            return [];
        }
        
        $nama_studio = strtolower($studio_data['nama']);
        
        // Tentukan paket berdasarkan nama studio
        $paket = [];
        
        if (stripos($nama_studio, 'bronze') !== false) {
            // Studio Bronze: 1 jam = 50, 2 jam = 90, 3 jam = 130
            $paket = [
                ['value' => 'bronze-1jam', 'label' => 'Tanpa Keyboad', 'price' => 35000, 'duration' => 1],
                ['value' => 'bronze-2jam', 'label' => 'Dengan Keyboard', 'price' => 40000, 'duration' => 1],
            ];
        } elseif (stripos($nama_studio, 'gold') !== false) {
            // Studio Gold: dummy data (akan diganti user nanti)
            $paket = [
                ['value' => 'gold-1jam', 'label' => 'Paket 1 Jam', 'price' => 50000, 'duration' => 1],
                ['value' => 'gold-2jam', 'label' => 'Paket 2 Jam', 'price' => 90000, 'duration' => 2],
                ['value' => 'gold-3jam', 'label' => 'Paket 3 Jam', 'price' => 130000, 'duration' => 3]
            ];
        }
        
        return $paket;
    }
    
    // Public function untuk mendapatkan tanggal tersedia
    public function getTanggalTersedia() {
        $query = "SELECT DISTINCT tanggal FROM jadwal WHERE status = 'Belum Dibooking' ORDER BY tanggal";
        $result = mysqli_query($this->koneksi, $query);
        
        $tanggal = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tanggal[] = $row['tanggal'];
        }
        
        return $tanggal;
    }
    
    // Public function untuk mendapatkan daftar booking dengan pagination
    public function getBookings($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        // Query dengan pagination
        $query = "SELECT b.*, s.nama as nama_studio, j.jam_mulai, j.jam_selesai
                  FROM booking_offline b
                  JOIN studio s ON b.id_studio = s.id_studio
                  JOIN jadwal j ON b.id_jadwal = j.id_jadwal
                  ORDER BY b.tanggal DESC, b.jam_booking DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $bookings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
        
        // Hitung total
        $query_count = "SELECT COUNT(*) as total FROM booking_offline";
        $result_count = mysqli_query($this->koneksi, $query_count);
        $total = mysqli_fetch_assoc($result_count)['total'];
        
        return [
            'bookings' => $bookings,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    // Public function untuk update status booking
    public function updateStatusBooking($id_booking, $status, $status_pembayaran) {
        $id_booking = mysqli_real_escape_string($this->koneksi, $id_booking);
        $status = mysqli_real_escape_string($this->koneksi, $status);
        $status_pembayaran = mysqli_real_escape_string($this->koneksi, $status_pembayaran);
        
        $query = "UPDATE booking_offline 
                  SET status = ?, status_pembayaran = ?, updated_at = NOW() 
                  WHERE id_offline = ?";
        
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $status_pembayaran, $id_booking);
        
        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true, 'message' => 'Status booking berhasil diupdate'];
        } else {
            return ['success' => false, 'message' => 'Gagal update status booking: ' . mysqli_error($this->koneksi)];
        }
    }
    
    // Public function untuk menghapus booking
    public function hapusBooking($id_booking) {
        // Mulai transaksi
        mysqli_begin_transaction($this->koneksi);
        
        try {
            // 1. Ambil id_jadwal sebelum menghapus
            $query_get = "SELECT id_jadwal FROM booking_offline WHERE id_offline = ?";
            $stmt_get = mysqli_prepare($this->koneksi, $query_get);
            mysqli_stmt_bind_param($stmt_get, "i", $id_booking);
            mysqli_stmt_execute($stmt_get);
            $result = mysqli_stmt_get_result($stmt_get);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $id_jadwal = $row['id_jadwal'];
                
                // 2. Hapus booking
                $query_delete = "DELETE FROM booking_offline WHERE id_offline = ?";
                $stmt_delete = mysqli_prepare($this->koneksi, $query_delete);
                mysqli_stmt_bind_param($stmt_delete, "i", $id_booking);
                
                if (!mysqli_stmt_execute($stmt_delete)) {
                    throw new Exception("Gagal menghapus booking");
                }
                
                // 3. Update status jadwal
                $query_update = "UPDATE jadwal SET status = 'Belum Dibooking' WHERE id_jadwal = ?";
                $stmt_update = mysqli_prepare($this->koneksi, $query_update);
                mysqli_stmt_bind_param($stmt_update, "i", $id_jadwal);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception("Gagal mengembalikan status jadwal");
                }
            }
            
            mysqli_commit($this->koneksi);
            return ['success' => true, 'message' => 'Booking berhasil dihapus'];
            
        } catch (Exception $e) {
            mysqli_rollback($this->koneksi);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Public function untuk mendapatkan detail booking by ID
    public function getBookingById($id_booking) {
        $query = "SELECT b.*, s.nama as nama_studio, j.jam_mulai, j.jam_selesai
                  FROM booking_offline b
                  JOIN studio s ON b.id_studio = s.id_studio
                  JOIN jadwal j ON b.id_jadwal = j.id_jadwal
                  WHERE b.id_offline = ?";
        
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_booking);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
}

// Inisialisasi controller
$bookingController = new BookingController($koneksi);

// Handle form submission untuk tambah booking
if (isset($_POST['tambah'])) {
    $result = $bookingController->tambahBooking($_POST);
    
    if ($result['success']) {
        $_SESSION['booking_success'] = $result;
        header("Location: ../admin/order_offline.php");
    } else {
        $_SESSION['error'] = $result['message'];
        header("Location: ../admin/tambah_booking.php");
    }
    exit();
}

// Handle AJAX request untuk get jadwal
if (isset($_GET['action']) && $_GET['action'] == 'get_jadwal') {
    $tanggal = $_GET['tanggal'] ?? '';
    $studio_id = $_GET['studio'] ?? '';
    
    $jadwal = $bookingController->getJadwal($tanggal, $studio_id);
    
    header('Content-Type: application/json');
    echo json_encode($jadwal);
    exit();
}

// Handle AJAX request untuk get studio
if (isset($_GET['action']) && $_GET['action'] == 'get_studio') {
    $studio = $bookingController->getStudioList();
    
    header('Content-Type: application/json');
    echo json_encode($studio);
    exit();
}

// Handle AJAX request untuk get tanggal tersedia
if (isset($_GET['action']) && $_GET['action'] == 'get_tanggal_tersedia') {
    $tanggal = $bookingController->getTanggalTersedia();
    
    header('Content-Type: application/json');
    echo json_encode($tanggal);
    exit();
}

// Handle AJAX request untuk get paket berdasarkan studio
if (isset($_GET['action']) && $_GET['action'] == 'get_paket_by_studio') {
    $studio_id = $_GET['studio_id'] ?? '';
    
    if (empty($studio_id)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Studio ID tidak boleh kosong']);
        exit();
    }
    
    $paket = $bookingController->getPaketByStudio($studio_id);
    
    header('Content-Type: application/json');
    echo json_encode($paket);
    exit();
}

// Handle update status booking
if (isset($_POST['update_status'])) {
    $result = $bookingController->updateStatusBooking(
        $_POST['id_booking'],
        $_POST['status'],
        $_POST['status_pembayaran']
    );
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    header("Location: ../admin/order.php");
    exit();
}

// Handle hapus booking
if (isset($_POST['hapus_booking'])) {
    $result = $bookingController->hapusBooking($_POST['id_booking']);
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    header("Location: ../admin/order.php");
    exit();
}

// Default redirect jika tidak ada aksi yang sesuai
header("Location: ../admin/tambah_booking.php");
exit();
?>