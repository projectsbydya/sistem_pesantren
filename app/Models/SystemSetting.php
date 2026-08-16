<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = ['key', 'value', 'description', 'updated_at'];

    public $timestamps = false;

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get all landing page settings
     */
    public static function getLandingSettings(): array
    {
        $keys = [
            'landing.tagline',
            'landing.title',
            'landing.subtitle',
            'landing.cta_primary',
            'landing.cta_secondary',
            'landing.whatsapp',
            'landing.email',
            'landing.stats_pesantren',
            'landing.stats_santri',
            'landing.stats_kepuasan',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[str_replace('.', '_', $key)] = self::get($key);
        }

        return $settings;
    }
}
