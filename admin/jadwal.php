<?php
$title = "Daftar Jadwal | Reys Music Studio";
include "../config/koneksi.php";

// === AUTO-ARCHIVE & CLEANUP JADWAL LAMA ===
// Jalan otomatis setiap halaman dibuka

// 1. ARCHIVE: Pindahkan jadwal yang ada booking-nya ke archive (BACKUP)
mysqli_query($koneksi, "
    INSERT INTO jadwal_archive (id_jadwal, jam_mulai, jam_selesai, status, id_studio, tanggal, jam_booking, id_order, deleted_at, archived_at)
    SELECT id_jadwal, jam_mulai, jam_selesai, status, id_studio, tanggal, jam_booking, id_order, deleted_at, NOW()
    FROM jadwal 
    WHERE tanggal < CURDATE() 
    AND (status = 'Dibooking' OR id_order IS NOT NULL)
    AND id_jadwal NOT IN (SELECT IFNULL(id_jadwal, 0) FROM jadwal_archive)
");

// 2. CLEANUP: Hapus SEMUA jadwal yang sudah lewat dari tabel jadwal (kosong & isi)
mysqli_query($koneksi, "
    DELETE FROM jadwal 
    WHERE tanggal < CURDATE()
");

// Hasil: Tabel jadwal BERSIH, data penting di-ARCHIVE, data sampah HILANG

// === GENERATE JADWAL OTOMATIS ===
if (isset($_POST['generate_jadwal'])) {
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $jumlah_hari = (int)$_POST['jumlah_hari'];
    
    $studios = ['ST001', 'ST002']; // Studio Bronze & Gold
    $time_slots = [
        ['10:00:00', '11:00:00'],
        ['11:00:00', '12:00:00'],
        ['12:00:00', '13:00:00'],
        ['13:00:00', '14:00:00'],
        ['14:00:00', '15:00:00'],
        ['15:00:00', '16:00:00'],
        ['16:00:00', '17:00:00'],
        ['17:00:00', '18:00:00'],
        ['18:00:00', '19:00:00'],
        ['19:00:00', '20:00:00'],
        ['20:00:00', '21:00:00'],
        ['21:00:00', '22:00:00']
    ];
    
    $success_count = 0;
    $start_date = new DateTime($tanggal_mulai);
    
    for ($day = 0; $day < $jumlah_hari; $day++) {
        $current_date = clone $start_date;
        $current_date->modify("+$day days");
        $date_str = $current_date->format('Y-m-d');
        
        foreach ($studios as $studio_id) {
            foreach ($time_slots as $slot) {
                // Cek apakah jadwal sudah ada
                $check = mysqli_query($koneksi, "
                    SELECT id_jadwal FROM jadwal 
                    WHERE id_studio='$studio_id' 
                    AND tanggal='$date_str' 
                    AND jam_mulai='{$slot[0]}'
                ");
                
                if (mysqli_num_rows($check) == 0) {
                    $query = mysqli_query($koneksi, "
                        INSERT INTO jadwal (jam_mulai, jam_selesai, status, id_studio, tanggal, jam_booking, id_order, deleted_at)
                        VALUES ('{$slot[0]}', '{$slot[1]}', 'Belum Dibooking', '$studio_id', '$date_str', NULL, NULL, NULL)
                    ");
                    if ($query) $success_count++;
                }
            }
        }
    }
    
    header("Location: jadwal.php?status=sukses&aksi=generate&count=$success_count");
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
    
    // Cek apakah jadwal sudah dibooking
    $check_query = mysqli_query($koneksi, "
        SELECT status, id_order FROM jadwal 
        WHERE id_jadwal='$id'
    ");
    
    if ($check_query && mysqli_num_rows($check_query) > 0) {
        $data = mysqli_fetch_assoc($check_query);
        
        // Hanya hapus jika status "Belum Dibooking" dan tidak ada id_order
        if ($data['status'] == 'Belum Dibooking' && (empty($data['id_order']) || $data['id_order'] == 0)) {
            $query = mysqli_query($koneksi, "DELETE FROM jadwal WHERE id_jadwal='$id'");
            header("Location: jadwal.php?status=" . ($query ? 'sukses' : 'gagal') . "&aksi=hapus");
        } else {
            // Jadwal sudah dibooking, tidak bisa dihapus
            header("Location: jadwal.php?status=gagal&aksi=hapus_booking");
        }
    } else {
        header("Location: jadwal.php?status=gagal&aksi=hapus");
    }
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

// Tampilkan jadwal dari hari ini dan masa depan
$where = [];
if (!empty($_GET['studio'])) {
    $studio = mysqli_real_escape_string($koneksi, $_GET['studio']);
    $where[] = "j.id_studio = '$studio'";
}
if (!empty($_GET['tanggal'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
    $where[] = "j.tanggal = '$tanggal'";
}

// Tampilkan jadwal dari hari ini dan masa depan
$where[] = "j.tanggal >= CURDATE()";
$where_sql = "WHERE " . implode(" AND ", $where);

// PERBAIKAN QUERY: Cek status booking = 'dibatalkan' juga
$sql = "
    SELECT 
        j.*, 
        s.nama AS nama_studio,
        CASE 
            WHEN b.id_order IS NOT NULL AND b.status != 'dibatalkan' THEN 'Dibooking'
            ELSE 'Belum Dibooking'
        END AS status
    FROM jadwal j
    JOIN studio s ON j.id_studio = s.id_studio
    LEFT JOIN booking b ON j.id_jadwal = b.id_jadwal
    $where_sql
    ORDER BY j.tanggal ASC, j.jam_mulai ASC
";
$result = mysqli_query($koneksi, $sql);


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
    margin-left: -30px;
    margin-bottom: 20px; 
    color: #222; 
}

.table-purple {
  background-color: #4B0082 !important;
  color: #ffffff !important;
}

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

.btn-generate {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-generate:hover {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
  color: white !important;
}

.btn-generate i {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.badge.bg-danger,
.badge.bg-success {
    color: white !important;
}

.btn-edit {
    background-color: #17a2b8;
    color: white;
    border: 1px solid #138496;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-edit:hover {
    background-color: #138496;
    color: white;
}

.btn-delete {
    background-color: #b71c1c;
    color: white;
    border: 1px solid #7f0000;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-delete:hover {
    background-color: #7f0000;
    color: white;
}

.btn-delete:disabled {
    background-color: #cccccc;
    border-color: #999999;
    cursor: not-allowed;
    opacity: 0.6;
}

.modal-generate {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.info-box {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border-radius: 10px;
  padding: 15px;
  color: white;
  margin-bottom: 20px;
}

.info-box h6 {
  margin: 0;
  font-weight: 600;
}
/* === Membuat tabel sudut melengkung === */
.table {
  border-collapse: separate !important;
  border-spacing: 0;
  border: 1px solid #ddd;
  border-radius: 10px;
  overflow: hidden;
}

/* Sudut atas */
.table thead th:first-child {
  border-top-left-radius: 10px;
}
.table thead th:last-child {
  border-top-right-radius: 10px;
}

/* Sudut bawah */
.table tbody tr:last-child td:first-child {
  border-bottom-left-radius: 10px;
}
.table tbody tr:last-child td:last-child {
  border-bottom-right-radius: 10px;
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

            <div class="col-md-3 col-sm-6">
              <label class="form-label fw-semibold mb-1">Tanggal</label>
              <input type="date" name="tanggal" class="form-control"
                     value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : '' ?>">
            </div>

            <div class="col-md-2 col-sm-6 d-flex align-items-end">
              <button type="submit" class="btn btn-filter fw-semibold w-100">
                <i class="fa fa-filter"></i> Filter
              </button>
            </div>

            <div class="col-md-4 col-sm-12 d-flex align-items-end">
              <button type="button" class="btn btn-generate fw-semibold w-100"
                      data-bs-toggle="modal" data-bs-target="#modalGenerate">
                <i class="fa fa-magic"></i> Generate Jadwal Otomatis
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
                while ($row = mysqli_fetch_assoc($result)) : 
                  $bisa_hapus = ($row['status'] == 'Belum Dibooking' && (empty($row['id_order']) || $row['id_order'] == 0));
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_studio']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= date('H:i', strtotime($row['jam_mulai'])) ?></td>
                    <td><?= date('H:i', strtotime($row['jam_selesai'])) ?></td>
                    <td>
                      <?php if ($row['status'] == 'Dibooking') : ?>
                        <span class="badge bg-danger">Dibooking</span>
                      <?php else : ?>
                        <span class="badge bg-success">Belum Dibooking</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <button class="btn btn-edit btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_jadwal'] ?>">Edit</button>
                      
                      <?php if ($bisa_hapus) : ?>
                        <a href="jadwal.php?hapus=<?= $row['id_jadwal'] ?>" 
                           onclick="return confirm('Yakin ingin menghapus jadwal ini?')" 
                           class="btn btn-delete btn-sm">Hapus</a>
                      <?php else : ?>
                        <button class="btn btn-delete btn-sm" disabled title="Tidak bisa dihapus - Jadwal sudah dibooking">
                          <i class="fa fa-lock"></i> Hapus
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <!-- Modal Edit -->
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

                            <?php if (!empty($row['id_order'])) : ?>
                              <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> <strong>Info:</strong> Jadwal ini terhubung dengan Order ID: <?= htmlspecialchars($row['id_order']) ?>
                              </div>
                            <?php endif; ?>
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

    <!-- Modal Generate -->
    <div class="modal fade" id="modalGenerate" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post" onsubmit="return confirmGenerate(this)">
            <div class="modal-header modal-generate text-white">
              <h5 class="modal-title"><i class="fa fa-magic"></i> Generate Jadwal Otomatis</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              
              <div class="info-box">
                <h6><i class="fa fa-info-circle"></i> Informasi</h6>
                <small>
                  Fitur ini akan membuat jadwal otomatis untuk <strong>Studio Bronze (ST001)</strong> dan <strong>Studio Gold (ST002)</strong><br>
                  Jam operasional: <strong>10:00 - 22:00</strong> (12 slot per hari)<br>
                  Status default: <strong>Belum Dibooking</strong>
                </small>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required value="<?= date('Y-m-d') ?>">
                    <small class="text-muted">Jadwal akan dimulai dari tanggal ini</small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="fw-semibold">Jumlah Hari</label>
                    <input type="number" name="jumlah_hari" class="form-control" min="1" max="90" value="30" required id="inputJumlahHari" oninput="hitungTotal()">
                    <small class="text-muted">Maksimal 90 hari</small>
                  </div>
                </div>
              </div>

              <div class="alert alert-info">
                <strong>Total yang akan digenerate:</strong>
                <div class="mt-2">
                  <span id="totalSlots" class="badge bg-primary fs-6">720</span> slot jadwal
                  <br>
                  <small class="text-muted">(<span id="totalDays">30</span> hari × 2 studio × 12 slot)</small>
                </div>
              </div>

              <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> <strong>Catatan:</strong>
                <ul class="mb-0 mt-2">
                  <li>Jadwal yang sudah ada tidak akan di-duplikat</li>
                  <li>Hanya slot kosong yang akan dibuat</li>
                  <li>Proses mungkin memakan waktu beberapa detik</li>
                </ul>
              </div>

            </div>
            <div class="modal-footer">
              <button type="submit" name="generate_jadwal" class="btn btn-generate">
                <i class="fa fa-magic"></i> Generate Sekarang
              </button>
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

function hitungTotal() {
    const hari = parseInt(document.getElementById('inputJumlahHari').value) || 0;
    const total = hari * 2 * 12;
    document.getElementById('totalSlots').textContent = total;
    document.getElementById('totalDays').textContent = hari;
}

function confirmGenerate(form) {
    const hari = form.jumlah_hari.value;
    const total = hari * 2 * 12;
    return confirm(`Anda akan membuat ${total} slot jadwal untuk ${hari} hari ke depan.\n\nLanjutkan?`);
}

document.addEventListener("DOMContentLoaded", function(){
    const status = "<?= $_GET['status'] ?? '' ?>";
    const aksi = "<?= $_GET['aksi'] ?? '' ?>";
    const count = "<?= $_GET['count'] ?? '' ?>";
    
    if(status==="sukses"){
        if(aksi==="generate"){
            showToast(`Berhasil generate ${count} jadwal otomatis! 🎉`, "success");
        } else {
            showToast("Berhasil melakukan " + aksi + " jadwal!", "success");
        }
    } else if(status==="gagal"){
        if(aksi==="hapus_booking"){
            showToast("Tidak bisa menghapus! Jadwal sudah terhubung dengan booking. 🔒", "danger");
        } else if(aksi==="validasi"){
            showToast("Jam selesai harus lebih besar dari jam mulai!", "danger");
        } else {
            showToast("Gagal melakukan " + aksi + " jadwal!", "danger");
        }
    }
});
</script>