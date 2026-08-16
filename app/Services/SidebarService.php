<?php

namespace App\Services;

use App\Models\SantriProgram;
use App\Models\User;
use Illuminate\Support\Collection;

class SidebarService
{
    public function build(?User $user = null): array
    {
        $user ??= auth()->user();
        if (! $user) {
            return [];
        }

        $tenant = TenantService::getTenant();
        $nav = NavigationGateService::forUser($user);
        $programs = $tenant?->activePrograms()->get() ?? collect();

        if ($nav->isStudent() || $nav->isParent()) {
            $accessibleSantriIds = $user->getAccessibleSantriIds();
            $enrolledProgramIds = [];

            if (! empty($accessibleSantriIds)) {
                $enrolledProgramIds = SantriProgram::whereIn('santri_id', $accessibleSantriIds)
                    ->distinct()
                    ->pluck('program_id')
                    ->toArray();
            }

            $programs = $programs->whereIn('id', $enrolledProgramIds);
        }

        return $this->buildMenuGroups($nav, $programs);
    }

    private function buildMenuGroups(NavigationGateService $nav, Collection $programs): array
    {
        $commonUtama = [
            'title' => 'Utama',
            'items' => [
                $this->item('dashboard.index', 'fa-gauge-high', 'Dashboard', 'dashboard.index'),
                $this->item('profile.edit', 'fa-user', 'Profil', 'profile.'),
            ],
        ];

        $lainnya = [
            'title' => 'Lainnya',
            'items' => [
                $this->item('dashboard.live-pengajian.index', 'fa-circle-play', 'Live Pengajian', 'dashboard.live-pengajian', 'canViewLivePengajian'),
            ],
        ];

        if ($nav->isUstadz()) {
            return [
                $commonUtama,
                [
                    'title' => 'Aktivitas Saya',
                    'items' => $this->buildUstadzProgramItems($programs),
                ],
                [
                    'title' => 'Kepesantrenan',
                    'items' => [
                        $this->item('dashboard.kepesantrenan.perizinan.pending', 'fa-clock', 'Perizinan', 'dashboard.kepesantrenan.perizinan', 'canViewPerizinan'),
                    ],
                ],
                [
                    'title' => 'SDM',
                    'items' => [
                        $this->item('dashboard.sdm.absensi-ustadz.index', 'fa-clipboard-user', 'Absensi Saya', 'dashboard.sdm.absensi-ustadz', 'canViewAbsensiUstadz'),
                    ],
                ],
                $lainnya,
            ];
        }

        if ($nav->isStudent() || $nav->isParent()) {
            return [
                $commonUtama,
                [
                    'title' => 'Operasional',
                    'gate' => 'canViewFinanceSection',
                    'items' => [
                        $this->item('dashboard.spp.index', 'fa-file-invoice-dollar', 'Tagihan', 'dashboard.spp', 'canViewSpp'),
                        $this->item('dashboard.tabungan.index', 'fa-piggy-bank', 'Tabungan', 'dashboard.tabungan', 'canViewTabungan'),
                    ],
                ],
                [
                    'title' => 'Kepesantrenan',
                    'items' => [
                        $this->item('dashboard.kepesantrenan.perizinan.index', 'fa-file-signature', 'Pengajuan Izin', 'dashboard.kepesantrenan.perizinan', 'canViewPerizinan'),
                    ],
                ],
                [
                    'title' => 'Akademik',
                    'gate' => 'canViewAcademicSection',
                    'dropdowns' => $this->buildProgramDropdowns($nav, $programs),
                ],
                $lainnya,
            ];
        }

        return [
            $commonUtama,
            [
                'title' => 'Data Pendidikan',
                'icon' => 'fa-graduation-cap',
                'dropdown' => true,
                'items' => [
                    $this->item('dashboard.santri.index', 'fa-users', 'Data Santri', 'dashboard.santri', 'canViewSantri'),
                    $this->item('dashboard.ustadz.index', 'fa-chalkboard-user', 'Data Ustadz', 'dashboard.ustadz', 'canViewUstadz'),
                    $this->item('dashboard.parent.index', 'fa-people-roof', 'Data Orang Tua', 'dashboard.parent', 'canViewParents'),
                    $this->item('dashboard.sdm.absensi-ustadz.index', 'fa-clipboard-user', 'Absensi Ustadz', 'dashboard.sdm.absensi-ustadz', 'canViewAbsensiUstadz'),
                ],
            ],
            [
                'title' => 'Kepesantrenan',
                'icon' => 'fa-mosque',
                'dropdown' => true,
                'gate' => 'canViewKepesantrenanSection',
                'items' => [
                    $this->item('dashboard.kepesantrenan.kamar.index', 'fa-bed', 'Kamar', 'dashboard.kepesantrenan.kamar', 'canViewKamar'),
                    $this->item('dashboard.kepesantrenan.penempatan.index', 'fa-user-plus', 'Penempatan Kamar', 'dashboard.kepesantrenan.penempatan', 'canViewPenempatanKamar'),
                    $this->item('dashboard.kepesantrenan.mutasi.index', 'fa-people-arrows', 'Mutasi Kamar', 'dashboard.kepesantrenan.mutasi', 'canViewMutasiKamar'),
                    $this->item('dashboard.kepesantrenan.pelanggaran.index', 'fa-triangle-exclamation', 'Pelanggaran', 'dashboard.kepesantrenan.pelanggaran', 'canViewPelanggaran'),
                    $this->item('dashboard.kepesantrenan.sanksi.index', 'fa-gavel', 'Sanksi', 'dashboard.kepesantrenan.sanksi', 'canViewSanksi'),
                    $this->item('dashboard.kepesantrenan.perizinan.index', 'fa-file-signature', 'Perizinan', 'dashboard.kepesantrenan.perizinan', 'canViewPerizinan'),
                    $this->item('dashboard.kepesantrenan.monitoring-karakter.index', 'fa-heart-pulse', 'Monitoring Karakter', 'dashboard.kepesantrenan.monitoring-karakter', 'canViewMonitoringKarakter'),
                    $this->item('dashboard.kepesantrenan.kegiatan-harian.index', 'fa-calendar-check', 'Kegiatan Harian', 'dashboard.kepesantrenan.kegiatan-harian', 'canViewKegiatanHarian'),
                ],
            ],
            [
                'title' => 'Akademik',
                'gate' => 'canViewAcademicSection',
                'dropdowns' => $this->buildProgramDropdowns($nav, $programs),
            ],
            [
                'title' => 'Operasional',
                'gate' => 'canViewFinanceSection',
                'items' => [
                    $this->item('dashboard.spp.index', 'fa-file-invoice-dollar', 'Tagihan', 'dashboard.spp', 'canViewSpp'),
                    $this->item('dashboard.tabungan.index', 'fa-piggy-bank', 'Tabungan', 'dashboard.tabungan', 'canViewTabungan'),
                ],
            ],
            $lainnya,
        ];
    }

