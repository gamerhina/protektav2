@use('App\Helpers\Terbilang')
@php


    // Pastikan nilai per evaluator diambil untuk dosen yang saat ini terdaftar di seminar
    $nilaiP1 = $seminar->nilai->first(function ($n) use ($seminar) {
        return $n->jenis_penilai === 'p1' && $n->dosen_id == $seminar->p1_dosen_id;
    });

    $nilaiP2 = $seminar->nilai->first(function ($n) use ($seminar) {
        return $n->jenis_penilai === 'p2' && $n->dosen_id == $seminar->p2_dosen_id;
    });

    $nilaiPembahas = $seminar->nilai->first(function ($n) use ($seminar) {
        return $n->jenis_penilai === 'pembahas' && $n->dosen_id == $seminar->pembahas_dosen_id;
    });
@endphp

<!-- Score Recapitulation Section -->
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Rekapitulasi Nilai Seminar</h3>
    
    @if($nilaiP1 || $nilaiP2 || $nilaiPembahas)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Pembimbing 1 -->
            <div class="border-2 border-blue-200 rounded-lg p-5 {{ $nilaiP1 ? 'bg-blue-50' : 'bg-gray-50' }}">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P1</span>
                    {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}
                </h3>
                
                @if($nilaiP1)
                    <!-- Assessment Aspects Breakdown -->
                    @if($nilaiP1->assessmentScores->count() > 0)
                        <div class="space-y-2 mb-4 border-t border-blue-200 pt-3 mt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                            @foreach($nilaiP1->assessmentScores as $score)
                                <div class="flex justify-between text-sm gap-2">
                                    <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                    <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="bg-white border-2 border-blue-300 rounded-lg p-4 mb-3">
                        <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                        <p class="text-3xl font-bold text-blue-700 break-words">{{ number_format($nilaiP1->nilai_angka, 2) }}</p>
                        <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                            {{ ucwords(Terbilang::convert($nilaiP1->nilai_angka)) }}
                        </p>
                    </div>
                    
                    @if($nilaiP1->catatan)
                        <div class="text-sm text-gray-700 bg-white p-3 rounded border border-blue-200">
                            <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                            <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP1->catatan }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                @endif
            </div>

            <!-- Pembimbing 2 -->
            <div class="border-2 border-green-200 rounded-lg p-5 {{ $nilaiP2 ? 'bg-green-50' : 'bg-gray-50' }}">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P2</span>
                    {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}
                </h3>
                
                @if($nilaiP2)
                    <!-- Assessment Aspects Breakdown -->
                    @if($nilaiP2->assessmentScores->count() > 0)
                        <div class="space-y-2 mb-4 border-t border-green-200 pt-3 mt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                            @foreach($nilaiP2->assessmentScores as $score)
                                <div class="flex justify-between text-sm gap-2">
                                    <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                    <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="bg-white border-2 border-green-300 rounded-lg p-4 mb-3">
                        <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                        <p class="text-3xl font-bold text-green-700 break-words">{{ number_format($nilaiP2->nilai_angka, 2) }}</p>
                        <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                            {{ ucwords(Terbilang::convert($nilaiP2->nilai_angka)) }}
                        </p>
                    </div>
                    
                    @if($nilaiP2->catatan)
                        <div class="text-sm text-gray-700 bg-white p-3 rounded border border-green-200">
                            <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                            <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP2->catatan }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                @endif
            </div>

            <!-- Pembahas -->
            <div class="border-2 border-purple-200 rounded-lg p-5 {{ $nilaiPembahas ? 'bg-purple-50' : 'bg-gray-50' }}">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">PMB</span>
                    {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
                </h3>
                
                @if($nilaiPembahas)
                    <!-- Assessment Aspects Breakdown -->
                    @if($nilaiPembahas->assessmentScores->count() > 0)
                        <div class="space-y-2 mb-4 border-t border-purple-200 pt-3 mt-3">
                            <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                            @foreach($nilaiPembahas->assessmentScores as $score)
                                <div class="flex justify-between text-sm gap-2">
                                    <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                    <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="bg-white border-2 border-purple-300 rounded-lg p-4 mb-3">
                        <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                        <p class="text-3xl font-bold text-purple-700 break-words">{{ number_format($nilaiPembahas->nilai_angka, 2) }}</p>
                        <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                            {{ ucwords(Terbilang::convert($nilaiPembahas->nilai_angka)) }}
                        </p>
                    </div>
                    
                    @if($nilaiPembahas->catatan)
                        <div class="text-sm text-gray-700 bg-white p-3 rounded border border-purple-200">
                            <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                            <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiPembahas->catatan }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                @endif
            </div>
        </div>

        <!-- Final Average Score (if all evaluated) -->
        @if($nilaiP1 && $nilaiP2 && $nilaiPembahas)
            @php
                // Get weight percentages from seminar type
                $p1Percentage = $seminar->seminarJenis->p1_weight ?? 35;
                $p2Percentage = $seminar->seminarJenis->p2_weight ?? 35;
                $pembahasPercentage = $seminar->seminarJenis->pembahas_weight ?? 30;
                
                $finalScore = ($nilaiP1->nilai_angka * $p1Percentage / 100) +
                              ($nilaiP2->nilai_angka * $p2Percentage / 100) +
                              ($nilaiPembahas->nilai_angka * $pembahasPercentage / 100);
            @endphp
            
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-400 rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-shrink-0">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Nilai Akhir Keseluruhan</h4>
                        <p class="text-sm text-gray-600">
                            P1 ({{ $p1Percentage }}%) + P2 ({{ $p2Percentage }}%) + Pembahas ({{ $pembahasPercentage }}%)
                        </p>
                    </div>
                    <div class="text-left md:text-right flex-shrink min-w-0">
                        <p class="text-4xl md:text-5xl font-bold text-orange-600 break-words">{{ number_format($finalScore, 2) }}</p>
                        <p class="text-sm md:text-base italic text-gray-700 mt-2 break-words overflow-wrap-anywhere">
                            {{ ucwords(Terbilang::convert($finalScore)) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-gray-600">Belum ada penilaian untuk seminar ini.</p>
        </div>
    @endif
</div>
