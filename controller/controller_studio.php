<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Studio {
    private $koneksi;
    
    public function __construct($db) {
        $this->koneksi = $db;
    }
<<<<<<< HEAD
    
    // Ambil semua data studio
    public function readAll() {
        $query = "SELECT * FROM studio ORDER BY id_studio ASC";
        $result = mysqli_query($this->koneksi, $query);
        
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
=======

    // CREATE (dengan ID otomatis: ST001, ST002, dst)
    public function create($nama, $fasilitas, $harga) {
        // Ambil ID terakhir
        $result = $this->conn->query("SELECT id_studio FROM studio ORDER BY id_studio DESC LIMIT 1");
        $row = $result->fetch_assoc();

        if ($row) {
            $lastId = $row['id_studio']; // contoh: ST005
            $num = (int) substr($lastId, 2) + 1; // ambil angka setelah "ST"
            $id_studio = "ST" . str_pad($num, 3, "0", STR_PAD_LEFT);
        } else {
            $id_studio = "ST001"; // jika belum ada data
        }

        $sql = "INSERT INTO studio (id_studio, nama, fasilitas, harga) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssd", $id_studio, $nama, $fasilitas, $harga);
        return $stmt->execute();
    }

    // READ (semua data)
    public function readAll() {
        $sql = "SELECT * FROM studio";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // READ (berdasarkan ID)
    public function readById($id_studio) {
        $sql = "SELECT * FROM studio WHERE id_studio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $id_studio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // UPDATE
    public function update($id, $nama, $fasilitas, $harga) {
        $sql = "UPDATE studio SET nama=?, fasilitas=?, harga=? WHERE id_studio=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssds", $nama, $fasilitas, $harga, $id);
        return $stmt->execute();
    }

    // DELETE
    public function delete($id) {
        $sql = "DELETE FROM studio WHERE id_studio=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = isset($_POST['id_studio']) ? trim($_POST['id_studio']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $fasilitas = isset($_POST['fasilitas']) ? trim($_POST['fasilitas']) : '';
    $harga = isset($_POST['harga']) ? (float) $_POST['harga'] : 0;

    if ($id !== '' && $nama !== '' && $fasilitas !== '' && $harga > 0) {
        $studio = new Studio($koneksi);
        $success = $studio->update($id, $nama, $fasilitas, $harga);
        header('Location: ../admin/studio.php?status=' . ($success ? 'updated' : 'error'));
        exit;
    }

    header('Location: ../admin/studio.php?status=invalid');
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $id = isset($_POST['id_studio']) ? trim($_POST['id_studio']) : '';
    if ($id !== '') {
        $studio = new Studio($koneksi);
        $success = $studio->delete($id);
        header('Location: ../admin/studio.php?status=' . ($success ? 'deleted' : 'error'));
        exit;
    }
    header('Location: ../admin/studio.php?status=invalid');
    exit;
}

// Handle create request from admin/studio.php modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $fasilitas = isset($_POST['fasilitas']) ? trim($_POST['fasilitas']) : '';
    $harga = isset($_POST['harga']) ? (float) $_POST['harga'] : 0;

    if ($nama !== '' && $fasilitas !== '' && $harga > 0) {
        $studio = new Studio($koneksi);
        $success = $studio->create($nama, $fasilitas, $harga);
        if ($success) {
            header('Location: ../admin/studio.php?status=success');
            exit;
>>>>>>> 0242034b358f8f117811f6e296fca9329270a6e8
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