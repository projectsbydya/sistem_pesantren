<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Landing Page Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk landing page. Nilai ini dapat di-set via .env
    | atau akan menggunakan nilai default jika tidak di-set.
    |
    */

    // Tagline/badge di hero section
    'tagline' => env('LANDING_TAGLINE', 'Solusi Digital Pesantren #1 di Indonesia'),

    // Judul utama hero section
    'title' => env('LANDING_TITLE', 'Kelola Pesantren Lebih Mudah'),

    // Subtitle/Deskripsi hero section
    'subtitle' => env('LANDING_SUBTITLE', 'Platform all-in-one untuk manajemen santri, akademik, keuangan, dan asrama. Transformasi digital pondok pesantren Anda dimulai dari sini.'),

    // Tombol CTA Primary
    'cta_primary' => env('LANDING_CTA_PRIMARY', 'Mulai Sekarang'),

    // Tombol CTA Secondary
    'cta_secondary' => env('LANDING_CTA_SECONDARY', 'Hubungi Kami'),

    // Deskripsi pada CTA card hero section (tanpa angka statistik)
    'cta_description' => env('LANDING_CTA_DESCRIPTION', 'Digitalisasi sistem operasional pesantren Anda dalam satu platform terpadu.'),

    // Label tombol WhatsApp di CTA card hero
    'cta_whatsapp_label' => env('LANDING_CTA_WHATSAPP_LABEL', 'Chat WhatsApp'),

    // Kontak WhatsApp (format dengan strip: 0812-3456-7890)
    'whatsapp' => env('LANDING_WHATSAPP', '0812-3456-7890'),

    // Kontak Email
    'email' => env('LANDING_EMAIL', 'info@kelolapesantren.com'),

    // Nama aplikasi yang tampil di landing (override config app.name)
    'app_name' => env('LANDING_APP_NAME', env('APP_NAME', 'Kelola Pesantren')),

    // URL Google Maps lokasi (opsional)
    'maps_url' => env('LANDING_MAPS_URL', ''),

    // Alamat lengkap (opsional)
    'address' => env('LANDING_ADDRESS', 'Indonesia'),

    // Hero dashboard capability cards (inside dashboard mockup)
    'hero_dashboard_cards' => [
        [
            'title' => 'Multi Tenant',
            'subtitle' => 'Satu platform untuk banyak lembaga.',
            'icon' => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        ],
        [
            'title' => 'Multi Program',
            'subtitle' => 'Diniyah, Modern, Salafiyah, dan lainnya.',
            'icon' => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        ],
        [
            'title' => 'Cloud Based',
            'subtitle' => 'Akses dari mana saja, kapan saja.',
            'icon' => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>',
        ],
    ],

    // Hero floating cards
    'hero_floating_cards' => [
        [
            'title' => 'Real-time',
            'subtitle' => 'Data selalu sinkron otomatis.',
            'icon' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        [
            'title' => 'Role Permission',
            'subtitle' => 'Akses sesuai peran pengguna.',
            'icon' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
        ],
    ],

    // Hero bottom capability stats
    'hero_bottom_stats' => [
        [
            'title' => 'Akademik',
            'subtitle' => 'Jadwal, nilai & absensi.',
            'icon' => '<svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        ],
        [
            'title' => 'SPP Digital',
            'subtitle' => 'Pembayaran online terintegrasi.',
            'icon' => '<svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        ],
        [
            'title' => 'E-Raport',
            'subtitle' => 'Rapor elektronik lengkap.',
            'icon' => '<svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ],
    ],

    // Social Media Links (opsional)
    'social' => [
        'facebook' => env('LANDING_FACEBOOK_URL', ''),
        'instagram' => env('LANDING_INSTAGRAM_URL', ''),
        'twitter' => env('LANDING_TWITTER_URL', ''),
        'youtube' => env('LANDING_YOUTUBE_URL', ''),
    ],

];
