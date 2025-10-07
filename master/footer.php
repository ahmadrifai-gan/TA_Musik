  <div class="footer">
            <div class="copyright">
                <p>Copyright &copy; Designed & Developed by <a href="https://themeforest.net/user/quixlab">Reys Studio Musik</a> 2018</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->
    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="../assets/admin/plugins/common/common.min.js"></script>
    <script src="../assets/admin/js/custom.min.js"></script>
    <script src="../assets/admin/js/settings.js"></script>
    <script src="../assets/admin/js/gleek.js"></script>
    <script src="../assets/admin/js/styleSwitcher.js"></script>

    <!-- Chartjs -->
    <script src="../assets/admin/plugins/chart.js/Chart.bundle.min.js"></script>
    <!-- Circle progress -->
    <script src="../assets/admin/plugins/circle-progress/circle-progress.min.js"></script>
    <!-- Datamap -->
    <script src="../assets/admin/plugins/d3v3/index.js"></script>
    <script src="../assets/admin/plugins/topojson/topojson.min.js"></script>
    <script src="../assets/admin/plugins/datamaps/datamaps.world.min.js"></script>
    <!-- Morrisjs -->
    <script src="../assets/admin/plugins/raphael/raphael.min.js"></script>
    <script src="../assets/admin/plugins/morris/morris.min.js"></script>
    <!-- Pignose Calender -->
    <script src="../assets/admin/plugins/moment/moment.min.js"></script>
    <script src="../assets/admin/plugins/pg-calendar/js/pignose.calendar.min.js"></script>
    <!-- ChartistJS -->
    <script src="../assets/admin/plugins/chartist/js/chartist.min.js"></script>
    <script src="../assets/admin/plugins/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js"></script>



    <script>
      $('#modalEdit').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var id = button.data('id')
        var nama = button.data('nama')
        var deskripsi = button.data('deskripsi')
        var harga = button.data('harga')

        var modal = $(this)
        modal.find('#edit-id').val(id)
        modal.find('#edit-nama').val(nama)
        modal.find('#edit-deskripsi').val(deskripsi)
        modal.find('#edit-harga').val(harga)
      })
    </script>

</body>

</html>