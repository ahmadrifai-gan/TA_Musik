<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
require "../config/koneksi.php";
require "../controller/controller_user.php";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$query_count = "SELECT COUNT(*) as total FROM user";
$result_count = mysqli_query($koneksi, $query_count);
$total_data = mysqli_fetch_assoc($result_count)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data user
$query = "SELECT id_user, username, nama_lengkap, email, password FROM user ORDER BY id_user ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$data = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }
}
?>

<div class="content-body" style="background-color:#f4f5f7; min-height:100vh; padding:30px;">
  <div class="container-fluid">
    <h2 class="fw-bold mb-4" style="font-size:28px; color:#1a1a1a;">Data Pelanggan</h2>

    <div class="bg-white rounded shadow-sm p-4">
      <!-- Header -->
      <div class="header-row" style="display:flex; gap:10px; background-color:#4B0082; color:#fff; font-weight:600; padding:12px; border-radius:6px; text-align:center;">
          <div style="width:50px; display:flex; align-items:center; justify-content:center;">No.</div>
          <div style="flex:1; display:flex; align-items:center; justify-content:center;">Username</div>
          <div style="flex:1.5; display:flex; align-items:center; justify-content:center;">Nama Lengkap</div>
          <div style="flex:2; display:flex; align-items:center; justify-content:center;">Email</div>
          <div style="flex:2; display:flex; align-items:center; justify-content:center;">Password</div>
          <!-- <div style="width:100px; display:flex; align-items:center; justify-content:center;">Aksi</div> -->
      </div>


      <!-- Baris Data -->
      <div class="data-wrapper" style="display:flex; flex-direction:column; gap:10px; margin-top:5px;">
        <?php $no = $offset + 1; ?>
        <?php if (!empty($data)): ?>
          <?php foreach ($data as $row): ?>
            <div class="row-data" style="display:flex; gap:10px; align-items:center; background:#fff; padding:12px; border-radius:6px; box-shadow:0 0 2px rgba(0,0,0,0.1);">

              <div style="width:50px; text-align:center;"><?= $no++ ?>.</div>
              <div style="flex:1; text-align:center;"><?= htmlspecialchars($row['username']) ?></div>
              <div style="flex:1.5; text-align:center;"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
              <div style="flex:2; text-align:center; word-break:break-word;"><?= htmlspecialchars($row['email']) ?></div>
              <div style="flex:2; text-align:center; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($row['password']) ?>"><?= htmlspecialchars($row['password']) ?></div>

              <!-- Tombol Hapus -->
              <!-- <div style="width:100px; display:flex; justify-content:center;">
                <form action="../controller/controller_user.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" style="margin:0;">
                  <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                  <button type="submit" name="hapus" class="btn-hapus">Hapus</button>
                </form>
              </div> -->

            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="padding:20px; text-align:center; color:#6c757d; background:#fff; border-radius:6px;">Belum ada data pengguna.</div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
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

    </div>
  </div>
</div>

<?php require "../master/footer.php"; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

body, button, a {
  font-family: 'Poppins', sans-serif;
}

/* Tombol */
.btn-hapus {
  background-color:#e76f51; color:#fff; border-radius:6px; padding:6px 12px; font-weight:600; border:none; cursor:pointer; transition:0.2s;
}
.btn-hapus:hover { background-color:#d65b43; }

/* Pagination Styles */
.pagination-wrapper {
  padding-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
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