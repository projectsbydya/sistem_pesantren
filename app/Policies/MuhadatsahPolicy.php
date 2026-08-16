<?php

namespace App\Policies;

use App\Models\Muhadatsah;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Policies\Concerns\HasReadonlyAccess;

class MuhadatsahPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, Muhadatsah $muhadatsah): bool
    {
        return $this->viewRecordAllowed($user, $muhadatsah, $muhadatsah->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->createAllowed($user);
    }

    public function update(User $user, Muhadatsah $muhadatsah): bool
    {
        return $this->modifyAllowed($user, $muhadatsah);
    }

    public function delete(User $user, Muhadatsah $muhadatsah): bool
    {
        return $this->modifyAllowed($user, $muhadatsah);
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        return $this->recordForAllowed($user, $ustadzKelas);
    }
}
