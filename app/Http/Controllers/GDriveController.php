<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GdriveFolder;
use App\Support\PaginationHelper;

class GDriveController extends Controller
{
    use Concerns\DetectsImpersonation;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GdriveFolder::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
                    ->orWhere('keterangan', 'like', $like);
            });
        }

        $sortFields = [
            'nama' => 'nama',
            'keterangan' => 'keterangan',
            'created_at' => 'created_at',
        ];

        $sort = $request->input('sort', 'created_at');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $perPage = PaginationHelper::resolvePerPage($request);

        $folders = $query
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        $isAdmin = $this->isEffectivelyAdmin();

        $viewPath = $isAdmin ? 'admin.gdrive.index' : 'dosen.gdrive.index';
        
        return view($viewPath, compact('folders', 'perPage', 'isAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.gdrive.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'link' => 'required|url',
            'keterangan' => 'nullable|string',
        ]);

        $folder = new GdriveFolder();
        $folder->nama = $request->nama;
        $folder->link = $request->link;
        $folder->keterangan = $request->keterangan;
        $folder->semester = 'ganjil'; // Default value
        $folder->tahun_akademik = date('Y'); // Default value
        $folder->folder_id = ''; // Default value for removed field
        $folder->save();

        return redirect()->route('admin.gdrive.index')->with('success', 'GDrive folder berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GdriveFolder $gdriveFolder)
    {
        return view('admin.gdrive.edit', compact('gdriveFolder'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GdriveFolder $gdriveFolder)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'link' => 'required|url',
            'keterangan' => 'nullable|string',
        ]);

        $gdriveFolder->nama = $request->nama;
        $gdriveFolder->link = $request->link;
        $gdriveFolder->keterangan = $request->keterangan;
        $gdriveFolder->save();

        return redirect()->route('admin.gdrive.index')->with('success', 'GDrive folder berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GdriveFolder $gdriveFolder)
    {
        $gdriveFolder->delete();

        return redirect()->route('admin.gdrive.index')->with('success', 'GDrive folder berhasil dihapus!');
    }
}