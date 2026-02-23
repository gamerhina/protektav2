@extends('layouts.app')

@section('title', 'Import Data Mahasiswa')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Import Data Mahasiswa</h1>

        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-medium text-blue-800 mb-2">Petunjuk Import:</h3>
            <ul class="list-disc pl-5 space-y-1 text-blue-700">
                <li>Format file yang didukung: Excel (.xlsx, .xls) atau CSV</li>
                <li>Kolom yang wajib diisi: <strong>Nama</strong>, <strong>NPM</strong>, dan <strong>Email</strong></li>
                <li>Kolom opsional: <strong>HP</strong>, <strong>WA</strong>, <strong>Pembimbing Akademik (NIP)</strong>, dan <strong>Password</strong></li>
                <li>Gunakan format kolom sesuai contoh di bawah</li>
                <li>Data mahasiswa dengan NPM yang sudah ada akan diperbarui</li>
                <li>Untuk kolom <strong>Pembimbing Akademik (NIP)</strong>, masukkan NIP dosen yang sudah terdaftar di sistem</li>
                <li>Jika kolom Password kosong, maka password default akan menjadi NPM</li>
            </ul>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.mahasiswa.sample.download') }}" class="btn-gradient inline-flex items-center gap-2" data-no-ajax>
                <i class="fas fa-download"></i> Download Contoh File
            </a>
        </div>

        <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">File Import</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                WAJIB • XLSX, XLS, CSV
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                    </div>
                    <div class="relative group/input">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas</label>
                        <input
                            type="file"
                            name="file"
                            id="file"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                            required
                        >
                        <p class="text-[10px] text-gray-400 mt-2 italic px-1">Pastikan format sesuai template.</p>
                        @error('file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn-pill btn-pill-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Import Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
