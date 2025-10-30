<?php
session_start();
require "config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu!');
        window.location.href='login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil nama lengkap user
$namaLengkap = '';
$resultUser = $koneksi->query("SELECT nama_lengkap FROM user WHERE id_user = '$id_user' LIMIT 1");
if ($resultUser && $rowUser = $resultUser->fetch_assoc()) {
    $namaLengkap = $rowUser['nama_lengkap'];
}
$_SESSION['nama_lengkap'] = $namaLengkap;

// Handle ubah jadwal
if (isset($_POST['ubah_jadwal'])) {
    $id_order = $_POST['id_order'];
    $tanggal_baru = $_POST['tanggal_baru'];
    $jam_baru = $_POST['jam_baru'];

    if (!empty($tanggal_baru) && !empty($jam_baru)) {
        $updateJadwal = $koneksi->prepare("UPDATE booking SET Tanggal = ?, jam_booking = ? WHERE id_order = ? AND id_user = ?");
        $updateJadwal->bind_param("ssii", $tanggal_baru, $jam_baru, $id_order, $id_user);
        $updateJadwal->execute();
        $updateJadwal->close();

        echo "<script>
            alert('Jadwal berhasil diubah!');
            window.location.href='riwayat_reservasi.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Tanggal dan jam tidak boleh kosong!');</script>";
    }
}

// Aksi batal - HANYA update kolom status
if (isset($_GET['batal'])) {
    $id_order = $_GET['batal'];
    $update = $koneksi->prepare("UPDATE booking SET status = 'dibatalkan' WHERE id_order = ? AND id_user = ?");
    $update->bind_param("ii", $id_order, $id_user);
    $update->execute();
    $update->close();
    echo "<script>
        alert('Reservasi berhasil dibatalkan.');
        window.location.href='riwayat_reservasi.php';
    </script>";
    exit;
}

// Filter - hanya status pembayaran
$filterStatusPembayaran = $_GET['status_pembayaran'] ?? '';
$filterTanggalAwal = $_GET['tanggal_awal'] ?? '';
$filterTanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$showEntries = $_GET['entries'] ?? 10;

// Query dasar
$query = "SELECT * FROM booking WHERE id_user = ?";
$params = [$id_user];
$types = "i";

// Filter berdasarkan status pembayaran
if (!empty($filterStatusPembayaran)) {
    $query .= " AND status_pembayaran = ?";
    $params[] = $filterStatusPembayaran;
    $types .= "s";
}

// Filter berdasarkan tanggal booking
if (!empty($filterTanggalAwal) && !empty($filterTanggalAkhir)) {
    $query .= " AND (Tanggal BETWEEN ? AND ?)";
    $params[] = $filterTanggalAwal;
    $params[] = $filterTanggalAkhir;
    $types .= "ss";
} elseif (!empty($filterTanggalAwal)) {
    $query .= " AND Tanggal >= ?";
    $params[] = $filterTanggalAwal;
    $types .= "s";
} elseif (!empty($filterTanggalAkhir)) {
    $query .= " AND Tanggal <= ?";
    $params[] = $filterTanggalAkhir;
    $types .= "s";
}

// Urutkan dan batasi jumlah data
$query .= " ORDER BY Tanggal DESC LIMIT ?";
$params[] = (int)$showEntries;
$types .= "i";

