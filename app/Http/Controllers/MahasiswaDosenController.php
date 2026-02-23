<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Support\PaginationHelper;
use Illuminate\Http\Request;

class MahasiswaDosenController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = PaginationHelper::resolvePerPage($request, 10);

        $query = Dosen::query();

        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('nama', 'like', $like)
                    ->orWhere('nip', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $sortFields = [
            'nama' => 'nama',
            'nip' => 'nip',
            'email' => 'email',
            'created_at' => 'created_at',
        ];

        $sort = (string) $request->query('sort', 'nama');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'nama';
        }

        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $dosens = $query
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('mahasiswa.dosen.index', compact('dosens', 'perPage', 'search', 'sort', 'direction'));
    }
}
