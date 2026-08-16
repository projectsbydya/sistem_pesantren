<?php

/**
 * Onboarding Step Registry
 *
 * Single source of truth for the ORDER and metadata of the academic setup
 * wizard. Controllers, models and views MUST resolve step order/labels/
 * routes from here via App\Services\OnboardingStepRegistry — never hardcode
 * step order, numbers, or "next step" literals elsewhere.
 *
 * 'flows' maps a program slug to an ordered list of step keys. Programs not
 * listed fall back to 'default'. This is what allows different programs to
 * define a different onboarding order/subset without touching any code.
 *
 * 'steps' holds metadata for every step referenced by any flow:
 *  - label/title/description/icon/color: UI presentation
 *  - required: must be complete before the flow (and dashboard access) is
 *    considered done
 *  - progress_field: key into TenantSetupService::getActualProgress() used
 *    to determine whether the step is complete. Completion is always
 *    derived from LIVE tenant data (no per-step DB column needed), so new
 *    steps never require a schema migration.
 *  - store_route: the route name the step's form submits to
 *
 * Dependency chain enforced by this order (default flow):
 *   Kelas -> Mata Pelajaran -> Ustadz -> Penugasan Mengajar -> Jadwal -> Ringkasan
 * A step is only reachable once every earlier REQUIRED step is complete.
 */
return [
    'flows' => [
        'default' => ['kelas', 'mapel', 'ustadz', 'penugasan', 'jadwal', 'ringkasan'],
    ],

    'steps' => [
        'kelas' => [
            'label' => 'Kelas',
            'title' => 'Buat Kelas',
            'description' => 'Tambahkan kelas untuk setiap program yang dipilih',
            'icon' => 'fa-chalkboard',
            'color' => 'emerald',
            'required' => true,
            'progress_field' => 'hasKelas',
            'store_route' => 'dashboard.onboarding.wizard.store-kelas',
        ],
        'mapel' => [
            'label' => 'Mata Pelajaran',
            'title' => 'Setup Mata Pelajaran',
            'description' => 'Tambahkan mata pelajaran untuk setiap program',
            'icon' => 'fa-book',
            'color' => 'blue',
            'required' => true,
            'progress_field' => 'hasSubject',
            'store_route' => 'dashboard.onboarding.wizard.store-mapel',
        ],
        'ustadz' => [
            'label' => 'Ustadz',
            'title' => 'Tambah Ustadz',
            'description' => 'Tambahkan ustadz/pengajar sebelum membuat penugasan mengajar',
            'icon' => 'fa-chalkboard-user',
            'color' => 'purple',
            'required' => true,
            'progress_field' => 'hasUstadz',
            'store_route' => 'dashboard.onboarding.wizard.store-ustadz',
        ],
        'penugasan' => [
            'label' => 'Penugasan Mengajar',
            'title' => 'Buat Penugasan Mengajar',
            'description' => 'Tugaskan ustadz ke kelas dan mata pelajaran sebelum membuat jadwal',
            'icon' => 'fa-user-tie',
            'color' => 'indigo',
            'required' => true,
            'progress_field' => 'hasUstadzKelas',
            'store_route' => 'dashboard.onboarding.wizard.store-penugasan',
        ],
        'jadwal' => [
            'label' => 'Jadwal',
            'title' => 'Buat Jadwal',
            'description' => 'Buat jadwal pelajaran untuk kelas-kelas berdasarkan penugasan mengajar',
            'icon' => 'fa-calendar-days',
            'color' => 'amber',
            'required' => true,
            'progress_field' => 'hasSchedule',
            'store_route' => 'dashboard.onboarding.wizard.store-jadwal',
        ],
        'ringkasan' => [
            'label' => 'Ringkasan',
            'title' => 'Setup Selesai',
            'description' => 'Tinjau ringkasan setup pesantren Anda',
            'icon' => 'fa-check-circle',
            'color' => 'teal',
            'required' => false,
            'progress_field' => null,
            'store_route' => null,
        ],
    ],
];
