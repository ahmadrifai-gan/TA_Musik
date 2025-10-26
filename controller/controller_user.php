<?php
class User {
    private $koneksi;
    
    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
    }
    
    // Method yang sudah ada
    public function readAll() {
        $query = "SELECT * FROM tb_user ORDER BY id_user DESC";
        $result = mysqli_query($this->koneksi, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Method baru untuk ambil data berdasarkan ID
    public function readById($id_user) {
        $query = "SELECT * FROM tb_user WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    // Method baru untuk update data
    public function update($id_user, $username, $email, $password, $no_wa) {
        $query = "UPDATE tb_user SET username = ?, email = ?, password = ?, no_wa = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $password, $no_wa, $id_user);
        return mysqli_stmt_execute($stmt);
    }
    
    // Method untuk hapus (yang sudah ada)
    public function delete($id_user) {
        $query = "DELETE FROM tb_user WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        return mysqli_stmt_execute($stmt);
    }
}

// Handle hapus data
if (isset($_POST['hapus'])) {
    $id_user = $_POST['id_user'];
    $user = new User($koneksi);
    
    if ($user->delete($id_user)) {
        header("Location: ../admin/pelanggan.php?status=success");
    } else {
        header("Location: ../admin/pelanggan.php?status=error");
    }
    exit();
}
?>