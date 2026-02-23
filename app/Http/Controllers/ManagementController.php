<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Admin;
use App\Models\Seminar;
use App\Models\SeminarJenis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Support\PaginationHelper;
use App\Exports\SeminarExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\EvaluatorAssignedNotification;
use App\Models\DocumentTemplate;
use App\Services\UrlShortenerService;

use App\Services\PdfGeneratorService;
use Illuminate\Support\Facades\Mail;

class ManagementController extends Controller
{
    /**
     * Show all dosens
     */
    public function indexDosen(Request $request)
    {
        $query = Dosen::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
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

        $sort = $request->input('sort', 'nama');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'nama';
        }

        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = PaginationHelper::resolvePerPage($request);

        $dosens = $query
            ->withCount('mahasiswaBimbinganAkademik')
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.management.dosen.index', compact('dosens', 'perPage'));
    }

    /**
     * Show form to create new dosen
     */
    public function createDosen()
    {
        return view('admin.management.dosen.create');
    }

    /**
     * Store new dosen
     */
    public function storeDosen(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:dosen,nip',
            'email' => 'required|email|unique:dosen,email',
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = [
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
            'password' => Hash::make($request->password),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('photos/dosen', 'uploads');
        }

        Dosen::create($data);

        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil ditambahkan!');
    }

    /**
     * Show all mahasiswas
     */
    public function indexMahasiswa(Request $request)
    {
        $query = Mahasiswa::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
                    ->orWhere('npm', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $paId = $request->input('pembimbing_akademik_id');
        if ($paId) {
            $query->where('pembimbing_akademik_id', $paId);
        }

        $sortFields = [
            'nama' => 'nama',
            'npm' => 'npm',
            'email' => 'email',
            'created_at' => 'created_at',
        ];

        $sort = $request->input('sort', 'nama');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'nama';
        }

        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = PaginationHelper::resolvePerPage($request);

        $selectedPA = null;
        if ($paId) {
            $selectedPA = Dosen::find($paId);
        }

        $mahasiswas = $query
            ->with('pembimbingAkademik')
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.management.mahasiswa.index', compact('mahasiswas', 'perPage', 'selectedPA'));
    }

    /**
     * Show PA assignments management
     */
    public function indexPA(Request $request)
    {
        $query = Dosen::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
                    ->orWhere('nip', 'like', $like);
            });
        }

        $dosens = $query
            ->withCount('mahasiswaBimbinganAkademik')
            ->orderBy('nama', 'asc')
            ->paginate(PaginationHelper::resolvePerPage($request))
            ->withQueryString();

        return view('admin.management.pa.index', compact('dosens'));
    }

    /**
     * Show form to create new mahasiswa
     */
    public function createMahasiswa()
    {
        $dosens = Dosen::orderBy('nama')->get();
        return view('admin.management.mahasiswa.create', compact('dosens'));
    }

    /**
     * Store new mahasiswa
     */
    public function storeMahasiswa(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'npm' => 'required|string|max:255|unique:mahasiswa,npm',
            'email' => 'required|email|unique:mahasiswa,email',
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'pembimbing_akademik_id' => 'nullable|exists:dosen,id',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = [
            'nama' => $request->nama,
            'npm' => $request->npm,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
            'pembimbing_akademik_id' => $request->pembimbing_akademik_id,
            'password' => Hash::make($request->password),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('photos/mahasiswa', 'uploads');
        }

        Mahasiswa::create($data);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    /**
     * Show all admins
     */
    public function indexAdmin(Request $request)
    {
        $query = Admin::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($builder) use ($like) {
                $builder->where('nama', 'like', $like)
                    ->orWhere('nip', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('hp', 'like', $like)
                    ->orWhere('wa', 'like', $like);
            });
        }

        $sortFields = [
            'nama' => 'nama',
            'nip' => 'nip',
            'email' => 'email',
            'hp' => 'hp',
            'created_at' => 'created_at',
        ];

        $sort = $request->input('sort', 'nama');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'nama';
        }

        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = PaginationHelper::resolvePerPage($request);

        $admins = $query
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.management.admin.index', compact('admins', 'perPage'));
    }

    /**
     * Show form to create new admin
     */
    public function createAdmin()
    {
        return view('admin.management.admin.create');
    }

    /**
     * Store new admin
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:admins,nip',
            'email' => 'required|email|unique:admins,email',
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = [
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
            'password' => Hash::make($request->password),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('photos/admin', 'uploads');
        }

        $admin = Admin::create($data);

        // Assign admin role
        $admin->assignRole('admin');

        return redirect()->route('admin.admins.index')->with('success', 'Admin berhasil ditambahkan!');
    }

    /**
     * Show form to edit a dosen
     */
    public function editDosen(Dosen $dosen)
    {
        return view('admin.management.dosen.edit', compact('dosen'));
    }

    /**
     * Update a dosen
     */
    public function updateDosen(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:dosen,nip,' . $dosen->id,
            'email' => 'required|email|unique:dosen,email,' . $dosen->id,
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($dosen->foto) {
                Storage::disk('uploads')->delete($dosen->foto);
            }

            $data['foto'] = $request->file('foto')->store('photos/dosen', 'uploads');
        }

        $dosen->update($data);

        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil diperbarui!');
    }

    /**
     * Delete a dosen
     */
    public function destroyDosen(Dosen $dosen)
    {
        // Delete photo if exists
        if ($dosen->foto) {
            Storage::disk('uploads')->delete($dosen->foto);
        }

        $dosen->delete();
        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil dihapus!');
    }

    /**
     * Show form to edit a mahasiswa
     */
    public function editMahasiswa(Mahasiswa $mahasiswa)
    {
        $dosens = Dosen::orderBy('nama')->get();
        return view('admin.management.mahasiswa.edit', compact('mahasiswa', 'dosens'));
    }

    /**
     * Update a mahasiswa
     */
    public function updateMahasiswa(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'npm' => 'required|string|max:255|unique:mahasiswa,npm,' . $mahasiswa->id,
            'email' => 'required|email|unique:mahasiswa,email,' . $mahasiswa->id,
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
            'pembimbing_akademik_id' => 'nullable|exists:dosen,id',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'nama' => $request->nama,
            'npm' => $request->npm,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
            'pembimbing_akademik_id' => $request->pembimbing_akademik_id,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($mahasiswa->foto) {
                Storage::disk('uploads')->delete($mahasiswa->foto);
            }

            $data['foto'] = $request->file('foto')->store('photos/mahasiswa', 'uploads');
        }

        $mahasiswa->update($data);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui!');
    }

    /**
     * Delete a mahasiswa
     */
    public function destroyMahasiswa(Mahasiswa $mahasiswa)
    {
        // Delete photo if exists
        if ($mahasiswa->foto) {
            Storage::disk('uploads')->delete($mahasiswa->foto);
        }

        $mahasiswa->delete();
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus!');
    }

    /**
     * Show form to edit an admin
     */
    public function editAdmin(Admin $admin)
    {
        return view('admin.management.admin.edit', compact('admin'));
    }

    /**
     * Update an admin
     */
    public function updateAdmin(Request $request, Admin $admin)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:admins,nip,' . $admin->id,
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'wa' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:800',
        ]);

        $data = [
            'nama' => $request->nama,
            'nip' => $request->nip,
            'email' => $request->email,
            'wa' => $request->wa,
            'hp' => $request->hp,
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($admin->foto) {
                Storage::disk('uploads')->delete($admin->foto);
            }

            $data['foto'] = $request->file('foto')->store('photos/admin', 'uploads');
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Admin berhasil diperbarui!');
    }

    /**
     * Delete an admin
     */
    public function destroyAdmin(Admin $admin)
    {
        // Delete photo if exists
        if ($admin->foto) {
            Storage::disk('uploads')->delete($admin->foto);
        }

        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin berhasil dihapus!');
    }

    public function exportSeminar()
    {
        return Excel::download(new SeminarExport, 'rekap_seminar_' . date('Y-m-d_H-i') . '.xlsx');
    }

    /**
     * Show all seminars
     */
    public function indexSeminar(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $sortFields = [
            'tanggal' => 'seminars.tanggal',
            'no_surat' => 'seminars.no_surat',
            'status' => 'seminars.status',
            'mahasiswa' => 'mahasiswa.nama',
            'jenis' => 'seminar_jenis.nama',
            'created_at' => 'seminars.created_at',
        ];

        $sort = $request->input('sort', 'tanggal');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'tanggal';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $seminars = Seminar::with(['mahasiswa', 'seminarJenis', 'nilai', 'p1Dosen', 'p2Dosen', 'pembahasDosen'])
            ->select('seminars.*')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'seminars.mahasiswa_id')
            ->leftJoin('seminar_jenis', 'seminar_jenis.id', '=', 'seminars.seminar_jenis_id')
            ->leftJoin('dosen as p1', 'p1.id', '=', 'seminars.p1_dosen_id')
            ->leftJoin('dosen as p2', 'p2.id', '=', 'seminars.p2_dosen_id')
            ->leftJoin('dosen as pembahas', 'pembahas.id', '=', 'seminars.pembahas_dosen_id');

        if ($search !== '') {
            $like = "%{$search}%";
            $seminars->where(function ($builder) use ($like) {
                $builder->where('seminars.no_surat', 'like', $like)
                    ->orWhere('seminars.judul', 'like', $like)
                    ->orWhere('mahasiswa.nama', 'like', $like)
                    ->orWhere('mahasiswa.npm', 'like', $like)
                    ->orWhere('seminar_jenis.nama', 'like', $like)
                    ->orWhere('seminars.status', 'like', $like)
                    ->orWhere('p1.nama', 'like', $like)
                    ->orWhere('p2.nama', 'like', $like)
                    ->orWhere('pembahas.nama', 'like', $like)
                    ->orWhere('seminars.p1_nama', 'like', $like)
                    ->orWhere('seminars.p2_nama', 'like', $like)
                    ->orWhere('seminars.pembahas_nama', 'like', $like);
            });
        }

        $perPage = PaginationHelper::resolvePerPage($request, 20);

    if ($request->filled('status_filter')) {
        $status = $request->input('status_filter');
        $seminars->where('seminars.status', $status);
    }

        $seminars = $seminars
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.management.seminar.index', compact('seminars', 'perPage'));
    }

    /**
     * Show a single seminar detail
     */
    public function showSeminar(Seminar $seminar)
    {
        $seminar->load([
            'mahasiswa',
            'seminarJenis',
            'p1Dosen',
            'p2Dosen',
            'pembahasDosen',
            'nilai.assessmentScores.assessmentAspect',
            'signatures'
        ]);
        $seminar->refreshCompletionStatus();

    return view('admin.management.seminar.show', compact('seminar'));
    }

    /**
     * Show form to create a new seminar
     */
    public function createSeminar()
    {
        $seminarJenis = SeminarJenis::all();
        $mahasiswas = Mahasiswa::all();
        $dosens = Dosen::all();
        $defaultJenisId = $seminarJenis->first()->id ?? null;
        $defaultNoSurat = $defaultJenisId ? $this->generateDefaultNoSurat($defaultJenisId) : $this->generateDefaultNoSurat();

        return view('admin.management.seminar.create', compact('seminarJenis', 'mahasiswas', 'dosens', 'defaultNoSurat'));
    }

    public function previewSeminarPdf(Seminar $seminar, DocumentTemplate $template)
    {
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        
        $availableFields = DocumentTemplate::getAvailableFields();
        $availableTags = [];
        foreach ($availableFields as $group) {
            foreach ($group as $key => $label) {
                $availableTags[] = $key;
            }
        }

        $finalData = $pdfService->getSeminarData($seminar);

        // Map tag types for the preview UI
        $tagTypes = [];
        foreach ($availableTags as $tag) {
            if (str_contains($tag, 'signature') || str_contains($tag, 'qr_code')) {
                $tagTypes[$tag] = 'image';
            } elseif (str_contains($tag, 'judul')) {
                $tagTypes[$tag] = 'html';
            } else {
                $tagTypes[$tag] = 'standard';
            }
        }

        $previewHtml = $pdfService->renderHtml(
            $pdfService->replaceSeminarTags($seminar->custom_html ?: ($template->template_html ?? ''), $seminar, $template), 
            $template->header_image_path, 
            [
                'header_repeat' => $template->header_repeat,
                'header_visibility' => $template->header_visibility,
            ]
        );

        $emailDefaults = [
            'subject' => $template->nama . ' - <<mahasiswa_nama>>',
            'body' => "Halo <<mahasiswa_nama>>,\n\nBerikut adalah tautan untuk mengunduh <<template_nama>> Anda:\n<<link_dokumen>>\n\nTerima kasih."
        ];

        // Replace tags in email defaults
        foreach ($emailDefaults as $key => &$val) {
            $val = $pdfService->replaceSeminarTags($val, $seminar, $template);
            $val = str_ireplace('<<template_nama>>', $template->nama, $val);
            // link_dokumen will be handled by client-side since it needs the token
        }

        return view('admin.document.preview', compact(
            'template', 
            'seminar', 
            'availableTags', 
            'finalData', 
            'tagTypes',
            'previewHtml',
            'emailDefaults'
        ));
    }

    public function downloadSeminarPdf(Seminar $seminar, DocumentTemplate $template)
    {
        // 1. Check if user has access to this seminar
        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];
        
        $user = null;
        $effectiveGuard = null;
        foreach ($guardPriority as $g) {
            if (auth()->guard($g)->check()) {
                $user = auth()->guard($g)->user();
                $effectiveGuard = $g;
                break;
            }
        }
        
        if (!$user) {
            abort(401);
        }

        $isAdmin = $effectiveGuard === 'admin';
        $isMahasiswa = $effectiveGuard === 'mahasiswa' && $seminar->mahasiswa_id === $user->id;
        $isDosenEvaluator = $effectiveGuard === 'dosen' && (
            $seminar->p1_dosen_id === $user->id || 
            $seminar->p2_dosen_id === $user->id || 
            $seminar->pembahas_dosen_id === $user->id
        );

        if (!$isAdmin && !$isMahasiswa && !$isDosenEvaluator) {
            abort(403, 'Unauthorized access to this document.');
        }

        // 2. Check if template is relevant for this seminar
        if ($template->seminar_jenis_id && $template->seminar_jenis_id !== $seminar->seminar_jenis_id) {
            abort(404, 'Template not relevant for this seminar type.');
        }

        // 3. If not admin, check download rules based on seminar status
        if (!$isAdmin) {
            $rules = $template->download_rules ?? [];
            $status = $seminar->status;
            
            $ruleKey = null;
            if ($status === 'disetujui') $ruleKey = 'disetujui';
            if ($status === 'selesai') $ruleKey = 'selesai';

            if (!$ruleKey || !isset($rules[$ruleKey]) || empty($rules[$ruleKey])) {
                return back()->with('error', 'Dokumen ini belum tersedia untuk diunduh pada status saat ini (' . $status . ').');
            }

            $allowedRoles = $rules[$ruleKey];
            $roleKey = $isMahasiswa ? 'mahasiswa' : 'dosen';

            if (!in_array($roleKey, $allowedRoles)) {
                return back()->with('error', 'Anda tidak memiliki izin untuk mengunduh dokumen ini.');
            }
        }

        $pdfService = app(\App\Services\PdfGeneratorService::class);
        
        $previewHtml = $pdfService->renderHtml(
            $pdfService->replaceSeminarTags($seminar->custom_html ?: ($template->template_html ?? ''), $seminar, $template), 
            $template->header_image_path, 
            [
                'header_repeat' => $template->header_repeat,
                'header_visibility' => $template->header_visibility,
            ]
        );

        return view('document.print', compact('previewHtml', 'template', 'seminar'));
    }

    /**
     * Store a new seminar
     */
    public function storeSeminar(Request $request)
    {
        $seminarJenisId = $request->input('seminar_jenis_id');

        $jenis = null;
        if ($seminarJenisId) {
            $jenis = SeminarJenis::find((int) $seminarJenisId);
        }

        $defaultExtensions = ['pdf'];
        $defaultMaxKb = 5120;

        if ($jenis && is_array($jenis->berkas_syarat_items) && count($jenis->berkas_syarat_items)) {
            $uploadRules['berkas_syarat_items'] = 'required|array';
            foreach ($jenis->berkas_syarat_items as $item) {
                if (!is_array($item)) continue;
                
                $key = (string) ($item['key'] ?? '');
                $label = (string) ($item['label'] ?? '');
                if ($key === '' || $label === '') continue;

                $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
                $rulePrefix = $required ? 'required' : 'nullable';
                
                $type = $this->getBerkasItemType($item);

                if ($type === 'file') {
                    $extensions = (isset($item['extensions']) && is_array($item['extensions']) && count($item['extensions']))
                        ? $item['extensions']
                        : $defaultExtensions;
                    $extensions = array_values(array_filter(array_map(fn($e) => ltrim(strtolower(trim((string) $e)), '.'), $extensions)));
                    $extensions = count($extensions) ? $extensions : $defaultExtensions;
                    $mimes = implode(',', $extensions);

                    $maxKb = (int) (($item['max_size_kb'] ?? null) ?: $defaultMaxKb);
                    if ($maxKb < 1) $maxKb = $defaultMaxKb;

                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|file|mimes:{$mimes}|max:{$maxKb}";
                } elseif ($type === 'number') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|numeric";
                } elseif ($type === 'email') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|email";
                } elseif ($type === 'date') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|date";
                } elseif ($type === 'checkbox') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|array";
                } else {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|string";
                }
            }
        }

        if (!$request->filled('no_surat')) {
            $request->merge([
                'no_surat' => $this->generateDefaultNoSurat($seminarJenisId ? (int) $seminarJenisId : null),
            ]);
        } else {
            $request->merge([
                'no_surat' => $this->normalizeNoSurat($request->input('no_surat')),
            ]);
        }

        $request->validate([
            'mahasiswa_id' => [
                'required',
                'exists:mahasiswa,id',
                Rule::unique('seminars', 'mahasiswa_id')->where(function ($query) use ($request) {
                    return $query->where('seminar_jenis_id', $request->seminar_jenis_id)
                        ->whereYear('created_at', date('Y'));
                }),
            ],
            'seminar_jenis_id' => 'required|exists:seminar_jenis,id',
            'no_surat' => [
                'required',
                'string',
                'max:255',
                Rule::unique('seminars', 'no_surat')->where(function ($query) use ($request) {
                    return $query->where('seminar_jenis_id', $request->seminar_jenis_id)
                        ->whereYear('created_at', date('Y'));
                }),
            ],
            'judul' => 'required|string|max:500', // Increased length to accommodate HTML
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'p1_dosen_id' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembimbing 1 is invalid.');
                }
            }],
            'p1_nama' => 'required_if:p1_dosen_id,manual|nullable|string|max:255',
            'p1_nip' => 'nullable|string|max:255',
            'p2_dosen_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value && $value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembimbing 2 is invalid.');
                }
            }],
            'p2_nama' => 'required_if:p2_dosen_id,manual|nullable|string|max:255',
            'p2_nip' => 'nullable|string|max:255',
            'pembahas_dosen_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value && $value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembahas is invalid.');
                }
            }],
            'pembahas_nama' => 'required_if:pembahas_dosen_id,manual|nullable|string|max:255',
            'pembahas_nip' => 'nullable|string|max:255',
            'status' => 'required|in:diajukan,disetujui,ditolak,belum_lengkap,selesai',
        ] + $uploadRules);

        $data = [
            'mahasiswa_id' => $request->mahasiswa_id,
            'seminar_jenis_id' => $request->seminar_jenis_id,
            'no_surat' => $request->no_surat,
            'judul' => strip_tags($request->judul, '<p><br><strong><em><u><ol><ul><li>'), // Sanitize HTML and allow basic formatting
            'tanggal' => $request->tanggal,
            'waktu_mulai' => $request->waktu_mulai,
            'lokasi' => $request->lokasi,
            'p1_dosen_id' => $request->p1_dosen_id === 'manual' ? null : $request->p1_dosen_id,
            'p1_nama' => $request->p1_nama,
            'p1_nip' => $request->p1_nip,
            'p2_dosen_id' => $request->p2_dosen_id === 'manual' ? null : $request->p2_dosen_id,
            'p2_nama' => $request->p2_nama,
            'p2_nip' => $request->p2_nip,
            'pembahas_dosen_id' => $request->pembahas_dosen_id === 'manual' ? null : $request->pembahas_dosen_id,
            'pembahas_nama' => $request->pembahas_nama,
            'pembahas_nip' => $request->pembahas_nip,
            'status' => $request->status,
            'berkas_syarat' => [],
        ];

        $seminar = Seminar::create($data);

        // Handle file uploads (item-based)
        if ($jenis && is_array($jenis->berkas_syarat_items) && count($jenis->berkas_syarat_items)) {
            $stored = [];
            foreach ($jenis->berkas_syarat_items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $key = (string) ($item['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                
                $type = $item['type'] ?? 'text';

                if ($type === 'file') {
                    if ($request->hasFile("berkas_syarat_items.{$key}")) {
                        $file = $request->file("berkas_syarat_items.{$key}");
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $ext = $file->getClientOriginalExtension();
                        // Simple counter for key if it's too long
                        $shortKey = Str::slug($key);
                        $filename = "seminar_{$seminar->id}_{$shortKey}_" . Str::random(4) . ".{$ext}";
                        
                        $stored[$key] = $file->storeAs('documents/seminar', $filename, 'uploads');
                    }
                } else {
                    $val = $request->input("berkas_syarat_items.{$key}");
                    if (!is_null($val)) {
                        $stored[$key] = $val;
                    }
                }
            }
            $seminar->update(['berkas_syarat' => $stored]);
        }

        return redirect()->route('admin.seminar.index')->with('success', 'Seminar berhasil ditambahkan!');
    }

    /**
     * Provide next nomor surat for selected seminar jenis.
     */
    public function getNextNoSurat(Request $request)
    {
        $validated = $request->validate([
            'seminar_jenis_id' => 'required|exists:seminar_jenis,id',
        ]);

        $nextNoSurat = $this->generateDefaultNoSurat((int) $validated['seminar_jenis_id']);

        return response()->json([
            'next_no_surat' => $nextNoSurat,
        ]);
    }

    /**
     * Generate default nomor surat that resets every year starting from 001
     */
    private function generateDefaultNoSurat(?int $seminarJenisId = null): string
    {
        $currentYear = Carbon::now()->year;

        $query = Seminar::whereYear('created_at', $currentYear);

        if ($seminarJenisId) {
            $query->where('seminar_jenis_id', $seminarJenisId);
        }

        $maxNumber = $query->max(DB::raw('CAST(no_surat AS UNSIGNED)'));

        $nextNumber = $maxNumber ? ((int) $maxNumber + 1) : 1;

        return $this->normalizeNoSurat((string) $nextNumber);
    }

    private function normalizeNoSurat(string $value): string
    {
        $numeric = ltrim($value, '0');
        if ($numeric === '') {
            $numeric = '0';
        }

        return str_pad($numeric, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Show form to edit a seminar
     */
    public function editSeminar(Seminar $seminar)
    {
        // Eager load relationships including assessment aspects, scores, and signatures
        $seminar->load([
            'seminarJenis.assessmentAspects',
            'nilai.assessmentScores.assessmentAspect',
            'signatures'
        ]);

        $seminarJenis = SeminarJenis::all();
        $dosens = Dosen::all();

        // Build seminarJenisData for dynamic berkas upload rendering
        $seminarJenisData = collect($seminarJenis)->mapWithKeys(function ($j) {
            return [
                $j->id => [
                    'berkas_syarat_items' => is_array($j->berkas_syarat_items) ? $j->berkas_syarat_items : [],
                ]
            ];
        })->all();

        // Get current berkas syarat from seminar
        $rawBerkas = $seminar->berkas_syarat;
        $currentBerkasSyarat = [];
        $isOldFormat = false;

        if (is_string($rawBerkas)) {
            // Old format - single file path stored as string
            if (!str_starts_with($rawBerkas, '{') && !str_starts_with($rawBerkas, '[')) {
                $isOldFormat = true;
                $currentBerkasSyarat = ['__old_format_file__' => $rawBerkas];
            } else {
                $decoded = json_decode($rawBerkas, true);
                $currentBerkasSyarat = is_array($decoded) ? $decoded : [];
            }
        } elseif (is_array($rawBerkas)) {
            $currentBerkasSyarat = $rawBerkas;
        }

        return view('admin.management.seminar.edit', compact(
            'seminar',
            'seminarJenis',
            'dosens',
            'seminarJenisData',
            'currentBerkasSyarat',
            'isOldFormat'
        ));
    }

    /**
     * Update a seminar
     */
    public function updateSeminar(Request $request, Seminar $seminar)
    {
        if ($request->filled('waktu_mulai')) {
            try {
                $request->merge([
                    'waktu_mulai' => Carbon::parse($request->waktu_mulai)->format('H:i'),
                ]);
            } catch (\Exception $e) {
                // Leave as-is to let validation handle invalid format
            }
        }

        if ($request->filled('no_surat')) {
            $request->merge([
                'no_surat' => $this->normalizeNoSurat($request->input('no_surat')),
            ]);
        }

        $jenis = null;
        if ($request->filled('seminar_jenis_id')) {
            $jenis = SeminarJenis::find((int) $request->input('seminar_jenis_id'));
        }

        $existingBerkas = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
        if (is_string($existingBerkas)) {
            $decoded = json_decode($existingBerkas, true);
            $existingBerkas = is_array($decoded) ? $decoded : [];
        }

        $defaultExtensions = ['pdf'];
        $defaultMaxKb = 5120;

        $uploadRules = [];
        if ($jenis && is_array($jenis->berkas_syarat_items) && count($jenis->berkas_syarat_items)) {
            $uploadRules['berkas_syarat_items'] = 'nullable|array';
            foreach ($jenis->berkas_syarat_items as $item) {
                if (!is_array($item)) continue;

                $key = (string) ($item['key'] ?? '');
                $label = (string) ($item['label'] ?? '');
                if ($key === '' || $label === '') continue;

                $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
                $hasExisting = is_array($existingBerkas) && array_key_exists($key, $existingBerkas) && !empty($existingBerkas[$key]);
                $isRequiredNow = $required && !$hasExisting;
                $rulePrefix = $isRequiredNow ? 'required' : 'nullable';

                $type = $this->getBerkasItemType($item);

                if ($type === 'file') {
                    $extensions = (isset($item['extensions']) && is_array($item['extensions']) && count($item['extensions']))
                        ? $item['extensions']
                        : $defaultExtensions;
                    $extensions = array_values(array_filter(array_map(fn($e) => ltrim(strtolower(trim((string) $e)), '.'), $extensions)));
                    $extensions = count($extensions) ? $extensions : $defaultExtensions;
                    $mimes = implode(',', $extensions);

                    $maxKb = (int) (($item['max_size_kb'] ?? null) ?: $defaultMaxKb);
                    if ($maxKb < 1) $maxKb = $defaultMaxKb;

                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|file|mimes:{$mimes}|max:{$maxKb}";
                } elseif ($type === 'number') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|numeric";
                } elseif ($type === 'email') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|email";
                } elseif ($type === 'date') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|date";
                } elseif ($type === 'checkbox') {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|array";
                } else {
                    $uploadRules["berkas_syarat_items.{$key}"] = "{$rulePrefix}|string";
                }
            }
        }

        $request->validate([
            'seminar_jenis_id' => [
                'required',
                'exists:seminar_jenis,id',
                Rule::unique('seminars', 'seminar_jenis_id')
                    ->ignore($seminar->id)
                    ->where(function ($query) use ($seminar) {
                        return $query->where('mahasiswa_id', $seminar->mahasiswa_id)
                            ->whereYear('created_at', date('Y'));
                    }),
            ],
            'no_surat' => [
                'required',
                'string',
                'max:255',
                Rule::unique('seminars', 'no_surat')
                    ->ignore($seminar->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('seminar_jenis_id', $request->seminar_jenis_id)
                            ->whereYear('created_at', date('Y'));
                    }),
            ],
            'judul' => 'required|string|max:500',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'p1_dosen_id' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembimbing 1 is invalid.');
                }
            }],
            'p1_nama' => 'required_if:p1_dosen_id,manual|nullable|string|max:255',
            'p1_nip' => 'nullable|string|max:255',
            'p2_dosen_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value && $value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembimbing 2 is invalid.');
                }
            }],
            'p2_nama' => 'required_if:p2_dosen_id,manual|nullable|string|max:255',
            'p2_nip' => 'nullable|string|max:255',
            'pembahas_dosen_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value && $value !== 'manual' && !\DB::table('dosen')->where('id', $value)->exists()) {
                    $fail('The selected pembahas is invalid.');
                }
            }],
            'pembahas_nama' => 'required_if:pembahas_dosen_id,manual|nullable|string|max:255',
            'pembahas_nip' => 'nullable|string|max:255',
            'status' => 'required|in:diajukan,disetujui,ditolak,belum_lengkap,selesai',
            'nilai' => 'nullable|array',
            'nilai.*.dosen_id' => 'nullable', // Allow manual
            'nilai.*.jenis_penilai' => 'nullable|in:p1,p2,pembahas',
            'nilai.*.nilai_angka' => 'nullable|numeric|min:0|max:100',
            'nilai.*.catatan' => 'nullable|string|max:500',
            'nilai.*.komponen' => 'nullable|array',
            'nilai.*.komponen.*' => 'nullable|numeric|min:0|max:100',
            'signatures' => 'nullable|array',
            'signatures.*.data' => 'nullable|string',
            'signatures.*.signature_type' => 'nullable|in:manual,qr_code',
            'signatures.*.qr_agreement' => 'nullable|boolean',
            'signatures.*.dosen_id' => 'nullable', // Allow manual
            'signatures.*.jenis_penilai' => 'nullable|in:p1,p2,pembahas',
        ] + $uploadRules);

        $previousStatus = $seminar->status;

        $origP1 = $seminar->p1_dosen_id;
        $origP2 = $seminar->p2_dosen_id;
        $origPembahas = $seminar->pembahas_dosen_id;

        $seminar->update([
            'seminar_jenis_id' => $request->seminar_jenis_id,
            'no_surat' => $request->no_surat,
            'judul' => strip_tags($request->judul, '<p><br><strong><em><u><ol><ul><li>'), // Sanitize HTML and allow basic formatting
            'tanggal' => $request->tanggal,
            'waktu_mulai' => $request->waktu_mulai,
            'lokasi' => $request->lokasi,
            'p1_dosen_id' => $request->p1_dosen_id === 'manual' ? null : $request->p1_dosen_id,
            'p1_nama' => $request->p1_nama,
            'p1_nip' => $request->p1_nip,
            'p2_dosen_id' => $request->p2_dosen_id === 'manual' ? null : $request->p2_dosen_id,
            'p2_nama' => $request->p2_nama,
            'p2_nip' => $request->p2_nip,
            'pembahas_dosen_id' => $request->pembahas_dosen_id === 'manual' ? null : $request->pembahas_dosen_id,
            'pembahas_nama' => $request->pembahas_nama,
            'pembahas_nip' => $request->pembahas_nip,
            'status' => $request->status,
        ]);

        // Cleanup signatures if evaluator ID changed (avoiding orphaning signatures for previous evaluators)
        if ($origP1 != $seminar->p1_dosen_id) {
            $seminar->signatures()->where('jenis_penilai', 'p1')->delete();
        }
        if ($origP2 != $seminar->p2_dosen_id) {
            $seminar->signatures()->where('jenis_penilai', 'p2')->delete();
        }
        if ($origPembahas != $seminar->pembahas_dosen_id) {
            $seminar->signatures()->where('jenis_penilai', 'pembahas')->delete();
        }

        if ($previousStatus !== $seminar->status) {
            $this->handleSeminarStatusChange($seminar, $previousStatus);
        }

        // Handle file uploads (item-based)
        if ($jenis && is_array($jenis->berkas_syarat_items) && count($jenis->berkas_syarat_items)) {
            $stored = is_array($existingBerkas) ? $existingBerkas : [];
            foreach ($jenis->berkas_syarat_items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $key = (string) ($item['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $type = $this->getBerkasItemType($item);

                if ($type === 'file') {
                    if ($request->hasFile("berkas_syarat_items.{$key}")) {
                        $file = $request->file("berkas_syarat_items.{$key}");
                        $ext = $file->getClientOriginalExtension();
                        
                        // Simple counter for key if it's too long
                        $loopIndex = array_search($item, $jenis->berkas_syarat_items) + 1;
                        $shortKey = Str::startsWith($key, 'item_') ? 'file_' . $loopIndex : $key;
                        $filename = "seminar_{$seminar->id}_{$shortKey}_" . Str::random(4) . ".{$ext}";
                        
                        // Optional: Delete old file if exists
                        if (isset($stored[$key]) && !empty($stored[$key])) {
                            Storage::disk('uploads')->delete($stored[$key]);
                        }

                        $stored[$key] = $file->storeAs('documents/seminar', $filename, 'uploads');
                    }
                } else {
                    if ($request->has("berkas_syarat_items.{$key}")) {
                        $stored[$key] = $request->input("berkas_syarat_items.{$key}");
                    }
                }
            }

            $seminar->update(['berkas_syarat' => $stored]);
        }

        // Update nilai if provided
        if ($request->has('nilai')) {
            foreach ($request->nilai as $nilaiData) {
                if (empty($nilaiData['dosen_id']) || empty($nilaiData['jenis_penilai'])) {
                    continue;
                }

                $hasKomponen = isset($nilaiData['komponen']) && is_array($nilaiData['komponen'])
                    ? collect($nilaiData['komponen'])->filter(function ($value) {
                        return $value !== null && $value !== '';
                    })->isNotEmpty()
                    : false;

                if (empty($nilaiData['nilai_angka']) && empty($nilaiData['catatan']) && !$hasKomponen) {
                    continue; // Skip if both nilai and catatan are empty
                }

                // Check if nilai record already exists
                $existingNilai = $seminar->nilai()
                    ->where('dosen_id', $nilaiData['dosen_id'])
                    ->where('jenis_penilai', $nilaiData['jenis_penilai'])
                    ->first();

                $nilaiUpdateData = [];

                // Handle nilai_angka
                if (isset($nilaiData['nilai_angka']) && $nilaiData['nilai_angka'] !== '') {
                    $nilaiUpdateData['nilai_angka'] = $nilaiData['nilai_angka'];
                }

                // Handle catatan
                if (isset($nilaiData['catatan']) && $nilaiData['catatan'] !== '') {
                    $nilaiUpdateData['catatan'] = $nilaiData['catatan'];
                }

                // Handle komponen_nilai
                if ($hasKomponen) {
                    $komponenFiltered = [];
                    foreach ($nilaiData['komponen'] as $componentKey => $componentValue) {
                        if ($componentValue !== null && $componentValue !== '') {
                            $komponenFiltered[$componentKey] = $componentValue;
                        }
                    }

                    if (!empty($komponenFiltered)) {
                        $nilaiUpdateData['komponen_nilai'] = $komponenFiltered;

                        // If nilai_angka is not provided, calculate from components (simple average or sum)
                        // You can adjust this logic based on your needs
                        if (!isset($nilaiUpdateData['nilai_angka'])) {
                            $nilaiUpdateData['nilai_angka'] = array_sum($komponenFiltered) / count($komponenFiltered);
                        }
                    }
                }

                // Ensure nilai_angka always has a value for new records
                if (!empty($nilaiUpdateData)) {
                    // For new records, ensure nilai_angka is set
                    if (!$existingNilai && !isset($nilaiUpdateData['nilai_angka'])) {
                        $nilaiUpdateData['nilai_angka'] = 0; // Default to 0 if not provided
                    }

                    if ($existingNilai) {
                        $existingNilai->update($nilaiUpdateData);
                    } else {
                        $seminar->nilai()->create(array_merge([
                            'dosen_id' => $nilaiData['dosen_id'],
                            'jenis_penilai' => $nilaiData['jenis_penilai'],
                        ], $nilaiUpdateData));
                    }
                }
            }
        }

        // Handle aspect scores if provided
        if ($request->has('aspect_scores')) {
            foreach ($request->aspect_scores as $evaluatorType => $aspectScores) {
                // Map evaluator type to jenis_penilai
                $jenisPenilai = $evaluatorType;
                $dosenId = null;

                if ($jenisPenilai === 'p1') {
                    $dosenId = $seminar->p1_dosen_id;
                } elseif ($jenisPenilai === 'p2') {
                    $dosenId = $seminar->p2_dosen_id;
                } elseif ($jenisPenilai === 'pembahas') {
                    $dosenId = $seminar->pembahas_dosen_id;
                }

                if (empty($aspectScores)) {
                    continue;
                }

                // Get or create nilai record for this evaluator
                $nilai = $seminar->nilai()
                    ->where('dosen_id', $dosenId)
                    ->where('jenis_penilai', $jenisPenilai)
                    ->first();

                if (!$nilai) {
                    $nilai = $seminar->nilai()->create([
                        'dosen_id' => $dosenId,
                        'jenis_penilai' => $jenisPenilai,
                        'nilai_angka' => 0,
                    ]);
                }

                // Update or create assessment scores for each aspect
                foreach ($aspectScores as $aspectId => $scoreValue) {
                    if ($scoreValue === null || $scoreValue === '') {
                        continue;
                    }

                    $nilai->assessmentScores()->updateOrCreate(
                        ['assessment_aspect_id' => $aspectId],
                        ['nilai' => $scoreValue]
                    );
                }

                // Recalculate and update nilai_angka (average of all aspect scores)
                $nilai->nilai_angka = $nilai->calculateFinalScore();
                $nilai->save();
            }
        }

        // Handle per-evaluator notes (nilai_catatan array)
        if ($request->has('nilai_catatan') && is_array($request->nilai_catatan)) {
            foreach ($request->nilai_catatan as $evaluatorType => $catatanText) {
                // Map evaluator type to jenis_penilai and get dosen_id
                $jenisPenilai = $evaluatorType;
                $dosenId = null;

                if ($jenisPenilai === 'p1') {
                    $dosenId = $seminar->p1_dosen_id;
                } elseif ($jenisPenilai === 'p2') {
                    $dosenId = $seminar->p2_dosen_id;
                } elseif ($jenisPenilai === 'pembahas') {
                    $dosenId = $seminar->pembahas_dosen_id;
                }

                if ($catatanText === null || $catatanText === '') {
                    continue;
                }

                // Get or create nilai record for this evaluator
                $nilai = $seminar->nilai()
                    ->where('dosen_id', $dosenId)
                    ->where('jenis_penilai', $jenisPenilai)
                    ->first();

                if (!$nilai) {
                    // Create nilai record if doesn't exist
                    $nilai = $seminar->nilai()->create([
                        'dosen_id' => $dosenId,
                        'jenis_penilai' => $jenisPenilai,
                        'nilai_angka' => 0,
                        'catatan' => $catatanText,
                    ]);
                } else {
                    // Update catatan only
                    $nilai->update(['catatan' => $catatanText]);
                }
            }
        }

        // Handle signatures if provided
        if ($request->has('signatures')) {
            foreach ($request->signatures as $evaluatorType => $signatureData) {
                $jenisPenilai = $evaluatorType; // p1, p2, pembahas
                $dosenId = $signatureData['dosen_id'] ?? null;
                if ($dosenId === 'manual') $dosenId = null;
                
                $signatureType = $signatureData['signature_type'] ?? 'manual';
                
                // Check if signature record already exists
                $existingSignature = $seminar->signatures()
                    ->where('dosen_id', $dosenId)
                    ->where('jenis_penilai', $jenisPenilai)
                    ->first();
                
                if ($signatureType === 'qr_code') {
                    // QR Code Signature Mode
                    $qrAgreement = $signatureData['qr_agreement'] ?? false;
                    
                    if ($qrAgreement) {
                        // Generate QR Code
                        $verificationToken = \Str::uuid()->toString();
                        $originalUrl = \Illuminate\Support\Facades\URL::signedRoute('verify.seminar.signature', [
                            'seminarId' => $seminar->id,
                            'type' => $jenisPenilai,
                            'token' => $verificationToken
                        ]);
                        
                        // Shorten URL
                        $shortener = new UrlShortenerService();
                        $qrCodeData = $shortener->shorten($originalUrl);
                        
                        // Generate QR Code using chillerlan/php-qrcode
                        $options = new \chillerlan\QRCode\QROptions([
                            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                            'outputBase64' => false, // Get raw binary instead of base64 data URI
                            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
                            'scale' => 10,
                            'imageTransparent' => false,
                        ]);
                        
                        $qrcode = new \chillerlan\QRCode\QRCode($options);
                        $qrCodeImage = $qrcode->render($qrCodeData);
                        
                        $qrCodePath = 'signatures/qr-seminar-' . $seminar->id . '-' . $jenisPenilai . '-' . time() . '.png';
                        Storage::disk('uploads')->put($qrCodePath, $qrCodeImage);
                        
                        if ($existingSignature) {
                            // Delete old QR code if exists
                            if ($existingSignature->qr_code_path) {
                                Storage::disk('uploads')->delete($existingSignature->qr_code_path);
                            }
                            if ($existingSignature->tanda_tangan) {
                                Storage::disk('uploads')->delete($existingSignature->tanda_tangan);
                            }
                            
                            $existingSignature->update([
                                'signature_type' => 'qr_code',
                                'qr_code_path' => $qrCodePath,
                                'verification_token' => $verificationToken,
                                'tanda_tangan' => '', // Empty string for QR code mode
                                'tanggal_ttd' => now(),
                            ]);
                        } else {
                            \App\Models\SeminarSignature::create([
                                'seminar_id' => $seminar->id,
                                'dosen_id' => $dosenId,
                                'jenis_penilai' => $jenisPenilai,
                                'signature_type' => 'qr_code',
                                'qr_code_path' => $qrCodePath,
                                'verification_token' => $verificationToken,
                                'tanda_tangan' => '', // Empty string for QR code mode
                                'tanggal_ttd' => now(),
                            ]);
                        }
                    }
                } elseif ($signatureType === 'manual' && !empty($signatureData['data'])) {
                    // Manual Canvas Signature Mode
                    $image = $signatureData['data'];
                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'signatures/seminar-' . $seminar->id . '-' . $jenisPenilai . '-' . time() . '.png';
                    
                    Storage::disk('uploads')->put($imageName, base64_decode($image));
                    
                    if ($existingSignature) {
                        // Delete old files
                        if ($existingSignature->tanda_tangan) {
                            Storage::disk('uploads')->delete($existingSignature->tanda_tangan);
                        }
                        if ($existingSignature->qr_code_path) {
                            Storage::disk('uploads')->delete($existingSignature->qr_code_path);
                        }
                        
                        $existingSignature->update([
                            'signature_type' => 'manual',
                            'tanda_tangan' => $imageName,
                            'qr_code_path' => '',
                            'verification_token' => '',
                            'tanggal_ttd' => now(),
                        ]);
                    } else {
                        \App\Models\SeminarSignature::create([
                            'seminar_id' => $seminar->id,
                            'dosen_id' => $dosenId,
                            'jenis_penilai' => $jenisPenilai,
                            'signature_type' => 'manual',
                            'tanda_tangan' => $imageName,
                            'tanggal_ttd' => now(),
                        ]);
                    }
                }
            }
        }

        // Update seminar status based on evaluator completion
        $seminar->refreshCompletionStatus();

        return redirect()->route('admin.seminar.edit', $seminar->id)->with('success', 'Seminar berhasil diperbarui!');
    }

    /**
     * Delete a seminar
     */
    public function destroySeminar(Seminar $seminar)
    {
        $seminar->delete();
        return redirect()->route('admin.seminar.index')->with('success', 'Seminar berhasil dihapus!');
    }

    /**
     * Approve a seminar request quickly.
     */
    public function approveSeminar(Seminar $seminar)
    {
        $previousStatus = $seminar->status;
        $seminar->update(['status' => 'disetujui']);

        $this->handleSeminarStatusChange($seminar, $previousStatus);

        return redirect()->back()->with('success', 'Seminar berhasil disetujui!');
    }

    /**
     * Reject a seminar request quickly.
     */
    public function rejectSeminar(Seminar $seminar)
    {
        $previousStatus = $seminar->status;
        $seminar->update(['status' => 'ditolak']);

        $this->handleSeminarStatusChange($seminar, $previousStatus);

        return redirect()->back()->with('success', 'Seminar berhasil ditolak!');
    }

    /**
     * Handle notifications after a seminar status change.
     */
    private function handleSeminarStatusChange(Seminar $seminar, string $previousStatus)
    {
        // 1. Notify Mahasiswa & Admins
        if ($seminar->mahasiswa) {
            $seminar->mahasiswa->notify(new \App\Notifications\SeminarStatusUpdatedNotification($seminar, $previousStatus));
        }
        
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\SeminarStatusUpdatedNotification($seminar, $previousStatus));
        }

        // 2. Notify Evaluators if approved
        if ($previousStatus !== 'disetujui' && $seminar->status === 'disetujui') {
            $evaluators = collect();
            if ($seminar->p1_dosen_id && $seminar->p1Dosen) {
                $evaluators->push(['dosen' => $seminar->p1Dosen, 'role' => 'Pembimbing 1']);
            }
            if ($seminar->p2_dosen_id && $seminar->p2Dosen) {
                $evaluators->push(['dosen' => $seminar->p2Dosen, 'role' => 'Pembimbing 2']);
            }
            if ($seminar->pembahas_dosen_id && $seminar->pembahasDosen) {
                $evaluators->push(['dosen' => $seminar->pembahasDosen, 'role' => 'Penguji/Pembahas']);
            }

            foreach ($evaluators as $evaluator) {
                $evaluator['dosen']->notify(new \App\Notifications\EvaluatorAssignedNotification($seminar, $evaluator['role']));
            }
        }
    }

    /**
     * Update nilai for a seminar
     */
    public function updateNilai(Request $request, Seminar $seminar, $jenis)
    {
        $request->validate([
            'nilai_angka' => 'required|numeric|min:0|max:100',
        ]);

        // Check if nilai record already exists
        $existingNilai = $seminar->nilai()->where('jenis_penilai', $jenis)->first();

        if ($existingNilai) {
            $existingNilai->update([
                'nilai_angka' => $request->nilai_angka,
            ]);
        } else {
            // Create new nilai record
            $seminar->nilai()->create([
                'dosen_id' => $this->getDosenIdByJenis($seminar, $jenis),
                'jenis_penilai' => $jenis,
                'nilai_angka' => $request->nilai_angka,
            ]);
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Nilai berhasil diperbarui!']);
        }

        return redirect()->back()->with('success', 'Nilai berhasil diperbarui!');
    }

    /**
     * Get dosen ID based on jenis penilai for a seminar
     */
    private function getDosenIdByJenis(Seminar $seminar, $jenis)
    {
        switch ($jenis) {
            case 'p1':
                return $seminar->p1_dosen_id;
            case 'p2':
                return $seminar->p2_dosen_id;
            case 'pembahas':
                return $seminar->pembahas_dosen_id;
            default:
                return null;
        }
    }

    /**
     * Delete a specific berkas from seminar
     */
    public function deleteBerkas(Seminar $seminar, $filename)
    {
        $berkasSyarat = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
        if (is_string($seminar->berkas_syarat)) {
            $decoded = json_decode($seminar->berkas_syarat, true);
            $berkasSyarat = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($berkasSyarat) || count($berkasSyarat) === 0) {
            return redirect()->back()->with('error', 'Berkas tidak ditemukan.');
        }

        // Special handling for old format file deletion
        if ($filename === 'old_format_file') {
            // Delete all files and set to empty array (migration to new format)
            foreach ($berkasSyarat as $path) {
                if (is_string($path) && $path !== '') {
                    Storage::disk('uploads')->delete(ltrim($path, '/'));
                }
            }
            $seminar->update(['berkas_syarat' => []]);
            return redirect()->back()->with('success', 'File lama berhasil dihapus! Sekarang menggunakan format baru.');
        }

        // New format: associative array key => path
        if (array_key_exists($filename, $berkasSyarat)) {
            $path = $berkasSyarat[$filename];
            if (is_string($path) && $path !== '') {
                Storage::disk('uploads')->delete(ltrim($path, '/'));
            }
            unset($berkasSyarat[$filename]);
            $seminar->update(['berkas_syarat' => $berkasSyarat]);
            return redirect()->back()->with('success', 'Berkas berhasil dihapus!');
        }

        // Backward compatibility: try find by value
        foreach ($berkasSyarat as $key => $path) {
            if ($path === $filename) {
                Storage::disk('uploads')->delete(ltrim((string) $path, '/'));
                unset($berkasSyarat[$key]);
                $seminar->update(['berkas_syarat' => $berkasSyarat]);
                return redirect()->back()->with('success', 'Berkas berhasil dihapus!');
            }
        }

        return redirect()->back()->with('error', 'Berkas tidak ditemukan.');
    }

    /**
     * Display stored seminar files (signatures and berkas) for authenticated admins.
     */
    public function showSeminarFile($path)
    {
        $decodedPath = rawurldecode($path);
        
        // Handle direct favicon request if it bypasses static file serving
        if ($decodedPath === 'favicon.ico') {
            $settings = \App\Models\LandingPageSetting::first();
            if ($settings && $settings->favicon_path) {
                $decodedPath = $settings->favicon_path;
            }
        }

        $normalizedPath = ltrim($decodedPath, '/');

        if (Str::contains($normalizedPath, '..')) {
            abort(403);
        }

        // Strip leading uploads/ if present (for urls using the route manually)
        if (Str::startsWith($normalizedPath, 'uploads/')) {
            $normalizedPath = Str::after($normalizedPath, 'uploads/');
        }

        $allowedPrefixes = [
            'signatures/', 
            'seminar-berkas/', 
            'documents/', 
            'branding/', 
            'photos/',
            'surat/'
        ];

        if (!Str::startsWith($normalizedPath, $allowedPrefixes)) {
            abort(404);
        }

        if (!Storage::disk('uploads')->exists($normalizedPath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('uploads')->path($normalizedPath);
        // Normalize path for Windows/Unix compatibility
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->file($absolutePath, [
            'Content-Type' => Storage::disk('uploads')->mimeType($normalizedPath) ?: 'application/octet-stream',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Send email notification for a seminar
     */
    public function previewNotification(Request $request, Seminar $seminar)
    {
        try {
            $templateId = $request->input('template_id');
            $recipientType = $request->input('recipient_type'); // mahasiswa, p1, p2, pembahas
            
            $recipientInfo = [];
            switch ($recipientType) {
                case 'mahasiswa':
                    $recipientInfo = [
                        'penerima_nama' => $seminar->mahasiswa->nama,
                        'penerima_email' => $seminar->mahasiswa->email,
                        'penerima_wa' => $seminar->mahasiswa->wa ?: ($seminar->mahasiswa->hp ?? ''),
                    ];
                    break;
                case 'p1':
                    $recipientInfo = [
                        'penerima_nama' => $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'Pembimbing 1'),
                        'penerima_email' => $seminar->p1Dosen->email ?? '',
                        'penerima_wa' => $seminar->p1Dosen->wa ?? ($seminar->p1Dosen->hp ?? ''),
                    ];
                    break;
                case 'p2':
                    $recipientInfo = [
                        'penerima_nama' => $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'Pembimbing 2'),
                        'penerima_email' => $seminar->p2Dosen->email ?? '',
                        'penerima_wa' => $seminar->p2Dosen->wa ?? ($seminar->p2Dosen->hp ?? ''),
                    ];
                    break;
                case 'pembahas':
                    $recipientInfo = [
                        'penerima_nama' => $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'Pembahas'),
                        'penerima_email' => $seminar->pembahasDosen->email ?? '',
                        'penerima_wa' => $seminar->pembahasDosen->wa ?? ($seminar->pembahasDosen->hp ?? ''),
                    ];
                    break;
            }

            $template = \App\Models\DocumentTemplate::find($templateId);
            if (!$template) {
                // Fallback to basic content if no template is chosen
                $subject = "Notifikasi Seminar: " . $seminar->judul;
                $body = "Halo <<penerima_nama>>,\n\nIni adalah notifikasi terkait seminar: " . $seminar->judul . ".\nStatus: " . ucfirst($seminar->status);
            } else {
                $subject = $template->email_subject_template ?: "Notifikasi Seminar: " . $seminar->judul;
                $body = $template->email_body_template ?: "Halo <<penerima_nama>>,\n\nBerikut terlampir link dokumen untuk seminar Anda:\n<<link_dokumen>>\n\nTerima kasih.";
            }

            $pdfService = app(\App\Services\PdfGeneratorService::class);
            $subject = $pdfService->replaceSeminarTags($subject, $seminar, $template);
            $body = $pdfService->replaceSeminarTags($body, $seminar, $template);

            // Manual replacement for recipient tags (since they aren't in getSeminarData)
            foreach ($recipientInfo as $key => $val) {
                $subject = str_ireplace("<<{$key}>>", $val ?? '', $subject);
                $body = str_ireplace("<<{$key}>>", $val ?? '', $body);
            }

            // Force inject link_dokumen if present in body but not replaced, or as a fallback
            // For seminar, we might want to link to the Docx/PDF preview if a template matches
            if ($template) {
                $downloadUrl = route('admin.seminar.document.preview', [$seminar, $template]);
                $body = str_ireplace('<<link_dokumen>>', $downloadUrl, $body);
            }

            return response()->json([
                'subject' => strip_tags(html_entity_decode($subject)),
                'body' => strip_tags(html_entity_decode($body))
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in seminar previewNotification: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendNotification(Request $request, Seminar $seminar)
    {
        $request->validate([
            'recipient_email' => 'required_if:channel,email|email|nullable',
            'recipient_wa' => 'required_if:channel,wa|string|nullable',
            'subject' => 'required|string',
            'body' => 'required|string',
            'channel' => 'required|in:email,wa'
        ]);

        try {
            if ($request->channel === 'email') {
                \Mail::raw($request->body, function ($message) use ($request) {
                    $message->to($request->recipient_email)
                            ->subject($request->subject);
                });
                return redirect()->back()->with('success', 'Email berhasil dikirim ke ' . $request->recipient_email);
            } else {
                // WA is handled client-side by opening a link, but we could log it here if needed
                return redirect()->back()->with('success', 'Instruksi WhatsApp telah disiapkan.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengirim notifikasi: ' . $e->getMessage());
        }
    }





    /**
     * Check if all emails sent and update status to 'selesai'
     */
    private function checkAndUpdateSeminarStatus(Seminar $seminar)
    {
        // Check if all three email types have been sent
        if ($seminar->undangan_sent_at && $seminar->nilai_sent_at && $seminar->borang_sent_at) {
            // Only update if status is not already 'selesai' or 'ditolak'
            if ($seminar->status !== 'selesai' && $seminar->status !== 'ditolak') {
                $seminar->update(['status' => 'selesai']);
            }
        }
    }

    /**
     * Show import form for dosen
     */
    public function showImportDosen()
    {
        return view('admin.management.dosen.import');
    }

    /**
     * Import dosens from Excel file
     */
    public function importDosen(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\DosenImport, $file);

            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('dosen.import.form')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Show import form for mahasiswa
     */
    public function showImportMahasiswa()
    {
        return view('admin.management.mahasiswa.import');
    }

    /**
     * Import mahasiswas from Excel file
     */
    public function importMahasiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\MahasiswaImport, $file);

            return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('mahasiswa.import.form')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download sample import file for dosen
     */
    public function downloadSampleDosen()
    {
        $headings = ['Nama', 'NIP', 'Email', 'HP', 'WA', 'Password'];
        $rows = [
            ['John Doe', '1234567890', 'john@example.com', '081234567890', '081234567891', 'password123'],
            ['Jane Smith', '0987654321', 'jane@example.com', '089876543210', '089876543211', ''],
        ];

        // Using Laravel Excel to create the file
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SampleDosenExport($rows, $headings),
            'sample_import_dosen.xlsx'
        );
    }

    /**
     * Download sample import file for mahasiswa
     */
    public function downloadSampleMahasiswa()
    {
        $headings = ['Nama', 'NPM', 'Email', 'HP', 'WA', 'Pembimbing Akademik (NIP)', 'Password'];
        $rows = [
            ['Student 1', '2021001', 'student1@example.com', '081234567891', '081234567892', '198001012005011001', 'password123'],
            ['Student 2', '2021002', 'student2@example.com', '081234567893', '081234567894', '198505052010011002', ''],
        ];

        // Using Laravel Excel to create the file
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SampleMahasiswaExport($rows, $headings),
            'sample_import_mahasiswa.xlsx'
        );
    }
    /**
     * Save custom HTML for seminar document.
     */
    public function saveCustomSeminarHtml(Request $request, Seminar $seminar)
    {
        $request->validate([
            'custom_html' => 'required|string',
        ]);

        $seminar->update([
            'custom_html' => $request->custom_html
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perubahan dokumen berhasil disimpan.'
        ]);
    }

    /**
     * Reset custom HTML for seminar document.
     */
    public function resetCustomSeminarHtml(Seminar $seminar)
    {
        $seminar->update([
            'custom_html' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen telah direset ke template default.'
        ]);
    }


    /**
     * Send document via email.
     */
    public function sendSeminarDocument(Request $request, Seminar $seminar, DocumentTemplate $template)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment_mode' => 'required|in:auto,custom',
            'custom_attachment' => 'required_if:attachment_mode,custom|file|max:15360',
        ]);

        $recipients = $request->input('recipients');
        $subject = $request->input('subject');
        $message = $request->input('message');
        $mode = $request->input('attachment_mode');
        
        $attachmentName = 'Document.pdf';

        if ($mode === 'auto') {
            $pdfService = app(PdfGeneratorService::class);
            $customHtml = $request->input('document_html');

            if (!empty($customHtml)) {
                // If user edited HTML in TinyMCE, generate from that
                $pdf = $pdfService->renderPdf($customHtml, $template->paper_size ?? 'A4', $template->header_image_path, [
                    'header_repeat' => $template->header_repeat,
                    'header_visibility' => $template->header_visibility,
                    'header_custom_pages' => $template->header_custom_pages,
                ]);
            } else {
                $pdf = $pdfService->generateSeminarPdf($seminar, $template);
            }
            
            // Save temporary PDF for attachment
            $tempPath = 'temp/' . Str::random(20) . '.pdf';
            Storage::disk('public')->put($tempPath, $pdf->output());
            $attachmentPath = Storage::disk('public')->path($tempPath);
        } else {
            $file = $request->file('custom_attachment');
            $attachmentPath = $file->getRealPath();
            $attachmentName = $file->getClientOriginalName();
        }

        try {

            Mail::send([], [], function ($m) use ($recipients, $subject, $message, $attachmentPath, $attachmentName) {
                $m->to($recipients)
                  ->subject($subject)
                  ->html($message);
                
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $m->attach($attachmentPath, ['as' => $attachmentName]);
                }
            });

            // Clean up temp PDF if it was auto-generated
            if ($mode === 'auto' && isset($tempPath)) {
                Storage::disk('public')->delete($tempPath);
            }

            return back()->with('success', 'Dokumen berhasil dikirim ke ' . count($recipients) . ' penerima.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Infer item type for berkas_syarat_items
     */
    private function getBerkasItemType(array $item): string
    {
        $type = $item['type'] ?? '';
        if ($type) return $type;

        $key = (string) ($item['key'] ?? '');
        $keyLower = strtolower($key);
        
        $fileKeywords = ['file', 'berkas', 'scan', 'upload', 'dokumen', 'transkrip', 'krs', 'ktm', 'sertifikat', 'surat', 'abstrak', 'poster', 'artikel', 'lembar', 'bukti', 'kartu'];
        $isLikelyFile = false;
        foreach ($fileKeywords as $kw) {
            if (strpos($keyLower, $kw) !== false) {
                $isLikelyFile = true;
                break;
            }
        }
        
        if (isset($item['extensions']) && !empty($item['extensions'])) $isLikelyFile = true;
        if ($isLikelyFile) return 'file';
        
        if (strpos($keyLower, 'tgl') !== false || strpos($keyLower, 'tanggal') !== false) {
            if (strpos($keyLower, 'tempat') === false) {
                return 'date';
            }
        }

        return 'text';
    }
}
