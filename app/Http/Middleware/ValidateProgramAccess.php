<?php

namespace App\Http\Middleware;

use App\Services\ProgramAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidateProgramAccess
{
    public function handle(Request $request, Closure $next)
    {
        $programSlug = $request->route('programSlug');
        if (!$programSlug) {
            return $next($request);
        }
        $program = ProgramAccessService::getBySlug($programSlug);
        if (!$program) {
            throw new NotFoundHttpException('Program tidak ditemukan atau Anda tidak memiliki akses.');
        }
        $request->merge(['_validated_program' => $program]);
        return $next($request);
    }
}
