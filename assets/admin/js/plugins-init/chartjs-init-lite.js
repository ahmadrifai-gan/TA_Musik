(function ($) {
    "use strict";

    // Team chart (per minggu)
    var t = document.getElementById("team-chart");
    if (t && window.Chart) {
        t.height = 150;
        new Chart(t, {
            type: 'line',
            data: {
                labels: ["jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
                datasets: [{
                    data: [12, 18, 14, 20, 25, 22, 19, 1, 12, 6, 9, 2],
                    label: "Tim A",
                    backgroundColor: 'rgba(77,124,255,.15)',
                    borderColor: '#4d7cff',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBorderColor: 'transparent',
                    pointBackgroundColor: '#4d7cff'
                }, 
            ]
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

    // Line chart (per minggu)
    var l = document.getElementById("lineChart");
    if (l && window.Chart) {
        l.height = 150;
        new Chart(l, {
            type: 'line',
            data: {
                labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
                datasets: [
                    {
                        label: "Dataset A",
                        borderColor: "rgba(144, 104, 190, .9)",
                        borderWidth: 1,
                        backgroundColor: "rgba(144, 104, 190, .25)",
                        data: [5, 9, 7, 12, 10, 8, 6]
                    },
                ]
            },
            options: {
                responsive: true,
                tooltips: { mode: 'index', intersect: false },
                hover: { mode: 'nearest', intersect: true }
            }
        });
    }

})(jQuery);


