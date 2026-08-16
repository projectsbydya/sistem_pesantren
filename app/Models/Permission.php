<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Permission constants
     */
    // Santri permissions
    public const VIEW_SANTRI = 'view_santri';
    public const CREATE_SANTRI = 'create_santri';
    public const UPDATE_SANTRI = 'update_santri';
    public const DELETE_SANTRI = 'delete_santri';

    // Ustadz permissions
    public const VIEW_USTADZ = 'view_ustadz';
    public const CREATE_USTADZ = 'create_ustadz';
    public const UPDATE_USTADZ = 'update_ustadz';
    public const DELETE_USTADZ = 'delete_ustadz';

    // Akademik permissions
    public const VIEW_KELAS = 'view_kelas';
    public const MANAGE_KELAS = 'manage_kelas';
    public const VIEW_NILAI = 'view_nilai';
    public const INPUT_NILAI = 'input_nilai';
    public const VIEW_ABSENSI = 'view_absensi';
    public const INPUT_ABSENSI = 'input_absensi';

    // Keuangan permissions
    public const MANAGE_FINANCES = 'manage_finances';
    public const VIEW_SPP = 'view_spp';
    public const MANAGE_SPP = 'manage_spp';
    public const VIEW_TABUNGAN = 'view_tabungan';
    public const MANAGE_TABUNGAN = 'manage_tabungan';

    // Tenant management (super admin only)
    public const MANAGE_TENANTS = 'manage_tenants';
}
