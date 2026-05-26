@props([
    'tepatWaktuCount' => 0,
    'tidakTepatWaktuCount' => 0,
    'ongoingTepatWaktuCount' => 0,
    'ongoingOverdueCount' => 0,
    'studentsPaginated',
    'searchMhs' => '',
    'updateRoutePrefix' => 'admin' // 'admin' or 'dosen'
])

<div class="grid gap-6 lg:grid-cols-3 mt-6">
    <!-- Chart Card -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6 flex flex-col justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2.5 h-6 rounded-full bg-indigo-600 block"></span>
                <span>Persentase Kelulusan Tepat Waktu</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Estimasi ketepatan kelulusan (batas 4 tahun / 48 bulan dari 1 Agustus angkatan)</p>
        </div>
        
        <div class="relative w-full h-52 my-6 flex items-center justify-center">
            @if($tepatWaktuCount + $tidakTepatWaktuCount + $ongoingTepatWaktuCount + $ongoingOverdueCount > 0)
                <canvas id="graduationChart" class="max-w-[200px] max-h-[200px]"></canvas>
            @else
                <div class="text-center text-gray-400 py-10 flex flex-col items-center gap-2">
                    <i class="fas fa-chart-pie text-4xl"></i>
                    <p class="text-xs">Belum ada data statistik mahasiswa</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2.5 text-xs">
            <div class="flex items-center gap-1.5 p-2 rounded-xl bg-emerald-50/50 border border-emerald-100">
                <span class="w-3 h-3 rounded-full bg-emerald-500 block shrink-0"></span>
                <div>
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Tepat Waktu</span>
                    <strong class="text-emerald-700 text-sm block mt-0.5">{{ $tepatWaktuCount }} Mhs</strong>
                </div>
            </div>
            <div class="flex items-center gap-1.5 p-2 rounded-xl bg-rose-50/50 border border-rose-100">
                <span class="w-3 h-3 rounded-full bg-rose-500 block shrink-0"></span>
                <div>
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Terlambat</span>
                    <strong class="text-rose-700 text-sm block mt-0.5">{{ $tidakTepatWaktuCount }} Mhs</strong>
                </div>
            </div>
            <div class="flex items-center gap-1.5 p-2 rounded-xl bg-blue-50/50 border border-blue-100">
                <span class="w-3 h-3 rounded-full bg-blue-500 block shrink-0"></span>
                <div>
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Aktif (Aman)</span>
                    <strong class="text-blue-700 text-sm block mt-0.5">{{ $ongoingTepatWaktuCount }} Mhs</strong>
                </div>
            </div>
            <div class="flex items-center gap-1.5 p-2 rounded-xl bg-amber-50/50 border border-amber-100">
                <span class="w-3 h-3 rounded-full bg-amber-500 block shrink-0"></span>
                <div>
                    <span class="text-[10px] text-gray-400 block uppercase font-semibold">Aktif (Kritis)</span>
                    <strong class="text-amber-700 text-sm block mt-0.5">{{ $ongoingOverdueCount }} Mhs</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Student List Card -->
    <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-lg p-6 flex flex-col justify-between">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2.5 h-6 rounded-full bg-indigo-600 block"></span>
                        <span>Daftar Mahasiswa & Kelulusan</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Klik tombol edit untuk merubah tanggal kelulusan</p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <!-- Export Button -->
                    <a 
                        href="{{ route($updateRoutePrefix . '.mahasiswa.export-graduation') }}" 
                        target="_blank"
                        download="data_kelulusan.xlsx"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-100 text-xs font-semibold transition whitespace-nowrap"
                    >
                        <i class="fas fa-file-excel text-xs"></i>
                        <span>Export Excel</span>
                    </a>

                    <!-- Mini Search Form -->
                    <form method="GET" class="relative sm:w-60">
                        @foreach(request()->except(['search_mhs', 'page_mhs']) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input 
                            type="text" 
                            name="search_mhs" 
                            value="{{ $searchMhs }}" 
                            placeholder="Cari nama / NPM..."
                            class="w-full rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-xs text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition"
                        >
                    </form>
                </div>
            </div>

            <!-- Table of Students -->
            <div class="overflow-x-auto mt-4 border border-slate-100 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Mahasiswa</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Angkatan</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tgl Wisuda / Lulus</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            @if($updateRoutePrefix === 'admin')
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100 text-xs">
                        @forelse($studentsPaginated as $student)
                            @php
                                $startDate = $student->getTanggalMulaiKuliah();
                                $endDate = $student->getTanggalLulus();
                                $isTepatWaktu = $student->isLulusTepatWaktu();
                                $limitDate = $startDate ? $startDate->copy()->addYears(4) : null;
                                $hasCompletedSkripsi = $student->seminars()
                                    ->whereHas('seminarJenis', function ($query) {
                                        $query->where('kode', 'US');
                                    })
                                    ->where('status', 'selesai')
                                    ->exists();
                                
                                // Package student details as JSON safe object
                                $studentJson = json_encode([
                                    'id' => $student->id,
                                    'nama' => $student->nama,
                                    'npm' => $student->npm,
                                    'angkatan' => $startDate ? $startDate->year : null,
                                    'tanggal_mulai_kuliah_formatted' => $startDate ? $startDate->translatedFormat('d F Y') : '-',
                                    'batas_lulus_tepat_waktu_formatted' => $limitDate ? $limitDate->translatedFormat('d F Y') : '-',
                                    'tanggal_lulus_formatted' => $endDate ? $endDate->translatedFormat('d F Y') : 'Belum Lulus',
                                    'tanggal_lulus_raw' => $student->tanggal_lulus_manual ? $student->tanggal_lulus_manual->format('Y-m-d') : '',
                                    'tanggal_mulai_kuliah_raw' => $student->tanggal_mulai_kuliah_manual ? $student->tanggal_mulai_kuliah_manual->format('Y-m-d') : '',
                                    'has_completed_skripsi' => $hasCompletedSkripsi,
                                    'is_manual' => !empty($student->tanggal_lulus_manual)
                                ]);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $student->nama }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $student->npm }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $startDate ? 'Angkatan ' . $startDate->year : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-slate-700">
                                        {{ $endDate ? $endDate->translatedFormat('d F Y') : 'Belum Ditentukan' }}
                                    </div>
                                    <div class="text-[9px]">
                                        @if($student->tanggal_lulus_manual)
                                            <span class="text-indigo-500 font-medium">Input Manual</span>
                                        @elseif($hasCompletedSkripsi)
                                            <span class="text-emerald-500 font-medium">Ujian Skripsi</span>
                                        @else
                                            <span class="text-amber-500 font-medium">Belum Lulus</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($startDate)
                                        @if($endDate)
                                            @if($isTepatWaktu)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Tepat Waktu</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">Terlambat</span>
                                            @endif
                                        @else
                                            @php
                                                $now = now()->startOfDay();
                                                $isWithinLimit = $limitDate ? $now->lessThanOrEqualTo($limitDate) : true;
                                            @endphp
                                            @if($isWithinLimit)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Aktif (Aman)</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Aktif (Kritis)</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                @if($updateRoutePrefix === 'admin')
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <button 
                                            type="button" 
                                            onclick='openEditGraduationModal({!! htmlspecialchars($studentJson, ENT_QUOTES, "UTF-8") !!})'
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 transition"
                                            title="Edit Tanggal Kelulusan"
                                        >
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-slate-400">
                                    Tidak ada data mahasiswa ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($studentsPaginated->count() > 0)
            <div class="mt-4 flex justify-end">
                {{ $studentsPaginated->appends(['search_mhs' => $searchMhs])->links('components.pagination') }}
            </div>
        @endif
    </div>
