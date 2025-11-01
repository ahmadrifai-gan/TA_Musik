<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";
require "../controller/controller_studio.php";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$query_count = "SELECT COUNT(*) as total FROM studio";
$result_count = mysqli_query($koneksi, $query_count);
$total_data = mysqli_fetch_assoc($result_count)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data studio dengan limit dan offset
$query = "SELECT id_studio, nama, fasilitas, harga FROM studio ORDER BY id_studio ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$data = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
}
?>

<style>
  * {
    font-family: 'Poppins', sans-serif;
  }

  .content-body {
    background-color: #f5f7fa;
    min-height: 100vh;
    padding: 20px;
  }

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
  }

  .page-header h4 {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
  }

  .btn-add {
    background-color: #28a745;
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
  }

  .btn-add:hover {
    background-color: #218838;
  }

  .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
  }

  .table-wrapper {
    background: white;
    border-radius: 12px;
    overflow: hidden;
  }

  .table {
    margin-bottom: 0;
  }

  .table thead th {
    background-color: #4B0082;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    padding: 18px 12px;
    border: none;
    text-align: center;
    vertical-align: middle;
  }

  .table tbody tr {
    border-bottom: 1px solid #ecf0f1;
  }

  .table tbody tr:last-child {
    border-bottom: none;
  }

  .table td {
    padding: 16px 12px;
    vertical-align: middle;
    text-align: center;
    color: #2c3e50;
    font-size: 14px;
  }

  .table td:nth-child(1) { 
    font-weight: 600;
    color: #4B0082;
    width: 60px;
  }

  .table td:nth-child(2) { 
    text-align: left;
    font-weight: 500;
    color: #2c3e50;
  }

  .table td:nth-child(3) {
    color: #2c3e50;
  }

  .table td:nth-child(4) {
    color: #2c3e50;
    white-space: nowrap;
  }

  /* Button Action Styling */
  .btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    margin: 0 3px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
  }

  .btn-action i {
    font-size: 14px;
  }

  .btn-edit {
    background-color: #f39c12;
    color: white;
  }

  .btn-edit:hover {
    background-color: #e67e22;
  }

  .btn-hapus {
    background-color: #e74c3c;
    color: white;
  }

  .btn-hapus:hover {
    background-color: #c0392b;
  }

  .table td:last-child {
    text-align: center;
    white-space: nowrap;
  }

  .no-data {
    text-align: center;
    padding: 40px;
    color: #95a5a6;
    font-style: italic;
  }

  /* Pagination Styles */
  .pagination-wrapper {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    background: white;
  }

  .pagination-info {
    color: #999;
    font-size: 14px;
  }

  .pagination-list {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 8px;
    align-items: center;
  }

  .pagination-link {
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    color: #666;
    background-color: #fff;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-block;
  }

  .pagination-link:hover:not(.disabled):not(.active) {
    background-color: #f5f5f5;
    border-color: #d0d0d0;
  }

  .pagination-link.active {
    background-color: #6C1FAF;
    color: #fff;
    border-color: #6C1FAF;
    font-weight: 600;
  }

  .pagination-link.disabled {
    pointer-events: none;
    opacity: 0.5;
    cursor: not-allowed;
  }
</style>

<div class="content-body">
  <div class="container-fluid">

    <div class="page-header">
      <h4>Manajemen Studio</h4>
      <button class="btn btn-add" data-toggle="modal" data-target="#modalTambah">
        <i class="fa fa-plus"></i> Tambah Studio
      </button>
    </div>

    <div class="card">
      <div class="table-wrapper">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Studio</th>
                <th>Fasilitas</th>
                <th>Harga</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)) : ?>
                <?php foreach ($data as $row) : ?>
                  <tr>
                    <td><?= htmlspecialchars($row['id_studio']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['fasilitas']) ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>
                      <button type="button" class="btn btn-edit btn-action"
                              data-toggle="modal"
                              data-target="#modalEdit"
                              data-id="<?= htmlspecialchars($row['id_studio']) ?>"
                              data-nama="<?= htmlspecialchars($row['nama']) ?>"
                              data-fasilitas="<?= htmlspecialchars($row['fasilitas']) ?>"
                              data-harga="<?= htmlspecialchars($row['harga']) ?>">
                        <i class="fa fa-edit"></i> Edit
                      </button>
                      <form action="../controller/controller_studio.php" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus studio ini?');">
                        <input type="hidden" name="id_studio" value="<?= htmlspecialchars($row['id_studio']) ?>">
                        <button type="submit" name="hapus" class="btn btn-hapus btn-action">
                          <i class="fa fa-trash"></i> Hapus
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="5" class="no-data">Belum ada data studio</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_data > 0): ?>
        <div class="pagination-wrapper">
          <div class="pagination-info">
            Menampilkan <?= $offset + 1 ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> data
          </div>
          <ul class="pagination-list">
            <li>
              <a href="?page=<?= max(1, $page - 1) ?>" 
                 class="pagination-link <?= ($page <= 1) ? 'disabled' : '' ?>">
                 &lt; Sebelumnya
              </a>
            </li>
            <?php
            $range = 2;
            $start = max(1, $page - $range);
            $end = min($total_pages, $page + $range);
            for ($i = $start; $i <= $end; $i++): ?>
              <li>
                <a href="?page=<?= $i ?>" 
                   class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
            <li>
              <a href="?page=<?= min($total_pages, $page + 1) ?>" 
                 class="pagination-link <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                 Selanjutnya &gt;
              </a>
            </li>
          </ul>
        </div>
        <?php endif; ?>

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

<script>
// Isi otomatis modal edit
$('#modalEdit').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  $('#edit-id').val(button.data('id'));
  $('#edit-nama').val(button.data('nama'));
  $('#edit-fasilitas').val(button.data('fasilitas'));
  $('#edit-harga').val(button.data('harga'));
});
</script>