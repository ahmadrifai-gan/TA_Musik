<?php
require_once "../config/koneksi.php";
class User
{
    private $koneksi;

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    // Method yang sudah ada
    public function readAll()
    {
        $query = "SELECT * FROM user ORDER BY id_user DESC";
        $result = mysqli_query($this->koneksi, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // Method baru untuk ambil data berdasarkan ID
    public function readById($id_user)
    {
        $query = "SELECT * FROM user WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // Method baru untuk update data
    public function update($id_user, $username, $email, $password, $no_wa)
    {
        $query = "UPDATE user SET username = ?, email = ?, password = ?, no_wa = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($this->koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $password, $no_wa, $id_user);
        return mysqli_stmt_execute($stmt);
    }

    // Method untuk hapus (yang sudah ada)
    public function delete($id_user)
    {
        // Cek apakah user punya data booking
        $queryCheck = "SELECT COUNT(*) FROM booking WHERE id_user = ?";
        $stmtCheck = mysqli_prepare($this->koneksi, $queryCheck);
        mysqli_stmt_bind_param($stmtCheck, "i", $id_user);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_bind_result($stmtCheck, $count);
        mysqli_stmt_fetch($stmtCheck);
        mysqli_stmt_close($stmtCheck);

        if ($count > 0) {
            // kirim pesan kembali ke pemanggil
            return "User tidak bisa dihapus karena masih punya data booking.";
        } else {
            // hapus user
            $query = "DELETE FROM user WHERE id_user = ?";
            $stmt = mysqli_prepare($this->koneksi, $query);
            mysqli_stmt_bind_param($stmt, "i", $id_user);
            if (mysqli_stmt_execute($stmt)) {
                return "User berhasil dihapus.";
            } else {
                return "Gagal menghapus user.";
            }
        }
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