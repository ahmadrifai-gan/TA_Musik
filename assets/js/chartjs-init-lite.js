(function ($) {
    "use strict";

    fetch('../admin/get_data.php')
        .then(response => response.json())
        .then(data => {

            // === Grafik Reservasi ===
            var t = document.getElementById("team-chart");
            if (t && window.Chart) {
                t.height = 150;
                new Chart(t, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: "Total Reservasi",
                            data: data.reservasi,
                            backgroundColor: 'rgba(77,124,255,.15)',
                            borderColor: '#4d7cff',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBorderColor: 'transparent',
                            pointBackgroundColor: '#4d7cff'
                        }]
                    },
                    options: {
                        responsive: true,
                        legend: { position: 'top' },
                        tooltips: { mode: 'index', intersect: false },
                        scales: {
                            xAxes: [{ gridLines: { display: false } }],
                            yAxes: [{ gridLines: { color: 'rgba(0,0,0,.05)' }, ticks: { beginAtZero: true } }]
                        }
                    }
                });
            }

            // === Grafik Pendapatan ===
            var l = document.getElementById("lineChart");
            if (l && window.Chart) {
                l.height = 150;
                new Chart(l, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: "Total Pendapatan (Rp)",
                            data: data.pendapatan,
                            borderColor: "rgba(144, 104, 190, .9)",
                            borderWidth: 2,
                            backgroundColor: "rgba(144, 104, 190, .25)"
                        }]
                    },
                    options: {
                        responsive: true,
                        tooltips: { mode: 'index', intersect: false },
                        hover: { mode: 'nearest', intersect: true }
                    }
                });
            }
        })
        .catch(error => console.error('Gagal memuat data grafik:', error));

})(jQuery);
