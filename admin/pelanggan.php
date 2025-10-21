<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";
require "../controller/controller_user.php";

// Ambil data pelanggan
$user = new User($koneksi);
$data = $user->readAll();
?>

<div class="content-body">
  <div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1 text-primary">Data Pelanggan</h4>
        <p class="text-muted mb-0">Kelola akun pelanggan yang terdaftar pada sistem booking studio musik.</p>
      </div>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['status'])): ?>
      <?php
        $status = $_GET['status'];
        $map = [
          'success' => ['class' => 'alert-success', 'msg' => 'Data pelanggan berhasil ditambahkan.'],
          'updated' => ['class' => 'alert-info', 'msg' => 'Data pelanggan berhasil diperbarui.'],
          'deleted' => ['class' => 'alert-warning', 'msg' => 'Data pelanggan berhasil dihapus.'],
          'error'   => ['class' => 'alert-danger', 'msg' => 'Terjadi kesalahan, coba lagi.'],
        ];
        $conf = $map[$status] ?? null;
      ?>
      <?php if ($conf): ?>
        <div class="alert <?= $conf['class'] ?> alert-dismissible fade show py-2 px-3 mb-3" role="alert">
          <?= $conf['msg'] ?>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Card Tabel -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered table-striped table-sm align-middle mb-0">
            <thead class="bg-primary text-white text-center">
              <tr>
                <th width="5%">No</th>
                <th>Username</th>
                <th>Email</th>
                <th>Password</th>
                <th>No. WhatsApp</th>
                <th width="15%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)): ?>
                <?php $no = 1; foreach ($data as $row): ?>
                  <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['password']) ?></td>
                    <td><?= htmlspecialchars($row['no_wa']) ?></td>
                    <td class="text-center">
                      <form action="../controller/controller_user.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?');">
                        <input type="hidden" name="id_user" value="<?= htmlspecialchars($row['id_user']) ?>">
                        <button type="submit" name="hapus" class="btn btn-danger btn-sm">
                          <i class="fa fa-trash"></i> Hapus
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-3">Belum ada data pelanggan.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require "../master/footer.php"; ?>
