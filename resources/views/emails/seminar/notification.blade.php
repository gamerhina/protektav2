@use('App\Helpers\Terbilang')
<x-mail::message>
@if($emailType === 'invitation')
# Undangan Seminar
Anda telah diundang untuk menghadiri seminar berikut:

<x-mail::panel>
**Judul:** {!! strip_tags($seminar->judul) !!}<br>
**Jenis:** {{ $seminar->seminarJenis->nama ?? 'N/A' }}<br>
**Tanggal:** {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}<br>
**Waktu:** {{ $seminar->waktu_mulai }}<br>
**Lokasi:** {{ $seminar->lokasi }}<br>
**Mahasiswa:** {{ $seminar->mahasiswa->nama ?? 'N/A' }} ({{ $seminar->mahasiswa->npm ?? 'N/A' }})<br>
**Pembimbing 1:** {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}<br>
**Pembimbing 2:** {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}<br>
**Pembahas:** {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
</x-mail::panel>

Silakan konfirmasi kehadiran Anda dan bersiap-siap untuk mengevaluasi seminar ini.

@elseif($emailType === 'grade')
# Pengumuman Nilai Seminar
Berikut adalah nilai dari seminar yang telah diselesaikan:

<x-mail::panel>
**Judul:** {!! strip_tags($seminar->judul) !!}<br>
**Jenis:** {{ $seminar->seminarJenis->nama ?? 'N/A' }}<br>
**Tanggal:** {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}<br>
**Mahasiswa:** {{ $seminar->mahasiswa->nama ?? 'N/A' }} ({{ $seminar->mahasiswa->npm ?? 'N/A' }})<br>

---

@php
    $nilaiP1 = $seminar->nilai->firstWhere('jenis_penilai', 'p1');
    $nilaiP2 = $seminar->nilai->firstWhere('jenis_penilai', 'p2');
    $nilaiPembahas = $seminar->nilai->firstWhere('jenis_penilai', 'pembahas');
@endphp

**NILAI PEMBIMBING 1**<br>
Dosen: {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}<br>
@if($nilaiP1)
Nilai Akhir: **{{ $nilaiP1->nilai_angka }}**<br>
Terbilang: *{{ ucwords(Terbilang::convert($nilaiP1->nilai_angka)) }}*
@else
Status: Belum dinilai
@endif

---

**NILAI PEMBIMBING 2**<br>
Dosen: {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}<br>
@if($nilaiP2)
Nilai Akhir: **{{ $nilaiP2->nilai_angka }}**<br>
Terbilang: *{{ ucwords(Terbilang::convert($nilaiP2->nilai_angka)) }}*
@else
Status: Belum dinilai
@endif

---

**NILAI PEMBAHAS**<br>
Dosen: {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}<br>
@if($nilaiPembahas)
Nilai Akhir: **{{ $nilaiPembahas->nilai_angka }}**<br>
Terbilang: *{{ ucwords(Terbilang::convert($nilaiPembahas->nilai_angka)) }}*
@else
Status: Belum dinilai
@endif
</x-mail::panel>

@elseif($emailType === 'borang')
# Borang Penilaian Seminar
Berikut adalah borang penilaian untuk seminar:

<x-mail::panel>
**Judul:** {!! strip_tags($seminar->judul) !!}<br>
**Jenis:** {{ $seminar->seminarJenis->nama ?? 'N/A' }}<br>
**Tanggal:** {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}<br>
**Mahasiswa:** {{ $seminar->mahasiswa->nama ?? 'N/A' }} ({{ $seminar->mahasiswa->npm ?? 'N/A' }})<br>
</x-mail::panel>

Silakan lengkapi borang penilaian untuk seminar ini.

@endif

<x-mail::subcopy>
Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi administrator sistem.
</x-mail::subcopy>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
