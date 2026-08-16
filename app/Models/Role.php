<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    /**
     * Role constants for clean architecture
     */
    public const SUPER_ADMIN = 'super_admin';
    public const TENANT_ADMIN = 'tenant_admin';
    public const BENDAHARA = 'bendahara';
    public const USTADZ = 'ustadz';
    public const PARENT = 'parent';
    public const SANTRI = 'santri';

}
