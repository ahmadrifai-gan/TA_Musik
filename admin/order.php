<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../master/header.php"; 
require "../master/navbar.php"; 
require "../master/sidebar.php"; 
require "../config/koneksi.php"; 

// ---------------------- PROSES HAPUS ----------------------
if (isset($_POST['hapus'])) {
  $id = $_POST['hapus_id'];
  $current_page = isset($_GET['page']) ? '?page=' . (int)$_GET['page'] : '';
  mysqli_query($koneksi, "DELETE FROM booking WHERE id_order = '$id'");
  echo "<script>alert('Data berhasil dihapus!'); window.location='order.php$current_page';</script>";
  exit;
}

// ---------------------- PROSES TAMBAH ----------------------
if (isset($_POST['simpan'])) {
  $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
  $id_studio = $_POST['id_studio'];
  $tanggal = $_POST['tanggal'];
  $jam_booking = $_POST['jam_booking'];
  $total_tagihan = $_POST['total_tagihan'];
  $status = $_POST['status'];
  $status_pembayaran = $_POST['status_pembayaran'];

  // cek user, kalau belum ada tambahkan ke tabel user
  $cekUser = mysqli_query($koneksi, "SELECT id_user FROM user WHERE nama_lengkap='$nama_pelanggan' LIMIT 1");
  if (mysqli_num_rows($cekUser) > 0) {
    $id_user = mysqli_fetch_assoc($cekUser)['id_user'];
  } else {
    mysqli_query($koneksi, "INSERT INTO user (username, nama_lengkap, email, role, password, whatsapp) 
                            VALUES ('$nama_pelanggan', '$nama_pelanggan', '', 'user', '', '')");
    $id_user = mysqli_insert_id($koneksi);
  }

  mysqli_query($koneksi, "
    INSERT INTO booking (id_user, id_studio, total_tagihan, status, status_pembayaran, Tanggal, jam_booking)
    VALUES ('$id_user', '$id_studio', '$total_tagihan', '$status', '$status_pembayaran', '$tanggal', '$jam_booking')
  ");
  $total_after_insert = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM booking"))['total'];
  $last_page = ceil($total_after_insert / 10);
  echo "<script>alert('Order baru berhasil ditambahkan!'); window.location='order.php?page=$last_page';</script>";
  exit;
}

// ---------------------- PROSES UPDATE STATUS ----------------------
if (isset($_POST['update_status'])) {
  $id_order = $_POST['status_id'];
  $status = $_POST['status_value'];
  $status_pembayaran = $_POST['pembayaran_value'];
  
  $updateQuery = "UPDATE booking 
    SET status = '$status', 
        status_pembayaran = '$status_pembayaran'
    WHERE id_order = '$id_order'";
  
  $result = mysqli_query($koneksi, $updateQuery);
  
  if ($result) {
    echo "<script>alert('✅ Status berhasil diupdate!'); window.location='order.php';</script>";
  } else {
    $error = mysqli_error($koneksi);
    echo "<script>alert('❌ Error: $error'); window.location='order.php';</script>";
  }
  exit;
}

?>


<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap');

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
    font-size: 28px;
    font-weight: 700;
    color: #222;
    margin-bottom: 20px;
}

  .btn-add {
    background-color: #28a745;
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
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

  .table td:nth-child(5) {
    color: #2c3e50;
    white-space: nowrap;
  }

  /* Badge Styling */
  .badge {
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: capitalize;
    letter-spacing: 0.3px;
  }

  .badge-warning {
    background-color: #f39c12;
    color: white;
  }

  .badge-success {
    background-color: #27ae60;
    color: white;
  }

  .badge-danger {
    background-color: #e74c3c;
    color: white;
  }

  .badge-info {
    background-color: #3498db;
    color: white;
  }

  .badge-secondary {
    background-color: #95a5a6;
    color: white;
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
  }

  .btn-action i {
    font-size: 14px;
  }

  .btn-lihat {
    background-color: #17a2b8;
    color: white;
  }

  .btn-edit {
    background-color: #f39c12;
    color: white;
  }

  .btn-hapus {
    background-color: #e74c3c;
    color: white;
  }
  .btn-status {
    background-color: #27ae60;
    color: white;
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

  /* Pagination Styling */
  .pagination-wrapper {
    padding: 20px;
    background: white;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
  }

  .pagination-info {
    color: #7f8c8d;
    font-size: 14px;
    font-weight: 500;
  }

  .pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 5px;
  }

  .page-item {
    display: inline-block;
  }

  .page-link {
    display: block;
    padding: 10px 16px;
    color: #4B0082;
    background-color: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .page-link:hover:not(.disabled):not(.active) {
    background-color: #f8f9fa;
    border-color: #4B0082;
    color: #4B0082;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(75, 0, 130, 0.15);
  }

  .page-item.active .page-link {
    background-color: #4B0082;
    border-color: #4B0082;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(75, 0, 130, 0.3);
  }

  .page-item.disabled .page-link {
    color: #95a5a6;
    background-color: #f5f5f5;
    border-color: #e0e0e0;
    cursor: not-allowed;
    opacity: 0.6;
  }

  .page-link i {
    font-size: 12px;
    vertical-align: middle;
  }

  @media (max-width: 768px) {
    .pagination-wrapper {
      flex-direction: column;
      align-items: center;
    }

    .pagination-info {
      text-align: center;
      margin-bottom: 10px;
    }   

    .pagination {
      flex-wrap: wrap;
      justify-content: center;
    }

    .page-link {
      padding: 8px 12px;
      font-size: 13px;
    }
  }

/* ==========================
   Filter input (ID Order)
   ========================== */
   #filter_id {
  max-width: 220px;   /* ubah 180 / 200 / 250 sesuai preferensi */
  width: 100%;
  display: inline-block; /* supaya tidak memaksa full-width container */
}

/* versi mobile: lebih sempit agar tidak terlalu besar */
@media (max-width: 576px) {
  #filter_id {
    max-width: 160px;
  }
}

/* opsional: beri sedikit jarak kanan bila mau tombol di samping */
.filter-wrap {
  display: inline-block;
  vertical-align: middle;
}

.filter-form {
    max-width: 250px;    /* ubah sesuai kebutuhan: 280, 300, 400, dll */
}

</style>

<div class="content-body">
  <div class="container-fluid">

    <div class="page-header">
    <h4>Manajemen Order</h4>
</div>
    <!-- Form Filter dipisah dari tabel -->
    <form method="GET" class="filter-form p-3 mb-3" 
      style="background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
  <div class="filter-wrap">
    <input type="text" inputmode="numeric" pattern="\d*" 
           name="filter_id" id="filter_id" class="form-control"
           placeholder="Cari ID Order..." 
           value="<?= isset($_GET['filter_id']) ? $_GET['filter_id'] : '' ?>">
  </div>
</form>
<script>
let timer;
document.getElementById("filter_id").addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => { this.form.submit(); }, 750);
});
</script>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>ID Order</th>
                <th>Nama Pelanggan</th>
                <th>Studio</th>
                <th>Tanggal</th>
                <th>Jam Booking</th>
                <th>Total Tagihan</th>
                <th>Status</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Pagination setup
              $per_page = 10;
              
              // Get total records first
              $filter = "";
