<?php
session_start();
require "config/koneksi.php";

// Cek session booking
if (!isset($_SESSION['booking_data'])) {
  header("Location: index.php");
  exit;
}

$booking = $_SESSION['booking_data'];
$error = '';
$success = '';

// Validasi id_order
if (!isset($booking['id_order']) || empty($booking['id_order'])) {
    die("Error: ID Order tidak valid. Silakan ulangi proses booking.");
}

$id_order = (int)$booking['id_order'];

// === Jika tombol "Lanjutkan" ditekan ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['bukti_dp']) && $_FILES['bukti_dp']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti_dp']['tmp_name'];
        $fileName = $_FILES['bukti_dp']['name'];
        $fileSize = $_FILES['bukti_dp']['size'];
        $fileType = $_FILES['bukti_dp']['type'];

        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $uploadFileDir = 'uploads/bukti_dp/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'pdf');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 5 * 1024 * 1024) {
                $newFileName = 'dp_' . $id_order . '_' . time() . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $status = "dp_dibayar";
                    $stmt = $koneksi->prepare("UPDATE booking SET bukti_dp = ?, status_pembayaran = 'dp_dibayar' WHERE id_order = ?");
                    $stmt->bind_param("si", $newFileName, $id_order);
                    if ($stmt->execute()) {
                        $success = "✅ Bukti DP berhasil diupload! Tunggu konfirmasi admin.";
                        unset($_SESSION['booking_data']);
                    } else {
                        $error = "Terjadi kesalahan saat menyimpan ke database.";
                    }

                    $stmt->close();
                } else {
                    $error = "Gagal memindahkan file ke folder tujuan.";
                }
            } else {
                $error = "Ukuran file terlalu besar! Maksimal 5MB.";
            }
        } else {
            $error = "Format file tidak valid! Hanya diperbolehkan JPG, JPEG, PNG, atau PDF.";
        }
    } else {
        $error = "Harap pilih file untuk diupload.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Upload Bukti DP - Reys Music Studio</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #FFD54F 0%, #FFB300 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
    }

    .card {
      background-color: #fffbea;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      padding: 30px;
      width: 100%;
      max-width: 600px;
    }

    h4 {
      text-align: center;
      font-weight: 700;
      color: #ff9800;
    }

    .btn-upload {
      background: linear-gradient(135deg, #FFD54F, #FFB300);
      border: none;
      color: #4e342e;
      font-weight: 600;
      padding: 10px 0;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    .btn-upload:hover {
      background: linear-gradient(135deg, #FFCA28, #FFA000);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 179, 0, 0.4);
      color: #3e2723;
    }

    .alert {
      border-radius: 12px;
    }

    .form-label {
      font-weight: 600;
      color: #6d4c41;
    }

    .text-muted {
      color: #8d6e63 !important;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #795548;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      text-decoration: underline;
      color: #5d4037;
    }
  </style>
</head>
<body>
  <div class="card">
    <h4 class="mb-4"><i class="bi bi-cloud-upload"></i> Upload Bukti DP</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="bukti_dp" class="form-label">Pilih File Bukti DP</label>
        <input type="file" name="bukti_dp" id="bukti_dp" class="form-control" required>
        <small class="text-muted">Format: JPG, JPEG, PNG, atau PDF (maks. 5MB)</small>
      </div>
      <button type="submit" class="btn btn-upload w-100">Upload Sekarang</button>
    </form>
    <?php endif; ?>

    <a href="ketentuan.php" class="back-link">
      ← Kembali ke Halaman Sebelumnya
    </a>
  </div>
</body>
</html>
