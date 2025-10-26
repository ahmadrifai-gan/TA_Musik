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
    <h2 class="fw-bold mb-4" style="font-size:28px; color:#1a1a1a; font-family:'Poppins', sans-serif;">Data Pelanggan</h2>

    <div class="bg-white rounded shadow-sm p-4">
      <!-- wrapper dua kolom -->
      <div class="data-wrapper" style="display:flex; align-items:flex-start; gap:15px;">
        
        <!-- tabel -->
        <div class="table-responsive" style="flex:1;">
          <table class="table table-bordered" style="width:100%; border-collapse:collapse; font-family:'Poppins', sans-serif; color:#1a1a1a;">
            <thead>
              <tr style="background-color:#f8f9fa;">
                <th style="padding:12px; text-align:center; font-weight:600;">No.</th>
                <th style="padding:12px; text-align:left; font-weight:600;">Username</th>
                <th style="padding:12px; text-align:left; font-weight:600;">Nama Lengkap</th>
                <th style="padding:12px; text-align:left; font-weight:600;">Email</th>
                <th style="padding:12px; text-align:left; font-weight:600;">Password</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)): ?>
                <?php $no = $offset + 1; foreach ($data as $row): ?>
                  <tr style="border-bottom:1px solid #dee2e6; height:60px;">
                    <td style="padding:12px; text-align:center;"><?= $no++ ?></td>
                    <td style="padding:12px;"><?= htmlspecialchars($row['username']) ?></td>
                    <td style="padding:12px;"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td style="padding:12px;"><?= htmlspecialchars($row['email']) ?></td>
                    <td style="padding:12px; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" 
                        title="<?= htmlspecialchars($row['password']) ?>"><?= htmlspecialchars($row['password']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="padding:20px; text-align:center; color:#6c757d;">Belum ada data pengguna.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- tombol aksi di luar tabel tapi sejajar -->
        <div class="action-col" style="display:flex; flex-direction:column; gap:5px; justify-content:start; padding-top:46px;">
          <?php foreach ($data as $row): ?>
            <div style="display:flex; gap:8px; height:60px; align-items:center;">
              
              <a href="edit_pelanggan.php?id=<?= $row['id_user'] ?>"
                 style="background-color:#f4a261; color:#fff; border-radius:6px; padding:8px 16px; font-weight:600; text-decoration:none; transition:0.2s;">
                Edit
              </a>

              <form action="../controller/controller_user.php" method="POST" 
                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" 
                    style="margin:0;">
                <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                <button type="submit" name="hapus"
                        style="background-color:#e76f51; color:#fff; border-radius:6px; padding:8px 16px; font-weight:600; border:none; cursor:pointer; transition:0.2s;">
                  Hapus
                </button>
              </form>

            </div>
          <?php endforeach; ?>
        </div>
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

body, table, th, td, button, a {
  font-family: 'Poppins', sans-serif;
}

.table th, .table td {
  vertical-align: middle;
  border: 1px solid #dee2e6;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

/* Efek hover tombol */
a[href*="edit_pelanggan.php"]:hover {
  background-color: #e38e40;
}
button[name="hapus"]:hover {
  background-color: #d65b43;
}
</style>