</div>

<!-- Premium Modal Backdrop-Blur -->
<div 
    id="editGraduationModal" 
    class="fixed inset-0 z-50 hidden overflow-y-auto"
>
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Backdrop with blur -->
        <div 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
            onclick="closeEditGraduationModal()"
        ></div>

        <!-- Modal Card -->
        <div class="relative bg-white rounded-3xl text-left shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full overflow-hidden border border-slate-100 z-10">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-4 text-white flex items-center justify-between">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Atur Tanggal Kelulusan</span>
                </h3>
                <button 
                    type="button" 
                    class="text-white/80 hover:text-white transition"
                    onclick="closeEditGraduationModal()"
                >
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Content Form -->
            <form id="editGraduationForm" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- Quick Student Summary -->
                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Mahasiswa</span>
                        <strong class="text-slate-800" id="modalStudentName">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">NPM & Angkatan</span>
                        <strong class="text-slate-800 font-mono" id="modalStudentNpm">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Tanggal Mulai Kuliah</span>
                        <strong class="text-slate-800" id="modalStudentMulai">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Batas Lulus Tepat Waktu</span>
                        <strong class="text-slate-800" id="modalStudentBatas">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Tanggal Kelulusan Saat Ini</span>
                        <strong class="text-slate-800" id="modalStudentTanggal">-</strong>
                    </div>
                </div>

                <!-- Input Date Picker - Mulai Kuliah -->
                <div>
                    <label for="modal_tanggal_mulai_kuliah_manual" class="text-xs font-semibold text-slate-700">Tanggal Mulai Kuliah</label>
                    <input 
                        type="date" 
                        name="tanggal_mulai_kuliah_manual" 
                        id="modal_tanggal_mulai_kuliah_manual" 
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition mt-1.5"
                    >
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                        Kosongkan untuk mengembalikan nilai default (Otomatis berdasarkan NPM: 1 Agustus tahun angkatan).
                    </p>
                </div>

                <!-- Input Date Picker -->
                <div>
                    <label for="modal_tanggal_lulus_manual" class="text-xs font-semibold text-slate-700">Tanggal Wisuda / Kelulusan</label>
                    <input 
                        type="date" 
                        name="tanggal_lulus_manual" 
                        id="modal_tanggal_lulus_manual" 
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition mt-1.5"
                    >
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                        Kosongkan untuk mengembalikan nilai default (Tanggal Ujian Skripsi otomatis).
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-2">
                    <button 
                        type="button" 
                        onclick="closeEditGraduationModal()"
                        class="flex-1 rounded-xl border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 rounded-xl bg-indigo-600 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition shadow-md"
                    >
                        Simpan Tanggal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Register chart drawing on load
    window.Protekta.registerInit(() => {
        const canvas = document.getElementById('graduationChart');
        if (!canvas) return;
        if (canvas.getAttribute('data-chart-initialized') === 'true') return;

        const dataValues = [
            {{ $tepatWaktuCount }},
            {{ $tidakTepatWaktuCount }},
            {{ $ongoingTepatWaktuCount }},
            {{ $ongoingOverdueCount }}
        ];

        // Chart.js instance setup
        if (window.graduationChartInstance instanceof Chart) {
            window.graduationChartInstance.destroy();
        }

        window.graduationChartInstance = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Tepat Waktu', 'Terlambat', 'Aktif (Aman)', 'Aktif (Kritis)'],
                datasets: [{
                    data: dataValues,
                    backgroundColor: [
                        '#10b981', // emerald-500
                        '#f43f5e', // rose-500
                        '#3b82f6', // blue-500
                        '#f59e0b'  // amber-500
                    ],
                    borderWidth: 1,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                cutout: '65%'
            }
        });
        
        canvas.setAttribute('data-chart-initialized', 'true');
    });

    // Modal Handling Functions
    function openEditGraduationModal(student) {
        const modal = document.getElementById('editGraduationModal');
        const form = document.getElementById('editGraduationForm');
        
        // Populate modal text labels
        document.getElementById('modalStudentName').textContent = student.nama;
        document.getElementById('modalStudentNpm').textContent = `${student.npm} (Angkatan ${student.angkatan || '-'})`;
        document.getElementById('modalStudentMulai').textContent = student.tanggal_mulai_kuliah_formatted;
        document.getElementById('modalStudentBatas').textContent = student.batas_lulus_tepat_waktu_formatted;
        
        let labelSuffix = '';
        if (student.is_manual) {
            labelSuffix = ' (Input Manual)';
        } else if (student.has_completed_skripsi) {
            labelSuffix = ' (Ujian Skripsi)';
        } else {
            labelSuffix = ' (Belum Lulus)';
        }
        document.getElementById('modalStudentTanggal').textContent = student.tanggal_lulus_formatted + labelSuffix;
        
        // Populate date picker value
        document.getElementById('modal_tanggal_lulus_manual').value = student.tanggal_lulus_raw || '';
        document.getElementById('modal_tanggal_mulai_kuliah_manual').value = student.tanggal_mulai_kuliah_raw || '';
        
        // Dynamic form action binding
        const routePrefix = '{{ $updateRoutePrefix }}';
        form.action = `/${routePrefix}/mahasiswa/${student.id}/update-tanggal-lulus`;
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeEditGraduationModal() {
        const modal = document.getElementById('editGraduationModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
