<?php

namespace App\Http\Controllers;

use App\Models\SuratRole;
use Illuminate\Http\Request;

class SuratRoleController extends Controller
{
    /**
     * Display a listing of surat roles
     */
    public function index()
    {
        $roles = SuratRole::ordered()->paginate(15);
        
        return view('admin.surat-role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $dosens = \App\Models\Dosen::orderBy('nama')->get();
        return view('admin.surat-role.create', compact('dosens'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:surat_roles,kode',
            'deskripsi' => 'nullable|string',
            'dosen_id' => 'nullable|exists:dosen,id',
            'warna' => 'nullable|string|max:7',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['kode'] = strtoupper($validated['kode']);

        SuratRole::create($validated);

        return redirect()
            ->route('admin.surat-role.index')
            ->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(SuratRole $suratRole)
    {
        $dosens = \App\Models\Dosen::orderBy('nama')->get();
        return view('admin.surat-role.edit', compact('suratRole', 'dosens'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, SuratRole $suratRole)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:surat_roles,kode,' . $suratRole->id,
            'deskripsi' => 'nullable|string',
            'dosen_id' => 'nullable|exists:dosen,id',
            'warna' => 'nullable|string|max:7',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['kode'] = strtoupper($validated['kode']);

        $suratRole->update($validated);

        return redirect()
            ->route('admin.surat-role.index')
            ->with('success', 'Role berhasil diperbarui!');
    }

    /**
     * Remove the specified role
     */
    public function destroy(SuratRole $suratRole)
    {
        // Check if role is being used in workflow steps (by name) or approvals (by ID)
        $isUsedInWorkflow = \App\Models\SuratRoleAssignment::where('role_nama', $suratRole->nama)->exists();
        $isUsedInApprovals = $suratRole->approvals()->exists();

        if ($isUsedInWorkflow || $isUsedInApprovals) {
            return redirect()
                ->back()
                ->with('error', 'Role tidak dapat dihapus karena masih digunakan dalam alur persetujuan atau dokumen yang ada!');
        }

        $suratRole->delete();

        return redirect()
            ->route('admin.surat-role.index')
            ->with('success', 'Role berhasil dihapus!');
    }

    /**
     * Toggle role active status
     */
    public function toggleStatus(SuratRole $suratRole)
    {
        $suratRole->update([
            'is_active' => !$suratRole->is_active
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status role berhasil diubah!');
    }
}
