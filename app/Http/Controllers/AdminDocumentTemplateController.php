<?php

namespace App\Http\Controllers;

use App\Models\SeminarJenis;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDocumentTemplateController extends Controller
{
    public function index(SeminarJenis $seminarJenis)
    {
        $templates = $seminarJenis->documentTemplates()->orderBy('created_at', 'desc')->get();
        return view('admin.document-template.index', compact('seminarJenis', 'templates'));
    }

    public function create(SeminarJenis $seminarJenis)
    {
        $availableFields = DocumentTemplate::getAvailableFields($seminarJenis);
        return view('admin.document-template.create', compact('seminarJenis', 'availableFields'));
    }

    public function store(Request $request, SeminarJenis $seminarJenis)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:document_templates,kode',
            'template_html' => 'required|string',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'header_repeat' => 'nullable|boolean',
            'header_visibility' => 'required|string|in:all,first_only,except_first,custom',
            'header_custom_pages' => 'nullable|string',
            'signature_method' => 'required|in:manual,qr_code',
            'download_rules' => 'nullable|array',
        ]);

        $data = [
            'nama' => $request->nama,
            'kode' => $request->kode,
            'template_html' => $request->template_html,
            'header_repeat' => $request->boolean('header_repeat'),
            'header_visibility' => $request->input('header_visibility', 'all'),
            'header_custom_pages' => $request->input('header_custom_pages'),
            'signature_method' => $request->input('signature_method', 'qr_code'),
            'download_rules' => $request->input('download_rules', []),
            'aktif' => true,
            'qr_code_enabled' => false,
            'qr_code_position' => 'bottom-right',
            'qr_code_size' => 100,
            'paper_size' => 'A4',
        ];

        if ($request->hasFile('header_image')) {
            $path = $request->file('header_image')->store('template-headers', 'public');
            $data['header_image_path'] = $path;
        }

        $seminarJenis->documentTemplates()->create($data);

        return redirect()->route('admin.document-template.index', $seminarJenis)->with('success', 'Template berhasil dibuat.');
    }

    public function edit(SeminarJenis $seminarJenis, DocumentTemplate $template)
    {
        $availableFields = DocumentTemplate::getAvailableFields($seminarJenis);
        return view('admin.document-template.edit', compact('seminarJenis', 'template', 'availableFields'));
    }

    public function update(Request $request, SeminarJenis $seminarJenis, DocumentTemplate $template)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:document_templates,kode,' . $template->id,
            'template_html' => 'required|string',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'header_repeat' => 'nullable|boolean',
            'header_visibility' => 'required|string|in:all,first_only,except_first,custom',
            'header_custom_pages' => 'nullable|string',
            'signature_method' => 'required|in:manual,qr_code',
            'download_rules' => 'nullable|array',
        ]);

        $data = [
            'nama' => $request->nama,
            'kode' => $request->kode,
            'template_html' => $request->template_html,
            'header_repeat' => $request->boolean('header_repeat'),
            'header_visibility' => $request->input('header_visibility', 'all'),
            'header_custom_pages' => $request->input('header_custom_pages'),
            'signature_method' => $request->input('signature_method', 'qr_code'),
            'download_rules' => $request->input('download_rules', []),
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

        return redirect()->route('admin.document-template.index', $seminarJenis)->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(SeminarJenis $seminarJenis, DocumentTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.document-template.index', $seminarJenis)->with('success', 'Template berhasil dihapus.');
    }

    public function toggleAktif(SeminarJenis $seminarJenis, DocumentTemplate $template)
    {
        $template->update(['aktif' => !$template->aktif]);
        return redirect()->route('admin.document-template.index', $seminarJenis)->with('success', 'Status template berhasil diubah.');
    }
}