    private function buildProgramDropdowns(NavigationGateService $nav, Collection $programs): array
    {
        $dropdowns = [];
        $features = config('academic_programs.features', []);

        foreach ($programs as $program) {
            $items = [];
            foreach ($features as $featureKey => $feature) {
                $label = $feature['name'];
                if ($nav->isStudent() && !empty($feature['student_name'])) {
                    $label = $feature['student_name'];
                }

                $items[$featureKey] = [
                    'href' => $this->route($feature['route'], ['programSlug' => $program->slug]),
                    'icon' => $feature['icon'],
                    'label' => $label,
                    'active' => $feature['route'],
                    'program' => $program->slug,
                    'gate' => $feature['gate'] ?? null,
                    'feature' => $featureKey,
                ];
            }

            foreach (config('academic_programs.packs.' . $program->slug, []) as $packFeature) {
                $items[] = [
                    'href' => $this->route($packFeature['route'], ['programSlug' => $program->slug]),
                    'icon' => $packFeature['icon'],
                    'label' => $packFeature['name'],
                    'active' => $packFeature['route'],
                    'program' => $program->slug,
                    'gate' => $packFeature['gate'] ?? null,
                ];
            }

            $visibleItems = array_filter($items, fn ($item) => $nav->allows($item['gate'] ?? null));
            $visibleItems = $this->groupFeatureItems(array_values($visibleItems), $features);

            $dropdowns[] = [
                'id' => 'dropdown-' . $program->slug,
                'icon' => $program->icon ?? 'fa-graduation-cap',
                'label' => $program->name,
                'items' => $visibleItems,
            ];
        }

        return $dropdowns;
    }

    private function groupFeatureItems(array $items, array $features): array
    {
        $grouped = [];
        foreach ($items as $index => $item) {
            $key = $item['feature'] ?? $index;
            $grouped[$key] = $item;
            $grouped[$key]['children'] = [];
        }

        foreach ($items as $item) {
            $key = $item['feature'] ?? null;
            $parentKey = $key ? ($features[$key]['parent'] ?? null) : null;
            if ($parentKey && isset($grouped[$parentKey]) && $parentKey !== $key) {
                $grouped[$parentKey]['children'][] = $item;
                unset($grouped[$key]);
            }
        }

        return array_values($grouped);
    }

    private function buildUstadzProgramItems(Collection $programs): array
    {
        $items = [];
        $ustadzFeatures = [
            ['route' => 'dashboard.akademik.jadwal.index', 'icon' => 'fa-calendar-week', 'label' => config('academic_programs.features.jadwal.name', 'Jadwal'), 'active' => 'dashboard.akademik.jadwal', 'gate' => 'canViewJadwal'],
            ['route' => 'dashboard.akademik.kelas.index', 'icon' => 'fa-chalkboard', 'label' => 'Kelas', 'active' => 'dashboard.akademik.kelas', 'gate' => 'canViewKelas'],
            ['route' => 'dashboard.akademik.subjects.index', 'icon' => 'fa-book', 'label' => 'Mata Pelajaran', 'active' => 'dashboard.akademik.subjects', 'gate' => 'canViewMataPelajaran'],
            ['route' => 'dashboard.akademik.absensi.index', 'icon' => 'fa-clipboard-list', 'label' => 'Absensi', 'active' => 'dashboard.akademik.absensi', 'gate' => 'canViewAbsensiSantri'],
            ['route' => 'dashboard.akademik.nilai.index', 'icon' => 'fa-chart-line', 'label' => 'Nilai', 'active' => 'dashboard.akademik.nilai', 'gate' => 'canViewNilai'],
        ];

        foreach ($programs as $program) {
            foreach ($ustadzFeatures as $feature) {
                $items[] = [
                    'href' => $this->route($feature['route'], ['programSlug' => $program->slug]),
                    'icon' => $feature['icon'],
                    'label' => $feature['label'] . ' ' . $program->name,
                    'active' => $feature['active'],
                    'program' => $program->slug,
                    'gate' => $feature['gate'],
                ];
            }
        }

        return $items;
    }

    private function item(string $route, string $icon, string $label, string $active, ?string $gate = null): array
    {
        return [
            'href' => $this->route($route),
            'icon' => $icon,
            'label' => $label,
            'active' => $active,
            'gate' => $gate,
        ];
    }

    private function route(string $name, array $params = []): string
    {
        try {
            return tenant_route($name, $params);
        } catch (\Throwable $e) {
            return '#';
        }
    }
}
