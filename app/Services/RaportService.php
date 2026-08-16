<?php

namespace App\Services;

use App\Models\AbsensiSantri;
use App\Models\Nilai;
use App\Models\Program;
use App\Models\Raport;
use App\Models\RaportNilai;
use App\Models\RaportNilaiComponent;
use App\Models\Santri;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RaportService
{
    private AssessmentCalculationService $calculationService;

    public function __construct(AssessmentCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Generate a raport header and snapshot all subject grades from nilai.
     *
     * @param array<string, mixed> $headerData
     * @param array<string, string> $dateRange
     */
    public function generate(
        Program $program,
        Santri $santri,
        Collection $subjects,
        array $dateRange,
        array $headerData
    ): Raport {
        return DB::transaction(function () use ($program, $santri, $subjects, $dateRange, $headerData) {
            $raport = Raport::create([
                'tenant_id'          => tenant_id(),
                'program_id'         => $program->id,
                'santri_id'          => $santri->id,
                'kelas_id'           => $headerData['kelas_id'],
                'semester'           => $headerData['semester'],
                'tahun_ajaran'       => $headerData['tahun_ajaran'],
                'status'             => 'draft',
                'total_hari_efektif' => $headerData['total_hari_efektif'],
                'sakit'              => 0,
                'izin'               => 0,
                'alpa'               => 0,
            ]);

            $this->buildSnapshot($raport, $program, $santri, $subjects, $dateRange);

            return $raport;
        });
    }

    /**
     * Regenerate a draft raport snapshot from the current nilai records.
     */
    public function regenerate(Raport $raport): Raport
    {
        if ($raport->status !== 'draft') {
            throw new InvalidArgumentException('Only draft raports can be regenerated.');
        }

        return DB::transaction(function () use ($raport) {
            $raport->update([
                'status' => 'draft',
                'tanggal_diterbitkan' => null,
            ]);

            $descriptions = RaportNilai::where('raport_id', $raport->id)
                ->pluck('deskripsi', 'subject_id')
                ->toArray();

            RaportNilai::where('raport_id', $raport->id)->delete();

            $program = Program::findOrFail($raport->program_id);
            $santri = Santri::findOrFail($raport->santri_id);
            $subjects = Subject::where('program_id', $program->id)->get();
            $dateRange = $this->semesterDateRange($raport->semester, $raport->tahun_ajaran);

            $this->buildSnapshot($raport, $program, $santri, $subjects, $dateRange);

            foreach ($raport->fresh()->nilaiRaport as $nilai) {
                if (array_key_exists($nilai->subject_id, $descriptions)) {
                    $nilai->update(['deskripsi' => $descriptions[$nilai->subject_id]]);
                }
            }

            return $raport;
        });
    }

    /**
     * Build the subject score snapshot for a raport from the source nilai records.
     */
    private function buildSnapshot(
        Raport $raport,
        Program $program,
        Santri $santri,
        Collection $subjects,
        array $dateRange
    ): void {
        $configs = $this->calculationService->getTypeConfigs($program->id);

        $nilaiRecords = Nilai::where('program_id', $program->id)
            ->where('santri_id', $santri->id)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->get(['subject_id', 'assessment_type', 'nilai', 'tanggal'])
            ->groupBy(['subject_id', 'assessment_type']);

        foreach ($subjects as $subject) {
            $nilaiByType = $nilaiRecords->get($subject->id, collect());
            $components = $this->calculationService->calculateComponents($nilaiByType, $configs);
            $final = $this->calculationService->calculateFinal($components);

            $raportNilai = RaportNilai::create([
                'tenant_id'     => tenant_id(),
                'raport_id'     => $raport->id,
                'subject_id'    => $subject->id,
                // nilai_akhir is a NOT NULL column (default 0); fall back to 0
                // when the santri has no assessment records yet for this subject.
                'nilai_akhir'   => $final ?? 0,
                'predikat'      => $this->calculationService->determinePredikat($final),
                'deskripsi'     => null,
            ] + $this->calculationService->legacyColumnMap($components));

            foreach ($components as $component) {
                RaportNilaiComponent::create([
                    'tenant_id'          => tenant_id(),
                    'raport_nilai_id'    => $raportNilai->id,
                    'assessment_type_id' => $component['assessment_type_id'],
                    'assessment_code'    => $component['assessment_code'],
                    'assessment_label'   => $component['assessment_label'],
                    'score'              => $component['score'],
                    'weight'             => $component['weight'],
                    'contribution'       => $component['contribution'],
                ]);
            }
        }

        $this->syncAbsensiCounts($raport, $program->id, $santri->id, $dateRange);
    }

    /**
     * Calculate the date range for a semester within an academic year.
     */
    private function semesterDateRange(string $semester, string $tahunAjaran): array
    {
        [$startYear, $endYear] = explode('/', $tahunAjaran);

        if ($semester === 'ganjil') {
            return [
                'start' => "{$startYear}-07-01",
                'end' => "{$startYear}-12-31",
            ];
        }

        return [
            'start' => "{$endYear}-01-01",
            'end' => "{$endYear}-06-30",
        ];
    }

    /**
     * Update an existing raport's header and per-subject descriptions.
     * Score snapshots remain immutable; regenerate the draft raport to
     * reflect corrections made in the source nilai records.
     *
     * @param array<int, array<string, mixed>> $nilaiData
     * @param array<string, mixed> $headerData
     */
    public function update(Raport $raport, array $nilaiData, array $headerData): Raport
    {
        if ($raport->status === 'published') {
            throw new InvalidArgumentException('Published raports cannot be edited.');
        }

        return DB::transaction(function () use ($raport, $nilaiData, $headerData) {
            $raport->update([
                'catatan_umum'     => $headerData['catatan_umum'] ?? null,
                'kepala_pesantren' => $headerData['kepala_pesantren'] ?? null,
                'nip_kepala'       => $headerData['nip_kepala'] ?? null,
                'sakit'            => $headerData['sakit'] ?? $raport->sakit,
                'izin'             => $headerData['izin'] ?? $raport->izin,
                'alpa'             => $headerData['alpa'] ?? $raport->alpa,
            ]);

            foreach ($nilaiData as $raportNilaiId => $item) {
                $raportNilai = RaportNilai::find($raportNilaiId);

                if (! $raportNilai || $raportNilai->raport_id !== $raport->id) {
                    continue;
                }

                if (array_key_exists('deskripsi', $item)) {
                    $raportNilai->update([
                        'deskripsi' => $item['deskripsi'] ?? null,
                    ]);
                }
            }

            return $raport;
        });
    }

    /**
     * Publish a draft raport.
     */
    public function publish(Raport $raport): Raport
    {
        if ($raport->status !== 'draft') {
            throw new InvalidArgumentException('Only draft raports can be published.');
        }

        $raport->update([
            'status' => 'published',
            'tanggal_diterbitkan' => now(),
        ]);

        return $raport;
    }

    /**
     * Return a published raport to draft for corrections.
     */
    public function unpublish(Raport $raport): Raport
    {
        if ($raport->status !== 'published') {
            throw new InvalidArgumentException('Only published raports can be unpublished.');
        }

        $raport->update([
            'status' => 'draft',
            'tanggal_diterbitkan' => null,
        ]);

        return $raport;
    }

    /**
     * @param array<string, string> $dateRange
     */
    private function syncAbsensiCounts(Raport $raport, int $programId, int $santriId, array $dateRange): void
    {
        $counts = AbsensiSantri::where('program_id', $programId)
            ->where('santri_id', $santriId)
            ->whereBetween('tanggal', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $raport->update([
            'sakit' => $counts['sakit'] ?? 0,
            'izin'  => $counts['izin'] ?? 0,
            'alpa'  => $counts['alpa'] ?? 0,
        ]);
    }
}
