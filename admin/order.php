<?php require "../master/header.php"; ?>
<?php require "../master/navbar.php"; ?>
<?php require "../master/sidebar.php"; ?>
<div class="content-body">
  <div class="container-fluid mt-3">
    
    <!-- Judul dan tombol tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Manajemen Order</h4>
      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
        <i class="fa fa-plus"></i> Tambah Order
      </button>
    </div>

    <!-- Card tabel -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="thead-dark">
              <tr>
                <th width="5%">#</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Harga</th>
                <th>Status</th>
                <th width="20%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Kolor Tea Shirt For Man</td>
                <td>2025-10-07</td>
                <td>Rp 200.000</td>
                <td><span class="badge badge-success">Selesai</span></td>
                <td>
                  <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetail">
                    <i class="fa fa-eye"></i> Lihat
                  </button>
                  <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-danger">
                    <i class="fa fa-trash"></i> Hapus
                  </button>
                </td>
              </tr>

              <tr>
                <td>2</td>
                <td>Blue Backpack For Baby</td>
                <td>2025-09-15</td>
                <td>Rp 150.000</td>
                <td><span class="badge badge-warning">Pending</span></td>
                <td>
                  <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetail">
                    <i class="fa fa-eye"></i> Lihat
                  </button>
                  <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-danger">
                    <i class="fa fa-trash"></i> Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Order</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" class="form-control" placeholder="Masukkan nama produk">
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" class="form-control">
          </div>
          <div class="form-group">
            <label>Harga</label>
            <input type="number" class="form-control" placeholder="Masukkan harga">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="form-control">
              <option>Pending</option>
              <option>Selesai</option>
            </select>
          </div>
          <button type="button" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Order</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" class="form-control" value="Kolor Tea Shirt For Man">
          </div>
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" class="form-control" value="2025-10-07">
          </div>
          <div class="form-group">
            <label>Harga</label>
            <input type="number" class="form-control" value="200000">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="form-control">
              <option selected>Selesai</option>
              <option>Pending</option>
            </select>
          </div>
          <button type="button" class="btn btn-success">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Order</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p><strong>Nama Produk:</strong> Kolor Tea Shirt For Man</p>
        <p><strong>Tanggal:</strong> 2025-10-07</p>
        <p><strong>Harga:</strong> Rp 200.000</p>
        <p><strong>Status:</strong> <span class="badge badge-success">Selesai</span></p>
      </div>
    </div>
  </div>
</div>
<?php require "../master/footer.php"; ?>