// Eksekusi query
$stmt = $koneksi->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Reservasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            font-style: italic;
        }
        .header-title {
            background-color: #000;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            display: inline-block;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            background-color: white;
        }
        .btn-filter {
            background-color: #FFD700;
            border: none;
            color: #000;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-filter:hover { background-color: #f2c200; }
        .btn-reset {
            background-color: #6c757d;
            color: white;
            border: none;
            font-weight: 600;
        }
        .btn-reset:hover { background-color: #5a6268; }
        .badge-warning { background-color: #f9e79f; color: #856404; }
        .badge-danger { background-color: #f5b7b1; color: #721c24; }
        .badge-success { background-color: #abebc6; color: #155724; }
        .btn-blue { background-color: #0d6efd; color: white; border: none; }
        .btn-blue:hover { background-color: #0b5ed7; }
        .btn-red { background-color: #ff4d4d; color: white; border: none; }
        .btn-red:hover { background-color: #e63946; }
        .footer-note {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 20px;
        }

        /* Tambahan warna background untuk studio */
        .studio-gold {
            background-color: gold !important;
            color: black;
            font-weight: 600;
            border-radius: 5px;
            padding: 5px 10px;
        }
        .studio-bronze {
            background-color: #cd7f32 !important; /* warna bronze */
            color: white;
            font-weight: 600;
            border-radius: 5px;
            padding: 5px 10px;
        }
    </style>
</head>
<body>

<nav class="navbar bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Reys Music Studio</a>
    <a href="index.php" class="text-dark text-decoration-none fw-semibold">Home</a>
  </div>
</nav>

<div class="container my-5">
    <div class="header-title mb-4">Riwayat Reservasi</div>

    <div class="card p-4">
        <form class="mb-4" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select">
                        <option value="">Semua Transaksi</option>
                        <option value="belum_dibayar" <?= $filterStatusPembayaran == 'belum_dibayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                        <option value="dp_dibayar" <?= $filterStatusPembayaran == 'dp_dibayar' ? 'selected' : '' ?>>DP Dibayar</option>
                        <option value="lunas" <?= $filterStatusPembayaran == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($filterTanggalAwal) ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($filterTanggalAkhir) ?>" class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-filter w-50">FILTER</button>
                    <a href="riwayat_reservasi.php" class="btn btn-reset w-50">RESET</a>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label mb-1">Show Entries</label>
                    <select name="entries" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="10" <?= $showEntries == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $showEntries == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $showEntries == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $showEntries == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Id Order</th>
                        <th>Nama</th>
                        <th>Studio</th>
                        <th>Tanggal Booking</th>
                        <th>Jam Booking</th>
                        <th>Total Tagihan</th>
                        <th>Status Konfirmasi</th>
                        <th>Status Pembayaran</th>
                        <th>Bukti DP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id_order'] ?></td>
                                <td><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $namaLengkap) ?></td>
                                <td>
                                    <?php
                                    $idStudio = $row['id_studio'];
                                    $namaStudio = '-';
                                    $queryStudio = $koneksi->query("SELECT nama FROM studio WHERE id_studio = '$idStudio' LIMIT 1");
                                    if ($queryStudio && $rowStudio = $queryStudio->fetch_assoc()) {
                                        $namaStudio = $rowStudio['nama'];
                                    }

                                    // Tambahan logika warna background tanpa ubah struktur
                                    $classStudio = '';
                                    if (stripos($namaStudio, 'gold') !== false) {
                                        $classStudio = 'studio-gold';
                                    } elseif (stripos($namaStudio, 'bronze') !== false) {
                                        $classStudio = 'studio-bronze';
                                    }

                                    echo "<span class='$classStudio'>" . htmlspecialchars($namaStudio) . "</span>";
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['Tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['jam_booking']) ?></td>
                                <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                    if ($row['status'] === 'menunggu') echo "<span class='badge badge-warning p-2'>Menunggu Konfirmasi</span>";
                                    elseif ($row['status'] === 'terkonfirmasi') echo "<span class='badge badge-success p-2'>Terkonfirmasi</span>";
                                    else echo "<span class='badge badge-danger p-2'>Dibatalkan</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row['status_pembayaran'] === 'belum_dibayar') echo "<span class='badge badge-danger p-2'>Belum Dibayar</span>";
                                    elseif ($row['status_pembayaran'] === 'dp_dibayar') echo "<span class='badge badge-success p-2'>DP Dibayar</span>";
                                    elseif ($row['status_pembayaran'] === 'lunas') echo "<span class='badge badge-success p-2'>Lunas</span>";
                                    else echo "<span class='badge badge-danger p-2'>-</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['bukti_dp'])): ?>
                                        <a href="uploads/bukti_dp/<?= urlencode($row['bukti_dp']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted">Belum upload</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] !== 'dibatalkan'): ?>
                                        <button class="btn btn-blue btn-sm mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#ubahModal" 
                                                data-id="<?= $row['id_order'] ?>"
                                                data-tanggal="<?= $row['Tanggal'] ?>"
                                                data-jam="<?= $row['jam_booking'] ?>">
                                            Ubah Jadwal
                                        </button><br>
                                        <a href="?batal=<?= $row['id_order'] ?>" onclick="return confirm('Batalkan pesanan ini?')" class="btn btn-red btn-sm">Batalkan</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-muted">Belum ada reservasi ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="footer-note">© 2025 Reys Music Studio</p>
</div>

<!-- Modal Ubah Jadwal -->
<div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="ubahModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ubahModalLabel">Ubah Jadwal Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_order" id="id_order">
        <div class="mb-3">
          <label class="form-label">Tanggal Baru</label>
          <input type="date" name="tanggal_baru" id="tanggal_baru" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Jam Baru</label>
          <input type="text" name="jam_baru" id="jam_baru" class="form-control" placeholder="Contoh: 14.00-15.00" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="ubah_jadwal" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ubahModal = document.getElementById('ubahModal');
ubahModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    document.getElementById('id_order').value = button.getAttribute('data-id');
    document.getElementById('tanggal_baru').value = button.getAttribute('data-tanggal');
    document.getElementById('jam_baru').value = button.getAttribute('data-jam');
});
</script>
</body>
</html>
