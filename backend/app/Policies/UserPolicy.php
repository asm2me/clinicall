<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('clinic_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') || $user->getKey() === $model->getKey();
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') || $user->getKey() === $model->getKey();
    }
}