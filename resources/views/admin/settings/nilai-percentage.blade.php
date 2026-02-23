@extends('layouts.app')

@section('title', 'Nilai Percentage Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Konfigurasi Persentase Nilai Seminar</h1>
            <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-primary justify-center sm:justify-start">
                Kelola Aspek Penilaian
            </a>
        </div>
        
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
                <strong>Catatan:</strong> Persentase di bawah ini mengatur bobot nilai akhir dari setiap penilai (P1, P2, Pembahas). 
                Untuk mengatur aspek penilaian detail per jenis seminar, klik "Kelola Aspek Penilaian" di atas atau buka menu Seminar Types → Aspek Penilaian.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.nilai-percentage.update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Pembimbing 1</h3>
                    <div class="mb-4">
                        <label for="p1" class="block text-sm font-medium text-gray-700 mb-1">Persentase (%)</label>
                        <input 
                            type="number" 
                            name="p1" 
                            id="p1" 
                            value="{{ old('p1', $nilaiPercentageConfig['p1'] ?? 40) }}" 
                            min="0" 
                            max="100" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1') border-red-500 @enderror"
                            required
                        />
                        @error('p1')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-sm text-gray-600">Nilai yang diberikan oleh Pembimbing 1 akan dihitung sebagai {{ old('p1', $nilaiPercentageConfig['p1'] ?? 40) }}% dari nilai akhir.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Pembimbing 2</h3>
                    <div class="mb-4">
                        <label for="p2" class="block text-sm font-medium text-gray-700 mb-1">Persentase (%)</label>
                        <input 
                            type="number" 
                            name="p2" 
                            id="p2" 
                            value="{{ old('p2', $nilaiPercentageConfig['p2'] ?? 30) }}" 
                            min="0" 
                            max="100" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2') border-red-500 @enderror"
                            required
                        />
                        @error('p2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-sm text-gray-600">Nilai yang diberikan oleh Pembimbing 2 akan dihitung sebagai {{ old('p2', $nilaiPercentageConfig['p2'] ?? 30) }}% dari nilai akhir.</p>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Pembahas</h3>
                    <div class="mb-4">
                        <label for="pembahas" class="block text-sm font-medium text-gray-700 mb-1">Persentase (%)</label>
                        <input 
                            type="number" 
                            name="pembahas" 
                            id="pembahas" 
                            value="{{ old('pembahas', $nilaiPercentageConfig['pembahas'] ?? 30) }}" 
                            min="0" 
                            max="100" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas') border-red-500 @enderror"
                            required
                        />
                        @error('pembahas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-sm text-gray-600">Nilai yang diberikan oleh Pembahas akan dihitung sebagai {{ old('pembahas', $nilaiPercentageConfig['pembahas'] ?? 30) }}% dari nilai akhir.</p>
                </div>
            </div>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-medium text-blue-800 mb-2">Total Persentase: 
                    <span id="total-percentage" class="font-bold">
                        {{ (old('p1', $nilaiPercentageConfig['p1'] ?? 40)) + (old('p2', $nilaiPercentageConfig['p2'] ?? 30)) + (old('pembahas', $nilaiPercentageConfig['pembahas'] ?? 30)) }}%
                    </span>
                </h4>
                <p class="text-sm text-gray-700">Pastikan total persentase mencapai 100%</p>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-pill-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-pill btn-pill-primary">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    function initNilaiPercentageSettings() {
        const p1Input = document.getElementById('p1');
        const p2Input = document.getElementById('p2');
        const pembahasInput = document.getElementById('pembahas');
        const totalDisplay = document.getElementById('total-percentage');
        
        if (!p1Input || !p2Input || !pembahasInput || !totalDisplay) return;
        if (p1Input.dataset.initialized === 'true') return;

        function updateTotal() {
            const p1 = parseInt(p1Input.value) || 0;
            const p2 = parseInt(p2Input.value) || 0;
            const pembahas = parseInt(pembahasInput.value) || 0;
            const total = p1 + p2 + pembahas;
            
            totalDisplay.textContent = total + '%';
            
            if (total === 100) {
                totalDisplay.className = 'font-bold text-green-600';
            } else {
                totalDisplay.className = 'font-bold text-red-600';
            }
        }
        
        p1Input.addEventListener('input', updateTotal);
        p2Input.addEventListener('input', updateTotal);
        pembahasInput.addEventListener('input', updateTotal);
        
        p1Input.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initNilaiPercentageSettings();
    else document.addEventListener('DOMContentLoaded', initNilaiPercentageSettings);
    window.addEventListener('page-loaded', initNilaiPercentageSettings);
})();
</script>
@endsection
