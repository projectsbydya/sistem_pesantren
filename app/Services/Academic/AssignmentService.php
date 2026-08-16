<?php

namespace App\Services\Academic;

use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class-centric assignment CRUD + student progress management.
 *
 * All business logic for assignments (creation, member enrollment,
 * validation, updates) lives here — controllers only orchestrate HTTP
 * concerns (authorization gate calls, request/response mapping).
 *
 * SNAPSHOT SEMANTICS: an Assignment's roster (AssignmentMember rows) is
 * generated exactly once — at the moment the assignment transitions to (or
 * is created directly as) state=published — never on initial draft
 * creation, and never re-synced afterward. Students added to the class
 * after publication are intentionally NOT retroactively enrolled.
 */
class AssignmentService
{
    // =====================================================================
    // Type resolution
    // =====================================================================

    public function resolveType(string $pack, string $feature, ?string $variant = null): string
    {
        $type = AcademicAssignmentRegistry::resolve($pack, $feature, $variant);

        abort_if($type === null, 404, 'Tipe assignment tidak ditemukan.');

        return $type;
    }

    public function resolveProgram(string $programSlug): Program
    {
        return Program::where('slug', $programSlug)->firstOrFail();
    }

    public function getTypeConfig(string $type): array
    {
        $config = AcademicAssignmentRegistry::get($type);

        abort_if($config === null, 404, 'Konfigurasi tipe tidak ditemukan.');

        return $config;
    }

    // =====================================================================
    // Lists
    // =====================================================================

    public function getKelasListForProgram(Program $program): Collection
    {
        return Kelas::where('program_id', $program->id)
            ->orderBy('name')
            ->get();
    }

    public function getSantriListForProgram(Program $program): Collection
    {
        return Santri::where('status', 'active')
            ->whereHas('programs', fn (Builder $q) => $q->where('program_id', $program->id))
            ->orderBy('name')
            ->get();
    }

    public function getSantriListForKelas(Kelas $kelas): Collection
    {
        return Santri::where('status', 'active')
            ->whereHas('programs', fn (Builder $q) => $q->where('kelas_id', $kelas->id))
            ->orderBy('name')
            ->get();
    }

