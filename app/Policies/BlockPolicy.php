<?php

namespace App\Policies;

use App\Models\Block;
use App\Models\User;

class BlockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function view(User $user, Block $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function update(User $user, Block $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function delete(User $user, Block $model): bool
    {
        return $user->isSuperAdmin();
    }
}
