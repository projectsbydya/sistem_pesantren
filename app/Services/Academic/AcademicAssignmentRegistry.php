<?php

namespace App\Services\Academic;

/**
 * Extensible registry for class-centric academic assignment types.
 *
 * Add a new type here; no controller/view code needs to reference it by key.
 */
final class AcademicAssignmentRegistry
{
    /**
     * Assignment lifecycle (Assignment.state) — distinct from a student's
     * progress, which is AssignmentMember.status.
     */
    private const ASSIGNMENT_STATES = [
        'draft'     => 'Draft',
        'published' => 'Diterbitkan',
        'archived'  => 'Diarsipkan',
    ];

    private const ASSIGNMENT_STATE_COLORS = [
        'draft'     => 'gray',
        'published' => 'emerald',
        'archived'  => 'slate',
    ];

    /**
     * Member field definitions.
     *
     * 'column' => true  → maps to a physical AssignmentMember column (reused by
     *                     most/all types: progress, status, score, notes).
     * 'column' => false → stored in AssignmentMember.metadata (type-specific,
     *                     e.g. muhadhoroh's performed_at/submission_url).
     */
    private const MEMBER_FIELD_DEFINITIONS = [
        'progress' => [
            'label'       => 'Progress / Capaian',
            'type'        => 'textarea',
            'placeholder' => 'Deskripsikan capaian santri...',
            'column'      => true,
        ],
        'status' => [
            'label'  => 'Status',
            'type'   => 'select',
            'column' => true,
        ],
        'score' => [
            'label'  => 'Nilai',
            'type'   => 'number',
            'min'    => 0,
            'max'    => 100,
            'column' => true,
        ],
        'notes' => [
            'label'       => 'Catatan',
            'type'        => 'textarea',
            'placeholder' => 'Catatan untuk santri...',
            'column'      => true,
        ],
        'performed_at' => [
            'label'  => 'Tanggal Penampilan',
            'type'   => 'date',
            'column' => false,
        ],
        'submission_url' => [
            'label'       => 'URL Video / Tugas',
            'type'        => 'url',
            'placeholder' => 'https://...',
            'column'      => false,
        ],
        'is_video_submission' => [
            'label'  => 'Kiriman Video',
            'type'   => 'boolean',
            'column' => false,
        ],
    ];

