<x-guest-layout>
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl animated-bg shadow-lg shadow-teal-500/30 mb-4">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Ubah Password</h2>
        <p class="mt-3 text-sm text-gray-600 leading-relaxed">Untuk keamanan akun, silakan ubah password<br>pada login pertama.</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.update-first') }}" class="space-y-6">
        @csrf

        <!-- Current Password -->
        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                Password Saat Ini
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input type="password" id="current_password" name="current_password"
                       class="block w-full !pl-12 pr-10 py-3 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-colors bg-white/50 @error('current_password') border-red-300 @enderror"
                       placeholder="Password dari admin"
                       required autofocus>
                <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition-colors">
                    <svg id="eye-current" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-slash-current" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.574-2.827m2.643-2.643A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-2.225 3.503m-2.643 2.643L3.36 3.36" />
                    </svg>
                </button>
            </div>
            @error('current_password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- New Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                Password Baru
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <input type="password" id="password" name="password"
                       class="block w-full !pl-12 pr-10 py-3 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-colors bg-white/50 @error('password') border-red-300 @enderror"
                       placeholder="Minimal 8 karakter"
                       required oninput="validatePassword(this.value)">
                <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition-colors">
                    <svg id="eye-new" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-slash-new" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.574-2.827m2.643-2.643A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-2.225 3.503m-2.643 2.643L3.36 3.36" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Password Requirements -->
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-500 mb-3">Persyaratan password:</p>
                <div class="space-y-2.5">
                    <div id="req-length" class="flex items-center text-xs text-gray-400 transition-all duration-200">
                        <span class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 transition-all duration-200">
                            <svg class="w-3 h-3 opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Minimal 8 karakter
                    </div>
                    <div id="req-mixed" class="flex items-center text-xs text-gray-400 transition-all duration-200">
                        <span class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 transition-all duration-200">
                            <svg class="w-3 h-3 opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Huruf besar & kecil
                    </div>
                    <div id="req-number" class="flex items-center text-xs text-gray-400 transition-all duration-200">
                        <span class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 transition-all duration-200">
                            <svg class="w-3 h-3 opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Minimal 1 angka
                    </div>
                    <div id="req-symbol" class="flex items-center text-xs text-gray-400 transition-all duration-200">
                        <span class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3 flex-shrink-0 transition-all duration-200">
                            <svg class="w-3 h-3 opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Minimal 1 simbol (!@#$%^&*)
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                Konfirmasi Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="block w-full !pl-12 pr-10 py-3 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-teal-500 transition-colors bg-white/50"
                       placeholder="Ulangi password baru"
                       required oninput="validateMatch()">
                <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition-colors">
                    <svg id="eye-confirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-slash-confirm" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.574-2.827m2.643-2.643A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-2.225 3.503m-2.643 2.643L3.36 3.36" />
                    </svg>
                </button>
            </div>
            <p id="match-error" class="mt-1.5 text-sm text-red-600 hidden">Password tidak cocok</p>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" id="submit-btn"
                    class="w-full flex justify-center items-center py-3.5 px-4 rounded-xl text-white font-bold text-base animated-bg shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:scale-[1.02] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Simpan Password
            </button>
        </div>
    </form>

    <!-- Logout -->
    <div class="mt-8 pt-2 border-t border-gray-100 text-center">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition-colors inline-flex items-center gap-1.5 py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Kembali ke Login
            </button>
        </form>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';

            // Update icons
            const suffix = fieldId === 'current_password' ? 'current' : (fieldId === 'password' ? 'new' : 'confirm');
            document.getElementById('eye-' + suffix).classList.toggle('hidden', !isPassword);
            document.getElementById('eye-slash-' + suffix).classList.toggle('hidden', isPassword);
        }

        function validatePassword(value) {
            // Check length
            const hasLength = value.length >= 8;
            updateRequirement('req-length', hasLength);

            // Check mixed case
            const hasMixed = /[a-z]/.test(value) && /[A-Z]/.test(value);
            updateRequirement('req-mixed', hasMixed);

            // Check number
            const hasNumber = /\d/.test(value);
            updateRequirement('req-number', hasNumber);

            // Check symbol
            const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);
            updateRequirement('req-symbol', hasSymbol);

            validateMatch();
        }

        function updateRequirement(id, valid) {
            const el = document.getElementById(id);
            const circle = el.querySelector('span');
            const check = el.querySelector('svg');

            if (valid) {
                el.classList.remove('text-gray-400');
                el.classList.add('text-teal-600', 'font-medium');
                circle.classList.remove('border-gray-300');
                circle.classList.add('border-teal-500', 'bg-teal-500');
                check.classList.remove('opacity-0');
                check.classList.add('opacity-100', 'text-white');
            } else {
                el.classList.add('text-gray-400');
                el.classList.remove('text-teal-600', 'font-medium');
                circle.classList.add('border-gray-300');
                circle.classList.remove('border-teal-500', 'bg-teal-500');
                check.classList.add('opacity-0');
                check.classList.remove('opacity-100', 'text-white');
            }
        }

        function validateMatch() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const errorEl = document.getElementById('match-error');

            if (confirm && password !== confirm) {
                errorEl.classList.remove('hidden');
            } else {
                errorEl.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>
