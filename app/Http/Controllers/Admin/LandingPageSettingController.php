<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LandingPageSettingController extends Controller
{
    public function edit()
    {
        $settings = LandingPageSetting::firstOrCreate(['id' => 1], $this->defaults());

        $sliderReady = Schema::hasColumn('landing_page_settings', 'landing_slider_enabled')
            && Schema::hasColumn('landing_page_settings', 'landing_slider_interval_ms')
            && Schema::hasColumn('landing_page_settings', 'landing_background_slides');

        return view('admin.settings.landing', compact('settings', 'sliderReady'));
    }

    public function update(Request $request)
    {
        $settings = LandingPageSetting::firstOrCreate(['id' => 1], $this->defaults());

        $hasSliderColumns = Schema::hasColumn('landing_page_settings', 'landing_slider_enabled')
            && Schema::hasColumn('landing_page_settings', 'landing_slider_interval_ms')
            && Schema::hasColumn('landing_page_settings', 'landing_background_slides');

        if (!$hasSliderColumns && ($request->hasFile('landing_slides') || $request->has('landing_slider_enabled') || $request->has('landing_slider_interval_ms'))) {
            return back()->withInput()->with('error', 'Fitur slider header belum siap (kolom DB belum ada). Jalankan: php artisan migrate');
        }

        $data = $request->validate([
            'app_name' => 'nullable|string|max:150',
            'hero_super_title' => 'nullable|string|max:120',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'app_description' => 'nullable|string',
            'cta_label' => 'nullable|string|max:100',
            'cta_link' => 'nullable|string|max:255',
            'schedule_heading' => 'nullable|string|max:255',
            'primary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'accent_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'button_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_header_from' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_header_to' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_header_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_row_even_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_row_odd_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_row_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'table_border_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_overlay_from' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_overlay_to' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'hero_overlay_opacity' => ['nullable', 'numeric', 'between:0,1'],
            'landing_background_opacity' => ['nullable', 'numeric', 'between:0,1'],
            'content_background_opacity' => ['nullable', 'numeric', 'between:0,1'],
            'header_height' => ['nullable', 'numeric', 'between:300,800'],
            'landing_slider_enabled' => ['nullable', 'boolean'],
            'landing_slider_interval_ms' => ['nullable', 'integer', 'between:2000,20000'],
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'app_icon' => 'nullable|image|max:2048',
            'login_background' => 'nullable|image|max:4096',
            'landing_background' => 'nullable|image|max:4096',
            'landing_slides' => 'nullable|array',
            'landing_slides.*' => 'image|max:4096',
            'slides_existing' => 'nullable|array',
            'slides_existing.*.path' => 'nullable|string',
            'slides_existing.*.order' => 'nullable|integer|min:1|max:999',
            'slides_existing.*.remove' => 'nullable|boolean',
            'content_background' => 'nullable|image|max:4096',
        ]);

        // Normalize checkbox.
        $data['landing_slider_enabled'] = $request->boolean('landing_slider_enabled');

        foreach ([
            'logo' => 'logo_path',
            'favicon' => 'favicon_path',
            'app_icon' => 'app_icon_path',
            'login_background' => 'login_background_path',
            'landing_background' => 'landing_background_path',
            'content_background' => 'content_background_path',
        ] as $input => $column) {
            if ($request->hasFile($input)) {
                if ($settings->{$column}) {
                    Storage::disk('uploads')->delete($settings->{$column});
                }

                $data[$column] = $request->file($input)->store('branding', 'uploads');
            }
        }

        if ($hasSliderColumns) {
            // Header slides (slider)
            $existingSlides = $request->input('slides_existing', []);
            $nextSlides = [];

            if (is_array($existingSlides) && count($existingSlides) > 0) {
                foreach ($existingSlides as $row) {
                    $path = is_array($row) ? ($row['path'] ?? null) : null;
                    $remove = is_array($row) ? filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOLEAN) : false;
                    $order = is_array($row) ? (int)($row['order'] ?? 999) : 999;

                    if (!is_string($path) || $path === '') {
                        continue;
                    }

                    if ($remove) {
                        Storage::disk('uploads')->delete($path);
                        continue;
                    }

                    $nextSlides[] = ['path' => $path, 'order' => $order];
                }
            }

            if ($request->hasFile('landing_slides')) {
                foreach ((array) $request->file('landing_slides') as $file) {
                    if (!$file) {
                        continue;
                    }

                    $stored = $file->store('branding', 'uploads');
                    $nextSlides[] = ['path' => $stored, 'order' => 999];
                }
            }

            if (count($nextSlides) > 0) {
                usort($nextSlides, fn ($a, $b) => ($a['order'] <=> $b['order']));
                $data['landing_background_slides'] = array_values(array_map(fn ($s) => $s['path'], $nextSlides));
            } else {
                $data['landing_background_slides'] = [];
            }
        } else {
            // Prevent 500s when migrations haven't been applied yet.
            unset(
                $data['landing_slider_enabled'],
                $data['landing_slider_interval_ms'],
                $data['landing_background_slides']
            );
        }

        foreach ([
            'logo' => 'logo_path',
            'favicon' => 'favicon_path',
            'app_icon' => 'app_icon_path',
            'login_background' => 'login_background_path',
            'landing_background' => 'landing_background_path',
            'content_background' => 'content_background_path',
        ] as $input => $column) {
            if ($request->boolean('remove_' . $input)) {
                if ($settings->{$column}) {
                    Storage::disk('uploads')->delete($settings->{$column});
                }
                $data[$column] = null;
            }
        }

        if ($hasSliderColumns) {
            // If single landing background is removed, also ensure it's not lingering as the only slide.
            if ($request->boolean('remove_landing_background') && empty($data['landing_background_slides'])) {
                $data['landing_background_slides'] = [];
            }
        }

        $settings->fill($data);
        $settings->save();

        return redirect()->route('admin.settings.landing')->with('success', 'Landing page berhasil diperbarui.');
    }

    private function defaults(): array
    {
        return [
            'app_name' => 'Protekta Apps',
            'hero_super_title' => 'Protekta Apps',
            'hero_title' => 'Pusat Informasi Seminar Protekta',
            'hero_subtitle' => 'Monitoring jadwal, status, serta ekosistem seminar dalam satu dashboard responsif.',
            'app_description' => 'Platform terpadu untuk mengelola seminar akademik.',
            'cta_label' => 'Daftar Sekarang',
            'cta_link' => '/login',
            'schedule_heading' => 'Jadwal Seminar Terbaru',
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#0f172a',
            'accent_color' => '#f97316',
            'button_color' => '#0ea5e9',
            'table_header_from' => '#0f172a',
            'table_header_to' => '#2563eb',
            'table_header_text_color' => '#e2e8f0',
            'table_row_even_color' => '#f7f8fb',
            'table_row_odd_color' => '#fdfdfd',
            'table_row_text_color' => '#0f172a',
            'table_border_color' => '#e2e8f0',
            'header_overlay_from' => '#0f172a',
            'header_overlay_to' => '#172554',
            'hero_overlay_opacity' => 0.9,
            'landing_background_opacity' => 0.95,
            'content_background_opacity' => 0.92,
            'header_height' => 500,
            'landing_slider_enabled' => true,
            'landing_slider_interval_ms' => 6000,
            'landing_background_slides' => [],
        ];
    }
}
