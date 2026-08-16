<?php

namespace App\View\Composers;

use App\Services\NavigationGateService;
use App\Services\SidebarService;
use App\Services\TenantService;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(
        private SidebarService $sidebarService,
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        $view->with('menuGroups', $this->sidebarService->build($user));
        $view->with('nav', NavigationGateService::forUser($user));
        $view->with('tenant', TenantService::getTenant());
        $view->with('currentRoute', request()->route()?->getName() ?? '');
        $view->with('currentProgram', request()->route('programSlug'));
    }
}