    private const TYPES = [
        'diniyah-hafalan-doa' => [
            'pack'          => 'diniyah',
            'feature'       => 'hafalan-doa',
            'variant'       => 'doa',
            'label'         => 'Hafalan Doa',
            'icon'          => 'fa-hands-praying',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['progress', 'status', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Judul',  'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Target', 'type' => 'textarea', 'required' => false, 'column' => true],
            ],
        ],

        'diniyah-hafalan-hadits' => [
            'pack'          => 'diniyah',
            'feature'       => 'hafalan-hadits',
            'variant'       => 'hadits',
            'label'         => 'Hafalan Hadits',
            'icon'          => 'fa-book-open',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['progress', 'status', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Judul',  'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Target', 'type' => 'textarea', 'required' => false, 'column' => true],
            ],
        ],

        'diniyah-hafalan-surat' => [
            'pack'          => 'diniyah',
            'feature'       => 'hafalan-surat',
            'variant'       => 'surat',
            'label'         => 'Hafalan Surat',
            'icon'          => 'fa-book-quran',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['progress', 'status', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Judul',  'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Target', 'type' => 'textarea', 'required' => false, 'column' => true],
            ],
        ],

        'modern-vocabulary-arabic' => [
            'pack'          => 'modern',
            'feature'       => 'vocabulary',
            'variant'       => 'arabic',
            'label'         => 'Mufrodat (Arab)',
            'icon'          => 'fa-language',
            'member_statuses'       => ['belum', 'proses', 'hafal'],
            'member_status_labels'  => ['belum' => 'Belum Hafal', 'proses' => 'Sedang Dihafal', 'hafal' => 'Sudah Hafal'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'hafal' => 'emerald'],
            'member_fields'         => ['status', 'score', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Kosa Kata',  'type' => 'text', 'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Terjemahan', 'type' => 'text', 'required' => false, 'max' => 255, 'column' => true],
                ['name' => 'language',         'label' => 'Bahasa',           'type' => 'select', 'required' => true,  'options' => ['ar' => 'Arab', 'en' => 'Inggris'], 'column' => false],
                ['name' => 'example_sentence', 'label' => 'Contoh Kalimat',   'type' => 'textarea', 'required' => false, 'column' => false],
                ['name' => 'category',         'label' => 'Kategori',         'type' => 'text',     'required' => false, 'max' => 100, 'column' => false],
            ],
        ],

        'modern-vocabulary-english' => [
            'pack'          => 'modern',
            'feature'       => 'vocabulary',
            'variant'       => 'english',
            'label'         => 'Vocabulary (Inggris)',
            'icon'          => 'fa-language',
            'member_statuses'       => ['belum', 'proses', 'hafal'],
            'member_status_labels'  => ['belum' => 'Belum Hafal', 'proses' => 'Sedang Dihafal', 'hafal' => 'Sudah Hafal'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'hafal' => 'emerald'],
            'member_fields'         => ['status', 'score', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Kosa Kata',  'type' => 'text', 'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Terjemahan', 'type' => 'text', 'required' => false, 'max' => 255, 'column' => true],
                ['name' => 'language',         'label' => 'Bahasa',           'type' => 'select', 'required' => true,  'options' => ['ar' => 'Arab', 'en' => 'Inggris'], 'column' => false],
                ['name' => 'example_sentence', 'label' => 'Contoh Kalimat',   'type' => 'textarea', 'required' => false, 'column' => false],
                ['name' => 'category',         'label' => 'Kategori',         'type' => 'text',     'required' => false, 'max' => 100, 'column' => false],
            ],
        ],

        'modern-muhadatsah-arabic' => [
            'pack'          => 'modern',
            'feature'       => 'muhadatsah',
            'variant'       => 'arabic',
            'label'         => 'Muhadatsah (Arab)',
            'icon'          => 'fa-comments',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['score', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Topik',   'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Isi',     'type' => 'textarea', 'required' => false, 'column' => true],
                ['name' => 'category', 'label' => 'Kategori', 'type' => 'text', 'required' => false, 'max' => 100, 'column' => false],
            ],
        ],

        'modern-muhadatsah-english' => [
            'pack'          => 'modern',
            'feature'       => 'muhadatsah',
            'variant'       => 'english',
            'label'         => 'Conversation (Inggris)',
            'icon'          => 'fa-comments',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['score', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Topik',   'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Isi',     'type' => 'textarea', 'required' => false, 'column' => true],
                ['name' => 'category', 'label' => 'Kategori', 'type' => 'text', 'required' => false, 'max' => 100, 'column' => false],
            ],
        ],

        'modern-muhadhoroh-muhadhoroh' => [
            'pack'          => 'modern',
            'feature'       => 'muhadhoroh',
            'variant'       => 'muhadhoroh',
            'label'         => 'Muhadhoroh (Pidato)',
            'icon'          => 'fa-microphone',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['status', 'score', 'performed_at', 'submission_url', 'is_video_submission', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Judul Pidato',  'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Deskripsi',     'type' => 'textarea', 'required' => false, 'column' => true],
                ['name' => 'theme_id', 'label' => 'Tema',        'type' => 'theme',    'required' => false, 'column' => false],
                ['name' => 'language', 'label' => 'Bahasa',      'type' => 'select',   'required' => false, 'options' => ['ar' => 'Arab', 'en' => 'Inggris', 'id' => 'Indonesia'], 'column' => false],
            ],
        ],

        'modern-muhadhoroh-public-speaking' => [
            'pack'          => 'modern',
            'feature'       => 'muhadhoroh',
            'variant'       => 'public-speaking',
            'label'         => 'Public Speaking',
            'icon'          => 'fa-microphone-lines',
            'member_statuses'       => ['belum', 'proses', 'selesai'],
            'member_status_labels'  => ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'],
            'member_status_colors'  => ['belum' => 'gray', 'proses' => 'amber', 'selesai' => 'emerald'],
            'member_fields'         => ['status', 'score', 'performed_at', 'submission_url', 'is_video_submission', 'notes'],
            'assignment_fields'     => [
                ['name' => 'title',  'label' => 'Judul',         'type' => 'text',     'required' => true, 'max' => 255, 'column' => true],
                ['name' => 'target', 'label' => 'Deskripsi',     'type' => 'textarea', 'required' => false, 'column' => true],
                ['name' => 'theme_id', 'label' => 'Tema',        'type' => 'theme',    'required' => false, 'column' => false],
                ['name' => 'language', 'label' => 'Bahasa',      'type' => 'select',   'required' => false, 'options' => ['ar' => 'Arab', 'en' => 'Inggris', 'id' => 'Indonesia'], 'column' => false],
            ],
        ],
    ];

