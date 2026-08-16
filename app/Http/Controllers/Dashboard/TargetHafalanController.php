<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TargetHafalan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TargetHafalanController extends Controller
{
    /**
     * List all target hafalan.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TargetHafalan::class);
        $targets = TargetHafalan::with('santri.kelas')
            ->orderBy('deadline', 'asc')
            ->get();

        $santriList = Santri::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.target-hafalan.index', compact('targets', 'santriList'));
    }

    /**
     * Store a new target.
     */
    public function store(Request $request)
    {
        $this->authorize('create', TargetHafalan::class);
        $data = $request->validate([
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'type'      => ['required', Rule::in(TargetHafalan::TYPES)],
            'target'    => ['required', 'string', 'max:255'],
            'deadline'  => ['nullable', 'date'],
            'status'    => ['required', Rule::in(TargetHafalan::STATUS)],
            'catatan'   => ['nullable', 'string', 'max:500'],
        ]);

        TargetHafalan::create($data);

        return redirect(tenant_route('dashboard.kepesantrenan.target-hafalan.index'))
            ->with('success', 'Target hafalan berhasil ditambahkan.');
    }

    /**
     * Update status of a target.
     */
    public function update(Request $request, int $id)
    {
        $target = TargetHafalan::findOrFail($id);
        $this->authorize('update', $target);

        $data = $request->validate([
            'status'  => ['required', Rule::in(TargetHafalan::STATUS)],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $target->update($data);

        return redirect(tenant_route('dashboard.kepesantrenan.target-hafalan.index'))
            ->with('success', 'Target hafalan diperbarui.');
    }

    /**
     * Delete a target.
     */
    public function destroy(int $id)
    {
        $target = TargetHafalan::findOrFail($id);
        $this->authorize('delete', $target);
        $target->delete();

        return redirect(tenant_route('dashboard.kepesantrenan.target-hafalan.index'))
            ->with('success', 'Target dihapus.');
    }
}
