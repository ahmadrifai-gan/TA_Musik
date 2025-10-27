<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";
require "../controller/controller_user.php";

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: pelanggan.php");
    exit();
}

$id_user = $_GET['id'];

// Ambil data pelanggan berdasarkan ID langsung dari database
$query = "SELECT id_user, username, email, password, whatsapp FROM user WHERE id_user = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data pelanggan tidak ditemukan!'); window.location.href='pelanggan.php';</script>";
    exit();
}

// Proses update data
if (isset($_POST['update'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $whatsapp = trim($_POST['whatsapp']);
    
    // Query update - jika password diisi maka hash, jika tidak maka gunakan password lama
    if (!empty($password)) {
        // Hash password baru
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE user SET username = ?, email = ?, password = ?, whatsapp = ? WHERE id_user = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ssssi", $username, $email, $hashed_password, $whatsapp, $id_user);
    } else {
        // Tidak update password
        $update_query = "UPDATE user SET username = ?, email = ?, whatsapp = ? WHERE id_user = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "sssi", $username, $email, $whatsapp, $id_user);
    }
    
    $result = mysqli_stmt_execute($update_stmt);
    
    if ($result) {
        echo "<script>alert('Data pelanggan berhasil diupdate!'); window.location.href='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data pelanggan!');</script>";
    }
}
?>

<div class="content-body" style="background-color: #f4f5f7; min-height: 100vh; padding: 30px;">
  <div class="container-fluid">
    <!-- Judul halaman -->
    <h2 class="fw-bold text-dark mb-4" style="font-size: 28px; font-family: 'Poppins', sans-serif;">Edit Data Pelanggan</h2>
    
    <!-- Form Edit -->
    <div class="bg-white rounded shadow-sm p-4" style="max-width: 800px;">
      <form action="" method="POST">
        <div class="mb-3">
          <label for="username" class="form-label fw-semibold" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #495057;">Username</label>
          <input type="text" class="form-control" id="username" name="username" 
                 value="<?= htmlspecialchars($data['username'] ?? '') ?>" 
                 required
                 style="font-family: 'Poppins', sans-serif; font-size: 13px; padding: 10px 15px;">
        </div>
        
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #495057;">Email</label>
          <input type="email" class="form-control" id="email" name="email" 
                 value="<?= htmlspecialchars($data['email'] ?? '') ?>" 
                 required
                 style="font-family: 'Poppins', sans-serif; font-size: 13px; padding: 10px 15px;">
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label fw-semibold" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #495057;">Password Baru</label>
          <input type="password" class="form-control" id="password" name="password" 
                 placeholder="Kosongkan jika tidak ingin mengubah"
                 style="font-family: 'Poppins', sans-serif; font-size: 13px; padding: 10px 15px;">
          <small class="text-muted" style="font-family: 'Poppins', sans-serif; font-size: 12px;">Kosongkan jika tidak ingin mengubah password</small>
        </div>
        
        <div class="mb-4">
          <label for="whatsapp" class="form-label fw-semibold" style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #495057;">No. WhatsApp</label>
          <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                 value="<?= htmlspecialchars($data['whatsapp'] ?? '') ?>" 
                 required
                 style="font-family: 'Poppins', sans-serif; font-size: 13px; padding: 10px 15px;">
        </div>
        
        <div class="d-flex gap-2">
          <button type="submit" name="update" class="btn btn-update" 
                  style="background-color: #00b8ff; color: white; padding: 10px 30px; border-radius: 6px; font-weight: 500; font-family: 'Poppins', sans-serif; font-size: 14px; border: none;">
            Update
          </button>
          <a href="pelanggan.php" class="btn btn-secondary" 
             style="padding: 10px 30px; border-radius: 6px; font-family: 'Poppins', sans-serif; font-size: 14px; text-decoration: none; display: inline-block; line-height: 1.5;">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require "../master/footer.php"; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f5f7;
  }
  
  .form-control:focus {
    border-color: #00b8ff;
    box-shadow: 0 0 0 0.2rem rgba(0, 184, 255, 0.25);
  }
  
  .btn-update:hover {
    background-color: #0099d6 !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 184, 255, 0.3);
    transition: all 0.2s ease;
  }
  
  .btn-secondary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }
</style>