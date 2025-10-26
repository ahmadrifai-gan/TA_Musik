<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Studio {
    private $koneksi;
    
    public function __construct($db) {
        $this->koneksi = $db;
    }
    
    // Ambil semua data studio
    public function readAll() {
        $query = "SELECT * FROM studio ORDER BY id_studio ASC";
        $result = mysqli_query($this->koneksi, $query);
        
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    
    // Tambah studio
    public function create($nama, $fasilitas, $harga) {
        // Generate ID Studio otomatis
        $query_last = "SELECT id_studio FROM studio ORDER BY id_studio DESC LIMIT 1";
        $result = mysqli_query($this->koneksi, $query_last);
        
        if (mysqli_num_rows($result) > 0) {
            $last = mysqli_fetch_assoc($result);
            $last_number = (int) substr($last['id_studio'], 2);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        $id_studio = 'ST' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO studio (id_studio, nama, fasilitas, harga) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssd", $id_studio, $nama, $fasilitas, $harga);
        
        return mysqli_stmt_execute($stmt);
    }
    
    // Update studio
    public function update($id_studio, $nama, $fasilitas, $harga) {
        $query = "UPDATE studio SET nama = ?, fasilitas = ?, harga = ? WHERE id_studio = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssds", $nama, $fasilitas, $harga, $id_studio);
        
        $result = mysqli_stmt_execute($stmt);
        
        // Debug untuk cek apakah update berhasil
        if (!$result) {
            error_log("Update gagal: " . mysqli_error($this->koneksi));
        }
        
        return $result;
    }
    
    // Hapus studio
    public function delete($id_studio) {
        $query = "DELETE FROM studio WHERE id_studio = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $id_studio);
        
        return mysqli_stmt_execute($stmt);
    }
}

// Proses dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once "../config/koneksi.php";
    $studio = new Studio($koneksi);
    
    // Tambah Studio
    if (isset($_POST['tambah'])) {
        $nama = trim($_POST['nama']);
        $fasilitas = trim($_POST['fasilitas']);
        $harga = floatval($_POST['harga']);
        
        if (!empty($nama) && !empty($fasilitas) && $harga > 0) {
            $result = $studio->create($nama, $fasilitas, $harga);
            if ($result) {
                header("Location: ../admin/studio.php?status=success");
            } else {
                header("Location: ../admin/studio.php?status=error");
            }
        } else {
            header("Location: ../admin/studio.php?status=invalid");
        }
        exit();
    }
    
    // Update Studio
    if (isset($_POST['update'])) {
        $id_studio = trim($_POST['id_studio']);
        $nama = trim($_POST['nama']);
        $fasilitas = trim($_POST['fasilitas']);
        $harga = floatval($_POST['harga']);
        
        // Debug untuk cek data yang diterima
        error_log("Update Studio - ID: $id_studio, Nama: $nama, Fasilitas: $fasilitas, Harga: $harga");
        
        if (!empty($id_studio) && !empty($nama) && !empty($fasilitas) && $harga > 0) {
            $result = $studio->update($id_studio, $nama, $fasilitas, $harga);
            if ($result) {
                header("Location: ../admin/studio.php?status=updated");
            } else {
                header("Location: ../admin/studio.php?status=error");
            }
        } else {
            header("Location: ../admin/studio.php?status=invalid");
        }
        exit();
    }
    
    // Hapus Studio
    if (isset($_POST['hapus'])) {
        $id_studio = trim($_POST['id_studio']);
        
        if (!empty($id_studio)) {
            $result = $studio->delete($id_studio);
            if ($result) {
                header("Location: ../admin/studio.php?status=deleted");
            } else {
                header("Location: ../admin/studio.php?status=error");
            }
        } else {
            header("Location: ../admin/studio.php?status=invalid");
        }
        exit();
    }
}
?>