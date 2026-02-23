<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Show the nilai percentage settings page
     */
    public function showNilaiPercentage()
    {
        $nilaiPercentageConfig = Setting::getValue('nilai_percentage_config', [
            'p1' => 40,
            'p2' => 30,
            'pembahas' => 30
        ]);

        return view('admin.settings.nilai-percentage', compact('nilaiPercentageConfig'));
    }

    /**
     * Update the nilai percentage settings
     */
    public function updateNilaiPercentage(Request $request)
    {
        $request->validate([
            'p1' => 'required|integer|min:0|max:100',
            'p2' => 'required|integer|min:0|max:100',
            'pembahas' => 'required|integer|min:0|max:100',
        ]);

        // Validate that the sum equals 100%
        $total = $request->p1 + $request->p2 + $request->pembahas;
        if ($total != 100) {
            return redirect()->back()->withErrors(['total' => 'Total persentase harus 100%. Saat ini totalnya: ' . $total . '%']);
        }

        $percentageConfig = [
            'p1' => $request->p1,
            'p2' => $request->p2,
            'pembahas' => $request->pembahas
        ];

        Setting::setValue('nilai_percentage_config', $percentageConfig);

        return redirect()->back()->with('success', 'Konfigurasi persentase nilai berhasil diperbarui!');
    }
}
