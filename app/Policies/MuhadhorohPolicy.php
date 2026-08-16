<?php

namespace App\Policies;

use App\Models\Muhadhoroh;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Policies\Concerns\HasReadonlyAccess;

class MuhadhorohPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, Muhadhoroh $muhadhoroh): bool
    {
        return $this->viewRecordAllowed($user, $muhadhoroh, $muhadhoroh->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->createAllowed($user);
    }

    public function update(User $user, Muhadhoroh $muhadhoroh): bool
    {
        return $this->modifyAllowed($user, $muhadhoroh);
    }

    public function delete(User $user, Muhadhoroh $muhadhoroh): bool
    {
        return $this->modifyAllowed($user, $muhadhoroh);
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        return $this->recordForAllowed($user, $ustadzKelas);
    }
}
