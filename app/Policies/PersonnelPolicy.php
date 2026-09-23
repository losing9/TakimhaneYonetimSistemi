<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;

class PersonnelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function view(User $user, Personnel $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function update(User $user, Personnel $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function delete(User $user, Personnel $model): bool
    {
        return $user->isSuperAdmin();
    }
}
