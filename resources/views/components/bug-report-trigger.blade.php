@php
use App\Models\BugReport;

$hasErrors = isset($errors) && $errors->hasAny(['title', 'description', 'category', 'severity']);
@endphp

<div class="fixed bottom-6 right-6 z-50" style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 50;">
    <button type="button"
            x-data
            x-on:click="$dispatch('open-modal', 'bug-report-modal')"
            class="inline-flex items-center gap-2 px-0 sm:px-4 h-14 w-14 sm:w-auto bg-amber-500 hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400 text-white dark:text-white rounded-full shadow-xl ring-2 ring-white/60 dark:ring-gray-900/60 hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition ease-in-out duration-150"
            aria-label="Kirim laporan error">
        <i class="fas fa-bug text-lg"></i>
        <span class="hidden sm:inline font-semibold text-sm">Kirim Error</span>
    </button>

    <x-modal name="bug-report-modal" max-width="lg" :show="$hasErrors">
        <form method="POST"
              action="{{ route('dashboard.bug-reports.store') }}"
              enctype="multipart/form-data"
              class="p-6"
              x-data="{ submitting: false, fileName: '' }"
              x-on:submit="submitting = true">
            @csrf

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-5">
                Kirim Laporan Error
            </h2>

            <!-- Title -->
            <div class="mb-4">
                <x-input-label for="bug-report-title" value="Bagian mana yang error?" />
                <x-text-input id="bug-report-title"
                              name="title"
                              type="text"
                              class="mt-1 block w-full"
                              :value="old('title')"
                              required
                              maxlength="255" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <!-- Description -->
            <div class="mb-4">
                <x-input-label for="bug-report-description" value="Deskripsi error" />
                <textarea id="bug-report-description"
                          name="description"
                          rows="4"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-emerald-500 dark:focus:ring-emerald-500/20 rounded-md shadow-sm transition-colors"
                          required
                          maxlength="5000">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- Category -->
            <div class="mb-4">
                <x-input-label for="bug-report-category" value="Kategori" />
                <select id="bug-report-category"
                        name="category"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-emerald-500 dark:focus:ring-emerald-500/20 rounded-md shadow-sm transition-colors"
                        required>
                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih kategori</option>
                    @foreach (BugReport::$categories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>
                            {{ match($category) {
                                BugReport::CATEGORY_BUG => 'Bug',
                                BugReport::CATEGORY_ERROR => 'Error',
                                BugReport::CATEGORY_FEATURE_REQUEST => 'Permintaan Fitur',
                                BugReport::CATEGORY_SUPPORT => 'Bantuan',
                                default => ucfirst($category),
                            } }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category')" class="mt-2" />
            </div>

            <!-- Severity -->
            <div class="mb-6">
                <x-input-label for="bug-report-severity" value="Tingkat keparahan" />
                <select id="bug-report-severity"
                        name="severity"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-emerald-500 dark:focus:ring-emerald-500/20 rounded-md shadow-sm transition-colors"
                        required>
                    <option value="" disabled {{ old('severity') ? '' : 'selected' }}>Pilih tingkat keparahan</option>
                    @foreach (BugReport::$severities as $severity)
                        <option value="{{ $severity }}" {{ old('severity') === $severity ? 'selected' : '' }}>
                            {{ match($severity) {
                                BugReport::SEVERITY_LOW => 'Rendah',
                                BugReport::SEVERITY_MEDIUM => 'Sedang',
                                BugReport::SEVERITY_HIGH => 'Tinggi',
                                BugReport::SEVERITY_CRITICAL => 'Kritis',
                                default => ucfirst($severity),
                            } }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('severity')" class="mt-2" />
            </div>

            <!-- Screenshot -->
            <div class="mb-6">
                <x-input-label for="bug-report-screenshot" value="Tambahkan screenshot error" />
                <input id="bug-report-screenshot"
                       name="screenshot"
                       type="file"
                       accept="image/png,image/jpeg,image/gif,image/webp"
                       class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-emerald-50 file:text-emerald-700
                              hover:file:bg-emerald-100
                              dark:file:bg-emerald-500/10 dark:file:text-emerald-400
                              dark:hover:file:bg-emerald-500/20"
                       x-on:change="fileName = $event.target.files[0]?.name || ''" />
                <p x-show="fileName"
                   x-text="fileName"
                   class="mt-2 text-xs text-gray-600 dark:text-gray-400 truncate"></p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maksimal 5 MB (JPG, PNG, GIF, WebP).</p>
                <x-input-error :messages="$errors->get('screenshot')" class="mt-2" />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'bug-report-modal')">
                    Batal
                </x-secondary-button>
                <x-primary-button x-bind:disabled="submitting">
                    <span x-show="!submitting">Kirim Laporan</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        Mengirim...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
