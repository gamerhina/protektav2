@extends('layouts.mahasiswa')

@section('title', 'Edit Pendaftaran Seminar')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit Pendaftaran</h1>
                <p class="mt-2 text-sm text-gray-500 font-medium">Lengkapi atau perbarui detail pendaftaran seminar Anda</p>
            </div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center text-sm font-semibold text-blue-600 hover:text-blue-500 transition-colors">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-red-50 p-4 border border-red-100 animate-shake">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="text-sm font-bold text-red-800">Mohon perbaiki kesalahan berikut:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc ps-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('mahasiswa.seminar.update', $seminar) }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-10">
            @csrf
            @method('PUT')

            <!-- Seminar Type Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Jenis Seminar</h2>
                            <p class="text-sm text-gray-500">Pilih kategori seminar yang diikuti</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="seminar_jenis_id" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Nama Seminar</label>
                        <div class="relative">
                            <select name="seminar_jenis_id" id="seminar_jenis_id" required 
                                class="block w-full px-4 py-4 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-blue-500 transition-all appearance-none font-medium">
                                <option value="" disabled>Pilih Jenis Seminar</option>
                                @foreach($seminarJenis as $jenis)
                                    <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id', $seminar->seminar_jenis_id) == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seminar Details Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-info-circle text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Detail Seminar</h2>
                            <p class="text-sm text-gray-500">Lengkapi informasi pelaksanaan seminar</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2">
                        <div class="sm:col-span-2 space-y-2">
                            <label for="judul" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Judul Seminar</label>
                            <x-tinymce-editor
                                name="judul"
                                id="judul"
                                :content="old('judul', $seminar->judul)"
                                placeholder="Masukkan Judul Seminar..."
                                :has-header="false"
                                height="200"
                            />
                        </div>
                        <div class="space-y-2">
                            <label for="tanggal" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Tanggal Pelaksanaan</label>
                            <div class="relative">
                                <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal', $seminar->tanggal ? $seminar->tanggal->format('Y-m-d') : '') }}"
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="waktu_mulai" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Waktu Mulai</label>
                            <div class="relative">
                                <input type="time" name="waktu_mulai" id="waktu_mulai" required value="{{ old('waktu_mulai', $seminar->waktu_mulai ? \Carbon\Carbon::parse($seminar->waktu_mulai)->format('H:i') : '') }}"
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                            </div>
                        </div>

                        <div class="sm:col-span-2 space-y-2">
                            <label for="lokasi" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Lokasi/Ruangan</label>
                            <div class="relative">
                                <input type="text" name="lokasi" id="lokasi" required placeholder="Contoh: Ruang Sidang Utama atau Zoom Meeting" value="{{ old('lokasi', $seminar->lokasi) }}"
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all font-medium ps-11">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adcisors Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-user-tie text-emerald-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Pembimbing & Pembahas</h2>
                            <p class="text-sm text-gray-500">Pilih dosen pendamping yang bertugas</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="p1_dosen_id" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Pembimbing 1</label>
                            <div class="relative">
                                <select name="p1_dosen_id" id="p1_dosen_id" required 
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all appearance-none font-medium ps-11 dosen-select-toggle">
                                    <option value="">Pilih Pembimbing 1</option>
                                    @foreach($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('p1_dosen_id', $seminar->p1_dosen_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama }}</option>
                                    @endforeach
                                    <option value="manual" {{ old('p1_dosen_id', $seminar->p1_dosen_id ?? ($seminar->p1_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user-check text-gray-400"></i>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <div id="p1_manual_fields" class="{{ old('p1_dosen_id', $seminar->p1_dosen_id ?? ($seminar->p1_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100 mt-3">
                                <input type="text" name="p1_nama" value="{{ old('p1_nama', $seminar->p1_nama) }}" placeholder="Nama Pembimbing 1" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all mb-2">
                                <input type="text" name="p1_nip" value="{{ old('p1_nip', $seminar->p1_nip) }}" placeholder="NIP Pembimbing 1" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="p2_dosen_id" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Pembimbing 2</label>
                            <div class="relative">
                                <select name="p2_dosen_id" id="p2_dosen_id"
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all appearance-none font-medium ps-11 dosen-select-toggle">
                                    <option value="">Pilih Pembimbing 2 (Opsional)</option>
                                    @foreach($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('p2_dosen_id', $seminar->p2_dosen_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama }}</option>
                                    @endforeach
                                    <option value="manual" {{ old('p2_dosen_id', $seminar->p2_dosen_id ?? ($seminar->p2_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user-check text-gray-400 opacity-60"></i>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <div id="p2_manual_fields" class="{{ old('p2_dosen_id', $seminar->p2_dosen_id ?? ($seminar->p2_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100 mt-3">
                                <input type="text" name="p2_nama" value="{{ old('p2_nama', $seminar->p2_nama) }}" placeholder="Nama Pembimbing 2" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all mb-2">
                                <input type="text" name="p2_nip" value="{{ old('p2_nip', $seminar->p2_nip) }}" placeholder="NIP Pembimbing 2" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all">
                            </div>
                        </div>

                        <div class="sm:col-span-2 space-y-2">
                            <label for="pembahas_dosen_id" class="block text-sm font-bold text-gray-700 uppercase tracking-wider ml-1">Dosen Pembahas</label>
                            <div class="relative">
                                <select name="pembahas_dosen_id" id="pembahas_dosen_id"
                                    class="block w-full px-4 py-3 bg-gray-50 border-0 rounded-2xl text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all appearance-none font-medium ps-11 dosen-select-toggle">
                                    <option value="">Pilih Pembahas (Opsional)</option>
                                    @foreach($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama }}</option>
                                    @endforeach
                                    <option value="manual" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id ?? ($seminar->pembahas_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user-tag text-gray-400"></i>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <div id="pembahas_manual_fields" class="{{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id ?? ($seminar->pembahas_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100 mt-3">
                                <input type="text" name="pembahas_nama" value="{{ old('pembahas_nama', $seminar->pembahas_nama) }}" placeholder="Nama Pembahas" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all mb-2">
                                <input type="text" name="pembahas_nip" value="{{ old('pembahas_nip', $seminar->pembahas_nip) }}" placeholder="NIP Pembahas" class="w-full px-4 py-3 text-sm border-0 bg-white rounded-xl focus:ring-2 focus:ring-emerald-500 shadow-sm transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Berkas Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-file-alt text-amber-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Berkas Persyaratan</h2>
                            <p class="text-sm text-gray-500">Unggah dokumen yang diperlukan (Maksimal 5MB per file)</p>
                        </div>
                    </div>

                    @php
                        $jenisData = $seminar->seminarJenis;
                        $items = [];
                        if ($jenisData && is_array($jenisData->berkas_syarat_items)) {
                            $items = $jenisData->berkas_syarat_items;
                        }
                        $existingBerkas = $seminar->berkas_syarat;
                    @endphp

                    @if(count($items) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($items as $item)
                                @php
                                    $key = $item['key'] ?? null;
                                    $label = $item['label'] ?? null;
                                    $required = $item['required'] ?? true;
                                    if (!$key || !$label) continue;
                                    $existingValue = is_array($existingBerkas) ? ($existingBerkas[$key] ?? null) : null;

                                     $type = $item['type'] ?? null;
                                     if (!$type) {
                                         $keyLower = strtolower($key);
                                         $fileKeywords = ['file', 'berkas', 'scan', 'upload', 'dokumen', 'transkrip', 'krs', 'ktm', 'sertifikat', 'surat', 'abstrak', 'poster', 'artikel', 'lembar', 'bukti', 'kartu'];
                                         
                                         $isLikelyFile = false;
                                         foreach ($fileKeywords as $kw) {
                                             if (strpos($keyLower, $kw) !== false) {
                                                 $isLikelyFile = true;
                                                 break;
                                             }
                                         }
                                         
                                         if (isset($item['extensions']) && !empty($item['extensions'])) {
                                             $isLikelyFile = true;
                                         }

                                         $type = $isLikelyFile ? 'file' : 'text';
                                         
                                         // Safe date inference: avoid converting text fields like "tempat lahir"
                                         if ($type !== 'file' && (strpos($keyLower, 'tgl') !== false || strpos($keyLower, 'tanggal') !== false)) {
                                             if (strpos($keyLower, 'tempat') === false) {
                                                 $type = 'date';
                                             }
                                         }
                                     }

                                    $options = $item['options'] ?? [];
                                    $placeholder = $item['placeholder'] ?? '';
                                    if (is_string($options) && str_contains($options, "\n")) {
                                        $options = explode("\n", $options);
                                        $options = array_map('trim', $options);
                                    }
                                @endphp

                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 transition-all hover:bg-gray-100/50">
                                    <div class="mb-3 flex items-center justify-between">
                                        <label class="text-sm font-bold text-gray-700 uppercase tracking-wider">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                                        @if($existingValue && $type === 'file')
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-tighter">Terunggah</span>
                                        @elseif($existingValue)
                                             <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-tighter">Terisi</span>
                                        @endif
                                    </div>

                                    <div class="space-y-3">
                                        @if($type === 'file')
                                            {{-- File Input --}}
                                            <div class="relative group">
                                                <input type="file" name="berkas_syarat_items[{{ $key }}]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" @if($required && !$existingValue) required @endif>
                                            </div>
                                            @if($existingValue)
                                                <div class="flex items-center gap-2 mt-2">
                                                    <a href="{{ route('mahasiswa.seminar.files.show', $existingValue) }}" target="_blank" class="inline-flex items-center text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-white px-3 py-1.5 rounded-xl border border-blue-100 shadow-sm transition-all">
                                                        <i class="fas fa-external-link-alt me-1.5"></i>Lihat Berkas Saat Ini
                                                    </a>
                                                </div>
                                            @endif
                                        @elseif($type === 'select')
                                            {{-- Select Input --}}
                                            <div class="relative">
                                                <select name="berkas_syarat_items[{{ $key }}]" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" {{ $required ? 'required' : '' }}>
                                                    <option value="">Pilih {{ $label }}</option>
                                                    @foreach($options as $opt)
                                                        <option value="{{ $opt }}" {{ $existingValue == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif($type === 'textarea')
                                            {{-- Textarea Input --}}
                                            <textarea name="berkas_syarat_items[{{ $key }}]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="{{ $placeholder ?: 'Masukkan ' . $label }}" {{ $required ? 'required' : '' }}>{{ $existingValue }}</textarea>
                                        @elseif($type === 'date')
                                            {{-- Date Input --}}
                                            <div class="relative">
                                                <input type="date" name="berkas_syarat_items[{{ $key }}]" value="{{ $existingValue }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" {{ $required ? 'required' : '' }}>
                                            </div>
                                        @elseif($type === 'checkbox')
                                            {{-- Checkbox Input --}}
                                            @foreach($options as $opt)
                                                <label class="inline-flex items-center mr-4 mb-2">
                                                    <input type="checkbox" name="berkas_syarat_items[{{ $key }}][]" value="{{ $opt }}" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded" {{ is_array($existingValue) && in_array($opt, $existingValue) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            {{-- Text/Number/Email Input --}}
                                            <div class="relative">
                                                <input type="{{ $type }}" name="berkas_syarat_items[{{ $key }}]" value="{{ $existingValue }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="{{ $placeholder ?: 'Masukkan ' . $label }}" {{ $required ? 'required' : '' }}>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
                    <i class="fas fa-save me-2"></i>Update Seminar
                </button>
                
                <button type="button" class="btn-pill btn-pill-danger w-full sm:w-auto" onclick="if(confirm('Apakah Anda yakin ingin membatalkan seminar ini?')) document.getElementById('cancel-form').submit();">
                    <i class="fas fa-times-circle me-2"></i>Kembali
                </button>
            </div>
        </form>

        <div class="mt-4 mb-10" id="discussion">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                            <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan Admin / Dosen</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <!-- Comments List -->
                    <div class="space-y-4 mb-6 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($seminar->comments as $comment)
                            <div class="flex gap-3 {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user()) ? 'flex-row-reverse' : '' }}">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm
                                        {{ $comment->user_type === 'App\Models\Admin' ? 'bg-indigo-100 text-indigo-600' : 
                                          ($comment->user_type === 'App\Models\Dosen' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600') }}">
                                        {{ substr($comment->user->nama ?? 'U', 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex flex-col max-w-[85%] {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user()) ? 'items-end' : 'items-start' }}">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-[10px] font-bold text-gray-900">{{ $comment->user->nama ?? 'Unknown' }}</span>
                                        <span class="text-gray-400 text-[9px]">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-xl text-xs leading-normal shadow-sm border
                                        {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user())
                                            ? 'bg-blue-600 text-white border-blue-600 rounded-tr-none' 
                                            : 'bg-white text-gray-700 border-gray-100 rounded-tl-none' }}">
                                        {!! nl2br(e($comment->message)) !!}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <i class="fas fa-comment-slash text-gray-200 text-2xl mb-2"></i>
                                <p class="text-[11px] text-gray-400">Belum ada diskusi.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Comment Form -->
                    <form action="{{ route('mahasiswa.seminar.comment.store', $seminar) }}" method="POST">
                        @csrf
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <textarea name="message" rows="1" required
                                    class="w-full pl-3 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none resize-none"
                                    placeholder="Tulis pesan..."></textarea>
                            </div>
                            <button type="submit" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex-shrink-0 self-end">
                                <i class="fas fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form id="cancel-form" action="{{ route('mahasiswa.seminar.cancel', $seminar) }}" method="POST" class="hidden">
            @csrf
            @method('PUT')
        </form>
    </div>
</div>

<script type="application/json" id="seminar-jenis-evaluator-rules">
    {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
        'p1_required' => (bool) ($j->p1_required ?? true),
        'p2_required' => (bool) ($j->p2_required ?? true),
        'pembahas_required' => (bool) ($j->pembahas_required ?? true),
    ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<script>
    (function () {
        function initSeminarEdit() {
            const select = document.getElementById('seminar_jenis_id');
            const rulesEl = document.getElementById('seminar-jenis-evaluator-rules');

            const p1 = document.getElementById('p1_dosen_id');
            const p2 = document.getElementById('p2_dosen_id');
            const pembahas = document.getElementById('pembahas_dosen_id');

            if (!select || !rulesEl || !p1 || !p2 || !pembahas) return;
            if (select.dataset.initialized === 'true') return;

            let rules = {};
            try {
                rules = JSON.parse(rulesEl.textContent || '{}') || {};
            } catch (e) {
                rules = {};
            }

            const setReq = (el, required) => {
                if (required) {
                    el.setAttribute('required', 'required');
                } else {
                    el.removeAttribute('required');
                }
            };

            const apply = () => {
                const id = select.value;
                const r = id && rules[id] ? rules[id] : null;

                setReq(p1, r ? !!r.p1_required : true);
                setReq(p2, r ? !!r.p2_required : true);
                setReq(pembahas, r ? !!r.pembahas_required : true);
            };

            select.addEventListener('change', apply);
            apply();
            
            // Manual Dosen Toggle
            document.querySelectorAll('.dosen-select-toggle').forEach(selectEl => {
                selectEl.addEventListener('change', function() {
                    const targetId = this.id.replace('_dosen_id', '_manual_fields');
                    const target = document.getElementById(targetId);
                    if (target) {
                        if (this.value === 'manual') {
                            target.classList.remove('hidden');
                        } else {
                            target.classList.add('hidden');
                        }
                    }
                });
            });
            
            select.dataset.initialized = 'true';
        }

        window.addEventListener('app:init', initSeminarEdit);
        window.addEventListener('page-loaded', initSeminarEdit);
        if (document.readyState !== 'loading') initSeminarEdit();
        else document.addEventListener('DOMContentLoaded', initSeminarEdit);
    })();
</script>
@vite('resources/js/signature-pad.js')
@endsection