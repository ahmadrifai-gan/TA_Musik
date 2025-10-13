<?php
$title = "Laporan Transaksi | Reys Music Studio";
require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
include "../config/koneksi.php"; // koneksi database

// --- Variabel filter ---
$studio = isset($_GET['studio']) ? $_GET['studio'] : '';
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- Query dasar (prepared statement) ---
$query = "SELECT t.*, u.nama_lengkap AS nama_pelanggan, s.nama AS nama_studio
          FROM transaksi t
          JOIN user u ON t.id_user = u.id_user
          JOIN studio s ON t.id_studio = s.id_studio
          WHERE DATE(t.tanggal) BETWEEN ? AND ?";

if (!empty($studio)) {
    $query .= " AND s.nama = ?";
}

$stmt = $koneksi->prepare($query);
if (!empty($studio)) {
    $stmt->bind_param("sss", $tgl_awal, $tgl_akhir, $studio);
} else {
    $stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
}

$stmt->execute();
$result = $stmt->get_result();

// --- Hitung total & jumlah transaksi ---
$totalPendapatan = 0;
$jumlahTransaksi = 0;
$data = [];

if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $totalPendapatan += $r['total_harga'];
        $jumlahTransaksi++;
        $data[] = $r;
    }
}

// --- Export Excel ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Transaksi.xls");

    echo "<table border='1'>";
    echo "<tr><th>No</th><th>ID Booking</th><th>Nama Pelanggan</th><th>Studio</th><th>Tanggal</th><th>Durasi (jam)</th><th>Total Harga</th></tr>";
    $no = 1;
    foreach ($data as $row) {
        echo "<tr>
                <td>{$no}</td>
                <td>{$row['id_transaksi']}</td>
                <td>{$row['nama_pelanggan']}</td>
                <td>{$row['nama_studio']}</td>
                <td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>
                <td>{$row['durasi']}</td>
                <td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>
              </tr>";
        $no++;
    }
    echo "</table>";
    exit;
}
?>

<!-- ======== STYLE TAMBAHAN ======== -->
<style>
    .content-body {
        margin-left: 230px;
        transition: margin-left 0.3s;
    }

    .laporan-info {
        background-color: #fff9e6; /* krem kekuningan */
        border: 1px solid #f3e5ab;
        border-radius: 10px;
        padding: 20px;
        font-size: 16px;
        color: #000; /* teks hitam */
    }

    .laporan-info p {
        margin-bottom: 8px;
        color: #000;
    }

    /* Hanya label yang tebal */
    .laporan-info b {
        font-weight: 700;
        color: #000;
    }

    .judul-laporan {
        font-weight: 700;
        font-size: 24px;
        color: #222;
        margin-bottom: 20px;
    }

    @media (max-width: 992px) {
        .content-body {
            margin-left: 0;
        }
    }
</style>

<div class="content-body">
    <div class="container-fluid mt-4">

        <!-- Judul Halaman -->
        <h3 class="judul-laporan">Laporan Transaksi (Booking & Keuangan)</h3>

        <!-- Filter -->
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Studio</label>
                            <select name="studio" class="form-control">
                                <option value="">-- Semua --</option>
                                <option value="Bronze" <?= ($studio == 'Bronze' ? 'selected' : '') ?>>Bronze</option>
                                <option value="Gold" <?= ($studio == 'Gold' ? 'selected' : '') ?>>Gold</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Dari</label>
                            <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Sampai</label>
                            <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning text-dark mt-2" type="submit">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <a class="btn btn-success mt-2"
                               href="?studio=<?= $studio ?>&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&export=excel">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Laporan -->
        <div class="card mb-3">
            <div class="card-body laporan-info">
                <p><b>Total Pendapatan :</b> Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
                <p><b>Jumlah Transaksi :</b> <?= $jumlahTransaksi ?></p>
                <p><b>Periode :</b> <?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="card shadow-sm">
            <div class="card-body">
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
                        <?php if (count($data) > 0): $no = 1; foreach ($data as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['id_transaksi'] ?></td>
                                <td><?= $row['nama_pelanggan'] ?></td>
                                <td><?= $row['nama_studio'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['durasi'] ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const content = document.querySelector(".content-body");

  if (sidebar && content) {
    const observer = new MutationObserver(() => {
      if (sidebar.classList.contains("collapsed")) {
        content.style.marginLeft = "80px";
      } else {
        content.style.marginLeft = "230px";
      }
    });
    observer.observe(sidebar, { attributes: true });
  }
});
</script>

<?php require "../master/footer.php"; ?>
