@extends('layouts.app')

@section('title', 'Ubah Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl p-8 space-y-8 border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="text-3xl font-semibold text-gray-800">Ubah Seminar</h1>
                <p class="text-sm text-gray-500">Perbarui informasi seminar secara lengkap</p>
            </div>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <a href="{{ route('admin.seminar.index') }}" class="btn-pill btn-pill-secondary">
                    Kembali
                </a>
                @if($seminar->status == 'diajukan')
                    <button type="submit" form="approve-form" class="btn-pill btn-pill-success" onclick="return confirm('Setujui pengajuan seminar ini?')">
                        Setujui
                    </button>
                    <button type="submit" form="reject-form" class="btn-pill btn-pill-danger" onclick="return confirm('Tolak pengajuan seminar ini?')">
                        Tolak
                    </button>
                @endif
                <button type="submit" form="edit-seminar-form" class="btn-pill btn-pill-primary">
                    Simpan Seminar
                </button>
            </div>
        </div>

        @php
            $berkasSyarat = null;
            if($seminar->berkas_syarat) {
                if(is_array($seminar->berkas_syarat)) {
                    $berkasSyarat = $seminar->berkas_syarat;
                } else {
                    $berkasSyarat = json_decode($seminar->berkas_syarat, true);
                }
            }
        @endphp

        <form id="edit-seminar-form" method="POST" action="{{ route('admin.seminar.update', $seminar->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mahasiswa</label>
                    <input 
                        type="text" 
                        value="{{ $seminar->mahasiswa->nama ?? 'N/A' }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600"
                        readonly
                    />
                </div>

                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select name="seminar_jenis_id" id="seminar_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror" required>
                        <option value="">Pilih Jenis Seminar</option>
                        @foreach($seminarJenis as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id', $seminar->seminar_jenis_id) == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('seminar_jenis_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="no_surat" class="block text-sm font-medium text-gray-700 mb-1">No. Surat</label>
                    <input 
                        type="text" 
                        name="no_surat" 
                        id="no_surat" 
                        value="{{ old('no_surat', $seminar->no_surat) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('no_surat') border-red-500 @enderror"
                        required
                    />
                    @error('no_surat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 space-y-2">
                    <label for="judul" class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest ml-1">Judul Seminar</label>
                    <x-tinymce-editor
                        name="judul"
                        id="judul"
                        :content="old('judul', $seminar->judul)"
                        placeholder="Masukkan Judul Seminar..."
                        :has-header="false"
                        height="200"
                    />
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            value="{{ old('tanggal', $seminar->tanggal->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('tanggal') border-red-500 @enderror"
                            required
                            oninput="this.parentElement.nextElementSibling?.previousElementSibling?.querySelector('.tanggal-indo')?.remove(); var p = document.createElement('p'); p.className='tanggal-indo text-xs text-blue-600 font-semibold mt-1'; p.textContent = this.value ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(this.value)) : ''; this.parentElement.appendChild(p);"
                        />
                    </div>
                    <p class="tanggal-indo text-xs text-blue-600 font-semibold mt-1">{{ \Carbon\Carbon::parse(old('tanggal', $seminar->tanggal->format('Y-m-d')))->translatedFormat('d F Y') }}</p>
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="waktu_mulai" class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                    <div class="relative">
                        <input
                            type="time"
                            name="waktu_mulai"
                            id="waktu_mulai"
                            value="{{ old('waktu_mulai', $seminar->waktu_mulai ? \Carbon\Carbon::parse($seminar->waktu_mulai)->format('H:i') : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('waktu_mulai') border-red-500 @enderror"
                            required
                        />
                    </div>
                    @error('waktu_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input 
                        type="text" 
                        name="lokasi" 
                        id="lokasi" 
                        value="{{ old('lokasi', $seminar->lokasi) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('lokasi') border-red-500 @enderror"
                        required
                    />
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1</label>
                    <div class="space-y-2">
                        <select name="p1_dosen_id" id="p1_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror dosen-select-toggle" required>
                            <option value="">Pilih Pembimbing 1</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p1_dosen_id', $seminar->p1_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p1_dosen_id', $seminar->p1_dosen_id === null && $seminar->p1_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p1_manual_fields" class="{{ (old('p1_dosen_id', $seminar->p1_dosen_id ? '' : ($seminar->p1_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p1_nama" value="{{ old('p1_nama', $seminar->p1_nama) }}" placeholder="Nama Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p1_nip" value="{{ old('p1_nip', $seminar->p1_nip) }}" placeholder="NIP Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p1_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p2_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2</label>
                    <div class="space-y-2">
                        <select name="p2_dosen_id" id="p2_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">(Opsional) Pilih Pembimbing 2</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p2_dosen_id', $seminar->p2_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p2_dosen_id', $seminar->p2_dosen_id === null && $seminar->p2_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p2_manual_fields" class="{{ (old('p2_dosen_id', $seminar->p2_dosen_id ? '' : ($seminar->p2_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p2_nama" value="{{ old('p2_nama', $seminar->p2_nama) }}" placeholder="Nama Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p2_nip" value="{{ old('p2_nip', $seminar->p2_nip) }}" placeholder="NIP Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p2_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pembahas_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembahas</label>
                    <div class="space-y-2">
                        <select name="pembahas_dosen_id" id="pembahas_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">(Opsional) Pilih Pembahas</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id === null && $seminar->pembahas_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="pembahas_manual_fields" class="{{ (old('pembahas_dosen_id', $seminar->pembahas_dosen_id ? '' : ($seminar->pembahas_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="pembahas_nama" value="{{ old('pembahas_nama', $seminar->pembahas_nama) }}" placeholder="Nama Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="pembahas_nip" value="{{ old('pembahas_nip', $seminar->pembahas_nip) }}" placeholder="NIP Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('pembahas_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('status') border-red-500 @enderror" required>
                        <option value="diajukan" {{ old('status', $seminar->status) == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="disetujui" {{ old('status', $seminar->status) == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ old('status', $seminar->status) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="belum_lengkap" {{ old('status', $seminar->status) == 'belum_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                        <option value="selesai" {{ old('status', $seminar->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 mt-8 pt-8 border-t border-gray-100" id="syarat-upload-container">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-file-upload text-blue-500"></i>
                        Berkas Persyaratan
                    </h2>

                    @php
                        // Get berkas_syarat_items for currently selected seminar jenis
                        $items = is_array($seminar->seminarJenis?->berkas_syarat_items) ? $seminar->seminarJenis->berkas_syarat_items : [];
                        $existing = is_array($berkasSyarat) ? $berkasSyarat : [];
                    @endphp

                    @if(is_array($items) && count($items))
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($items as $item)
                                @php
                                    if (!is_array($item)) continue;
                                    $key = $item['key'] ?? null;
                                    $label = $item['label'] ?? null;
                                    if (!$key || !$label) continue;

                                    $type = $item['type'] ?? null;
                                    if (!$type) {
                                        // Simple fallback: if extensions or file keywords are present, it's a file.
                                        $keyLower = strtolower($key);
                                        $isLikelyFile = false;
                                        $fileKeywords = ['file', 'berkas', 'scan', 'upload', 'dokumen', 'transkrip', 'krs', 'ktm', 'sertifikat', 'surat', 'abstrak', 'poster', 'artikel', 'lembar', 'bukti', 'kartu'];
                                        
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
                                        
                                        // ONLY infer date if it strictly matches date-related keywords and NOT text-related ones
                                        if ($type !== 'file' && (strpos($keyLower, 'tgl') !== false || strpos($keyLower, 'tanggal') !== false)) {
                                            // Check to avoid "Tempat Lahir" becoming a date
                                            if (strpos($keyLower, 'tempat') === false) {
                                                $type = 'date';
                                            }
                                        }
                                    }

                                    $options = $item['options'] ?? [];
                                    $placeholder = $item['placeholder'] ?? '';
                                    $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
                                    
                                    $existingValue = is_array($existing) ? ($existing[$key] ?? null) : null;
                                    
                                    // File specific config
                                    $itemExts = (isset($item['extensions']) && is_array($item['extensions']) && count($item['extensions'])) ? $item['extensions'] : ['pdf'];
                                    $itemAccept = implode(',', array_map(fn($e) => '.' . ltrim((string) $e, '.'), $itemExts));
                                    $itemMaxKb = (int) (($item['max_size_kb'] ?? null) ?: 5120);
                                    if ($itemMaxKb < 1) $itemMaxKb = 5120;
                                    $itemMaxMb = round(($itemMaxKb / 1024), 1);
                                @endphp

                                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-bold text-gray-800 truncate">{{ $label }}</h3>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                                {{ $required ? 'Wajib' : 'Opsional' }} 
                                                @if($type === 'file') • {{ strtoupper(implode(', ', $itemExts)) }} @endif
                                            </p>
                                        </div>
                                        @if($existingValue)
                                            <span class="flex-shrink-0 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                                {{ $type === 'file' ? 'TERUNGGAH' : 'TERISI' }}
                                            </span>
                                        @else
                                            <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">KOSONG</span>
                                        @endif
                                    </div>
                                    
                                    <div class="space-y-4">
                                        @if($type === 'file')
                                            {{-- File Input Handling --}}
                                            @if(is_string($existingValue) && $existingValue !== '')
                                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            <div class="bg-blue-600 text-white p-2 rounded-lg shadow-sm">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Nama File</p>
                                                                <p class="text-sm text-gray-700 truncate font-mono font-medium" title="{{ basename($existingValue) }}">{{ basename($existingValue) }}</p>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('admin.seminar.files.show', ['path' => $existingValue]) }}" target="_blank" class="flex-shrink-0 bg-white text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 p-2 rounded-lg transition-all shadow-sm" title="Lihat">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 hover:border-red-200 transition-all flex items-center justify-center gap-2" onclick="deleteBerkas('{{ $key }}')">
                                                    <i class="fas fa-trash-alt"></i> Hapus Berkas Saat Ini
                                                </button>
                                            @endif

                                            <div class="relative group/input">
                                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                                    {{ is_string($existingValue) && $existingValue !== '' ? 'Ganti Berkas' : 'Unggah Berkas' }}
                                                </label>
                                                <input
                                                    type="file"
                                                    name="berkas_syarat_items[{{ $key }}]"
                                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                                    accept="{{ $itemAccept }}"
                                                    {{ $required && !$existingValue ? 'required' : '' }}
                                                />
                                                <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimum ukuran file: <span class="font-bold text-gray-600">{{ $itemMaxMb }}MB</span></p>
                                            </div>
                                        @elseif($type === 'select')
                                            {{-- Select Input --}}
                                            <div class="relative">
                                                <select name="berkas_syarat_items[{{ $key }}]" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white" {{ $required ? 'required' : '' }}>
                                                    <option value="">{{ $placeholder ?: 'Pilih ' . $label }}</option>
                                                    @foreach($options as $opt)
                                                        <option value="{{ $opt }}" {{ $existingValue == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                                    <i class="fas fa-chevron-down text-xs"></i>
                                                </div>
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
                    @else
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center">
                            <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 font-medium">Tidak ada berkas persyaratan yang diperlukan untuk jenis seminar ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Nilai Seminar Section - Assessment Aspects -->
            <div class="mt-10 pt-10 border-t border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nilai Seminar</h2>
                
                <div class="mb-6 flex items-center gap-3 p-4 bg-blue-50 border border-blue-100 rounded-2xl text-blue-700">
                    <i class="fas fa-info-circle text-xl"></i>
                    <p class="text-sm font-medium">Catatan: Gunakan tanda titik <span class="font-bold">.</span> sebagai pemisah desimal (contoh: 85.50).</p>
                </div>
                
                @php
                    // Get assessment aspects for this seminar type
                    $assessmentAspects = $seminar->seminarJenis->assessmentAspects ?? collect();
                    $p1Aspects = $assessmentAspects->where('evaluator_type', 'p1');
                    $p2Aspects = $assessmentAspects->where('evaluator_type', 'p2');
                    $pembahasAspects = $assessmentAspects->where('evaluator_type', 'pembahas');
                    
                    // Get existing nilai records, memastikan sesuai dosen yang saat ini terdaftar di seminar
                    $p1Nilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'p1' && (int) $n->dosen_id === (int) $seminar->p1_dosen_id;
                    });

                    $p2Nilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'p2' && (int) $n->dosen_id === (int) $seminar->p2_dosen_id;
                    });

                    $pembahasNilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'pembahas' && (int) $n->dosen_id === (int) $seminar->pembahas_dosen_id;
                    });
                    
                    // Get assessment scores if using new system
                    $p1Scores = $p1Nilai ? $p1Nilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                    $p2Scores = $p2Nilai ? $p2Nilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                    $pembahasScores = $pembahasNilai ? $pembahasNilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                @endphp

                @if($assessmentAspects->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <svg class="w-12 h-12 text-yellow-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-yellow-800 font-medium">Belum Ada Aspek Penilaian</p>
                        <p class="text-yellow-600 text-sm mt-1">Silakan konfigurasi aspek penilaian untuk jenis seminar "{{ $seminar->seminarJenis->nama }}" terlebih dahulu.</p>
                        <a href="{{ route('admin.seminarjenis.edit', $seminar->seminarJenis) }}" class="inline-block mt-4 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            Kelola Aspek Penilaian
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Pembimbing 1 -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-blue-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2">P1</span>
                                {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $p1Signature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'p1' && (int) $signature->dosen_id === (int) $seminar->p1_dosen_id;
                                });
                            @endphp
                            
                            @if($p1Aspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembimbing 1</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($p1Aspects->sortBy('urutan') as $aspect)
                                        @continue($aspect->type !== 'input')
                                        @php
                                            $score = $p1Scores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-blue-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[p1][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-blue-200 rounded focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section P1 -->
                                    <div class="mt-4 pt-4 border-t border-blue-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[p1]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-200" 
                                            placeholder="Tambahkan catatan untuk Pembimbing 1...">{{ old('nilai_catatan.p1', $p1Nilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section P1 -->
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @php
                                    $activeTemplate = $seminar->seminarJenis->documentTemplates()->where('aktif', true)->first();
                                    $signatureMethod = $activeTemplate ? $activeTemplate->signature_method : 'qr_code';
                                @endphp

                                @if($p1Signature && ($p1Signature->qr_code_path || $p1Signature->tanda_tangan))
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-blue-200">
                                        @if($p1Signature->signature_type === 'qr_code' && $p1Signature->qr_code_path)
                                            <img src="{{ Storage::disk('uploads')->url($p1Signature->qr_code_path) }}" alt="QR Code P1" class="w-24 h-24 mx-auto">
                                            <p class="text-xs text-green-600 mt-2"><i class="fas fa-check-circle"></i> Terverifikasi Digital</p>
                                        @elseif($p1Signature->tanda_tangan)
                                            <img src="{{ route('admin.seminar.files.show', ['path' => $p1Signature->tanda_tangan]) }}?t={{ time() }}" alt="Signature P1" class="h-16 mx-auto">
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                    
                                    @if($signatureMethod === 'qr_code' && $p1Signature->signature_type === 'qr_code')
                                        {{-- Allow regenerating QR code --}}
                                        <input type="hidden" name="signatures[p1][signature_type]" value="qr_code">
                                        <input type="hidden" name="signatures[p1][dosen_id]" value="{{ $seminar->p1_dosen_id }}">
                                        <input type="hidden" name="signatures[p1][jenis_penilai]" value="p1">
                                        
                                        <div class="mt-2">
                                            <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border border-blue-200 hover:border-blue-400 bg-blue-50 hover:bg-blue-100 transition-all group">
                                                <div class="relative flex items-center">
                                                    <input type="checkbox" name="signatures[p1][qr_agreement]" value="1" class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-blue-300 bg-white transition-all checked:border-blue-600 checked:bg-blue-600">
                                                    <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 group-hover:text-blue-700"><i class="fas fa-sync-alt mr-1"></i> Update Tanda Tangan QR</span>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                                
                                @if($signatureMethod === 'qr_code' && !($p1Signature && $p1Signature->qr_code_path))
                                    {{-- QR Code Mode - Show checkbox only if no QR code exists --}}
                                    <input type="hidden" name="signatures[p1][signature_type]" value="qr_code">
                                    <input type="hidden" name="signatures[p1][dosen_id]" value="{{ $seminar->p1_dosen_id }}">
                                    <input type="hidden" name="signatures[p1][jenis_penilai]" value="p1">
                                    
                                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white text-blue-600 mb-2">
                                            <i class="fas fa-qrcode text-xl"></i>
                                        </div>
                                        <p class="text-xs text-gray-600 mb-3">Mode QR Code aktif. Centang kotak di bawah untuk menandatangani.</p>
                                        <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border-2 border-dashed border-blue-300 hover:bg-white hover:border-blue-400 transition-all group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="signatures[p1][qr_agreement]" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-blue-300 bg-white transition-all checked:border-blue-600 checked:bg-blue-600">
                                                <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-xs opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-800 group-hover:text-blue-700">
                                                {{ $p1Signature && $p1Signature->signature_type === 'qr_code' ? 'Update Tanda Tangan QR' : 'Saya menandatangani dokumen ini' }}
                                            </span>
                                        </label>
                                    </div>
                                @elseif($signatureMethod === 'manual' && !($p1Signature && $p1Signature->tanda_tangan))
                                    <div class="signature-pad-wrapper-p1">
                                        <input type="hidden" name="signatures[p1][data]" class="signature-input-p1">
                                        <input type="hidden" name="signatures[p1][signature_type]" value="manual">
                                        <input type="hidden" name="signatures[p1][dosen_id]" value="{{ $seminar->p1_dosen_id }}">
                                        <input type="hidden" name="signatures[p1][jenis_penilai]" value="p1">
                                        
                                        <button type="button" class="toggle-signature-btn-p1 text-xs text-blue-600 hover:text-blue-800 mb-2 w-full py-1 border border-blue-300 rounded">
                                            Buat Tanda Tangan
                                        </button>
                                        
                                        <div class="signature-pad-container-p1 hidden">
                                            <canvas width="360" height="120" class="signature-canvas-p1 border border-blue-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                            <button type="button" class="clear-signature-btn-p1 text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pembimbing 2 -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-green-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm mr-2">P2</span>
                                {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $p2Signature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'p2' && (int) $signature->dosen_id === (int) $seminar->p2_dosen_id;
                                });
                            @endphp
                            
                            @if($p2Aspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembimbing 2</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($p2Aspects->sortBy('urutan') as $aspect)
                                        @continue($aspect->type !== 'input')
                                        @php
                                            $score = $p2Scores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-green-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[p2][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-green-200 rounded focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section P2 -->
                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[p2]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-green-200 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-200" 
                                            placeholder="Tambahkan catatan untuk Pembimbing 2...">{{ old('nilai_catatan.p2', $p2Nilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section P2 -->
                            <div class="mt-4 pt-4 border-t border-green-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @if($p2Signature && ($p2Signature->qr_code_path || $p2Signature->tanda_tangan))
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-green-200">
                                        @if($p2Signature->signature_type === 'qr_code' && $p2Signature->qr_code_path)
                                            <img src="{{ Storage::disk('uploads')->url($p2Signature->qr_code_path) }}" alt="QR Code P2" class="w-24 h-24 mx-auto">
                                            <p class="text-xs text-green-600 mt-2"><i class="fas fa-check-circle"></i> Terverifikasi Digital</p>
                                        @elseif($p2Signature->tanda_tangan)
                                            <img src="{{ route('admin.seminar.files.show', ['path' => $p2Signature->tanda_tangan]) }}?t={{ time() }}" alt="Signature P2" class="h-16 mx-auto">
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                    
                                    @if($signatureMethod === 'qr_code' && $p2Signature->signature_type === 'qr_code')
                                        <input type="hidden" name="signatures[p2][signature_type]" value="qr_code">
                                        <input type="hidden" name="signatures[p2][dosen_id]" value="{{ $seminar->p2_dosen_id }}">
                                        <input type="hidden" name="signatures[p2][jenis_penilai]" value="p2">
                                        
                                        <div class="mt-2">
                                            <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border border-green-200 hover:border-green-400 bg-green-50 hover:bg-green-100 transition-all group">
                                                <div class="relative flex items-center">
                                                    <input type="checkbox" name="signatures[p2][qr_agreement]" value="1" class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-green-300 bg-white transition-all checked:border-green-600 checked:bg-green-600">
                                                    <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 group-hover:text-green-700"><i class="fas fa-sync-alt mr-1"></i> Update Tanda Tangan QR</span>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                                
                                @if($signatureMethod === 'qr_code' && !($p2Signature && $p2Signature->qr_code_path))
                                    <input type="hidden" name="signatures[p2][signature_type]" value="qr_code">
                                    <input type="hidden" name="signatures[p2][dosen_id]" value="{{ $seminar->p2_dosen_id }}">
                                    <input type="hidden" name="signatures[p2][jenis_penilai]" value="p2">
                                    
                                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 border border-green-200 rounded-lg p-4 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white text-green-600 mb-2">
                                            <i class="fas fa-qrcode text-xl"></i>
                                        </div>
                                        <p class="text-xs text-gray-600 mb-3">Mode QR Code aktif. Centang kotak di bawah untuk menandatangani.</p>
                                        
                                        <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border-2 border-dashed border-green-300 hover:bg-white hover:border-green-400 transition-all group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="signatures[p2][qr_agreement]" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-green-300 bg-white transition-all checked:border-green-600 checked:bg-green-600">
                                                <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-xs opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-800 group-hover:text-green-700">
                                                {{ $p2Signature && $p2Signature->signature_type === 'qr_code' ? 'Update Tanda Tangan QR' : 'Saya menandatangani dokumen ini' }}
                                            </span>
                                        </label>
                                    </div>
                                @elseif($signatureMethod === 'manual' && !($p2Signature && $p2Signature->tanda_tangan))
                                    <div class="signature-pad-wrapper-p2">
                                        <input type="hidden" name="signatures[p2][data]" class="signature-input-p2">
                                        <input type="hidden" name="signatures[p2][signature_type]" value="manual">
                                        <input type="hidden" name="signatures[p2][dosen_id]" value="{{ $seminar->p2_dosen_id }}">
                                        <input type="hidden" name="signatures[p2][jenis_penilai]" value="p2">
                                        
                                        <button type="button" class="toggle-signature-btn-p2 text-xs text-green-600 hover:text-green-800 mb-2 w-full py-1 border border-green-300 rounded">
                                            Buat Tanda Tangan
                                        </button>
                                        
                                        <div class="signature-pad-container-p2 hidden">
                                            <canvas width="360" height="120" class="signature-canvas-p2 border border-green-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                            <button type="button" class="clear-signature-btn-p2 text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pembahas -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-purple-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm mr-2">PMB</span>
                                {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $pembahasSignature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'pembahas' && (int) $signature->dosen_id === (int) $seminar->pembahas_dosen_id;
                                });
                            @endphp
                            
                            @if($pembahasAspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembahas</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($pembahasAspects->sortBy('urutan') as $aspect)
                                        @continue($aspect->type !== 'input')
                                        @php
                                            $score = $pembahasScores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-purple-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[pembahas][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-purple-200 rounded focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section Pembahas -->
                                    <div class="mt-4 pt-4 border-t border-purple-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[pembahas]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-purple-200 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200" 
                                            placeholder="Tambahkan catatan untuk Pembahas...">{{ old('nilai_catatan.pembahas', $pembahasNilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section Pembahas -->
                            <div class="mt-4 pt-4 border-t border-purple-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @if($pembahasSignature && ($pembahasSignature->qr_code_path || $pembahasSignature->tanda_tangan))
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-purple-200">
                                        @if($pembahasSignature->signature_type === 'qr_code' && $pembahasSignature->qr_code_path)
                                            <img src="{{ Storage::disk('uploads')->url($pembahasSignature->qr_code_path) }}" alt="QR Code Pembahas" class="w-24 h-24 mx-auto">
                                            <p class="text-xs text-green-600 mt-2"><i class="fas fa-check-circle"></i> Terverifikasi Digital</p>
                                        @elseif($pembahasSignature->tanda_tangan)
                                            <img src="{{ route('admin.seminar.files.show', ['path' => $pembahasSignature->tanda_tangan]) }}?t={{ time() }}" alt="Signature Pembahas" class="h-16 mx-auto">
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                    
                                    @if($signatureMethod === 'qr_code' && $pembahasSignature->signature_type === 'qr_code')
                                        <input type="hidden" name="signatures[pembahas][signature_type]" value="qr_code">
                                        <input type="hidden" name="signatures[pembahas][dosen_id]" value="{{ $seminar->pembahas_dosen_id }}">
                                        <input type="hidden" name="signatures[pembahas][jenis_penilai]" value="pembahas">
                                        
                                        <div class="mt-2">
                                            <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border border-purple-200 hover:border-purple-400 bg-purple-50 hover:bg-purple-100 transition-all group">
                                                <div class="relative flex items-center">
                                                    <input type="checkbox" name="signatures[pembahas][qr_agreement]" value="1" class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-purple-300 bg-white transition-all checked:border-purple-600 checked:bg-purple-600">
                                                    <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 group-hover:text-purple-700"><i class="fas fa-sync-alt mr-1"></i> Update Tanda Tangan QR</span>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                                
                                @if($signatureMethod === 'qr_code' && !($pembahasSignature && $pembahasSignature->qr_code_path))
                                    <input type="hidden" name="signatures[pembahas][signature_type]" value="qr_code">
                                    <input type="hidden" name="signatures[pembahas][dosen_id]" value="{{ $seminar->pembahas_dosen_id }}">
                                    <input type="hidden" name="signatures[pembahas][jenis_penilai]" value="pembahas">
                                    
                                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 border border-purple-200 rounded-lg p-4 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white text-purple-600 mb-2">
                                            <i class="fas fa-qrcode text-xl"></i>
                                        </div>
                                        <p class="text-xs text-gray-600 mb-3">Mode QR Code aktif. Centang kotak di bawah untuk menandatangani.</p>
                                        
                                        <label class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg border-2 border-dashed border-purple-300 hover:bg-white hover:border-purple-400 transition-all group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="signatures[pembahas][qr_agreement]" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-purple-300 bg-white transition-all checked:border-purple-600 checked:bg-purple-600">
                                                <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-xs opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-800 group-hover:text-purple-700">
                                                {{ $pembahasSignature && $pembahasSignature->signature_type === 'qr_code' ? 'Update Tanda Tangan QR' : 'Saya menandatangani dokumen ini' }}
                                            </span>
                                        </label>
                                    </div>
                                @elseif($signatureMethod === 'manual' && !($pembahasSignature && $pembahasSignature->tanda_tangan))
                                    <div class="signature-pad-wrapper-pembahas">
                                        <input type="hidden" name="signatures[pembahas][data]" class="signature-input-pembahas">
                                        <input type="hidden" name="signatures[pembahas][signature_type]" value="manual">
                                        <input type="hidden" name="signatures[pembahas][dosen_id]" value="{{ $seminar->pembahas_dosen_id }}">
                                        <input type="hidden" name="signatures[pembahas][jenis_penilai]" value="pembahas">
                                        
                                        <button type="button" class="toggle-signature-btn-pembahas text-xs text-purple-600 hover:text-purple-800 mb-2 w-full py-1 border border-purple-300 rounded">
                                            Buat Tanda Tangan
                                        </button>
                                        
                                        <div class="signature-pad-container-pembahas hidden">
                                            <canvas width="360" height="120" class="signature-canvas-pembahas border border-purple-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                            <button type="button" class="clear-signature-btn-pembahas text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Overall Score Calculation with Weights -->
                    @if(($p1Nilai && $p1Scores->isNotEmpty()) || ($p2Nilai && $p2Scores->isNotEmpty()) || ($pembahasNilai && $pembahasScores->isNotEmpty()))
                        <div class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Perhitungan Nilai Akhir</h3>
                            
                            @php
                                // Get weight percentages from seminar type
                                $p1Weight = $seminar->seminarJenis->p1_weight ?? 35;
                                $p2Weight = $seminar->seminarJenis->p2_weight ?? 35;
                                $pembahasWeight = $seminar->seminarJenis->pembahas_weight ?? 30;
                                
                                // Calculate weighted scores
                                $p1FinalScore = 0;
                                $p2FinalScore = 0;
                                $pembahasFinalScore = 0;
                                
                                if ($p1Nilai && $p1Scores->isNotEmpty()) {
                                    $p1FinalScore = ($p1Nilai->nilai_angka * $p1Weight) / 100;
                                }
                                
                                if ($p2Nilai && $p2Scores->isNotEmpty()) {
                                    $p2FinalScore = ($p2Nilai->nilai_angka * $p2Weight) / 100;
                                }
                                
                                if ($pembahasNilai && $pembahasScores->isNotEmpty()) {
                                    $pembahasFinalScore = ($pembahasNilai->nilai_angka * $pembahasWeight) / 100;
                                }
                                
                                $totalFinalScore = $p1FinalScore + $p2FinalScore + $pembahasFinalScore;
                            @endphp
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-white rounded-lg p-4 border border-blue-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembimbing 1 ({{ $p1Weight }}%)</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($p1FinalScore, 2) }}</div>
                                    @if($p1Nilai && $p1Scores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($p1Nilai->nilai_angka, 2) }} × {{ $p1Weight }}%</div>
                                    @endif
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-green-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembimbing 2 ({{ $p2Weight }}%)</div>
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($p2FinalScore, 2) }}</div>
                                    @if($p2Nilai && $p2Scores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($p2Nilai->nilai_angka, 2) }} × {{ $p2Weight }}%</div>
                                    @endif
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-purple-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembahas ({{ $pembahasWeight }}%)</div>
                                    <div class="text-2xl font-bold text-purple-600">{{ number_format($pembahasFinalScore, 2) }}</div>
                                    @if($pembahasNilai && $pembahasScores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($pembahasNilai->nilai_angka, 2) }} × {{ $pembahasWeight }}%</div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="bg-indigo-600 text-white rounded-lg p-5 flex justify-between items-center">
                                <div>
                                    <div class="text-sm opacity-90 mb-1">Nilai Akhir Seminar</div>
                                    <div class="text-3xl font-bold">{{ number_format($totalFinalScore, 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs opacity-75">Total Bobot: {{ $p1Weight + $p2Weight + $pembahasWeight }}%</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                @endif
            </div>

            <!-- Buttons moved to top -->
        </form>

        @if($seminar->status == 'diajukan')
            <form id="approve-form" action="{{ route('admin.seminar.approve', $seminar->id) }}" method="POST" class="hidden">
                @csrf
            </form>
            <form id="reject-form" action="{{ route('admin.seminar.reject', $seminar->id) }}" method="POST" class="hidden">
                @csrf
            </form>
        @endif

        <!-- Old format deletion handled via special route -->
    </div>


        <!-- Seminar Discussion Section -->
        <div class="mt-8 pt-8 border-t border-gray-100" id="discussion">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-900/5">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                            <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan mahasiswa / dosen pembimbing</p>
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
                    <form action="{{ route('admin.seminar.comment.store', $seminar) }}" method="POST">
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
    </div>
</div>
@endsection

@section('scripts')

    <script>
        // Global function to handle deletion via form submit
        window.deleteBerkas = function(key) {
            if (!confirm('Apakah Anda yakin ingin menghapus berkas ini?')) return;
            
            const deleteRoute = "{{ route('admin.seminar.delete-berkas', [$seminar->id, 'KEY_PLACEHOLDER']) }}";
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteRoute.replace('KEY_PLACEHOLDER', key);
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);
            
            document.body.appendChild(form);
            form.submit();
        };

        (function() {
            function initSeminarEdit() {
                const editor = document.getElementById('judul-editor');
                const textarea = document.getElementById('judul');
                const select = document.getElementById('seminar_jenis_id');
                const container = document.getElementById('syarat-upload-container');
                
                // 3. Manual Dosen Toggle
                document.querySelectorAll('.dosen-select-toggle').forEach(select => {
                    select.addEventListener('change', function() {
                        const role = this.id.replace('_dosen_id', ''); // p1, p2, or pembahas
                        const targetId = role + '_manual_fields';
                        const target = document.getElementById(targetId);
                        
                        // Toggle manual fields visibility
                        if (target) {
                            if (this.value === 'manual') {
                                target.classList.remove('hidden');
                            } else {
                                target.classList.add('hidden');
                            }
                        }

                        // Sync signature hidden dosen_id
                        const sigDosenInput = document.querySelector(`input[name="signatures[${role}][dosen_id]"]`);
                        if (sigDosenInput) {
                            sigDosenInput.value = this.value;
                        }
                    });
                });

                if (!select || !container) return;
                
                // 2. Dynamic Berkas Logic
                const jenisData = {!! json_encode($seminarJenisData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
                const currentBerkas = {!! json_encode($currentBerkasSyarat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
                const isOldFormat = @json($isOldFormat);

                const deleteRoute = "{{ route('admin.seminar.delete-berkas', [$seminar->id, 'KEY_PLACEHOLDER']) }}";

                function renderBerkasFields() {
                    container.innerHTML = '';
                    const selectedId = select.value;
                    const jenis = jenisData[selectedId];
                    if (!jenis || !jenis.berkas_syarat_items || !Array.isArray(jenis.berkas_syarat_items)) {
                        container.innerHTML = `
                            <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center">
                                <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 font-medium">Tidak ada berkas persyaratan yang diperlukan untuk jenis seminar ini.</p>
                            </div>
                        `;
                        return;
                    }

                    // Create Grid Wrapper
                    container.innerHTML = `
                        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-file-upload text-blue-500"></i>
                            Berkas Persyaratan
                        </h2>
                        <div class="grid grid-cols-1 gap-6" id="berkas-grid-container"></div>
                    `;
                    const grid = document.getElementById('berkas-grid-container');

                    // Support old format file (Legacy)
                    if (isOldFormat && currentBerkas['__old_format_file__']) {
                        const oldItem = document.createElement('div');
                        oldItem.className = 'bg-white p-5 rounded-2xl border border-amber-200 shadow-sm transition-all bg-amber-50/30';
                        oldItem.innerHTML = `
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-amber-800 truncate">Berkas Format Lama</h3>
                                    <p class="text-[10px] text-amber-600 uppercase tracking-wider font-semibold mt-0.5">Legacy File Detected</p>
                                </div>
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-full text-center">PERLU DIHAPUS</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-amber-100 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="bg-amber-500 text-white p-2 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-amber-400 uppercase leading-none mb-1">Nama File</p>
                                        <p class="text-sm text-gray-700 truncate font-mono">${currentBerkas['__old_format_file__'].split('/').pop()}</p>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 shadow-sm transition-all" 
                                    onclick="deleteBerkas('old_format_file')">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Berkas Lama
                            </button>
                        `;
                        grid.appendChild(oldItem);
                    }

                    jenis.berkas_syarat_items.forEach(item => {
                        if (!item || !item.key || !item.label) return;
                        const existingPath = isOldFormat ? '' : (currentBerkas[item.key] || '');
                        
                        // Intelligent Type Inference for JS
                        let type = item.type || '';
                        if (!type) {
                            const keyLower = item.key.toLowerCase();
                            const fileKeywords = ['file', 'berkas', 'scan', 'upload', 'dokumen', 'transkrip', 'krs', 'ktm', 'sertifikat', 'surat', 'abstrak', 'poster', 'artikel', 'lembar', 'bukti', 'kartu'];
                            let isFile = false;
                            for (const kw of fileKeywords) {
                                if (keyLower.includes(kw)) { isFile = true; break; }
                            }
                            if (item.extensions && item.extensions.length) isFile = true;
                            
                            type = isFile ? 'file' : 'text';
                            if (type !== 'file' && (keyLower.includes('tgl') || keyLower.includes('tanggal'))) {
                                if (!keyLower.includes('tempat')) {
                                    type = 'date';
                                }
                            }
                        }

                        const isRequired = item.required !== false && !existingPath;
                        const itemExts = Array.isArray(item.extensions) && item.extensions.length ? item.extensions : ['pdf'];
                        const extensions = itemExts.map(e => '.' + e.replace(/^\./, '')).join(',');
                        const maxSize = item.max_size_kb ? Math.round(item.max_size_kb / 1024 * 10) / 10 : 5;
                        
                        const card = document.createElement('div');
                        card.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group';
                        
                        let html = `
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">${item.label}</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                        ${item.required === false ? 'Opsional' : 'Wajib'} ${type === 'file' ? '• ' + itemExts.join(', ').toUpperCase() : ''}
                                    </p>
                                </div>
                                <span class="flex-shrink-0 ${existingPath ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'} text-[10px] font-bold px-2 py-1 rounded-full">
                                    ${existingPath ? (type === 'file' ? 'TERUNGGAH' : 'TERISI') : (type === 'file' ? 'BELUM ADA' : 'KOSONG')}
                                </span>
                            </div>
                            <div class="space-y-4">
                        `;

                        if (existingPath && type === 'file') {
                            const fileName = existingPath.split('/').pop();
                            html += `
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="bg-blue-600 text-white p-2 rounded-lg shadow-sm">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Nama File</p>
                                                <p class="text-sm text-gray-700 truncate font-mono font-medium" title="${fileName}">${fileName}</p>
                                            </div>
                                        </div>
                                        <a href="/admin/seminars/files/${encodeURIComponent(existingPath)}" target="_blank" class="flex-shrink-0 bg-white text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 p-2 rounded-lg transition-all shadow-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 transition-all flex items-center justify-center gap-2" 
                                        onclick="deleteBerkas('${item.key}')">
                                    <i class="fas fa-trash-alt"></i> Hapus Berkas Saat Ini
                                </button>
                            `;
                        }

                        html += `<div class="relative">`;
                        
                        // Input rendering based on type
                        if (type === 'file') {
                            html += `
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    ${existingPath ? 'Ganti Berkas' : 'Unggah Berkas'}
                                </label>
                                <input
                                    type="file"
                                    name="berkas_syarat_items[${item.key}]"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                    accept="${extensions}"
                                    ${isRequired ? 'required' : ''}
                                />
                                <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimum ukuran file: <span class="font-bold text-gray-600">${maxSize}MB</span></p>
                            `;
                        } else if (type === 'textarea') {
                            html += `
                                <textarea name="berkas_syarat_items[${item.key}]" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:border-blue-300 outline-none transition-all text-sm" placeholder="${item.placeholder || 'Masukkan ' + item.label}" ${isRequired ? 'required' : ''}>${existingPath}</textarea>
                            `;
                        } else if (type === 'select') {
                            const options = Array.isArray(item.options) ? item.options : (typeof item.options === 'string' ? item.options.split('\n').map(l => l.trim()).filter(l => l) : []);
                            html += `
                                <select name="berkas_syarat_items[${item.key}]" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:border-blue-300 outline-none transition-all text-sm bg-white" ${isRequired ? 'required' : ''}>
                                    <option value="">Pilih ${item.label}</option>
                                    ${options.map(opt => `<option value="${opt}" ${opt == existingPath ? 'selected' : ''}>${opt}</option>`).join('')}
                                </select>
                            `;
                        } else if (type === 'checkbox') {
                            const options = Array.isArray(item.options) ? item.options : (typeof item.options === 'string' ? item.options.split('\n').map(l => l.trim()).filter(l => l) : []);
                            const currentVals = Array.isArray(existingPath) ? existingPath : [];
                            html += `<div class="flex flex-wrap gap-4 mt-2">`;
                            options.forEach(opt => {
                                const checked = currentVals.includes(opt) ? 'checked' : '';
                                html += `
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="berkas_syarat_items[${item.key}][]" value="${opt}" ${checked} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                        <span class="text-sm text-gray-700">${opt}</span>
                                    </label>
                                `;
                            });
                            html += `</div>`;
                        } else {
                            // Default for text, email, number
                            if (type === 'date') {
                                const indoDate = existingPath ? (() => { try { return new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(existingPath)); } catch(e) { return existingPath; }})() : '';
                                html += `
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">${item.label}</label>
                                    <input
                                        type="date"
                                        name="berkas_syarat_items[${item.key}]"
                                        value="${existingPath}"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:border-blue-300 outline-none transition-all text-sm"
                                        placeholder="${item.placeholder || 'Masukkan ' + item.label}"
                                        ${isRequired ? 'required' : ''}
                                        oninput="this.nextElementSibling.textContent = this.value ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(this.value)) : ''"
                                    />
                                    <p class="text-xs text-blue-600 font-semibold mt-1">${indoDate}</p>
                                `;
                            } else {
                                html += `
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">${item.label}</label>
                                    <input
                                        type="${type}"
                                        name="berkas_syarat_items[${item.key}]"
                                        value="${existingPath}"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:border-blue-300 outline-none transition-all text-sm"
                                        placeholder="${item.placeholder || 'Masukkan ' + item.label}"
                                        ${isRequired ? 'required' : ''}
                                    />
                                `;
                            }
                        }

                        html += `</div></div>`;
                        
                        card.innerHTML = html;
                        grid.appendChild(card);
                    });
                        
                        card.innerHTML = html;
                        grid.appendChild(card);
                    });
                }

                select.addEventListener('change', renderBerkasFields);
                renderBerkasFields();
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initSeminarEdit();
                if (window.location.hash) {
                    setTimeout(() => {
                        const element = document.querySelector(window.location.hash);
                        if (element) {
                            element.scrollIntoView({ behavior: 'smooth' });
                        }
                    }, 500);
                }
            } else {
                document.addEventListener('DOMContentLoaded', () => {
                    initSeminarEdit();
                    if (window.location.hash) {
                        setTimeout(() => {
                            const element = document.querySelector(window.location.hash);
                            if (element) {
                                element.scrollIntoView({ behavior: 'smooth' });
                            }
                        }, 500);
                    }
                });
            }
            window.addEventListener('page-loaded', () => {
                initSeminarEdit();
                if (window.location.hash) {
                    const element = document.querySelector(window.location.hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
            
            // For general PDF opening
            window.openPdfUrl = function(url) {
                window.open(url, '_blank');
            };
        })();
    </script>
    @vite('resources/js/signature-pad.js')
@endsection
