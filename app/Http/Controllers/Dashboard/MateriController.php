<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\UstadzKelas;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    /**
     * List materi for a program, optionally filtered by jadwal
     */
    public function index(Request $request, string $programSlug)
    {
        $this->authorize('viewAny', Materi::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user = auth()->user();

        $materi = Materi::with(['jadwal', 'ustadzKelas.ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->when($user->ustadz, function ($query) use ($user) {
                $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
                $query->whereIn('ustadz_kelas_id', $assignedIds);
            })
            ->when($request->jadwal_id, function ($query) use ($request) {
                $query->where('jadwal_id', $request->jadwal_id);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return view('dashboard.materi.index', compact('materi', 'programSlug', 'program'));
    }

    /**
     * Show materi linked to a specific jadwal
     */
    public function byJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $this->authorize('viewAny', Materi::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject'])->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $materi = Materi::with(['ustadzKelas.ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->where('jadwal_id', $jadwalId)
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return view('dashboard.materi.by-jadwal', compact('materi', 'jadwal', 'programSlug', 'program'));
    }

    /**
     * Create materi from a jadwal (pre-filled)
     */
    public function fromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $this->authorize('create', Materi::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject'])->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $tanggal = $request->get('tanggal', today()->toDateString());

        return view('dashboard.materi.create', compact('jadwal', 'programSlug', 'program', 'tanggal'));
    }

    /**
     * General create form
     */
    public function create(Request $request, string $programSlug)
    {
        $this->authorize('create', Materi::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user = auth()->user();

        $ustadzKelas = UstadzKelas::with(['ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->when($user->ustadz, function ($query) use ($user) {
                $query->where('ustadz_id', $user->ustadz->id);
            })
            ->get();

        $jadwalList = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject'])
            ->where('program_id', $program->id)
            ->when($user->ustadz, function ($query) use ($user) {
                $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
                $query->whereIn('ustadz_kelas_id', $assignedIds);
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $tanggal = $request->get('tanggal', today()->toDateString());

        return view('dashboard.materi.create', compact('programSlug', 'program', 'ustadzKelas', 'jadwalList', 'tanggal'));
    }

    /**
     * Store new materi
     */
    public function store(Request $request, string $programSlug)
    {
        $this->authorize('create', Materi::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user = auth()->user();

        $data = $request->validate([
            'jadwal_id'           => ['nullable', 'integer', 'exists:jadwal,id'],
            'ustadz_kelas_id'       => ['required', 'integer', 'exists:ustadz_kelas,id'],
            'kelas_id'              => ['required', 'integer', 'exists:kelas,id'],
            'subject_id'            => ['required', 'integer', 'exists:subjects,id'],
            'tanggal'               => ['required', 'date'],
            'judul'                 => ['required', 'string', 'max:255'],
            'deskripsi'             => ['nullable', 'string'],
            'tujuan_pembelajaran'   => ['nullable', 'string'],
            'aktivitas'             => ['nullable', 'string'],
            'referensi'             => ['nullable', 'string'],
            'status'                => ['required', 'in:draft,published,completed'],
        ]);

        // Validate ustadz_kelas belongs to ustadz if user is ustadz
        if ($user->ustadz) {
            $ustadzKelasIds = $user->ustadz->ustadzKelas()->pluck('id')->toArray();
            if (!in_array($data['ustadz_kelas_id'], $ustadzKelasIds)) {
                abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
            }
        }

        // If jadwal_id provided, validate it matches the ustadz_kelas
        if ($data['jadwal_id']) {
            $jadwal = Schedule::find($data['jadwal_id']);
            if ($jadwal && $jadwal->ustadz_kelas_id != $data['ustadz_kelas_id']) {
                return back()->withInput()->with('error', 'Jadwal tidak sesuai dengan penugasan ustadz.');
            }
        }

        Materi::create([
            'tenant_id'           => tenant_id(),
            'program_id'          => $program->id,
            'jadwal_id'           => $data['jadwal_id'],
            'ustadz_kelas_id'     => $data['ustadz_kelas_id'],
            'kelas_id'            => $data['kelas_id'],
            'subject_id'          => $data['subject_id'],
            'tanggal'             => $data['tanggal'],
            'judul'               => $data['judul'],
            'deskripsi'           => $data['deskripsi'],
            'tujuan_pembelajaran' => $data['tujuan_pembelajaran'],
            'aktivitas'           => $data['aktivitas'],
            'referensi'           => $data['referensi'],
            'status'              => $data['status'],
        ]);

        if ($data['jadwal_id']) {
            return redirect(tenant_route('dashboard.akademik.materi.by-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $data['jadwal_id']]))
                ->with('success', 'Materi berhasil ditambahkan.');
        }

        return redirect(tenant_route('dashboard.akademik.materi.index', ['programSlug' => $programSlug]))
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Edit form
     */
    public function edit(string $programSlug, int $id)
    {
        $materi = Materi::with(['jadwal', 'ustadzKelas'])->findOrFail($id);
        $this->authorize('update', $materi);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user = auth()->user();

        $ustadzKelas = UstadzKelas::with(['ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->when($user->ustadz, function ($query) use ($user) {
                $query->where('ustadz_id', $user->ustadz->id);
            })
            ->get();

        $jadwalList = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject'])
            ->where('program_id', $program->id)
            ->when($user->ustadz, function ($query) use ($user) {
                $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
                $query->whereIn('ustadz_kelas_id', $assignedIds);
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('dashboard.materi.edit', compact('materi', 'programSlug', 'program', 'ustadzKelas', 'jadwalList'));
    }

    /**
     * Update materi
     */
    public function update(Request $request, string $programSlug, int $id)
    {
        $materi = Materi::findOrFail($id);
        $this->authorize('update', $materi);

        $data = $request->validate([
            'jadwal_id'           => ['nullable', 'integer', 'exists:jadwal,id'],
            'ustadz_kelas_id'     => ['required', 'integer', 'exists:ustadz_kelas,id'],
            'kelas_id'            => ['required', 'integer', 'exists:kelas,id'],
            'subject_id'          => ['required', 'integer', 'exists:subjects,id'],
            'tanggal'             => ['required', 'date'],
            'judul'               => ['required', 'string', 'max:255'],
            'deskripsi'           => ['nullable', 'string'],
            'tujuan_pembelajaran' => ['nullable', 'string'],
            'aktivitas'           => ['nullable', 'string'],
            'referensi'           => ['nullable', 'string'],
            'status'              => ['required', 'in:draft,published,completed'],
        ]);

        $materi->update($data);

        if ($materi->jadwal_id) {
            return redirect(tenant_route('dashboard.akademik.materi.by-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $materi->jadwal_id]))
                ->with('success', 'Materi berhasil diperbarui.');
        }

        return redirect(tenant_route('dashboard.akademik.materi.index', ['programSlug' => $programSlug]))
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Delete materi
     */
    public function destroy(string $programSlug, int $id)
    {
        $materi = Materi::findOrFail($id);
        $this->authorize('delete', $materi);

        $jadwalId = $materi->jadwal_id;
        $materi->delete();

        if ($jadwalId) {
            return redirect(tenant_route('dashboard.akademik.materi.by-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $jadwalId]))
                ->with('success', 'Materi berhasil dihapus.');
        }

        return redirect(tenant_route('dashboard.akademik.materi.index', ['programSlug' => $programSlug]))
            ->with('success', 'Materi berhasil dihapus.');
    }
}
