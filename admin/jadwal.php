<?php
$title = "Daftar Jadwal | Reys Music Studio";
include "../config/koneksi.php";
date_default_timezone_set("Asia/Jakarta");

// ==== AUTO GENERATE JADWAL (BUFFER 7 HARI KE DEPAN) ====
// ==== AUTO GENERATE JADWAL (1x PER HARI, BUFFER 7 HARI) ====
$buffer_days = 7;

$meta = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT last_generated_date FROM system_meta WHERE id=1")
);

$today = date('Y-m-d');

$lastGenerated = $meta['last_generated_date'] ?? null;

if ($lastGenerated !== $today) {


    // ambil semua studio
    $studios = [];
    $q = mysqli_query($koneksi, "SELECT id_studio FROM studio");
    while ($r = mysqli_fetch_assoc($q)) {
        $studios[] = $r['id_studio'];
    }

    $time_slots = [
        ['10:00:00','11:00:00'],
        ['11:00:00','12:00:00'],
        ['12:00:00','13:00:00'],
        ['13:00:00','14:00:00'],
        ['14:00:00','15:00:00'],
        ['15:00:00','16:00:00'],
        ['16:00:00','17:00:00'],
        ['17:00:00','18:00:00'],
        ['18:00:00','19:00:00'],
        ['19:00:00','20:00:00'],
        ['20:00:00','21:00:00'],
        ['21:00:00','22:00:00']
    ];

    $start_date = new DateTime($today);
$target = new DateTime($today);
$target->modify("+$buffer_days days");

while ($start_date <= $target) {
    $tanggal = $start_date->format("Y-m-d");

    foreach ($studios as $studio_id) {
        foreach ($time_slots as $slot) {
            mysqli_query($koneksi, "
                INSERT IGNORE INTO jadwal
                (jam_mulai, jam_selesai, status, id_studio, tanggal)
                VALUES
                ('{$slot[0]}','{$slot[1]}','Belum Dibooking','$studio_id','$tanggal')
            ");
        }
    }

    $start_date->modify("+1 day");
}

// ==== END AUTO GENERATE ====
mysqli_query($koneksi, "
    UPDATE system_meta 
    SET last_generated_date = '$today' 
    WHERE id = 1
");

// ==== 2) ARCHIVE hanya jadwal LAMA yang status = 'Dibooking' atau punya id_order (sama seperti requirement) ====
// Pindahkan booking lama ke jadwal_archive (jangan pindahkan yang sudah ada di archive)
mysqli_query($koneksi, "
    INSERT INTO jadwal_archive (id_jadwal, jam_mulai, jam_selesai, status, id_studio, tanggal, jam_booking, id_order, deleted_at, archived_at)
    SELECT id_jadwal, jam_mulai, jam_selesai, status, id_studio, tanggal, jam_booking, id_order, deleted_at, NOW()
    FROM jadwal 
    WHERE tanggal < CURDATE() 
      AND (status = 'Dibooking' OR id_order IS NOT NULL)
      AND id_jadwal NOT IN (SELECT IFNULL(id_jadwal, 0) FROM jadwal_archive)
");

// ==== 3) CLEANUP: Hapus semua jadwal lama dari tabel jadwal (tetap hindari menghapus yg terhubung booking_offline) ====
mysqli_query($koneksi, "
    DELETE FROM jadwal
    WHERE tanggal < CURDATE()
      AND (status = 'Belum Dibooking' OR status IS NULL)
      AND id_jadwal NOT IN (SELECT id_jadwal FROM booking_offline)
");


// === GENERATE JADWAL OTOMATIS via POST (tetap pertahankan fitur manual generate) ===
if (isset($_POST['generate_jadwal'])) {
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $jumlah_hari = (int)$_POST['jumlah_hari'];
    
    // gunakan studio yang ada di db (atau fallback ke array)
    $studios = [];
    $sq = mysqli_query($koneksi, "SELECT id_studio FROM studio");
    while ($r = mysqli_fetch_assoc($sq)) $studios[] = $r['id_studio'];
    if (empty($studios)) $studios = ['ST001','ST002'];

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
                $query = mysqli_query($koneksi, "
                INSERT IGNORE INTO jadwal
                (jam_mulai, jam_selesai, status, id_studio, tanggal)
                VALUES
                ('{$slot[0]}','{$slot[1]}','Belum Dibooking','$studio_id','$date_str')
              ");
              
              if ($query && mysqli_affected_rows($koneksi) > 0) {
                  $success_count++;
              }              
                }
            }
        }
    }
    
    header("Location: jadwal.php?status=sukses&aksi=generate&count=$success_count");
    exit;
}

// === EDIT JADWAL ===
if (isset($_POST['edit'])) {
    $id_jadwal = mysqli_real_escape_string($koneksi, $_POST['id_jadwal']);
    $id_studio = mysqli_real_escape_string($koneksi, $_POST['id_studio']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

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

// === HAPUS JADWAL (DIPERBAIKI) ===
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Query untuk cek jadwal dan booking terkait
    $check_query = mysqli_query($koneksi, "
        SELECT 
            j.id_jadwal,
            j.status,
            j.id_order,
            b.status AS status_booking
        FROM jadwal j
        LEFT JOIN booking b ON j.id_jadwal = b.id_jadwal
        WHERE j.id_jadwal = '$id'
        LIMIT 1
    ");
    
    if ($check_query && mysqli_num_rows($check_query) > 0) {
        $data = mysqli_fetch_assoc($check_query);
        
        // Boleh hapus jika: Belum Dibooking DAN (tidak ada order ATAU booking dibatalkan)
        $bisa_hapus = false;
        
        if ($data['status'] == 'Belum Dibooking') {
            // Jika tidak ada booking sama sekali
            if (is_null($data['id_order']) || $data['id_order'] == '' || $data['id_order'] == '0') {
                $bisa_hapus = true;
            }
            // Jika ada booking tapi dibatalkan
            elseif (!is_null($data['status_booking']) && $data['status_booking'] == 'dibatalkan') {
                $bisa_hapus = true;
            }
        }
        
        if ($bisa_hapus) {
            $delete_query = mysqli_query($koneksi, "DELETE FROM jadwal WHERE id_jadwal = '$id'");
            
            if ($delete_query) {
                header("Location: jadwal.php?status=sukses&aksi=hapus");
                exit;
            } else {
                header("Location: jadwal.php?status=gagal&aksi=hapus&msg=query_error");
                exit;
            }
        } else {
            header("Location: jadwal.php?status=gagal&aksi=hapus_booking");
            exit;
        }
    } else {
        header("Location: jadwal.php?status=gagal&aksi=hapus&msg=not_found");
        exit;
    }
}

// === FILTER DATA ===
// (Catatan: sebelumnya ada duplikat; saya gunakan satu blok filter yang rapi)
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
        j.status AS status_asli,
        s.nama AS nama_studio,
        b.id_order,
        b.status AS status_booking,
        CASE
    WHEN b.id_order IS NOT NULL AND b.status != 'dibatalkan' THEN 'Dibooking'
    ELSE j.status
END AS status
    FROM jadwal j
    LEFT JOIN studio s ON j.id_studio = s.id_studio
    LEFT JOIN booking b ON j.id_jadwal = b.id_jadwal
    $where_sql
    ORDER BY j.tanggal, j.jam_mulai
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

/* (sisa CSS sama persis seperti file aslimu) */
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
                  // PERBAIKAN: Logika boleh hapus yang konsisten
                  $bisa_hapus = false;
                  
                  if ($row['status_asli'] == 'Belum Dibooking') {
                      // Tidak ada order sama sekali
                      if (empty($row['id_order']) || $row['id_order'] == '0' || is_null($row['id_order'])) {
                          $bisa_hapus = true;
                      }
                      // Ada order tapi booking dibatalkan
                      elseif (isset($row['status_booking']) && $row['status_booking'] == 'dibatalkan') {
                          $bisa_hapus = true;
                      }
                  }
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
                      <button class="btn btn-edit btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_jadwal'] ?>">
                        <i class="fa fa-edit"></i> Edit
                      </button>
                      
                      <?php if ($bisa_hapus) : ?>
                        <a href="jadwal.php?hapus=<?= $row['id_jadwal'] ?>" 
                           onclick="return confirm('Yakin ingin menghapus jadwal ini?\n\nStudio: <?= htmlspecialchars($row['nama_studio']) ?>\nTanggal: <?= date('d/m/Y', strtotime($row['tanggal'])) ?>\nJam: <?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?>')" 
                           class="btn btn-delete btn-sm">
                          <i class="fa fa-trash"></i> Hapus
                        </a>
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
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const aksi = urlParams.get('aksi');
    const count = urlParams.get('count');
    const msg = urlParams.get('msg');
    
    if(status === "sukses"){
        if(aksi === "generate"){
            showToast(`Berhasil generate ${count} jadwal otomatis! 🎉`, "success");
        } else if(aksi === "hapus"){
            showToast("✅ Jadwal berhasil dihapus!", "success");
        } else if(aksi === "edit"){
            showToast("✅ Jadwal berhasil diupdate!", "success");
        } else {
            showToast("✅ Berhasil melakukan " + aksi + " jadwal!", "success");
            </script>