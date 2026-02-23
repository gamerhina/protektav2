@extends('layouts.app')

@section('title', 'Tambah Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Admin Baru</h1>
            <p class="text-gray-500 mt-1">Daftarkan akun administrator baru untuk sistem.</p>
        </div>

        <form action="{{ route('admin.admins.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        placeholder="Nama lengkap admin"
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input
                            type="text"
                            name="nip"
                            id="nip"
                            value="{{ old('nip') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nip') border-red-500 @enderror"
                            placeholder="Nomor Induk Pegawai"
                            required
                        >
                        @error('nip')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('email') border-red-500 @enderror"
                            placeholder="email@protekta.com"
                            required
                        >
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="wa" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                        <input
                            type="text"
                            name="wa"
                            id="wa"
                            value="{{ old('wa') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('wa') border-red-500 @enderror"
                            placeholder="628123456789"
                        >
                        @error('wa')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hp" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                            type="text"
                            name="hp"
                            id="hp"
                            value="{{ old('hp') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('hp') border-red-500 @enderror"
                            placeholder="08123456789"
                        >
                        @error('hp')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 group hover:border-blue-200 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">Foto Profil</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                OPSIONAL • JPG, JPEG, PNG, WEBP, GIF
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                    </div>

                    <div class="relative group/input">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Foto</label>
                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            accept=".jpg,.jpeg,.png,.webp,.gif"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                        >
                        <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimal ukuran file: <span class="font-bold text-gray-600">800KB</span></p>
                    </div>
                </div>
                @error('foto')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md @error('password') border-red-500 @enderror pr-10"
                                placeholder="Masukkan password"
                                required
                            >
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10"
                                placeholder="Konfirmasi password"
                                required
                            >
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.admins.index') }}" class="btn-pill btn-pill-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Admin
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
