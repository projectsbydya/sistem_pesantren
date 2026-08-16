<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Landing Page Settings
            [
                'key' => 'landing.tagline',
                'value' => 'Solusi Digital Pesantren #1 di Indonesia',
                'description' => 'Tagline/badge di hero section',
            ],
            [
                'key' => 'landing.title',
                'value' => 'Kelola Pesantren Lebih Mudah',
                'description' => 'Judul utama hero section',
            ],
            [
                'key' => 'landing.subtitle',
                'value' => 'Platform all-in-one untuk manajemen santri, akademik, keuangan, dan asrama. Transformasi digital pondok pesantren Anda dimulai dari sini.',
                'description' => 'Subtitle hero section',
            ],
            [
                'key' => 'landing.cta_primary',
                'value' => 'Mulai Sekarang',
                'description' => 'Text tombol CTA utama',
            ],
            [
                'key' => 'landing.cta_secondary',
                'value' => 'Hubungi Kami',
                'description' => 'Text tombol CTA sekunder',
            ],
            [
                'key' => 'landing.whatsapp',
                'value' => '0812-3456-7890',
                'description' => 'Nomor WhatsApp kontak',
            ],
            [
                'key' => 'landing.email',
                'value' => 'info@kelolapesantren.com',
                'description' => 'Email kontak',
            ],
            [
                'key' => 'landing.stats_pesantren',
                'value' => '500+',
                'description' => 'Statistik jumlah pesantren aktif',
            ],
            [
                'key' => 'landing.stats_santri',
                'value' => '50K+',
                'description' => 'Statistik jumlah santri terdata',
            ],
            [
                'key' => 'landing.stats_kepuasan',
                'value' => '99%',
                'description' => 'Statistik tingkat kepuasan pengguna',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::set($setting['key'], $setting['value'], $setting['description']);
        }
    }
}
