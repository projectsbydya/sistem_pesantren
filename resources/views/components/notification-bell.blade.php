@php
$indexUrl = route('notifications.index');
$unreadCountUrl = route('notifications.unread-count');
$markReadUrlTemplate = route('notifications.read', ['notification' => '__ID__']);
$markAllReadUrl = route('notifications.mark-all-read');
@endphp

<div class="relative"
     x-data="notificationBell({
         indexUrl: '{{ $indexUrl }}',
         unreadCountUrl: '{{ $unreadCountUrl }}',
         markReadUrlTemplate: '{{ $markReadUrlTemplate }}',
         markAllReadUrl: '{{ $markAllReadUrl }}',
     })"
     @click.away="open = false"
     x-init="init()">
    <button type="button"
            @click="toggle()"
            class="relative p-2 rounded-lg transition-colors text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
            aria-label="Notifikasi">
        <i class="fa-solid fa-bell text-sm"></i>
        <span x-show="unreadCount > 0"
              x-text="unreadCount"
              x-cloak
              class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900">
        </span>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak
         class="absolute right-0 mt-2 w-80 sm:w-96 max-h-[80vh] overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 z-50 flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700 shrink-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifikasi</h3>
            <button type="button"
                    @click="markAllRead()"
                    x-show="unreadCount > 0"
                    x-cloak
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                Tandai semua dibaca
            </button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" x-cloak class="p-6 text-center shrink-0">
            <i class="fa-solid fa-circle-notch fa-spin text-gray-400 dark:text-gray-500"></i>
        </div>

        {{-- Error --}}
        <div x-show="error && !loading" x-cloak class="p-4 text-center shrink-0">
            <p class="text-xs text-red-600 dark:text-red-400" x-text="error"></p>
            <button type="button"
                    @click="refresh()"
                    class="mt-2 text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                Coba lagi
            </button>
        </div>

        {{-- Empty state --}}
        <div x-show="!loading && !error && notifications.length === 0" x-cloak class="p-8 text-center shrink-0">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-bell-slash text-gray-400 dark:text-gray-500"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi</p>
        </div>

        {{-- Notification list --}}
        <div x-show="!loading && !error && notifications.length > 0" x-cloak class="overflow-y-auto max-h-[60vh]">
            <template x-for="notification in notifications" :key="notification.id">
                <div @click="handleClick(notification)"
                     class="group flex items-start gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30"
                     :class="{ 'bg-emerald-50/40 dark:bg-emerald-500/5': !notification.read_at }">
                    <div class="mt-1.5 shrink-0">
                        <span class="block w-2 h-2 rounded-full"
                              :class="notification.read_at ? 'bg-transparent' : 'bg-emerald-500'"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                           x-text="notification.data?.title ?? 'Notifikasi'"></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 break-words"
                           x-text="notification.data?.message ?? ''"></p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1"
                           x-text="timeAgo(notification.created_at)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationBell', (config) => ({
            open: false,
            loading: false,
            error: null,
            notifications: [],
            unreadCount: 0,
            ...config,

            init() {
                this.fetchUnreadCount();
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.refresh();
                }
            },

            async fetchUnreadCount() {
                try {
                    const response = await fetch(this.unreadCountUrl, {
                        headers: this.headers(),
                    });
                    if (!response.ok) throw new Error('Gagal memuat jumlah notifikasi');
                    const data = await response.json();
                    this.unreadCount = data.count ?? 0;
                } catch (e) {
                    // Fail silently for badge; user can open dropdown to see error.
                    this.unreadCount = 0;
                }
            },

            async refresh() {
                this.loading = true;
                this.error = null;
                try {
                    const [countRes, listRes] = await Promise.all([
                        fetch(this.unreadCountUrl, { headers: this.headers() }),
                        fetch(this.indexUrl, { headers: this.headers() }),
                    ]);
                    if (!countRes.ok || !listRes.ok) {
                        throw new Error('Gagal memuat notifikasi');
                    }
                    const countData = await countRes.json();
                    const listData = await listRes.json();
                    this.unreadCount = countData.count ?? 0;
                    this.notifications = listData.data ?? [];
                } catch (e) {
                    this.error = e.message || 'Terjadi kesalahan';
                } finally {
                    this.loading = false;
                }
            },

            async markAllRead() {
                try {
                    const response = await fetch(this.markAllReadUrl, {
                        method: 'POST',
                        headers: this.headers(),
                    });
                    if (!response.ok) throw new Error('Gagal menandai dibaca');
                    this.notifications.forEach((n) => { n.read_at = n.read_at ?? new Date().toISOString(); });
                    this.unreadCount = 0;
                } catch (e) {
                    this.error = e.message || 'Terjadi kesalahan';
                }
            },

            async markRead(notification) {
                if (notification.read_at) return;
                try {
                    const url = this.markReadUrlTemplate.replace('__ID__', notification.id);
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: this.headers(),
                    });
                    if (!response.ok) throw new Error('Gagal menandai dibaca');
                    const data = await response.json();
                    notification.read_at = data.read_at ?? new Date().toISOString();
                    if (this.unreadCount > 0) this.unreadCount--;
                } catch (e) {
                    this.error = e.message || 'Terjadi kesalahan';
                }
            },

            async handleClick(notification) {
                const url = notification.data?.action_url;
                await this.markRead(notification);
                if (url) {
                    window.location.href = url;
                }
            },

            headers() {
                return {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                };
            },

            timeAgo(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                if (seconds < 60) return 'baru saja';
                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return `${minutes} menit lalu`;
                const hours = Math.floor(minutes / 60);
                if (hours < 24) return `${hours} jam lalu`;
                const days = Math.floor(hours / 24);
                if (days < 30) return `${days} hari lalu`;
                return date.toLocaleDateString('id-ID');
            },
        }));
    });
</script>
