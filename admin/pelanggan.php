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
      <div class="header-row" style="display:flex; gap:15px; background-color:#4B0082; color:#fff; font-weight:600; padding:12px; border-radius:6px; text-align:center;">
          <div style="width:50px; display:flex; align-items:center; justify-content:center;">No.</div>
          <div style="width:150px; display:flex; align-items:center; justify-content:center;">Username</div>
          <div style="width:200px; display:flex; align-items:center; justify-content:center;">Nama Lengkap</div>
          <div style="width:200px; display:flex; align-items:center; justify-content:center;">Email</div>
          <div style="width:200px; display:flex; align-items:center; justify-content:center;">Password</div>
          <div style="width:150px; display:flex; align-items:center; justify-content:center;">Aksi</div>
      </div>


      <!-- Baris Data -->
      <div class="data-wrapper" style="display:flex; flex-direction:column; gap:10px; margin-top:5px;">
        <?php $no = $offset + 1; ?>
        <?php if (!empty($data)): ?>
          <?php foreach ($data as $row): ?>
            <div class="row-data" style="display:flex; align-items:center; background:#fff; padding:12px; border-radius:6px; box-shadow:0 0 2px rgba(0,0,0,0.1);">

              <div class="data-col" style="flex:1; display:flex; gap:15px; text-align:center;">
                <div style="width:50px;"><?= $no++ ?>.</div>
                <div style="width:150px;"><?= htmlspecialchars($row['username']) ?></div>
                <div style="width:200px;"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                <div style="width:200px;"><?= htmlspecialchars($row['email']) ?></div>
                <div style="width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($row['password']) ?>"><?= htmlspecialchars($row['password']) ?></div>
              </div>

              <!-- Tombol Edit & Hapus -->
              <div class="action-btns" style="width:150px; display:flex; gap:8px; justify-content:center;">
                <a href="edit_pelanggan.php?id=<?= $row['id_user'] ?>" class="btn-edit">Edit</a>
                <form action="../controller/controller_user.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" style="margin:0;">
                  <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                  <button type="submit" name="hapus" class="btn-hapus">Hapus</button>
                </form>
              </div>

            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="padding:20px; text-align:center; color:#6c757d; background:#fff; border-radius:6px;">Belum ada data pengguna.</div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <div style="padding-top:20px; display:flex; justify-content:center;">
        <ul style="display:flex; list-style:none; padding:0; gap:5px; font-family:'Poppins', sans-serif;">
          <li>
            <a href="?page=<?= max(1, $page - 1) ?>" 
               style="color:#1a1a1a; text-decoration:none; border:1px solid #dee2e6; padding:8px 12px; border-radius:4px; <?= ($page <= 1) ? 'pointer-events:none; opacity:0.5;' : '' ?>">&lt;&lt;</a>
          </li>
          <?php
          $range = 2;
          $start = max(1, $page - $range);
          $end = min($total_pages, $page + $range);
          for ($i = $start; $i <= $end; $i++): ?>
            <li>
              <a href="?page=<?= $i ?>" 
                 style="text-decoration:none; padding:8px 12px; border-radius:4px; border:1px solid #dee2e6;
                 <?= ($i == $page) ? 'background-color:#00b8ff; color:#fff; border-color:#00b8ff;' : 'color:#1a1a1a;' ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
          <li>
            <a href="?page=<?= min($total_pages, $page + 1) ?>" 
               style="color:#1a1a1a; text-decoration:none; border:1px solid #dee2e6; padding:8px 12px; border-radius:4px; <?= ($page >= $total_pages) ? 'pointer-events:none; opacity:0.5;' : '' ?>">&gt;&gt;</a>
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
.btn-edit {
  background-color:#f4a261; color:#fff; border-radius:6px; padding:6px 12px; font-weight:600; text-decoration:none; transition:0.2s;
}
.btn-edit:hover { background-color:#e38e40; }

.btn-hapus {
  background-color:#e76f51; color:#fff; border-radius:6px; padding:6px 12px; font-weight:600; border:none; cursor:pointer; transition:0.2s;
}
.btn-hapus:hover { background-color:#d65b43; }
</style>
