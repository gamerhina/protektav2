@props(['chartData'])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8 mb-4">
    <!-- Grafik Progres Skripsi -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-32 bg-indigo-50/50 rounded-full blur-3xl -z-10 -mr-20 -mt-20"></div>
        <h3 class="text-lg font-semibold text-slate-800 mb-1 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600">
                <i class="fas fa-chart-bar text-xs"></i>
            </span>
            Progres Skripsi per Angkatan
        </h3>
        <p class="text-xs text-slate-500 mb-6 pl-10">Distribusi mahasiswa berdasarkan tahap pengerjaan skripsi saat ini.</p>
        
        <div class="h-[300px] w-full relative">
            <canvas id="progresSkripsiChart"></canvas>
        </div>
    </div>

    <!-- Grafik Persentase KTW -->
    <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-100 p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-32 bg-emerald-50/50 rounded-full blur-3xl -z-10 -mr-20 -mt-20"></div>
        <h3 class="text-lg font-semibold text-slate-800 mb-1 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-600">
                <i class="fas fa-chart-line text-xs"></i>
            </span>
            Capaian KTW
        </h3>
        <p class="text-xs text-slate-500 mb-6 pl-10">Persentase kelulusan tepat waktu per angkatan.</p>
        
        <div class="h-[300px] w-full relative">
            <canvas id="ktwChart"></canvas>
        </div>
    </div>
</div>

<!-- Ensure Chart.js is loaded -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        const labels = chartData.angkatan;

        // Colors mapping for statuses
        const colors = {
            'Selesai': '#10b981', // emerald-500
            'Kompre': '#3b82f6',  // blue-500
            'Hasil': '#8b5cf6',   // violet-500
            'Proposal': '#f59e0b',// amber-500
            'Diajukan': '#64748b',// slate-500
            'Ditolak': '#ef4444'  // red-500
        };

        const datasets = [];
        for (const [status, data] of Object.entries(chartData.progres)) {
            datasets.push({
                label: status,
                data: data,
                backgroundColor: colors[status] || '#cbd5e1',
                borderRadius: 4,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.8
            });
        }

        // Initialize Progres Chart (Stacked Bar)
        const ctxProgres = document.getElementById('progresSkripsiChart').getContext('2d');
        new Chart(ctxProgres, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: "'Inter', sans-serif", size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        itemSort: (a, b) => b.raw - a.raw // Sort tooltip by highest value
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: "'Inter', sans-serif" }, color: '#64748b' }
                    },
                    y: {
                        stacked: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { family: "'Inter', sans-serif" }, color: '#64748b', precision: 0 }
                    }
                }
            }
        });

        // Initialize KTW Chart (Line/Bar hybrid)
        const ctxKtw = document.getElementById('ktwChart').getContext('2d');
        
        // Create gradient for KTW line area
        let gradientKtw = ctxKtw.createLinearGradient(0, 0, 0, 300);
        gradientKtw.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        gradientKtw.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(ctxKtw, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Persentase KTW (%)',
                    data: chartData.ktw,
                    borderColor: '#10b981', // emerald-500
                    backgroundColor: gradientKtw,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '% Tepat Waktu';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: "'Inter', sans-serif" }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#f1f5f9', drawBorder: false, borderDash: [5, 5] },
                        ticks: { 
                            font: { family: "'Inter', sans-serif" }, 
                            color: '#64748b',
                            callback: function(value) { return value + '%' }
                        }
                    }
                }
            }
        });
    });
</script>
