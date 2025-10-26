<?php
$title = "Daftar Jadwal | Reys Music Studio";
include "../config/koneksi.php";

// === TAMBAH JADWAL ===
if (isset($_POST['tambah'])) {
    $id_studio = $_POST['id_studio'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status = $_POST['status'];

    $query = mysqli_query($koneksi, "
        INSERT INTO jadwal (id_studio, tanggal, jam_mulai, jam_selesai, status)
        VALUES ('$id_studio', '$tanggal', '$jam_mulai', '$jam_selesai', '$status')
    ");

    header("Location: jadwal.php?status=" . ($query ? 'sukses' : 'gagal') . "&aksi=tambah");
    exit;
}

// === EDIT JADWAL ===
if (isset($_POST['edit'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $id_studio = $_POST['id_studio'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status = $_POST['status'];

    if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        header("Location: jadwal.php?status=gagal&aksi=validasi");
        exit;
    }

    $query = mysqli_query($koneksi, "
        UPDATE jadwal SET 
            id_studio='$id_studio',
            tanggal='$tanggal',
            jam_mulai='$jam_mulai',
            jam_selesai='$jam_selesai',
            status='$status'
        WHERE id_jadwal='$id_jadwal'
    ");

    header("Location: jadwal.php?status=" . ($query ? 'sukses' : 'gagal') . "&aksi=edit");
    exit;
}

// === HAPUS JADWAL ===
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = mysqli_query($koneksi, "DELETE FROM jadwal WHERE id_jadwal='$id'");
    header("Location: jadwal.php?status=" . ($query ? 'sukses' : 'gagal') . "&aksi=hapus");
    exit;
}

// === FILTER DATA ===
$where = [];
if (!empty($_GET['studio'])) {
    $studio = mysqli_real_escape_string($koneksi, $_GET['studio']);
    $where[] = "j.id_studio = '$studio'";
}
if (!empty($_GET['tanggal'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
    $where[] = "j.tanggal = '$tanggal'";
}
$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT j.*, s.nama AS nama_studio
    FROM jadwal j
    JOIN studio s ON j.id_studio = s.id_studio
    $where_sql
    ORDER BY j.tanggal DESC
";
$result = mysqli_query($koneksi, $query);

require "../master/header.php";
require "../master/navbar.php";
require "../master/sidebar.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
.judul-laporan { 
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 28px; 
    margin-bottom: 20px; 
    color: #222; 
}

/* 🔹 Tambahan warna ungu gelap untuk header tabel */
.table-purple {
  background-color: #4B0082 !important; /* Warna ungu gelap */
  color: #ffffff !important;            /* Teks putih */
}
/* Tombol Filter tetap kuning, Tambah Jadwal hijau */
.btn-filter {
  background-color: #FFD700;
  color: black;
  border: 1px solid #e6c200;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-filter:hover {
  background-color: #e6c200 !important;
  color: white !important;
}

.btn-tambah {
  background-color: #28a745; /* hijau */
  color: black;
  border: 1px solid #218838;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-tambah:hover {
  background-color: #218838 !important; /* hijau lebih gelap */
  color: white !important;
}
/* Badge status jadwal teks hitam */
.badge.bg-danger,
.badge.bg-success {
    color: black !important;  /* teks jadi hitam */
}
/* Tombol Edit biru cerah */
.btn-edit {
    background-color: #17a2b8; /* biru cerah */
    color: white;
    border: 1px solid #138496;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-edit:hover {
    background-color: #138496;
    color: white;
}

/* Tombol Hapus merah gelap */
.btn-delete {
    background-color: #b71c1c; /* merah gelap */
    color: white;
    border: 1px solid #7f0000;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-delete:hover {
    background-color: #7f0000;
    color: white;
}
</style>


<div class="content-body">
  <div class="container-fluid">

    <div class="row page-titles mx-0">
      <div class="col-sm-6 p-md-0">
        <h3 class="judul-laporan">Daftar Jadwal Studio</h3>
      </div>
    </div>

    <!-- Filter Jadwal -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="get" action="">
          <div class="row align-items-end g-3">

            <!-- Kolom Studio -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label fw-semibold mb-1">Studio</label>
              <select name="studio" class="form-select">
                <option value="">Semua</option>
                <?php
                $studio_query = mysqli_query($koneksi, "SELECT * FROM studio");
                while ($s = mysqli_fetch_assoc($studio_query)) {
                    $selected = (isset($_GET['studio']) && $_GET['studio'] == $s['id_studio']) ? 'selected' : '';
                    echo "<option value='{$s['id_studio']}' $selected>{$s['nama']}</option>";
                }
                ?>
              </select>
            </div>

            <!-- Kolom Tanggal -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label fw-semibold mb-1">Tanggal</label>
              <input type="date" name="tanggal" class="form-control"
                     value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : '' ?>">
            </div>

            <!-- Tombol Filter -->
<div class="col-md-2 col-sm-6 d-flex align-items-end">
<button type="submit" class="btn btn-filter fw-semibold w-100">
  <i class="fa fa-filter"></i> Filter
</button>
</div>

<!-- Tombol Tambah Jadwal (sama gaya dengan Filter) -->
<div class="col-md-2 col-sm-6 d-flex align-items-end">
  <button type="button" class="btn btn-tambah fw-semibold w-100"
          data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="fa fa-plus"></i> Tambah Jadwal
  </button>
</div>

          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Jadwal -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered text-center align-middle">
          <thead class="table-purple">
              <tr>
                <th>No</th>
                <th>Studio</th>
                <th>Tanggal</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $no = 1;
              if (mysqli_num_rows($result) > 0) :
                while ($row = mysqli_fetch_assoc($result)) : ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_studio']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['jam_mulai']) ?></td>
                    <td><?= htmlspecialchars($row['jam_selesai']) ?></td>
                    <td>
                    <?php if ($row['status'] == 'Dibooking') : ?>
  <span class="badge bg-danger">Dibooking</span>
<?php else : ?>
  <span class="badge bg-success">Belum Dibooking</span>
<?php endif; ?>
                    </td>
                    <td>
  <button class="btn btn-edit btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_jadwal'] ?>">Edit</button>
  <a href="jadwal.php?hapus=<?= $row['id_jadwal'] ?>" onclick="return confirm('Yakin ingin menghapus jadwal ini?')" class="btn btn-delete btn-sm">Hapus</a>
</td>
                  </tr>
                  <!-- Modal Edit untuk row -->
<div class="modal fade" id="modalEdit<?= $row['id_jadwal'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" onsubmit="return validasiJam(this)">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Edit Jadwal #<?= htmlspecialchars($row['id_jadwal']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_jadwal" value="<?= $row['id_jadwal'] ?>">

          <div class="form-group mb-3">
            <label>Studio</label>
            <select name="id_studio" class="form-control" required>
              <?php
              $studio_opt = mysqli_query($koneksi, "SELECT * FROM studio ORDER BY nama ASC");
              while ($sopt = mysqli_fetch_assoc($studio_opt)) {
                  $sel = ($sopt['id_studio'] == $row['id_studio']) ? 'selected' : '';
                  echo "<option value='{$sopt['id_studio']}' $sel>" . htmlspecialchars($sopt['nama']) . "</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($row['tanggal']) ?>" required>
          </div>

          <div class="form-group mb-3">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" class="form-control" value="<?= htmlspecialchars($row['jam_mulai']) ?>" required>
          </div>

          <div class="form-group mb-3">
            <label>Jam Selesai</label>
            <input type="time" name="jam_selesai" class="form-control" value="<?= htmlspecialchars($row['jam_selesai']) ?>" required>
          </div>

          <div class="form-group mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
  <option value="Belum Dibooking" <?= $row['status'] == 'Belum Dibooking' ? 'selected' : '' ?>>Belum Dibooking</option>
  <option value="Dibooking" <?= $row['status'] == 'Dibooking' ? 'selected' : '' ?>>Dibooking</option>
</select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="edit" value="1" class="btn btn-primary">Simpan Perubahan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
                <?php endwhile;
              else : ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">Tidak ada data jadwal ditemukan</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" onsubmit="return validasiJam(this)">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Tambah Jadwal Baru</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="form-group mb-3">
                <label>Studio</label>
                <select name="id_studio" class="form-control" required>
                  <option value="">-- Pilih Studio --</option>
                  <?php
                  $studio_query = mysqli_query($koneksi, "SELECT * FROM studio");
                  while ($s = mysqli_fetch_assoc($studio_query)) {
                      echo "<option value='{$s['id_studio']}'>{$s['nama']}</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group mb-3">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
              </div>

              <div class="form-group mb-3">
                <label>Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" required>
              </div>

              <div class="form-group mb-3">
                <label>Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control" required>
              </div>

              <div class="form-group mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
  <option value="Belum Dibooking">Belum Dibooking</option>
  <option value="Dibooking">Dibooking</option>
</select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require "../master/footer.php"; ?>

<script>
function showToast(message, type="success") {
    const toastContainerId = "toast-container";
    let container = document.getElementById(toastContainerId);
    if(!container) {
        container = document.createElement("div");
        container.id = toastContainerId;
        container.className = "position-fixed bottom-0 end-0 p-3";
        container.style.zIndex = "1100";
        document.body.appendChild(container);
    }
    const toast = document.createElement("div");
    toast.className = `toast align-items-center text-white bg-${type} border-0 mb-2 show`;
    toast.role = "alert";
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    setTimeout(()=>toast.remove(), 4000);
}

function validasiJam(form){
    const mulai = form.jam_mulai.value;
    const selesai = form.jam_selesai.value;
    if(mulai >= selesai){
        showToast("Jam selesai harus lebih besar dari jam mulai!", "danger");
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function(){
    const status = "<?= $_GET['status'] ?? '' ?>";
    const aksi = "<?= $_GET['aksi'] ?? '' ?>";
    if(status==="sukses"){
        showToast("Berhasil melakukan " + aksi + " jadwal!", "success");
    } else if(status==="gagal"){
        const msg = (aksi==="validasi") ? "Jam selesai harus lebih besar dari jam mulai!" : "Gagal melakukan " + aksi + " jadwal!";
        showToast(msg, "danger");
    }
});
</script>
