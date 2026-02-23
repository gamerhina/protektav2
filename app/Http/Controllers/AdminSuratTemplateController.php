<?php

namespace App\Http\Controllers;

use App\Models\SuratJenis;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSuratTemplateController extends Controller
{
    public function index(SuratJenis $suratJenis)
    {
        $templates = $suratJenis->templates()->orderBy('created_at', 'desc')->get();
        return view('admin.surat-template.index', compact('suratJenis', 'templates'));
    }

    public function create(SuratJenis $suratJenis)
    {
        $availableFields = SuratTemplate::getAvailableFields($suratJenis);
        return view('admin.surat-template.create', compact('suratJenis', 'availableFields'));
    }

    public function store(Request $request, SuratJenis $suratJenis)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'template_html' => 'required|string',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'header_repeat' => 'nullable|boolean',
            'header_visibility' => 'required|string|in:all,first_only,except_first,custom',
            'header_custom_pages' => 'nullable|string',
            'signature_method' => 'required|in:manual,qr_code',
        ]);

        $data = [
            'nama' => $request->nama,
            'template_html' => $request->template_html,
            'aktif' => true, // Default to active
            'qr_code_enabled' => false, // No longer used as fixed setting
            'qr_code_position' => 'bottom-right',
            'qr_code_size' => 100,
            'paper_size' => 'A4', // Default paper size
            'header_repeat' => $request->boolean('header_repeat'),
            'header_visibility' => $request->input('header_visibility', 'all'),
            'header_custom_pages' => $request->input('header_custom_pages'),
            'signature_method' => $request->input('signature_method', 'qr_code'),
        ];

        if ($request->hasFile('header_image')) {
            $path = $request->file('header_image')->store('template-headers', 'public');
            $data['header_image_path'] = $path;
        }

        // Deactivate other templates if this one is active (default is active)
        $suratJenis->templates()->update(['aktif' => false]);

        $suratJenis->templates()->create($data);

        return redirect()->route('admin.surat-template.index', $suratJenis)->with('success', 'Template berhasil dibuat.');
    }

    public function edit(SuratJenis $suratJenis, SuratTemplate $template)
    {
        $availableFields = SuratTemplate::getAvailableFields($suratJenis);
        return view('admin.surat-template.edit', compact('suratJenis', 'template', 'availableFields'));
    }

    public function update(Request $request, SuratJenis $suratJenis, SuratTemplate $template)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'template_html' => 'required|string',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'header_repeat' => 'nullable|boolean',
            'header_visibility' => 'required|string|in:all,first_only,except_first,custom',
            'header_custom_pages' => 'nullable|string',
            'signature_method' => 'required|in:manual,qr_code',
        ]);

        $data = [
            'nama' => $request->nama,
            'template_html' => $request->template_html,
            'header_repeat' => $request->boolean('header_repeat'),
            'header_visibility' => $request->input('header_visibility', 'all'),
            'header_custom_pages' => $request->input('header_custom_pages'),
            'signature_method' => $request->input('signature_method', 'qr_code'),
            // Keep other fields as they were or use defaults if they were null
        ];

        if ($request->hasFile('header_image')) {
            // Delete old image
            if ($template->header_image_path) {
                Storage::disk('public')->delete($template->header_image_path);
            }
            
            $path = $request->file('header_image')->store('template-headers', 'public');
            $data['header_image_path'] = $path;
        } elseif ($request->boolean('remove_header_image')) {
             if ($template->header_image_path) {
                Storage::disk('public')->delete($template->header_image_path);
            }
            $data['header_image_path'] = null;
        }

        $template->update($data);

        return redirect()->route('admin.surat-template.index', $suratJenis)->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(SuratJenis $suratJenis, SuratTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.surat-template.index', $suratJenis)->with('success', 'Template berhasil dihapus.');
    }

    public function toggleAktif(SuratJenis $suratJenis, SuratTemplate $template)
    {
        // Jika akan diaktifkan, nonaktifkan template lain untuk jenis surat ini
        if (!$template->aktif) {
            $suratJenis->templates()->where('id', '!=', $template->id)->update(['aktif' => false]);
        }
        
        $template->update(['aktif' => !$template->aktif]);
        
        return redirect()->route('admin.surat-template.index', $suratJenis)->with('success', 'Status template berhasil diubah.');
    }
}
