@extends('layouts.app')

@section('title', 'Ubah GDrive Folder')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Ubah Google Drive Folder</h1>
                <p class="text-sm text-gray-600">Ubah informasi folder Google Drive</p>
            </div>
        </div>

        <form action="{{ route('admin.gdrive.update', $gdriveFolder) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Folder</label>
                <input
                    type="text"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('nama') border-red-500 @enderror"
                    id="nama"
                    name="nama"
                    value="{{ old('nama', $gdriveFolder->nama) }}"
                    required
                >
                @error('nama')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="link" class="block text-sm font-medium text-gray-700 mb-2">Link Google Drive</label>
                <input
                    type="url"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('link') border-red-500 @enderror"
                    id="link"
                    name="link"
                    value="{{ old('link', $gdriveFolder->link) }}"
                    required
                >
                @error('link')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('keterangan') border-red-500 @enderror"
                    id="keterangan"
                    name="keterangan"
                    rows="4"
                >{{ old('keterangan', $gdriveFolder->keterangan) }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3 justify-end pt-6 border-t border-gray-100">
                <a href="{{ route('admin.gdrive.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    Kembali
                </a>
                <button type="submit" class="btn-gradient px-6 py-2.5 rounded-xl text-sm font-semibold">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