    public function getThemeListForProgram(Program $program): Collection
    {
        return \App\Models\MuhadhorohTheme::byProgram($program->id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    // =====================================================================
    // Queries
    // =====================================================================

    /**
     * Get assignments for a program + type, scoped to the given tenant/program
     * plus the caller's own visibility (student/parent/ustadz/staff).
     *
     * Students/parents: read-only — only PUBLISHED assignments that have a
     * member for an accessible santri, and the kelas filter is ignored (they
     * must not be able to browse other classes' assignment lists).
     * Ustadz: only assignments for their assigned classes.
     * Admin/Tenant staff: all assignments in the program (any kelas/state).
     */
    public function getAssignmentsForProgram(Program $program, string $type, ?int $kelasId, ?User $user = null): Collection
    {
        $user ??= auth()->user();
        $isSantriOrParent = $user?->isStudent() || $user?->isParent();

        // NOTE: with()'s constraint closure receives a Relation (HasMany) instance,
        // not a Builder — do not type-hint Builder here or it throws a TypeError.
        $membersWith = fn ($q) => $q->with('santri');

        if ($isSantriOrParent) {
            $santriIds = $user->getAccessibleSantriIds();
            $membersWith = fn ($q) => $q->whereIn('santri_id', $santriIds)->with('santri');
        }

        // program_id scoping is mandatory here — Assignment is tenant-scoped via
        // HasTenant/TenantScope automatically, but program isolation (a tenant can
        // run multiple programs) must be enforced explicitly per query.
        $query = Assignment::byProgram($program->id)
            ->byType($type)
            ->with(['kelas', 'members' => $membersWith])
            ->orderBy('created_at', 'desc');

        if ($isSantriOrParent) {
            // Students/parents are read-only viewers of their own roster: the
            // kelas filter is intentionally ignored (never let them browse
            // other classes), and only published assignments are visible —
            // drafts/archived assignments must never leak to them.
            $query->published();

            $santriIds = $user->getAccessibleSantriIds();

            if (empty($santriIds)) {
                return collect();
            }

            $query->whereHas('members', fn (Builder $q) => $q->whereIn('santri_id', $santriIds));
        } elseif ($kelasId !== null) {
            $query->byKelas($kelasId);
        }

        if ($user?->ustadz) {
            $kelasIds = $this->getUstadzKelasIds($user);

            if (empty($kelasIds)) {
                return collect();
            }

            $query->whereIn('kelas_id', $kelasIds);
        }

        return $query->get();
    }

    /**
     * Get members for a specific santri + program + type.
     *
     * Students/parents only ever see PUBLISHED assignments (drafts/archived
     * must not leak). Staff/ustadz viewing a student's history via the same
     * route see the full history regardless of assignment state.
     */
    public function getMembersForSantri(int $santriId, Program $program, string $type, ?User $user = null): Collection
    {
        $user ??= auth()->user();
        $isReadOnlyViewer = $user?->isStudent() || $user?->isParent();

        return AssignmentMember::with('assignment')
            ->whereHas('assignment', function (Builder $q) use ($program, $type, $isReadOnlyViewer) {
                $q->byProgram($program->id)->byType($type);

                if ($isReadOnlyViewer) {
                    $q->published();
                }
            })
            ->bySantri($santriId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get members for an assignment.
     * For students/parents: filter to accessible santri.
     */
    public function getMembersForAssignment(Assignment $assignment, ?User $user = null): Collection
    {
        $user ??= auth()->user();

        $query = $assignment->members()->with('santri');

        if ($user?->isStudent() || $user?->isParent()) {
            $santriIds = $user->getAccessibleSantriIds();

            if (empty($santriIds)) {
                return collect();
            }

            $query->whereIn('santri_id', $santriIds);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // =====================================================================
    // CRUD
    // =====================================================================

    /**
     * Create an assignment scoped to the given (tenant-resolved) program.
     *
     * Assignment is treated as a SNAPSHOT: members are enrolled once, at the
     * moment the assignment is published — either directly (created with
     * state=published) or later via a draft → published transition in
     * updateAssignment(). Creating a draft never enrolls anyone. Students who
     * join the class after publication are intentionally NOT retroactively
     * enrolled — the roster is fixed at publish time.
     *
     * @throws ValidationException
     */
    public function createAssignment(array $data, string $type, Program $program, User $user): Assignment
    {
        $this->validateAssignmentData($data, $type, $program, 'create');

        // Defense in depth: kelas_id is already validated to belong to $program
        // in getValidationRules(), but we never trust request-supplied program_id.
        $assignmentData = $this->extractAssignmentData($data, $type);
        $assignmentData['program_id'] = $program->id;
        $assignmentData['type'] = $type;
        $assignmentData['created_by'] = $user->id;
        $assignmentData['updated_by'] = $user->id;

        $assignmentData['published_at'] = $assignmentData['state'] === 'published' ? now() : null;

        $assignment = Assignment::create($assignmentData);

        $this->publishMembersIfNeeded($assignment, $user);

        return $assignment;
    }

    public function updateAssignment(Assignment $assignment, array $data, User $user): Assignment
    {
        $this->validateAssignmentData($data, $assignment->type, $assignment->program, 'update');

        $assignmentData = $this->extractAssignmentData($data, $assignment->type);
        $assignmentData['updated_by'] = $user->id;

        if ($assignmentData['state'] === 'published') {
            $assignmentData['published_at'] = $assignment->published_at ?? now();
        } else {
            $assignmentData['published_at'] = null;
        }

        $assignment->update($assignmentData);

        // Snapshot enrollment happens exactly once, the first time the
        // assignment becomes published (draft → published, or archived →
        // published if it was somehow never enrolled). Re-publishing an
        // already-enrolled assignment does NOT re-sync the roster.
        $this->publishMembersIfNeeded($assignment, $user);

        return $assignment;
    }

    public function deleteAssignment(Assignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Update a student's progress/evaluation on an assignment.
     *
     * Universal fields (progress/status/score/notes) map to physical columns;
     * type-specific fields (e.g. muhadhoroh's performed_at/submission_url)
     * are validated and stored in metadata, driven by the type's registry entry.
     */
    public function updateMember(AssignmentMember $member, array $data, User $user): AssignmentMember
    {
        $type = $member->assignment->type;
        $fields = AcademicAssignmentRegistry::memberFields($type);

        $rules = [];
        foreach ($fields as $name => $field) {
            $rules[$name] = $this->buildMemberFieldRules($name, $field, $type);
        }

        $validated = Validator::make($data, $rules)->validate();

        $update = ['updated_by' => $user->id];
        $metadata = $member->metadata ?? [];

        foreach ($fields as $name => $field) {
            if (! array_key_exists($name, $validated)) {
                continue;
            }

            if ($field['column']) {
                $update[$name] = $validated[$name];
            } else {
                $metadata[$name] = $validated[$name];
            }
        }

        $update['metadata'] = $metadata;

        $member->update($update);

        return $member;
    }

    private function buildMemberFieldRules(string $name, array $field, string $type): array
    {
        if ($name === 'status') {
            return ['required', Rule::in(AcademicAssignmentRegistry::memberStatuses($type))];
        }

        $rules = ['nullable'];

        switch ($field['type']) {
            case 'textarea':
            case 'text':
                $rules[] = 'string';
                break;
            case 'number':
                $rules[] = 'integer';
                if (isset($field['min'])) {
                    $rules[] = 'min:' . $field['min'];
                }
                if (isset($field['max'])) {
                    $rules[] = 'max:' . $field['max'];
                }
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'url':
                $rules[] = 'url';
                $rules[] = 'max:500';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            default:
                $rules[] = 'string';
                $rules[] = 'max:500';
        }

        return $rules;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    public function getValidationRules(string $type, Program $program, string $mode = 'create'): array
    {
        $config = $this->getTypeConfig($type);
        $rules = [];

        if ($mode === 'create') {
            $rules['kelas_id'] = ['required', 'integer', Rule::exists('kelas', 'id')->where('program_id', $program->id)];
        } else {
            $rules['kelas_id'] = ['nullable', 'integer', Rule::exists('kelas', 'id')->where('program_id', $program->id)];
        }

        $rules['state'] = ['required', Rule::in(AcademicAssignmentRegistry::assignmentStates())];
        $rules['due_date'] = ['nullable', 'date'];
        $rules['notes'] = ['nullable', 'string', 'max:500'];

        foreach ($config['assignment_fields'] as $field) {
            $name = $field['column'] ? $field['name'] : "metadata.{$field['name']}";
            $fieldRules = [];

            if (! empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type']) {
                case 'text':
                    $fieldRules[] = 'string';
                    if (! empty($field['max'])) {
                        $fieldRules[] = 'max:' . $field['max'];
                    }
                    break;

                case 'textarea':
                    $fieldRules[] = 'string';
                    break;

                case 'number':
                    $fieldRules[] = 'integer';
                    if (isset($field['min'])) {
                        $fieldRules[] = 'min:' . $field['min'];
                    }
                    if (isset($field['max'])) {
                        $fieldRules[] = 'max:' . $field['max'];
                    }
                    break;

                case 'select':
                    if (! empty($field['options'])) {
                        $fieldRules[] = Rule::in(array_keys($field['options']));
                    } else {
                        $fieldRules[] = 'string';
                    }
                    break;

                case 'theme':
                    $fieldRules[] = 'integer';
                    $fieldRules[] = Rule::exists('muhadhoroh_themes', 'id')
                        ->where('tenant_id', tenant_id())
                        ->where('program_id', $program->id);
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'url':
                    $fieldRules[] = 'url';
                    if (! empty($field['max'])) {
                        $fieldRules[] = 'max:' . $field['max'];
                    } else {
                        $fieldRules[] = 'max:500';
                    }
                    break;

                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
            }

            $rules[$name] = $fieldRules;
        }

        return $rules;
    }

    public function validateAssignmentData(array $data, string $type, Program $program, string $mode): array
    {
        return Validator::make($data, $this->getValidationRules($type, $program, $mode))->validate();
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function extractAssignmentData(array $data, string $type): array
    {
        $config = $this->getTypeConfig($type);

        $assignment = [
            'kelas_id' => $data['kelas_id'] ?? null,
            'state'    => $data['state'] ?? 'published',
            'due_date' => $data['due_date'] ?? null,
            'notes'    => $data['notes'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ];

        foreach ($config['assignment_fields'] as $field) {
            if ($field['column']) {
                $assignment[$field['name']] = $data[$field['name']] ?? null;
            } else {
                $metadataName = $field['name'];
                $assignment['metadata'][$metadataName] = $data['metadata'][$metadataName] ?? null;
            }
        }

        return $assignment;
    }

    /**
     * Enroll the class roster into AssignmentMember rows — but only once, at
     * the moment the assignment is (or becomes) published, and only if it
     * hasn't already been enrolled.
     *
     * This is a ONE-TIME SNAPSHOT, not a live sync: students who join the
     * class after this runs are intentionally excluded, and re-publishing
     * (e.g. archived → published again) will not re-run it once members
     * already exist for the assignment.
     */
    private function publishMembersIfNeeded(Assignment $assignment, User $user): void
    {
        if ($assignment->state !== 'published') {
            return;
        }

        if ($assignment->members()->exists()) {
            return;
        }

        $this->snapshotMembers($assignment, $user);
    }

    /**
     * Snapshot every active student currently enrolled in the assignment's
     * class + program into AssignmentMember (tenant isolation is implicit —
     * SantriProgram and Santri are both tenant-scoped via HasTenant/TenantScope).
     */
    private function snapshotMembers(Assignment $assignment, User $user): void
    {
        if ($assignment->kelas_id === null) {
            return;
        }

        $santriIds = SantriProgram::where('kelas_id', $assignment->kelas_id)
            ->where('program_id', $assignment->program_id)
            ->where('status', 'aktif')
            ->whereHas('santri', fn (Builder $q) => $q->where('status', 'active'))
            ->pluck('santri_id')
            ->unique()
            ->values();

        $members = $santriIds->map(fn (int $santriId) => [
            'tenant_id'     => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'santri_id'     => $santriId,
            'progress'      => null,
            'status'        => 'belum',
            'score'         => null,
            'notes'         => null,
            'metadata'      => null,
            'updated_by'    => $user->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ])->toArray();

        if (! empty($members)) {
            AssignmentMember::insert($members);
        }
    }

    private function getUstadzKelasIds(User $user): array
    {
        if (! $user->ustadz) {
            return [];
        }

        return $user->ustadz->ustadzKelas()->pluck('kelas_id')->toArray();
    }
}
