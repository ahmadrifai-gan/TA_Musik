<?php
class User {
    private $koneksi;
    private $table = "user";

    public function __construct($db) {
        $this->koneksi = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM $this->table ORDER BY id_user ASC";
        $result = mysqli_query($this->koneksi, $query);

        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function delete($id) {
        $query = "DELETE FROM $this->table WHERE id_user = '$id'";
        return mysqli_query($this->koneksi, $query);
    }
}

// Aksi hapus
if (isset($_POST['hapus'])) {
    require "../config/koneksi.php";
    $user = new User($koneksi);
    $id = $_POST['id_user'];

    if ($user->delete($id)) {
        header("Location: ../admin/pelanggan.php?status=deleted");
    } else {
        header("Location: ../admin/pelanggan.php?status=error");
    }
    exit;
}
?>
