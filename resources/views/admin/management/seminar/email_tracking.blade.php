<!-- Email Tracking Section -->
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Pengiriman Email</h3>
    
    @php
        $adminEmail = auth()->guard('admin')->user()->email ?? 'admin@example.com';
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Undangan Email -->
        <div class="border border-gray-200 rounded-lg p-4 {{ $seminar->undangan_sent_at ? 'bg-green-50' : 'bg-gray-50' }}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-700">Email Undangan</h4>
                @if($seminar->undangan_sent_at)
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Terkirim</span>
                @else
                    <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded-full">Belum Dikirim</span>
                @endif
            </div>
            
            @if($seminar->undangan_sent_at)
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Waktu:</strong> {{ $seminar->undangan_sent_at->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}
                </p>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Pengirim:</strong> {{ $adminEmail }}
                </p>
                
                @if($seminar->undangan_recipients && count($seminar->undangan_recipients) > 0)
                    <div class="text-sm">
                        <p class="font-medium text-gray-700 mb-1">Penerima ({{ count($seminar->undangan_recipients) }}):</p>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            @foreach($seminar->undangan_recipients as $recipient)
                                <div class="bg-white rounded px-2 py-1 border border-green-200">
                                    <p class="text-xs font-medium text-gray-700">{{ $recipient['name'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $recipient['email'] ?? 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 italic">Belum ada pengiriman</p>
            @endif
        </div>

        <!-- Nilai Email -->
        <div class="border border-gray-200 rounded-lg p-4 {{ $seminar->nilai_sent_at ? 'bg-blue-50' : 'bg-gray-50' }}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-700">Email Nilai</h4>
                @if($seminar->nilai_sent_at)
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Terkirim</span>
                @else
                    <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded-full">Belum Dikirim</span>
                @endif
            </div>
            
            @if($seminar->nilai_sent_at)
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Waktu:</strong> {{ $seminar->nilai_sent_at->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}
                </p>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Pengirim:</strong> {{ $adminEmail }}
                </p>
                
                @if($seminar->nilai_recipients && count($seminar->nilai_recipients) > 0)
                    <div class="text-sm">
                        <p class="font-medium text-gray-700 mb-1">Penerima ({{ count($seminar->nilai_recipients) }}):</p>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            @foreach($seminar->nilai_recipients as $recipient)
                                <div class="bg-white rounded px-2 py-1 border border-blue-200">
                                    <p class="text-xs font-medium text-gray-700">{{ $recipient['name'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $recipient['email'] ?? 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 italic">Belum ada pengiriman</p>
            @endif
        </div>

        <!-- Borang Email -->
        <div class="border border-gray-200 rounded-lg p-4 {{ $seminar->borang_sent_at ? 'bg-purple-50' : 'bg-gray-50' }}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-700">Email Borang</h4>
                @if($seminar->borang_sent_at)
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">Terkirim</span>
                @else
                    <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded-full">Belum Dikirim</span>
                @endif
            </div>
            
            @if($seminar->borang_sent_at)
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Waktu:</strong> {{ $seminar->borang_sent_at->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}
                </p>
                <p class="text-sm text-gray-600 mb-2">
                    <strong>Pengirim:</strong> {{ $adminEmail }}
                </p>
                
                @if($seminar->borang_recipients && count($seminar->borang_recipients) > 0)
                    <div class="text-sm">
                        <p class="font-medium text-gray-700 mb-1">Penerima ({{ count($seminar->borang_recipients) }}):</p>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            @foreach($seminar->borang_recipients as $recipient)
                                <div class="bg-white rounded px-2 py-1 border border-purple-200">
                                    <p class="text-xs font-medium text-gray-700">{{ $recipient['name'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $recipient['email'] ?? 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 italic">Belum ada pengiriman</p>
            @endif
        </div>
    </div>
</div>

<!-- Email Action Buttons -->
<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Pengiriman Email</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Send Undangan -->
        <div class="border-2 border-green-200 rounded-lg p-4 {{ $seminar->undangan_sent_at ? 'bg-green-50' : 'bg-white' }}">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email Undangan
            </h4>
            @if($seminar->undangan_sent_at)
                <p class="text-xs text-green-700 mb-3">✓ Sudah dikirim</p>
            @else
                <p class="text-xs text-gray-500 mb-3">Belum dikirim</p>
            @endif
            <form action="{{ route('admin.seminar.send-email', $seminar->id) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="invitation">
                <input type="hidden" name="recipients[]" value="mahasiswa">
                <input type="hidden" name="recipients[]" value="p1">
                <input type="hidden" name="recipients[]" value="p2">
                <input type="hidden" name="recipients[]" value="pembahas">
                <button type="submit" class="w-full btn-pill btn-pill-success text-sm" onclick="return confirm('Kirim email undangan kepada 4 penerima (Mahasiswa, P1, P2, Pembahas)?')">
                    {{ $seminar->undangan_sent_at ? 'Kirim Ulang' : 'Kirim' }}
                </button>
            </form>
        </div>

        <!-- Send Nilai -->
        <div class="border-2 border-blue-200 rounded-lg p-4 {{ $seminar->nilai_sent_at ? 'bg-blue-50' : 'bg-white' }}">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                Email Nilai
            </h4>
            @if($seminar->nilai_sent_at)
                <p class="text-xs text-blue-700 mb-3">✓ Sudah dikirim</p>
            @else
                <p class="text-xs text-gray-500 mb-3">Belum dikirim</p>
            @endif
            <form action="{{ route('admin.seminar.send-email', $seminar->id) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="grade">
                <input type="hidden" name="recipients[]" value="mahasiswa">
                <input type="hidden" name="recipients[]" value="p1">
                <input type="hidden" name="recipients[]" value="p2">
                <input type="hidden" name="recipients[]" value="pembahas">
                <button type="submit" class="w-full btn-pill btn-pill-info text-sm" onclick="return confirm('Kirim email nilai kepada 4 penerima (Mahasiswa, P1, P2, Pembahas)?')">
                    {{ $seminar->nilai_sent_at ? 'Kirim Ulang' : 'Kirim' }}
                </button>
            </form>
        </div>

        <!-- Send Borang -->
        <div class="border-2 border-purple-200 rounded-lg p-4 {{ $seminar->borang_sent_at ? 'bg-purple-50' : 'bg-white' }}">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Email Borang
            </h4>
            @if($seminar->borang_sent_at)
                <p class="text-xs text-purple-700 mb-3">✓ Sudah dikirim</p>
            @else
                <p class="text-xs text-gray-500 mb-3">Belum dikirim</p>
            @endif
            <form action="{{ route('admin.seminar.send-email', $seminar->id) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="borang">
                <input type="hidden" name="recipients[]" value="mahasiswa">
                <input type="hidden" name="recipients[]" value="p1">
                <input type="hidden" name="recipients[]" value="p2">
                <input type="hidden" name="recipients[]" value="pembahas">
                <button type="submit" class="w-full btn-pill btn-pill-purple text-sm" onclick="return confirm('Kirim email borang kepada 4 penerima (Mahasiswa, P1, P2, Pembahas)?')">
                    {{ $seminar->borang_sent_at ? 'Kirim Ulang' : 'Kirim' }}
                </button>
            </form>
        </div>

        <!-- Send All Emails -->
        <div class="border-2 border-orange-200 rounded-lg p-4 bg-orange-50">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                </svg>
                Kirim Semua
            </h4>
            <p class="text-xs text-gray-600 mb-3">
                @php
                    $allSent = $seminar->undangan_sent_at && $seminar->nilai_sent_at && $seminar->borang_sent_at;
                @endphp
                @if($allSent)
                    ✓ Semua email sudah dikirim
                @else
                    Kirim 3 email sekaligus
                @endif
            </p>
            <form action="{{ route('admin.seminar.send-all-emails', $seminar->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full btn-pill btn-pill-warning text-sm font-medium" onclick="return confirm('Kirim SEMUA email (Undangan + Nilai + Borang) kepada semua penerima?\n\nIni akan mengirim 3 jenis email sekaligus.')">
                    {{ $allSent ? 'Kirim Ulang Semua' : 'Kirim Semua' }}
                </button>
            </form>
        </div>
    </div>
</div>