if (!empty($_GET['filter_id'])) {
    $fid = mysqli_real_escape_string($koneksi, $_GET['filter_id']);
    $filter = " WHERE b.id_order = '$fid' ";
}

$count_query = "
  SELECT COUNT(*) as total
  FROM booking b
  JOIN user u ON b.id_user = u.id_user
  JOIN studio s ON b.id_studio = s.id_studio
  $filter
";
              $count_result = mysqli_query($koneksi, $count_query);
              $total_records = mysqli_fetch_assoc($count_result)['total'];
              $total_pages = ceil($total_records / $per_page);
              
              // Validate page number
              $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
              if ($page < 1) $page = 1;
              if ($page > $total_pages && $total_pages > 0) {
                header("Location: order.php?page=" . $total_pages);
                exit;
              }
              
              $offset = ($page - 1) * $per_page;

              // Query dengan pagination
              $query = "
  SELECT 
    b.id_order,
    b.id_studio,
    u.nama_lengkap AS nama_user,
    s.nama AS nama_studio,
    b.Tanggal,
    b.jam_booking,
    b.total_tagihan,
    b.status,
    b.status_pembayaran,
    b.bukti_dp
  FROM booking b
  JOIN user u ON b.id_user = u.id_user
  JOIN studio s ON b.id_studio = s.id_studio
  $filter
  ORDER BY b.id_order DESC
  LIMIT $per_page OFFSET $offset
