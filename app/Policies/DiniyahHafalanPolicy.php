<?php

namespace App\Policies;

use App\Models\DiniyahHafalan;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Policies\Concerns\HasReadonlyAccess;

class DiniyahHafalanPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, DiniyahHafalan $diniyahHafalan): bool
    {
        return $this->viewRecordAllowed($user, $diniyahHafalan, $diniyahHafalan->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->createAllowed($user);
    }

    public function update(User $user, DiniyahHafalan $diniyahHafalan): bool
    {
        return $this->modifyAllowed($user, $diniyahHafalan);
    }

    public function delete(User $user, DiniyahHafalan $diniyahHafalan): bool
    {
        return $this->modifyAllowed($user, $diniyahHafalan);
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        return $this->recordForAllowed($user, $ustadzKelas);
    }
}
