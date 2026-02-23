<?php

namespace App\Exports;

use App\Models\Surat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuratExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Surat::with([
            'jenis',
            'pemohonDosen',
            'pemohonMahasiswa',
            'mahasiswa'
        ])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'No Surat',
            'Jenis Surat',
            'Pemohon',
            'Tipe Pemohon',
            'Mahasiswa Terkait',
            'Tanggal Surat',
            'Tujuan',
            'Perihal',
            'Status',
            'Dikirim Pada',
            'Created At',
        ];
    }

    public function map($surat): array
    {
        $pemohonNama = '-';
        if ($surat->pemohon_type === 'dosen') {
            $pemohonNama = $surat->pemohonDosen?->nama ?? '-';
        } elseif ($surat->pemohon_type === 'mahasiswa') {
            $pemohonNama = $surat->pemohonMahasiswa?->nama ?? '-';
        }

        return [
            $surat->id,
            $surat->no_surat ?? '-',
            $surat->jenis?->nama ?? '-',
            $pemohonNama,
            ucfirst($surat->pemohon_type ?? '-'),
            $surat->mahasiswa ? ($surat->mahasiswa->nama . ' (' . $surat->mahasiswa->npm . ')') : '-',
            $surat->tanggal_surat ? $surat->tanggal_surat->format('d-m-Y') : '-',
            $surat->tujuan ?? '-',
            $surat->perihal ?? '-',
            ucfirst($surat->status),
            $surat->sent_at ? $surat->sent_at->format('d-m-Y H:i') : '-',
            $surat->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
