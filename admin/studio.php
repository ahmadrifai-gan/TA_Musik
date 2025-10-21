<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";
require "../controller/controller_studio.php";

// Ambil data studio
$studio = new Studio($koneksi);
$data = $studio->readAll();
?>

<div class="content-body">
  <div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1 text-primary">Manajemen Studio</h4>
        <p class="text-muted mb-0">Kelola daftar studio, harga, dan fasilitas dengan mudah.</p>
      </div>
      <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modalTambah">
        <i class="fa fa-plus mr-1"></i> Tambah Studio
      </button>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['status'])): ?>
      <?php
        $status = $_GET['status'];
        $map = [
          'success' => ['class' => 'alert-success', 'msg' => 'Studio berhasil ditambahkan.'],
          'updated' => ['class' => 'alert-info', 'msg' => 'Studio berhasil diperbarui.'],
          'deleted' => ['class' => 'alert-warning', 'msg' => 'Studio berhasil dihapus.'],
          'invalid' => ['class' => 'alert-secondary', 'msg' => 'Data tidak lengkap atau tidak valid.'],
          'error' => ['class' => 'alert-danger', 'msg' => 'Terjadi kesalahan. Coba lagi.'],
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
                <th width="10%">ID</th>
                <th>Nama Studio</th>
                <th>fasilitas</th>
                <th class="text-right">Harga</th>
                <th width="17%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)) : ?>
                <?php foreach ($data as $row) : ?>
                  <tr>
                    <td class="text-center"><?= htmlspecialchars($row['id_studio']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td class="text-right font-weight-bold text-success">
                      Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center">
                      <button class="btn btn-warning btn-sm mr-1"
                              data-toggle="modal"
                              data-target="#modalEdit"
                              data-id="<?= htmlspecialchars($row['id_studio']) ?>"
                              data-nama="<?= htmlspecialchars($row['nama']) ?>"
                              data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                              data-harga="<?= htmlspecialchars($row['harga']) ?>">
                        <i class="fa fa-edit"></i>
                      </button>
                      <form action="../controller/controller_studio.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus studio ini?');">
                        <input type="hidden" name="id_studio" value="<?= htmlspecialchars($row['id_studio']) ?>">
                        <button type="submit" name="hapus" class="btn btn-danger btn-sm">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">Belum ada data studio.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="tambahLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Tambah Studio</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form action="../controller/controller_studio.php" method="POST">
          <div class="form-group">
            <label>Nama Studio</label>
            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama studio" required>
          </div>
          <div class="form-group">
            <label>fasilitas</label>
            <textarea name="fasilitas" class="form-control" rows="3" placeholder="Masukkan fasilitas" required></textarea>
          </div>
          <div class="form-group">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" placeholder="Masukkan harga" required>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
            <button type="submit" name="tambah" class="btn btn-primary btn-sm">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-warning">
        <h5 class="modal-title text-white">Edit Studio</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form action="../controller/controller_studio.php" method="POST">
          <input type="hidden" name="id_studio" id="edit-id">
          <div class="form-group">
            <label>Nama Studio</label>
            <input type="text" name="nama" id="edit-nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label>fasilitas</label>
            <textarea name="fasilitas" id="edit-fasilitas" class="form-control" rows="3" required></textarea>
          </div>
          <div class="form-group">
            <label>Harga</label>
            <input type="number" name="harga" id="edit-harga" class="form-control" required>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
            <button type="submit" name="update" class="btn btn-warning btn-sm">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require "../master/footer.php"; ?>

<!-- <script>
// Isi otomatis modal edit
$('#modalEdit').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  $('#edit-id').val(button.data('id'));
  $('#edit-nama').val(button.data('nama'));
  $('#edit-fasilitas').val(button.data('fasilitas'));
  $('#edit-harga').val(button.data('harga'));
});
</script> -->