";
               $result = mysqli_query($koneksi, $query);
              if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                  // Format tanggal
                  $tanggal = date('Y-m-d', strtotime($row['Tanggal']));
              ?>
              <tr>
                <td><span style="font-weight: 700; color: #4B0082; font-size: 1rem;">#<?= htmlspecialchars($row['id_order']) ?></span></td>
                <td><?= htmlspecialchars($row['nama_user']) ?></td>
                <td><?= htmlspecialchars($row['nama_studio']) ?></td>
                <td><?= $tanggal ?></td>
                <td><?= htmlspecialchars($row['jam_booking']) ?></td>
                <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                <td>
                  <?php if ($row['status'] == 'terkonfirmasi'): ?>
                    <span class="badge badge-success">Terkonfirmasi</span>
                  <?php elseif ($row['status'] == 'dibatalkan'): ?>
                    <span class="badge badge-danger">Dibatalkan</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Menunggu</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($row['status_pembayaran'] == 'lunas'): ?>
                    <span class="badge badge-success">Lunas</span>
                  <?php elseif ($row['status_pembayaran'] == 'dp_dibayar'): ?>
                    <span class="badge badge-info">DP Dibayar</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Belum Dibayar</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-lihat btn-action lihatBtn"
                    data-nama="<?= htmlspecialchars($row['nama_user']) ?>"
                    data-studio="<?= htmlspecialchars($row['nama_studio']) ?>"
                    data-tanggal="<?= $tanggal ?>"
                    data-jam="<?= htmlspecialchars($row['jam_booking']) ?>"
                    data-total="<?= htmlspecialchars($row['total_tagihan']) ?>"
                    data-status="<?= htmlspecialchars($row['status']) ?>"
                    data-pembayaran="<?= htmlspecialchars($row['status_pembayaran']) ?>"
                    data-bukti-dp="<?= htmlspecialchars($row['bukti_dp'] ?? '') ?>">
                    <i class="fa fa-eye"></i> Lihat
                  </button>

                  <button type="button" class="btn btn-status btn-action statusBtn"
                    data-id="<?= $row['id_order'] ?>"
                    data-status="<?= htmlspecialchars($row['status']) ?>"
                    data-pembayaran="<?= htmlspecialchars($row['status_pembayaran']) ?>">
                    <i class="fa fa-check-circle"></i> Status
                  </button>

                  <button type="button" class="btn btn-hapus btn-action hapusBtn" data-id="<?= $row['id_order'] ?>">
                    <i class="fa fa-trash"></i> Hapus
                  </button>
                </td>
              </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="9" class="no-data">Belum ada data booking</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="pagination-wrapper">
        <div class="pagination-info">
          Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $per_page, $total_records) ?> dari <?= $total_records ?> data
        </div>
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <!-- Previous Button -->
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?>">
                  <i class="fa fa-chevron-left"></i> Sebelumnya
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link"><i class="fa fa-chevron-left"></i> Sebelumnya</span>
              </li>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=1">1</a>
              </li>
              <?php if ($start_page > 2): ?>
                <li class="page-item disabled">
                  <span class="page-link">...</span>
                </li>
              <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
              <?php if ($end_page < $total_pages - 1): ?>
                <li class="page-item disabled">
                  <span class="page-link">...</span>
                </li>
              <?php endif; ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
              </li>
            <?php endif; ?>

            <!-- Next Button -->
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?>">
                  Selanjutnya <i class="fa fa-chevron-right"></i>
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link">Selanjutnya <i class="fa fa-chevron-right"></i></span>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>


