@extends('layouts.app')

@section('title', 'Edit Role: ' . $suratRole->nama)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.surat-role.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Role
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-4">Edit Role: {{ $suratRole->nama }}</h1>
        <p class="text-slate-500 mt-1">Perbarui informasi role persetujuan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('admin.surat-role.update', $suratRole) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Nama Role --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nama Role <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" 
                           value="{{ old('nama', $suratRole->nama) }}"
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all @error('nama') border-red-500 @enderror"
                           required>
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kode Role --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Kode Unik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode" 
                           value="{{ old('kode', $suratRole->kode) }}"
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all font-mono uppercase @error('kode') border-red-500 @enderror"
                           required>
                    @error('kode')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all @error('deskripsi') border-red-500 @enderror">{{ old('deskripsi', $suratRole->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Delegasi Dosen --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Delegasikan ke Dosen <span class="text-red-500">*</span>
                    </label>
                    <select name="dosen_id" 
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all @error('dosen_id') border-red-500 @enderror"
                            required>
                        <option value="">-- Pilih Dosen Pengampu Role --</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id', $suratRole->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->nama }} (NIP: {{ $dosen->nip }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-slate-500 text-sm mt-1">Dosen yang didelegasikan akan menerima tugas persetujuan untuk role ini</p>
                    @error('dosen_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>



                {{-- Warna --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Warna Badge
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="color" name="warna" 
                               value="{{ old('warna', $suratRole->warna ?? '#4F46E5') }}"
                               class="h-12 w-24 border border-slate-200 rounded-xl cursor-pointer">
                        <span class="text-slate-600 text-sm">Pilih warna untuk badge role</span>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl">
                    <input type="checkbox" name="is_active" 
                           id="is_active" 
                           value="1" 
                           {{ old('is_active', $suratRole->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                    <label for="is_active" class="font-medium text-slate-700 cursor-pointer">
                        Role Aktif
                    </label>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.surat-role.index') }}" 
                   class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
