@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-3xl shadow-2xl p-8 md:p-12 text-white relative overflow-hidden">
        <div class="relative z-10 grid gap-6 md:grid-cols-2 items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold leading-tight text-white">Selamat datang kembali, {{ auth()->user()->nama ?? 'Admin' }}.</h1>
                <p class="text-white mt-4 text-lg">Pantau ekosistem seminar, kelola pengguna, dan jaga proses akademik tetap lancar melalui ringkasan ini.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.seminar.create') }}" class="btn-gradient inline-flex items-center gap-2 text-sm">
                        <i class="fas fa-bolt"></i> Buat Seminar Baru
                    </a>
                </div>
            </div>
            <div class="bg-white/20 rounded-2xl p-6 backdrop-blur-sm flex flex-col gap-4">
                <div class="flex justify-between text-sm text-white/70">
                    <span>Aktivitas Mingguan</span>
                    <span class="{{ $trendPercent >= 0 ? 'text-green-300' : 'text-red-300' }} font-bold">
                        {{ $trendPercent >= 0 ? '+' : '' }}{{ $trendPercent }}%
                    </span>
                </div>
                <div class="w-full h-32">
                    <canvas id="activitySparkline" class="w-full h-full"></canvas>
                </div>
                <div class="text-sm text-white/80">
                    @if($trendPercent > 0)
                        Lonjakan aktivitas minggu ini dipimpin oleh pendaftaran seminar baru.
                    @elseif($trendPercent < 0)
                        Aktivitas sedikit menurun dibanding pekan lalu, tetap pantau pengajuan baru.
                    @else
                        Aktivitas stabil dibanding pekan lalu.
                    @endif
                </div>
            </div>
        </div>
        <div class="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.35),_transparent_55%)]"></div>
    </div>

    <!-- Metric Cards -->
    <div class="grid gap-5 md:grid-cols-3">
        @php
            $stats = [
                [
                    'label' => 'Total Mahasiswa',
                    'value' => $mahasiswaCount,
                    'trend' => null, // We could calculate this too if needed
                    'icon' => 'fa-user-graduate',
                    'gradient' => 'from-blue-500 to-indigo-500'
                ],
                [
                    'label' => 'Total Dosen',
                    'value' => $dosenCount,
                    'trend' => null,
                    'icon' => 'fa-chalkboard-teacher',
                    'gradient' => 'from-emerald-500 to-green-500'
                ],
                [
                    'label' => 'Total Seminar',
                    'value' => $seminarCount,
                    'trend' => ($trendPercent >= 0 ? '+' : '') . $trendPercent . '%',
                    'icon' => 'fa-calendar-check',
                    'gradient' => 'from-purple-500 to-pink-500'
                ],
            ];
        @endphp

        @foreach($stats as $stat)
            <div class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-lg">
                <div class="absolute inset-x-6 top-6 h-14 rounded-2xl bg-gradient-to-r {{ $stat['gradient'] }} opacity-20 blur-md"></div>
                <div class="relative p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="text-gray-900 text-base font-medium">{{ $stat['label'] }}</div>
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-r {{ $stat['gradient'] }} text-white shadow-lg">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </span>
                    </div>
                    <div class="text-4xl font-semibold text-gray-900">{{ $stat['value'] }}</div>
                    @if($stat['trend'])
                        <div class="text-sm {{ $trendPercent >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">{{ $stat['trend'] }} dibanding pekan lalu</div>
                    @else
                        <div class="text-sm text-gray-400 font-semibold italic text-xs">Simpan berkala</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Actions & System Snapshot -->
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Navigasi Cepat</h2>
                    <p class="text-sm text-gray-500">Kelola bagian paling sering diakses</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @php
                    $shortcuts = [
                        ['label' => 'Kelola Dosen', 'icon' => 'fa-chalkboard-teacher', 'color' => 'from-blue-500 to-indigo-500', 'route' => route('admin.dosen.index')],
                        ['label' => 'Kelola Mahasiswa', 'icon' => 'fa-user-graduate', 'color' => 'from-emerald-500 to-teal-500', 'route' => route('admin.mahasiswa.index')],
                        ['label' => 'Kelola Seminar', 'icon' => 'fa-calendar-alt', 'color' => 'from-purple-500 to-pink-500', 'route' => route('admin.seminar.index')],
                        ['label' => 'Kelola Surat', 'icon' => 'fa-envelope', 'color' => 'from-cyan-500 to-blue-500', 'route' => route('admin.surat.index')],
                        ['label' => 'Jenis Seminar', 'icon' => 'fa-layer-group', 'color' => 'from-orange-500 to-amber-500', 'route' => route('admin.seminarjenis.index')],
                        ['label' => 'Folder GDrive', 'icon' => 'fa-folder-open', 'color' => 'from-rose-500 to-red-500', 'route' => route('admin.gdrive.index')],
                    ];
                @endphp
                @foreach($shortcuts as $shortcut)
                    <a href="{{ $shortcut['route'] }}" class="group rounded-2xl border border-gray-100 bg-white p-4 shadow-sm hover:shadow-lg transition-all">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-r {{ $shortcut['color'] }} text-white shadow">
                            <i class="fas {{ $shortcut['icon'] }}"></i>
                        </span>
                        <p class="mt-4 font-semibold text-gray-900 group-hover:text-blue-600">{{ $shortcut['label'] }}</p>
                        <p class="text-xs text-gray-500">Klik untuk melihat detail</p>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Snapshot Sistem</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Agenda Seminar Mendatang</p>
                        <p class="font-semibold text-gray-900">{{ $scheduledSeminarsCount }} jadwal</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $scheduledSeminarsCount > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                        {{ $scheduledSeminarsCount > 0 ? 'On Track' : 'Belum Ada' }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-2">Penjadwalan Bulan Ini</p>
                    <div class="h-2 rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Bulan ini: {{ $progressPercent }}% tersentralisasi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h2>
                <p class="text-sm text-gray-500">Catatan pendaftaran dan perubahan sistem terbaru</p>
            </div>
        </div>
        <div class="space-y-5">
            @forelse($recentActivities as $activity)
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center {{ $activity['color'] }} text-lg">
                        <i class="fas {{ $activity['icon'] }}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-900">{{ $activity['title'] }}</p>
                            <span class="text-xs text-gray-400">{{ $activity['time'] }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{!! $activity['desc'] !!}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="text-gray-400 mb-2 mt-4 text-4xl">
                        <i class="fas fa-history"></i>
                    </div>
                    <p class="text-gray-500">Belum ada aktivitas terbaru dalam sistem.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>


    {{-- Scripts moved inside content section to ensure AJAX compatibility --}}
    <script>
        window.Protekta.registerInit(() => {
            const canvas = document.getElementById('activitySparkline');
            if (!canvas) return;
            if (canvas.getAttribute('data-chart-initialized') === 'true') return;

            const labels = {!! json_encode($labels) !!};
            const data = {!! json_encode($weeklyActivity) !!};

            if (window.activityChart instanceof Chart) window.activityChart.destroy();

            window.activityChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        borderColor: 'rgba(255,255,255,0.9)',
                        borderWidth: 2,
                        fill: true,
                        backgroundColor: 'rgba(255,255,255,0.15)',
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(255,255,255,1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: { x: { display: false }, y: { display: false } }
                }
            });
            canvas.setAttribute('data-chart-initialized', 'true');
        });
    </script>
@endsection