<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Order</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Studio</label>
            <select name="id_studio" class="form-control" required>
              <option value="">-- Pilih Studio --</option>
              <?php
              $studioQ = mysqli_query($koneksi, "SELECT id_studio, nama FROM studio");
              while ($s = mysqli_fetch_assoc($studioQ)) {
                echo "<option value='{$s['id_studio']}'>{$s['nama']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Jam Booking</label>
            <input type="text" name="jam_booking" class="form-control" placeholder="Contoh: 10.00-12.00" required>
          </div>
          <div class="form-group">
            <label>Total Tagihan (Rp)</label>
            <input type="number" name="total_tagihan" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="menunggu">Menunggu</option>
              <option value="terkonfirmasi">Terkonfirmasi</option>
              <option value="dibatalkan">Dibatalkan</option>
            </select>
          </div>
          <div class="form-group">
            <label>Status Pembayaran</label>
            <select name="status_pembayaran" class="form-control">
              <option value="belum_dibayar">Belum Dibayar</option>
              <option value="dp_dibayar">DP Dibayar</option>
              <option value="lunas">Lunas</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalStatus">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-purple text-white">
          <h5 class="modal-title">Update Status Order</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="status_id" id="status_id">
          
          <div class="form-group">
            <label><strong>Status Pemesanan</strong></label>
            <select name="status_value" id="status_value" class="form-control" required>
              <option value="menunggu">Menunggu</option>
              <option value="terkonfirmasi">Terkonfirmasi</option>
              <option value="dibatalkan">Dibatalkan</option>
            </select>
            <small class="form-text text-muted">
              Ubah status pemesanan menjadi Terkonfirmasi atau Dibatalkan
            </small>
          </div>

          <div class="form-group">
            <label><strong>Status Pembayaran</strong></label>
            <select name="pembayaran_value" id="pembayaran_value" class="form-control" required>
              <option value="belum_dibayar">Belum Dibayar</option>
              <option value="dp_dibayar">DP Dibayar</option>
              <option value="lunas">Lunas</option>
            </select>
            <small class="form-text text-muted">
              Update status pembayaran pelanggan
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_status" class="btn btn-primary">
            <i class="fa fa-save"></i> Update Status
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Order</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><strong>Nama Pelanggan:</strong> <span id="detailNama"></span></p>
        <p><strong>Studio:</strong> <span id="detailStudio"></span></p>
        <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
        <p><strong>Jam Booking:</strong> <span id="detailJam"></span></p>
        <p><strong>Total Tagihan:</strong> Rp <span id="detailTotal"></span></p>
        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
        <p><strong>Status Pembayaran:</strong> <span id="detailPembayaran"></span></p>
        <div id="detailBuktiDp" style="margin-top: 15px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p>Apakah kamu yakin ingin menghapus order ini?</p>
        <form method="POST">
          <input type="hidden" name="hapus_id" id="hapus_id">
          <button type="submit" name="hapus" class="btn btn-danger">Ya, Hapus</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPT -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){
  // Lihat
  $(document).on('click', '.lihatBtn', function(){
    $('#detailNama').text($(this).data('nama'));
    $('#detailStudio').text($(this).data('studio'));
    $('#detailTanggal').text($(this).data('tanggal'));
    $('#detailJam').text($(this).data('jam'));
    $('#detailTotal').text(new Intl.NumberFormat('id-ID').format($(this).data('total')));
    
    let pembayaran = $(this).data('pembayaran');
    if(pembayaran === 'lunas') $('#detailPembayaran').html('<span class="badge badge-success">Lunas</span>');
    else if(pembayaran === 'dp_dibayar') $('#detailPembayaran').html('<span class="badge badge-info">DP Dibayar</span>');
    else $('#detailPembayaran').html('<span class="badge badge-secondary">Belum Dibayar</span>');

    let st = $(this).data('status');
    if(st === 'terkonfirmasi') $('#detailStatus').html('<span class="badge badge-success">Terkonfirmasi</span>');
    else if(st === 'dibatalkan') $('#detailStatus').html('<span class="badge badge-danger">Dibatalkan</span>');
    else $('#detailStatus').html('<span class="badge badge-warning">Menunggu</span>');

    // Tampilkan bukti DP jika ada
    let buktiDp = $(this).data('bukti-dp');
    let buktiHtml = '';
    if (buktiDp && buktiDp.trim() !== '') {
      let fileExt = buktiDp.split('.').pop().toLowerCase();
      let filePath = '../uploads/bukti_dp/' + buktiDp;

      buktiHtml = '<hr><p><strong>Bukti DP:</strong></p>';

      if (['jpg', 'jpeg', 'png', 'gif', 'png', 'webp'].includes(fileExt)) {
        buktiHtml += '<div class="mt-2 mb-2">';
        buktiHtml += '<img src="' + filePath + '" alt="Bukti DP" class="img-fluid" style="max-width: 100%; max-height: 350px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
        buktiHtml += '</div>';
      } else {
        buktiHtml += '<div class="alert alert-info py-2 mb-2">File bukti DP terupload (format non-gambar).</div>';
      }

      buktiHtml += '<a href="' + filePath + '" target="_blank" class="btn btn-sm btn-outline-primary">';
      buktiHtml += '<i class="fa fa-external-link"></i> Lihat File Lengkap';
      buktiHtml += '</a>';
    } else {
      buktiHtml = '<hr><p><strong>Bukti DP:</strong> <span class="text-muted">Belum ada bukti DP</span></p>';
    }

    $('#detailBuktiDp').html(buktiHtml);

    $('#modalDetail').modal('show');
  });

  // Status - UPDATE BARU
  $(document).on('click', '.statusBtn', function(){
    var id = $(this).data('id');
    var status = $(this).data('status');
    var pembayaran = $(this).data('pembayaran');
    
    console.log('=== STATUS BUTTON CLICKED ===');
    console.log('ID:', id);
    console.log('Status:', status);
    console.log('Pembayaran:', pembayaran);
    
    $('#status_id').val(id);
    $('#status_value').val(status);
    $('#pembayaran_value').val(pembayaran);
    
    $('#modalStatus').modal('show');
  });

  // Hapus
  $(document).on('click', '.hapusBtn', function(){
    $('#hapus_id').val($(this).data('id'));
    $('#modalHapus').modal('show');
  });

  // Pastikan semua tombol dengan data-dismiss="modal" benar-benar menutup modal
  $(document).on('click', '[data-dismiss="modal"]', function(){
    $(this).closest('.modal').modal('hide');
  });
});
</script>

<?php require "../master/footer.php"; ?>