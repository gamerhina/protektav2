<?php

namespace App\Traits;

trait ProgresSkripsiTrait
{
    protected function generateProgresChartData($students)
    {
        $chartData = [
            'angkatan' => [],
            'progres' => [
                'Selesai' => [],
                'Kompre' => [],
                'Hasil' => [],
                'Proposal' => [],
                'Diajukan' => [],
                'Ditolak' => [],
            ],
            'ktw' => []
        ];

        $groupedByAngkatan = [];

        foreach ($students as $student) {
            $angkatan = '-';
            if ($student->npm && strlen($student->npm) >= 2) {
                $year2Digit = substr($student->npm, 0, 2);
                if (is_numeric($year2Digit)) {
                    $angkatan = '20' . $year2Digit;
                }
            }
            if ($angkatan === '-') continue;

            if (!isset($groupedByAngkatan[$angkatan])) {
                $groupedByAngkatan[$angkatan] = [
                    'total' => 0,
                    'tepat_waktu' => 0,
                    'status' => [
                        'Selesai' => 0,
                        'Kompre' => 0,
                        'Hasil' => 0,
                        'Proposal' => 0,
                        'Diajukan' => 0,
                        'Ditolak' => 0,
                        'Belum Ada' => 0,
                    ]
                ];
            }

            $groupedByAngkatan[$angkatan]['total']++;
            if ($student->isLulusTepatWaktu()) {
                $groupedByAngkatan[$angkatan]['tepat_waktu']++;
            }

            // Determine status
            $statusMapping = 'Belum Ada';
            $latestSeminar = $student->seminars->sortByDesc('created_at')->first();
            
            if ($latestSeminar) {
                if ($latestSeminar->status === 'ditolak') {
                    $statusMapping = 'Ditolak';
                } elseif ($latestSeminar->status === 'diajukan') {
                    $statusMapping = 'Diajukan';
                } elseif (in_array($latestSeminar->status, ['disetujui', 'belum_lengkap', 'selesai'])) {
                    $kode = $latestSeminar->seminarJenis?->kode;
                    if ($kode === 'SP') {
                        $statusMapping = 'Proposal';
                    } elseif ($kode === 'SH') {
                        $statusMapping = 'Hasil';
                    } elseif ($kode === 'US') {
                        $statusMapping = $latestSeminar->status === 'selesai' ? 'Selesai' : 'Kompre';
                    }
                }
            }

            if (isset($groupedByAngkatan[$angkatan]['status'][$statusMapping])) {
                $groupedByAngkatan[$angkatan]['status'][$statusMapping]++;
            }
        }

        ksort($groupedByAngkatan);

        foreach ($groupedByAngkatan as $angk => $data) {
            $chartData['angkatan'][] = $angk;
            foreach ($chartData['progres'] as $stat => &$arr) {
                $arr[] = $data['status'][$stat];
            }
            $pct = $data['total'] > 0 ? round(($data['tepat_waktu'] / $data['total']) * 100, 1) : 0;
            $chartData['ktw'][] = $pct;
        }

        return $chartData;
    }
}
