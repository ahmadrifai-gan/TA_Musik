<?php
session_start();
$msg = ""; // pesan info

// BYPASS LOGIN - Comment bagian pengecekan session
/*
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
*/

$host="localhost"; $user="root"; $pass=""; $db="db_login";
$conn = new mysqli($host, $user, $pass, $db);

// jika tombol logout ditekan
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- Tambah User ---
if (isset($_POST['add'])) {
    $nama = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $wa = trim($_POST['whatsapp']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, password, email, whatsapp) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $username, $password, $email, $wa);
    if ($stmt->execute()) {
        $msg = "User baru berhasil ditambahkan!";
    } else {
        $msg = "Gagal menambah user.";
    }
}

// --- Update User ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $wa = trim($_POST['whatsapp']);

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, password=?, email=?, whatsapp=? WHERE id=?");
        $stmt->bind_param("sssssi", $nama, $username, $password, $email, $wa, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, email=?, whatsapp=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $username, $email, $wa, $id);
    }
    if ($stmt->execute()) {
        $msg = "Data user berhasil diperbarui!";
    } else {
        $msg = "Gagal memperbarui data user.";
    }
}

// --- Delete User ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

// ambil semua user
$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <span class="navbar-brand">My App</span>
    <div class="d-flex">
      <a href="dashboard.php?logout=1" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <h3>Selamat Datang 🎉</h3>
  <hr>

  <?php if (!empty($msg)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Form Tambah User -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">Tambah User Baru</div>
    <div class="card-body">
      <form method="POST" class="row g-2">
        <div class="col-md-3">
          <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="col-md-2">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="whatsapp" class="form-control" placeholder="WhatsApp" required>
        </div>
        <div class="col-md-2">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col-md-1 d-grid">
          <button type="submit" name="add" class="btn btn-success">Tambah</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel User -->
  <div class="card">
    <div class="card-header bg-primary text-white">Daftar User</div>
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>Email</th>
            <th>WhatsApp</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['whatsapp']) ?></td>
            <td>
              <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Update</button>
              <a href="dashboard.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus user ini?')">Delete</a>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($row['nama_lengkap']) ?>" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" value="<?= htmlspecialchars($row['whatsapp']) ?>" class="form-control" required>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Password Baru (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="update" class="btn btn-warning">Simpan Perubahan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>