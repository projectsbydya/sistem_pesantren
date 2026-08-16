<?php

namespace App\Policies;

use App\Models\UstadzKelas;
use App\Models\User;
use App\Models\Vocabulary;
use App\Policies\Concerns\HasReadonlyAccess;

class VocabularyPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, Vocabulary $vocabulary): bool
    {
        return $this->viewRecordAllowed($user, $vocabulary, $vocabulary->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->createAllowed($user);
    }

    public function update(User $user, Vocabulary $vocabulary): bool
    {
        return $this->modifyAllowed($user, $vocabulary);
    }

    public function delete(User $user, Vocabulary $vocabulary): bool
    {
        return $this->modifyAllowed($user, $vocabulary);
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        return $this->recordForAllowed($user, $ustadzKelas);
    }
}
