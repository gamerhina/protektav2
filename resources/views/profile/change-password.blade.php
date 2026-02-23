@extends('layouts.app')

@section('title', 'Change Password')

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
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Change Password</h1>

        <form method="POST" action="{{
            ($currentGuard === 'admin') ? route('admin.change-password.update') :
            (($currentGuard === 'dosen') ? route('dosen.change-password.update') :
            (($currentGuard === 'mahasiswa') ? route('mahasiswa.change-password.update') : '#'))
        }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="current_password" 
                            id="current_password" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('current_password') border-red-500 @enderror pr-10"
                            required
                        />
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="new_password" 
                            id="new_password" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('new_password') border-red-500 @enderror pr-10"
                            required
                        />
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="new_password_confirmation" 
                            id="new_password_confirmation" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md pr-10"
                            required
                        />
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="new_password_confirmation">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{
                    ($currentGuard === 'admin') ? route('admin.dashboard') :
                    (($currentGuard === 'dosen') ? route('dosen.dashboard') :
                    (($currentGuard === 'mahasiswa') ? route('mahasiswa.dashboard') : '/'))
                }}" class="btn-pill btn-pill-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-pill btn-pill-primary">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection