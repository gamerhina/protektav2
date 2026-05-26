<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaGraduationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $dosenId;

    public function __construct($dosenId = null)
    {
        $this->dosenId = $dosenId;
    }

    public function collection()
    {
        $query = Mahasiswa::with('seminars.seminarJenis');
        
        if ($this->dosenId) {
            $query->where('pembimbing_akademik_id', $this->dosenId);
        }
        
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Mahasiswa',
            'NPM',
            'Angkatan',
            'Tanggal Mulai Kuliah',
            'Tanggal Kelulusan / Wisuda',
            'Tipe Tanggal Lulus',
            'Status Kelulusan',
            'Durasi Studi (Bulan)'
        ];
    }

    protected $rowNumber = 0;

    public function map($student): array
    {
        $this->rowNumber++;
        $startDate = $student->getTanggalMulaiKuliah();
        $endDate = $student->getTanggalLulus();
        $isTepatWaktu = $student->isLulusTepatWaktu();
        
        $hasCompletedSkripsi = $student->seminars()
            ->whereHas('seminarJenis', function ($query) {
                $query->where('kode', 'US');
            })
            ->where('status', 'selesai')
            ->exists();
            
        $type = 'Belum Lulus';
        if ($student->tanggal_lulus_manual) {
            $type = 'Input Manual';
        } elseif ($hasCompletedSkripsi) {
            $type = 'Ujian Skripsi';
        }

        // Determine status string
        $status = '-';
        if ($startDate) {
            if ($endDate) {
                $status = $isTepatWaktu ? 'Tepat Waktu' : 'Terlambat';
            } else {
                $limitDate = $startDate->copy()->addYears(4);
                $now = now()->startOfDay();
                $status = $now->lessThanOrEqualTo($limitDate) ? 'Aktif (Aman)' : 'Aktif (Kritis)';
            }
        }

        // Calculate duration in months
        $duration = '-';
        if ($startDate && $endDate) {
            $duration = round($startDate->diffInMonths($endDate), 1) . ' Bulan';
        } elseif ($startDate) {
            $duration = round($startDate->diffInMonths(now()), 1) . ' Bulan (Berjalan)';
        }

        return [
            $this->rowNumber,
            $student->nama,
            $student->npm,
            $startDate ? $startDate->year : '-',
            $startDate ? $startDate->format('d-M-Y') : '-',
            $endDate ? $endDate->format('d-M-Y') : '-',
            $type,
            $status,
            $duration
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
