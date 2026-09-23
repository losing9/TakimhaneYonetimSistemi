<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;

class DailyReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, DailyReport $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, DailyReport $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, DailyReport $model): bool
    {
        return $user->isSuperAdmin();
    }
}
