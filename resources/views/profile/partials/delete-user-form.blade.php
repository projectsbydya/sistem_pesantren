<section class="space-y-5">
    <header>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Hapus Akun
                </h2>
                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                    Setelah akun dihapus, semua data dan sumber dayanya akan dihapus secara permanen.
                </p>
            </div>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Apakah Anda yakin ingin menghapus akun?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Akun akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengonfirmasi.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata Sandi" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="Kata Sandi"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
