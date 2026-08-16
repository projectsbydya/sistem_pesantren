<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProgramAssessmentConfig;
use App\Services\AssessmentTypeService;
use App\Services\ProgramAccessService;
use Illuminate\Http\Request;

class ProgramAssessmentConfigController extends Controller
{
    public function __construct(private AssessmentTypeService $assessmentTypeService) {}

    public function index(string $programSlug)
    {
        $this->authorize('viewAny', ProgramAssessmentConfig::class);

        $program = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $configs = ProgramAssessmentConfig::with('assessmentType')
            ->where('program_id', $program->id)
            ->orderBy('sort_order')
            ->orderBy('assessment_type_id')
            ->get();

        return view('dashboard.assessment-config.index', compact('program', 'programSlug', 'configs'));
    }

    public function update(Request $request, string $programSlug, ProgramAssessmentConfig $config)
    {
        $program = ProgramAccessService::getBySlug($programSlug) ?? abort(404);

        abort_unless((int) $config->program_id === (int) $program->id, 404);

        $this->authorize('update', $config);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
            'weight' => ['nullable', 'numeric', 'between:0,100'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if (! $data['is_active'] && $config->is_active && $this->assessmentTypeService->getActiveTypes($program->id)->count() <= 1) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'Setidaknya satu jenis penilaian harus tetap aktif.']);
        }

        $config->update($data);

        return back()->with('success', 'Konfigurasi penilaian berhasil diperbarui.');
    }
}
