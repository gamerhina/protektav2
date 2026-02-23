@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
@php
    $isImpersonating = session()->has('impersonated_by');
    $_guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];
    $currentGuard = null;
    foreach ($_guardPriority as $_g) {
        if (Auth::guard($_g)->check()) { $currentGuard = $_g; break; }
    }
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit Profile</h1>

        <form method="POST" enctype="multipart/form-data" action="{{
            ($currentGuard === 'admin') ? route('admin.profile.update') :
            (($currentGuard === 'dosen') ? route('dosen.profile.update') :
            (($currentGuard === 'mahasiswa') ? route('mahasiswa.profile.update') : '#'))
        }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $user->nama) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        required
                    />
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('email') border-red-500 @enderror"
                        required
                    />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="wa" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                    <input
                        type="text"
                        name="wa"
                        id="wa"
                        value="{{ old('wa', $user->wa) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('wa') border-red-500 @enderror"
                    />
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
                        value="{{ old('hp', $user->hp) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('hp') border-red-500 @enderror"
                    />
                    @error('hp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($currentGuard === 'admin' || $currentGuard === 'dosen')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-700 rounded-md font-medium">
                        {{ $user->nip ?? '-' }}
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider font-semibold italic">Hubungi Admin IT jika terdapat ketidaksesuaian data NIP.</p>
                </div>
                @endif

                @if($currentGuard === 'mahasiswa')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NPM</label>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-700 rounded-md font-medium">
                        {{ $user->npm ?? '-' }}
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider font-semibold italic">Hubungi Admin jika terdapat ketidaksesuaian data NPM.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pembimbing Akademik (PA)</label>
                    <div class="w-full px-3 py-2 border border-blue-200 bg-blue-50 text-blue-800 rounded-md font-medium">
                        {{ $user->pembimbingAkademik->nama ?? 'Belum Ditentukan' }}
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider font-semibold italic">Hubungi Admin jika terdapat ketidaksesuaian data PA.</p>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">Foto Profil</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                OPSIONAL • JPG, JPEG, PNG, WEBP, GIF
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-{{ $user->foto ? 'emerald' : 'gray' }}-100 text-{{ $user->foto ? 'emerald' : 'gray' }}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                            {{ $user->foto ? 'SUDAH ADA' : 'BELUM ADA' }}
                        </span>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 relative">
                            @if($user->foto ?? false)
                                <img src="{{ asset('uploads/' . $user->foto) }}" alt="Current Photo" class="w-16 h-16 rounded-xl object-cover border border-gray-100 shadow-sm">
                                <button type="button" 
                                        onclick="if(confirm('Yakin ingin menghapus foto profil?')) document.getElementById('delete-foto-form').submit();"
                                        class="absolute -top-2 -right-2 flex items-center justify-center w-6 h-6 bg-red-500 text-white rounded-full shadow-md hover:bg-red-600 transition-all hover:scale-110"
                                        title="Hapus foto profil">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            @else
                                <div class="w-16 h-16 rounded-xl flex items-center justify-center bg-gray-50 text-gray-300 border border-gray-200">
                                    <i class="fas fa-user text-2xl"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 relative group/input">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Ganti Foto</label>
                            <input
                                type="file"
                                name="foto"
                                id="foto"
                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                            />
                            <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimal ukuran file: <span class="font-bold text-gray-600">800KB</span></p>
                            @error('foto')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-8 border-gray-200">

            <h2 class="text-xl font-semibold text-gray-800 mb-2">Ubah Password</h2>
            <p class="text-sm text-gray-500 mb-4">Kosongkan kolom di bawah jika tidak ingin mengubah password.</p>

            <div class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('current_password') border-red-500 @enderror pr-10"
                        />
                <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="current_password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('current_password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
            <div class="relative">
                <input
                    type="password"
                    name="new_password"
                    id="new_password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md @error('new_password') border-red-500 @enderror pr-10"
                />
                <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="new_password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('new_password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
            <div class="relative">
                <input
                    type="password"
                    name="new_password_confirmation"
                    id="new_password_confirmation"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10"
                />
                <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="new_password_confirmation">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{
            ($currentGuard === 'admin') ? route('admin.dashboard') :
            (($currentGuard === 'dosen') ? route('dosen.dashboard') :
            (($currentGuard === 'mahasiswa') ? route('mahasiswa.dashboard') : '/'))
        }}" class="btn-pill btn-pill-secondary text-center w-full sm:w-auto">
            Cancel
        </a>
        <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
            Update Profil
        </button>
    </div>
</form>

{{-- Hidden form for deleting photo (outside main form to avoid nesting) --}}
@if($user->foto ?? false)
<form id="delete-foto-form" method="POST" action="{{ route($currentGuard . '.profile.destroy-foto') }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif

</div>
</div>
@endsection
