<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivePengajian extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'live_pengajian';

    protected $fillable = [
        'tenant_id',
        'judul',
        'deskripsi',
        'platform',
        'link_url',
        'meeting_id',
        'passcode',
        'jadwal_mulai',
        'jadwal_selesai',
        'status',
        'thumbnail_url',
        'created_by',
    ];

    protected $casts = [
        'jadwal_mulai'   => 'datetime',
        'jadwal_selesai' => 'datetime',
    ];

    const PLATFORM_ZOOM    = 'zoom';
    const PLATFORM_GMEET   = 'gmeet';
    const PLATFORM_YOUTUBE = 'youtube';

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_LIVE      = 'live';
    const STATUS_ENDED     = 'ended';

    const PLATFORM_LABELS = [
        'zoom'    => 'Zoom',
        'gmeet'   => 'Google Meet',
        'youtube' => 'YouTube Live',
    ];

    const PLATFORM_ICONS = [
        'zoom'    => 'fa-video',
        'gmeet'   => 'fa-video',
        'youtube' => 'fa-youtube',
    ];

    const PLATFORM_COLORS = [
        'zoom'    => 'blue',
        'gmeet'   => 'green',
        'youtube' => 'red',
    ];

    const STATUS_LABELS = [
        'scheduled' => 'Terjadwal',
        'live'      => 'Sedang Live',
        'ended'     => 'Selesai',
    ];

    const STATUS_COLORS = [
        'scheduled' => 'warning',
        'live'      => 'danger',
        'ended'     => 'secondary',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->jadwal_mulai->isFuture();
    }
}
