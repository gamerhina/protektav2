@extends('layouts.app')

@section('title', 'Manage Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manage Home Page</h1>
            <p class="text-sm text-slate-500">Atur konten, tampilan visual, dan warna landing page aplikasi.</p>
        </div>
        <div class="flex gap-2 flex-wrap sm:flex-nowrap">
            <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-pill-secondary !no-underline">
                Kembali
            </a>
            <button type="submit" form="settingsForm" class="btn-pill btn-pill-primary inline-flex items-center gap-2">
                Simpan Perubahan
            </button>
        </div>
    </div>

    <div class="mt-6">
        <form id="settingsForm" method="POST" action="{{ route('admin.settings.landing.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        
        <!-- Left Column: Content & Assets (Span 8) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- General Content Section -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Konten Utama
                </h2>
                <div class="grid gap-5">
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Aplikasi</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings->app_name) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400" placeholder="Protekta Apps">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Header</label>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $settings->hero_title) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400" placeholder="Judul utama">
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Subjudul Singkat</label>
                        <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $settings->hero_subtitle) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi Aplikasi</label>
                        <textarea name="app_description" rows="2" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors placeholder-slate-400 resize-none">{{ old('app_description', $settings->app_description) }}</textarea>
                    </div>

                    <div class="grid md:grid-cols-3 gap-5 pt-2 border-t border-slate-100">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Label Tombol CTA</label>
                            <input type="text" name="cta_label" value="{{ old('cta_label', $settings->cta_label) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Link CTA</label>
                            <input type="text" name="cta_link" value="{{ old('cta_link', $settings->cta_link) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors" placeholder="/login">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Jadwal</label>
                            <input type="text" name="schedule_heading" value="{{ old('schedule_heading', $settings->schedule_heading) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:bg-white transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Assets Section -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Aset Visual & Background
                </h2>
                
                <!-- Logos Row -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    @foreach([
                        'app_icon' => ['label' => 'Icon Dashboard', 'format' => 'PNG/JPG', 'type' => 'logo'],
                        'logo' => ['label' => 'Logo Utama', 'format' => 'PNG/JPG', 'type' => 'logo'],
                        'favicon' => ['label' => 'Favicon', 'format' => 'ICO/PNG', 'type' => 'logo']
                    ] as $field => $config)
                        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group {{ $field === 'favicon' ? 'md:col-span-2' : '' }}">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">{{ $config['label'] }}</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                        {{ $config['format'] }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @php
                                        $fieldUrl = $field === 'landing_background' ? $settings->landing_background_url : $settings->{$field . '_url'};
                                    @endphp
                                    @if($fieldUrl)
                                        <button type="button" data-remove-asset="remove_{{ $field }}" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-1 rounded-md transition-colors">HAPUS</button>
                                    @endif
                                    <span class="flex-shrink-0 bg-{{ $fieldUrl ? 'emerald' : 'gray' }}-100 text-{{ $fieldUrl ? 'emerald' : 'gray' }}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ $fieldUrl ? 'ADA' : 'KOSONG' }}
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="remove_{{ $field }}" value="0">

                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div id="preview-{{ $field }}" class="w-16 h-16 rounded-xl border border-gray-100 shadow-sm bg-gray-50 flex items-center justify-center p-2">
                                        @if($fieldUrl)
                                            <img 
                                                src="{{ $fieldUrl }}" 
                                                class="max-w-full max-h-full object-contain" 
                                                alt="{{ $config['label'] }}"
                                                onerror="document.getElementById('preview-{{ $field }}').innerHTML='<svg class=\'w-8 h-8 text-gray-300\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg>'"
                                            >
                                        @else
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex-1 relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Upload File</label>
                                    <input
                                        type="file"
                                        name="{{ $field }}"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                    />
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Klik browse untuk mengganti.</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Backgrounds Grid -->
                <div class="grid md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                    <!-- Header Slider (Full Width) -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                                Slider Header
                                @if(!empty($settings->landing_background_slides) || $settings->landing_background_url)
                                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                @endif
                            </label>
                        </div>
                        <input type="hidden" name="remove_landing_background" value="0">

                        @if(isset($sliderReady) && !$sliderReady)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                                Slider belum aktif karena kolom database belum ada. Jalankan: <span class="font-mono">php artisan migrate</span>
                            </div>
                        @endif

                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold text-slate-700">Aktifkan Slider</label>
                                <div>
                                    <input type="hidden" name="landing_slider_enabled" value="0">
                                    <input type="checkbox" name="landing_slider_enabled" value="1" {{ old('landing_slider_enabled', $settings->landing_slider_enabled ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Interval (ms)</label>
                                    <input type="number" name="landing_slider_interval_ms" min="2000" max="20000" step="500" value="{{ old('landing_slider_interval_ms', $settings->landing_slider_interval_ms ?? 6000) }}" class="mt-1.5 w-full rounded-xl border-slate-200 bg-white px-3 py-2 text-sm">
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] font-bold text-slate-500 uppercase">Upload Slide</label>
                                        <button type="button" id="add_slide_upload" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded-md transition-colors">+ Tambah Gambar</button>
                                    </div>
                                    <div id="slide_upload_list" class="space-y-4">
                                        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group relative">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-sm font-bold text-gray-800 truncate">Slide Baru</h3>
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                                        JPG/PNG
                                                    </p>
                                                </div>
                                                <span class="flex-shrink-0 bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-1 rounded-full">BARU</span>
                                            </div>
                                            <div class="relative group/input">
                                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Pilih Gambar</label>
                                                <input type="file" name="landing_slides[]" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4">
                                <p class="text-xs font-semibold text-slate-800">Tambah Upload Gambar Slide</p>
                                <p class="mt-1 text-[11px] text-slate-500">Pilih 1+ gambar sekaligus (JPG/PNG). Setelah upload, klik <b>Simpan Perubahan</b>.</p>
                            </div>
                            <p class="text-[11px] text-slate-500">Tips: urutan slide mengikuti nilai Order (kecil → besar).</p>
                        </div>

                        @php
                            $slidePaths = old('slides_existing') ? collect(old('slides_existing'))->pluck('path')->filter()->values()->all() : ($settings->landing_background_slides ?? []);
                        @endphp

                        @if(!empty($slidePaths))
                            <div id="slides-sortable" class="space-y-3">
                                @foreach($slidePaths as $i => $path)
                                    <div class="slide-item flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 cursor-move hover:border-blue-300 transition-colors" data-index="{{ $i }}">
                                        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 px-2">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="h-14 w-24 overflow-hidden rounded-lg bg-slate-200 flex-shrink-0">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('uploads')->url($path) }}" class="w-full h-full object-cover" alt="Slide {{ $i + 1 }}">
                                        </div>
                                        <div class="flex-1 flex items-center justify-between">
                                            <div class="text-xs text-slate-600">
                                                <span class="font-semibold">Slide {{ $i + 1 }}</span>
                                            </div>
                                            <button type="button" class="remove-slide text-red-600 hover:text-red-800 p-2" title="Hapus Slide" data-slide-index="{{ $i }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="slides_existing[{{ $i }}][path]" value="{{ $path }}" class="slide-path">
                                        <input type="hidden" name="slides_existing[{{ $i }}][order]" value="{{ $i + 1 }}" class="slide-order">
                                        <input type="hidden" name="slides_existing[{{ $i }}][remove]" value="0" class="slide-remove">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-xs text-slate-500">
                                Belum ada slide header.
                            </div>
                        @endif

                        
                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Tinggi (px)</label>
                                <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('header_height', $settings->header_height ?? 500) }}px</span>
                            </div>
                            <input type="range" name="header_height" min="300" max="800" value="{{ old('header_height', $settings->header_height ?? 500) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value + 'px'">
                        </div>
                    </div>

                    <!-- Content Background -->
                    <div class="space-y-4">
                        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">Background Konten</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                        OPSIONAL • JPG/PNG
                                    </p>
                                </div>
                                @if($settings->content_background_url)
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-remove-asset="remove_content_background" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-1 rounded-md transition-colors">HAPUS</button>
                                    </div>
                                @endif
                                <input type="hidden" name="remove_content_background" value="0">
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($settings->content_background_url)
                                        <div class="w-24 h-16 rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                                            <img src="{{ $settings->content_background_url }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-24 h-16 rounded-xl flex items-center justify-center bg-gray-50 text-gray-300 border border-gray-200">
                                            <span class="text-[10px] font-bold">NO IMG</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1 relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Upload File</label>
                                    <input
                                        type="file"
                                        name="content_background"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                    />
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Ganti gambar pattern/background halaman.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3 space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas</label>
                                <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('content_background_opacity', $settings->content_background_opacity ?? 0.92) }}</span>
                            </div>
                            <input type="range" name="content_background_opacity" min="0" max="1" step="0.05" value="{{ old('content_background_opacity', $settings->content_background_opacity ?? 0.92) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                        </div>
                    </div>

                    <!-- Login Background -->
                    <div class="space-y-4">
                        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">Background Login</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                        OPSIONAL • JPG/PNG
                                    </p>
                                </div>
                                @if($settings->login_background_url)
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-remove-asset="remove_login_background" class="text-[10px] font-bold text-red-500 hover:text-red-600 bg-red-50 px-2 py-1 rounded-md transition-colors">HAPUS</button>
                                    </div>
                                @endif
                                <input type="hidden" name="remove_login_background" value="0">
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($settings->login_background_url)
                                        <div class="w-32 h-20 rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                                            <img src="{{ $settings->login_background_url }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-32 h-20 rounded-xl flex items-center justify-center bg-gray-50 text-gray-300 border border-gray-200">
                                            <span class="text-[10px] font-bold">NO IMG</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1 relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Upload File</label>
                                    <input
                                        type="file"
                                        name="login_background"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                    />
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Gambar ini akan tampil di halaman login.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Styling (Span 4) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Theme Colors -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                    Warna Tema
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'primary_color' => 'Primary',
                        'secondary_color' => 'Secondary',
                        'accent_color' => 'Accent',
                        'button_color' => 'CTA Button'
                    ] as $key => $label)
                    <div class="p-3 rounded-2xl border border-slate-100 bg-slate-50">
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-2">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $key }}" value="{{ old($key, $settings->$key ?? '#000000') }}" class="h-8 w-8 rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            <input type="text" name="{{ $key }}_text" value="{{ old($key, $settings->$key ?? '#000000') }}" class="flex-1 w-full min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs font-mono text-center uppercase" maxlength="7">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Overlay Settings -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Hero Overlay
                </h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                         <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Gradient Dari</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="header_overlay_from" value="{{ old('header_overlay_from', $settings->header_overlay_from ?? '#0f172a') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Gradient Ke</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="header_overlay_to" value="{{ old('header_overlay_to', $settings->header_overlay_to ?? '#172554') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-50">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas Overlay</label>
                            <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('hero_overlay_opacity', $settings->hero_overlay_opacity ?? 0.9) }}</span>
                        </div>
                        <input type="range" name="hero_overlay_opacity" step="0.05" min="0" max="1" value="{{ old('hero_overlay_opacity', $settings->hero_overlay_opacity ?? 0.9) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                    </div>
                     <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Opasitas Background</label>
                            <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ old('landing_background_opacity', $settings->landing_background_opacity ?? 0.95) }}</span>
                        </div>
                        <input type="range" name="landing_background_opacity" step="0.05" min="0" max="1" value="{{ old('landing_background_opacity', $settings->landing_background_opacity ?? 0.95) }}" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="this.previousElementSibling.lastElementChild.textContent = this.value">
                    </div>
                </div>
            </div>

            <!-- Table Style -->
            <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Tampilan Tabel
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        'table_header_from' => 'Header From',
                        'table_header_to' => 'Header To',
                        'table_header_text_color' => 'Header Text',
                        'table_row_odd_color' => 'Baris Ganjil',
                        'table_row_even_color' => 'Baris Genap',
                        'table_row_text_color' => 'Teks Baris',
                        'table_border_color' => 'Border'
                    ] as $key => $label)
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1 truncate" title="{{ $label }}">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $key }}" value="{{ old($key, $settings->$key ?? '#000000') }}" class="h-8 w-full rounded-lg border border-slate-200 cursor-pointer p-0.5 bg-white">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    function initLandingSettings() {
        const addBtn = document.getElementById('add_slide_upload');
        const uploadList = document.getElementById('slide_upload_list');
        const sortableContainer = document.getElementById('slides-sortable');
        
        if (!addBtn || addBtn.dataset.initialized === 'true') return;

        // 1. Sortable.js Initialization
        if (sortableContainer && typeof Sortable !== 'undefined') {
            new Sortable(sortableContainer, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-blue-50',
                onEnd: function() {
                    updateSlideNumbers();
                }
            });
        }

        function updateSlideNumbers() {
            if (!sortableContainer) return;
            const items = sortableContainer.querySelectorAll('.slide-item:not(.hidden)');
            items.forEach((item, index) => {
                const orderInput = item.querySelector('.slide-order');
                const label = item.querySelector('.font-semibold');
                const realIndex = Array.from(sortableContainer.querySelectorAll('.slide-item')).indexOf(item);
                
                // Simpan order for persistent slides
                if (orderInput) orderInput.value = index + 1;
                if (label) label.textContent = `Slide ${index + 1}`;
            });
        }

        // 2. Remove Slide Handler (Existing Slides)
        if (sortableContainer) {
            sortableContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-slide');
                if (removeBtn) {
                    const item = removeBtn.closest('.slide-item');
                    if (item && confirm('Yakin ingin menghapus slide ini?')) {
                        const removeInput = item.querySelector('.slide-remove');
                        if (removeInput) {
                            removeInput.value = '1';
                            item.classList.add('hidden');
                            updateSlideNumbers();
                        }
                    }
                }
            });
        }

        addBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group relative';
            row.innerHTML = `
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-gray-800 truncate">Slide Baru</h3>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">JPG/PNG</p>
                    </div>
                    <button type="button" class="remove-slide-upload text-[10px] font-bold text-red-600 hover:text-red-700 bg-red-50 px-2 py-1 rounded-md">Hapus</button>
                </div>
                <div class="relative group/input">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Pilih Gambar</label>
                    <input type="file" name="landing_slides[]" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                </div>
            `;
            uploadList.appendChild(row);
            row.querySelector('.remove-slide-upload')?.addEventListener('click', () => row.remove());
        });

        document.querySelectorAll('[data-remove-asset]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.removeAsset;
                const hidden = document.querySelector(`input[name="${target}"]`);
                if (!hidden) return;
                if (confirm('Hapus file ini?')) {
                    hidden.value = '1';
                    button.closest('form').submit();
                }
            });
        });
        
        document.querySelectorAll('input[type="color"]').forEach(input => {
            const textInput = document.querySelector(`input[name="${input.name}_text"]`);
            if (textInput) {
                input.addEventListener('input', (e) => textInput.value = e.target.value);
                textInput.addEventListener('input', (e) => input.value = e.target.value);
            }
        });

        addBtn.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initLandingSettings();
    else document.addEventListener('DOMContentLoaded', initLandingSettings);
    window.addEventListener('page-loaded', initLandingSettings);
})();
</script>
@endsection
