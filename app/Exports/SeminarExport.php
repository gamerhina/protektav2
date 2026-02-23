<?php

namespace App\Exports;

use App\Models\Seminar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeminarExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Seminar::with([
            'mahasiswa',
            'seminarJenis',
            'p1Dosen',
            'p2Dosen',
            'pembahasDosen',
            'nilai'
        ])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'No. Surat',
            'Mahasiswa',
            'NPM',
            'Jenis Seminar',
            'Judul',
            'Tanggal',
            'Waktu',
            'Lokasi',
            'Pembimbing 1',
            'Pembimbing 2',
            'Pembahas',
            'Status',
            'Nilai P1',
            'Nilai P2',
            'Nilai Pembahas',
            'Nilai Akhir',
            'Status Kelulusan',
            'Created At',
        ];
    }

    public function map($seminar): array
    {
        $nilaiP1 = $seminar->nilai->where('jenis_penilai', 'p1')->first()?->nilai_angka ?? 0;
        $nilaiP2 = $seminar->nilai->where('jenis_penilai', 'p2')->first()?->nilai_angka ?? 0;
        $nilaiPembahas = $seminar->nilai->where('jenis_penilai', 'pembahas')->first()?->nilai_angka ?? 0;
        $nilaiAkhir = $seminar->calculateWeightedScore();

        return [
            $seminar->id,
            $seminar->no_surat ?? '-',
            $seminar->mahasiswa?->nama ?? '-',
            $seminar->mahasiswa?->npm ?? '-',
            $seminar->seminarJenis?->nama ?? '-',
            strip_tags(html_entity_decode($seminar->judul ?? '-')),
            $seminar->tanggal ? $seminar->tanggal->format('d-m-Y') : '-',
            $seminar->waktu_mulai ?? '-',
            $seminar->lokasi ?? '-',
            $seminar->p1Dosen?->nama ?? '-',
            $seminar->p2Dosen?->nama ?? '-',
            $seminar->pembahasDosen?->nama ?? '-',
            ucfirst($seminar->status),
            $nilaiP1 > 0 ? $nilaiP1 : '-',
            $nilaiP2 > 0 ? $nilaiP2 : '-',
            $nilaiPembahas > 0 ? $nilaiPembahas : '-',
            $nilaiAkhir > 0 ? $nilaiAkhir : '-',
            $seminar->status === 'selesai' ? 'Lulus' : ($seminar->status === 'gagal' ? 'Tidak Lulus' : '-'),
            $seminar->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
