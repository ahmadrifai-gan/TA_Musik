<?php require "../master/header.php"; ?>
<?php require "../master/navbar.php"; ?>
<?php require "../master/sidebar.php"; ?>
<?php require "../controller/get_dashboard.php"; ?>

<div class="content-body">

     <div class="container-fluid mt-3">
    <div class="row">
        <!-- Total Pendapatan -->
        <div class="col-lg-4 col-sm-6">
            <div class="card gradient-2">
                <div class="card-body">
                    <h3 class="card-title text-white">Total Pendapatan</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white">Rp <?= number_format($totalPendapatan, 0, ',', '.'); ?></h2>
                        <p class="text-white mb-0">/Bulan</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-money"></i></span>
                </div>
            </div>
        </div>

        <!-- Total Reservasi -->
        <div class="col-lg-4 col-sm-6">
            <div class="card gradient-3">
                <div class="card-body">
                    <h3 class="card-title text-white">Total Reservasi</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white"><?= $totalReserfasi; ?></h2>
                        <p class="text-white mb-0">/Bulan</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-users"></i></span>
                </div>
            </div>
        </div>

        <!-- Total Customer -->
        <div class="col-lg-4 col-sm-6">
            <div class="card gradient-4">
                <div class="card-body">
                    <h3 class="card-title text-white">Total Customer</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white"><?= $totalCustomer; ?></h2>
                        <p class="text-white mb-0">/Bulan</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-heart"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Grafik Reserfasi</h4>
                        <canvas id="team-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Grafik Pendapatam</h4>
                        <canvas id="lineChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php require "../master/footer.php"; ?>