    // =====================================================================
    // Public accessors
    // =====================================================================

    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    public static function all(): array
    {
        return self::TYPES;
    }

    public static function get(string $type): ?array
    {
        return self::TYPES[$type] ?? null;
    }

    public static function has(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function resolve(string $pack, string $feature, ?string $variant = null): ?string
    {
        foreach (self::TYPES as $key => $config) {
            if ($config['pack'] === $pack && $config['feature'] === $feature) {
                if ($variant === null || $config['variant'] === $variant) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function resolveFromRequest(string $pack, string $feature, ?string $queryType = null): ?string
    {
        return self::resolve($pack, $feature, $queryType);
    }

    public static function label(string $type): string
    {
        return self::TYPES[$type]['label'] ?? $type;
    }

    public static function icon(string $type): string
    {
        return self::TYPES[$type]['icon'] ?? 'fa-circle';
    }

    public static function assignmentStates(): array
    {
        return array_keys(self::ASSIGNMENT_STATES);
    }

    public static function assignmentStateLabels(): array
    {
        return self::ASSIGNMENT_STATES;
    }

    public static function assignmentStateColors(): array
    {
        return self::ASSIGNMENT_STATE_COLORS;
    }

    public static function memberStatuses(string $type): array
    {
        return self::TYPES[$type]['member_statuses'] ?? ['belum', 'proses', 'selesai'];
    }

    public static function memberStatusLabels(string $type): array
    {
        return self::TYPES[$type]['member_status_labels'] ?? [];
    }

    public static function memberStatusColors(string $type): array
    {
        return self::TYPES[$type]['member_status_colors'] ?? [];
    }

    public static function memberFields(string $type): array
    {
        $names = self::TYPES[$type]['member_fields'] ?? [];

        return array_filter(
            self::MEMBER_FIELD_DEFINITIONS,
            fn (string $key) => in_array($key, $names, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    public static function allMemberFieldDefinitions(): array
    {
        return self::MEMBER_FIELD_DEFINITIONS;
    }

    public static function assignmentFields(string $type): array
    {
        return self::TYPES[$type]['assignment_fields'] ?? [];
    }

    public static function packAndFeature(string $type): ?array
    {
        $config = self::TYPES[$type] ?? null;

        if ($config === null) {
            return null;
        }

        return [
            'pack'    => $config['pack'],
            'feature' => $config['feature'],
            'variant' => $config['variant'],
        ];
    }

    public static function typesForPack(string $pack): array
    {
        return array_filter(
            self::TYPES,
            fn (array $config) => $config['pack'] === $pack,
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function variantOptions(string $pack, string $feature): array
    {
        $options = [];

        foreach (self::TYPES as $key => $config) {
            if ($config['pack'] === $pack && $config['feature'] === $feature) {
                $options[$config['variant']] = $config['label'];
            }
        }

        return $options;
    }
}
