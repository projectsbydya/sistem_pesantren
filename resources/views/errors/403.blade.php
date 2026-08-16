<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    @php
        $errorMsg = $exception?->getMessage() ?? '';
        $isSuspended = str_contains($errorMsg, 'inactive') || str_contains($errorMsg, 'suspended') || str_contains($errorMsg, 'Tenant is inactive');
        $isTrialExpired = str_contains($errorMsg, 'Trial') || str_contains($errorMsg, 'trial');
    @endphp

    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">

            @if($isSuspended || $isTrialExpired)
                <!-- Suspended / Trial Expired Icon -->
                <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>

                @if($isSuspended)
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Akun Dinonaktifkan</h1>
                    <p class="text-gray-600 mb-2">
                        Akses ke pesantren Anda telah dinonaktifkan oleh administrator.
                    </p>
                    <p class="text-sm text-gray-500 mb-6">
                        Silakan hubungi administrator sistem untuk informasi lebih lanjut.
                    </p>
                @else
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Masa Percobaan Berakhir</h1>
                    <p class="text-gray-600 mb-2">
                        Masa percobaan (trial) akun Anda telah habis.
                    </p>
                    <p class="text-sm text-gray-500 mb-6">
                        Silakan hubungi administrator sistem untuk melanjutkan langganan.
                    </p>
                @endif

                @auth
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 text-left">
                        <p class="text-sm text-amber-700 font-medium">Anda masih masuk sebagai:</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ Auth::user()->name }}</p>
                        @if(Auth::user()->tenant)
                            <p class="text-sm text-gray-600">{{ Auth::user()->tenant->name }}</p>
                        @endif
                    </div>
                @endauth

                <!-- Logout as primary action for suspended accounts -->
                <div class="space-y-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                            Keluar dari Akun
                        </button>
                    </form>
                    @auth
                        @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('dashboard.super-admin.index') }}" class="block w-full py-3 px-4 bg-amber-100 text-amber-700 font-medium rounded-lg hover:bg-amber-200 transition">
                                Ke Super Admin Panel
                            </a>
                        @endif
                    @endauth
                </div>

            @else
                <!-- Generic 403 Icon -->
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Akses Ditolak</h1>
                <p class="text-gray-600 mb-6">
                    Anda tidak memiliki izin untuk mengakses halaman ini.
                </p>

                @auth
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                        <p class="text-sm text-gray-500">Role Anda:</p>
                        <p class="font-medium text-gray-900">{{ Auth::user()->getRoleLabel() }}</p>
                        @if(Auth::user()->tenant)
                            <p class="text-sm text-gray-500 mt-2">Pesantren:</p>
                            <p class="font-medium text-gray-900">{{ Auth::user()->tenant->name }}</p>
                        @endif
                    </div>
                @endauth

                <div class="space-y-3">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : tenant_route('dashboard.index') }}" class="block w-full py-3 px-4 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition">
                        Kembali
                    </a>

                    @auth
                        @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('dashboard.super-admin.index') }}" class="block w-full py-3 px-4 bg-amber-100 text-amber-700 font-medium rounded-lg hover:bg-amber-200 transition">
                                Ke Super Admin Panel
                            </a>
                        @endif
                    @endauth

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 border border-gray-300 text-gray-600 font-medium rounded-lg hover:bg-gray-50 transition">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-6">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
