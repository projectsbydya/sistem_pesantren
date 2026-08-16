<?php

/**
 * Academic Program Configuration
 *
 * IMPORTANT: Programs are now loaded dynamically from the Program model.
 * This config only defines feature templates and route mappings.
 * DO NOT add hardcoded program definitions here.
 *
 * @see \App\Models\Program
 * @see \App\Services\ProgramService
 */

return [
    /**
     * Feature definitions - applied dynamically to all programs
     * Routes use :programSlug placeholder resolved at runtime
     */
    'features' => [
        'penugasan' => [
            'name' => 'Penugasan Mengajar',
            'icon' => 'fa-user-tie',
            'route' => 'dashboard.akademik.penugasan.index',
            'description' => 'Manajemen penugasan ustadz ke kelas',
            'gate' => 'canViewUstadzKelas',
            'parent' => 'jadwal',
        ],
        'absensi' => [
            'name' => 'Absensi',
            'icon' => 'fa-clipboard-list',
            'route' => 'dashboard.akademik.absensi.index',
            'description' => 'Manajemen absensi santri',
            'gate' => 'canViewAbsensiSantri',
        ],
        'subjects' => [
            'name' => 'Mata Pelajaran',
            'icon' => 'fa-book',
            'route' => 'dashboard.akademik.subjects.index',
            'description' => 'Manajemen mata pelajaran',
            'gate' => 'canViewMataPelajaran',
        ],
        'kelas' => [
            'name' => 'Kelas',
            'icon' => 'fa-chalkboard',
            'route' => 'dashboard.akademik.kelas.index',
            'description' => 'Manajemen kelas',
            'gate' => 'canViewKelas',
        ],
        'jadwal' => [
            'name' => 'Jadwal Mengajar',
            'student_name' => 'Jadwal Pelajaran',
            'icon' => 'fa-calendar-alt',
            'route' => 'dashboard.akademik.jadwal.index',
            'description' => 'Manajemen jadwal pelajaran',
            'gate' => 'canViewJadwal',
        ],
        'nilai' => [
            'name' => 'Nilai',
            'icon' => 'fa-chart-line',
            'route' => 'dashboard.akademik.nilai.index',
            'description' => 'Manajemen nilai akademik',
            'gate' => 'canViewNilai',
        ],
        'elearning' => [
            'name' => 'E-Learning',
            'icon' => 'fa-laptop',
            'route' => 'dashboard.akademik.elearning.index',
            'description' => 'Platform e-learning',
            'gate' => 'canViewElearning',
        ],
        'materi' => [
            'name' => 'Materi',
            'icon' => 'fa-book-open',
            'route' => 'dashboard.akademik.materi.index',
            'description' => 'Rencana pembelajaran',
            'gate' => 'canViewMateri',
        ],
        'raport' => [
            'name' => 'E-Raport',
            'icon' => 'fa-file-invoice',
            'route' => 'dashboard.akademik.raport.index',
            'description' => 'Raport elektronik santri',
            'gate' => 'canViewRaport',
        ],
        'assessment-config' => [
            'name' => 'Konfigurasi Penilaian',
            'icon' => 'fa-sliders',
            'route' => 'dashboard.akademik.assessment-config.index',
            'description' => 'Konfigurasi jenis dan bobot penilaian',
            'gate' => 'canViewAssessmentConfig',
        ],
    ],

    /**
     * Program pack navigation items — sidebar nav metadata for pack-specific features.
     *
     * Key = program slug (matches programs.slug in DB).
     * Each entry is an ordered list of nav items: route, icon, name, gate.
     *
     * The sidebar reads this generically via config('academic_programs.packs.<slug>').
     * To add a new pack: add an entry here + implement pack-specific controller/service/views.
     * DO NOT touch sidebar.blade.php when adding new packs.
     *
     * gate = method name on NavigationGateService (must return bool).
     */
    'packs' => [
        'diniyah' => [
            ['route' => 'dashboard.diniyah.hafalan-doa.index',        'icon' => 'fa-hands-praying',      'name' => 'Hafalan Doa',        'gate' => 'canViewDiniyahHafalan'],
            ['route' => 'dashboard.diniyah.hafalan-hadits.index',      'icon' => 'fa-book-open',          'name' => 'Hafalan Hadits',     'gate' => 'canViewDiniyahHafalan'],
            ['route' => 'dashboard.diniyah.hafalan-surat.index',       'icon' => 'fa-book-quran',         'name' => 'Hafalan Surat',      'gate' => 'canViewDiniyahHafalan'],
            ['route' => 'dashboard.diniyah.monitoring-sholat.index',   'icon' => 'fa-person-praying',     'name' => 'Monitoring Sholat',  'gate' => 'canViewDiniyahMonitoring'],
            ['route' => 'dashboard.diniyah.monitoring-adab.index',     'icon' => 'fa-handshake',          'name' => 'Monitoring Adab',    'gate' => 'canViewDiniyahMonitoring'],
            ['route' => 'dashboard.diniyah.monitoring-akhlak.index',   'icon' => 'fa-heart',              'name' => 'Monitoring Akhlak',  'gate' => 'canViewDiniyahMonitoring'],
            ['route' => 'dashboard.diniyah.nilai-keagamaan.index',     'icon' => 'fa-star-and-crescent',  'name' => 'Nilai Keagamaan',    'gate' => 'canViewDiniyahAssessment'],
            ['route' => 'dashboard.diniyah.nilai-akhlak.index',        'icon' => 'fa-award',              'name' => 'Nilai Akhlak',       'gate' => 'canViewDiniyahAssessment'],
        ],
        'modern' => [
            ['route' => 'dashboard.modern.vocabulary.index',      'icon' => 'fa-language',       'name' => 'Vocabulary',      'gate' => 'canViewModernVocabulary'],
            ['route' => 'dashboard.modern.muhadatsah.index',      'icon' => 'fa-comments',       'name' => 'Muhadatsah',      'gate' => 'canViewModernMuhadatsah'],
            ['route' => 'dashboard.modern.muhadhoroh.index',      'icon' => 'fa-microphone',     'name' => 'Muhadhoroh',      'gate' => 'canViewModernMuhadhoroh'],
            ['route' => 'dashboard.modern.placement-test.index',  'icon' => 'fa-clipboard-check', 'name' => 'Placement Test', 'gate' => 'canViewModernPlacementTest'],
        ],
        // 'salafiyah'               => [ /* tambah saat Salafiyah Pack diimplementasi */ ],
        // 'pesantren-quran-tahfidz' => [ /* tambah saat Tahfidz Pack diimplementasi */ ],
    ],

    /**
     * Kepesantrenan universal sidebar items (Kamar, Pelanggaran, Sanksi, etc.).
     * These are NOT pack-specific — they appear for all tenants regardless of program.
     * DO NOT add pack-specific features here.
     */
    'kepesantrenan' => [
        'hafalan-quran' => [
            'name' => 'Hafalan Quran',
            'icon' => 'fa-book-quran',
            'route' => 'dashboard.kepesantrenan.hafalan-quran.index',
            'description' => 'Manajemen hafalan Al-Quran',
            'gate' => 'canViewHafalanQuran',
        ],
        'hafalan-kitab' => [
            'name' => 'Hafalan Kitab',
            'icon' => 'fa-book',
            'route' => 'dashboard.kepesantrenan.hafalan-kitab.index',
            'description' => 'Manajemen hafalan kitab kuning',
            'gate' => 'canViewHafalanKitab',
        ],
        'target-hafalan' => [
            'name' => 'Target Hafalan',
            'icon' => 'fa-bullseye',
            'route' => 'dashboard.kepesantrenan.target-hafalan.index',
            'description' => 'Manajemen target hafalan santri',
            'gate' => 'canViewTargetHafalan',
        ],
    ],
    
    'sdm' => [
        'name' => 'SDM',
        'icon' => 'fa-users',
        'description' => 'Manajemen Sumber Daya Manusia',
        'features' => [
            'absensi-ustadz' => [
                'name' => 'Absensi Ustadz',
                'icon' => 'fa-clipboard-user',
                'route' => 'dashboard.sdm.absensi-ustadz.index',
                'description' => 'Manajemen absensi ustadz dan karyawan',
            ],
        ],
    ],
    
    'operasional' => [
        'name' => 'Operasional',
        'icon' => 'fa-cogs',
        'description' => 'Manajemen operasional pesantren',
        'features' => [
            'keuangan' => [
                'name' => 'Keuangan',
                'icon' => 'fa-wallet',
                'route' => 'dashboard.keuangan.tagihan',
                'description' => 'Manajemen keuangan dan SPP',
                'admin_only' => true,
            ],
        ],
    ],
];
