<?php

namespace App\Policies;

use App\Models\Shelf;
use App\Models\User;

class ShelfPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function view(User $user, Shelf $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function update(User $user, Shelf $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function delete(User $user, Shelf $model): bool
    {
        return $user->isSuperAdmin();
    }
}
