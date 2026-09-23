<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function view(User $user, Tool $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function update(User $user, Tool $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function delete(User $user, Tool $model): bool
    {
        return $user->isSuperAdmin();
    }
}
