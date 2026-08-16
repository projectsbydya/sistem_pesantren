<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HafalanNilai;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\Subject;
use App\Models\UstadzKelas;
use App\Services\Akademik\UstadzKelasResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HafalanNilaiController extends Controller
{

    /**
     * Step 1: pilih kelas → subject (scoped by ustadz_kelas)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', HafalanNilai::class);

        $user = auth()->user();

        // Relation-based filtering: USER -> Ustadz -> UstadzKelas -> Kelas
        $kelasList = $user->ustadz
            ? $user->ustadz->ustadzKelas()
                ->with(['kelas', 'subject'])
                ->get()
                ->groupBy('kelas_id')
                ->map(function ($items) {
                    $kelas = $items->first()->kelas;
                    $kelas->setRelation('subjects', $items->pluck('subject')->unique('id'));
                    return $kelas;
                })
                ->values()
            : Kelas::with('subjects')->orderBy('name')->get();

        return view('dashboard.hafalan-nilai.index', compact('kelasList'));
    }

    /**
     * Step 2: form input
     */
    public function input(Request $request)
    {
        $request->validate([
            'kelas_id'   => ['required', 'integer', 'exists:kelas,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'tanggal'    => ['required', 'date'],
            'jenis'      => ['required', Rule::in(HafalanNilai::JENIS)],
        ]);

        $user    = auth()->user();
        $kelas   = Kelas::findOrFail((int) $request->kelas_id);
        $subject = Subject::findOrFail((int) $request->subject_id);
        $tanggal = $request->tanggal;
        $jenis   = $request->jenis;

        // ✅ resolve ustadz_kelas (SINGLE SOURCE OF TRUTH)
        $ustadzKelas = $this->resolver->resolve(
            $user,
            $kelas->id,
            $subject->id
        );

        // ✅ POLICY
        $this->authorize('inputFor', [HafalanNilai::class, $ustadzKelas]);

        $santriList = Santri::where('kelas_id', $ustadzKelas->kelas_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // ✅ gunakan ustadz_kelas_id (bukan kelas+subject)
        $existing = HafalanNilai::where('ustadz_kelas_id', $ustadzKelas->id)
            ->where('tanggal', $tanggal)
            ->where('jenis', $jenis)
            ->get()
            ->keyBy('santri_id');

        return view('dashboard.hafalan-nilai.input', compact(
            'kelas',
            'subject',
            'tanggal',
            'jenis',
            'santriList',
            'existing'
        ));
    }

    /**
     * Step 3: store bulk
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'kelas_id'              => ['required', 'integer', 'exists:kelas,id'],
            'subject_id'            => ['required', 'integer', 'exists:subjects,id'],
            'tanggal'               => ['required', 'date'],
            'jenis'                 => ['required', Rule::in(HafalanNilai::JENIS)],
            'records'               => ['required', 'array', 'min:1'],
            'records.*.santri_id'   => ['required', 'integer', 'exists:santri,id'],
            'records.*.nilai'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'records.*.materi'      => ['nullable', 'string', 'max:255'],
            'records.*.catatan'     => ['nullable', 'string', 'max:500'],
        ]);

        $user    = auth()->user();
        $kelas   = Kelas::findOrFail((int) $request->kelas_id);
        $subject = Subject::findOrFail((int) $request->subject_id);
        $tanggal = $request->tanggal;
        $jenis   = $request->jenis;

        // ✅ resolve ustadz_kelas
        $ustadzKelas = $this->resolver->resolve($user, $kelas->id, $subject->id);

        // ✅ POLICY
        $this->authorize('inputFor', [HafalanNilai::class, $ustadzKelas]);

        DB::transaction(function () use ($request, $kelas, $subject, $tanggal, $jenis, $ustadzKelas) {
            foreach ($request->records as $row) {
                HafalanNilai::updateOrCreate(
                    [
                        'santri_id'       => $row['santri_id'],
                        'ustadz_kelas_id' => $ustadzKelas->id,
                        'tanggal'         => $tanggal,
                        'jenis'           => $jenis,
                    ],
                    [
                        'kelas_id'        => $kelas->id,
                        'subject_id'      => $subject->id,
                        'materi'          => $row['materi'] ?? null,
                        'nilai'           => $row['nilai'] ?? null,
                        'catatan'         => $row['catatan'] ?? null,
                    ]
                );
            }
        });

        return redirect(tenant_route('dashboard.hafalan-nilai.index'))
            ->with('success', 'Data hafalan/nilai berhasil disimpan.');
    }

    /**
     * Progress view
     */
    public function show(Request $request, int $santriId)
    {
        $user = auth()->user();

        $santri = Santri::with(['kelas'])
            ->findOrFail($santriId);

        // ✅ WAJIB: pakai policy
        $this->authorize('view', $santri);

        // ✅ ambil ustadz_kelas_id milik user
        $ustadzKelasIds = $user->ustadz->ustadzKelas()->pluck('id');

        $records = HafalanNilai::with(['subject', 'kelas', 'ustadzKelas'])
            ->where('santri_id', $santri->id)
            ->whereIn('ustadz_kelas_id', $ustadzKelasIds)
            ->orderByDesc('tanggal')
            ->get();

        $bySubject = $records->groupBy('subject_id');

        return view('dashboard.hafalan-nilai.show', compact('santri', 'records', 'bySubject'));
    }

    public function __construct(
        private UstadzKelasResolver $resolver
    ) {}
}