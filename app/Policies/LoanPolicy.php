<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function view(User $user, Loan $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function update(User $user, Loan $model): bool
    {
        return $user->isSuperAdmin() || $user->isTakimhaneSorumlusu();
    }

    public function delete(User $user, Loan $model): bool
    {
        return $user->isSuperAdmin();
    }
}
