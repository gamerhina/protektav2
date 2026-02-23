@extends('layouts.app')

@section('title', 'Ubah Jenis Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Ubah Jenis Seminar</h1>
            <div id="autoSaveIndicator" class="text-sm text-gray-500 hidden">
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="saveStatusText">Draft tersimpan otomatis</span>
                </span>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.seminarjenis.update', $seminarJenis->id) }}" method="POST" id="basicInfoForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="basic_info">
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Seminar</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $seminarJenis->nama) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        placeholder="Contoh: Seminar Usul, Seminar Hasil, Ujian Skripsi"
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode Jenis (tanpa spasi)</label>
                    <input
                        type="text"
                        name="kode"
                        id="kode"
                        value="{{ old('kode', $seminarJenis->kode) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('kode') border-red-500 @enderror"
                        placeholder="Contoh: SUSUL, SHAS, UKRP"
                        required
                    >
                    @error('kode')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea
                        name="keterangan"
                        id="keterangan"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('keterangan') border-red-500 @enderror"
                        placeholder="Deskripsi tambahan tentang jenis seminar ini">{{ old('keterangan', $seminarJenis->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Tim Evaluator</h3>
                    <p class="text-xs text-slate-600 mb-3">Centang evaluator yang <span class="font-semibold">wajib</span> mengisi nilai & tanda tangan untuk menyelesaikan seminar.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="p1_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="p1_required" value="1" {{ old('p1_required', $seminarJenis->p1_required ?? true) ? 'checked' : '' }}>
                                <span>Pembimbing 1</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="p2_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="p2_required" value="1" {{ old('p2_required', $seminarJenis->p2_required ?? true) ? 'checked' : '' }}>
                                <span>Pembimbing 2</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-3">
                            <input type="hidden" name="pembahas_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                                <input type="checkbox" name="pembahas_required" value="1" {{ old('pembahas_required', $seminarJenis->pembahas_required ?? true) ? 'checked' : '' }}>
                                <span>Pembahas</span>
                            </label>
                        </div>
                    </div>

                    @error('p1_required')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                @if(($syaratReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur syarat seminar belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @else
                    <div>
                        <label for="syarat_seminar" class="block text-sm font-medium text-gray-700 mb-1">Syarat Seminar (Opsional)</label>
                        <x-tinymce-editor
                            name="syarat_seminar"
                            id="syarat_seminar"
                            :content="old('syarat_seminar', $seminarJenis->syarat_seminar)"
                            placeholder="Tuliskan syarat seminar yang harus dipenuhi mahasiswa (akan tampil sebelum upload berkas syarat)"
                            :has-header="false"
                            height="400"
                        />
                        @error('syarat_seminar')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(($berkasItemsReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur upload syarat belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @endif

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-file-list text-blue-500"></i>
                                Konfigurasi Berkas & Form Persyaratan
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Tambahkan field sesuai kebutuhan (File upload, Text input, dll).</p>
                        </div>
                        <button type="button" id="add-berkas-item" class="btn-pill btn-pill-primary text-xs px-4 py-2">
                            <i class="fas fa-plus mr-1"></i> Tambah Field
                        </button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                        <div class="app-table-wrapper">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-3 bg-gray-50 w-8"></th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Label</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Key</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Placeholder</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aturan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Wajib</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="berkas-items-body" class="divide-y divide-gray-100 bg-white">
                                    <tr id="no-fields-row" style="display: none;">
                                        <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada field. Klik “Tambah Field”.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                     <p class="text-xs text-gray-500 mt-3">
                        Catatan opsi untuk <strong>Select/Radio/Checkbox</strong>: isi per baris format <span class="font-mono">value|label</span> (contoh: <span class="font-mono">mhs|Mahasiswa</span>).
                        Untuk <strong>File</strong>: isi ekstensi dipisah koma (contoh: <span class="font-mono">pdf,jpg,png</span>) dan max size dalam KB.
                    </p>

                    @if(($berkasItemsReady ?? false) === false)
                        <div class="text-sm text-gray-600 mt-2">(Tidak tersedia sebelum migrasi dijalankan)</div>
                    @else
                        @php
                            $existingItems = old('berkas_syarat_items') !== null
                                ? old('berkas_syarat_items') // If validation fails, use old input (needs careful handling as array of array)
                                : ($seminarJenis->berkas_syarat_items ?? []);
                            
                            // Ensure it is array
                            if (!is_array($existingItems)) $existingItems = [];
                        @endphp

                        <script type="application/json" id="existing-berkas-items">
                            {!! json_encode($existingItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                        </script>
                    @endif
                </div>

                <!-- Hidden fields for weights -->
                <input type="hidden" name="p1_weight" id="basic_p1_weight" value="{{ $seminarJenis->p1_weight ?? 35 }}">
                <input type="hidden" name="p2_weight" id="basic_p2_weight" value="{{ $seminarJenis->p2_weight ?? 35 }}">
                <input type="hidden" name="pembahas_weight" id="basic_pembahas_weight" value="{{ $seminarJenis->pembahas_weight ?? 30 }}">
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end pt-6 border-t border-gray-200">
                <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Simpan Informasi Dasar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Aspect Management Section (Outside form to allow nested forms) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Kelola Aspek Penilaian</h2>
            <p class="text-sm text-gray-600 mb-6">Konfigurasikan aspek-aspek penilaian untuk jenis seminar ini. Setiap penilai (P1, P2, Pembahas) dapat memiliki aspek penilaian yang berbeda.</p>

            <!-- Add New Aspect Form -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-800 mb-3">Tambah Aspek Penilaian Baru</h3>
                <form action="{{ route('admin.seminarjenis.aspects.store', $seminarJenis) }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    @csrf
                    <div>
                        <label for="evaluator_type_add" class="block text-sm font-medium text-gray-700 mb-1">Penilai</label>
                        <select name="evaluator_type" id="evaluator_type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                            <option value="">Pilih Penilai</option>
                            <option value="p1">Pembimbing 1 (P1)</option>
                            <option value="p2">Pembimbing 2 (P2)</option>
                            <option value="pembahas">Pembahas (PMB)</option>
                        </select>
                    </div>
                    <div>
                        <label for="type_add" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="type" id="type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                            <option value="input">Input Angka</option>
                            <option value="sum">Jumlah (Sum)</option>
                            <option value="prev_avg">Rata-rata (Avg)</option>
                        </select>
                    </div>
                    <div>
                        <label for="category_add" class="block text-sm font-medium text-gray-700 mb-1">Kat.</label>
                        <input type="text" name="category" id="category_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Grup A">
                    </div>
                    <div>
                        <label for="nama_aspek_add" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                        <input type="text" name="nama_aspek" id="nama_aspek_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Nilai ..." required>
                    </div>

                    <div>
                        <label for="urutan_add" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" id="urutan_add" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="1" required>
                    </div>

                    <!-- Dynamic Choice for Sum/Avg -->
                    <div id="related_aspects_container_add" class="md:col-span-6 hidden bg-gray-50 border border-blue-200 rounded-lg p-4 mt-2">
                        <label class="block text-sm font-bold text-blue-800 mb-2">Pilih Aspek Komponen (Input):</label>
                        <div id="aspect_list_add" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <!-- Checkboxes will be inserted here via JS -->
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2 italic">*Hanya aspek bertipe "Input" yang dapat dipilih.</p>
                    </div>

                    <div class="md:col-span-6 flex justify-end">
                        <button type="submit" class="btn-pill btn-pill-primary px-8">
                            Tambah Aspek
                        </button>
                    </div>
                </form>
            </div>

            <!-- Display Existing Aspects -->
            @foreach(['p1' => 'Pembimbing 1 (P1)', 'p2' => 'Pembimbing 2 (P2)', 'pembahas' => 'Pembahas (PMB)'] as $type => $label)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $label }}</h3>
                        @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                            <span class="px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $aspects[$type]->count() }} Aspek
                            </span>
                        @endif
                    </div>
                    
                    @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase">Seq</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase">Jenis</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase">Kat.</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase">Nama Aspek</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($aspects[$type] as $aspect)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">{{ $aspect->urutan }}</td>
                                        <td class="px-4 py-3 text-[10px] font-bold">
                                            @if($aspect->type === 'input')
                                                <span class="text-blue-600">INPUT</span>
                                            @elseif($aspect->type === 'sum')
                                                <span class="text-emerald-600">SUM</span>
                                            @else
                                                <span class="text-purple-600">AVG</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[10px] text-gray-500">{{ $aspect->category ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $aspect->nama_aspek }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button onclick="editAspect({{ json_encode($aspect) }})" class="text-blue-600 hover:text-blue-900 font-semibold" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.seminarjenis.aspects.destroy', [$seminarJenis, $aspect]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspek ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center border-2 border-dashed border-gray-300">
                            <p class="mt-2 text-sm text-gray-500">Belum ada aspek penilaian untuk {{ $label }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
    </div>
</div>

<script id="aspects-data" type="application/json">
    {!! json_encode($seminarJenis->assessmentAspects()->get()->groupBy('evaluator_type')) !!}
</script>

<!-- Evaluator Weight Percentages & Submit Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form action="{{ route('admin.seminarjenis.update', $seminarJenis->id) }}" method="POST" id="weightsForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="weights">
            <input type="hidden" name="nama" id="weight_nama" value="{{ $seminarJenis->nama }}">
            <input type="hidden" name="kode" id="weight_kode" value="{{ $seminarJenis->kode }}">
            <input type="hidden" name="keterangan" id="weight_keterangan" value="{{ $seminarJenis->keterangan }}">
            <input type="hidden" name="syarat_seminar" id="weight_syarat_seminar" value="{{ $seminarJenis->syarat_seminar }}">
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bobot Persentase Penilai</h3>
                <p class="text-sm text-gray-600 mb-4">Tentukan bobot persentase untuk setiap penilai. Total harus 100%.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="p1_weight_bottom" class="block text-sm font-medium text-blue-700 mb-1">Pembimbing 1 (P1) %</label>
                        <input
                            type="number"
                            name="p1_weight"
                            id="p1_weight_bottom"
                            value="{{ old('p1_weight', $seminarJenis->p1_weight ?? 35) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-blue-300 rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="35"
                            required
                        >
                    </div>

                    <div>
                        <label for="p2_weight_bottom" class="block text-sm font-medium text-green-700 mb-1">Pembimbing 2 (P2) %</label>
                        <input
                            type="number"
                            name="p2_weight"
                            id="p2_weight_bottom"
                            value="{{ old('p2_weight', $seminarJenis->p2_weight ?? 35) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-green-300 rounded-md focus:border-green-500 focus:ring-2 focus:ring-green-200"
                            placeholder="35"
                            required
                        >
                    </div>

                    <div>
                        <label for="pembahas_weight_bottom" class="block text-sm font-medium text-purple-700 mb-1">Pembahas (PMB) %</label>
                        <input
                            type="number"
                            name="pembahas_weight"
                            id="pembahas_weight_bottom"
                            value="{{ old('pembahas_weight', $seminarJenis->pembahas_weight ?? 30) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full px-3 py-2 border-2 border-purple-300 rounded-md focus:border-purple-500 focus:ring-2 focus:ring-purple-200"
                            placeholder="30"
                            required
                        >
                    </div>
                </div>

                <div class="mt-4 p-3 bg-white rounded-lg border border-indigo-300">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Bobot:</span>
                        <span id="total-weight-bottom" class="text-lg font-bold text-indigo-600">100%</span>
                    </div>
                    <p id="weight-warning-bottom" class="text-xs text-red-600 mt-1 hidden">Total harus 100%</p>
                </div>
            </div>

            <!-- Grading Scheme Section -->
            <div class="bg-gradient-to-r from-green-50 to-teal-50 border-2 border-green-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Skema Penilaian Huruf</h3>
                <p class="text-sm text-gray-600 mb-4">Tentukan batas nilai untuk setiap huruf mutu. Sistem akan melakukan pembulatan otomatis (contoh: 75.5 → 76 = A, 75.4 → 75 = B+)</p>

                <div class="space-y-3" id="grading-scheme-container">
                    @php
                        $defaultScheme = [
                            ['min' => 76, 'max' => 100, 'grade' => 'A'],
                            ['min' => 71, 'max' => 75.99, 'grade' => 'B+'],
                            ['min' => 66, 'max' => 70.99, 'grade' => 'B'],
                            ['min' => 61, 'max' => 65.99, 'grade' => 'C+'],
                            ['min' => 56, 'max' => 60.99, 'grade' => 'C'],
                            ['min' => 50, 'max' => 55.99, 'grade' => 'D'],
                            ['min' => 0, 'max' => 49.99, 'grade' => 'E'],
                        ];
                        $gradingScheme = old('grading_scheme', $seminarJenis->grading_scheme ?? $defaultScheme);
                    @endphp

                    @foreach($gradingScheme as $index => $grade)
                    <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-green-300">
                        <div class="w-16">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                            <input
                                type="text"
                                name="grading_scheme[{{ $index }}][grade]"
                                value="{{ $grade['grade'] }}"
                                class="w-full px-2 py-1 text-center border border-gray-300 rounded font-bold text-lg"
                                placeholder="A"
                                required
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nilai Minimal</label>
                            <input
                                type="number"
                                name="grading_scheme[{{ $index }}][min]"
                                value="{{ $grade['min'] }}"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1 border border-gray-300 rounded"
                                placeholder="76"
                                required
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nilai Maksimal</label>
                            <input
                                type="number"
                                name="grading_scheme[{{ $index }}][max]"
                                value="{{ $grade['max'] }}"
                                step="0.01"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1 border border-gray-300 rounded"
                                placeholder="100"
                                required
                            >
                        </div>
                        <button type="button" onclick="removeGradeRow(this)" class="text-red-600 hover:text-red-800 p-2 mt-5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addGradeRow()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    + Tambah Grade
                </button>

                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>Tips:</strong> Pastikan rentang nilai tidak tumpang tindih dan mencakup semua kemungkinan nilai (0-100).
                        Contoh standar: A (76-100), B+ (71-75.99), B (66-70.99), dst.
                    </p>
                </div>
            </div>

        <!-- Action Buttons at Bottom -->
        <div class="mt-6 flex items-center justify-between pt-6 border-t border-gray-200">
            <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button type="submit" class="btn-pill btn-pill-primary">
                Perbarui Jenis Seminar
            </button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Ubah Aspek Penilaian</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="type" id="edit_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="input">Input Angka</option>
                            <option value="sum">Jumlah (Sum)</option>
                            <option value="prev_avg">Rata-rata (Avg)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <input type="text" name="category" id="edit_category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="edit_nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                    <label for="edit_urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="edit_urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>


                <!-- Dynamic Choice for Sum/Avg in Modal -->
                <div id="related_aspects_container_edit" class="hidden bg-gray-50 border border-blue-200 rounded-lg p-4 mt-2 mb-4">
                    <label class="block text-sm font-bold text-blue-800 mb-2 font-mono text-[10px] uppercase">Pilih Aspek Komponen (Input):</label>
                    <div id="aspect_list_edit" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <!-- Checkboxes inserted here -->
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition-all">
                        Kembali
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
(function() {
    // 1. Core State & Helpers
    const STORAGE_KEY = 'seminarJenis_edit_{{ $seminarJenis->id }}';
    let gradeIndex = {{ count($gradingScheme) }};
    let berkasKeyCounter = 0;
    let saveTimeout;

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"}[m]));
    }

    // Shared scope: accessible by init AND window.editAspect
    let allAspectsData = {};
    try {
        const el = document.getElementById('aspects-data');
        if (el) allAspectsData = JSON.parse(el.textContent || '{}');
    } catch (e) { console.error('Aspects Data Error', e); }

    function updateAspectList(evaluatorType, containerId, listId, selectedIds) {
        selectedIds = selectedIds || [];
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        const typeSelect = containerId.includes('add') ? document.getElementById('type_add') : document.getElementById('edit_type');
        if (!container || !list || !typeSelect) return;

        const typeVal = typeSelect.value;

        if (typeVal === 'input') {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');

        if (!evaluatorType) {
            list.innerHTML = '<p class="text-xs text-red-500 italic p-2 bg-red-50 border border-red-200 rounded">Silakan pilih Penilai terlebih dahulu.</p>';
            return;
        }

        let data = allAspectsData[evaluatorType] || [];
        if (containerId.includes('edit') && window._editingAspect) {
            data = data.filter(function(a) { return a.id !== window._editingAspect.id; });
        }

        list.innerHTML = '';
        if (data.length === 0) {
            list.innerHTML = '<p class="text-xs text-gray-500 italic p-2 md:col-span-3">Belum ada aspek input lainnya.</p>';
        } else {
            data.forEach(function(aspect) {
                const isChecked = selectedIds.includes(aspect.id) || selectedIds.includes(String(aspect.id));
                const isInput = aspect.type === 'input';
                const badge = isInput
                    ? '<span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded">Input</span>'
                    : '<span class="text-[10px] bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded">' + (aspect.type === 'sum' ? 'Sum' : 'Avg') + '</span>';
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded border hover:border-gray-200 group';
                div.innerHTML = '<input type="checkbox" name="related_aspects[]" value="' + aspect.id + '" id="rel_' + containerId + '_' + aspect.id + '" ' + (isChecked ? 'checked' : '') + ' class="rounded border-gray-300 text-blue-600">'
                    + '<label for="rel_' + containerId + '_' + aspect.id + '" class="text-sm text-gray-700 flex-1 flex items-center justify-between ml-2">'
                    + '<span class="truncate mr-2" title="' + aspect.nama_aspek + '">' + aspect.nama_aspek + '</span>' + badge + '</label>';
                list.appendChild(div);
            });
        }
    }

    // 2. Main Logic Functions
    function syncToWeightsForm() {
        const ids = {
            'weight_nama': 'nama',
            'weight_kode': 'kode',
            'weight_keterangan': 'keterangan',
            'weight_syarat_seminar': 'syarat_seminar'
        };
        for (const [targetId, sourceId] of Object.entries(ids)) {
            const target = document.getElementById(targetId);
            const source = document.getElementById(sourceId);
            if (target && source) target.value = source.value;
        }
    }

    function saveFormData() {
        const data = {
            nama: document.getElementById('nama')?.value || '',
            kode: document.getElementById('kode')?.value || '',
            keterangan: document.getElementById('keterangan')?.value || '',
            timestamp: Date.now()
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        syncToWeightsForm();
    }

    function updateTotalWeightBottom() {
        const v1 = parseFloat(document.getElementById('p1_weight_bottom')?.value) || 0;
        const v2 = parseFloat(document.getElementById('p2_weight_bottom')?.value) || 0;
        const vm = parseFloat(document.getElementById('pembahas_weight_bottom')?.value) || 0;
        const total = v1 + v2 + vm;

        const el = document.getElementById('total-weight-bottom');
        const warn = document.getElementById('weight-warning-bottom');
        if (el) {
            el.textContent = total.toFixed(2) + '%';
            if (Math.abs(total - 100) < 0.01) {
                el.classList.replace('text-red-600', 'text-green-600');
                warn?.classList.add('hidden');
            } else {
                el.classList.replace('text-green-600', 'text-red-600');
                warn?.classList.remove('hidden');
            }
        }
    }

    function addBerkasRow(data = {}) {
        const container = document.getElementById('berkas-items-body');
        if (!container) return;
        
        // Re-use counter or fieldIndex
        const idx = berkasKeyCounter++; // Using existing counter from scope
        const row = document.createElement('tr');
        row.className = 'field-row hover:bg-gray-50 transition-colors';
        
        // Def values
        const label = data.label || '';
        const key = data.key || '';
        const type = data.type || 'text';
        const placeholder = data.placeholder || '';
        const required = data.required !== false; // Default true
        const options = data.options || ''; 
        const ext = data.extensions || ''; 
        const maxKb = data.max_kb || ''; 

        // Helper to create the type select logic
        function createTypeSelect(i, selectedValue) {
            const types = [
                {val: 'text', label: 'Text'},
                {val: 'textarea', label: 'Textarea'},
                {val: 'number', label: 'Number'},
                {val: 'email', label: 'Email'},
                {val: 'date', label: 'Tanggal (Date)'},
                {val: 'select', label: 'Dropdown (Select)'},
                {val: 'radio', label: 'Radio Button'},
                {val: 'checkbox', label: 'Checklist (Checkbox)'},
                {val: 'file', label: 'File Upload'},
            ];
            
            let html = `<select name="berkas_syarat_items[${i}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">`;
            types.forEach(function(t) {
                html += `<option value="${t.val}" ${t.val === selectedValue ? 'selected' : ''}>${t.label}</option>`;
            });
            html += `</select>`;
            return html;
        }

        row.innerHTML = `
            <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                <i class="fas fa-grip-vertical"></i>
            </td>
            <td class="px-4 py-3 align-top">
                <input name="berkas_syarat_items[${idx}][label]" value="${escapeHtml(label)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Surat Pengantar" required>
            </td>
            <td class="px-4 py-3 align-top">
                <input name="berkas_syarat_items[${idx}][key]" value="${escapeHtml(key)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="snake_case" required>
                <div class="text-xs text-gray-400 mt-1">Gunakan huruf kecil & operator _</div>
            </td>
            <td class="px-4 py-3 align-top">
                ${createTypeSelect(idx, type)}
            </td>
            <td class="px-4 py-3 align-top">
                <input name="berkas_syarat_items[${idx}][placeholder]" value="${escapeHtml(placeholder)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
            </td>
            <td class="px-4 py-3 align-top">
                <div class="space-y-2 field-config-wrap">
                    <!-- Options for Select/Radio/Checkbox -->
                    <div class="options-wrap hidden">
                        <textarea name="berkas_syarat_items[${idx}][options]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs font-mono" placeholder="value|Label">${escapeHtml(options)}</textarea>
                    </div>
                    <!-- Config for File -->
                    <div class="file-wrap hidden">
                        <div class="grid grid-cols-1 gap-2">
                            <input name="berkas_syarat_items[${idx}][extensions]" value="${escapeHtml(ext)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg">
                            <input type="number" min="0" name="berkas_syarat_items[${idx}][max_kb]" value="${escapeHtml(maxKb)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB">
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 align-top text-center">
                <input type="checkbox" name="berkas_syarat_items[${idx}][required]" value="1" ${required ? 'checked' : ''} class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            </td>
            <td class="px-4 py-3 align-top text-center relative">
                <button type="button" class="remove-field text-gray-400 hover:text-red-600 transition-colors p-2" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        
        // Attach Config Logic
        const typeSelect = row.querySelector('.field-type');
        const configWrap = row.querySelector('.field-config-wrap');
        const optionsWrap = configWrap.querySelector('.options-wrap');
        const fileWrap = configWrap.querySelector('.file-wrap');

        function updateConfigVisibility() {
            const val = typeSelect.value;
            if (['select', 'radio', 'checkbox'].includes(val)) {
                optionsWrap.classList.remove('hidden');
                fileWrap.classList.add('hidden');
            } else if (val === 'file') {
                optionsWrap.classList.add('hidden');
                fileWrap.classList.remove('hidden');
            } else {
                optionsWrap.classList.add('hidden');
                fileWrap.classList.add('hidden');
            }
        }
        typeSelect.addEventListener('change', updateConfigVisibility);
        updateConfigVisibility();

        // Remove Handler
        row.querySelector('.remove-field').addEventListener('click', function() {
            row.remove();
            if (container.querySelectorAll('.field-row').length === 0) {
                 document.getElementById('no-fields-row').style.display = 'table-row';
            }
        });

        // No native drag setup needed for SortableJS

        container.appendChild(row);
        document.getElementById('no-fields-row').style.display = 'none';
    }

    // 3. Initialization Function
    function initSeminarJenisEdit() {
        const root = document.getElementById('grading-scheme-container');
        if (!root || root.dataset.initialized === 'true') return;
        
        // Weights & Auto-save
        @if(!$errors->any())
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    if ((Date.now() - data.timestamp) / (3600000) < 24) {
                        if (document.getElementById('nama')) document.getElementById('nama').value = data.nama || '';
                        if (document.getElementById('kode')) document.getElementById('kode').value = data.kode || '';
                        if (document.getElementById('keterangan')) document.getElementById('keterangan').value = data.keterangan || '';
                    }
                } catch(e) {}
            }
        @endif
        
        syncToWeightsForm();
        ['nama', 'kode', 'keterangan', 'syarat_seminar'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveFormData, 1000);
            });
        });

        const p1B = document.getElementById('p1_weight_bottom');
        if (p1B) {
            p1B.addEventListener('input', updateTotalWeightBottom);
            document.getElementById('p2_weight_bottom')?.addEventListener('input', updateTotalWeightBottom);
            document.getElementById('pembahas_weight_bottom')?.addEventListener('input', updateTotalWeightBottom);
            updateTotalWeightBottom();
        }

        // Berkas Items
        const addB = document.getElementById('add-berkas-item');
        const berkasContainer = document.getElementById('berkas-items-body');

        if (addB && berkasContainer) {
            addB.addEventListener('click', () => addBerkasRow({}));
            
            const exEl = document.getElementById('existing-berkas-items');
            if (exEl) {
                try {
                    let ex = JSON.parse(exEl.textContent || '[]');
                    // Normalize object to array if needed (though json_encode usually handles this)
                    if (typeof ex === 'object' && !Array.isArray(ex)) ex = Object.values(ex);
                    
                    if (ex.length) {
                        ex.forEach(it => {
                            // If coming from DB/JSON, key names might match what we save
                            // DB stores: key, label, extensions, max_size_kb, required, type, options
                            addBerkasRow({
                                key: it.key,
                                label: it.label,
                                type: it.type || 'text',
                                placeholder: it.placeholder || '',
                                required: it.required !== false,
                                extensions: Array.isArray(it.extensions) ? it.extensions.join(', ') : (it.extensions || ''),
                                max_kb: it.max_size_kb || '',
                                options: it.options || ''
                            });
                        });
                    } else {
                         // document.getElementById('no-fields-row').style.display = 'table-row';
                         // Optionally add one empty row or just leave blank
                         addBerkasRow({});
                    }
                } catch(e) { 
                    console.error("Error parsing existing items", e);
                    addBerkasRow({}); 
                }
            } else {
                 if (berkasContainer.querySelectorAll('.field-row').length === 0) {
                     addBerkasRow({});
                 }
            }

            // Init SortableJS
            if (typeof Sortable !== 'undefined') {
                Sortable.create(berkasContainer, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'bg-blue-50'
                });
            } else {
                console.warn('SortableJS not loaded');
            }
        }

        // 3. Aspect Event Listeners (functions defined in outer IIFE scope)
        const _typeAddInput = document.getElementById('type_add');
        const _evalAddInput = document.getElementById('evaluator_type_add');
        if (_typeAddInput && _evalAddInput) {
            const handleAddUpdate = function() { updateAspectList(_evalAddInput.value, 'related_aspects_container_add', 'aspect_list_add', []); };
            _typeAddInput.addEventListener('change', handleAddUpdate);
            _evalAddInput.addEventListener('change', handleAddUpdate);
            handleAddUpdate(); // initial state check
        }

        const _typeEditInput = document.getElementById('edit_type');
        if (_typeEditInput) {
            _typeEditInput.addEventListener('change', function() {
                const asp = window._editingAspect;
                if (!asp) return;
                updateAspectList(asp.evaluator_type, 'related_aspects_container_edit', 'aspect_list_edit', asp.related_aspects || []);
            });
        }

        root.dataset.initialized = 'true';
    }

    // 4. Modal & Grading Helpers (Global for onclick)
    window.editAspect = function(aspect) {
        window._editingAspect = aspect;
        document.getElementById('edit_nama_aspek').value = aspect.nama_aspek;
        // document.getElementById('edit_persentase').value = aspect.persentase || 0; // Removed
        document.getElementById('edit_urutan').value = aspect.urutan;
        document.getElementById('edit_type').value = aspect.type || 'input';
        document.getElementById('edit_category').value = aspect.category || '';

        // Trigger UI update logic manually
        const containerId = 'related_aspects_container_edit';
        const listId = 'aspect_list_edit';

        if (aspect.type !== 'input') {
            document.getElementById(containerId).classList.remove('hidden');
            // Populate list
             updateAspectList(aspect.evaluator_type, containerId, listId, aspect.related_aspects || []);
        } else {
             document.getElementById(containerId).classList.add('hidden');
        }
        
        document.getElementById('editForm').action = "{{ route('admin.seminarjenis.aspects.update', [$seminarJenis, '__ID__']) }}".replace('__ID__', aspect.id);
        document.getElementById('editModal').classList.remove('hidden');
    };
    window.closeEditModal = function() { document.getElementById('editModal').classList.add('hidden'); };
    window.addGradeRow = function() {
        const c = document.getElementById('grading-scheme-container');
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 bg-white p-3 rounded-lg border border-green-300';
        row.innerHTML = `
            <div class="w-16"><label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                <input type="text" name="grading_scheme[${gradeIndex}][grade]" class="w-full px-2 py-1 text-center border border-gray-300 rounded font-bold text-lg" required></div>
            <div class="flex-1"><label class="block text-xs font-medium text-gray-600 mb-1">Nilai Min</label>
                <input type="number" name="grading_scheme[${gradeIndex}][min]" step="0.01" class="w-full px-3 py-1 border border-gray-300 rounded" required></div>
            <div class="flex-1"><label class="block text-xs font-medium text-gray-600 mb-1">Nilai Max</label>
                <input type="number" name="grading_scheme[${gradeIndex}][max]" step="0.01" class="w-full px-3 py-1 border border-gray-300 rounded" required></div>
            <button type="button" onclick="window.removeGradeRow(this)" class="text-red-600 p-2 mt-5">Hapus</button>
        `;
        c.appendChild(row);
        gradeIndex++;
    };
    window.removeGradeRow = function(btn) {
        if (document.querySelectorAll('#grading-scheme-container > div').length > 1) btn.closest('.flex').remove();
        else alert('Minimal 1 grade!');
    };

    // 5. Run Pattern
    if (document.readyState !== 'loading') initSeminarJenisEdit();
    else document.addEventListener('DOMContentLoaded', initSeminarJenisEdit);
    window.addEventListener('app:init', initSeminarJenisEdit);
    window.addEventListener('page-loaded', initSeminarJenisEdit);
})();
</script>
@endpush
