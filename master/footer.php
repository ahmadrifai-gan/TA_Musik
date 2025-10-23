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
    
    
    
    <!-- Page-level initializers -->
    <!-- <script src="../assets/admin/js/plugins-init/chartjs-init-lite.js"></script> -->

    <script>
      (function() {
        function addScript(src, onload){ var s=document.createElement('script'); s.src=src; s.onload=onload||null; document.body.appendChild(s); }
        // Fallback jQuery if common.min.js failed
        if (typeof window.jQuery === 'undefined') {
          addScript('https://code.jquery.com/jquery-3.6.0.min.js');
        }
        // Fallback Chart.js if local not found
        if (typeof window.Chart === 'undefined') {
          addScript('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.bundle.min.js', function(){
            // Re-run chart initializer after CDN loads
            addScript('../assets/js/chartjs-init-lite.js');
          });
        }
      })();
    </script>

</body>

</html>