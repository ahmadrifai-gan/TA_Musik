<?php
// admin/get_jadwal.php (FIXED)
require "../config/koneksi.php";
header('Content-Type: application/json');

// Cek koneksi database
if ($koneksi->connect_errno) {
    echo json_encode(['error' => 'Database connection failed: ' . $koneksi->connect_error]);
    exit;
}

$tanggal = $_GET['tanggal'] ?? '';
$studio = $_GET['studio'] ?? '';

// Validasi input
if (empty($tanggal) || empty($studio)) {
    echo json_encode([]);
    exit;
}

// Debug log (opsional, hapus di production)
error_log("GET JADWAL - Tanggal: $tanggal, Studio: $studio");

try {
    $stmt = $koneksi->prepare("
        SELECT 
            id_jadwal,
            jam_mulai,
            jam_selesai,
            status
        FROM jadwal
        WHERE id_studio = ?
          AND tanggal = ?
        ORDER BY jam_mulai ASC
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $koneksi->error);
    }
    
    $stmt->bind_param("ss", $studio, $tanggal);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id_jadwal' => $row['id_jadwal'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'status' => $row['status']
        ];
    }
    
    // Debug log
    error_log("Found " . count($data) . " jadwal records");
    
    echo json_encode($data);
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in get_jadwal.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

$koneksi->close();
?>