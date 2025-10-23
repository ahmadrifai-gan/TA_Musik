<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../config/koneksi.php";

// ========== VARIABEL FILTER ==========
$studio = isset($_GET['studio']) ? $_GET['studio'] : '';
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// ========== CEK EXPORT ==========
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    ob_end_clean();

    if ($koneksi->connect_error) die("Koneksi gagal: " . $koneksi->connect_error);

    // Query untuk export Excel
    $query = "SELECT t.order_id, t.durasi, t.total_harga, t.tanggal,
                     u.nama_lengkap AS nama_pelanggan, s.nama AS nama_studio
              FROM transaksi t
              JOIN user u ON t.id_user = u.id_user
              JOIN studio s ON t.id_studio = s.id_studio
              WHERE DATE(t.tanggal) BETWEEN ? AND ?";

    $params = [$tgl_awal, $tgl_akhir];
    $types = "ss";

    if (!empty($studio)) {
        $query .= " AND s.nama = ?";
        $types .= "s";
        $params[] = $studio;
    }

    $query .= " ORDER BY t.tanggal DESC";
    $stmt = $koneksi->prepare($query);
    if (!$stmt) die("Error prepare: " . $koneksi->error);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) die("Error execute: " . $stmt->error);
    $result = $stmt->get_result();

    // Header Excel
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Laporan_Transaksi_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo '<!DOCTYPE html>
    <html><head><meta charset="utf-8">
    <style>
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #FFD700; font-weight: bold; padding: 8px; text-align: left; }
    td { padding: 5px; border: 1px solid #ddd; }
    .total-row { background-color: #fff9e6; font-weight: bold; }
    </style></head><body>
    <h2>Laporan Transaksi Reys Music Studio</h2>
    <p><strong>Periode:</strong> ' . date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)) . '</p>
    <p><strong>Studio:</strong> ' . ($studio ? htmlspecialchars($studio) : 'Semua') . '</p>
    <br><table border="1">
    <thead><tr>
    <th>No</th><th>ID Booking</th><th>Nama Pelanggan</th><th>Studio</th><th>Tanggal</th><th>Durasi (jam)</th><th>Total Harga</th>
    </tr></thead><tbody>';

    $no = 1; $totalPendapatan = 0;
    while ($row = $result->fetch_assoc()) {
        $totalPendapatan += $row['total_harga'];
        echo '<tr>
        <td>' . $no++ . '</td>
        <td>' . htmlspecialchars($row['order_id']) . '</td>
        <td>' . htmlspecialchars($row['nama_pelanggan']) . '</td>
        <td>' . htmlspecialchars($row['nama_studio']) . '</td>
        <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
        <td>' . $row['durasi'] . '</td>
        <td>Rp ' . number_format($row['total_harga'], 0, ',', '.') . '</td>
        </tr>';
    }

    echo '<tr class="total-row">
    <td colspan="6" align="right"><strong>TOTAL PENDAPATAN:</strong></td>
    <td><strong>Rp ' . number_format($totalPendapatan, 0, ',', '.') . '</strong></td>
    </tr></tbody></table>
    <br><p><em>Dicetak pada: ' . date('d/m/Y H:i:s') . '</em></p>
    </body></html>';
    $stmt->close();
    $koneksi->close();
    exit;
}

// ========== HALAMAN NORMAL ==========
$title = "Laporan Transaksi | Reys Music Studio";
require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";

if ($koneksi->connect_error) die("Koneksi gagal: " . $koneksi->connect_error);

// --- Pagination ---
$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Query COUNT ---
$count_query = "SELECT COUNT(*) as total FROM transaksi t
                JOIN user u ON t.id_user = u.id_user
                JOIN studio s ON t.id_studio = s.id_studio
                WHERE DATE(t.tanggal) BETWEEN ? AND ?";
$count_params = [$tgl_awal, $tgl_akhir];
$count_types = "ss";
if (!empty($studio)) {
    $count_query .= " AND s.nama = ?";
    $count_types .= "s";
    $count_params[] = $studio;
}
$stmt_count = $koneksi->prepare($count_query);
$stmt_count->bind_param($count_types, ...$count_params);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_rows = $result_count->fetch_assoc()['total'];
$stmt_count->close();
$total_pages = ($total_rows > 0) ? ceil($total_rows / $limit) : 1;

// --- Query data tampilan ---
$query = "SELECT t.order_id, t.durasi, t.total_harga, t.tanggal,
                 u.nama_lengkap AS nama_pelanggan, s.nama AS nama_studio
          FROM transaksi t
          JOIN user u ON t.id_user = u.id_user
          JOIN studio s ON t.id_studio = s.id_studio
          WHERE DATE(t.tanggal) BETWEEN ? AND ?";
$params = [$tgl_awal, $tgl_akhir];
$types = "ss";
if (!empty($studio)) {
    $query .= " AND s.nama = ?";
    $types .= "s";
    $params[] = $studio;
}
$query .= " ORDER BY t.tanggal DESC LIMIT ?, ?";
$types .= "ii";
$params[] = $offset;
$params[] = $limit;

$stmt = $koneksi->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// --- Query TOTAL PENDAPATAN ---
$query_sum = "SELECT COALESCE(SUM(t.total_harga), 0) as total_pendapatan, COUNT(*) as jumlah
              FROM transaksi t
              JOIN user u ON t.id_user = u.id_user
              JOIN studio s ON t.id_studio = s.id_studio
              WHERE DATE(t.tanggal) BETWEEN ? AND ?";
$sum_params = [$tgl_awal, $tgl_akhir];
$sum_types = "ss";
if (!empty($studio)) {
    $query_sum .= " AND s.nama = ?";
    $sum_types .= "s";
    $sum_params[] = $studio;
}
$stmt_sum = $koneksi->prepare($query_sum);
$stmt_sum->bind_param($sum_types, ...$sum_params);
$stmt_sum->execute();
$row_sum = $stmt_sum->get_result()->fetch_assoc();
$totalPendapatan = $row_sum['total_pendapatan'];
$jumlahTransaksi = $row_sum['jumlah'];
$stmt_sum->close();

// Simpan data
$data = [];
while ($r = $result->fetch_assoc()) {
    $data[] = $r;
}
$stmt->close();
?>

<!-- ======== STYLE ======== -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
* { font-family: 'Poppins', sans-serif; }
.content-body { margin-left: 230px; transition: margin-left 0.3s; }
.laporan-info { background-color: #fff9e6; border: 1px solid #f3e5ab; border-radius: 10px; padding: 20px; font-size: 16px; color: #000; }
.laporan-info b { font-weight: 700; }
.judul-laporan { font-weight: 700; font-size: 28px; margin-bottom: 20px; color: #222; }
.table thead th { font-weight: 600; }
label { font-weight: 500; }
.btn { font-weight: 500; }
@media (max-width: 992px) { .content-body { margin-left: 0; } }
</style>

<div class="content-body">
<div class="container-fluid mt-4">

<h3 class="judul-laporan">Laporan Transaksi (Booking & Keuangan)</h3>

<!-- Filter -->
<div class="card mb-3 shadow-sm">
  <div class="card-body">
    <form method="GET" action="" id="filterForm">
      <div class="row align-items-end">
        <div class="col-md-3">
          <label>Studio</label>
          <select name="studio" class="form-control">
            <option value="">-- Semua --</option>
            <?php
            $studio_query = mysqli_query($koneksi, "SELECT * FROM studio ORDER BY nama ASC");
            while ($s = mysqli_fetch_assoc($studio_query)) {
                $selected = ($studio == $s['nama']) ? 'selected' : '';
                echo "<option value='{$s['nama']}' $selected>{$s['nama']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-3">
          <label>Dari</label>
          <input type="date" name="tgl_awal" class="form-control" value="<?=htmlspecialchars($tgl_awal)?>">
        </div>
        <div class="col-md-3">
          <label>Sampai</label>
          <input type="date" name="tgl_akhir" class="form-control" value="<?=htmlspecialchars($tgl_akhir)?>">
        </div>
        <div class="col-md-3">
          <button class="btn text-dark mt-2" type="submit" style="background-color: #FFD700; border: 1px solid #e6c200;">
            <i class="fa fa-filter"></i> Filter
          </button>
          <button type="button" class="btn btn-success mt-2" onclick="exportToExcel()">
            <i class="fa fa-file-excel"></i> Export Excel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Info Laporan -->
<div class="card mb-3">
  <div class="card-body laporan-info">
    <p><b>Total Pendapatan :</b> Rp <?=number_format($totalPendapatan,0,',','.')?></p>
    <p><b>Jumlah Transaksi :</b> <?=$jumlahTransaksi?></p>
    <p><b>Periode :</b> <?=date('d/m/Y',strtotime($tgl_awal))?> - <?=date('d/m/Y',strtotime($tgl_akhir))?></p>
  </div>
</div>

<!-- Tabel Data -->
<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle table-bordered">
        <thead class="table-warning text-dark">
          <tr>
            <th>No.</th>
            <th>ID Booking</th>
            <th>Nama Pelanggan</th>
            <th>Studio</th>
            <th>Tanggal</th>
            <th>Durasi (jam)</th>
            <th>Total Harga</th>
          </tr>
        </thead>
        <tbody>
        <?php if(count($data)>0): 
            $no = $offset + 1; 
            foreach($data as $row): ?>
          <tr>
            <td><?=$no++?></td>
            <td><?=htmlspecialchars($row['order_id'])?></td>
            <td><?=htmlspecialchars($row['nama_pelanggan'])?></td>
            <td><?=htmlspecialchars($row['nama_studio'])?></td>
            <td><?=date('d/m/Y',strtotime($row['tanggal']))?></td>
            <td><?=$row['durasi']?></td>
            <td>Rp <?=number_format($row['total_harga'],0,',','.')?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center text-muted">Tidak ada data transaksi.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

<!-- Pagination -->
<?php if($total_pages > 1): ?>
<nav aria-label="Pagination">
  <ul class="pagination justify-content-center">
    <li class="page-item <?=($page<=1?'disabled':'')?>"><a class="page-link" href="?studio=<?=urlencode($studio)?>&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=($page-1)?>">Previous</a></li>
    <?php 
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for($i=$start; $i<=$end; $i++): ?>
    <li class="page-item <?=($i==$page?'active':'')?>"><a class="page-link" href="?studio=<?=urlencode($studio)?>&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=$i?>"><?=$i?></a></li>
    <?php endfor; ?>
    <li class="page-item <?=($page>=$total_pages?'disabled':'')?>"><a class="page-link" href="?studio=<?=urlencode($studio)?>&tgl_awal=<?=$tgl_awal?>&tgl_akhir=<?=$tgl_akhir?>&page=<?=($page+1)?>">Next</a></li>
  </ul>
</nav>
<?php endif; ?>

  </div>
</div>

</div>
</div>

<script>
function exportToExcel() {
    const form = document.getElementById('filterForm');
    const studio = form.querySelector('[name="studio"]').value;
    const tgl_awal = form.querySelector('[name="tgl_awal"]').value;
    const tgl_akhir = form.querySelector('[name="tgl_akhir"]').value;
    const url = window.location.pathname + '?studio=' + encodeURIComponent(studio) +
                '&tgl_awal=' + tgl_awal + '&tgl_akhir=' + tgl_akhir + '&export=excel';
    window.open(url, '_blank');
}
</script>

<?php 
ob_end_flush();
require "../master/footer.php"; 
?>
