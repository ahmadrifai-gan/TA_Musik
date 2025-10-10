<?php
include "../config/koneksi.php"; // pastikan path benar (../config/koneksi.php)

// --- Variabel filter ---
$studio = isset($_GET['studio']) ? $_GET['studio'] : '';
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- Export ke Excel ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Transaksi_" . date('Ymd') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}

// --- Query dasar ---
$query = "SELECT t.*, 
                 u.nama_lengkap AS nama_user, 
                 s.nama AS nama_studio
          FROM transaksi t
          JOIN user u ON t.id_user = u.id_user
          JOIN studio s ON t.id_studio = s.id_studio
          WHERE 1=1";

if (!empty($studio)) {
    $query .= " AND s.nama_studio = '$studio'";
}

$result = $koneksi->query($query);

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
?>

<?php if (!isset($_GET['export'])): ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - Reys Music Studio</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { margin: 0; display: flex; background-color: #f9fafb; }

        .sidebar {
            position: fixed; left: 0; top: 0;
            width: 220px; height: 100vh;
            background-color: #111827; color: white;
            display: flex; flex-direction: column; align-items: center;
            padding-top: 20px;
        }

        .sidebar h2 {
            font-family: 'Lobster', cursive;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 40px;
            text-align: center;
            line-height: 1.4;
            letter-spacing: 1px;
        }

        .sidebar ul { list-style: none; padding: 0; width: 100%; }
        .sidebar ul li { width: 100%; }
        .sidebar ul li a {
            display: block; padding: 12px 20px; color: white;
            text-decoration: none; transition: 0.3s;
        }
        .sidebar ul li a i { margin-right: 10px; }
        .sidebar ul li a:hover,
        .sidebar ul li.active a { background-color: #ffd700; color: black; }

        .main { margin-left: 220px; padding: 40px; width: 100%; }

        /* ✅ Dibuat lebih tebal */
        h1 {
            font-size: 24px;
            font-weight: 700; /* <== tambahkan ini */
            color: #111827;
            margin-bottom: 30px;
        }

        .filter-box {
            background: white; border-radius: 10px; padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .filter-box select, .filter-box input {
            padding: 8px; border: 1px solid #d1d5db;
            border-radius: 6px; margin-right: 10px;
        }
        .btn-filter, .btn-excel {
            background-color: #ffd700; border: none;
            padding: 8px 15px; border-radius: 6px;
            cursor: pointer; font-weight: 600;
        }
        .btn-excel {
            background-color: #22c55e; color: white;
            margin-left: 10px;
        }
        .info-box {
            background: #fefce8; border-radius: 10px;
            padding: 20px; margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%; border-collapse: collapse;
            background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px; text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        th { background-color: #f3f4f6; }
        tr:hover { background-color: #f9fafb; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Reys Music<br>Studio</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="order.php"><i class="fa fa-shopping-cart"></i> Order</a></li>
            <li><a href="studio.php"><i class="fa fa-music"></i> Studio</a></li>
            <li class="active"><a href="report.php"><i class="fa fa-chart-bar"></i> Report</a></li>
            <li><a href="pelanggan.php"><i class="fa fa-users"></i> Pelanggan</a></li>
            <li><a href="jadwal.php"><i class="fa fa-calendar"></i> Jadwal</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>Laporan Transaksi (Booking & Keuangan)</h1>

        <div class="filter-box">
            <form method="GET" action="">
                <label>Studio</label>
                <select name="studio">
                    <option value="">-- Semua --</option>
                    <option value="Bronze" <?= ($studio == 'Bronze' ? 'selected' : '') ?>>Bronze</option>
                    <option value="Gold" <?= ($studio == 'Gold' ? 'selected' : '') ?>>Gold</option>
                </select>

                <label>Dari</label>
                <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">

                <label>Sampai</label>
                <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">

                <button class="btn-filter" type="submit">Filter</button>
                <a class="btn-excel" href="?studio=<?= $studio ?>&tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&export=excel">
                    <i class="fa fa-file-excel"></i> Export Excel
                </a>
            </form>
        </div>

        <div class="info-box">
            <p><b>Total Pendapatan :</b> Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
            <p><b>Jumlah Transaksi :</b> <?= $jumlahTransaksi ?></p>
            <p><b>Periode :</b> <?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
        </div>
<?php endif; ?>

        <table border="1">
            <thead>
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
                <?php if (count($data) > 0): $no=1; foreach($data as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['id_transaksi'] ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= $row['nama_studio'] ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= $row['durasi'] ?></td>
                    <td>Rp <?= number_format($row['total_harga'],0,',','.') ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7">Tidak ada data transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

<?php if (!isset($_GET['export'])): ?>
    </div>
</body>
</html>
<?php endif; ?>
