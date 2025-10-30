<?php
require_once __DIR__ . "/../config/koneksi.php";

class Studio {
    private $conn;

    // Constructor: terhubung ke database
    public function __construct($koneksi) {
        $this->conn = $koneksi;
    }

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
        }
        header('Location: ../admin/studio.php?status=error');
        exit;
    }

    header('Location: ../admin/studio.php?status=invalid');
    exit;
ovii
}
=======
}

